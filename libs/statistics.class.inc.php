<?php
	class statistics {
		
		public static function users($ldap){
			$result = user::get_all_users($ldap);
			return count($result);
		}
		public static function expiring_users($ldap){
			$filter = "(shadowExpire=*)";
			$result = $ldap->search($filter, __LDAP_PEOPLE_OU__, array(''));
			return $result['count'];
		}
		
		public static function expired_users($ldap){
			$filter = "(shadowExpire=*)";
			$result = $ldap->search($filter, __LDAP_PEOPLE_OU__, array('shadowExpire'));
			$count = 0;
			$time = time();
			for($i=0;$i<$result['count'];$i++){
				if($result[$i]['shadowexpire'][0]<$time){
					$count++;
				}
			}
			return $count;
		}
		public static function non_ad_users($ldap,$adldap){
			$users = user::get_all_users($ldap);
			$count = 0;
			for($i=0; $i<count($users); $i++){
				if(!user::is_ad_user($adldap,$users[$i])){
					$count++;
				}
			}
			return $count;
		}
		public static function groups($ldap){
			$filter = "(cn=*)";
			$result = $ldap->search($filter,__LDAP_GROUP_OU__, array(''));
			return $result['count'];
		}
		public static function empty_groups($ldap){
			$filter = "(!(memberUid=*))";
			$result = $ldap->search($filter,__LDAP_GROUP_OU__,array(''));
			return $result['count'];
		}
	}