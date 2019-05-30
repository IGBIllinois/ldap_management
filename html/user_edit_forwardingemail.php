<?php
require_once('includes/main.inc.php');
require_once('includes/session.inc.php');

$uid = requireGetKey('uid');
$user = new User($uid);

if(count($_POST) > 0){
    if(isset($_POST['forwardingEmail'])){
        $user->setForwardingEmail($_POST['forwardingEmail']);
        header('location: user.php?uid='.$user->getUsername());
    }
}

renderTwigTemplate('user/edit.html.twig', array(
    'siteArea'=>'users',
    'user'=>$user,
    'header'=>'Edit Forwarding Email',
    'inputs'=>array(
        array('attr'=>'forwardingEmail', 'name'=>'Forwarding Email', 'type'=>'text', 'value'=>$user->getForwardingEmail())
    )
));