<?php
include_once('includes/main.inc.php');
include_once('includes/session.inc.php');

$hid = requireGetKey('hid');
$host = new Host($hid);

$logs = Log::getLogs($host->getId(), Log::TYPE_HOST);

renderTwigTemplate('host/view_log.html.twig', array(
    'siteArea' => 'hosts',
    'host' => $host,
    'logs' => $logs,
));
