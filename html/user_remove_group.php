<?php

require_once('includes/main.inc.php');
require_once('includes/session.inc.php');

$uid = requireGetKey('uid', 'User');
$user = new User($uid);

$gid = requireGetKey('gid', 'Group');
$group = new Group($gid);

$errors = [];
if (count($_POST) > 0) {
    $group = new Group($_POST['gid']);
    $result = $group->removeUser($_POST['uid']);

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
            ['type' => 'hidden', 'name' => 'Group', 'attr' => 'gid', 'value' => $group->getName()],
        ],
        'button' => ['color' => 'danger', 'text' => 'Remove'],
        'errors' => $errors,
    ]
);
