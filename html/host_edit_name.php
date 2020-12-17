<?php

require_once('includes/main.inc.php');
require_once('includes/session.inc.php');

$hid = requireGetKey('hid', 'Host');
$host = new Host($hid);

$errors = [];
if (count($_POST) > 0) {
    $_POST = array_map("trim", $_POST);

    if ($_POST['name'] == "") {
        $errors[] = "Hostname cannot be blank.";
    }

    if (count($errors) == 0) {
        $result = $host->setName($_POST['name']);

        if ($result['RESULT'] == true) {
            header("Location: host.php?hid=" . $result['hid']);
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
        'header' => 'Edit Hostname',
        'inputs' => [
            ['attr' => 'name', 'name' => 'New Hostname', 'type' => 'text', 'value' => $host->getName()],
        ],
        'errors' => $errors,
    ]
);
