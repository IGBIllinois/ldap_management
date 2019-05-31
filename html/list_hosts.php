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
	
	$hosts = Host::all();

	renderTwigTemplate('host/index.html.twig', array(
		'siteArea'=>'hosts',
		'hosts'=>$hosts,
	));
