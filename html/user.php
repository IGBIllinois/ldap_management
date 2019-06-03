<?php
require_once('includes/main.inc.php');
require_once('includes/session.inc.php');

$username = requireGetKey('uid', 'User');
$user = new User($username);

renderTwigTemplate(
    'user/view.html.twig',
    array(
        'siteArea' => 'users',
        'user' => $user,
    ));
