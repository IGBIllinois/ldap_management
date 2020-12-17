<?php

require_once('includes/main.inc.php');
require_once('includes/session.inc.php');

$errors = [];
if (count($_POST) > 0) {
    $_POST = array_map("trim", $_POST);

    if (count($errors) == 0) {
        $group = new Group();
        $result = $group->create($_POST['name'], $_POST['description']);

        if ($result['RESULT'] == true) {
            header("Location: group.php?gid=" . $result['gid']);
        } else {
            if ($result['RESULT'] == false) {
                $errors[] = $result['MESSAGE'];
            }
        }
    }
}

renderTwigTemplate(
    'edit.html.twig',
    [
        'siteArea' => 'groups',
        'header' => 'Add Group',
        'inputs' => [
            ['attr' => 'name', 'name' => 'Name', 'type' => 'text'],
            ['attr' => 'description', 'name' => 'Description', 'type' => 'text'],
        ],
        'button' => ['color' => 'success', 'text' => 'Add group'],
        'errors' => $errors,
        'validation' => 'change_group_errors',
    ]
);
