<?php
require_once('includes/main.inc.php');
require_once('includes/session.inc.php');

$uid = requireGetKey('uid', "Computer");
$computer = new Computer($uid);

renderTwigTemplate(
    'computer/view.html.twig',
    array(
        'siteArea' => 'domain',
        'computer' => $computer,
    ));
