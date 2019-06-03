<?php
ini_set("display_errors", 1);
chdir(dirname(__FILE__));
set_include_path(get_include_path() . ':../libs');
function __autoload($class_name) {
    if ( file_exists("../libs/" . $class_name . ".class.inc.php") ) {
        require_once $class_name . '.class.inc.php';
    }
}

require_once '../conf/settings.inc.php';

/**
 * @param User   $user
 * @param string $subject
 * @param string $duration
 */
function emailmessage($user, $subject, $duration) {
    $to = $user->getEmail();
    // TODO use a twig template
    $emailmessage = $user->getName() . ",<br><br>You are receiving this email because your IGB account will expire and be removed in $duration (" . date(
            'F j, Y',
            $user->getExpiration()) . "). This will not affect your University of Illinois account. Please make sure you have no important data on the IGB File Server or Biocluster, as it will be inaccessible after this time. Connection information for the IGB File Server can be found here <a href='https://help.igb.illinois.edu/File_Server_Access'>https://help.igb.illinois.edu/File_Server_Access</a>, and information for the Biocluster can be found here <a href='https://help.igb.illinois.edu/Biocluster'>https://help.igb.illinois.edu/Biocluster</a>. <br/><br/>If you believe you are receiving this message in error, or would like to request additional time to remove your data, please contact us at help@igb.illinois.edu. <br/><br/>Computer and Network Resource Group<br/>Institute for Genomic Biology<br/>help@igb.illinois.edu";

    $headers = "From: do-not-reply@igb.illinois.edu\r\n";
    $headers .= "Content-Type: text/html; charset=iso-8859-1" . "\r\n";
    $headers .= "Reply-To: help@igb.illinois.edu\r\n";
    mail($to, $subject, $emailmessage, $headers, " -f " . __ADMIN_EMAIL__);
    Log::info("Expiration email sent to " . $user->getUsername() . ".", Log::EXP_EMAIL_SENT, $user);
}

$sapi_type = php_sapi_name();
// If run from command line
if ( $sapi_type != 'cli' ) {
    echo "Error: This script can only be run from the command line.\n";
} else {
    echo "Analyzing users...";
    // Connect to ldap
    Ldap::init(__LDAP_HOST__, __LDAP_SSL__, __LDAP_PORT__, __LDAP_BASE_DN__);
    $users = User::all();
    /** @var User[] $onemonth */
    $onemonth = array();
    /** @var User[] $oneweek */
    $oneweek = array();
    /** @var User[] $emailtomorrow */
    $emailtomorrow = array();
    $digestmonth = "";
    $digestweek = "";
    foreach ( $users as $uid ) {
        $user = new User($uid);
        if ( !($user->isClassroom()) ) {
            if ( $user->isExpiring() && $user->getEmail() != null ) {
                $expiration = $user->getExpiration();
                $timetoexp = intval(($expiration - time()) / (60 * 60 * 24));

                if ( $timetoexp == 6 ) {
                    $oneweek[] = $user;
                } else if ( $timetoexp == 29 ) {
                    $onemonth[] = $user;
                } else if ( $timetoexp == 7 || $timetoexp == 30 ) {
                    $emailtomorrow[] = $user;
                }

            }
        }
    }

    if ( count($onemonth) > 0 ) {
        echo "\n==== Expiring in One Month ====\n";
        foreach ( $onemonth as $user ) {
            echo date('Y-m-d', $user->getExpiration()) . "\t" . $user->getUsername() . "  \t" . $user->getName() . "\n";
        }
        echo "Sending mail...";
        foreach ( $onemonth as $user ) {
            $digestmonth .= $user->getUsername() . "<br>";
            emailmessage($user, "IGB Account Expiration", "one month");
        }
    } else {
        echo "\nNo users expiring in one month.\n";
    }

    if ( count($oneweek) > 0 ) {
        echo "\n==== Expiring in One Week ====\n";
        foreach ( $oneweek as $user ) {
            echo date('Y-m-d', $user->getExpiration()) . "\t" . $user->getUsername() . "  \t" . $user->getName() . "\n";
        }
        echo "Sending mail...";
        foreach ( $oneweek as $user ) {
            $digestweek .= $user->getUsername() . "<br>";
            emailmessage($user, "IGB Account Expiration Final Notice", "one week");
        }
    } else {
        echo "\nNo users expiring in one week.\n";
    }

    if ( count($emailtomorrow) > 0 ) {
        // Email joe secretly who's going to be emailed tomorrow
        $subject = "IGB Account Expiration Notices Pending";
        $to = "jleigh@illinois.edu";
        $emailmessage = "The following users will be emailed expiration notices tomorrow:<br><pre>";
        for ( $i = 0; $i < count($emailtomorrow); $i++ ) {
            $emailmessage .= $emailtomorrow[$i]->getUsername() . "\t" . date(
                    'F j, Y',
                    $emailtomorrow[$i]->getExpiration()) . "\n";
        }
        $emailmessage .= "</pre><br><br>--IGBLAM";

        $headers = "From: do-not-reply@igb.illinois.edu\r\n";
        $headers .= "Content-Type: text/html; charset=iso-8859-1" . "\r\n";
        mail($to, $subject, $emailmessage, $headers, " -f " . __ADMIN_EMAIL__);
    }

}