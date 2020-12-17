<?php

require_once('includes/main.inc.php');
require_once('includes/session.inc.php');

$hid = requireGetKey('hid', 'Host');
$host = new Host($hid);
$uid = requireGetKey('uid', 'User');

$errors = [];
if (count($_POST) > 0) {
    $user = new User($_POST['username']);
    $result = $user->removeHost($hid);

    if ($result['RESULT'] == true) {
        header("Location: host.php?hid=" . $hid);
    } else {
        if ($result['RESULT'] == false) {
            $errors[] = $result['MESSAGE'];
        }
    }
}

renderTwigTemplate(
    'host/edit.html.twig',
    [
        'siteArea' => 'hosts',
        'host' => $host,
        'header' => 'Remove Host Access',
        'inputs' => [
            ['type' => 'hidden', 'name' => 'User', 'attr' => 'username', 'value' => $uid],
        ],
        'button' => ['color' => 'danger', 'text' => 'Remove'],
        'errors' => $errors,
    ]
);