<?php

use Hackzilla\PasswordGenerator\Exception\FileNotFoundException;
use Hackzilla\PasswordGenerator\Exception\ImpossiblePasswordLengthException;
use Hackzilla\PasswordGenerator\Exception\WordsNotFoundException;
use Hackzilla\PasswordGenerator\Generator\ExtendedHumanPasswordGenerator;
use Hackzilla\PasswordGenerator\Generator\HumanPasswordGenerator;

class User extends LdapObject
{
    protected static $ou = __LDAP_PEOPLE_OU__;
    ////////////////Private Variables//////////

    private $username;
    private $name;

    private $uidnumber;
    private $email;
    private $emailforward;
    private $homeDirectory;
    private $homeSubfolder;
    private $givenName;
    private $sn;
    private $hosts = null;
    private $groups = null;
    private $loginShell;
    private $expiration = null;
    private $leftCampus = false;
    private $nonCampus = false;
    private $crashplan = false;
    private $classroom = false;

    private $description = "";
    private $expirationReason = "";

    private $creator;
    private $createTime;
    private $modifier;
    private $modifyTime;
    private $lastLogin;
    private $passwordSet = null;
    private $passwordExpiration = null;

    ////////////////Public Functions///////////

    public function __construct($username = "") {
        if ( $username != "" ) {
            $this->load_by_id($username);
        }
    }


    /**
     * Inserts a user into the database with the given values, then loads that user into this object. Displays errors
     * if there are any.
     * @param $username
     * @param $firstname
     * @param $lastname
     * @param $password
     * @return LdapStatus
     */
    public function create($username, $firstname, $lastname, $password) {
        $username = trim($username);
        $firstname = trim($firstname);
        $lastname = trim($lastname);
        $name = $firstname . " " . $lastname;

        $error = false;
        $message = "";
        //Verify Username
        if ( $username == "" ) {
            $error = true;
            $message = "Please enter a username.";
        } else if ( User::exists($username) ) {
            $error = true;
            $message = "User already exists.";
        } else if ( Group::exists($username) ) {
            $error = true;
            $message = "Username already exists as group";
        }
        if ( $firstname == "" ) {
            $error = true;
            $message .= "Please enter a first name.";
        }
        if ( $lastname == "" ) {
            $error = true;
            $message .= "Please enter a last name.";
        }
        if ( $password == "" ) {
            $error = true;
            $message .= "Please enter a password.";
        }

        //If Errors, return with error messages
        if ( $error ) {
            return new LdapStatus(false, $message);
        } //Everything looks good, add user
        else {
            // Get all users' uidnumber, gidnumber
            $users = Ldap::getInstance()->search(
                "(!(uid=ftp_*))",
                static::$ou,
                array(
                    'uid',
                    'uidnumber',
                    'gidnumber',
                ));
            $uidnumbers = array();
            $gidnumbers = array();
            for ( $i = 0; $i < $users['count']; $i++ ) {
                if ( isset($users[$i]['uidnumber']) ) {
                    $uidnumbers[] = $users[$i]['uidnumber'][0];
                    $gidnumbers[] = $users[$i]['gidnumber'][0];
                }
            }
            // Get all groups' gidnumber
            $groups = Ldap::getInstance()->search("(cn=*)", __LDAP_GROUP_OU__, array('cn', 'gidnumber'));
            $groupgidnumbers = array();
            for ( $i = 0; $i < $groups['count']; $i++ ) {
                if ( isset($users[$i]['gidnumber']) ) {
                    $groupgidnumbers[] = $groups[$i]['gidnumber'][0];
                }
            }
            // Find the max uidnumber already in use (at least 1000)
            $uidstart = 1000;
            $uidnumber = max($uidstart, max($uidnumbers), max($gidnumbers)) + 1;
            // Now start there and look for an empty slot in gidnumbers (which will probably be right away)
            while ( in_array($uidnumber, $groupgidnumbers) ) {
                $uidnumber++;
            }
            // gidnumber and uidnumber should match
            $gidnumber = $uidnumber;

            $passwd = "";

            if ( __PASSWD_HASH__ == "SSHA" ) {
                $passwd = self::SSHAHash($password);
            }

            $ntpasswd = self::NTLMHash($password);
            $lmpasswd = self::LMHash($password);

            if ( $username < 'n' ) {
                $homesub = 'a-m';
            } else {
                $homesub = 'n-z';
            }

            // Add LDAP user
            $dn = "uid=" . $username . "," . static::$ou;
            $data = array(
                'uid' => $username,
                'objectClass' => array(
                    'inetOrgPerson',
                    'posixAccount',
                    'shadowAccount',
                    'sambaSamAccount',
                    'account',
                ),
                'cn' => $name,
                'sn' => $lastname,
                'givenName' => $firstname,
                'mail' => $username . __MAIL_SUFFIX__,
                'userPassword' => $passwd,
                'loginShell' => __DEFAULT_SHELL__,
                'uidNumber' => $uidnumber,
                'gidNumber' => $gidnumber,
                'homeDirectory' => __HOME_DIR__ . "/" . $homesub . "/" . $username,
                'initials' => $homesub,
                'gecos' => $name,
                'sambaSID' => __SAMBA_ID__ . "-" . $uidnumber,
                'sambaLMPassword' => $lmpasswd,
                'sambaNTPassword' => $ntpasswd,
                'SambaPwdLastSet' => time(),
                'facsimiletelephonenumber' => time() + 60 * 60 * 24 * 365,
            );
            if ( !Ldap::getInstance()->add($dn, $data) ) {
                return new LdapStatus(false, 'LDAP error when adding user: ' . Ldap::getInstance()->get_error());
            }
            $this->load_by_id($username);
            Log::info(
                "Added user " . $this->getUsername() . " (" . $this->getName() . ")",
                Log::USER_ADD,
                $this);

            // Add LDAP group
            $group = new Group();
            $group->createUserGroup($username, $username, $gidnumber);

            return new LdapStatus(true, 'User successfully added.', $this);
        }

    }

    public function remove() {
        $dn = $this->getRDN();
        if ( Ldap::getInstance()->remove($dn) ) {
            if ( __RUN_SHELL_SCRIPTS__ ) {
                $safeusername = escapeshellarg($this->getUsername());
                exec("sudo ../bin/remove_user.pl $safeusername");
            }
            // remove user group
            $group = new Group($this->getUsername());
            $group->remove();
            // remove user from groups
            $groups = $this->getGroups();
            for ( $i = 0; $i < count($groups); $i++ ) {
                $group = new Group($groups[$i]);
                $group->removeUser($this->username);
            }

            Log::info(
                "Removed user " . $this->getUsername(),
                Log::USER_REMOVE,
                $this);
            return array('RESULT' => true, 'MESSAGE' => 'User deleted.', 'uid' => $this->username);
        }
    }

    public function getUsername() {
        return $this->username;
    }

    public function getEmail() {
        return $this->email;
    }

    public function getForwardingEmail() {
        return $this->emailforward;
    }

    public function setForwardingEmail($emailforward) {
        $dn = "uid=" . $this->getUsername() . "," . static::$ou;
        $data = array("postalAddress" => $emailforward);
        if ( Ldap::getInstance()->modify($dn, $data) ) {
            $this->emailforward = $emailforward;
            Log::info(
                "Set email forwarding for " . $this->getUsername() . " to " . $emailforward,
                Log::USER_SET_FORWARD,
                $this,
                null,
                $emailforward);
            return array('RESULT' => true, 'MESSAGE' => 'Email forwarding set', 'uid' => $this->getUsername());
        }
    }

    public function getCrashplan() {
        return $this->crashplan;
    }

    public function setCrashplan($crashplan) {
        $value = 0;
        if ( $crashplan ) {
            $value = 1;
        }
        $dn = "uid=" . $this->getUsername() . "," . static::$ou;
        $data = array("telexNumber" => $value);
        if ( Ldap::getInstance()->modify($dn, $data) ) {
            $this->crashplan = $value;
            Log::info(
                "Set crashplan for " . $this->getUsername() . " to " . ($crashplan ? 'active' : 'inactive'),
                Log::USER_SET_CRASHPLAN,
                $this,
                null,
                $value);
            return array('RESULT' => true, 'MESSAGE' => 'Crashplan set', 'uid' => $this->getUsername());
        }
    }

    public function getLoginShell() {
        return $this->loginShell;
    }

    public function setLoginShell($shell) {
        $dn = $this->getRDN();
        $data = array("loginShell" => $shell);
        if ( Ldap::getInstance()->modify($dn, $data) ) {
            Log::info(
                "Set login shell for " . $this->getUsername() . " to " . $shell,
                Log::USER_SET_LOGIN,
                $this,
                null,
                $shell);
            return array('RESULT' => true, 'MESSAGE' => 'Login shell changed.', 'uid' => $this->username);
        } else {
            return array(
                'RESULT' => false,
                'MESSAGE' => 'LDAP Error: ' . Ldap::getInstance()->get_error(),
                'uid' => $this->username,
            );
        }
    }

    public function getHomeDirectory() {
        return $this->homeDirectory;
    }

    public function getHomeSubFolder() {
        return $this->homeSubfolder;
    }

    public function setHomeSubFolder($subfolder) {
        $dn = $this->getRDN();
        $data = array("initials" => $subfolder);
        if ( Ldap::getInstance()->modify($dn, $data) ) {
            Log::info(
                "Set home subfolder for " . $this->getUsername() . " to " . $subfolder,
                Log::USER_SET_SUBFOLDER,
                $this,
                null,
                $subfolder);
            return array('RESULT' => true, 'MESSAGE' => 'Home Subfolder changed.', 'uid' => $this->username);
        } else {
            return array(
                'RESULT' => false,
                'MESSAGE' => 'LDAP Error: ' . Ldap::getInstance()->get_error(),
                'uid' => $this->username,
            );
        }
    }

    public function getName() {
        return $this->name;
    }

    public function getFirstName() {
        return $this->givenName;
    }

    public function getLastName() {
        return $this->sn;
    }

    public function getExpiration() {
        return $this->expiration;
    }

    public function getPasswordExpiration() {
        return $this->passwordExpiration;
    }

    public function isExpired() {
        return ($this->expiration != null && $this->expiration <= time());
    }

    public function isExpiring() {
        return ($this->expiration != null && $this->expiration > time());
    }

    public function isPasswordExpired() {
        return ($this->passwordExpiration != null && $this->passwordExpiration <= time());
    }

    public function getLastLogin() {
        return $this->lastLogin;
    }

    public function getUidNumber() {
        return $this->uidnumber;
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

    public function getPasswordLastSet() {
        return $this->passwordSet;
    }

    /**
     * @return array
     */
    public function getHosts() {
        if ( $this->hosts == null ) {
            $filter = "(uid=" . $this->getUsername() . ")";
            $attributes = array('Host');
            $result = Ldap::getInstance()->search($filter, "", $attributes);
            if ( $result['count'] && $result[0]["count"] ) {
                $this->hosts = $result[0]["host"];
                unset($this->hosts['count']);
                sort($this->hosts);
            } else {
                $this->hosts = array();
            }
        }
        return $this->hosts;
    }


    public function getGroups() {
        if ( $this->groups == null ) {
            $filter = "(&(cn=*)(memberUid=" . $this->getUsername() . "))";
            $attributes = array('cn');
            $result = Ldap::getInstance()->search($filter, __LDAP_GROUP_OU__, $attributes);
            unset($result['count']);
            $this->groups = array();
            foreach ( $result as $row ) {
                array_push($this->groups, $row['cn'][0]);
            }
            sort($this->groups);
        }
        return $this->groups;
    }


    public function addHost($host, $silent = false) {
        if ( Host::exists($host) && (!$this->getHosts() || !in_array($host, $this->getHosts())) ) {
            $dn = "uid=" . $this->getUsername() . "," . static::$ou;
            $filter = "(&(uid=" . $this->getUsername() . ")(objectClass=account))";
            $result = Ldap::getInstance()->search($filter, static::$ou, array());

            if ( $result['count'] == 0 ) {
                $data = array("objectClass" => 'account');
                Ldap::getInstance()->mod_add($dn, $data);
            }

            $data = array("host" => $host);
            if ( Ldap::getInstance()->mod_add($dn, $data) ) {
                if ( !$silent ) {
                    Log::info(
                        "Gave host access for " . $host . " to " . $this->getUsername(),
                        Log::USER_ADD_HOST,
                        $this,
                        new Dummy($host));
                }
                return array(
                    'RESULT' => true,
                    'MESSAGE' => 'Machine rights successfully added.',
                    'uid' => $this->getUsername(),
                );
            } else {
                return array('RESULT' => false, 'MESSAGE' => 'Error: ' . Ldap::getInstance()->get_error());
            }
        }
    }

    public function removeHost($host, $silent = false) {
        if ( Host::exists($host) || ($this->getHosts() && in_array($host, $this->getHosts())) ) {
            $dn = "uid=" . $this->getUsername() . "," . static::$ou;
            $data = array("host" => $host);
            if ( @Ldap::getInstance()->mod_del($dn, $data) ) {
                if ( !$silent ) {
                    Log::info(
                        "Removed host access to " . $host . " from " . $this->getUsername(),
                        Log::USER_REMOVE_HOST,
                        $this,
                        new Dummy($host));
                }
                return array(
                    'RESULT' => true,
                    'MESSAGE' => 'Machine rights successfully removed.',
                    'uid' => $this->getUsername(),
                );
            }
        }
    }

    public function setName($firstname, $lastname) {
        $dn = $this->getRDN();
        $name = $firstname . " " . $lastname;
        $data = array("cn" => $name, "sn" => $lastname, "givenName" => $firstname, "gecos" => $name);
        if ( Ldap::getInstance()->modify($dn, $data) ) {
            Log::info(
                "Changed name for " . $this->getUsername() . " to \"$name\"",
                Log::USER_SET_NAME,
                $this,
                null,
                $name);
            return array('RESULT' => true, 'MESSAGE' => 'Name successfully changed.', 'uid' => $this->getUsername());
        }
    }

    public function setUsername($username) {
        $dn = $this->getRDN();
        $old_username = $this->getUsername();
        if ( $username < 'n' ) {
            $homesub = 'a-m';
        } else {
            $homesub = 'n-z';
        }
        $groups = $this->getGroups();

        // Change dn of user
        $data = array("mail" => $username . __MAIL_SUFFIX__, "homeDirectory" => "/home/" . $homesub . "/" . $username);
        if ( Ldap::getInstance()->mod_rename($dn, "uid=" . $username) ) {
            $this->username = $username;
            $this->setId($username);
            $dn = $this->getRDN();
            Ldap::getInstance()->modify($dn, $data);
            Log::info(
                "Changed username for $old_username to $username.",
                Log::USER_SET_USERNAME,
                $this,
                null,
                $username,
                null,
                $old_username);
        }

        $this->setHomeSubFolder($homesub);

        // Change username in groups user is a member of
        for ( $i = 0; $i < count($groups); $i++ ) {
            $group = new Group($groups[$i]);
            $group->removeUser($old_username, true);
            $group->addUser($username, true);
        }

        // Change name of user group
        $group = new Group($old_username);
        $group->setName($username);
        $group->setDescription($username);

        // Change username on file-server, mail
        if ( __RUN_SHELL_SCRIPTS__ ) {
            $safeusername = escapeshellarg($old_username);
            $safenewusername = escapeshellarg($username);
            exec("sudo ../bin/change_username.pl $safeusername $safenewusername");
        }

        return array('RESULT' => true, 'MESSAGE' => 'Username changed.', 'uid' => $username);
    }

    public function setPassword($password) {
        $passwd = "";

        if ( __PASSWD_HASH__ == "SSHA" ) {
            $passwd = self::SSHAHash($password);
        }

        $ntpasswd = self::NTLMHash($password);
        $lmpasswd = self::LMHash($password);

        $dn = $this->getRDN();
        $data = array(
            'userPassword' => $passwd,
            'sambaLMPassword' => $lmpasswd,
            'sambaNTPassword' => $ntpasswd,
            'sambaPwdLastSet' => time(),
        );
        if ( $this->getPasswordExpiration() != null ) {
            // If user is not exempt, set password expiration date to one year hence
            $data['facsimiletelephonenumber'] = time() + 60 * 60 * 24 * 365;
        }
        if ( Ldap::getInstance()->modify($dn, $data) ) {
            Log::info(
                "Changed password for " . $this->getUsername(),
                Log::USER_SET_PASS,
                $this);
            return array('RESULT' => true, 'MESSAGE' => 'Password successfully set.', 'uid' => $this->getUsername());
        } else {
            return array('RESULT' => false, 'MESSAGE' => 'Set Password Failed: ' . Ldap::getInstance()->get_error());
        }
    }

    public function lock() {
        $filter = "(uid=" . $this->getUsername() . ")";
        $attributes = array("userPassword", "sambaLMPassword", "sambaNTPassword");
        $result = Ldap::getInstance()->search($filter, static::$ou, $attributes);
        if ( $result['count'] > 0 ) {
            $dn = $this->getRDN();
            $data = array(
                'userPassword' => '!' . $result[0]['userpassword'][0],
                'sambaLMPassword' => '!' . $result[0]['sambalmpassword'][0],
                'sambaNTPassword' => '!' . $result[0]['sambantpassword'][0],
            );
            if ( Ldap::getInstance()->modify($dn, $data) ) {
                Log::info(
                    "User " . $this->getUsername() . " locked",
                    Log::USER_LOCK,
                    $this);
                return array('RESULT' => true, 'MESSAGE' => 'User locked.', 'uid' => $this->getUsername());
            }
        }
        return array('RESULT' => false, 'MESSAGE' => 'Lock failed: ' . Ldap::getInstance()->get_error());
    }

    public function unlock() {
        $filter = "(uid=" . $this->getUsername() . ")";
        $attributes = array("userPassword", "sambaLMPassword", "sambaNTPassword");
        $result = Ldap::getInstance()->search($filter, static::$ou, $attributes);
        if ( $result['count'] > 0 ) {
            if ( substr($result[0]['userpassword'][0], 0, 1) == '!' ) {
                if ( substr(
                        $result[0]['sambalmpassword'][0],
                        0,
                        1) == '!' ) { // If the user was locked before this change, their samba password won't have been locked
                    $dn = $this->getRDN();
                    $data = array(
                        'userPassword' => substr($result[0]['userpassword'][0], 1),
                        'sambaLMPassword' => substr($result[0]['sambalmpassword'][0], 1),
                        'sambaNTPassword' => substr($result[0]['sambantpassword'][0], 1),
                    );
                    if ( Ldap::getInstance()->modify($dn, $data) ) {
                        Log::info(
                            "User " . $this->getUsername() . " unlocked",
                            Log::USER_UNLOCK,
                            $this);
                        return array('RESULT' => true, 'MESSAGE' => 'User unlocked.', 'uid' => $this->getUsername());
                    }
                } else {
                    $dn = $this->getRDN();
                    $data = array('userPassword' => substr($result[0]['userpassword'][0], 1));
                    if ( Ldap::getInstance()->modify($dn, $data) ) {
                        Log::info(
                            "User " . $this->getUsername() . " unlocked",
                            Log::USER_UNLOCK,
                            $this);
                        return array('RESULT' => true, 'MESSAGE' => 'User unlocked.', 'uid' => $this->getUsername());
                    }
                }

            }
        }
        return array('RESULT' => false, 'MESSAGE' => 'Unlock failed: ' . Ldap::getInstance()->get_error());
    }

    public function isLocked() {
        $filter = "(uid=" . $this->getUsername() . ")";
        $attributes = array("userPassword");
        $result = Ldap::getInstance()->search($filter, static::$ou, $attributes);
        if ( $result['count'] > 0 ) {
            if ( isset($result[0]['userpassword']) && substr($result[0]['userpassword'][0], 0, 1) == '!' ) {
                return true;
            } else {
                return false;
            }
        }
        return false;
    }

    public function setExpiration($expiration, $reason = "") {
        $dn = "uid=" . $this->getUsername() . "," . static::$ou;
        $data = array("shadowExpire" => $expiration);
        if ( Ldap::getInstance()->modify($dn, $data) ) {
            $this->expiration = $expiration;
            if ( $reason !== "" ) {
                $this->setExpirationReason($reason);
            }
            Log::info(
                "Set expiration for " . $this->getUsername() . " to " . strftime(
                    '%m/%d/%Y',
                    $this->getExpiration()),
                Log::USER_SET_EXP,
                $this,
                null,
                null,
                strftime(
                    '%Y-%m-%d %H:%M:%S',
                    $this->getExpiration()));
            return array('RESULT' => true, 'MESSAGE' => 'Expiration successfully set.', 'uid' => $this->getUsername());
        }
    }

    public function removeExpiration() {
        $dn = $this->getRDN();
        $data = array("shadowexpire" => array());
        if ( Ldap::getInstance()->mod_del($dn, $data) ) {
            Log::info(
                "Cancelled expiration for user " . $this->getUsername(),
                Log::USER_REMOVE_EXP,
                $this);
            return array('RESULT' => true, 'MESSAGE' => 'Expiration cancelled.', 'uid' => $this->getUsername());
        }
    }

    public function getExpirationReason() {
        return $this->expirationReason;
    }

    public function setExpirationReason($reason) {
        $dn = "uid=" . $this->getUsername() . "," . static::$ou;
        if ( $reason === "" ) { // Delete
            $data = array("destinationindicator" => array());
            if ( Ldap::getInstance()->mod_del($dn, $data) ) {
                $this->expirationReason = "";
                Log::info(
                    "Removed expiration reason for " . $this->getUsername(),
                    Log::USER_REMOVE_EXP_REASON,
                    $this);
                return array(
                    'RESULT' => true,
                    'MESSAGE' => 'Removed expiration reason',
                    'uid' => $this->getUsername(),
                );
            }
        } else { // Update
            $data = array("destinationindicator" => $reason);
            if ( Ldap::getInstance()->modify($dn, $data) ) {
                $this->expirationReason = $reason;
                Log::info(
                    "Set expiration reason for " . $this->getUsername() . " to '" . $this->getExpirationReason() . "'",
                    Log::USER_SET_EXP_REASON,
                    $this,
                    null,
                    $reason);
                return array(
                    'RESULT' => true,
                    'MESSAGE' => 'Expiration reason successfully set.',
                    'uid' => $this->getUsername(),
                );
            }
        }
    }

    public function setPasswordExpiration($expiration) {
        $dn = "uid=" . $this->getUsername() . "," . static::$ou;
        $data = array("facsimiletelephonenumber" => $expiration);
        if ( Ldap::getInstance()->modify($dn, $data) ) {
            $this->passwordExpiration = $expiration;
            Log::info(
                "Set password expiration for " . $this->getUsername() . " to " . strftime(
                    '%m/%d/%Y',
                    $this->getPasswordExpiration()),
                Log::USER_SET_PASS_EXP,
                $this,
                null,
                null,
                strftime(
                    '%Y-%m-%d %H:%M:%S',
                    $this->getPasswordExpiration()));
            return array(
                'RESULT' => true,
                'MESSAGE' => 'Password expiration successfully set.',
                'uid' => $this->getUsername(),
            );
        }
    }

    public function removePasswordExpiration() {
        $dn = $this->getRDN();
        $data = array("facsimiletelephonenumber" => array());
        if ( Ldap::getInstance()->mod_del($dn, $data) ) {
            Log::info(
                "Cancelled password expiration for user " . $this->getUsername(),
                Log::USER_REMOVE_PASS_EXP,
                $this);
            return array(
                'RESULT' => true,
                'MESSAGE' => 'Password expiration cancelled.',
                'uid' => $this->getUsername(),
            );
        }
    }

    public function getDescription() {
        return $this->description;
    }

    public function setDescription($description) {
        if ( $this->getDescription() != $description ) {
            $dn = "uid=" . $this->getUsername() . "," . static::$ou;
            if ( $description == "" ) { // Delete
                $data = array("description" => array());
                if ( Ldap::getInstance()->mod_del($dn, $data) ) {
                    $this->description = "";
                    Log::info(
                        "Removed description for " . $this->getUsername(),
                        Log::USER_REMOVE_DESC,
                        $this);
                    return array('RESULT' => true, 'MESSAGE' => 'Removed description', 'uid' => $this->getUsername(),);
                }
            } else { // Update
                $data = array("description" => $description);
                if ( Ldap::getInstance()->modify($dn, $data) ) {
                    $this->description = $description;
                    Log::info(
                        "Set description for " . $this->getUsername() . " to " . $this->getDescription(),
                        Log::USER_SET_DESC,
                        $this,
                        null,
                        $description);
                    return array(
                        'RESULT' => true,
                        'MESSAGE' => 'Description successfully set.',
                        'uid' => $this->getUsername(),
                    );
                }
            }
        }
    }

    public function authenticate($password) {
        $rdn = $this->getRDN();
        if ( Ldap::getInstance()->bind($rdn, $password) ) {
            if ( User::exists($this->username) ) {
                $in_admin_group = Ldap::getInstance()->search(
                    "(memberuid=" . $this->username . ")",
                    __LDAP_ADMIN_GROUP__);
                if ( $in_admin_group['count'] > 0 ) {
                    return 0;
                } else {
                    return 3;
                }
            } else {
                return 2;
            }
        } else {
// 			echo Ldap::getInstance()->get_error();
            return 1;
        }
    }

    public static function generatePassword($length = 8) {
        $generator = new ExtendedHumanPasswordGenerator();
        try {
            $generator->setWordList('../conf/google-10000-english/google-10000-english-no-swears.txt')
                      ->setWordCount(4)
                      ->setWordSeparator('-')
                      ->setOptionValue(HumanPasswordGenerator::OPTION_MIN_WORD_LENGTH, 4)
                      ->setOptionValue(HumanPasswordGenerator::OPTION_MAX_WORD_LENGTH, 8)
                      ->setOptionValue(ExtendedHumanPasswordGenerator::OPTION_REQUIRE_UPPERCASE, true);
        } catch (FileNotFoundException $e) {
            // Fall back to random password
            return static::randomPassword();
        }

        try {

            return $generator->generatePassword();
        } catch (ImpossiblePasswordLengthException $e) {
            // Fall back to random password
            return static::randomPassword();
        } catch (WordsNotFoundException $e) {
            // Fall back to random password
            return static::randomPassword();
        }
    }

    public static function randomPassword($length = 8) {
        $passwordChars = 'abcdefghijkmnopqrstuvwxyzABCDEFGHJKLMNPQRSTUVWXYZ23456789!@$%&';
        do {
            $password = "";
            for ( $i = 0; $i < $length; $i++ ) {
                $password .= $passwordChars[self::openssl_rand(0, strlen($passwordChars) - 1)];
            }
        } while ( !(preg_match("/[A-Z]/u", $password) && preg_match("/[a-z]/u", $password) && preg_match(
                "/[^A-Za-z]/u",
                $password)) );
        return $password;
    }

    public static function all() {
        $users_array = array();
        $filter = "(uid=*)";
        $attributes = array('uid');
        $result = Ldap::getInstance()->search($filter, static::$ou, $attributes);
        for ( $i = 0; $i < $result['count']; $i++ ) {
            array_push($users_array, $result[$i]['uid'][0]);
        }
        usort($users_array, 'LdapObject::username_cmp');
        return $users_array;
    }

    public static function search(
        $search,
        $start = 0,
        $count = 30,
        $sort = "username",
        $asc = true,
        $userfilter = 'none',
        $passwordSet = null
    ) {
        if ( $search == "" ) {
            $filter = "(uid=*)";
        } else {
            $filter = "(|(|(uid=*$search*)(cn=*$search*))(uidnumber=$search))";
        }
        $result = Ldap::getInstance()->search($filter, static::$ou, self::$fullAttributes);
        $users = array();
        for ( $i = 0; $i < $result['count']; $i++ ) {
            $user = new User();
            $user->load_from_result($result[$i]);

            if ( $passwordSet === null || strftime("%Y%m%d", $user->getPasswordLastSet()) === strftime(
                    "%Y%m%d",
                    strtotime($passwordSet)) ) {
                if ( $userfilter != 'none' ) {
                    if ( $userfilter == 'expiring' ) {
                        if ( $user->isExpiring() && !$user->isClassroom() ) {
                            $users[] = $user;
                        }
                    } else if ( $userfilter == 'expired' ) {
                        if ( $user->isExpired() && !$user->isClassroom() ) {
                            $users[] = $user;
                        }
                    } else if ( $userfilter == 'left' ) {
                        if ( $user->getLeftCampus() ) {
                            $users[] = $user;
                        }
                    } else if ( $userfilter == 'noncampus' ) {
                        if ( $user->getNonCampus() ) {
                            $users[] = $user;
                        }
                    } else if ( $userfilter == 'classroom' ) {
                        if ( $user->isClassroom() ) {
                            $users[] = $user;
                        }
                    } else if ( $userfilter == 'passwordexpired' ) {
                        if ( $user->isPasswordExpired() ) {
                            $users[] = $user;
                        }
                    } else {
                        $users[] = $user;
                    }
                } else {
                    $users[] = $user;
                }
            }
        }

        usort($users, static::sorter($sort, $asc));
        static::$lastSearch = $users;
        return array_slice($users, $start, $count);
    }


    public static function isInAD($username) {
        if ( $username == "" ) {
            return false;
        }
        $adldap = new Ldap(__AD_LDAP_HOST__, __AD_LDAP_SSL__, __AD_LDAP_PORT__, __AD_LDAP_BASE_DN__, __AD_LDAP_TLS__);
        $adldap->set_bind_user(__AD_LDAP_BIND_USER__);
        $adldap->set_bind_pass(__AD_LDAP_BIND_PASS__);

        $filter = "(&(cn=UIUC Campus Accounts)(member=CN=" . $username . "," . __AD_LDAP_PEOPLE_OU__ . "))";

        $attributes = array('dn');
        $results = $adldap->search($filter, __AD_LDAP_GROUP_OU__, $attributes);
        if ( $results && $results['count'] > 0 ) {
            return true;
        } else {
            return false;
        }
    }

    public function getLeftCampus() {
        return $this->leftCampus;
    }

    public function setLeftCampus($leftCampus) {
        $dn = "uid=" . $this->getUsername() . "," . static::$ou;
        $data = array("employeetype" => ($leftCampus ? 'leftcampus' : array()));
        if ( Ldap::getInstance()->modify($dn, $data) ) {
            $this->leftCampus = $leftCampus;
            Log::info(
                "Set left-campus for " . $this->getUsername() . " to " . ($this->getLeftCampus() ? "1" : "0"),
                Log::USER_SET_LEFT,
                $this,
                null,
                $leftCampus);
            return array('RESULT' => true, 'MESSAGE' => 'Leftcampus successfully set.', 'uid' => $this->getUsername());
        } else {
            return array(
                'RESULT' => false,
                'MESSAGE' => 'LDAP error when setting leftcampus: ' . Ldap::getInstance()->get_error(),
            );
        }
    }

    public function getNonCampus() {
        return $this->nonCampus;
    }

    public function setNonCampus($nonCampus) {
        $dn = "uid=" . $this->getUsername() . "," . static::$ou;
        $data = array("employeetype" => ($nonCampus ? 'noncampus' : array()));
        if ( Ldap::getInstance()->modify($dn, $data) ) {
            $this->nonCampus = $nonCampus;
            Log::info(
                "Set non-campus for " . $this->getUsername() . " to " . ($this->getNonCampus() ? "1" : "0"),
                Log::USER_SET_NONCAMPUS,
                $this,
                null,
                $nonCampus);
            return array('RESULT' => true, 'MESSAGE' => 'Noncampus successfully set.', 'uid' => $this->getUsername());
        } else {
            return array(
                'RESULT' => false,
                'MESSAGE' => 'LDAP error when setting noncampus: ' . Ldap::getInstance()->get_error(),
            );
        }
    }

    public function isClassroom() {
        return $this->classroom;
    }

    public function setClassroom($classroom) {
        $dn = "uid=" . $this->getUsername() . "," . static::$ou;
        $data = array("employeetype" => ($classroom ? 'classroom' : array()));
        if ( Ldap::getInstance()->modify($dn, $data) ) {
            $this->classroom = $classroom;
            Log::info(
                "Set classroom-user for " . $this->getUsername() . " to " . $this->isClassroom(),
                Log::USER_SET_CLASSROOM,
                $this,
                null,
                $classroom);
            return array(
                'RESULT' => true,
                'MESSAGE' => 'Classroom-user successfully set.',
                'uid' => $this->getUsername(),
            );
        }
    }

    public function serializable() {
        return array(
            'username' => $this->username,
            'name' => $this->name,
            'homeDirectory' => $this->homeDirectory,
            'loginShell' => $this->loginShell,
            'email' => $this->email,
            'givenName' => $this->givenName,
            'sn' => $this->sn,
        );
    }

//////////////////Private Functions//////////

    protected function load_from_result($result) {
        // TODO someday this can be reworked to all happen in the parent class
        parent::load_from_result($result);
        $this->name = $result['cn'][0];
        $this->sn = $result['sn'][0];
        if ( isset($result['givenname']) ) {
            $this->givenName = $result['givenname'][0];
        } else {
            $this->givenName = trim(strstr($this->name, $this->sn, true));
        }
        $this->username = $result['uid'][0];
        $this->homeDirectory = $result['homedirectory'][0];
        $this->loginShell = $result['loginshell'][0];
        $this->email = isset($result['mail']) ? $result['mail'][0] : null;
        $this->emailforward = isset($result['postaladdress'][0]) ? $result['postaladdress'][0] : null; // Yes, postalAddress holds the forwarding email.
        if ( isset($result['shadowexpire']) ) {
            $this->expiration = $result['shadowexpire'][0];
        }
        if ( isset($result['description']) ) {
            $this->description = $result['description'][0];
        }
        if ( preg_match("/uid=(.*?),/um", $result['creatorsname'][0], $matches) ) {
            $this->creator = $matches[1];
        } else {
            $this->creator = $result['creatorsname'][0];
        }
        $this->createTime = strtotime($result['createtimestamp'][0]);
        if ( preg_match("/uid=(.*?),/um", $result['modifiersname'][0], $matches) ) {
            $this->modifier = $matches[1];
        } else {
            $this->modifier = $result['modifiersname'][0];
        }
        $this->modifyTime = strtotime($result['modifytimestamp'][0]);
        if ( isset($result['authtimestamp']) ) {
            $this->lastLogin = strtotime($result['authtimestamp'][0]);
        }
        $this->uidnumber = $result['uidnumber'][0];
        if ( isset($result['sambapwdlastset']) ) {
            $this->passwordSet = $result['sambapwdlastset'][0];
        }
        if ( isset($result['facsimiletelephonenumber'][0]) ) {
            $this->passwordExpiration = $result['facsimiletelephonenumber'][0];
        }
        if ( isset($result['employeetype']) ) {
            $this->leftCampus = ($result['employeetype'][0] == 'leftcampus');
            $this->nonCampus = ($result['employeetype'][0] == 'noncampus');
            $this->classroom = ($result['employeetype'][0] == 'classroom');
        }
        if ( isset($result['telexnumber']) ) {
            $this->crashplan = ($result['telexnumber'][0] == 1);
        }
        if ( isset($result['destinationindicator']) ) {
            $this->expirationReason = $result['destinationindicator'][0];
        }
        if ( isset($result['initials']) ) {
            $this->homeSubfolder = $result['initials'][0];
        }
    }

    // returns random int between $min,$max inclusive
    private static function openssl_rand($min = 0, $max = 0x7FFFFFFF) {
        $diff = $max - $min;
        if ( $diff < 0 || $diff > 0x7FFFFFFF ) {
            throw new RuntimeException("Bad range");
        }
        $bytes = openssl_random_pseudo_bytes(4);
        if ( $bytes === false || strlen($bytes) != 4 ) {
            throw new RuntimeException("Unable to get 4 bytes");
        }
        $ary = unpack("Nint", $bytes);
        $val = $ary['int'] & 0x7FFFFFFF;   // 32-bit safe
        $fp = (float)$val / 2147483647.0; // convert to [0,1]
        return intval(round($fp * $diff) + $min);
    }

    private static function SSHAHash($password) {
        $salt = substr(
            str_shuffle(str_repeat('ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789', 4)),
            0,
            4);
        return '{SSHA}' . base64_encode(sha1($password . $salt, true) . $salt);
    }

    private static function NTLMHash($cleartext) {
        // Convert to UTF16 little endian
        $cleartext = iconv('UTF-8', 'UTF-16LE', $cleartext);
        //Encrypt with MD4
        $MD4Hash = hash('md4', $cleartext);
        $NTLMHash = strtoupper($MD4Hash);
        return $NTLMHash;
    }

    private static function LMhash($string) {
        $string = strtoupper(substr($string, 0, 14));

        $p1 = self::LMhash_DESencrypt(substr($string, 0, 7));
        $p2 = self::LMhash_DESencrypt(substr($string, 7, 7));

        return strtoupper($p1 . $p2);
    }

    private static function LMhash_DESencrypt($string) {
        $key = array();
        $tmp = array();
        $len = strlen($string);

        for ( $i = 0; $i < 7; ++$i ) $tmp[] = $i < $len ? ord($string[$i]) : 0;

        $key[] = $tmp[0] & 254;
        $key[] = ($tmp[0] << 7) | ($tmp[1] >> 1);
        $key[] = ($tmp[1] << 6) | ($tmp[2] >> 2);
        $key[] = ($tmp[2] << 5) | ($tmp[3] >> 3);
        $key[] = ($tmp[3] << 4) | ($tmp[4] >> 4);
        $key[] = ($tmp[4] << 3) | ($tmp[5] >> 5);
        $key[] = ($tmp[5] << 2) | ($tmp[6] >> 6);
        $key[] = $tmp[6] << 1;

        $key0 = "";

        foreach ( $key as $k ) $key0 .= chr($k);

        $crypt = openssl_encrypt("KGS!@#$%", 'des-ecb', $key0, true/*OPENSSL_RAW_DATA | OPENSSL_ZERO_PADDING*/); // TODO These constants won't exist until php 5.4

        return bin2hex($crypt);
    }

}
