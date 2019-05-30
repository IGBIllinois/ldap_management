<?php
	require_once('includes/main.inc.php');
	require_once('includes/session.inc.php');

	$get_array = array();
	$start = 0;
	$count = 30;
	
	if ( isset($_GET['start']) && is_numeric($_GET['start']) ){
		$start = $_GET['start'];
		$get_array['start'] = $start;
	}
	
	$search = "";
	if ( isset($_GET['search']) ){
		$search = trim($_GET['search']);
		$get_array['search'] = $search;
	}
	
	$sort = 'name';
	if(isset($_GET['sort'])){
		$sort = $_GET['sort'];
		$get_array['sort'] = $sort;
	}
	
	$asc = true;
	if(isset($_GET['asc'])){
		$asc = $_GET['asc'] == 'true';
		$get_array['asc'] = $asc;
	}

	$filter = 'none';
	if(isset($_GET['filter'])){
		$filter = $_GET['filter'];
	}
	
	$showUsers = false;
	if($filter == 'showUsers'){
		$showUsers = true;
	}
	$all_groups = Group::search($search,$start,$count,$sort,$asc,$showUsers);
	$num_groups = Group::lastSearchCount();

	renderTwigTemplate('group/index.html.twig', array(
		'siteArea'=>'groups',
		'groups'=>$all_groups,
		'search'=>array('search'=>$search,'sort'=>$sort,'asc'=>$asc,'filter'=>$filter,'start'=>$start),
		'totalGroups'=>$num_groups,
	));