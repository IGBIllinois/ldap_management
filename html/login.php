<?php
include_once 'includes/main.inc.php';

$session = new session(__SESSION_NAME__);
$message = "";
$webpage = $dir = dirname($_SERVER['PHP_SELF']) . "/index.php";
if ($session->get_var('webpage') != "") {
	$webpage = $session->get_var('webpage');
}

$username = "";

if (isset($_POST['login'])) {

	$username = trim($_POST['username']);
	$password = $_POST['password'];

	$error = false;
	if ($username == "") {
		$error = true;
		$message .= html::error_message("Please enter your username.");
	}
	if ($password == "") {
		$error = true;
		$message .= html::error_message("Please enter your password.");
	}
	if ($error == false) {
// 		Ldap::init(__LDAP_HOST__,__LDAP_SSL__,__LDAP_PORT__,__LDAP_BASE_DN__);
		$login_user = new User($username);
		$success = $login_user->authenticate($password);
		if ($success==0) {
			$session_vars = array('login'=>true,
                'username'=>$username,
                'password'=>$password,
                'timeout'=>time(),
                'ipaddress'=>$_SERVER['REMOTE_ADDR']
        	);
            $session->set_session($session_vars);
            Ldap::getInstance()->set_bind_user($login_user->getRDN());
            Ldap::getInstance()->set_bind_pass($password);


			$location = "http://" . $_SERVER['SERVER_NAME'] . $webpage;
        	header("Location: " . $location);
		}
		else {
			$message .= html::error_message("Invalid username or password. Please try again. Error: ".$success);
		}
	}
}

renderTwigTemplate('default/login.html.twig', array(
	'username'=>$username,
	'message'=>$message,
));
