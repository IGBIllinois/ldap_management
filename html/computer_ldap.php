<?php
include_once('includes/main.inc.php');
include_once('includes/session.inc.php');

$uid = requireGetKey('uid');
$computer = new Computer($uid);
$ldapattributes = $computer->getLdapAttributes();

$attributes = array();
for($i=0; $i<$ldapattributes['count']; $i++){
	$attributes[] = $ldapattributes[$i];
}
sort($attributes);

renderTwigTemplate('computer/view_advanced.html.twig', array(
    'siteArea'=>'domain',
    'computer' =>$computer,
    'attributes'=>$attributes,
    'attributeValues'=>$ldapattributes,
));