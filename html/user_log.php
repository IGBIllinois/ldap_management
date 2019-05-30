<?php
require_once('includes/main.inc.php');
require_once('includes/session.inc.php');

$username = requireGetKey('uid');
$user = new User($username);

$logs = Log::getLogs($user->getId(), Log::TYPE_USER);

renderTwigTemplate('user/view_log.html.twig', array(
    'siteArea' => 'users',
    'user' => $user,
    'logs' => $logs,
));
