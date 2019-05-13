<?php
include_once('includes/main.inc.php');
include_once('includes/session.inc.php');

$get_array = array();
$start = 0;
$count = 30;

if ( isset($_GET['start']) && is_numeric($_GET['start']) ) {
    $start = $_GET['start'];
    $get_array['start'] = $start;
}

$search = "";
if ( isset($_GET['search']) ) {
    $search = trim($_GET['search']);
    $get_array['search'] = $search;
}

$sort = 'name';
if ( isset($_GET['sort']) ) {
    $sort = $_GET['sort'];
    $get_array['sort'] = $sort;
}

$asc = "true";
if ( isset($_GET['asc']) ) {
    $asc = $_GET['asc'];
    $get_array['asc'] = $asc;
}

$all_computers = Computer::search($search, $start, $count, $sort, $asc);
$num_computers = Computer::lastSearchCount();

renderTwigTemplate('computer/index.html.twig', array(
    'siteArea' => 'domain',
    'search' => array('search' => $search, 'sort' => $sort, 'asc' => $asc, 'filter' => '', 'start' => $start),
    'computers' => $all_computers,
    'totalComputers' => $num_computers,
));
