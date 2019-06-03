<?php
require_once('includes/main.inc.php');
require_once('includes/session.inc.php');

$gid = requireGetKey('gid', 'Group');
$directory = requireGetKey('directory');
$group = new Group($gid);

$errors = array();
if ( count($_POST) > 0 ) {
    $_POST = array_map("trim", $_POST);

    if ( $_POST['directory'] == "" ) {
        $errors[] = "Invalid server/directory. Please stop trying to break my web interface.";
    }

    if ( count($errors) == 0 ) {
        $result = $group->removeDirectory($_POST['directory']);

        if ( $result['RESULT'] == true ) {
            header("Location: group.php?gid=" . $result['gid']);
            exit();
        } else if ( $result['RESULT'] == false ) {
            $errors[] = $result['MESSAGE'];
        }
    }
}

renderTwigTemplate(
    'group/edit.html.twig',
    array(
        'siteArea' => 'groups',
        'group' => $group,
        'header' => 'Remove Directory',
        'inputs' => array(
            array('attr' => 'directory', 'name' => 'Directory', 'type' => 'hidden', 'value' => $directory),
        ),
        'button' => array('color' => 'danger', 'text' => 'Remove'),
        'errors' => $errors,
    ));
