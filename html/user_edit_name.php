<?php

require_once('includes/main.inc.php');
require_once('includes/session.inc.php');

$uid = requireGetKey('uid', 'User');
$user = new User($uid);

if (count($_POST) > 0) {
    if (isset($_POST['firstName']) && isset($_POST['lastName'])) {
        $user->setName($_POST['firstName'], $_POST['lastName']);
        header('location: user.php?uid=' . $user->getUsername());
    }
}

renderTwigTemplate(
    'user/edit.html.twig',
    [
        'siteArea' => 'users',
        'user' => $user,
        'header' => 'Edit Name',
        'inputs' => [
            ['attr' => 'firstName', 'name' => 'First Name', 'type' => 'text', 'value' => $user->getFirstName()],
            ['attr' => 'lastName', 'name' => 'Last Name', 'type' => 'text', 'value' => $user->getLastName()],
        ],
    ]
);