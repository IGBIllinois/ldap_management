<?php

require_once('includes/main.inc.php');
require_once('includes/session.inc.php');

$uid = requireGetKey('uid', 'User');
$user = new User($uid);

if (count($_POST) > 0) {
    if (isset($_POST['description'])) {
        $user->setDescription($_POST['description']);
        header('location: user.php?uid=' . $user->getUsername());
    }
}

renderTwigTemplate(
    'user/edit.html.twig',
    [
        'siteArea' => 'users',
        'user' => $user,
        'header' => 'Edit Notes',
        'inputs' => [
            ['attr' => 'description', 'name' => 'Notes', 'type' => 'text', 'value' => $user->getDescription()],
        ],
    ]
);