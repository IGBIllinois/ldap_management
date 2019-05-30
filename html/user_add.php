<?php
require_once('includes/main.inc.php');
require_once('includes/session.inc.php');

$errors = array();
if ( count($_POST) > 0 ) {
    $_POST = array_map("trim", $_POST);

    if ( $_POST['password'] != $_POST['confirmPassword'] ) {
        $errors[] ="Passwords do not match.";
    }

    if ( count($errors) == 0 ) {
        $user = new User();
        $result = $user->create($_POST['username'], $_POST['firstName'], $_POST['lastName'], $_POST['password']);
        if ( $_POST['forwardingEmail'] != "" ) {
            $user->setForwardingEmail($_POST['forwardingEmail']);
        }
        if ( isset($_POST['realPerson']) ) {
            $group = new Group('igb_users');
            $group->addUser($user->getUsername());
        } else {
            $user->removePasswordExpiration();
        }
        if ( !User::isInAD($_POST['username']) ) {
            $user->setNonCampus(true);
        }

        if ( $result->getStatus() == true ) {
            // Run script to add user to file-server, mail
            if ( __RUN_SHELL_SCRIPTS__ ) {
                $safeUsername = escapeshellarg($_POST['username']);
                exec("sudo ../bin/add_user.pl $safeUsername", $shellOut);
            }
            header("Location: user.php?uid=" . $result->getObject()->getId());
        } else {
            $errors[] =$result->getMessage();
        }
    }
}

renderTwigTemplate('edit.html.twig', array(
    'siteArea' => 'users',
    'header' => 'Add User',
    'inputs' => array(
        array(
            'attr' => 'username',
            'name' => 'Username',
            'type' => 'text',
        ),
        array(
            'attr' => 'forwardingEmail',
            'name' => 'Forwarding Email',
            'type' => 'text',
        ),
        array(
            'attr' => 'firstName',
            'name' => 'First Name',
            'type' => 'text',
        ),
        array(
            'attr' => 'lastName',
            'name' => 'Last Name',
            'type' => 'text',
        ),
        array(
            'attr' => 'password',
            'name' => 'Password',
            'type' => 'password',
        ),
        array(
            'attr' => 'confirmPassword',
            'name' => 'Confirm Password',
            'type' => 'password',
        ),
        array(
            'attr' => 'realPerson',
            'name' => 'Real Person',
            'type' => 'checkbox',
            'value' => true,
        ),
    ),
    'message' => '<button class="btn btn-light" id="password-button" type="button">Generate Password</button><span id="password-text" class="ml-2"></span>',
    'button' => array('color' => 'success', 'text' => 'Add user'),
    'errors' => $errors,
    'validation' => 'add_user_errors',
    'readyScripts' => "$('#password-button').on('click',function(){generate_password();change_password_errors();});",
));
