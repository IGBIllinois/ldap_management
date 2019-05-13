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
    $user = new User($_POST['uid']);
    $result = $user->unlock();

    if ( $result['RESULT'] == true ) {
        header("Location: user.php?uid=" . $_POST['uid']);
    } else if ( $result['RESULT'] == false ) {
        $error = $result['MESSAGE'];
    }
}


renderTwigTemplate('user/edit.html.twig', array(
    'siteArea' => 'users',
    'user' => $user,
    'header' => 'Unlock User',
    'inputs' => array(),
    'message' => "Are you sure you want to unlock this user?",
    'button' => array('color' => 'warning', 'text' => 'Unlock'),
    'error' => $error,
));
