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
	$emailmessage = $user->get_name().",<br><br>You are receiving this email because your IGB account password will expire in $duration (".date('F j, Y', $user->get_password_expiration())."). This will not affect your University of Illinois account password.<br><br> To change your password, go to https://illinoisauth.igb.illinois.edu/password/ and log in with either your current IGB password or your University of Illinois AD password. <br><br>If you do not change your password before ".date('F j, Y', $user->get_password_expiration()).", you will not be able to log into IGB services such as IGB Wi-Fi, the IGB file-server, and the Biocluster. <br/><br/>If you have any questions, please contact us at help@igb.illinois.edu. <br/><br/>Computer and Network Resource Group<br/>Institute for Genomic Biology<br/>help@igb.illinois.edu";

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
	$ldap->set_bind_user(__LDAP_BIND_USER__);
	$ldap->set_bind_pass(__LDAP_BIND_PASS__);
	$users = user::get_all_users($ldap);
	$onemonth = array();
	$oneweek = array();
	$emailtomorrow = array();
	$digestmonth = "";
	$digestweek = "";
	$digestexpired = "";
	foreach($users as $uid){
		$user = new user($ldap,$uid);
		if($user->get_password_expiration()!=null && $user->get_email()!=null){
			$expiration = $user->get_password_expiration();
			$timetoexp = intval(($expiration-time())/(60*60*24));

			if( $timetoexp == 6 ){
				$oneweek[] = $user;
			} else if( $timetoexp == 29 ){
				$onemonth[] = $user;
			} else if( $timetoexp == 7 || $timetoexp == 30 ){
				$emailtomorrow[] = $user;
			}

		} 
		if ($user->is_password_expired() && !$user->is_locked()){
			echo "Password expired for ".$user->get_username()."\n";
			$user->lock();
		}
	}
	
	if(count($onemonth)>0){
		echo "\n==== Expiring in One Month ====\n";
		foreach($onemonth as $user){
			echo date('Y-m-d',$user->get_password_expiration())."\t".$user->get_username()."  \t".$user->get_name()."\n";
		}
		echo "Sending mail...";
		foreach($onemonth as $user){
			$digestmonth .= $user->get_username()."<br>";
  			emailmessage($user, "IGB Password Expiration", "one month");
		}
	} else {
		echo "\nNo users expiring in one month.\n";
	}
	
	if(count($oneweek)>0){
		echo "\n==== Expiring in One Week ====\n";
		foreach($oneweek as $user){
			echo date('Y-m-d',$user->get_password_expiration())."\t".$user->get_username()."  \t".$user->get_name()."\n";
		}
		echo "Sending mail...";
		foreach($oneweek as $user){
			$digestweek .= $user->get_username()."<br>";
  			emailmessage($user, "IGB Password Expiration Final Notice", "one week");
		}
	} else {
		echo "\nNo users expiring in one week.\n";
	}
	
	if(count($emailtomorrow)>0){
		// Email joe secretly who's going to be emailed tomorrow
		$subject = "IGB Account Expiration Notices Pending";
		$to = "jleigh@illinois.edu";
		$emailmessage = "The following users will be emailed password expiration notices tomorrow:<br><pre>";
		for($i=0; $i<count($emailtomorrow); $i++){
			$emailmessage .= $emailtomorrow[$i]->get_username()."\t".date('F j, Y', $emailtomorrow[$i]->get_password_expiration())."\n";
		}
		$emailmessage .= "</pre><br><br>--IGBLAM";
		
		$headers = "From: do-not-reply@igb.illinois.edu\r\n";
		$headers .= "Content-Type: text/html; charset=iso-8859-1" . "\r\n";
		mail($to,$subject,$emailmessage,$headers," -f " . __ADMIN_EMAIL__);
	}

}