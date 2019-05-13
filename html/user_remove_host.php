<?php
include_once('includes/main.inc.php');
include_once('includes/session.inc.php');

$uid = requireGetKey('uid');
$user = new User($uid);

$hid = requireGetKey('hid');
$host = new Host($hid);

$error = "";
if ( count($_POST) > 0 ) {
    $result = $user->removeHost($_POST['host']);

    if ( $result['RESULT'] == true ) {
        header("Location: user.php?uid=" . $_POST['uid']);
    } else if ( $result['RESULT'] == false ) {
        $error = $result['MESSAGE'];
    }
}

renderTwigTemplate('user/edit.html.twig', array(
    'siteArea' => 'users',
    'user' => $user,
    'header' => 'Remove from group',
    'inputs' => array(
        array('type' => 'hidden', 'name' => 'Host', 'attr' => 'host', 'value' => $host->getName()),
    ),
    'button' => array('color' => 'danger', 'text' => 'Remove'),
    'error' => $error,
));
