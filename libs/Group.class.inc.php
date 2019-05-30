<?php

class Group extends LdapObject
{
    protected static $idField = "cn";
    protected static $ou = __LDAP_GROUP_OU__;
    ////////////////Private Variables//////////

    private $userUIDs = null;
    private $users = null;
    private $description;
    private $name;
    private $gidNumber;

    private $serverdirs = array();
    private $owner;

    private $creator;
    private $createTime;
    private $modifier;
    private $modifyTime;

    private $isGroupOfNames = false;

    ////////////////Public Functions///////////

    /**
     * Group constructor.
     * @param string $name
     */
    public function __construct($name = "") {
        if ( $name != "" ) {
            $this->load_by_id($name);
        }
    }


    public function __destruct() {
    }


    // Inserts a group into the database with the given name, then loads that group into this object. Displays errors if there are any.
    public function create($name, $description) {
        $name = trim($name);

        $error = false;
        $message = "";
        //Verify Name
        if ( self::exists($name) ) {
            $error = true;
            $message = "A group with that name already exists.";
        }
        // Verify description
        if ( strlen($description) == 0 ) {
            $error = true;
            $message = "Description must not be blank.";
        }

        //If Errors, return with error messages
        if ( $error ) {
            return array(
                'RESULT' => false,
                'MESSAGE' => $message,
            );
        } //Everything looks good, add group
        else {

            // Get existing gidnumbers
            $groups = Ldap::getInstance()->search("(!(cn=ftp_*))", static::$ou, array('cn', 'gidNumber'));
            $gidnumbers = array();
            for ( $i = 0; $i < $groups['count']; $i++ ) {
                if ( isset($groups[$i]['gidnumber']) ) {
                    $gidnumbers[] = $groups[$i]['gidnumber'][0];
                }
            }
            // Find next highest gidnumber (starting at 20000)
            $gidstart = 20000;
            $gidnumber = max($gidstart, max($gidnumbers)) + 1;

            // Add to LDAP
            $dn = "cn=" . $name . "," . static::$ou;
            $data = array(
                "cn" => $name,
                "objectClass" => array('posixGroup', 'sambaGroupMapping'),
                "gidNumber" => $gidnumber,
                "description" => $description,
                'sambaGroupType' => 2,
                'sambaSID' => __SAMBA_ID__ . "-" . $gidnumber,
            );
            Ldap::getInstance()->add($dn, $data);

            $this->load_by_id($name);

            Log::info("Added group " . $this->getName(), Log::GROUP_ADD, $this);
            return array(
                'RESULT' => true,
                'MESSAGE' => 'Group successfully added.',
                'gid' => $name,
            );
        }

    }


    public function remove() {
        $dn = $this->getRDN();
        if ( Ldap::getInstance()->remove($dn) ) {
            Log::info("Removed group " . $this->getName(), Log::GROUP_REMOVE, $this);
            return array(
                'RESULT' => true,
                'MESSAGE' => 'Group removed.',
                'gid' => $this->name,
            );
        }
    }


    // Inserts a user-group into the database with the given name and gidnumber.
    public function createUserGroup($name, $description, $gidnumber) {
        $name = trim($name);

        $error = false;
        $message = "";
        //Verify Name
        if ( self::exists($name) ) {
            $error = true;
            $message = "A group with that name already exists.";
        }

        //If Errors, return with error messages
        if ( $error ) {
            return array(
                'RESULT' => false,
                'MESSAGE' => $message,
            );
        } //Everything looks good, add group
        else {
            // Add to LDAP
            $dn = "cn=" . $name . "," . static::$ou;
            $data = array(
                "cn" => $name,
                "objectClass" => array('posixGroup'),
                "gidNumber" => $gidnumber,
                "description" => $description,
                "memberUid" => $name,
            );
            Ldap::getInstance()->add($dn, $data);
            $this->load_by_id($name);
            Log::info("Added group " . $this->getName(), Log::GROUP_ADD, $this);
            Log::info("Added user " . $name . " to group " . $name, Log::GROUP_ADD_USER, $this, new Dummy($name));
            return array(
                'RESULT' => true,
                'MESSAGE' => 'Group successfully added.',
                'gid' => $name,
            );
        }

    }

    public function getName() {
        return $this->name;
    }


    public function getDescription() {
        return $this->description;
    }


    public function getCreator() {
        return $this->creator;
    }


    public function getCreateTime() {
        return $this->createTime;
    }


    public function getModifier() {
        return $this->modifier;
    }


    public function getModifyTime() {
        return $this->modifyTime;
    }


    public function getGidNumber() {
        return $this->gidNumber;
    }


    public function getMemberUIDs() {
        if ( $this->userUIDs == null ) {
            $filter = "(cn=" . $this->getName() . ")";
            $attributes = array('memberUid');
            $result = Ldap::getInstance()->search($filter, static::$ou, $attributes);
            if ( $result[0]['count'] == 0 ) {
                return array();
            }
            unset($result[0]['memberuid']['count']);
            $this->userUIDs = array();
            foreach ( $result[0]['memberuid'] as $row ) {
                array_push($this->userUIDs, $row);
            }
            usort($this->userUIDs, "LdapObject::username_cmp");
        }
        return $this->userUIDs;
    }

    /**
     * @return User[] array
     */
    public function getMembers() {
        if ( $this->users == null ) {
            $uids = $this->getMemberUIDs();
            $this->users = array();
            foreach ( $uids as $uid ) {
                $this->users[] = new User($uid);
            }
        }
        return $this->users;
    }


    public function addUser($username, $silent = false) {
        if ( User::exists($username) && !in_array($username, $this->getMemberUIDs()) ) {
            $dn = "cn=" . $this->getName() . "," . static::$ou;
            if ( $this->isGroupOfNames ) {
                $data = array("memberUid" => $username, "member" => "uid=" . $username . "," . __LDAP_PEOPLE_OU__);
            } else {
                $data = array("memberUid" => $username);
            }
            if ( Ldap::getInstance()->mod_add($dn, $data) ) {
                if ( !$silent ) {
                    Log::info(
                        "Added user " . $username . " to group " . $this->getName(),
                        Log::GROUP_ADD_USER,
                        $this,
                        new Dummy($username));
                }
                return array(
                    'RESULT' => true,
                    'MESSAGE' => 'User added to group.',
                    'gid' => $this->getName(),
                    'uid' => $username,
                );
            } else {
                return array(
                    'RESULT' => false,
                    'MESSAGE' => 'Failed adding user to group: LDAP error: ' . Ldap::getInstance()->get_error(),
                );
            }
        }
        return array(
            'RESULT' => false,
            'MESSAGE' => 'Failed adding user to group: invalid username or user already in group.',
        );
    }


    public function removeUser($username, $silent = false) {
        if ( in_array($username, $this->getMemberUIDs()) ) {
            $dn = "cn=" . $this->getName() . "," . static::$ou;
            if ( $this->isGroupOfNames ) {
                $data = array("memberUid" => $username, "member" => "uid=" . $username . "," . __LDAP_PEOPLE_OU__);
            } else {
                $data = array("memberUid" => $username);
            }
            if ( Ldap::getInstance()->mod_del($dn, $data) ) {
                if ( !$silent ) Log::info(
                    "Removed user " . $username . " from group " . $this->getName(),
                    Log::GROUP_REMOVE_USER,
                    $this,
                    new Dummy($username));
                return array(
                    'RESULT' => true,
                    'MESSAGE' => 'User removed from group.',
                    'gid' => $this->getName(),
                    'uid' => $username,
                );
            }
        }
    }


    public function setName($name) {
        $old_name = $this->getName();
        $dn = "cn=" . $old_name . "," . static::$ou;
        if ( Ldap::getInstance()->mod_rename($dn, "cn=" . $name) ) {
            $this->name = $name;
            $this->setId($name);
            Log::info(
                "Changed group name from $old_name to $name.",
                Log::GROUP_SET_NAME,
                $this,
                null,
                $name,
                null,
                $old_name);
            return array(
                'RESULT' => true,
                'MESSAGE' => 'Name changed',
                'gid' => $name,
            );
        }
    }


    public function setDescription($description) {
        $this->description = $description;

        if ( $this->set_desc_obj() ) {
            Log::info(
                "Changed group description for " . $this->getName() . " to '$description'",
                Log::GROUP_SET_DESC,
                $this,
                null,
                $description);
            return array(
                'RESULT' => true,
                'MESSAGE' => 'Description changed',
                'gid' => $this->getName(),
            );
        }
    }

    public function addDirectory($server, $dir) {
        $serverdir = $server . ": " . $dir;
        array_push($this->serverdirs, $serverdir);

        if ( $this->set_desc_obj() ) {
            Log::info(
                "Added server directory '$server: $dir' for " . $this->getName(),
                Log::GROUP_ADD_DIR,
                $this,
                null,
                "$server: $dir");
            return array(
                'RESULT' => true,
                'MESSAGE' => 'Server directory added',
                'gid' => $this->getName(),
            );
        }
    }

    public function removeDirectory($serverdir) {
        $serverdirs = array();
        foreach ( $this->serverdirs as $thisserverdir ) {
            if ( $serverdir != $thisserverdir ) {
                array_push($serverdirs, $thisserverdir);
            }
        }
        $this->serverdirs = $serverdirs;

        if ( $this->set_desc_obj() ) {
            Log::info(
                "Removed server directory '$serverdir' from " . $this->getName(),
                Log::GROUP_REMOVE_DIR,
                $this,
                null,
                $serverdir);
            return array(
                'RESULT' => true,
                'MESSAGE' => 'Server directory removed',
                'gid' => $this->getName(),
            );
        }
    }

    public function setOwner($owner) {
        if ( User::exists($owner) ) {
            $this->owner = $owner;
            if ( $this->set_desc_obj() ) {
                Log::info(
                    "Set owner to $owner for group " . $this->getName(),
                    Log::GROUP_SET_OWNER,
                    $this,
                    new Dummy($owner));
                return array(
                    'RESULT' => true,
                    'MESSAGE' => 'Group owner set',
                    'gid' => $this->getName(),
                );
            } else {
                return array(
                    'RESULT' => false,
                    'MESSAGE' => 'LDAP modify failed',
                    'gid' => $this->getName(),
                );
            }
        } else {
            return array(
                'RESULT' => false,
                'MESSAGE' => 'No such user',
                'gid' => $this->getName(),
            );
        }
    }

    private function set_desc_obj() {
        $dn = "cn=" . $this->getName() . "," . static::$ou;
        $descObj = array(
            'description' => $this->description,
            'directories' => $this->serverdirs,
            'owner' => $this->owner,
        );
        $data = array("description" => json_encode($descObj));
        if ( Ldap::getInstance()->modify($dn, $data) ) {
            return true;
        } else {
            return false;
        }
    }

    public function getDirectories() {
        return $this->serverdirs;
    }

    public function getOwner() {
        return $this->owner;
    }

    /**
     * @param string  $search
     * @param int     $start
     * @param int     $count
     * @param string  $sort
     * @param boolean $asc
     * @param boolean $showUsers
     * @return Group[]
     */
    public static function search(
        $search,
        $start = -1,
        $count = -1,
        $sort = "name",
        $asc = true,
        $showUsers = false
    ) {
        // TODO asc shouldnt be a string
        if ( $search == "" ) {
            $filter = "(cn=*)";
        } else {
            // This ugly str_replace brought to you by our version of php being too old to support JSON_UNESCAPED_SLASHES
            $filter = "(|(|(cn=*$search*)(description=*" . str_replace(
                    "/",
                    "\\\\/",
                    $search) . "*))(gidnumber=$search))";
        }

        if ( !$showUsers ) {
            $users = User::all();
        } else {
            $users = array();
        }

        $result = Ldap::getInstance()->search($filter, static::$ou, static::$fullAttributes);
        $groups = array();
        for ( $i = 0; $i < $result['count']; $i++ ) {
            $group = new Group();
            $group->load_from_result($result[$i]);

            if ( $showUsers || !in_array($group->getName(), $users) ) {
                $groups[] = $group;
            }
        }
        if ( count($groups) ) {

            usort($groups, static::sorter($sort, $asc));

        }

        static::$lastSearch = $groups;
        if ( $start >= 0 ) {
            $groups = array_slice($groups, $start, $count);
        }
        return $groups;
    }


    /**
     * @return array
     */
    public static function all() {
        $groups_array = array();
        $filter = "(cn=*)";
        $attributes = array('cn');
        $result = Ldap::getInstance()->search($filter, static::$ou, $attributes);
        for ( $i = 0; $i < $result['count']; $i++ ) {
            array_push($groups_array, $result[$i]['cn'][0]);
        }
        return $groups_array;
    }


    //////////////////Private Functions//////////

    public function load_from_result($result) {
        parent::load_from_result($result);
        $this->name = $result['cn'][0];

        // Attempt to parse JSON
        if ( !isset($result['description']) ) {
            $this->description = "";
            $this->owner = "";
            $this->serverdirs = array();
        } else {
            $descJson = json_decode($result['description'][0]);
            if ( $descJson == null ) {
                $this->description = $result['description'][0];
                $this->owner = '';
            } else {
                $this->description = isset($descJson->description) ? $descJson->description : "";
                $this->serverdirs = isset($descJson->directories) ? $descJson->directories : array();
                sort($this->serverdirs);
                $this->owner = isset($descJson->owner) ? $descJson->owner : "";
            }
        }

        if ( isset($result['creatorsname']) ) {
            if ( preg_match("/uid=(.*?),/um", $result['creatorsname'][0], $matches) ) {
                $this->creator = $matches[1];
            } else {
                $this->creator = $result['creatorsname'][0];
            }
        }
        if ( isset($result['createtimestamp']) ) $this->createTime = strtotime($result['createtimestamp'][0]);
        if ( isset($result['modifiersname']) ) {
            if ( preg_match("/uid=(.*?),/um", $result['modifiersname'][0], $matches) ) {
                $this->modifier = $matches[1];
            } else {
                $this->modifier = $result['modifiersname'][0];
            }
        }
        if ( isset($result['modifytimestamp']) ) $this->modifyTime = strtotime($result['modifytimestamp'][0]);

        if ( isset($result['gidnumber']) ) $this->gidNumber = $result['gidnumber'][0];
        if ( isset($result['objectclass']) ) {
            if ( in_array('groupOfNames', $result['objectclass']) ) {
                $this->isGroupOfNames = true;
            }
        }
    }

}
