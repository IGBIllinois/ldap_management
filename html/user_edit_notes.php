<?php
require_once('includes/main.inc.php');
require_once('includes/session.inc.php');

$uid = requireGetKey('uid');
$user = new User($uid);

if ( count($_POST) > 0 ) {
    if ( isset($_POST['description']) ) {
        $user->setDescription($_POST['description']);
        header('location: user.php?uid=' . $user->getUsername());
    }
}

renderTwigTemplate('user/edit.html.twig', array(
    'siteArea' => 'users',
    'user' => $user,
    'header' => 'Edit Notes',
    'inputs' => array(
        array('attr' => 'description', 'name' => 'Notes', 'type' => 'text', 'value' => $user->getDescription()),
    ),
));