<?php

require_once('includes/main.inc.php');
require_once('includes/session.inc.php');

$hid = requireGetKey('hid', 'Host');
$host = new Host($hid);
$ldapattributes = $host->getLdapAttributes();

$attributes = [];
for ($i = 0; $i < $ldapattributes['count']; $i++) {
    $attributes[] = $ldapattributes[$i];
}
sort($attributes);

renderTwigTemplate(
    'host/view_advanced.html.twig',
    [
        'siteArea' => 'hosts',
        'host' => $host,
        'attributes' => $attributes,
        'attributeValues' => $ldapattributes,
    ]
);