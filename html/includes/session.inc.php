<?php
//////////////////////////////////////////////////
//						
//	session.inc.php				
//						
//	Used to verify the user is		
//	logged in before proceeding		
//						
//	David Slater				
//	May 2009				
//						
//////////////////////////////////////////////////

$session = new session(__SESSION_NAME__);
/** @var User $login_user */
$login_user = null;

//If not logged in
if (!($session->get_var('login'))) {
    $webpage = $_SERVER['PHP_SELF'];
    if ($_SERVER['QUERY_STRING'] != "") {
        $webpage .= "?" . $_SERVER['QUERY_STRING'];
    }
    $session->set_session_var('webpage', $webpage);

    header('Location: login.php');
    die();
} //If session timeout is reached
elseif (time() > $session->get_var('timeout') + __SESSION_TIMEOUT__) {
    header('Location: logout.php');
    die();
} //If IP address is different
elseif ($_SERVER['REMOTE_ADDR'] != $session->get_var('ipaddress')) {
    header('Location: logout.php');
    die();
} else {
    $login_user = new User($session->get_var('username'));
    Ldap::getInstance()->set_bind_user($login_user->getRDN());
    Ldap::getInstance()->set_bind_pass($session->get_var('password'));
    //Reset Timeout
    $session_vars = ['timeout' => time()];
    $session->set_session($session_vars);
}

