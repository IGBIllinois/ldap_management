<?php

require_once('includes/main.inc.php');
require_once('includes/session.inc.php');

$hid = requireGetKey('hid', 'Host');
$host = new Host($hid);

renderTwigTemplate(
    'host/view.html.twig',
    [
        'siteArea' => 'hosts',
        'host' => $host,
    ]
);
