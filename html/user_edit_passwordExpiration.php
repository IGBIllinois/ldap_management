<?php
require_once('includes/main.inc.php');
require_once('includes/session.inc.php');

$uid = requireGetKey('uid', 'User');
$user = new User($uid);

if ( count($_POST) > 0 ) {
    if ( isset($_POST['passwordExpiration']) ) {
        $user->setPasswordExpiration(strtotime($_POST['passwordExpiration']));
        header('location: user.php?uid=' . $user->getUsername());
    }
}

renderTwigTemplate(
    'user/edit.html.twig',
    array(
        'siteArea' => 'users',
        'user' => $user,
        'header' => 'Edit Password Expiration',
        'inputs' => array(
            array(
                'attr' => 'passwordExpiration',
                'name' => 'Password Expiration',
                'type' => 'date',
                'value' => $user->getPasswordExpiration(),
            ),
        ),
    ));