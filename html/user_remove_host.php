<?php
require_once('includes/main.inc.php');
require_once('includes/session.inc.php');

$uid = requireGetKey('uid', 'User');
$user = new User($uid);

$hid = requireGetKey('hid', 'Host');
$host = new Host($hid);

$errors = array();
if ( count($_POST) > 0 ) {
    $result = $user->removeHost($_POST['host']);

    if ( $result['RESULT'] == true ) {
        header("Location: user.php?uid=" . $_POST['uid']);
    } else if ( $result['RESULT'] == false ) {
        $errors[] = $result['MESSAGE'];
    }
}

renderTwigTemplate(
    'user/edit.html.twig',
    array(
        'siteArea' => 'users',
        'user' => $user,
        'header' => 'Remove from group',
        'inputs' => array(
            array('type' => 'hidden', 'name' => 'Host', 'attr' => 'host', 'value' => $host->getName()),
        ),
        'button' => array('color' => 'danger', 'text' => 'Remove'),
        'errors' => $errors,
    ));
