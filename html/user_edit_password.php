<?php
require_once('includes/main.inc.php');
require_once('includes/session.inc.php');

$uid = requireGetKey('uid');
if ( $uid == "" ) {
    header('location: index.php');
}
$user = new User($uid);

$errors = array();
if ( count($_POST) > 0 ) {
    $_POST = array_map("trim", $_POST);

    if ( $_POST['uid'] == "" ) {
        $errors[] = "Username cannot be blank. Please stop trying to break my web interface.";
    }
    if ( $_POST['password'] == "" ) {
        $errors[] = "Password cannot be blank.";
    } else if ( $_POST['password'] != $_POST['confirmPassword'] ) {
        $errors[] = "Passwords do not match.";
    }

    if ( count($errors) == 0 ) {
        $user = new User($_POST['uid']);
        $result = $user->setPassword($_POST['password']);

        if ( $result['RESULT'] == true ) {
            header("Location: user.php?uid=" . $result['uid']);
        } else if ( $result['RESULT'] == false ) {
            $errors[] = $result['MESSAGE'];
        }
    }
}

renderTwigTemplate(
    'user/edit.html.twig',
    array(
        'siteArea' => 'users',
        'user' => $user,
        'header' => 'Change Password',
        'inputs' => array(
            array('attr' => 'password', 'name' => 'Password', 'type' => 'password'),
            array('attr' => 'confirmPassword', 'name' => 'Confirm Password', 'type' => 'password'),
        ),
        'message' => '<button class="btn btn-light" id="password-button" type="button">Generate Password</button><span id="password-text" class="ml-2"></span>',
        'errors' => $errors,
        'validation' => 'change_password_errors',
        'readyScripts' => "$('#password-button').on('click',function(){generate_password();change_password_errors();});",
    ));
