<?php

require_once('includes/main.inc.php');
require_once('includes/session.inc.php');

$uid = requireGetKey('uid', 'User');
$user = new User($uid);

$hid = requireGetKey('hid', 'Host');
$host = new Host($hid);

$errors = [];
if (count($_POST) > 0) {
    $result = $user->removeHost($_POST['host']);

    if ($result['RESULT'] == true) {
        header("Location: user.php?uid=" . $_POST['uid']);
    } else {
        if ($result['RESULT'] == false) {
            $errors[] = $result['MESSAGE'];
        }
    }
}

renderTwigTemplate(
    'user/edit.html.twig',
    [
        'siteArea' => 'users',
        'user' => $user,
        'header' => 'Remove from group',
        'inputs' => [
            ['type' => 'hidden', 'name' => 'Host', 'attr' => 'host', 'value' => $host->getName()],
        ],
        'button' => ['color' => 'danger', 'text' => 'Remove'],
        'errors' => $errors,
    ]
);
