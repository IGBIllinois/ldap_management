<?php
require_once 'includes/main.inc.php';
ob_start();

$jsondata = array();

/**
 * @param User $n
 * @return array
 */
function usermap($n){
    return array("username"=>$n->getUsername(), "name"=>$n->getName());
}

/**
 * @param Group $n
 * @return mixed
 */
function groupmap($n){
    return array("name"=>$n->getName());
}

if(isset($_GET['searchtext']) && $_GET['searchtext']!=""){
    $jsondata['users'] = array(
        'results'=>array_map("usermap",User::search($_GET['searchtext'],0,4, 'username', true)),
        'count'=>User::lastSearchCount());
    $jsondata['groups'] = array(
        'results'=>array_map("groupmap", Group::search($_GET['searchtext'], 0, 4, 'name', true, false)),
        'count'=>Group::lastSearchCount());
}
echo json_encode($jsondata);
?>