<?php
include_once('includes/main.inc.php');
include_once('includes/session.inc.php');

$gid = requireGetKey('gid');
$group = new Group($gid);
$isUserGroup = User::exists($group->getName());

$users = $group->getMemberUIDs();
usort($users,"LdapObject::username_cmp");

renderTwigTemplate('group/view.html.twig', array(
    'siteArea'=>'groups',
    'group'=>$group,
	'editable'=>!$isUserGroup,
));
