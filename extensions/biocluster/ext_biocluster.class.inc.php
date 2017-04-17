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
				return "<a href='edit_user_attribute.php?attr=bioclusteraccess&uid=$uid' class='btn btn-primary btn-xs pull-right'><span class='glyphicon glyphicon-plus-sign'></span> Give Biocluster Access</a>";
			}
		}

	}