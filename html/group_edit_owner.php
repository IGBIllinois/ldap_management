<?php

require_once('includes/main.inc.php');
require_once('includes/session.inc.php');

$gid = requireGetKey('gid', 'Group');
$group = new Group($gid);

$errors = [];
if (count($_POST) > 0) {
    $_POST = array_map("trim", $_POST);

    if ($_POST['owner'] == "") {
        $errors[] = "Please select a user.";
    } else {
        if (!User::exists($_POST['owner'])) {
            $errors[] = "Invalid username. Please stop trying to break my web interface.";
        }
    }

    if (count($errors) == 0) {
        $result = $group->setOwner($_POST['owner']);

        if ($result['RESULT'] == true) {
            header("Location: group.php?gid=" . $result['gid']);
            exit();
        } else {
            if ($result['RESULT'] == false) {
                $errors[] = $result['MESSAGE'];
            }
        }
    }
}
$allUsers = User::all();

renderTwigTemplate(
    'group/edit.html.twig',
    [
        'siteArea' => 'groups',
        'group' => $group,
        'header' => 'Edit Owner',
        'inputs' => [
            [
                'attr' => 'owner',
                'name' => 'Owner',
                'type' => 'select',
                'value' => $group->getOwner(),
                'options' => $allUsers,
            ],
        ],
        'errors' => $errors,
    ]
);
