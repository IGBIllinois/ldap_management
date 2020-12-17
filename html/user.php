<?php

require_once('includes/main.inc.php');
require_once('includes/session.inc.php');

$username = requireGetKey('uid', 'User');
$user = new User($username);

renderTwigTemplate(
    'user/view.html.twig',
    [
        'siteArea' => 'users',
        'user' => $user,
    ]
);
