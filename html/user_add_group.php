<?php
require_once('includes/main.inc.php');
require_once('includes/session.inc.php');

$uid = requireGetKey('uid');
$user = new User($uid);

// Process POST data
$errors = array();
if(count($_POST) > 0){
    $group = new Group($_POST['group']);
    $result = $group->addUser($uid);

    if($result['RESULT'] == true){
        header("Location: user.php?uid=".$_POST['uid']);
    } else if ($result['RESULT'] == false) {
        $errors[] =$result['MESSAGE'];
    }
}

// Set up template
$allgroups = Group::search("");
$usergroups = $user->getGroups();
$groups = array();
foreach ($allgroups as $group){
    if(!in_array($group->getName(), $usergroups)){
        $groups[] = $group->getName();
    }
}

renderTwigTemplate('user/edit.html.twig', array(
    'siteArea'=>'users',
    'user'=>$user,
    'header'=>'Add to group',
    'inputs'=>array(
        array('type'=>'select', 'name'=>'Group', 'attr'=>'group', 'options'=>$groups)
    ),
    'button'=>array('color'=>'success', 'text'=>'Add'),
    'errors' => $errors
));