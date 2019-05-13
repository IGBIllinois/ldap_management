<?php
include_once('includes/main.inc.php');
include_once('includes/session.inc.php');

$hid = requireGetKey('hid');
$host = new Host($hid);
$uid = requireGetKey('uid');

$error = "";
if ( count($_POST) > 0 ) {
    $user = new User($_POST['username']);
    $result = $user->removeHost($hid);

    if ( $result['RESULT'] == true ) {
        header("Location: host.php?hid=" . $hid);
    } else if ( $result['RESULT'] == false ) {
        $error = $result['MESSAGE'];
    }

}

renderTwigTemplate('host/edit.html.twig', array(
    'siteArea' => 'hosts',
    'host' => $host,
    'header' => 'Remove Host Access',
    'inputs' => array(
        array('type' => 'hidden', 'name' => 'User', 'attr' => 'username', 'value' => $uid),
    ),
    'button' => array('color' => 'danger', 'text' => 'Remove'),
    'error' => $error,
));