<?php
include_once('includes/main.inc.php');
include_once('includes/session.inc.php');

$username = requireGetKey('uid');
$user = new User($username);

renderTwigTemplate('user/view.html.twig', array(
    'siteArea'=>'users',
    'user' =>$user,
));
