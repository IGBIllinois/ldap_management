<?php

class Computer extends LdapObject
{
    protected static $ou = __LDAP_COMPUTER_OU__;
    ////////////////Private Variables//////////

    private $name;

    private $uidnumber;
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


    // Inserts a user into the database with the given values, then loads that user into this object. Displays errors if there are any.
    public function create($name)
    {
        $name = trim($name);

        $error = false;
        $message = "";
        //Verify Username
        if ($name == "") {
            $error = true;
            $message = "Please enter a name.";
        } else {
            if (self::exists($name)) {
                $error = true;
                $message = "Computer already exists.";
            }
        }

        //If Errors, return with error messages
        if ($error) {
            return [
                'RESULT' => false,
                'MESSAGE' => $message,
            ];
        } //Everything looks good, add computer
        else {
            // Find first unused uidNumber,gidNumber
            $uidnumber = 10000;
            $uidnumbers = Ldap::getInstance()->search("(uid=*)", static::$ou, ['uidnumber']);
            $cleanpass = 1;

            while ($cleanpass) {
                $cleanpass = 0;
                for ($i = 0; $i < $uidnumbers['count']; $i++) {
                    if ($uidnumbers[$i]['count'] != 0 && $uidnumbers[$i]['uidnumber'][0] == $uidnumber) {
                        $cleanpass++;
                        $uidnumber++;
                    }
                }
            }

            $gidnumber = '515';

            $machine = strtolower($name) . "$";

            $sid = __SAMBA_ID__ . '-' . $uidnumber;

            // Add LDAP entry
            $dn = "uid=" . $machine . "," . static::$ou;
            $data = [
                'uid' => $machine,
                'sambasid' => $sid,
                'objectClass' => ['inetOrgPerson', 'posixAccount', 'sambaSamAccount', 'account'],
                'displayname' => $machine,
                'cn' => 'Computer',
                'sn' => 'Computer',
                'uidNumber' => $uidnumber,
                'gidNumber' => $gidnumber,
                'sambaAcctFlags' => '[W        ]',
                'sambaPwdMustChange' => 0,
                'sambaPwdCanChange' => 0,
                'sambaPwdLastSet' => time(),
                'homeDirectory' => '/dev/null',
                'loginShell' => '/bin/false',
            ];
            Ldap::getInstance()->add($dn, $data);
            $this->load_by_id($machine);

            Log::info("Added domain computer " . $this->getName(), Log::DOMAIN_ADD, $this);
            return [
                'RESULT' => true,
                'MESSAGE' => 'Computer successfully added.',
                'uid' => $machine,
            ];
        }
    }

    public function remove()
    {
        $dn = $this->getRDN();
        if (Ldap::getInstance()->remove($dn)) {
            Log::info("Removed domain computer " . $this->getName(), Log::DOMAIN_REMOVE, $this);
            return [
                'RESULT' => true,
                'MESSAGE' => 'Computer deleted.',
                'uid' => $this->name,
            ];
        }
    }

    public function getName()
    {
        return $this->name;
    }

    public function getUidNumber()
    {
        return $this->uidnumber;
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


    public static function search($search, $start, $count, $sort = "name", $asc = true)
    {
        if ($search == "") {
            $filter = "(uid=*)";
        } else {
            $filter = "(uid=*$search*)";
        }
        $result = Ldap::getInstance()->search($filter, static::$ou, self::$fullAttributes);
        $computers = [];
        for ($i = 0; $i < $result['count']; $i++) {
            $computer = new Computer();
            $computer->load_from_result($result[$i]);
            $computers[] = $computer;
        }
        usort($computers, self::sorter($sort, $asc));
        self::$lastSearch = $computers;
        return array_slice($computers, $start, $count);
    }

//////////////////Private Functions//////////

    public function load_from_result($result)
    {
        parent::load_from_result($result);
        $this->name = $result['uid'][0];
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
        if (isset($result['uidnumber'])) {
            $this->uidnumber = $result['uidnumber'][0];
        }
    }

}
