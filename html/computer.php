<?php
include_once('includes/main.inc.php');
include_once('includes/session.inc.php');

$uid = requireGetKey('uid');
$computer = new Computer($uid);

renderTwigTemplate('computer/view.html.twig', array(
    'siteArea' => 'domain',
    'computer' => $computer,
));
