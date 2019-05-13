<?php
include_once('includes/main.inc.php');
include_once('includes/session.inc.php');

$error = "";
if ( count($_POST) > 0 ) {
    $_POST = array_map("trim", $_POST);

    if ( $error == "" ) {
        $group = new Group();
        $result = $group->create($_POST['name'], $_POST['description']);

        if ( $result['RESULT'] == true ) {
            header("Location: group.php?gid=" . $result['gid']);
        } else if ( $result['RESULT'] == false ) {
            $error = $result['MESSAGE'];
        }
    }
}

renderTwigTemplate('edit.html.twig', array(
    'siteArea' => 'groups',
    'header' => 'Add Group',
    'inputs' => array(
        array('attr' => 'name', 'name' => 'Name', 'type' => 'text'),
        array('attr' => 'description', 'name' => 'Description', 'type' => 'text'),
    ),
    'button' => array('color' => 'success', 'text' => 'Add group'),
    'error' => $error,
    'validation' => 'change_group_errors',
));
