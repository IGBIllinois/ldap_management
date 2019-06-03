<?php
require_once('includes/main.inc.php');
require_once('includes/session.inc.php');

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

$sort = 'username';
if ( isset($_GET['sort']) ) {
    $sort = $_GET['sort'];
    $get_array['sort'] = $sort;
}

$asc = true;
if ( isset($_GET['asc']) ) {
    $asc = $_GET['asc'] == "true";
    $get_array['asc'] = $asc;
}

$filter = 'classroom';
setcookie("lastUserSearchSort", $sort);
setcookie("lastUserSearchAsc", $asc);
setcookie("lastUserSearchFilter", $filter);
setcookie("lastUserSearch", $search);
$all_users = User::search($search, $start, $count, $sort, $asc, $filter);
$num_users = User::lastSearchCount();


renderTwigTemplate(
    'user/index.html.twig',
    array(
        'siteArea' => 'users',
        'search' => array('search' => $search, 'sort' => $sort, 'asc' => $asc, 'filter' => $filter, 'start' => $start),
        'users' => $all_users,
        'totalUsers' => $num_users,
        'classroom' => true,
    ));
