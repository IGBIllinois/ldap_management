<?php
include_once('includes/main.inc.php');
include_once('includes/session.inc.php');

$error = "";
if ( count($_POST) > 0 ) {
    $_POST = array_map("trim", $_POST);
    if ( !isset($_POST['prefix']) ) {
        $error = html::error_message("Please enter a username prefix.");
    }
    if ( !isset($_POST['start']) || !is_numeric($_POST['start']) || $_POST['start'] < 1 ) {
        $error = html::error_message("Please enter a valid start number.");
    }
    if ( !isset($_POST['end']) || !is_numeric($_POST['end']) || $_POST['end'] < 1 ) {
        $error = html::error_message("Please enter a valid end number.");
    }

    if ( $error == "" ) {
        $_POST['start'] = intval($_POST['start']);
        $_POST['end'] = intval($_POST['end']);
        $passwords = array();
        $padlength = 2;
        for ( $i = $_POST['start']; $i <= $_POST['end']; $i++ ) {
            $paddednum = str_pad($i, $padlength, "0", STR_PAD_LEFT);
            $username = $_POST['prefix'] . $paddednum;
            $user = new User($username);

            if ( User::exists($username) ) {
                // Create user with random password
                $user->remove();
            }
        }

        header("Location: list_classroom_users.php");
    }
}

renderTwigTemplate('edit.html.twig', array(
    'siteArea' => 'users',
    'header' => 'Remove Classroom Users',
    'inputs' => array(
        array('attr' => 'prefix', 'name' => 'Prefix', 'type' => 'text'),
        array('attr' => 'start', 'name' => 'Range Start', 'type' => 'text'),
        array('attr' => 'end', 'name' => 'Range End', 'type' => 'text'),

    ),
    'button' => array('color' => 'danger', 'text' => 'Remove'),
    'validation' => 'show_remove_classroom_text',
    'error' => $error,
));
