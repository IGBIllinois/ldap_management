<?php
class group {

	////////////////Private Variables//////////

	private $ldap;
	private $users = NULL;
	private $description;
	private $name;
	private $gidnumber;
	
	private $creator;
	private $createTime;
	private $modifier;
	private $modifyTime;
	
	private $isGroupOfNames = false;

	////////////////Public Functions///////////

	public function __construct($ldap, $name ="") {
		$this->ldap = $ldap;
		if ($name != "") {
			$this->load_by_name($name);
		}
	}


	public function __destruct() {
	}


	// Inserts a group into the database with the given name, then loads that group into this object. Displays errors if there are any.
	public function create($name, $description) {
		$name = trim(rtrim($name));

		$error = false;
		$message = "";
		//Verify Name
		if ($this->ldap->is_ldap_group($name)) {
			$error = true;
			$message = html::error_message("A group with that name already exists.");
		}

		//If Errors, return with error messages
		if ($error) {
			return array('RESULT'=>false,
				'MESSAGE'=>$message);
		}

		//Everything looks good, add group
		else {
			// Add to LDAP
			$dn = "cn=".$name.",".__LDAP_GROUP_OU__;
			$data = array("cn"=>$name,"objectClass"=>array('posixGroup','sambaGroupMapping'), "gidNumber"=>$gidnumber, "description"=>$description, 'sambaGroupType'=>2,'sambaSID'=>__SAMBA_ID__."-".$gidnumber);
			$this->ldap->add($dn, $data);
			
			$this->load_by_name($name);
			
			log::log_message("Added group ".$this->get_name());
			return array('RESULT'=>true,
				'MESSAGE'=>'Group successfully added.',
				'gid'=>$name);
		}

	}
	
	public function remove(){
		$dn = $this->get_group_rdn();
		if($this->ldap->remove($dn)){
			log::log_message("Removed group ".$this->get_name());
			return array('RESULT'=>true,
				'MESSAGE'=>'Group removed.',
				'gid'=>$this->name);
		}
	}
	
	// Inserts a user-group into the database with the given name and gidnumber.
	public function create_user_group($name, $description, $gidnumber) {
		$name = trim(rtrim($name));

		$error = false;
		$message = "";
		//Verify Name
		if ($this->ldap->is_ldap_group($name)) {
			$error = true;
			$message = html::error_message("A group with that name already exists.");
		}

		//If Errors, return with error messages
		if ($error) {
			return array('RESULT'=>false,
				'MESSAGE'=>$message);
		}

		//Everything looks good, add group
		else {
			// Add to LDAP
			$dn = "cn=".$name.",".__LDAP_GROUP_OU__;
			$data = array("cn"=>$name,"objectClass"=>array('posixGroup'), "gidNumber"=>$gidnumber, "description"=>$description,"memberUid"=>$name);
			$this->ldap->add($dn, $data);
			$this->load_by_name($name);
			log::log_message("Added group ".$this->get_name());
			log::log_message("Added user ".$name." to group ".$name);
			return array('RESULT'=>true,
				'MESSAGE'=>'Group successfully added.',
				'gid'=>$name);
		}

	}


	public function get_name() {
		return $this->name;
	}


	public function get_description() {
		return $this->description;
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
	
	public function get_gidnumber(){
		return $this->gidnumber;
	}

	public function get_users() {
		if ($this->users == null) {
			$this->users = $this->ldap->get_group_members($this->name);
		}
		return $this->users;
	}
	
	public function add_user($username){
		if(user::is_ldap_user($this->ldap,$username) && !in_array($username, $this->get_users())){
			$dn = "cn=".$this->get_name().",".__LDAP_GROUP_OU__;
			if($this->isGroupOfNames){
				$data = array("memberUid"=>$username,"member"=>"uid=".$username.",".__LDAP_PEOPLE_OU__);
			} else {
				$data = array("memberUid"=>$username);
			}
			if($this->ldap->mod_add($dn,$data)){
				log::log_message("Added user ".$username." to group ".$this->get_name());
				return array('RESULT'=>true,
				'MESSAGE'=>'User added to group.',
				'gid'=>$this->get_name(),
				'uid'=>$username);
			}
		}
		return array('RESULT'=>false,
			'MESSAGE'=>'LDAP error.'
		);
	}
	
	public function remove_user($username){
		if(in_array($username, $this->get_users())){
			$dn = "cn=".$this->get_name().",".__LDAP_GROUP_OU__;
			if($this->isGroupOfNames){
				$data = array("memberUid"=>$username,"member"=>"uid=".$username.",".__LDAP_PEOPLE_OU__);
			} else {
				$data = array("memberUid"=>$username);
			}
			if($this->ldap->mod_del($dn,$data)){
				log::log_message("Removed user ".$username." from group ".$this->get_name());
				return array('RESULT'=>true,
				'MESSAGE' => 'User removed from group.',
				'gid'=>$this->get_name(),
				'uid'=>$username);
			}
		}
	}
	
	public function set_name($name){
		$old_name = $this->get_name();
		$dn = "cn=".$old_name.",".__LDAP_GROUP_OU__;
		if($this->ldap->mod_rename($dn,"cn=".$name)){
			log::log_message("Changed group name from $old_name to $name.");
			$this->name = $name;
			return array('RESULT'=>true,
			'MESSAGE'=>'Name changed',
			'gid'=>$name);
		}
	}
	
	public function set_description($description){
		$dn = "cn=".$this->get_name().",".__LDAP_GROUP_OU__;
		$data = array("description"=>$description);
		if($this->ldap->modify($dn,$data)){
			log::log_message("Changed group description for ".$this->get_name()." to '$description'");
			$this->description = $description;
			return array('RESULT'=>true,
			'MESSAGE'=>'Description changed',
			'gid'=>$this->get_name());
		}
	}
	
	// $filterusers: 0=no filter, 1=filter out users, 2=filter only users
	public static function get_search_groups($ldap,$search,$start=-1,$count=-1,$sort="name",$asc="true",$filterusers=0){
		if($search == ""){
			$filter = "(cn=*)";
		} else {
			$filter = "(cn=*$search*)";
		}
		if($filterusers){
			$users = $ldap->get_all_users();
		}
		
		$attributes = array("cn","description","memberUid");
		$result = $ldap->search($filter,__LDAP_GROUP_OU__,$attributes);
		$groups = array();
		for($i=0; $i<$result['count']; $i++){
			if( $filterusers==0 || ($filterusers==1 && !in_array($result[$i]['cn'][0], $users)) || ($filterusers ==2 && in_array($result[$i]['cn'][0], $users)) ){
				$group = array("name"=>$result[$i]['cn'][0],"description"=>isset($result[$i]['description'][0])?$result[$i]['description'][0]:"","members"=>(isset($result[$i]['memberuid'])?$result[$i]['memberuid']['count']:0));
				$groups[] = $group;
			}
		}
		
		if(is_numeric($groups[0][$sort])){
			usort($groups,self::sorter($sort,$asc));
		} else {
			usort($groups,self::strsorter($sort,$asc));
		}
		if($start>=0){
			$groups = array_slice($groups,$start,$count);
		}
		return $groups;
	}
	
	public static function get_search_groups_count($ldap,$search,$filterusers){
		if($search == ""){
			$filter = "(cn=*)";
		} else {
			$filter = "(cn=*$search*)";
		}
		if($filterusers){
			$users = $ldap->get_all_users();
		}
		$attributes = array("cn","description","memberUid");
		$result = $ldap->search($filter,__LDAP_GROUP_OU__,$attributes);
		$count = 0;
		if($filterusers > 0){
			for($i=0; $i<$result['count']; $i++){
				if( ($filterusers==1 && !in_array($result[$i]['cn'][0], $users)) || ($filterusers ==2 && in_array($result[$i]['cn'][0], $users)) ){
					$count++;
				}
			}
		} else {
			$count = $result['count'];
		}
		return $count;
	}


	//////////////////Private Functions//////////
	public function load_by_name($name) {
		$filter = "(cn=".$name.")";
		$attributes = array("cn", "description", "gidNumber", "memberuid","creatorsName", "createTimestamp", "modifiersName", "modifyTimestamp", "objectClass");
		$result = $this->ldap->search($filter, __LDAP_GROUP_OU__, $attributes);
		$this->name = $result[0]['cn'][0];
		$this->description = $result[0]['description'][0];
		
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
		
		$this->gidnumber = $result[0]['gidnumber'][0];
		if(in_array('groupOfNames', $result[0]['objectclass'])){
			$this->isGroupOfNames = true;
		}
	}
	
	private static function sorter($key,$asc){
		if($asc == "true"){
			return function ($a,$b) use ($key) {
				return $a[$key]<$b[$key]?-1:($a[$key]==$b[$key]?0:1);
			};
		} else {
			return function ($a,$b) use ($key) {
				return $a[$key]<$b[$key]?1:($a[$key]==$b[$key]?0:-1);
			};
		}
	}
	
	private static function strsorter($key,$asc){
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


	private function get_group_rdn() {
		$filter = "(cn=" . $this->get_name() . ")";
		$attributes = array('dn');
		$result = $this->ldap->search($filter, __LDAP_GROUP_OU__, $attributes);
		if (isset($result[0]['dn'])) {
			return $result[0]['dn'];
		}
		else {
			return false;
		}
	}


	////////Static Functions///////////
	public static function get_all_groups($ldap) {

	}


}


?>
