<?php

require_once('includes/main.inc.php');
require_once('includes/session.inc.php');

$username = requireGetKey('uid', 'User');
$user = new User($username);

$ldapattributes = $user->getLdapAttributes();

$attributes = [];
for ($i = 0; $i < $ldapattributes['count']; $i++) {
    $attributes[] = $ldapattributes[$i];
}

renderTwigTemplate(
    'user/view_advanced.html.twig',
    [
        'siteArea' => 'users',
        'user' => $user,
        'attributes' => $attributes,
        'attributeValues' => $ldapattributes,
    ]
);
