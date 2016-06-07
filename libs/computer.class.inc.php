<?php
class computer {

	////////////////Private Variables//////////

	private $name;

	private $ldap;
	private $uidnumber;
	private $creator;
	private $createTime;
	private $modifier;
	private $modifyTime;

	////////////////Public Functions///////////

	public function __construct($ldap, $name = "") {
		$this->ldap = $ldap;
		if ($name != "") {
			$this->load_by_name($name);
		}
	}


	public function __destruct() {
	}


	// Inserts a user into the database with the given values, then loads that user into this object. Displays errors if there are any.
	public function create($name) {
		$name = trim(rtrim($name));

		$error = false;
		$message = "";
		//Verify Username
		if ($name == "") {
			$error = true;
			$message = html::error_message("Please enter a name.");
		} elseif (self::is_ldap_computer($this->ldap,$name)) {
			$error = true;
			$message = html::error_message("Computer already exists.");
		}
				
		//If Errors, return with error messages
		if ($error) {
			return array('RESULT'=>false,
				'MESSAGE'=>$message);
		}

		//Everything looks good, add computer
		else {
			// Find first unused uidNumber,gidNumber
			$uidnumber = 10000;
			$uidnumbers = $this->ldap->search("(cn=computer)", __LDAP_COMPUTER_OU__, array('cn', 'uidNumber'));
			$cleanpass = 1;
			while ($cleanpass) {
				$cleanpass = 0;
				for ($i=0;$i<$uidnumbers['count'];$i++) {
					if ($uidnumbers[$i]['uidnumber'][0] == $uidnumber) {
						$cleanpass++;
						$uidnumber++;
					}
				}
			}
			
			$gidnumber = '515';
			
			$machine = strtolower($name)."$";
			
			$sid = __SAMBA_ID__.'-'.$uidnumber;
			$groupsid = __SAMBA_ID__.'-'.$gidnumber;
						
			// Add LDAP entry
			$dn = "uid=".$machine.",".__LDAP_COMPUTER_OU__;
			$data = array(
				'uid'=>$machine,
				'sambasid'=>$sid,
				'objectClass'=>array('inetOrgPerson','posixAccount','sambaSamAccount','account'),
				'displayname'=>$machine,
				'cn'=>'computer',
				'sn'=>'computer',
				'uidNumber' => $uidnumber,
				'gidNumber' => $gidnumber,
				'sambaAcctFlags' => '[W        ]',
				'sambaPwdMustChange' => 0,
				'sambaPwdCanChange'	=> 0,
				'sambaPwdLastSet' => time(),
				'homeDirectory' => '/dev/null',
				'loginShell' => '/bin/false',
			);
			$this->ldap->add($dn, $data);
			$this->load_by_name($machine);
			
			log::log_message("Added domain computer ".$this->get_name());
			return array('RESULT'=>true,
				'MESSAGE'=>'Computer successfully added.',
				'uid'=>$machine);
		}

	}
	
	public function remove(){
		$dn = $this->get_user_rdn();
		if($this->ldap->remove($dn)){
			log::log_message("Removed domain computer ".$this->get_name());
			return array('RESULT'=>true,
				'MESSAGE'=>'Computer deleted.',
				'uid'=>$this->username);
		}		
	}

	public function get_name() {
		return $this->name;
	}
	
	public function get_uidnumber(){
		return $this->uidnumber;
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

	public static function get_search_computers($ldap,$search,$start,$count,$sort="name",$asc="true"){
		if($search == ""){
			$filter = "(uid=*)";
		} else {
			$filter = "(uid=*$search*)";
		}
		$attributes = array("uid");
		$result = $ldap->search($filter,__LDAP_COMPUTER_OU__,$attributes);
		$users = array();
		for($i=0; $i<$result['count']; $i++){
			$user = array("name"=>$result[$i]['uid'][0]);
			$users[] = $user;
		}
		usort($users,self::sorter($sort,$asc));
		return $users;
	}
	
	public static function is_ldap_computer($ldap,$name){
		$name = trim(rtrim($name));
		$filter = "(uid=".$name.")";
		$attributes = array('');
		$result = $ldap->search($filter,__LDAP_COMPUTER_OU__,$attributes);
		if($result['count']){
			return true;
		} else {
			return false;
		}
	}

//////////////////Private Functions//////////

	public function load_by_name($name) {
		$filter = "(uid=".$name.")";
		$attributes = array("uid","creatorsName", "createTimestamp", "modifiersName", "modifyTimestamp","uidnumber");
		$result = $this->ldap->search($filter, __LDAP_COMPUTER_OU__, $attributes);
		$this->name = $result[0]['uid'][0];
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
		$this->uidnumber = $result[0]['uidnumber'][0];
	}

	private function get_user_rdn() {
		$filter = "(uid=" . $this->get_name() . ")";
		$attributes = array('dn');
		$result = $this->ldap->search($filter, '', $attributes);
		if (isset($result[0]['dn'])) {
			return $result[0]['dn'];
		}
		else {
			return false;
		}
	}

	private static function sorter($key,$asc){
		if($asc == "true"){
			return function ($a,$b) use ($key) {
				return strcasecmp($a[$key], $b[$key]);
			};
		} else {
			return function ($a,$b) use ($key) {
				return strcasecmp($b[$key], $a[$key]);
			};
		}
	}

}


?>
