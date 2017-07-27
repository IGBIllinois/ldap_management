<?php
	class ext_crashplan {
		
		public static function has_crashplan($uid){
			$ch = curl_init("https://crashplan.igb.illinois.edu:4285/api/User?username=$uid");
			curl_setopt($ch,CURLOPT_USERPWD,'username:password');
			curl_setopt($ch,CURLOPT_RETURNTRANSFER,true);
			curl_setopt($ch,CURLOPT_HEADER,false);
			$jsonstr = curl_exec($ch);
			curl_close($ch);
			$json = json_decode($jsonstr,true);
			if(isset($json['data']) && isset($json['data']['totalCount']) && $json['data']['totalCount']>0 && $json['data']['users'][0]['status']=='Active'){
				return true;
			}
			return false;
		}
		
		public static function crashplan_field($ldap,$uid){
			if(self::has_crashplan($uid)){
				return "Yes";
			} else {
				return "";
			}
		}
		
		public static function crashplan_remove($ldap,$uid){
			if(__RUN_SHELL_SCRIPTS__){
				$safeusername = escapeshellarg($uid);
				exec("sudo ../bin/remove_crashplan.pl $safeusername");
			}
			log::log_message("Crashplan archive removed for ".$uid);
			return array('RESULT'=>true,
			'MESSAGE'=>'Crashplan removed',
			'uid'=>$uid);
		}
	}
