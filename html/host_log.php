<?php

require_once('includes/main.inc.php');
require_once('includes/session.inc.php');

$hid = requireGetKey('hid', 'Host');
$host = new Host($hid);

$logs = Log::getLogs($host->getId(), Log::TYPE_HOST);

renderTwigTemplate(
    'host/view_log.html.twig',
    [
        'siteArea' => 'hosts',
        'host' => $host,
        'logs' => $logs,
    ]
);
