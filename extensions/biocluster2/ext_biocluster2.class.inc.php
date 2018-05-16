<?php
	class ext_biocluster2 {
		private static function has_biocluster2_access($ldap,$uid){
			$user = new user($ldap,$uid);
			return $user->get_machinerights() && in_array('biocluster2.igb.illinois.edu', $user->get_machinerights());
		}
		
		public static function biocluster2access_field($ldap,$uid){
			if(self::has_biocluster2_access($ldap,$uid)){
				return "<a target='_blank' href='https://biocluster2.igb.illinois.edu/accounting/user.php?username=$uid'>Yes</a>";
			} else {
				return "No";
			}
		}
		public static function biocluster2access_button($ldap,$uid){
			if(self::has_biocluster2_access($ldap,$uid)){
				return '';
			} else {
				return "<a href='edit_user_attribute.php?attr=biocluster2access&uid=$uid' class='btn btn-primary btn-sm pull-right'><span class='fa fa-plus-circle'></span> Give Biocluster2 Access</a>";
			}
		}
		public static function biocluster2access_edit($ldap,$uid,$inputs){
			$user = new user($ldap,$uid);
			$result = $user->set_loginShell('/usr/local/bin/system-specific');
			if($result['RESULT']){
				$result = $user->add_machinerights("biocluster2.igb.illinois.edu");
			}
			if($result['RESULT']){
				$queuegroup = new group($ldap,'biocluster_queue');
				if(!in_array($uid, $queuegroup->get_users())){
					$result = $queuegroup->add_user($uid);
				}
			}
			if(__RUN_SHELL_SCRIPTS__){
				$safeusername = escapeshellarg($uid);
				exec("sudo ../bin/setup_biocluster.pl $safeusername");	
			}
			if($result['RESULT']){
				log::log_message('Biocluster2 access given to user '.$uid);
				return array('RESULT'=>true,
				'MESSAGE'=>'Biocluster2 Access given to user '.$uid,
				'uid'=>$uid);
			} else {
				return array('RESULT'=>false,
				'MESSAGE'=>'Biocluster2 Access not given to user '.$uid.' because: '.$result['MESSAGE'],
				'uid'=>$uid);
			}
		}

	}