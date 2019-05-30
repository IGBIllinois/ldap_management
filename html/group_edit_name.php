<?php
require_once('includes/main.inc.php');
require_once('includes/session.inc.php');

$gid = requireGetKey('gid');
$group = new Group($gid);

$errors = array();
if ( count($_POST) > 0 ) {
    $_POST = array_map("trim", $_POST);

    if ( $_POST['name'] == "" ) {
        $errors[] = "Name cannot be blank.";
    }

    if ( count($errors) == 0 ) {
        $result = $group->setName($_POST['name']);

        if ( $result['RESULT'] == true ) {
            header("Location: group.php?gid=" . $result['gid']);
        } else if ( $result['RESULT'] == false ) {
            $errors[] = $result['MESSAGE'];
        }
    }
}

renderTwigTemplate('group/edit.html.twig', array(
    'siteArea' => 'groups',
    'group' => $group,
    'header' => 'Edit Name',
    'inputs' => array(
        array('attr' => 'name', 'name' => 'New Name', 'type' => 'text', 'value' => $group->getName()),
    ),
    'validation' => 'change_groupName_errors',
    'errors' => $errors,
));
