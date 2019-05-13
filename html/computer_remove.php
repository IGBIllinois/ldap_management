<?php
include_once('includes/main.inc.php');
include_once('includes/session.inc.php');

$uid = requireGetKey('uid');
$computer = new Computer($uid);

$error = "";
if ( count($_POST) > 0 ) {
    $_POST = array_map("trim", $_POST);

    if ( $_POST['uid'] == "" ) {
        $error .= "Computer cannot be blank. Please stop trying to break my web interface.";
    }

    if ( $error == "" ) {
        $computer = new Computer($_POST['uid']);
        $result = $computer->remove();

        if ( $result['RESULT'] == true ) {
            header("Location: list_computers.php");
        } else if ( $result['RESULT'] == false ) {
            $error = $result['MESSAGE'];
        }
    }
}

renderTwigTemplate('computer/edit.html.twig', array(
    'siteArea'=>'domain',
    'computer'=>$computer,
    'header'=>'Remove Domain Computer',
    'inputs'=>array(),
    'message'=>"Are you sure you want to remove this computer? This operation cannot be undone.",
    'button'=>array('color'=>'danger', 'text'=>'Remove'),
    'error'=>$error
));
