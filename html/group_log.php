<?php

require_once('includes/main.inc.php');
require_once('includes/session.inc.php');

$gid = requireGetKey('gid', 'Group');
$group = new Group($gid);

$logs = Log::getLogs($group->getId(), Log::TYPE_GROUP);

renderTwigTemplate(
    'group/view_log.html.twig',
    [
        'siteArea' => 'groups',
        'group' => $group,
        'logs' => $logs,
    ]
);
