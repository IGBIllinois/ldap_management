<?php
	class statistics {
		
		public static function users($ldap){
			$filter = "(uid=*)";
			$result = $ldap->search($filter,__LDAP_PEOPLE_OU__,array(''));
			return $result['count'];
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