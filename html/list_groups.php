<?php

require_once('includes/main.inc.php');
require_once('includes/session.inc.php');

$start = 0;
$count = 30;

if (isset($_GET['start']) && is_numeric($_GET['start'])) {
    $start = $_GET['start'];
}

$search = "";
if (isset($_GET['search'])) {
    $search = trim($_GET['search']);
}

$sort = 'name';
if (isset($_GET['sort'])) {
    $sort = $_GET['sort'];
}

$asc = true;
if (isset($_GET['asc'])) {
    $asc = $_GET['asc'] == '1';
}

$filter = 'none';
if (isset($_GET['filter'])) {
    $filter = $_GET['filter'];
}

$showUsers = false;
if ($filter == 'showUsers') {
    $showUsers = true;
}

$empty = false;
if ($filter == 'empty') {
    $empty = true;
    $showUsers = true;
}
$all_groups = Group::search($search, $start, $count, $sort, $asc, $showUsers, $empty);
$num_groups = Group::lastSearchCount();

renderTwigTemplate(
    'group/index.html.twig',
    [
        'siteArea' => 'groups',
        'groups' => $all_groups,
        'search' => [
            'search' => $search,
            'sort' => $sort,
            'asc' => $asc,
            'filter' => $filter,
            'start' => $start,
        ],
        'totalGroups' => $num_groups,
    ]
);