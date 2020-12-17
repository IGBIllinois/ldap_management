<?php

require_once('includes/main.inc.php');
require_once('includes/session.inc.php');

$gid = requireGetKey('gid', 'Group');
$group = new Group($gid);

$errors = [];
if (count($_POST) > 0) {
    $result = $group->remove();

    if ($result['RESULT'] == true) {
        header("Location: list_groups.php");
    } else {
        if ($result['RESULT'] == false) {
            $errors[] = $result['MESSAGE'];
        }
    }
}

renderTwigTemplate(
    'group/edit.html.twig',
    [
        'siteArea' => 'groups',
        'group' => $group,
        'header' => 'Remove Group',
        'inputs' => [],
        'message' => "Are you sure you want to remove this group? This operation cannot be undone.",
        'button' => ['color' => 'danger', 'text' => 'Remove'],
        'errors' => $errors,
    ]
);