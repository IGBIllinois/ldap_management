<?php
require_once('includes/main.inc.php');
require_once('includes/session.inc.php');

$uid = requireGetKey('uid', 'User');
$user = new User($uid);

if ( count($_POST) > 0 ) {
    if ( isset($_POST['username']) ) {
        $user->setUsername($_POST['username']);
        header('location: user.php?uid=' . $user->getUsername());
    }
}

renderTwigTemplate(
    'user/edit.html.twig',
    array(
        'siteArea' => 'users',
        'user' => $user,
        'header' => 'Edit Username',
        'inputs' => array(
            array('attr' => 'username', 'name' => 'New Username', 'type' => 'text', 'value' => $user->getUsername()),
        ),
        'message' => "<strong>Note:</strong> This will <strong>not</strong> update the user's forwarding email. If this is a UIUC user and their netid has changed, their forwarding email also needs to be updated to reflect their new netid.",
    ));