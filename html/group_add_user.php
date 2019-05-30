<?php
require_once('includes/main.inc.php');
require_once('includes/session.inc.php');

$gid = requireGetKey('gid');
$group = new Group($gid);

$errors = array();
if ( count($_POST) > 0 ) {
    $_POST = array_map("trim", $_POST);

    if ( $_POST['username'] == "" ) {
        $errors[] = "Please select a user.";
    } else if ( !User::exists($_POST['username']) ) {
        $errors[] = "Invalid username. Please stop trying to break my web interface.";
    }

    if ( count($errors) == 0 ) {
        $result = $group->addUser($_POST['username']);

        if ( $result['RESULT'] == true ) {
            header("Location: group.php?gid=" . $result['gid']);
        } else if ( $result['RESULT'] == false ) {
            $errors[] = $result['MESSAGE'];
        }
    }
}

$allUsers = User::all();
$groupMembers = $group->getMemberUIDs();
$users = array_diff($allUsers, $groupMembers);

renderTwigTemplate('group/edit.html.twig', array(
    'siteArea' => 'groups',
    'group' => $group,
    'header' => 'Add Group Member',
    'inputs' => array(
        array('attr' => 'username', 'name' => 'User', 'type' => 'select', 'options' => $users),
    ),
    'button' => array('color' => 'success', 'text' => 'Add'),
    'errors' => $errors,
));
