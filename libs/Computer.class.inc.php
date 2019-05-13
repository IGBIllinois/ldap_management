<?php
class Computer {

	////////////////Private Variables//////////

	private $name;

	private $uidnumber;
	private $creator;
	private $createTime;
	private $modifier;
	private $modifyTime;

	private $raw_data;

	private static $lastSearch = array();

	private static $fullAttributes = array("uid","creatorsName", "createTimestamp", "modifiersName", "modifyTimestamp","uidnumber");

	////////////////Public Functions///////////

	public function __construct($name = "") {
		if ($name != "") {
			$this->load_by_name($name);
		}
	}


	public function __destruct() {
	}


	// Inserts a user into the database with the given values, then loads that user into this object. Displays errors if there are any.
	public function create($name) {
		$name = trim($name);

		$error = false;
		$message = "";
		//Verify Username
		if ($name == "") {
			$error = true;
			$message = html::error_message("Please enter a name.");
		} elseif (self::exists($name)) {
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
			$uidnumbers = Ldap::getInstance()->search("(uid=*)", __LDAP_COMPUTER_OU__, array('uidnumber'));
			$cleanpass = 1;
			
			while ($cleanpass) {
				$cleanpass = 0;
				for ($i=0;$i<$uidnumbers['count'];$i++) {
					if ($uidnumbers[$i]['count'] != 0 && $uidnumbers[$i]['uidnumber'][0] == $uidnumber) {
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
				'cn'=> 'Computer',
				'sn'=> 'Computer',
				'uidNumber' => $uidnumber,
				'gidNumber' => $gidnumber,
				'sambaAcctFlags' => '[W        ]',
				'sambaPwdMustChange' => 0,
				'sambaPwdCanChange'	=> 0,
				'sambaPwdLastSet' => time(),
				'homeDirectory' => '/dev/null',
				'loginShell' => '/bin/false',
			);
			Ldap::getInstance()->add($dn, $data);
			$this->load_by_name($machine);
			
			Log::info("Added domain computer ".$this->getName());
			return array('RESULT'=>true,
				'MESSAGE'=>'Computer successfully added.',
				'uid'=>$machine);
		}

	}
	
	public function remove(){
		$dn = $this->get_user_rdn();
		if(Ldap::getInstance()->remove($dn)){
			Log::info("Removed domain computer ".$this->getName());
			return array('RESULT'=>true,
				'MESSAGE'=>'Computer deleted.',
				'uid'=>$this->name);
		}		
	}

	public function getName() {
		return $this->name;
	}
	
	public function getUidNumber(){
		return $this->uidnumber;
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

	public function getLdapAttributes(){
		$this->loadLdapResult();
		if($this->raw_data){
			return ldap_get_attributes(Ldap::getInstance()->get_resource(),$this->raw_data);
		}
		return false;
	}
	private function loadLdapResult(){
		if($this->raw_data == null){
			$filter = "(uid=".$this->getName().")";
			$result = Ldap::getInstance()->search_result($filter, __LDAP_COMPUTER_OU__);
			if($result != false){
				$this->raw_data = ldap_first_entry(Ldap::getInstance()->get_resource(),$result);
			}
		}
	}

	public static function search($search, $start, $count, $sort="name", $asc="true"){
		if($search == ""){
			$filter = "(uid=*)";
		} else {
			$filter = "(uid=*$search*)";
		}
		$result = Ldap::getInstance()->search($filter,__LDAP_COMPUTER_OU__,self::$fullAttributes);
		$computers = array();
		for($i=0; $i<$result['count']; $i++){
			$computer = new Computer();
			$computer->load_from_result($result[$i]);
			$computers[] = $computer;
		}
		usort($computers,self::sorter($sort,$asc));
		self::$lastSearch = $computers;
		return array_slice($computers,$start,$count);
	}

	public static function lastSearchCount(){
		return count(self::$lastSearch);
	}
	
	public static function exists($name){
		$name = trim($name);
		$filter = "(uid=".$name.")";
		$attributes = array('');
		$result = Ldap::getInstance()->search($filter,__LDAP_COMPUTER_OU__,$attributes);
		if($result['count']){
			return true;
		} else {
			return false;
		}
	}

//////////////////Private Functions//////////

	public function load_by_name($name) {
		$filter = "(uid=".$name.")";
		$result = Ldap::getInstance()->search($filter, __LDAP_COMPUTER_OU__, self::$fullAttributes);
		$this->load_from_result($result[0]);
	}

	public function load_from_result($result){
		$this->name = $result['uid'][0];
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
		if(isset($result['uidnumber'])) $this->uidnumber = $result['uidnumber'][0];
	}

	private function get_user_rdn() {
		$filter = "(uid=" . $this->getName() . ")";
		$attributes = array('dn');
		$result = Ldap::getInstance()->search($filter, '', $attributes);
		if (isset($result[0]['dn'])) {
			return $result[0]['dn'];
		}
		else {
			return false;
		}
	}

	private static function sorter($key,$asc){
		$key = "get".ucfirst($key);
		if($asc == "true"){
			return function ($a,$b) use ($key) {
				return html::username_cmp($a->$key(), $b->$key());
			};
		} else {
			return function ($a,$b) use ($key) {
				return html::username_cmp($b->$key(), $a->$key());
			};
		}
	}

}


?>
