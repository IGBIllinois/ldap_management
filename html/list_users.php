<?php
	require_once('includes/main.inc.php');
	require_once('includes/session.inc.php');

	$start = 0;
	$count = 30;
	if ( isset($_GET['start']) && is_numeric($_GET['start']) ){
		$start = $_GET['start'];
	}
	
	$search = "";
	if ( isset($_GET['search']) ){
		$search = trim($_GET['search']);
	}
	
	$sort = 'username';
	if(isset($_GET['sort'])){
		$sort = $_GET['sort'];
	}
	
	$asc = true;
	if(isset($_GET['asc'])){
		$asc = $_GET['asc'] == '1';
	}
	
	$filter = 'none';
	if(isset($_GET['filter'])){
		$filter = $_GET['filter'];
	}
	setcookie("lastUserSearchSort",$sort);
	setcookie("lastUserSearchAsc",$asc);
	setcookie("lastUserSearchFilter",$filter);
	setcookie("lastUserSearch",$search);
	$passwordSet = null;
	if(isset($_GET['password_set'])){
		$passwordSet = $_GET['password_set'];
	}
	$all_users = User::search($search,$start,$count,$sort,$asc,$filter, $passwordSet);
	$num_users = User::lastSearchCount();

	ob_end_flush();

	// TODO pagination buttons screw up the asc order
// TODO sort by password expiration doesnt work
	renderTwigTemplate('user/index.html.twig', array(
		'siteArea'=>'users',
		'search'=>array('search'=>$search,'sort'=>$sort,'asc'=>$asc,'filter'=>$filter,'start'=>$start),
		'users'=>$all_users,
		'totalUsers'=>$num_users,
	));
