<?php
include_once('includes/main.inc.php');
include_once('includes/session.inc.php');

$gid = requireGetKey('gid');
$group = new Group($gid);

$error="";
if(count($_POST) > 0){
	$_POST = array_map("trim",$_POST);

	if($_POST['owner']==""){
		$error .= html::error_message("Please select a user.");
	} elseif (!User::exists($_POST['owner'])) {
		$error .= html::error_message("Invalid username. Please stop trying to break my web interface.");
	}

	if($error == ""){
		$result = $group->setOwner($_POST['owner']);

		if($result['RESULT'] == true){
            header("Location: group.php?gid=".$result['gid']);
			exit();
		} else if ($result['RESULT'] == false) {
			$error = $result['MESSAGE'];
		}
	}
}
$allUsers = User::all();

renderTwigTemplate('group/edit.html.twig', array(
    'siteArea'=>'groups',
    'group'=>$group,
    'header'=>'Edit Owner',
    'inputs'=>array(
        array('attr'=>'owner', 'name'=>'Owner', 'type'=>'select', 'value'=>$group->getOwner(), 'options'=>$allUsers)
    ),
    'error'=>$error
));
