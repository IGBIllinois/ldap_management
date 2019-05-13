<?php
include_once('includes/main.inc.php');
include_once('includes/session.inc.php');

$gid = requireGetKey('gid');
$group = new Group($gid);
$ldapattributes = $group->getLdapAttributes();

$attributes = array();
for($i=0; $i<$ldapattributes['count']; $i++){
	$attributes[] = $ldapattributes[$i];
}
sort($attributes);

renderTwigTemplate('group/view_advanced.html.twig', array(
    'siteArea'=>'groups',
    'group' =>$group,
    'attributes'=>$attributes,
    'attributeValues'=>$ldapattributes,
));