<?php
require_once('includes/main.inc.php');
require_once('includes/session.inc.php');

$gid = requireGetKey('gid', 'Group');
$group = new Group($gid);

$errors = array();
if ( count($_POST) > 0 ) {
    $_POST = array_map("trim", $_POST);

    if ( $_POST['description'] == "" ) {
        $errors[] = "Description cannot be blank.";
    }

    if ( count($errors) == 0 ) {
        $result = $group->setDescription($_POST['description']);

        if ( $result['RESULT'] == true ) {
            header("Location: group.php?gid=" . $result['gid']);
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
        'header' => 'Edit Description',
        'inputs' => array(
            array(
                'attr' => 'description',
                'name' => 'Description',
                'type' => 'text',
                'value' => $group->getDescription(),
            ),
        ),
        'errors' => $errors,
    ));
