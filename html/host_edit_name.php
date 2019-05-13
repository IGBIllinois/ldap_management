<?php
include_once('includes/main.inc.php');
include_once('includes/session.inc.php');

$hid = requireGetKey('hid');
$host = new Host($hid);

$error = "";
if ( count($_POST) > 0 ) {
    $_POST = array_map("trim", $_POST);

    if ( $_POST['name'] == "" ) {
        $error .= html::error_message("Hostname cannot be blank.");
    }

    if ( $error == "" ) {
        $result = $host->setName($_POST['name']);

        if ( $result['RESULT'] == true ) {
            header("Location: host.php?hid=" . $result['hid']);
        } else if ( $result['RESULT'] == false ) {
            $error = $result['MESSAGE'];
        }
    }
}

renderTwigTemplate('host/edit.html.twig', array(
    'siteArea' => 'hosts',
    'host' => $host,
    'header' => 'Edit Hostname',
    'inputs' => array(
        array('attr' => 'name', 'name' => 'New Hostname', 'type' => 'text', 'value' => $host->getName()),
    ),
    'error' => $error,
));
