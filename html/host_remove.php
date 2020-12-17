<?php

require_once('includes/main.inc.php');
require_once('includes/session.inc.php');

$hid = requireGetKey('hid', 'Host');
$host = new Host($hid);

$errors = [];
if (count($_POST) > 0) {
    $_POST = array_map("trim", $_POST);

    if ($_POST['hid'] == "") {
        $errors[] = "Hostname cannot be blank. Please stop trying to break my web interface.";
    }

    if (count($errors) == 0) {
        $host = new Host($_POST['hid']);
        $result = $host->remove();

        if ($result['RESULT'] == true) {
            header("Location: list_hosts.php");
        } else {
            if ($result['RESULT'] == false) {
                $errors[] = $result['MESSAGE'];
            }
        }
    }
}

renderTwigTemplate(
    'host/edit.html.twig',
    [
        'siteArea' => 'hosts',
        'host' => $host,
        'header' => 'Remove Host',
        'message' => 'Are you sure you want to remove this host? Access to this host will be revoked for all users. This operation cannot be undone.',
        'button' => ['color' => 'danger', 'text' => 'Remove'],
    ]
);
