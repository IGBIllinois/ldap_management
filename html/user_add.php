<?php

require_once('includes/main.inc.php');
require_once('includes/session.inc.php');

$errors = [];
if (count($_POST) > 0) {
    $_POST = array_map("trim", $_POST);

    if ($_POST['password'] != $_POST['confirmPassword']) {
        $errors[] = "Passwords do not match.";
    }

    if (count($errors) == 0) {
        $user = new User();
        $result = $user->create($_POST['username'], $_POST['firstName'], $_POST['lastName'], $_POST['password']);
        if ($_POST['forwardingEmail'] != "") {
            $user->setForwardingEmail($_POST['forwardingEmail']);
        }
        if (isset($_POST['realPerson'])) {
            $group = new Group('igb_users');
            $group->addUser($user->getUsername());
        } else {
            $user->removePasswordExpiration();
        }
        if (!User::isInAD($_POST['username'])) {
            $user->setNonCampus(true);
        }

        if ($result->getStatus() == true) {
            // Run script to add user to file-server, mail
            Command::execute("add_user.pl", [$_POST['username']]);
            header("Location: user.php?uid=" . $result->getObject()->getId());
        } else {
            $errors[] = $result->getMessage();
        }
    }
}

renderTwigTemplate(
    'edit.html.twig',
    [
        'siteArea' => 'users',
        'header' => 'Add User',
        'inputs' => [
            [
                'attr' => 'username',
                'name' => 'Username',
                'type' => 'text',
            ],
            [
                'attr' => 'forwardingEmail',
                'name' => 'Forwarding Email',
                'type' => 'text',
            ],
            [
                'attr' => 'firstName',
                'name' => 'First Name',
                'type' => 'text',
            ],
            [
                'attr' => 'lastName',
                'name' => 'Last Name',
                'type' => 'text',
            ],
            [
                'attr' => 'password',
                'name' => 'Password',
                'type' => 'password',
            ],
            [
                'attr' => 'confirmPassword',
                'name' => 'Confirm Password',
                'type' => 'password',
            ],
            [
                'attr' => 'realPerson',
                'name' => 'Real Person',
                'type' => 'checkbox',
                'value' => true,
            ],
        ],
        'message' => '<button class="btn btn-light" id="password-button" type="button">Generate Password</button><span id="password-text" class="ml-2"></span>',
        'button' => ['color' => 'success', 'text' => 'Add user'],
        'errors' => $errors,
        'validation' => 'add_user_errors',
        'readyScripts' => "$('#password-button').on('click',function(){generate_password();add_user_errors();});",
    ]
);
