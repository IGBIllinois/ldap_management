<?php
include_once('includes/main.inc.php');
include_once('includes/session.inc.php');

$uid = requireGetKey('uid');
$user = new User($uid);

$gid = requireGetKey('gid');
$group = new Group($gid);

$error = "";
if ( count($_POST) > 0 ) {
    $group = new Group($_POST['gid']);
    $result = $group->removeUser($_POST['uid']);

    if ( $result['RESULT'] == true ) {
        header("Location: user.php?uid=" . $_POST['uid']);
    } else if ( $result['RESULT'] == false ) {
        $error = $result['MESSAGE'];
    }
}

renderTwigTemplate('user/edit.html.twig', array(
    'siteArea' => 'users',
    'user' => $user,
    'header' => 'Remove from group',
    'inputs' => array(
        array('type' => 'hidden', 'name' => 'Group', 'attr' => 'gid', 'value' => $group->getName()),
    ),
    'button' => array('color' => 'danger', 'text' => 'Remove'),
    'error' => $error,
));
