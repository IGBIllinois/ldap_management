<?php
class host {

	////////////////Private Variables//////////

	private $ldap;
	private $users = NULL;
	private $name;
	private $ip;
	
	private $creator;
	private $createTime;
	private $modifier;
	private $modifyTime;

	////////////////Public Functions///////////

	public function __construct($ldap, $name ="") {
		$this->ldap = $ldap;
		if ($name != "") {
			$this->load_by_name($name);
		}
	}


	public function __destruct() {
	}


	// Inserts a host into the database with the given name, then loads that host into this object. Displays errors if there are any.
	public function create($name,$ip) {
		$name = trim(rtrim($name));

		$error = false;
		$message = "";
		//Verify Name
		if (self::is_ldap_host($this->ldap,$name)) {
			$error = true;
			$message = html::error_message("A group with that name already exists.");
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
			$this->ldap->add($dn, $data);
			
			$this->load_by_name($name);
			
			log::log_message("Added host ".$this->get_name());
			return array('RESULT'=>true,
				'MESSAGE'=>'Host successfully added.',
				'hid'=>$name);
		}

	}
	
	public function remove(){
		$users = $this->get_users();
		$dn = $this->get_host_rdn();
		if($this->ldap->remove($dn)){
			log::log_message("Removed host ".$this->get_name());
			// Remove host from all users
/* Actually no, don't do that
			foreach($users as $username){
				$user = new user($this->ldap,$username);
				$user->remove_machinerights($this->get_name());
			}
*/
			return array('RESULT'=>true,
				'MESSAGE'=>'Host removed.',
				'gid'=>$this->name);
		}
	}
	
	public function get_name() {
		return $this->name;
	}


	public function get_ip() {
		return $this->ip;
	}
	
	public function get_creator(){
		return $this->creator;
	}
	public function get_createTime(){
		return $this->createTime;
	}
	public function get_modifier(){
		return $this->modifier;
	}
	public function get_modifyTime(){
		return $this->modifyTime;
	}

	public function get_users() {
		if ($this->users == null) {
			$filter = "(host=".$this->get_name().")";
			$attributes = array('uid');
			$result = $this->ldap->search($filter,__LDAP_PEOPLE_OU__,$attributes);
			unset($result['count']);
			$this->users = array();
			foreach($result as $row){
				array_push($this->users,$row['uid'][0]);
			}
		}
		return $this->users;
	}
	
	public function set_name($name){
		$old_name = $this->get_name();
		$dn = "cn=".$old_name.",".__LDAP_HOST_OU__;
		if($this->ldap->mod_rename($dn,"cn=".$name)){
			log::log_message("Changed host name from $old_name to $name.");
			
			$users = $this->get_users();
			foreach($users as $username){
				$user = new user($this->ldap,$username);
				$user->remove_machinerights($old_name);
				$user->add_machinerights($name);
			}
			
			$this->name = $name;
			return array('RESULT'=>true,
			'MESSAGE'=>'Name changed',
			'hid'=>$name);
		}
	}
	
	public function set_ip($ip){
		$dn = "cn=".$this->get_name().",".__LDAP_HOST_OU__;
		$data = array("ipHostNumber"=>$ip);
		if($this->ldap->modify($dn,$data)){
			log::log_message("Changed host ip for ".$this->get_name()." to '$ip'");
			$this->ip = $ip;
			return array('RESULT'=>true,
			'MESSAGE'=>'IP changed',
			'hid'=>$this->get_name());
		}
	}
	
	public static function get_all_hosts($ldap,$expandedinfo = false){
		$filter = "(cn=*)";
		$attributes = array("cn","ipHostNumber");
		$result = $ldap->search($filter,__LDAP_HOST_OU__,$attributes);
		$hosts = array();
		for($i=0; $i<$result['count']; $i++){
			if($expandedinfo){
				$num_users = $ldap->search("(host=".$result[$i]['cn'][0].")",__LDAP_PEOPLE_OU__,array(''));
				$num_users = $num_users['count']?$num_users['count']:0;
				$host = array("name"=>$result[$i]['cn'][0],"ip"=>isset($result[$i]['iphostnumber'][0])?$result[$i]['iphostnumber'][0]:"","numusers"=>$num_users);
			} else {
				$host = array("name"=>$result[$i]['cn'][0],"ip"=>isset($result[$i]['iphostnumber'][0])?$result[$i]['iphostnumber'][0]:"");
			}
			$hosts[] = $host;
		}
		return $hosts;
	}
	
	public static function is_ldap_host($ldap, $name){
		$name = trim(rtrim($name));
		$filter = "(cn=".$name.")";
		$attributes = array('');
		$result = $ldap->search($filter,__LDAP_HOST_OU__,$attributes);
		if($result['count']){
			return true;
		} else {
			return false;
		}
	}

	//////////////////Private Functions//////////
	public function load_by_name($name) {
		$filter = "(cn=".$name.")";
		$attributes = array("cn", "ipHostNumber","creatorsName", "createTimestamp", "modifiersName", "modifyTimestamp");
		$result = $this->ldap->search($filter, __LDAP_HOST_OU__, $attributes);
		$this->name = $result[0]['cn'][0];
		$this->ip = $result[0]['iphostnumber'][0];
		
		if( preg_match("/uid=(.*?),/um", $result[0]['creatorsname'][0], $matches) ){
			$this->creator = $matches[1];
		} else {
			$this->creator = $result[0]['creatorsname'][0];
		}
		$this->createTime = strtotime($result[0]['createtimestamp'][0]);
		if( preg_match("/uid=(.*?),/um", $result[0]['modifiersname'][0], $matches) ){
			$this->modifier = $matches[1];
		} else {
			$this->modifier = $result[0]['modifiersname'][0];
		}
		$this->modifyTime = strtotime($result[0]['modifytimestamp'][0]);
	}
	
	private function get_host_rdn() {
		$filter = "(cn=" . $this->get_name() . ")";
		$attributes = array('dn');
		$result = $this->ldap->search($filter, __LDAP_HOST_OU__, $attributes);
		if (isset($result[0]['dn'])) {
			return $result[0]['dn'];
		}
		else {
			return false;
		}
	}


}


?>
