<?php
include_once('includes/main.inc.php');
include_once('includes/session.inc.php');

$uid = requireGetKey('uid');
$user = new User($uid);

$error = "";
if ( count($_POST) > 0 ) {
    $user = new User($_POST['uid']);
    $result = $user->lock();

    if ( $result['RESULT'] == true ) {
        header("Location: user.php?uid=" . $_POST['uid']);
    } else if ( $result['RESULT'] == false ) {
        $error = $result['MESSAGE'];
    }
}

renderTwigTemplate('user/edit.html.twig', array(
    'siteArea' => 'users',
    'user' => $user,
    'header' => 'Lock User',
    'inputs' => array(),
    'message' => "The user will not be able to log in or change their password until their account is unlocked.",
    'button' => array('color' => 'danger', 'text' => 'Lock'),
    'error' => $error,
));
