<?php

require_once 'includes/main.inc.php';

$session = new session(__SESSION_NAME__);
$errors = "";
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
        $errors[] = "Please enter your username.";
    }
    if ($password == "") {
        $error = true;
        $errors[] = "Please enter your password.";
    }
    if ($error == false) {
// 		Ldap::init(__LDAP_HOST__,__LDAP_SSL__,__LDAP_PORT__,__LDAP_BASE_DN__);
        $login_user = new User($username);
        $success = $login_user->authenticate($password);
        if ($success == 0) {
            $session_vars = [
                'login' => true,
                'username' => $username,
                'password' => $password,
                'timeout' => time(),
                'ipaddress' => $_SERVER['REMOTE_ADDR'],
            ];
            $session->set_session($session_vars);
            Ldap::getInstance()->set_bind_user($login_user->getRDN());
            Ldap::getInstance()->set_bind_pass($password);

            $location = "http://" . $_SERVER['SERVER_NAME'] . $webpage;
            header("Location: " . $location);
        } else {
            $errors[] = "Invalid username or password. Please try again. Error: " . $success;
        }
    }
}

renderTwigTemplate(
    'default/login.html.twig',
    [
        'username' => $username,
        'errors' => $errors,
    ]
);
