<?php
include_once('includes/main.inc.php');
include_once('includes/session.inc.php');

$gid = requireGetKey('gid');
$group = new Group($gid);

$error="";
if(count($_POST) > 0){
	$_POST = array_map("trim",$_POST);

	if($_POST['description'] == ""){
		$message .= html::error_message("Description cannot be blank.");
	}

	if($message == ""){
		$result = $group->setDescription($_POST['description']);

		if($result['RESULT'] == true){
			header("Location: group.php?gid=".$result['gid']);
		} else if ($result['RESULT'] == false) {
			$message = $result['MESSAGE'];
		}
	}
}

renderTwigTemplate('group/edit.html.twig', array(
    'siteArea'=>'groups',
    'group'=>$group,
    'header'=>'Edit Description',
    'inputs'=>array(
        array('attr'=>'description', 'name'=>'Description', 'type'=>'text', 'value'=>$group->getDescription())
    ),
    'error'=>$error
));
