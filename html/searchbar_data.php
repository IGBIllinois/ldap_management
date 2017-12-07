<?php
	require_once 'includes/main.inc.php';
	ob_start();
	
	$jsondata = array();
	
	if(isset($_GET['searchtext']) && $_GET['searchtext']!=""){
		$jsondata['users'] = array('results'=>user::get_search_users($ldap,$adldap,$_GET['searchtext'],0,4, 'username', true), 'count'=>user::get_search_users_count($ldap,$adldap,$_GET['searchtext']));
		$jsondata['groups'] = array('results'=>group::get_search_groups($ldap, $_GET['searchtext'], 0, 4, 'name', true, 1), 'count'=>group::get_search_groups_count());
	}
	echo json_encode($jsondata);
?>