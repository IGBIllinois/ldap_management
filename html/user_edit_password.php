<?php
include_once('includes/main.inc.php');
include_once('includes/session.inc.php');

$uid = requireGetKey('uid');
if ( $uid == "" ) {
    header('location: index.php');
}
$user = new User($uid);

$error = "";
if ( count($_POST) > 0 ) {
    $_POST = array_map("trim", $_POST);

    if ( $_POST['uid'] == "" ) {
        $error .= html::error_message("Username cannot be blank. Please stop trying to break my web interface.");
    }
    if ( $_POST['password'] == "" ) {
        $error .= html::error_message("Password cannot be blank.");
    } else if ( $_POST['password'] != $_POST['confirmPassword'] ) {
        $error .= html::error_message("Passwords do not match.");
    }

    if ( $error == "" ) {
        $user = new User($_POST['uid']);
        $result = $user->setPassword($_POST['password']);

        if ( $result['RESULT'] == true ) {
            header("Location: user.php?uid=" . $result['uid']);
        } else if ( $result['RESULT'] == false ) {
            $error = html::error_message($result['MESSAGE']);
        }
    }
}

renderTwigTemplate('user/edit.html.twig', array(
    'siteArea' => 'users',
    'user' => $user,
    'header' => 'Change Password',
    'inputs' => array(
        array('attr' => 'password', 'name' => 'Password', 'type' => 'password'),
        array('attr' => 'confirmPassword', 'name' => 'Confirm Password', 'type' => 'password'),
    ),
    'message' => '<button class="btn btn-light" id="password-button" type="button">Generate Password</button><span id="password-text" class="ml-2"></span>',
    'error' => $error,
    'validation' => 'change_password_errors',
    'readyScripts' => "$('#password-button').on('click',function(){generate_password();change_password_errors();});",
));
