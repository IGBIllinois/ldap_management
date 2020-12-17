<?php

require_once('includes/main.inc.php');
require_once('includes/session.inc.php');

$uid = requireGetKey('uid', 'User');
if ($uid == "") {
    header('location: index.php');
}
$user = new User($uid);

$errors = [];
if (count($_POST) > 0) {
    $user = new User($_POST['uid']);
    $result = $user->unlock();

    if ($result['RESULT'] == true) {
        header("Location: user.php?uid=" . $_POST['uid']);
    } else {
        if ($result['RESULT'] == false) {
            $errors[] = $result['MESSAGE'];
        }
    }
}

renderTwigTemplate(
    'user/edit.html.twig',
    [
        'siteArea' => 'users',
        'user' => $user,
        'header' => 'Unlock User',
        'inputs' => [],
        'message' => "Are you sure you want to unlock this user?",
        'button' => ['color' => 'warning', 'text' => 'Unlock'],
        'errors' => $errors,
    ]
);
