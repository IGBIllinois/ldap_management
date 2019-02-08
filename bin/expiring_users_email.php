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

function emailmessage($user, $subject, $duration){
	$to = $user->get_email();
	$emailmessage = $user->get_name().",<br><br>You are receiving this email because your IGB account will expire and be removed in $duration (".date('F j, Y', $user->get_expiration())."). This will not affect your University of Illinois account. Please make sure you have no important data on the IGB File Server or Biocluster, as it will be inaccessible after this time. Connection information for the IGB File Server can be found here <a href='https://help.igb.illinois.edu/File_Server_Access'>https://help.igb.illinois.edu/File_Server_Access</a>, and information for the Biocluster can be found here <a href='https://help.igb.illinois.edu/Biocluster'>https://help.igb.illinois.edu/Biocluster</a>. <br/><br/>If you believe you are receiving this message in error, or would like to request additional time to remove your data, please contact us at help@igb.illinois.edu. <br/><br/>Computer and Network Resource Group<br/>Institute for Genomic Biology<br/>help@igb.illinois.edu";

	$headers = "From: do-not-reply@igb.illinois.edu\r\n";
	$headers .= "Content-Type: text/html; charset=iso-8859-1" . "\r\n";
	$headers .= "Reply-To: help@igb.illinois.edu\r\n";
	mail($to,$subject,$emailmessage,$headers," -f " . __ADMIN_EMAIL__);
	log::log_message("Expiration email sent to ".$user->get_username().".");
}

$sapi_type = php_sapi_name();
// If run from command line
if ($sapi_type != 'cli') {
	echo "Error: This script can only be run from the command line.\n";
} else {
	echo "Analyzing users...";
	// Connect to ldap
	$ldap = new ldap(__LDAP_HOST__,__LDAP_SSL__,__LDAP_PORT__,__LDAP_BASE_DN__);
	$users = user::get_all_users($ldap);
	$onemonth = array();
	$oneweek = array();
	$emailtomorrow = array();
	$digestmonth = "";
	$digestweek = "";
	foreach($users as $uid){
		$user = new user($ldap,$uid);
		if(!($user->get_classroom())){
			if($user->is_expiring() && $user->get_email()!=null){
				$expiration = $user->get_expiration();
				$timetoexp = intval(($expiration-time())/(60*60*24));
	
				if( $timetoexp == 6 ){
					$oneweek[] = $user;
				} else if( $timetoexp == 29 ){
					$onemonth[] = $user;
				} else if( $timetoexp == 7 || $timetoexp == 30 ){
					$emailtomorrow[] = $user;
				}
	
			}
		}
	}
	
	if(count($onemonth)>0){
		echo "\n==== Expiring in One Month ====\n";
		foreach($onemonth as $user){
			echo date('Y-m-d',$user->get_expiration())."\t".$user->get_username()."  \t".$user->get_name()."\n";
		}
		echo "Sending mail...";
		foreach($onemonth as $user){
			$digestmonth .= $user->get_username()."<br>";
			emailmessage($user, "IGB Account Expiration", "one month");
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
			emailmessage($user, "IGB Account Expiration Final Notice", "one week");
		}
	} else {
		echo "\nNo users expiring in one week.\n";
	}
	
	if(count($emailtomorrow)>0){
		// Email joe secretly who's going to be emailed tomorrow
		$subject = "IGB Account Expiration Notices Pending";
		$to = "jleigh@illinois.edu";
		$emailmessage = "The following users will be emailed expiration notices tomorrow:<br><pre>";
		for($i=0; $i<count($emailtomorrow); $i++){
			$emailmessage .= $emailtomorrow[$i]->get_username()."\t".date('F j, Y', $emailtomorrow[$i]->get_expiration())."\n";
		}
		$emailmessage .= "</pre><br><br>--IGBLAM";
		
		$headers = "From: do-not-reply@igb.illinois.edu\r\n";
		$headers .= "Content-Type: text/html; charset=iso-8859-1" . "\r\n";
		mail($to,$subject,$emailmessage,$headers," -f " . __ADMIN_EMAIL__);
	}

}