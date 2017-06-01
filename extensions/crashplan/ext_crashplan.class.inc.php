<?php
	class ext_crashplan {
		
		private static function has_crashplan($uid){
			$ch = curl_init(__CRASHPLAN_URL__."/api/User?username=$uid");
			curl_setopt($ch,CURLOPT_USERPWD,__CRASHPLAN_USER__.':'.__CRASHPLAN_PASS__);
			curl_setopt($ch,CURLOPT_RETURNTRANSFER,true);
			curl_setopt($ch,CURLOPT_HEADER,false);
			curl_setopt($ch,CURLOPT_SSL_VERIFYPEER,false);
			$jsonstr = curl_exec($ch);
			curl_close($ch);
			$json = json_decode($jsonstr,true);
			if(isset($json['data']) && isset($json['data']['totalCount']) && $json['data']['totalCount']>0){
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
			'MESSAGE'=>"Crashplan archive(s) removed.",
			'uid'=>$uid);
		}
	}
