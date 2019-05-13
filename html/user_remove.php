<?php
include_once('includes/main.inc.php');
include_once('includes/session.inc.php');

$uid = requireGetKey('uid');
$user = new User($uid);

$error = "";
if ( count($_POST) > 0 ) {
    $user = new User($_POST['uid']);
    $result = $user->remove();

    if ( $result['RESULT'] == true ) {
        header("Location: list_users.php");
    } else if ( $result['RESULT'] == false ) {
        $error = $result['MESSAGE'];
    }
}

renderTwigTemplate('user/edit.html.twig', array(
    'siteArea' => 'users',
    'user' => $user,
    'header' => 'Remove User',
    'inputs' => array(),
    'message' => "Are you sure you want to remove this user? This operation cannot be undone.",
    'button' => array('color' => 'danger', 'text' => 'Remove'),
    'error' => $error,
));
