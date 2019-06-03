<?php
require_once('includes/main.inc.php');
require_once('includes/session.inc.php');

$uid = requireGetKey('uid', 'User');
$user = new User($uid);

$errors = array();
if ( count($_POST) > 0 ) {
    $user = new User($_POST['uid']);
    $result = $user->remove();

    if ( $result['RESULT'] == true ) {
        header("Location: list_users.php");
    } else if ( $result['RESULT'] == false ) {
        $errors[] = $result['MESSAGE'];
    }
}

renderTwigTemplate(
    'user/edit.html.twig',
    array(
        'siteArea' => 'users',
        'user' => $user,
        'header' => 'Remove User',
        'inputs' => array(),
        'message' => "Are you sure you want to remove this user? This operation cannot be undone.",
        'button' => array('color' => 'danger', 'text' => 'Remove'),
        'errors' => $errors,
    ));
