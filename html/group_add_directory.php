<?php
include_once('includes/main.inc.php');
include_once('includes/session.inc.php');

$gid = requireGetKey('gid');
$group = new Group($gid);

$error="";
if(count($_POST) > 0){
	$_POST = array_map("trim",$_POST);

	if($_POST['host']==""){
		$error .= html::error_message("Please enter a server.");
	}
	if($_POST['directory']==""){
		$error .= html::error_message("Please enter a directory.");
	}

	if($error == ""){
		$result = $group->addDirectory($_POST['host'],$_POST['directory']);

		if($result['RESULT'] == true){
            header("Location: group.php?gid=".$result['gid']);
		} else if ($result['RESULT'] == false) {
			$error = $result['MESSAGE'];
		}
	}
}
$allHosts = Host::all();
$allHosts = array_map(function(Host $v){
	return $v->getName();
}, $allHosts);

renderTwigTemplate('group/edit.html.twig', array(
    'siteArea'=>'groups',
    'group'=>$group,
    'header'=>'Add Managed Directory',
    'inputs'=>array(
        array('attr'=>'host', 'name'=>'Host', 'type'=>'select', 'options'=>$allHosts),
		array('attr'=>'directory', 'name'=>'Directory', 'type'=>'text')
    ),
    'error'=>$error
));
