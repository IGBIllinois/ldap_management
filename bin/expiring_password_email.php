<?php
ini_set("display_errors", 1);
chdir(dirname(__FILE__));
set_include_path(get_include_path() . ":../libs");
require_once('../conf/settings.inc.php');
function my_autoloader($class_name) {
    $class_name = str_replace('\\', '/', $class_name);
    if ( file_exists("../libs/" . $class_name . ".class.inc.php") ) {
        require_once $class_name . '.class.inc.php';
    }
}

spl_autoload_register('my_autoloader');

/**
 * @param User   $user
 * @param string $subject
 * @param string $duration
 * @throws \Twig\Error\LoaderError
 * @throws \Twig\Error\RuntimeError
 * @throws \Twig\Error\SyntaxError
 */
function emailmessage($user, $subject, $duration) {
    // Initialize Twig
    require_once '../vendor/autoload.php';
    $loader = new Twig_Loader_Filesystem('../templates');
    $twig = new Twig_Environment($loader, array());

    $to = $user->getEmail();
    $boundary = uniqid('mp');

    $emailMessage = "\r\n\r\n--" . $boundary . "\r\n";
    $emailMessage .= "Content-type: text/plain; charset=utf-8\r\n\r\n";

    $txtTemplate = $twig->load('email/expiring_password.txt.twig');
    $emailMessage .= $txtTemplate->render(
        array(
            'name' => $user->getName(),
            'duration' => $duration,
            'expiration' => $user->getPasswordExpiration()
        ));

    $emailMessage .= "\r\n\r\n--" . $boundary . "\r\n";
    $emailMessage .= "Content-type: text/html; charset=utf-8\r\n\r\n";

    $htmlTemplate = $twig->load('email/expiring_password.html.twig');
    $emailMessage .= $htmlTemplate->render(
        array(
            'name' => $user->getName(),
            'duration' => $duration,
            'expiration' => $user->getPasswordExpiration()
        ));

    $emailMessage .= "\r\n\r\n--" . $boundary . "--";

    $headers = "MIME-Version: 1.0\r\n";
    $headers .= "From: do-not-reply@igb.illinois.edu\r\n";
    $headers .= "Content-Type: multipart/alternative;boundary=" . $boundary . "\r\n";
    // TODO this multipart email thing is confusing and should be broken out into its own function, or maybe use a library
    mail($to, $subject, $emailMessage, $headers, " -f do-not-reply@igb.illinois.edu");

    Log::info(
        "Expiration email sent to " . $user->getUsername() . ".",
        Log::EXP_EMAIL_SENT,
        $user);
}

$sapi_type = php_sapi_name();
// If run from command line
if ( $sapi_type != 'cli' ) {
    echo "Error: This script can only be run from the command line.\n";
} else {
    echo "Analyzing users...";
    // Connect to ldap
    Ldap::init(__LDAP_HOST__, __LDAP_SSL__, __LDAP_PORT__, __LDAP_BASE_DN__);
    Ldap::getInstance()->set_bind_user(__LDAP_BIND_USER__);
    Ldap::getInstance()->set_bind_pass(__LDAP_BIND_PASS__);
    $users = User::all();
    /** @var User[] $onemonth */
    $onemonth = array();
    /** @var User[] $oneweek */
    $oneweek = array();
    /** @var User[] $emailtoday */
    $emailtoday = array();
    $digestmonth = "";
    $digestweek = "";
    $digestexpired = "";
    $userexpdate = date_format(date_add(date_create(), new DateInterval('P6M')), 'U');
    $userexpreason = "Password expired";
    foreach ( $users as $uid ) {
        $user = new User($uid);
        if ( $user->getPasswordExpiration() != null && $user->getEmail() != null ) {
            $expiration = $user->getPasswordExpiration();
            $timetoexp = intval(($expiration - time()) / (60 * 60 * 24));

            if ( $timetoexp == 6 ) {
                $oneweek[] = $user;
                $emailtoday[] = $user;
            } else if ( $timetoexp == 29 ) {
                $onemonth[] = $user;
                $emailtoday[] = $user;
            }

        }
        if ( $user->isPasswordExpired() && !$user->isLocked() ) {
            echo "Password expired for " . $user->getUsername() . "\n";
            $user->lock();
            if ( $user->getExpiration() == null ) { // Don't extend the user's expiration date if they're already set to expire
                $user->setExpiration($userexpdate, $userexpreason);
            }
        }
    }

    if ( count($onemonth) > 0 ) {
        echo "\n==== Expiring in One Month ====\n";
        foreach ( $onemonth as $user ) {
            echo date(
                    'Y-m-d',
                    $user->getPasswordExpiration()) . "\t" . $user->getUsername() . "  \t" . $user->getName() . "\n";
        }
        echo "Sending mail...";
        foreach ( $onemonth as $user ) {
            $digestmonth .= $user->getUsername() . "<br>";
            emailmessage($user, "IGB Password Expiration", "one month");
        }
    } else {
        echo "\nNo users expiring in one month.\n";
    }

    if ( count($oneweek) > 0 ) {
        echo "\n==== Expiring in One Week ====\n";
        foreach ( $oneweek as $user ) {
            echo date(
                    'Y-m-d',
                    $user->getPasswordExpiration()) . "\t" . $user->getUsername() . "  \t" . $user->getName() . "\n";
        }
        echo "Sending mail...";
        foreach ( $oneweek as $user ) {
            $digestweek .= $user->getUsername() . "<br>";
            emailmessage($user, "IGB Password Expiration Final Notice", "one week");
        }
    } else {
        echo "\nNo users expiring in one week.\n";
    }

    if ( count($emailtoday) > 0 ) {
        // Email joe secretly who's going to be emailed today
        $subject = "IGB Password Expiration Notices Pending";
        $to = "jleigh@illinois.edu";
        $emailmessage = "The following users will be emailed password expiration notices today:<br><pre>";
        for ( $i = 0; $i < count($emailtoday); $i++ ) {
            $emailmessage .= $emailtoday[$i]->getUsername() . "\t" . date(
                    'F j, Y',
                    $emailtoday[$i]->getPasswordExpiration()) . "\n";
        }
        $emailmessage .= "</pre><br><br>--IGBLAM";

        $headers = "From: do-not-reply@igb.illinois.edu\r\n";
        $headers .= "Content-Type: text/html; charset=iso-8859-1" . "\r\n";
        mail($to, $subject, $emailmessage, $headers, " -f " . __ADMIN_EMAIL__);
    }

}