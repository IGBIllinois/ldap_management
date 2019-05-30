<?php
include_once('includes/main.inc.php');
include_once('includes/session.inc.php');

$hid = requireGetKey('hid');
$host = new Host($hid);

$errors = array();
if ( count($_POST) > 0 ) {
    $_POST = array_map("trim", $_POST);

    if ( $_POST['username'] == "" ) {
        $errors[] = "Please select a user.";
    } else if ( !User::exists($_POST['username']) ) {
        $errors[] = "Invalid username. Please stop trying to break my web interface.";
    }

    if ( count($errors) == 0 ) {
        $user = new User($_POST['username']);
        $result = $user->addHost($hid);

        if ( $result['RESULT'] == true ) {
            header("Location: host.php?hid=" . $hid);
        } else if ( $result['RESULT'] == false ) {
            $errors[] =$result['MESSAGE'];
        }
    }
}

$allUsers = User::all();
$users = array_diff($allUsers, $host->getUserUIDs());

renderTwigTemplate('host/edit.html.twig', array(
    'siteArea' => 'hosts',
    'host' => $host,
    'header' => 'Add User',
    'inputs' => array(
        array('attr' => 'username', 'name' => 'User', 'type' => 'select', 'options'=>$users),
    ),
    'button'=>array('color'=>'success', 'text'=>'Add'),
    'errors' => $errors,
));
