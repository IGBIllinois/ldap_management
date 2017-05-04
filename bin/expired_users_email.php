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
	// Parse aliases file
/*
	$aliases = array();
	$fh = fopen('aliases','r');
	if($fh){
		while(($line = fgets($fh)) !== false){
			if(preg_match("/^([^#]+?):\\s+([\\w-\\.]+@[\\w-\\.]+,*)+/um", $line,$matches)){
				$aliases[$matches[1]] = $matches[2];
			}
		}
		fclose($fh);
	} else {
		echo "Aliases file not found.\n";
		exit();
	}
*/
	
	// Connect to ldap
	$ldap = new ldap(__LDAP_HOST__,__LDAP_SSL__,__LDAP_PORT__,__LDAP_BASE_DN__);
	$adldap = new ldap(__AD_LDAP_HOST__,false,__AD_LDAP_PORT__,__AD_LDAP_PEOPLE_OU__);
	$users = user::get_all_users($ldap);
	$nocontact = array();
	$igbmail = array();
	$contactinfo = array();
	foreach($users as $uid){
		$user = new user($ldap,$uid);
		if($user->is_expired() && $user->get_email()!=null){
			$contactinfo[] = $user;
		}
	}
	
/*
	echo "==== No contact info available ====\n";
	foreach($nocontact as $user){
		echo $user->get_username()."\t".$user->get_name()."\n";
	}
	echo "\n==== IGB mail only ====\n";
	foreach($igbmail as $user){
		echo $user->get_username()."\t".$user->get_name()."\n";
	}
*/
/*
	echo "\n==== Forwarding info found ====\n";
	foreach($contactinfo as $user){
		echo date('Y-m-d',$user->get_expiration())."\t".$user->get_username()."  \t".$user->get_name()."  \t".$user->get_email()."\n";
	}
*/

	echo "\nAbout to email ".count($contactinfo)." users. Are you sure you want to continue? (y/N)";
	$fh = fopen("php://stdin","r");
	$line = fgets($fh);
	fclose($fh);
	if(strtolower(trim($line)) != 'y'){
		echo "Aborting.\n";
		exit();
	} else {
		echo "Sending mail...";
		foreach($contactinfo as $user){
			$subject = "IGB Account Expiration Notice";
			$to = $user->get_email();
			$emailmessage = $user->get_name().",<br><br>You are receiving this email because your IGB account has expired and will be removed on October 27th, 2016. Please make sure you have no important data on the IGB File Server or Biocluster, as it will be inaccessible after this time. If you believe you are receiving this message in error, or would like to request additional time to remove your data, please contact us at help@igb.illinois.edu. <strong>Do not reply directly to this email.</strong><br/><br/>Computer and Network Resource Group<br/>Institute for Genomic Biology<br/>help@igb.illinois.edu";
	
			$headers = "From: do-not-reply@igb.illinois.edu\r\n";
			$headers .= "Content-Type: text/html; charset=iso-8859-1" . "\r\n";
			mail($to,$subject,$emailmessage,$headers," -f " . __ADMIN_EMAIL__);
		}
		echo "\nDone.\n";
	}

}