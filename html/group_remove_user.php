<?php
require_once('includes/main.inc.php');
require_once('includes/session.inc.php');

$uid = requireGetKey('uid');
$gid = requireGetKey('gid');
$group = new Group($gid);

$errors = array();
if ( count($_POST) > 0 ) {
    $group = new Group($_POST['gid']);
    $result = $group->removeUser($_POST['uid']);

    if ( $result['RESULT'] == true ) {
        header("Location: group.php?gid=" . $_POST['gid']);
    } else if ( $result['RESULT'] == false ) {
        $errors[] =$result['MESSAGE'];
    }
}

renderTwigTemplate('group/edit.html.twig', array(
    'siteArea' => 'groups',
    'group' => $group,
    'header' => 'Remove Group Member',
    'inputs' => array(
        array('type' => 'hidden', 'name' => 'User', 'attr' => 'uid', 'value' => $uid),
    ),
    'button' => array('color' => 'danger', 'text' => 'Remove'),
    'errors' => $errors,
));
