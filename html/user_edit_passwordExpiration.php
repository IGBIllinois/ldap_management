<?php

require_once('includes/main.inc.php');
require_once('includes/session.inc.php');

$uid = requireGetKey('uid', 'User');
$user = new User($uid);

if (isset($_POST['remove'])) {
    $result = $user->removePasswordExpiration();

    if ($result['RESULT'] == true) {
        header("Location: user.php?uid=" . $result['uid']);
        exit();
    } else {
        if ($result['RESULT'] == false) {
            $errors[] = $result['MESSAGE'];
        }
    }
} elseif (isset($_POST['submit'])) {
    if (count($_POST) > 0) {
        if (isset($_POST['passwordExpiration'])) {
            $user->setPasswordExpiration(strtotime($_POST['passwordExpiration']));
            header('location: user.php?uid=' . $user->getUsername());
        }
    }
}

renderTwigTemplate(
    'user/edit.html.twig',
    [
        'siteArea' => 'users',
        'user' => $user,
        'header' => 'Edit Password Expiration',
        'inputs' => [
            [
                'attr' => 'passwordExpiration',
                'name' => 'Password Expiration',
                'type' => 'date',
                'value' => $user->getPasswordExpiration(),
            ],
        ],
        'button' => ['color' => 'warning', 'text' => 'Set expiration'],
        'extraButtons' => [
            ['color' => 'danger', 'text' => 'Remove expiration', 'name' => 'remove'],
        ],
    ]
);