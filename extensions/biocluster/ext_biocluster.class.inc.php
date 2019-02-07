<?php
	class ext_biocluster {
		private static function has_biocluster_access($ldap,$uid){
			$user = new user($ldap,$uid);
			return $user->get_machinerights() && in_array('biocluster.igb.illinois.edu', $user->get_machinerights());
		}
		
		public static function bioclusteraccess_field($ldap,$uid){
			if(self::has_biocluster_access($ldap,$uid)){
				return "<a target='_blank' href='https://biocluster.igb.illinois.edu/accounting/user.php?username=$uid'>Yes</a>";
			} else {
				return "No";
			}
		}
		public static function bioclusteraccess_button($ldap,$uid){
			if(self::has_biocluster_access($ldap,$uid)){
				return '';
			} else {
				return "<a href='edit_user_attribute.php?attr=bioclusteraccess&uid=$uid' class='btn btn-primary btn-sm float-right'><span class='fa fa-plus-circle'></span> Give Biocluster Access</a>";
			}
		}
		public static function bioclusteraccess_edit($ldap,$uid,$inputs){
			$user = new user($ldap,$uid);
			$result = $user->set_loginShell('/usr/local/bin/system-specific');
			if($result['RESULT']){
				$result = $user->add_machinerights("biocluster.igb.illinois.edu");
			}
			if($result['RESULT']){
				$queuegroup = new group($ldap,'biocluster_queue');
				if(!in_array($uid, $queuegroup->get_users())){
					$result = $queuegroup->add_user($uid);
				}
			}
			if($result['RESULT']){
				log::log_message('Biocluster access given to user '.$uid);
				return array('RESULT'=>true,
				'MESSAGE'=>'Biocluster Access given to user '.$uid,
				'uid'=>$uid);
			} else {
				return array('RESULT'=>false,
				'MESSAGE'=>'Biocluster Access not given to user '.$uid.' because: '.$result['MESSAGE'],
				'uid'=>$uid);
			}
		}

	}