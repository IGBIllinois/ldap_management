<?php
include_once('includes/main.inc.php');
include_once('includes/session.inc.php');

$username = requireGetKey('uid');
$user = new User($username);

$logs = Log::search($user->getUsername());

renderTwigTemplate('user/view_log.html.twig', array(
    'siteArea' => 'users',
    'user' => $user,
    'logs' => $logs,
));
