<?php
include_once('includes/main.inc.php');
include_once('includes/session.inc.php');

$errors = array();
if ( count($_POST) > 0 ) {
    $_POST = array_map("trim", $_POST);
    if ( $_POST['name'] == "" ) {
        $errors[] = "Hostname cannot be blank";
    }
    if ( $_POST['ip'] == "" ) {
        $_POST['ip'] = gethostbyname($_POST['name']);
    }

    if ( $errors == "" ) {
        $host = new Host();
        $result = $host->create($_POST['name'], $_POST['ip']);

        if ( $result['RESULT'] == true ) {
            header("Location: host.php?hid=" . $result['hid']);
        } else if ( $result['RESULT'] == false ) {
            $errors[] = $result['MESSAGE'];
        }
    }
}

renderTwigTemplate('edit.html.twig', array(
    'siteArea' => 'hosts',
    'request' => $_POST,
    'header' => 'Add Host',
    'inputs' => array(
        array('attr' => 'name', 'name' => 'Hostname', 'type' => 'text'),
        array('attr' => 'ip', 'name' => 'IP', 'type' => 'text', 'placeholder' => 'Leave blank to set automatically'),
    ),
    'button' => array('color' => 'success', 'text' => 'Add host'),
    'errors' => $errors,
));
