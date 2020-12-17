<?php

require_once 'includes/main.inc.php';
ob_start();

$jsondata = [];

/**
 * @param User $n
 * @return array
 */
function usermap($n)
{
    return ["username" => $n->getUsername(), "name" => $n->getName()];
}

/**
 * @param Group $n
 * @return mixed
 */
function groupmap($n)
{
    return ["name" => $n->getName()];
}

if (isset($_GET['searchtext']) && $_GET['searchtext'] != "") {
    $jsondata['users'] = [
        'results' => array_map("usermap", User::search($_GET['searchtext'], 0, 4)),
        'count' => User::lastSearchCount(),
    ];
    $jsondata['groups'] = [
        'results' => array_map("groupmap", Group::search($_GET['searchtext'], 0, 4)),
        'count' => Group::lastSearchCount(),
    ];
}
echo json_encode($jsondata);
