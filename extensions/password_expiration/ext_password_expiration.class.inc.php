<?php
	class ext_password_expiration {
		
		public static function passwordexpiration_icon($ldap,$uid){
			$user = new user($ldap,$uid);
			$exp = $user->get_password_expiration();
			if($exp == null){
				return null;
			}
			if($exp < time()){
				return array("name"=>"key","color"=>"danger");
			}
			if($exp < time()+(60*60*24*30)){
				return array("name"=>"key","color"=>"warning");
			}
			return array("name"=>"key","color"=>"success");
		}
	}