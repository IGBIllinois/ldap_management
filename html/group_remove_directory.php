<?php
include_once('includes/main.inc.php');
include_once('includes/session.inc.php');

$gid = requireGetKey('gid');
$directory = requireGetKey('directory');
$group = new Group($gid);

$error="";
if(count($_POST) > 0){
	$_POST = array_map("trim",$_POST);

	if($_POST['directory']==""){
		$error .= html::error_message("Invalid server/directory. Please stop trying to break my web interface.");
	}

	if($error == ""){
		$result = $group->removeDirectory($_POST['directory']);

		if($result['RESULT'] == true){
            header("Location: group.php?gid=".$result['gid']);
			exit();
		} else if ($result['RESULT'] == false) {
			$error = $result['MESSAGE'];
		}
	}
}

renderTwigTemplate('group/edit.html.twig', array(
    'siteArea'=>'groups',
    'group'=>$group,
    'header'=>'Remove Directory',
    'inputs'=>array(
        array('attr'=>'directory', 'name'=>'Directory', 'type'=>'hidden', 'value'=>$directory)
    ),
    'button'=>array('color'=>'danger', 'text'=>'Remove'),
    'error'=>$error
));
