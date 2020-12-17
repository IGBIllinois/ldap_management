<?php

class Host extends LdapObject
{
    protected static $idField = "cn";
    protected static $ou = __LDAP_HOST_OU__;
    ////////////////Private Variables//////////

    private $userUIDs = null;
    private $users = null;
    private $name;
    private $ip;

    private $creator;
    private $createTime;
    private $modifier;
    private $modifyTime;

    ////////////////Public Functions///////////

    public function __construct($name = "")
    {
        if ($name != "") {
            $this->load_by_id($name);
        }
    }


    public function __destruct()
    {
    }


    // Inserts a host into the database with the given name, then loads that host into this object. Displays errors if there are any.
    public function create($name, $ip)
    {
        $name = trim($name);

        $error = false;
        $message = "";
        //Verify Name
        if (self::exists($name)) {
            $error = true;
            $message = "A host with that name already exists.";
        }

        //If Errors, return with error messages
        if ($error) {
            return [
                'RESULT' => false,
                'MESSAGE' => $message,
            ];
        } //Everything looks good, add host
        else {
            // Add to LDAP
            $dn = "cn=" . $name . "," . static::$ou;
            $data = ["cn" => $name, "objectClass" => ['device', 'ipHost'], 'ipHostNumber' => $ip];
            Ldap::getInstance()->add($dn, $data);

            $this->load_by_id($name);

            Log::info(
                "Added host " . $this->getName(),
                Log::HOST_ADD,
                $this
            );
            return [
                'RESULT' => true,
                'MESSAGE' => 'Host successfully added.',
                'hid' => $name,
            ];
        }
    }

    public function remove()
    {
        $dn = $this->getRDN();
        if (Ldap::getInstance()->remove($dn)) {
            Log::info(
                "Removed host " . $this->getName(),
                Log::HOST_REMOVE,
                $this
            );

            return [
                'RESULT' => true,
                'MESSAGE' => 'Host removed.',
                'gid' => $this->name,
            ];
        }
    }

    public function getName()
    {
        return $this->name;
    }


    public function getIp()
    {
        return $this->ip;
    }

    public function getCreator()
    {
        return $this->creator;
    }

    public function getCreateTime()
    {
        return $this->createTime;
    }

    public function getModifier()
    {
        return $this->modifier;
    }

    public function getModifyTime()
    {
        return $this->modifyTime;
    }

    public function getUserUIDs()
    {
        if ($this->userUIDs == null) {
            $filter = "(host=" . $this->getName() . ")";
            $attributes = ['uid'];
            $result = Ldap::getInstance()->search($filter, __LDAP_PEOPLE_OU__, $attributes);
            unset($result['count']);
            $this->userUIDs = [];
            foreach ($result as $row) {
                array_push($this->userUIDs, $row['uid'][0]);
            }
            usort($this->userUIDs, "LdapObject::username_cmp");
        }
        return $this->userUIDs;
    }

    public function getUsers()
    {
        if ($this->users == null) {
            $uids = $this->getUserUIDs();
            $this->users = [];
            foreach ($uids as $uid) {
                $this->users[] = new User($uid);
            }
        }
        return $this->users;
    }

    public function setName($name)
    {
        $old_name = $this->getName();
        $dn = "cn=" . $old_name . "," . static::$ou;
        if (Ldap::getInstance()->mod_rename($dn, "cn=" . $name)) {
            $users = $this->getUserUIDs();
            $this->name = $name;
            $this->setId($name);
            Log::info(
                "Changed host name from $old_name to $name.",
                Log::HOST_SET_NAME,
                $this,
                null,
                $name,
                null,
                $old_name
            );

            foreach ($users as $username) {
                $user = new User($username);
                $user->removeHost($old_name, true);
                $user->addHost($name, true);
            }
            return [
                'RESULT' => true,
                'MESSAGE' => 'Name changed',
                'hid' => $name,
            ];
        }
    }

    public function setIp($ip)
    {
        $dn = "cn=" . $this->getName() . "," . static::$ou;
        $data = ["ipHostNumber" => $ip];
        if (Ldap::getInstance()->modify($dn, $data)) {
            Log::info(
                "Changed host ip for " . $this->getName() . " to '$ip'",
                Log::HOST_SET_IP,
                $this,
                null,
                $ip
            );
            $this->ip = $ip;
            return [
                'RESULT' => true,
                'MESSAGE' => 'IP changed',
                'hid' => $this->getName(),
            ];
        }
    }

    public static function all()
    {
        // TODO decide whether these things should return objects or ids
        $filter = "(cn=*)";
        $result = Ldap::getInstance()->search($filter, static::$ou, self::$fullAttributes);
        $hosts = [];
        for ($i = 0; $i < $result['count']; $i++) {
            $host = new Host();
            $host->load_from_result($result[$i]);
            $hosts[] = $host;
        }
        usort($hosts, static::sorter('name'));
        return $hosts;
    }

    //////////////////Private Functions//////////
    protected function load_from_result($result)
    {
        parent::load_from_result($result);
        $this->name = $result['cn'][0];
        $this->ip = $result['iphostnumber'][0];

        if (preg_match("/uid=(.*?),/um", $result['creatorsname'][0], $matches)) {
            $this->creator = $matches[1];
        } else {
            $this->creator = $result['creatorsname'][0];
        }
        $this->createTime = strtotime($result['createtimestamp'][0]);
        if (preg_match("/uid=(.*?),/um", $result['modifiersname'][0], $matches)) {
            $this->modifier = $matches[1];
        } else {
            $this->modifier = $result['modifiersname'][0];
        }
        $this->modifyTime = strtotime($result['modifytimestamp'][0]);
    }
}
