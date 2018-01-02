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
		
		public static function password_expired_users($ldap){
			$filter = "(facsimileTelephoneNumber=*)";
			$result = $ldap->search($filter, __LDAP_PEOPLE_OU__, array('facsimileTelephoneNumber'));
			$count = 0;
			$time = time();
			for($i=0; $i<$result['count']; $i++){
				if($result[$i]['facsimiletelephonenumber'][0] < $time){
					$count++;
				}
			}
			return $count;
		}
		public static function leftcampus_users($ldap){
			$filter = "(employeeType=leftcampus)";
			$result = $ldap->search($filter, __LDAP_PEOPLE_OU__, array(''));
			return $result['count'];
		}
		public static function noncampus_users($ldap){
			$filter = "(employeeType=noncampus)";
			$result = $ldap->search($filter, __LDAP_PEOPLE_OU__, array(''));
			return $result['count'];
		}
		public static function classroom_users($ldap){
			$filter = "(employeeType=classroom)";
			$result = $ldap->search($filter, __LDAP_PEOPLE_OU__, array(''));
			return $result['count'];
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