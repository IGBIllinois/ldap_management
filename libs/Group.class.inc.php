<?php
class Group {

	////////////////Private Variables//////////

	private $userUIDs = NULL;
	private $users = NULL;
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
	
	private $raw_data = null;
	
	private static $lastSearch = array();

	////////////////Public Functions///////////

    /**
     * Group constructor.
     * @param string $name
     */
    public function __construct($name ="") {
		if ($name != "") {
			$this->load_by_name($name);
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
		if (self::exists($name)) {
			$error = true;
			$message = html::error_message("A group with that name already exists.");
		}
		// Verify description
		if (strlen($description)==0){
			$error = true;
			$message = html::error_message("Description must not be blank.");
		}

		//If Errors, return with error messages
		if ($error) {
			return array('RESULT'=>false,
				'MESSAGE'=>$message);
		}

		//Everything looks good, add group
		else {
			
			// Get existing gidnumbers
			$groups = Ldap::getInstance()->search("(!(cn=ftp_*))", __LDAP_GROUP_OU__, array('cn', 'gidNumber'));
			$gidnumbers = array();
			for($i=0; $i<$groups['count']; $i++){
				if(isset($groups[$i]['gidnumber'])){
					$gidnumbers[] = $groups[$i]['gidnumber'][0];
				}
			}
			// Find next highest gidnumber (starting at 20000)
			$gidstart = 20000;
			$gidnumber = max($gidstart,max($gidnumbers)) + 1;
			
			// Add to LDAP
			$dn = "cn=".$name.",".__LDAP_GROUP_OU__;
			$data = array("cn"=>$name, "objectClass"=>array('posixGroup', 'sambaGroupMapping'), "gidNumber"=>$gidnumber, "description"=>$description, 'sambaGroupType'=>2, 'sambaSID'=>__SAMBA_ID__."-".$gidnumber);
			Ldap::getInstance()->add($dn, $data);

			$this->load_by_name($name);

			Log::info("Added group ".$this->getName());
			return array('RESULT'=>true,
				'MESSAGE'=>'Group successfully added.',
				'gid'=>$name);
		}

	}


	public function remove() {
		$dn = $this->getRDN();
		if (Ldap::getInstance()->remove($dn)) {
			Log::info("Removed group ".$this->getName());
			return array('RESULT'=>true,
				'MESSAGE'=>'Group removed.',
				'gid'=>$this->name);
		}
	}


	// Inserts a user-group into the database with the given name and gidnumber.
	public function createUserGroup($name, $description, $gidnumber) {
		$name = trim($name);

		$error = false;
		$message = "";
		//Verify Name
		if (self::exists($name)) {
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
			$data = array("cn"=>$name, "objectClass"=>array('posixGroup'), "gidNumber"=>$gidnumber, "description"=>$description, "memberUid"=>$name);
			Ldap::getInstance()->add($dn, $data);
			$this->load_by_name($name);
			Log::info("Added group ".$this->getName());
			Log::info("Added user ".$name." to group ".$name);
			return array('RESULT'=>true,
				'MESSAGE'=>'Group successfully added.',
				'gid'=>$name);
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
			$result = Ldap::getInstance()->search_result($filter, __LDAP_GROUP_OU__);
			if($result != false){
				$this->raw_data = ldap_first_entry(Ldap::getInstance()->get_resource(),$result);
			}
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
		if ($this->userUIDs == null) {
			$filter = "(cn=" . $this->getName() . ")";
			$attributes = array('memberUid');
			$result = Ldap::getInstance()->search($filter, __LDAP_GROUP_OU__, $attributes);
			if ($result[0]['count']==0) {
				return array();
			}
			unset($result[0]['memberuid']['count']);
			$this->userUIDs = array();
			foreach ($result[0]['memberuid'] as $row) {
				array_push($this->userUIDs, $row);
			}
			usort($this->userUIDs, "html::username_cmp");
		}
		return $this->userUIDs;
	}

    /**
     * @return User[] array
     */
    public function getMembers(){
        if($this->users == null) {
            $uids = $this->getMemberUIDs();
            $this->users = array();
            foreach ( $uids as $uid ) {
                $this->users[] = new User($uid);
            }
        }
        return $this->users;
    }


	public function addUser($username) {
		if (User::exists($username) && !in_array($username, $this->getMemberUIDs())) {
			$dn = "cn=".$this->getName().",".__LDAP_GROUP_OU__;
			if ($this->isGroupOfNames) {
				$data = array("memberUid"=>$username, "member"=>"uid=".$username.",".__LDAP_PEOPLE_OU__);
			} else {
				$data = array("memberUid"=>$username);
			}
			if (Ldap::getInstance()->mod_add($dn, $data)) {
				Log::info("Added user ".$username." to group ".$this->getName());
				return array('RESULT'=>true,
					'MESSAGE'=>'User added to group.',
					'gid'=>$this->getName(),
					'uid'=>$username);
			} else {
				return array('RESULT'=>false,
					'MESSAGE'=>'Failed adding user to group: LDAP error: '.Ldap::getInstance()->get_error()
				);
			}
		}
		return array('RESULT'=>false,
			'MESSAGE'=>'Failed adding user to group: invalid username or user already in group.'
		);
	}


	public function removeUser($username) {
		if (in_array($username, $this->getMemberUIDs())) {
			$dn = "cn=".$this->getName().",".__LDAP_GROUP_OU__;
			if ($this->isGroupOfNames) {
				$data = array("memberUid"=>$username, "member"=>"uid=".$username.",".__LDAP_PEOPLE_OU__);
			} else {
				$data = array("memberUid"=>$username);
			}
			if (Ldap::getInstance()->mod_del($dn, $data)) {
				Log::info("Removed user ".$username." from group ".$this->getName());
				return array('RESULT'=>true,
					'MESSAGE' => 'User removed from group.',
					'gid'=>$this->getName(),
					'uid'=>$username);
			}
		}
	}


	public function setName($name) {
		$old_name = $this->getName();
		$dn = "cn=".$old_name.",".__LDAP_GROUP_OU__;
		if (Ldap::getInstance()->mod_rename($dn, "cn=".$name)) {
			Log::info("Changed group name from $old_name to $name.");
			$this->name = $name;
			return array('RESULT'=>true,
				'MESSAGE'=>'Name changed',
				'gid'=>$name);
		}
	}


	public function setDescription($description) {
		$this->description = $description;
		
		if ($this->set_desc_obj()) {
			Log::info("Changed group description for ".$this->getName()." to '$description'");
			return array('RESULT'=>true,
				'MESSAGE'=>'Description changed',
				'gid'=>$this->getName());
		}
	}
	
	public function addDirectory($server, $dir){
		$serverdir = $server.": ".$dir;
		array_push($this->serverdirs, $serverdir);
		
		if ($this->set_desc_obj()) {
			Log::info("Added server directory '$server: $dir' for ".$this->getName());
			return array('RESULT'=>true,
				'MESSAGE'=>'Server directory added',
				'gid'=>$this->getName());
		}
	}
	
	public function removeDirectory($serverdir){
		$serverdirs = array();
		foreach($this->serverdirs as $thisserverdir){
			if($serverdir != $thisserverdir){
				array_push($serverdirs, $thisserverdir);
			}
		}
		$this->serverdirs = $serverdirs;
		
		if($this->set_desc_obj()){
			Log::info("Removed server directory '$serverdir' from ".$this->getName());
			return array('RESULT'=>true,
				'MESSAGE'=>'Server directory removed',
				'gid'=>$this->getName());
		}
	}
	public function setOwner($owner){
		if(User::exists($owner)){
			$this->owner = $owner;
			if($this->set_desc_obj()){
				Log::info("Set owner to $owner for group ".$this->getName());
				return array('RESULT'=>true,
				'MESSAGE'=>'Group owner set',
				'gid'=>$this->getName());
			} else {
				return array('RESULT'=>false,
				'MESSAGE'=>'LDAP modify failed',
				'gid'=>$this->getName());
			}
		} else {
			return array('RESULT'=>false,
			'MESSAGE'=>'No such user',
			'gid'=>$this->getName());
		}
	}
	
	private function set_desc_obj(){
		$dn = "cn=".$this->getName().",".__LDAP_GROUP_OU__;
		$descObj = array('description'=>$this->description,'directories'=>$this->serverdirs,'owner'=>$this->owner);
		$data = array("description"=>json_encode($descObj));
		if (Ldap::getInstance()->modify($dn, $data)) {
			return true;
		} else {
			return false;
		}
	}
	
	public function getDirectories(){
		return $this->serverdirs;
	}
	
	public function getOwner(){
		return $this->owner;
	}

	/**
	 * @param string $search
	 * @param int $start
	 * @param int $count
	 * @param string $sort
	 * @param string $asc
	 * @param boolean $showUsers
	 * @return Group[]
	 */
	public static function search($search, $start=-1, $count=-1, $sort="name", $asc="true", $showUsers=false) {
	    // TODO asc shouldnt be a string
		if ($search == "") {
			$filter = "(cn=*)";
		} else {
			// This ugly str_replace brought to you by our version of php being too old to support JSON_UNESCAPED_SLASHES
			$filter = "(|(|(cn=*$search*)(description=*".str_replace("/","\\\\/",$search)."*))(gidnumber=$search))";
		}

		if (!$showUsers) {
			$users = User::all();
		}

		$attributes = array("cn", "description", "memberUid");
		$result = Ldap::getInstance()->search($filter, __LDAP_GROUP_OU__, $attributes);
		$groups = array();
		for($i=0; $i<$result['count']; $i++){
			$group = new Group();
			$group->load_from_result($result[$i]);

			if($showUsers || !in_array($group->getName(), $users)){
				$groups[] = $group;
			}
		}
		if(count($groups)){
			if (is_numeric($groups[0]->$sort)) {
				usort($groups, self::sorter($sort, $asc));
			} else {
				usort($groups, self::strsorter($sort, $asc));
			}
		}
		
		self::$lastSearch = $groups;
		if ($start>=0) {
			$groups = array_slice($groups, $start, $count);
		}
		return $groups;
	}


	public static function lastSearchCount() {
		return count(self::$lastSearch);
	}


	/**
	 * @return array
	 */
	public static function all() {
		$groups_array = array();
		$filter = "(cn=*)";
		$attributes = array('cn');
		$result = Ldap::getInstance()->search($filter, __LDAP_GROUP_OU__, $attributes);
		for ($i=0; $i<$result['count']; $i++) {
			array_push($groups_array, $result[$i]['cn'][0]);
		}
		return $groups_array;
	}


	/**
	 * @param string $name
	 * @return bool
	 */
	public static function exists($name) {
		$name = trim($name);
		$filter = "(cn=" . $name . ")";
		$attributes = array('');
		$result = Ldap::getInstance()->search($filter, __LDAP_GROUP_OU__, $attributes);
		if ($result['count']) {
			return true;
		} else {
			return false;
		}
	}


	//////////////////Private Functions//////////
	public function load_by_name($name) {
		$filter = "(cn=".$name.")";
		$attributes = array("cn", "description", "gidNumber", "memberuid", "creatorsName", "createTimestamp", "modifiersName", "modifyTimestamp", "objectClass");
		$result = Ldap::getInstance()->search($filter, __LDAP_GROUP_OU__, $attributes);
		if($result['count']>0){
			$this->load_from_result($result[0]);
		}
	}

	public function load_from_result($result){
		$this->name = $result['cn'][0];

		// Attempt to parse JSON
		if(!isset($result['description'])){
			$this->description = "";
			$this->owner = "";
			$this->serverdirs = array();
		} else {
			$descJson = json_decode($result['description'][0]);
			if($descJson==NULL){
				$this->description = $result['description'][0];
				$this->owner = '';
			} else {
				$this->description = isset($descJson->description)?$descJson->description:"";
				$this->serverdirs = isset($descJson->directories)?$descJson->directories:array();
				sort($this->serverdirs);
				$this->owner = isset($descJson->owner)?$descJson->owner:"";
			}
		}

		if(isset($result['creatorsname'])) {
			if ( preg_match("/uid=(.*?),/um", $result['creatorsname'][0], $matches) ) {
				$this->creator = $matches[1];
			} else {
				$this->creator = $result['creatorsname'][0];
			}
		}
		if(isset($result['createtimestamp'])) $this->createTime = strtotime($result['createtimestamp'][0]);
		if(isset($result['modifiersname'])) {
			if ( preg_match("/uid=(.*?),/um", $result['modifiersname'][0], $matches) ) {
				$this->modifier = $matches[1];
			} else {
				$this->modifier = $result['modifiersname'][0];
			}
		}
		if(isset($result['modifytimestamp'])) $this->modifyTime = strtotime($result['modifytimestamp'][0]);

		if(isset($result['gidnumber'])) $this->gidNumber = $result['gidnumber'][0];
		if(isset($result['objectclass'])){
			if (in_array('groupOfNames', $result['objectclass'])) {
				$this->isGroupOfNames = true;
			}
		}
	}


	private static function sorter($key, $asc) {
		if ($asc == "true") {
			return function ($a, $b) use ($key) {
				return $a->$key<$b->$key?-1:($a->$key==$b->$key?0:1);
			};
		} else {
			return function ($a, $b) use ($key) {
				return $a->$key<$b->$key?1:($a->$key==$b->$key?0:-1);
			};
		}


	}


	private static function strsorter($key, $asc) {
		if ($asc == "true") {
			return function ($a, $b) use ($key) {
				return html::username_cmp($a->$key, $b->$key);
			};
		} else {
			return function ($a, $b) use ($key) {
				return html::username_cmp($b->$key, $a->$key);
			};
		}


	}


	private function getRDN() {
		$filter = "(cn=" . $this->getName() . ")";
		$attributes = array('dn');
		$result = Ldap::getInstance()->search($filter, __LDAP_GROUP_OU__, $attributes);
		if (isset($result[0]['dn'])) {
			return $result[0]['dn'];
		}
		else {
			return false;
		}
	}
}