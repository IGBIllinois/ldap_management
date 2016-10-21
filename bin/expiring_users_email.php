<?php
ini_set("display_errors",1);
chdir(dirname(__FILE__));
set_include_path(get_include_path() . ':../libs');
function __autoload($class_name) {
	if(file_exists("../libs/" . $class_name . ".class.inc.php")) {
		require_once $class_name . '.class.inc.php';
	}
}

include_once '../conf/settings.inc.php';

$sapi_type = php_sapi_name();
// If run from command line
if ($sapi_type != 'cli') {
	echo "Error: This script can only be run from the command line.\n";
} else {
	echo "Analyzing users...";
	// Connect to ldap
	$ldap = new ldap(__LDAP_HOST__,__LDAP_SSL__,__LDAP_PORT__,__LDAP_BASE_DN__);
	$adldap = new ldap(__AD_LDAP_HOST__,false,__AD_LDAP_PORT__,__AD_LDAP_PEOPLE_OU__);
	$users = user::get_all_users($ldap);
	$onemonth = array();
	$oneweek = array();
	$other = array();
	$digestmonth = "";
	$digestweek = "";
	$digestexpired = "";
	foreach($users as $uid){
		$user = new user($ldap,$uid);
		if($user->is_expiring() && $user->get_email()!=null){
			$expiration = $user->get_expiration();
			$timetoexp = intval(($expiration-time())/(60*60*24));
			
			if( $timetoexp == 6 ){
				$oneweek[] = $user;
			} else if( $timetoexp == 29 ){
				$onemonth[] = $user;
			}

		} else if ($user->is_expired()){
			$expiration = $user->get_expiration();
			$timetoexp = intval(($expiration-time())/(60*60*24));
			
			if($timetoexp == 0){
				$digestexpired .= $user->get_username()."<br>";	
			}
		}
	}
	
	if(count($onemonth)>0){
		echo "\n==== Expiring in One Month ====\n";
		foreach($onemonth as $user){
			echo $user->get_username()."\t".$user->get_name()."\n";
		}
		echo "Sending mail...";
		foreach($onemonth as $user){
			$digestmonth .= $user->get_username()."<br>";
			$subject = "IGB Account Expiration Notice";
			$to = $user->get_email();
// 			$to = "jleigh@illinois.edu";
			$emailmessage = $user->get_name().",<br><br>You are receiving this email because your IGB account will expire and be removed in one month (".date('F j, Y', $user->get_expiration())."). Please make sure you have no important data on the IGB File Server or Biocluster, as it will be inaccessible after this time. If you believe you are receiving this message in error, or would like to request additional time to remove your data, please contact us at help@igb.illinois.edu. <strong>Do not reply directly to this email.</strong><br/><br/>Computer and Network Resource Group<br/>Institute for Genomic Biology<br/>help@igb.illinois.edu";
	
			$headers = "From: do-not-reply@igb.illinois.edu\r\n";
			$headers .= "Content-Type: text/html; charset=iso-8859-1" . "\r\n";
			mail($to,$subject,$emailmessage,$headers," -f " . __ADMIN_EMAIL__);
		}
	} else {
		echo "\nNo users expiring in one month.\n";
	}
	
	if(count($oneweek)>0){
		echo "\n==== Expiring in One Week ====\n";
		foreach($oneweek as $user){
			echo date('Y-m-d',$user->get_expiration())."\t".$user->get_username()."  \t".$user->get_name()."\n";
		}
		echo "Sending mail...";
		foreach($oneweek as $user){
			$digestweek .= $user->get_username()."<br>";
			
			$subject = "IGB Account Expiration Final Notice";
			$to = $user->get_email();
// 			$to = "jleigh@illinois.edu";
			$emailmessage = $user->get_name().",<br><br>You are receiving this email because your IGB account will expire and be removed in one week (".date('F j, Y', $user->get_expiration())."). Please make sure you have no important data on the IGB File Server or Biocluster, as it will be inaccessible after this time. If you believe you are receiving this message in error, or would like to request additional time to remove your data, please contact us at help@igb.illinois.edu. <strong>Do not reply directly to this email.</strong><br/><br/>Computer and Network Resource Group<br/>Institute for Genomic Biology<br/>help@igb.illinois.edu";
	
			$headers = "From: do-not-reply@igb.illinois.edu\r\n";
			$headers .= "Content-Type: text/html; charset=iso-8859-1" . "\r\n";
			mail($to,$subject,$emailmessage,$headers," -f " . __ADMIN_EMAIL__);
		}
	} else {
		echo "\nNo users expiring in one week.\n";
	}
	
	if(strlen($digestmonth)>0 || strlen($digestweek)>0 || strlen($digestexpired)>0){
		// Send a digest to help
		$subject = "IGB Account Expiration Notices Sent";
		$to = "help@igb.illinois.edu";
// 		$to = "jleigh@illinois.edu";
		$emailmessage = "The following users were sent notice that their account will expire in one month:<br><br>".$digestmonth."<br>The following users were sent notice that their account will expire in one week:<br><br>".$digestweek."<br>The following users expired today:<br><br>".$digestexpired;

		$headers = "From: do-not-reply@igb.illinois.edu\r\n";
		$headers .= "Content-Type: text/html; charset=iso-8859-1" . "\r\n";
		mail($to,$subject,$emailmessage,$headers," -f " . __ADMIN_EMAIL__);
		echo "\nDone.\n";
	}

}