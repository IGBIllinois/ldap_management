<?php
include_once('includes/main.inc.php');
include_once('includes/session.inc.php');

$gid = requireGetKey('gid');
$group = new Group($gid);

$error="";
if(count($_POST) > 0){
	$_POST = array_map("trim",$_POST);

	if($_POST['username']==""){
		$error .= html::error_message("Please select a user.");
	} elseif (!User::exists($_POST['username'])) {
		$error .= html::error_message("Invalid username. Please stop trying to break my web interface.");
	}

	if($error == ""){
		$result = $group->addUser($_POST['username']);

		if($result['RESULT'] == true){
            header("Location: group.php?gid=".$result['gid']);
		} else if ($result['RESULT'] == false) {
			$error = $result['MESSAGE'];
		}
	}
}

$allUsers = User::all();
$groupMembers = $group->getMemberUIDs();
$users = array_diff($allUsers, $groupMembers);

renderTwigTemplate('group/edit.html.twig', array(
    'siteArea'=>'groups',
    'group'=>$group,
    'header'=>'Add Group Member',
    'inputs'=>array(
        array('attr'=>'username', 'name'=>'User', 'type'=>'select', 'options'=>$users)
    ),
    'button'=>array('color'=>'success', 'text'=>'Add'),
    'error'=>$error
));