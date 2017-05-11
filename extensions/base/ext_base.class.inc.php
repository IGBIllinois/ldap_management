<?php
	class ext_base {
		public function username_edit($ldap,$uid,$inputs){
			$user = new user($ldap,$uid);
			$dn = "uid=".$uid.",".__LDAP_PEOPLE_OU__;
			if($uid < 'n'){
				$homesub = 'a-m';
			} else {
				$homesub = 'n-z';
			}
			$groups = $user->get_groups();
			
			// Change dn of user
			$data = array("mail"=>$inputs['username'].__MAIL_SUFFIX__,"homeDirectory"=>"/home/".$homesub."/".$inputs['username']);
			if($ldap->mod_rename($dn,"uid=".$inputs['username'])){
				$dn = "uid=".$inputs['username'].",".__LDAP_PEOPLE_OU__;
				$ldap->modify($dn,$data);
				log::log_message("Changed username for $uid to ".$inputs['username'].".");
			}
			
			// Change username in groups user is a member of
			for($i=0; $i<count($groups);$i++){
				$group = new group($ldap,$groups[$i]);
				$group->remove_user($uid);
				$group->add_user($inputs['username']);
			}
			
			// Change name of user group
			$group = new group($ldap,$uid);
			$group->set_name($inputs['username']);
			$group->set_description($inputs['username']);
			
			// Change username on file-server, mail
			if(__RUN_SHELL_SCRIPTS__){
				$safeusername = escapeshellarg($uid);
				$safenewusername = escapeshellarg($inputs['username']);
				exec("sudo ../bin/change_username.pl $safeusername $safenewusername");
			}
			
			return array('RESULT'=>true,
			'MESSAGE'=>'Username changed.',
			'uid'=>$inputs['username']);
		}
	
		public static function name_edit($ldap,$uid,$inputs){
			if( !isset($inputs['firstname']) || !isset($inputs['lastname']) ){
				return array(
					'RESULT'=>false,
					'MESSAGE'=>"Extension error: Invalid arguments"	
				);
			}
			if( $inputs['firstname']=="" ){
				return array(
					'RESULT'=>false,
					'MESSAGE'=>"First name cannot be blank."
				);
			}
			if( $inputs['lastname']=="" ){
				return array(
					'RESULT'=>false,
					'MESSAGE'=>"Last name cannot be blank."
				);
			}
			$dn = "uid=".$uid.",".__LDAP_PEOPLE_OU__;
			$name = $inputs['firstname']." ".$inputs['lastname'];
			$data = array("cn"=>$name,"sn"=>$inputs['lastname'],"givenName"=>$inputs['firstname'],"gecos"=>$name);
			if($ldap->modify($dn,$data)){
				log::log_message("Changed name for ".$uid." to \"$name\"");
				return array('RESULT'=>true,
				'MESSAGE'=>'Name successfully changed.',
				'uid'=>$uid);
			} else {
				return array('RESULT'=>false,
				'MESSAGE'=>'LDAP Error: '.$ldap->get_error(),
				'uid'=>$uid);
			}
		}
	}