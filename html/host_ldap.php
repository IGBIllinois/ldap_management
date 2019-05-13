<?php
include_once('includes/main.inc.php');
include_once('includes/session.inc.php');

$hid = requireGetKey('hid');
$host = new Host($hid);
$ldapattributes = $host->getLdapAttributes();

$attributes = array();
for ( $i = 0; $i < $ldapattributes['count']; $i++ ) {
    $attributes[] = $ldapattributes[$i];
}
sort($attributes);

renderTwigTemplate('host/view_advanced.html.twig', array(
    'siteArea' => 'hosts',
    'host' => $host,
    'attributes' => $attributes,
    'attributeValues' => $ldapattributes,
));