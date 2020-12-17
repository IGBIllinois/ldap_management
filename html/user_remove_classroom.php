<?php

require_once('includes/main.inc.php');
require_once('includes/session.inc.php');

$errors = [];
if (count($_POST) > 0) {
    $_POST = array_map("trim", $_POST);
    if (!isset($_POST['prefix'])) {
        $errors[] = "Please enter a username prefix.";
    }
    if (!isset($_POST['start']) || !is_numeric($_POST['start']) || $_POST['start'] < 1) {
        $errors[] = "Please enter a valid start number.";
    }
    if (!isset($_POST['end']) || !is_numeric($_POST['end']) || $_POST['end'] < 1) {
        $errors[] = "Please enter a valid end number.";
    }

    if (count($errors) == 0) {
        $_POST['start'] = intval($_POST['start']);
        $_POST['end'] = intval($_POST['end']);
        $passwords = [];
        $padlength = 2;
        for ($i = $_POST['start']; $i <= $_POST['end']; $i++) {
            $paddednum = str_pad($i, $padlength, "0", STR_PAD_LEFT);
            $username = $_POST['prefix'] . $paddednum;
            $user = new User($username);

            if (User::exists($username)) {
                // Create user with random password
                $user->remove();
            }
        }

        header("Location: list_classroom_users.php");
    }
}

renderTwigTemplate(
    'edit.html.twig',
    [
        'siteArea' => 'users',
        'header' => 'Remove Classroom Users',
        'inputs' => [
            ['attr' => 'prefix', 'name' => 'Prefix', 'type' => 'text'],
            ['attr' => 'start', 'name' => 'Range Start', 'type' => 'text'],
            ['attr' => 'end', 'name' => 'Range End', 'type' => 'text'],

        ],
        'button' => ['color' => 'danger', 'text' => 'Remove'],
        'validation' => 'show_remove_classroom_text',
        'errors' => $errors,
    ]
);
