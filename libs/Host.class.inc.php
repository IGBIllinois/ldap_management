<?php
class Host {

	////////////////Private Variables//////////

	private $userUIDs = NULL;
	private $users = NULL;
	private $name;
	private $ip;
	
	private $creator;
	private $createTime;
	private $modifier;
	private $modifyTime;

	private $raw_data;

	private static $fullAttributes = array("cn", "ipHostNumber","creatorsName", "createTimestamp", "modifiersName", "modifyTimestamp");

	////////////////Public Functions///////////

	public function __construct($name ="") {
		if ($name != "") {
			$this->load_by_name($name);
		}
	}


	public function __destruct() {
	}


	// Inserts a host into the database with the given name, then loads that host into this object. Displays errors if there are any.
	public function create($name,$ip) {
		$name = trim($name);

		$error = false;
		$message = "";
		//Verify Name
		if (self::exists($name)) {
			$error = true;
			$message = html::error_message("A host with that name already exists.");
		}

		//If Errors, return with error messages
		if ($error) {
			return array('RESULT'=>false,
				'MESSAGE'=>$message);
		}

		//Everything looks good, add host
		else {
			// Add to LDAP
			$dn = "cn=".$name.",".__LDAP_HOST_OU__;
			$data = array("cn"=>$name,"objectClass"=>array('device','ipHost'),'ipHostNumber'=>$ip);
			Ldap::getInstance()->add($dn, $data);
			
			$this->load_by_name($name);
			
			Log::info("Added host ".$this->getName());
			return array('RESULT'=>true,
				'MESSAGE'=>'Host successfully added.',
				'hid'=>$name);
		}

	}
	
	public function remove(){
		$dn = $this->get_rdn();
		if(Ldap::getInstance()->remove($dn)){
			Log::info("Removed host ".$this->getName());

			return array('RESULT'=>true,
				'MESSAGE'=>'Host removed.',
				'gid'=>$this->name);
		}
	}
	
	public function getName() {
		return $this->name;
	}


	public function getIp() {
		return $this->ip;
	}
	
	public function getCreator(){
		return $this->creator;
	}
	public function getCreateTime(){
		return $this->createTime;
	}
	public function getModifier(){
		return $this->modifier;
	}
	public function getModifyTime(){
		return $this->modifyTime;
	}

	public function getUserUIDs() {
		if ($this->userUIDs == null) {
			$filter = "(host=".$this->getName().")";
			$attributes = array('uid');
			$result = Ldap::getInstance()->search($filter,__LDAP_PEOPLE_OU__,$attributes);
			unset($result['count']);
			$this->userUIDs = array();
			foreach($result as $row){
				array_push($this->userUIDs,$row['uid'][0]);
			}
            usort($this->userUIDs,"html::username_cmp");
		}
		return $this->userUIDs;
	}

	public function getUsers(){
        if($this->users == null) {
            $uids = $this->getUserUIDs();
            $this->users = array();
            foreach ( $uids as $uid ) {
                $this->users[] = new User($uid);
            }
        }
        return $this->users;
    }
	
	public function setName($name){
		$old_name = $this->getName();
		$dn = "cn=".$old_name.",".__LDAP_HOST_OU__;
		if(Ldap::getInstance()->mod_rename($dn,"cn=".$name)){
			Log::info("Changed host name from $old_name to $name.");
			
			$users = $this->getUserUIDs();
			foreach($users as $username){
				$user = new User($username);
				$user->removeHost($old_name);
				$user->addHost($name);
			}
			
			$this->name = $name;
			return array('RESULT'=>true,
			'MESSAGE'=>'Name changed',
			'hid'=>$name);
		}
	}
	
	public function setIp($ip){
		$dn = "cn=".$this->getName().",".__LDAP_HOST_OU__;
		$data = array("ipHostNumber"=>$ip);
		if(Ldap::getInstance()->modify($dn,$data)){
			Log::info("Changed host ip for ".$this->getName()." to '$ip'");
			$this->ip = $ip;
			return array('RESULT'=>true,
			'MESSAGE'=>'IP changed',
			'hid'=>$this->getName());
		}
	}

    public function getLdapAttributes(){
        $this->loadLdapResult();
        if($this->raw_data){
            return ldap_get_attributes(Ldap::getInstance()->get_resource(),$this->raw_data);
        }
        return false;
    }
    private function loadLdapResult(){
        if($this->raw_data == null){
            $filter = "(cn=".$this->getName().")";
            $result = Ldap::getInstance()->search_result($filter, __LDAP_HOST_OU__);
            if($result != false){
                $this->raw_data = ldap_first_entry(Ldap::getInstance()->get_resource(),$result);
            }
        }
    }
	
	public static function all(){
	    // TODO decide whether these things should return objects or ids
		$filter = "(cn=*)";
		$result = Ldap::getInstance()->search($filter,__LDAP_HOST_OU__,self::$fullAttributes);
		$hosts = array();
		for($i=0; $i<$result['count']; $i++){
		    $host = new Host();
            $host->load_from_result($result[$i]);
			$hosts[] = $host;
		}
		usort($hosts, self::sorter());
		return $hosts;
	}
	
	public static function exists($name){
		$name = trim($name);
		$filter = "(cn=".$name.")";
		$attributes = array('');
		$result = Ldap::getInstance()->search($filter,__LDAP_HOST_OU__,$attributes);
		if($result['count']){
			return true;
		} else {
			return false;
		}
	}

	//////////////////Private Functions//////////
	private function load_by_name($name) {
		$filter = "(cn=".$name.")";
		$result = Ldap::getInstance()->search($filter, __LDAP_HOST_OU__, self::$fullAttributes);
		$this->load_from_result($result[0]);
	}

	private function load_from_result($result){
        $this->name = $result['cn'][0];
        $this->ip = $result['iphostnumber'][0];

        if( preg_match("/uid=(.*?),/um", $result['creatorsname'][0], $matches) ){
            $this->creator = $matches[1];
        } else {
            $this->creator = $result['creatorsname'][0];
        }
        $this->createTime = strtotime($result['createtimestamp'][0]);
        if( preg_match("/uid=(.*?),/um", $result['modifiersname'][0], $matches) ){
            $this->modifier = $matches[1];
        } else {
            $this->modifier = $result['modifiersname'][0];
        }
        $this->modifyTime = strtotime($result['modifytimestamp'][0]);
    }
	
	private function get_rdn() {
		$filter = "(cn=" . $this->getName() . ")";
		$attributes = array('dn');
		$result = Ldap::getInstance()->search($filter, __LDAP_HOST_OU__, $attributes);
		if (isset($result[0]['dn'])) {
			return $result[0]['dn'];
		}
		else {
			return false;
		}
	}

    private static function sorter(){
	    return function($a,$b){
	        return strcmp($a->name, $b->name);
        };
    }
}
