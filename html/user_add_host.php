<?php

require_once('includes/main.inc.php');
require_once('includes/session.inc.php');

$uid = requireGetKey('uid', 'User');
$user = new User($uid);

// Process POST data
$errors = [];
if (count($_POST) > 0) {
    $result = $user->addHost($_POST['host']);

    if ($result['RESULT'] == true) {
        header("Location: user.php?uid=" . $_POST['uid']);
    } else {
        if ($result['RESULT'] == false) {
            $errors[] = $result['MESSAGE'];
        }
    }
}

// Set up template
$allhosts = Host::all();
$userhosts = $user->getHosts();
$hosts = [];
foreach ($allhosts as $host) {
    if (!in_array($host->getName(), $userhosts)) {
        $hosts[] = $host->getName();
    }
}

renderTwigTemplate(
    'user/edit.html.twig',
    [
        'siteArea' => 'users',
        'user' => $user,
        'header' => 'Add host access',
        'inputs' => [
            ['type' => 'select', 'name' => 'Host', 'attr' => 'host', 'options' => $hosts],
        ],
        'button' => ['color' => 'success', 'text' => 'Add'],
        'errors' => $errors,
    ]
);