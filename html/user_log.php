<?php
include_once('includes/main.inc.php');
include_once('includes/session.inc.php');

$username = requireGetKey('uid');
$user = new User($username);

$logs = Log::search($user->getUsername());

$searchdescription = html::get_list_users_description_from_cookies();

renderTwigTemplate('user/view_log.html.twig', array(
    'siteArea' => 'users',
    'user' => $user,
    'logs' => $logs,
));
