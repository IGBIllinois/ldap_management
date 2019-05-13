<?php
include_once('includes/main.inc.php');
include_once('includes/session.inc.php');

$hid = requireGetKey('hid');
$host = new Host($hid);

$error = "";
if ( count($_POST) > 0 ) {
    $_POST = array_map("trim", $_POST);

    if ( $_POST['hid'] == "" ) {
        $error .= "Hostname cannot be blank. Please stop trying to break my web interface.";
    }

    if ( $error == "" ) {
        $host = new Host($_POST['hid']);
        $result = $host->remove();

        if ( $result['RESULT'] == true ) {
            header("Location: list_hosts.php");
        } else if ( $result['RESULT'] == false ) {
            $error = $result['MESSAGE'];
        }
    }
}

renderTwigTemplate('host/edit.html.twig', array(
    'siteArea' => 'hosts',
    'host' => $host,
    'header' => 'Remove Host',
    'message' => 'Are you sure you want to remove this host? Access to this host will be revoked for all users. This operation cannot be undone.',
    'button' => array('color' => 'danger', 'text' => 'Remove'),
));
