<?php
class user {

	////////////////Private Variables//////////

	private $username;
	private $name;

	private $ldap;
	private $uidnumber;
	private $email;
	private $homeDirectory;
	private $machinerights = null;
	private $groups = null;
	private $loginShell;
	private $expiration = null;
	
	private $creator;
	private $createTime;
	private $modifier;
	private $modifyTime;

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
		} elseif ($this->ldap->is_ldap_group($username)) {
			$error = true;
			$message = html::error_message("Username already exists as group");
		} elseif ($name == "") {
			$error = true;
			$message .= html::error_message("Please enter a name.");
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
			// Find first unused uidNumber,gidNumber
			$uidnumber = 20000;
			$users = $this->ldap->search("(uid=*)", __LDAP_BASE_DN__, array('uid', 'uidNumber', 'gidNumber'));
			$uidnumbers = array();
			$gidnumbers = array();
			for($i=0; $i<$users['count']; $i++){
				// TODO check if $users[$i]['uidnumber'] exists first
				if(isset($users[$i]['uidnumber'])){
					$uidnumbers[] = $users[$i]['uidnumber'][0];
					$gidnumbers[] = $users[$i]['gidnumber'][0];
				}
			}
			$uidgidstart = 1000;
			$uidnumber = 0;
			$gidnumber = -1;
			while($uidnumber != $gidnumber){
				$cleanpass=0;
				foreach($uidnumbers as $number){
					if($number==$uidgidstart){
						$uidgidstart++;
						$cleanpass++;
					}
				}
				foreach($gidnumbers as $number){
					if($number==$uidgidstart){
						$uidgidstart++;
						$cleanpass++;
					}
				}
				if(!($cleanpass)){
					$uidnumber = $uidgidstart;
					$gidnumber = $uidgidstart;
				}
			}
			
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
				'SambaPwdLastSet' => time()
			);
			$this->ldap->add($dn, $data);
			$this->load_by_username($username);
			
			// Add LDAP group
			$group = new group($this->ldap);
			$group->create_user_group($username,$username,$gidnumber);
			
			// Run script to add user to file-server, mail
			if(__RUN_SHELL_SCRIPTS__){
				$safeusername = escapeshellarg($username);
				// TODO script didnt work
				exec("sudo ../bin/add_user.pl $safeusername".$options);
			}
			
			log::log_message("Added user ".$this->get_username()." (".$this->get_name().")");
			return array('RESULT'=>true,
				'MESSAGE'=>'User successfully added.',
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

	public function get_username() {
		return $this->username;
	}

	public function get_email() {
		return $this->email;
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

	public function get_machinerights() {
		if ($this->machinerights == null) {
			$this->machinerights = $this->ldap->get_machinerights($this->username);
			unset($this->machinerights['count']);
		}
		return $this->machinerights;
	}


	public function get_groups() {
		if ($this->groups == null) {
			$this->groups = $this->ldap->get_user_groups($this->username);
		}
		return $this->groups;
	}

	public function add_machinerights($host) {
		if($this->ldap->is_ldap_host($host) && ($this->get_machinerights() || !in_array($host, $this->get_machinerights()))){
			$dn = "uid=".$this->get_username().",".__LDAP_PEOPLE_OU__;
			$data = array("host"=>$host);
			if($this->ldap->mod_add($dn,$data)){
				log::log_message("Gave host access for ".$host." to ".$this->get_username());
				return array('RESULT'=>true,
				'MESSAGE'=>'Machine rights successfully added.',
				'uid'=>$this->get_username());
			}
		}
	}
	
	public function remove_machinerights($host) {
		if($this->ldap->is_ldap_host($host) || ($this->get_machinerights() && in_array($host, $this->get_machinerights()))){
			$dn = "uid=".$this->get_username().",".__LDAP_PEOPLE_OU__;
			$data = array("host"=>$host);
			if($this->ldap->mod_del($dn,$data)){
				log::log_message("Removed host access to ".$_POST['host']." from ".$this->get_username());
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
		if($this->ldap->modify($dn,$data)){
			log::log_message("Changed password for ".$this->get_username());
			return array('RESULT'=>true,
				'MESSAGE'=>'Password successfully set.',
				'uid'=>$this->get_username());
		} else {
			return array('RESULT'=>false,
			'MESSAGE'=>'Set Password Failed: '.ldap_error($this->ldap->get_resource()));
		}
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
	
	public function give_biocluster_access(){
		$result = $this->set_loginShell('/usr/local/bin/system-specific');
		if($result['RESULT']){
			$result = $this->add_machinerights("biocluster.igb.illinois.edu");
		}
		if($result['RESULT']){
			$result['MESSAGE'] == "Biocluster access given";
		}
		return $result;
	}

	public function authenticate($password) {
		$rdn = $this->get_user_rdn();
		var_dump($rdn,$password);
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
			echo ldap_error($this->ldap->get_resource());
			return 1;
		}
	}
	
	public static function random_password($length=8){
		$passwordchars = 'abcdefghijkmnopqrstuvwxyzABCDEFGHJKLMNPQRSTUVWXYZ23456789!@$%&';
		$password = "";
		for($i=0; $i<$length; $i++){
			$password .= $passwordchars{self::devurandom_rand(0, strlen($passwordchars)-1)};
		}
		return $password;
	}

	public static function get_search_users($ldap,$search,$start,$count,$sort="username",$asc="true"){
		if($search == ""){
			$filter = "(uid=*)";
		} else {
			$filter = "(|(uid=*$search*)(cn=*$search*))";
		}
		$attributes = array("uid","cn","mail");
		$result = $ldap->search($filter,__LDAP_PEOPLE_OU__,$attributes);
		$users = array();
		for($i=0; $i<$result['count']; $i++){
			$user = array("username"=>$result[$i]['uid'][0],"name"=>$result[$i]['cn'][0],"email"=>(isset($result[$i]['mail'])?$result[$i]['mail'][0]:''));
			$users[] = $user;
		}
		usort($users,self::sorter($sort,$asc));
		return array_slice($users,$start,$count);
	}
	
	public static function get_search_users_count($ldap,$search){
		if($search == ""){
			$filter = "(uid=*)";
		} else {
			$filter = "(|(uid=*$search*)(cn=*$search*))";
		}
		$attributes = array("uid","cn","mail");
		$result = $ldap->search($filter,__LDAP_PEOPLE_OU__,$attributes,1);
		return $result['count'];
	}
	
	public static function is_ldap_user($ldap, $username) {
		$username = trim(rtrim($username));
		$filter = "(uid=" . $username . ")";
		$attributes = array('');
		$result = $ldap->search($filter, "", $attributes);
		if ($result['count']) {
			return true;
		} else {
			return false;
		}
	}

//////////////////Private Functions//////////

	public function load_by_username($username) {
		$filter = "(uid=".$username.")";
		$attributes = array("uid","cn","homeDirectory","loginShell","mail","shadowExpire","creatorsName", "createTimestamp", "modifiersName", "modifyTimestamp","uidnumber");
		$result = $this->ldap->search($filter, __LDAP_PEOPLE_OU__, $attributes);
		if($result['count']>0){
			$this->name = $result[0]['cn'][0];
			$this->username = $result[0]['uid'][0];
			$this->homeDirectory = $result[0]['homedirectory'][0];
			$this->loginShell = $result[0]['loginshell'][0];
			$this->email = $result[0]['mail'][0];
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
