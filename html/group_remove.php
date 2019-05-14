<?php
include_once('includes/main.inc.php');
include_once('includes/session.inc.php');

$gid = requireGetKey('gid');
$group = new Group($gid);

$errors = array();
if(count($_POST) > 0){
    $result = $group->remove();

    if($result['RESULT'] == true){
        header("Location: list_groups.php");
    } else if ($result['RESULT'] == false) {
        $errors[] =$result['MESSAGE'];
    }
}

renderTwigTemplate('group/edit.html.twig', array(
    'siteArea'=>'groups',
    'group'=>$group,
    'header'=>'Remove Group',
    'inputs'=>array(),
    'message'=>"Are you sure you want to remove this group? This operation cannot be undone.",
    'button'=>array('color'=>'danger', 'text'=>'Remove'),
    'errors' => $errors
));