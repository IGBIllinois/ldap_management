<?php

require_once('includes/main.inc.php');
require_once('includes/session.inc.php');

$uid = requireGetKey('uid', 'User');
$user = new User($uid);

$errors = [];
if (count($_POST) > 0) {
    $_POST = array_map("trim", $_POST);
    if (isset($_POST['remove'])) {
        $result = $user->removeExpiration();

        if ($result['RESULT'] == true) {
            header("Location: user.php?uid=" . $result['uid']);
            exit();
        } else {
            if ($result['RESULT'] == false) {
                $errors[] = $result['MESSAGE'];
            }
        }
    } else {
        if (isset($_POST['submit'])) {
            if ($_POST['expiration'] == "") {
                $errors[] = "Please enter an expiration date.";
            } else {
                if (!strtotime($_POST['expiration'])) {
                    $errors[] = "Invalid date. Please stop trying to break my web interface.";
                }
            }

            if (count($errors) == 0) {
                $result = $user->setExpiration(strtotime($_POST['expiration']), $_POST['reason']);

                if ($result['RESULT'] == true) {
                    header("Location: user.php?uid=" . $result['uid']);
                    exit();
                } else {
                    if ($result['RESULT'] == false) {
                        $errors[] = $result['MESSAGE'];
                    }
                }
            }
        }
    }
}

renderTwigTemplate(
    'user/edit.html.twig',
    [
        'siteArea' => 'users',
        'user' => $user,
        'header' => 'Set Expiration',
        'inputs' => [
            [
                'attr' => 'expiration',
                'name' => 'Expiration Date',
                'type' => 'date',
                'value' => strtotime("+6 months"),
            ],
            [
                'attr' => 'reason',
                'name' => 'Reason',
                'type' => 'text',
                'value' => $user->getExpirationReason(),
                'placeholder' => 'e.g. they brought us their exit form',
            ],
        ],
        'button' => ['color' => 'warning', 'text' => 'Set expiration'],
        'extraButtons' => [
            ['color' => 'danger', 'text' => 'Remove expiration', 'name' => 'remove'],
        ],
        'errors' => $errors,
    ]
);
