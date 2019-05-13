<?php
include_once('includes/main.inc.php');
include_once('includes/session.inc.php');

$uid = requireGetKey('uid');
$user = new User($uid);

if ( count($_POST) > 0 ) {
    if ( isset($_POST['firstName']) && isset($_POST['lastName']) ) {
        $user->setName($_POST['firstName'], $_POST['lastName']);
        header('location: user.php?uid=' . $user->getUsername());
    }
}

renderTwigTemplate('user/edit.html.twig', array(
    'siteArea' => 'users',
    'user' => $user,
    'header' => 'Edit Name',
    'inputs' => array(
        array('attr' => 'firstName', 'name' => 'First Name', 'type' => 'text', 'value' => $user->getFirstName()),
        array('attr' => 'lastName', 'name' => 'Last Name', 'type' => 'text', 'value' => $user->getLastName()),
    ),
));