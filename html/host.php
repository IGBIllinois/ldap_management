<?php
require_once('includes/main.inc.php');
require_once('includes/session.inc.php');

$hid = requireGetKey('hid');
$host = new Host($hid);

renderTwigTemplate('host/view.html.twig', array(
    'siteArea' => 'hosts',
    'host' => $host,
));
