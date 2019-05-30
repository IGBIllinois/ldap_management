<?php
require_once('includes/main.inc.php');
require_once('includes/session.inc.php');

$errors = array();
if ( count($_POST) > 0 ) {
    $_POST = array_map("trim", $_POST);

    $computer = new Computer();
    $result = $computer->create($_POST['name']);

    if ( $result['RESULT'] == true ) {
        header("Location: computer.php?uid=" . $result['uid']);
    } else if ( $result['RESULT'] == false ) {
        $errors[] =$result['MESSAGE'];
    }
}

renderTwigTemplate('edit.html.twig', array(
    'siteArea' => 'domain',
    'header' => 'Add Domain Computer',
    'inputs' => array(
        array('attr' => 'name', 'name' => 'Name', 'type' => 'text'),
    ),
    'button' => array('color' => 'success', 'text' => 'Add computer'),
    'errors' => $errors,
));