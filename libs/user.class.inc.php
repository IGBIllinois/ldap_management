<?php
class user {

	////////////////Private Variables//////////

	private $username;
	private $name;

	private $ldap;
	private $uidnumber;
	private $email;
	private $emailforward;
	private $homeDirectory;
	private $givenName;
	private $sn;
	private $machinerights = null;
	private $groups = null;
	private $loginShell;
	private $expiration = null;
	private $leftcampus = false;
	private $noncampus = false;
	private $crashplan = false;
	
	private $creator;
	private $createTime;
	private $modifier;
	private $modifyTime;
	private $passwordSet = null;
	private $passwordExpiration = null;
	
	private static $lastSearch = array();

	////////////////Public Functions///////////

	public function __construct($ldap, $username = "") {
		$this->ldap = $ldap;
		if ($username != "") {
			$this->load_by_username($username);
		}
	}


	public function __destruct() {
	}


	// Inserts a user into the database with the given values, then loads that user into this object. Displays errors if there are any.
	public function create($username, $firstname, $lastname, $password) {
		$username = trim(rtrim($username));
		$firstname = trim(rtrim($firstname));
		$lastname = trim(rtrim($lastname));
		$name = $firstname." ".$lastname;

		$error = false;
		$message = "";
		//Verify Username
		if ($username == "") {
			$error = true;
			$message = html::error_message("Please enter a username.");
		} elseif (user::is_ldap_user($this->ldap,$username)) {
			$error = true;
			$message = html::error_message("User already exists.");
		} elseif (group::is_ldap_group($this->ldap,$username)) {
			$error = true;
			$message = html::error_message("Username already exists as group");
		}
		if ($firstname == "") {
			$error = true;
			$message .= html::error_message("Please enter a first name.");
		}
		if ($lastname == "") {
			$error = true;
			$message .= html::error_message("Please enter a last name.");
		}
		if ($password == "") {
			$error = true;
			$message .= html::error_message("Please enter a password.");
		}
		
		//If Errors, return with error messages
		if ($error) {
			return array('RESULT'=>false,
				'MESSAGE'=>$message);
		}

		//Everything looks good, add user
		else {
			// Get all users' uidnumber, gidnumber
			$users = $this->ldap->search("(!(uid=ftp_*))", __LDAP_PEOPLE_OU__, array('uid', 'uidNumber', 'gidNumber'));
			$uidnumbers = array();
			$gidnumbers = array();
			for($i=0; $i<$users['count']; $i++){
				if(isset($users[$i]['uidnumber'])){
					$uidnumbers[] = $users[$i]['uidnumber'][0];
					$gidnumbers[] = $users[$i]['gidnumber'][0];
				}
			}
			// Get all groups' gidnumber
			$groups = $this->ldap->search("(cn=*)", __LDAP_GROUP_OU__, array('cn', 'gidNumber'));
			$groupgidnumbers = array();
			for($i=0; $i<$groups['count']; $i++){
				if(isset($users[$i]['gidnumber'])){
					$groupgidnumbers[] = $groups[$i]['gidnumber'][0];
				}
			}
			// Find the max uidnumber already in use (at least 1000)
			$uidstart = 1000;
			$uidnumber = max($uidstart,max($uidnumbers),max($gidnumbers)) + 1;
			// Now start there and look for an empty slot in gidnumbers (which will probably be right away)
			while(in_array($uidnumber, $groupgidnumbers)){
				$uidnumber++;
			}
			// gidnumber and uidnumber should match
			$gidnumber = $uidnumber;
			
			$passwd = "";
			if(__PASSWD_HASH__=="MD5"){
				$passwd = self::MD5Hash($password);
			}
			if(__PASSWD_HASH__=="SSHA"){
				$passwd = self::SSHAHash($password);
			}
			
			$ntpasswd = self::NTLMHash($password);
			$lmpasswd = self::LMHash($password);
			
			if($username < 'n'){
				$homesub = 'a-m';
			} else {
				$homesub = 'n-z';
			}
			
			// Add LDAP user
			$dn = "uid=".$username.",".__LDAP_PEOPLE_OU__;
			$data = array(
				'uid'=>$username,
				'objectClass'=>array('inetOrgPerson','posixAccount','shadowAccount','sambaSamAccount','account'),
				'cn'=>$name,
				'sn'=>$lastname,
				'givenName'=>$firstname,
				'mail'=>$username.__MAIL_SUFFIX__,
				'userPassword' => $passwd,
				'loginShell' => __DEFAULT_SHELL__,
				'uidNumber' => $uidnumber,
				'gidNumber' => $gidnumber,
				'homeDirectory' => __HOME_DIR__."/".$homesub."/".$username,
				'gecos'=> $name,
				'sambaSID' => __SAMBA_ID__."-".$uidnumber,
				'sambaLMPassword' => $lmpasswd,
				'sambaNTPassword' => $ntpasswd,
				'SambaPwdLastSet' => time(),
				'facsimiletelephonenumber' => time()+60*60*24*365
			);
			if(!$this->ldap->add($dn, $data)){
				return array(
					'RESULT'=>false,
					'MESSAGE'=>html::error_message('LDAP error when adding user: '.$this->ldap->get_error())
				);
			}
			$this->load_by_username($username);
			log::log_message("Added user ".$this->get_username()." (".$this->get_name().")");
			
			// Add LDAP group
			$group = new group($this->ldap);
			$group->create_user_group($username,$username,$gidnumber);
			
			return array('RESULT'=>true,
				'MESSAGE'=>html::success_message('User successfully added.'),
				'uid'=>$username);
		}

	}
	
	public function remove(){
		$dn = $this->get_user_rdn();
		if($this->ldap->remove($dn)){
			if(__RUN_SHELL_SCRIPTS__){
				$safeusername = escapeshellarg($this->get_username());
				exec("sudo ../bin/remove_user.pl $safeusername");
			}
			// remove user group
			$group = new group($this->ldap,$this->get_username());
			$group->remove();
			// remove user from groups
			$groups = $this->get_groups();
			for($i=0; $i<count($groups); $i++){
				$group = new group($this->ldap,$groups[$i]);
				$group->remove_user($this->username);
			}
			
			log::log_message("Removed user ".$this->get_username());
			return array('RESULT'=>true,
				'MESSAGE'=>'User deleted.',
				'uid'=>$this->username);
		}		
	}
	
	public function get_attribute($field){
		// TODO update this class to pull in all fields from extensions so we don't have to search for each attribute
		$filter = "(uid=".$this->get_username().")";
		$attributes = array($field);
		$result = $this->ldap->search($filter, __LDAP_PEOPLE_OU__, $attributes);
		if($result['count']>0){
			if($result[0]['count']==0){
				return "";
			} else {
				return $result[0][$field][0];
			}
		} else {
			return NULL;
		}
	}
	
	public function set_attribute($field,$value){
		$dn = "uid=".$this->get_username().",".__LDAP_PEOPLE_OU__;
		$data = array($field=>$value);
		if($this->ldap->modify($dn,$data)){
			// TODO once all fields are pulled in during load, update the field here
			log::log_message("Set ".$field." for ".$this->get_username()." to ".$value);
			return array('RESULT'=>true,
				'MESSAGE'=>$field." set",
				'uid'=>$this->get_username());
		} else {
			return array('RESULT'=>false,
				'MESSAGE'=>'LDAP Error: '.$this->ldap->get_error(),
				'uid'=>$this->username);
		}
	}
	public function remove_attribute($field){
		$dn = "uid=".$this->get_username().",".__LDAP_PEOPLE_OU__;
		$data = array($field=>array());
		if($this->ldap->mod_del($dn,$data)){
			log::log_message("Removed ".$field." for user ".$this->get_username());
			return array('RESULT'=>true,
				'MESSAGE'=>$field.' removed.',
				'uid'=>$this->get_username());
		}
	}

	public function get_username() {
		return $this->username;
	}

	public function get_email() {
		return $this->email;
	}
	
	public function get_emailforward(){
		return $this->emailforward;
	}
	public function set_emailforward($emailforward){
		$dn = "uid=".$this->get_username().",".__LDAP_PEOPLE_OU__;
		$data = array("postalAddress"=>$emailforward);
		if($this->ldap->modify($dn,$data)){
			$this->emailforward = $emailforward;
			log::log_message("Set email forwarding for ".$this->get_username()." to ".$emailforward);
			return array('RESULT'=>true,
				'MESSAGE'=>'Email forwarding set',
				'uid'=>$this->get_username());
		}
	}
	public function remove_emailforward(){
		$dn = "uid=".$this->get_username().",".__LDAP_PEOPLE_OU__;
		$data = array("postalAddress"=>array());
		if($this->ldap->mod_del($dn,$data)){
			$this->emailforward = null;
			log::log_message("Removed email forwarding for ".$this->get_username());
			return array('RESULT'=>true,
			'MESSAGE'=>'Email forwarding removed',
			'uid'=>$this->get_username());
		}
	}
	
	public function get_crashplan(){
		return $this->crashplan;
	}
	public function set_crashplan($crashplan){
		$value = 0;
		if($crashplan){
			$value = 1;
		}
		$dn = "uid=".$this->get_username().",".__LDAP_PEOPLE_OU__;
		$data = array("telexNumber"=>$value);
		if($this->ldap->modify($dn,$data)){
			$this->crashplan = $value;
			log::log_message("Set crashplan for ".$this->get_username()." to ".($crashplan?'active':'inactive'));
			return array('RESULT'=>true,
				'MESSAGE'=>'Crashplan set',
				'uid'=>$this->get_username());
		}
	}

	public function get_loginShell() {
 		return $this->loginShell;
	}
	
	public function set_loginShell($shell){
		$dn = $this->get_user_rdn();
		$data = array("loginShell"=>$shell);
		if($this->ldap->modify($dn,$data)){
			log::log_message("Set login shell for ".$this->get_username()." to ".$shell);
			return array('RESULT'=>true,
				'MESSAGE'=>'Login shell changed.',
				'uid'=>$this->username);
		} else {
			return array('RESULT'=>false,
				'MESSAGE'=>'LDAP Error: '.$this->ldap->get_error(),
				'uid'=>$this->username);
		}
	}
	
	public function get_homeDirectory(){
		return $this->homeDirectory;
	}


	public function get_name() {
		return $this->name;
	}

	public function get_expiration(){
		return $this->expiration;
	}
	public function get_password_expiration(){
		return $this->passwordExpiration;
	}
	public function is_expired(){
		return ($this->expiration != null && $this->expiration <= time());
	}
	public function is_expiring(){
		return ($this->expiration != null && $this->expiration > time());
	}
	public function is_password_expired(){
		return ($this->passwordExpiration != null && $this->passwordExpiration <= time());
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
	public function get_passwordSet(){
		return $this->passwordSet;
	}

	public function get_machinerights() {
		if ($this->machinerights == null) {
			$filter = "(uid=".$this->get_username().")";
			$attributes = array('host');
			$result = $this->ldap->search($filter, "", $attributes);
			if ($result['count'] && $result[0]["count"]) {
				$this->machinerights = $result[0]["host"];
				unset($this->machinerights['count']);
			} else {
				$this->machinerights = false;
			}
		}
		return $this->machinerights;
	}


	public function get_groups() {
		if ($this->groups == null) {
			$filter = "(&(cn=*)(memberUid=" . $this->get_username() . "))";
			$attributes = array('cn');
			$result = $this->ldap->search($filter, __LDAP_GROUP_OU__, $attributes);
			unset($result['count']);
			$this->groups = array();
			foreach ($result as $row) {
				array_push($this->groups, $row['cn'][0]);
			}
		}
		return $this->groups;
	}

	public function add_machinerights($host) {
		if(host::is_ldap_host($this->ldap,$host) && (!$this->get_machinerights() || !in_array($host, $this->get_machinerights()))){
			$dn = "uid=".$this->get_username().",".__LDAP_PEOPLE_OU__;
			$filter = "(&(uid=".$this->get_username().")(objectClass=account))";
			$result = $this->ldap->search($filter,__LDAP_PEOPLE_OU__,array());
			
			if($result['count']==0){
				$data = array("objectClass"=>'account');
				$this->ldap->mod_add($dn,$data);
			}
			
			$data = array("host"=>$host);
			if($this->ldap->mod_add($dn,$data)){
				log::log_message("Gave host access for ".$host." to ".$this->get_username());
				return array('RESULT'=>true,
				'MESSAGE'=>'Machine rights successfully added.',
				'uid'=>$this->get_username());
			} else {
				return array('RESULT'=>false,
				'MESSAGE'=>'Error: '.$this->ldap->get_error());
			}
		}
	}
	
	public function remove_machinerights($host) {
		if(host::is_ldap_host($this->ldap,$host) || ($this->get_machinerights() && in_array($host, $this->get_machinerights()))){
			$dn = "uid=".$this->get_username().",".__LDAP_PEOPLE_OU__;
			$data = array("host"=>$host);
			if(@$this->ldap->mod_del($dn,$data)){
				log::log_message("Removed host access to ".$host." from ".$this->get_username());
				return array('RESULT'=>true,
				'MESSAGE'=>'Machine rights successfully removed.',
				'uid'=>$this->get_username());
			}
		}
	}
	
	public function set_name($firstname,$lastname){
		$dn = $this->get_user_rdn();
		$name = $firstname." ".$lastname;
		$data = array("cn"=>$name,"sn"=>$lastname,"givenName"=>$firstname,"gecos"=>$name);
		if($this->ldap->modify($dn,$data)){
			log::log_message("Changed name for ".$this->get_username()." to \"$name\"");
			return array('RESULT'=>true,
			'MESSAGE'=>'Name successfully changed.',
			'uid'=>$this->get_username());
		}
	}
	
	public function set_username($username){
		$dn = $this->get_user_rdn();
		$old_username = $this->get_username();
		if($username < 'n'){
			$homesub = 'a-m';
		} else {
			$homesub = 'n-z';
		}
		$groups = $this->get_groups();
		
		// Change dn of user
		$data = array("mail"=>$username.__MAIL_SUFFIX__,"homeDirectory"=>"/home/".$homesub."/".$username);
		if($this->ldap->mod_rename($dn,"uid=".$username)){
			$this->username = $username;
			$dn = $this->get_user_rdn();
			$this->ldap->modify($dn,$data);
			log::log_message("Changed username for $old_username to $username.");
		}
		
		// Change username in groups user is a member of
		for($i=0; $i<count($groups);$i++){
			$group = new group($this->ldap,$groups[$i]);
			$group->remove_user($old_username);
			$group->add_user($username);
		}
		
		// Change name of user group
		$group = new group($this->ldap,$old_username);
		$group->set_name($username);
		$group->set_description($username);
		
		// Change username on file-server, mail
		if(__RUN_SHELL_SCRIPTS__){
			$safeusername = escapeshellarg($old_username);
			$safenewusername = escapeshellarg($username);
			exec("sudo ../bin/change_username.pl $safeusername $safenewusername");
		}
		
		return array('RESULT'=>true,
		'MESSAGE'=>'Username changed.',
		'uid'=>$username);
	}
	
	public function set_password($password){
		$passwd = "";
		if(__PASSWD_HASH__=="MD5"){
			$passwd = self::MD5Hash($password);
		}
		if(__PASSWD_HASH__=="SSHA"){
			$passwd = self::SSHAHash($password);
		}
		
		$ntpasswd = self::NTLMHash($password);
		$lmpasswd = self::LMHash($password);
		
		$dn = $this->get_user_rdn();
		$data = array('userPassword'=>$passwd,
		'sambaLMPassword'=>$lmpasswd,
		'sambaNTPassword'=>$ntpasswd,
		'sambaPwdLastSet'=>time());
		if($this->get_password_expiration() != null){
			// If user is not exempt, set password expiration date to one year hence
			$data['facsimiletelephonenumber'] = time()+60*60*24*365;
		}
		if($this->ldap->modify($dn,$data)){
			log::log_message("Changed password for ".$this->get_username());
			return array('RESULT'=>true,
				'MESSAGE'=>'Password successfully set.',
				'uid'=>$this->get_username());
		} else {
			return array('RESULT'=>false,
			'MESSAGE'=>'Set Password Failed: '.$this->ldap->get_error());
		}
	}
	
	public function lock(){
		$filter = "(uid=".$this->get_username().")";
		$attributes = array("userPassword");
		$result = $this->ldap->search($filter, __LDAP_PEOPLE_OU__, $attributes);
		if($result['count']>0){
			$dn = $this->get_user_rdn();
			$data = array('userPassword'=>'!'.$result[0]['userpassword'][0]);
			if($this->ldap->modify($dn,$data)){
				log::log_message("User ".$this->get_username()." locked");
				return array('RESULT'=>true,
					'MESSAGE'=>'User locked.',
					'uid'=>$this->get_username());
			}
		}
		return array('RESULT'=>false,
		'MESSAGE'=>'Lock failed: '.$this->ldap->get_error());
	}
	
	public function unlock(){
		$filter = "(uid=".$this->get_username().")";
		$attributes = array("userPassword");
		$result = $this->ldap->search($filter, __LDAP_PEOPLE_OU__, $attributes);
		if($result['count']>0){
			if(substr($result[0]['userpassword'][0],0,1) == '!'){
				$dn = $this->get_user_rdn();
				$data = array('userPassword'=>substr($result[0]['userpassword'][0],1));
				if($this->ldap->modify($dn,$data)){
					log::log_message("User ".$this->get_username()." unlocked");
					return array('RESULT'=>true,
						'MESSAGE'=>'User unlocked.',
						'uid'=>$this->get_username());
				}
			}
		}
		return array('RESULT'=>false,
		'MESSAGE'=>'Unlock failed: '.$this->ldap->get_error());
	}
	public function islocked(){
		$filter = "(uid=".$this->get_username().")";
		$attributes = array("userPassword");
		$result = $this->ldap->search($filter, __LDAP_PEOPLE_OU__, $attributes);
		if($result['count']>0){
			if(isset($result[0]['userpassword']) && substr($result[0]['userpassword'][0],0,1) == '!'){
				return true;
			} else {
				return false;
			}
		}
		return false;
	}
	
	public function set_expiration($expiration){
		$dn = "uid=".$this->get_username().",".__LDAP_PEOPLE_OU__;
		$data = array("shadowExpire"=>$expiration);
		if($this->ldap->modify($dn,$data)){
			$this->expiration = $expiration;
			log::log_message("Set expiration for ".$this->get_username()." to ".strftime('%m/%d/%Y', $this->get_expiration()));
			return array('RESULT'=>true,
				'MESSAGE'=>'Expiration successfully set.',
				'uid'=>$this->get_username());
		}
	}
	public function cancel_expiration(){
		$dn = $this->get_user_rdn();
		$data = array("shadowexpire"=>array());
		if($this->ldap->mod_del($dn,$data)){
			log::log_message("Cancelled expiration for user ".$this->get_username());
			return array('RESULT'=>true,
				'MESSAGE'=>'Expiration cancelled.',
				'uid'=>$this->get_username());
		}
	}
	
	public function set_password_expiration($expiration){
		$dn = "uid=".$this->get_username().",".__LDAP_PEOPLE_OU__;
		$data = array("facsimiletelephonenumber"=>$expiration);
		if($this->ldap->modify($dn,$data)){
			$this->expiration = $expiration;
			log::log_message("Set password expiration for ".$this->get_username()." to ".strftime('%m/%d/%Y', $this->get_password_expiration()));
			return array('RESULT'=>true,
				'MESSAGE'=>'Password expiration successfully set.',
				'uid'=>$this->get_username());
		}
	}
	public function cancel_password_expiration(){
		$dn = $this->get_user_rdn();
		$data = array("facsimiletelephonenumber"=>array());
		if($this->ldap->mod_del($dn,$data)){
			log::log_message("Cancelled password expiration for user ".$this->get_username());
			return array('RESULT'=>true,
				'MESSAGE'=>'Password expiration cancelled.',
				'uid'=>$this->get_username());
		}
	}

	public function authenticate($password) {
		$rdn = $this->get_user_rdn();
		if ($this->ldap->bind($rdn, $password)){
			if (user::is_ldap_user($this->ldap,$this->username)) {
				$in_admin_group = $this->ldap->search("(memberuid=".$this->username.")", __LDAP_ADMIN_GROUP__);
				if ($in_admin_group['count']>0) {
					return 0;
				} else {
					return 3;
				}
			} else {
				return 2;
			}
		} else {
// 			echo $this->ldap->get_error();
			return 1;
		}
	}
	
	public static function random_password($length=8){
		$passwordchars = 'abcdefghijkmnopqrstuvwxyzABCDEFGHJKLMNPQRSTUVWXYZ23456789!@$%&';
		do {
			$password = "";
			for($i=0; $i<$length; $i++){
				$password .= $passwordchars{self::devurandom_rand(0, strlen($passwordchars)-1)};
			}
		} while ( !(preg_match("/[A-Z]/u", $password)&&preg_match("/[a-z]/u", $password)&&preg_match("/[^A-Za-z]/u", $password)) );
		return $password;
	}

	public static function get_all_users($ldap){
		$users_array = array();
		$filter = "(uid=*)";
		$attributes = array('uid');
		$result = $ldap->search($filter, __LDAP_PEOPLE_OU__, $attributes);
		for ($i=0; $i<$result['count']; $i++) {
			array_push($users_array, $result[$i]['uid'][0]);
		}
		sort($users_array);
		return $users_array;
	}

	public static function get_search_users($ldap,$search,$start=0,$count=30,$sort="username",$asc="true",$userfilter='none'){
		if($search == ""){
			$filter = "(uid=*)";
		} else {
			$filter = "(|(uid=*$search*)(cn=*$search*))";
		}
		$attributes = array("uid","cn","mail","shadowexpire","postaladdress","employeetype",'telexnumber','facsimiletelephonenumber');
		$result = $ldap->search($filter,__LDAP_PEOPLE_OU__,$attributes);
		$users = array();
		$time = time();
		for($i=0; $i<$result['count']; $i++){
			$user = array("username"=>$result[$i]['uid'][0],"name"=>$result[$i]['cn'][0],"email"=>(isset($result[$i]['mail'])?$result[$i]['mail'][0]:''),"shadowexpire"=>(isset($result[$i]['shadowexpire'])?$result[$i]['shadowexpire'][0]:''), "emailforward"=>(isset($result[$i]['postaladdress'])?$result[$i]['postaladdress'][0]:''),"leftcampus"=>(isset($result[$i]['employeetype'])?$result[$i]['employeetype'][0]=='leftcampus':false),"noncampus"=>(isset($result[$i]['employeetype'])?$result[$i]['employeetype'][0]=='noncampus':false),'crashplan'=>(isset($result[$i]['telexnumber'])?$result[$i]['telexnumber'][0]==1:false), "passwordexpired"=>(isset($result[$i]['facsimiletelephonenumber'])?$result[$i]['facsimiletelephonenumber'][0]<$time:false));
			if($userfilter != 'none'){
				if($userfilter == 'expiring'){
					if($user['shadowexpire'] > time()){
						$users[] = $user;
					}
				} else if($userfilter == 'expired'){
					if($user['shadowexpire']!='' && $user['shadowexpire'] <= time()){
						$users[] = $user;
					}
				} else if($userfilter == 'left'){
					if($user['leftcampus']){
						$users[] = $user;
					}
				} else if($userfilter == 'noncampus'){
					if($user['noncampus']){
						$users[] = $user;
					}	
				} else {
					$users[] = $user;
				}
			} else {
				$users[] = $user;
			}
		}

		usort($users,self::sorter($sort,$asc));
		self::$lastSearch = $users;
		return array_slice($users,$start,$count);
	}
	
	public static function get_search_users_count($ldap,$search,$userfilter='none'){
		// TODO this is dumb
		return count(self::$lastSearch);
	}
	
	public static function get_previous_user($ldap,$uid,$search,$sort,$asc,$userfilter='none'){
		if(count(self::$lastSearch) == 0){
			self::get_search_users($ldap,$search,0,30,$sort,$asc,$userfilter);
		}
		for($i=0; $i<count(self::$lastSearch); $i++){
			if(self::$lastSearch[$i]['username'] == $uid){
				if($i==0){
					return null;
				}
				return self::$lastSearch[$i-1]['username'];
			}
		}
	}
	
	public static function get_next_user($ldap,$uid,$search,$sort,$asc,$userfilter='none'){
		if(count(self::$lastSearch) == 0){
			self::get_search_users($ldap,$search,0,30,$sort,$asc,$userfilter);
		}
		for($i=0; $i<count(self::$lastSearch); $i++){
			if(self::$lastSearch[$i]['username'] == $uid){
				if($i==count(self::$lastSearch)-1){
					return null;
				}
				return self::$lastSearch[$i+1]['username'];
			}
		}
	}
	
	public static function is_ldap_user($ldap, $username) {
		$username = trim(rtrim($username));
		$filter = "(uid=" . $username . ")";
		$attributes = array('');
		$result = $ldap->search($filter, __LDAP_PEOPLE_OU__, $attributes);
		if ($result['count']) {
			return true;
		} else {
			return false;
		}
	}
	

	public static function is_ad_user($adldap,$username){
		$filter = "(uid=".$username.")";
		$results = $adldap->search($filter);
		if($results && $results['count']>0){
			return true;
		} else {
			return false;
		}
	}
	
	public function get_leftcampus(){
		return $this->leftcampus;
	}
	
	public function set_leftcampus($leftcampus){
		$dn = "uid=".$this->get_username().",".__LDAP_PEOPLE_OU__;
		$data = array("employeetype"=>($leftcampus?'leftcampus':''));
		if($this->ldap->modify($dn,$data)){
			$this->leftcampus = $leftcampus;
			log::log_message("Set left-campus for ".$this->get_username()." to ".$this->get_leftcampus());
			return array('RESULT'=>true,
				'MESSAGE'=>'Leftcampus successfully set.',
				'uid'=>$this->get_username());
		}
	}
	
	public function get_noncampus(){
		return $this->noncampus;
	}
	public function set_noncampus($noncampus){
		$dn = "uid=".$this->get_username().",".__LDAP_PEOPLE_OU__;
		$data = array("employeetype"=>($noncampus?'noncampus':''));
		if($this->ldap->modify($dn,$data)){
			$this->noncampus = $noncampus;
			log::log_message("Set non-campus for ".$this->get_username()." to ".$this->get_noncampus());
			return array('RESULT'=>true,
				'MESSAGE'=>'Noncampus successfully set.',
				'uid'=>$this->get_username());
		}
	}
	
	public function serializable(){
		$data = array(
			'username'=>$this->username,
			'name'=>$this->name,
			'homeDirectory'=>$this->homeDirectory,
			'loginShell'=>$this->loginShell,
			'email'=>$this->email,
			'givenName'=>$this->givenName,
			'sn'=>$this->sn			
		);
		return $data;
	}

//////////////////Private Functions//////////

	public function load_by_username($username) {
		$filter = "(uid=".$username.")";
		$attributes = array("uid","cn",'sn','givenname',"homeDirectory","loginShell","mail","shadowExpire","creatorsName", "createTimestamp", "modifiersName", "modifyTimestamp","uidnumber",'sambaPwdLastSet','postalAddress','employeetype','telexNumber','facsimiletelephonenumber');
		$result = $this->ldap->search($filter, __LDAP_PEOPLE_OU__, $attributes);
		if($result['count']>0){
			$this->name = $result[0]['cn'][0];
			$this->sn = $result[0]['sn'][0];
			if(isset($result[0]['givenname'])){
				$this->givenName = $result[0]['givenname'][0];
			} else {
				$this->givenName = trim(strstr($this->name,$this->sn,true));
			}
			$this->username = $result[0]['uid'][0];
			$this->homeDirectory = $result[0]['homedirectory'][0];
			$this->loginShell = $result[0]['loginshell'][0];
			$this->email = isset($result[0]['mail'])?$result[0]['mail'][0]:null;
			$this->emailforward = isset($result[0]['postaladdress'][0])?$result[0]['postaladdress'][0]:null; // Yes, postalAddress holds the forwarding email. 
			if(isset($result[0]['shadowexpire'])){
				$this->expiration = $result[0]['shadowexpire'][0];
			}
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
			if(isset($result[0]['sambapwdlastset'])){
				$this->passwordSet = $result[0]['sambapwdlastset'][0];
			}
			if(isset($result[0]['facsimiletelephonenumber'][0])){
				$this->passwordExpiration = $result[0]['facsimiletelephonenumber'][0];
			}
			if(isset($result[0]['employeetype'])){
				$this->leftcampus = ($result[0]['employeetype'][0]=='leftcampus');
				$this->noncampus = ($result[0]['employeetype'][0]=='noncampus');
			}
			if(isset($result[0]['telexnumber'])){
				$this->crashplan = ($result[0]['telexnumber'][0]==1);
			}
		}
	}

	public function get_user_rdn() {
		$filter = "(uid=" . $this->get_username() . ")";
		$attributes = array('dn');
		$result = $this->ldap->search($filter, '', $attributes);
		if (isset($result[0]['dn'])) {
			return $result[0]['dn'];
		}
		else {
			return false;
		}
	}
	
	// returns random int between $min,$max inclusive
	private static function devurandom_rand($min = 0, $max = 0x7FFFFFFF) {
	    $diff = $max - $min;
	    if ($diff < 0 || $diff > 0x7FFFFFFF) {
		throw new RuntimeException("Bad range");
	    }
	    $bytes = mcrypt_create_iv(4, MCRYPT_DEV_URANDOM);
	    if ($bytes === false || strlen($bytes) != 4) {
	        throw new RuntimeException("Unable to get 4 bytes");
	    }
	    $ary = unpack("Nint", $bytes);
	    $val = $ary['int'] & 0x7FFFFFFF;   // 32-bit safe
	    $fp = (float) $val / 2147483647.0; // convert to [0,1]
	    return intval(round($fp * $diff) + $min);
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
	
	private static function MD5Hash($password) {
		$saltchars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789/.';
		$salt = $saltchars[rand(0,63)].$saltchars[rand(0,63)].$saltchars[rand(0,63)].$saltchars[rand(0,63)].$saltchars[rand(0,63)].$saltchars[rand(0,63)].$saltchars[rand(0,63)].$saltchars[rand(0,63)];
		return '{CRYPT}'.Md5Crypt::unix($password,$salt);
	}
	
	private static function SSHAHash($password) {
		$salt = substr(str_shuffle(str_repeat('ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789',4)),0,4);
		return '{SSHA}' . base64_encode(sha1( $password.$salt, TRUE ). $salt);
	}
	
	private static function NTLMHash($cleartext){
		// Convert to UTF16 little endian
		$cleartext = iconv('UTF-8','UTF-16LE',$cleartext);
		//Encrypt with MD4
		$MD4Hash=hash('md4',$cleartext);
		$NTLMHash=strtoupper($MD4Hash);
		return $NTLMHash;
	}

	private static function LMhash($string) {
	    $string = strtoupper(substr($string,0,14));
	
	    $p1 = self::LMhash_DESencrypt(substr($string, 0, 7));
	    $p2 = self::LMhash_DESencrypt(substr($string, 7, 7));
	
	    return strtoupper($p1.$p2);
	}
	
	private static function LMhash_DESencrypt($string) {
	    $key = array();
	    $tmp = array();
	    $len = strlen($string);
	
	    for ($i=0; $i<7; ++$i)
	        $tmp[] = $i < $len ? ord($string[$i]) : 0;
	
	    $key[] = $tmp[0] & 254;
	    $key[] = ($tmp[0] << 7) | ($tmp[1] >> 1);
	    $key[] = ($tmp[1] << 6) | ($tmp[2] >> 2);
	    $key[] = ($tmp[2] << 5) | ($tmp[3] >> 3);
	    $key[] = ($tmp[3] << 4) | ($tmp[4] >> 4);
	    $key[] = ($tmp[4] << 3) | ($tmp[5] >> 5);
	    $key[] = ($tmp[5] << 2) | ($tmp[6] >> 6);
	    $key[] = $tmp[6] << 1;
	   
	    $is = mcrypt_get_iv_size(MCRYPT_DES, MCRYPT_MODE_ECB);
	    $iv = mcrypt_create_iv($is, MCRYPT_RAND);
	    $key0 = "";
	   
	    foreach ($key as $k)
	        $key0 .= chr($k);
	    $crypt = mcrypt_encrypt(MCRYPT_DES, $key0, "KGS!@#$%", MCRYPT_MODE_ECB, $iv);
	
	    return bin2hex($crypt);
	}

}


?>
