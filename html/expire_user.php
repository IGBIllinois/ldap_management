<?php
include_once('includes/main.inc.php');
include_once('includes/session.inc.php');

$uid = requireGetKey('uid');
$user = new User($uid);

$error = "";
if ( count($_POST) > 0 ) {
    $_POST = array_map("trim", $_POST);

    if(isset($_POST['remove'])){
    	$result = $user->removeExpiration();

    	if ( $result['RESULT'] == true ) {
            header("Location: user.php?uid=" . $result['uid']);
            exit();
        } else if ( $result['RESULT'] == false ) {
            $error = $result['MESSAGE'];
        }
	}

    else if (isset($_POST['submit'])) {
        if ( $_POST['expiration'] == "" ) {
            $error .= html::error_message("Please enter an expiration date.");
        } else if ( !strtotime($_POST['expiration']) ) {
            $error .= html::error_message("Invalid date. Please stop trying to break my web interface.");
        }

        if ( $error == "" ) {
            $result = $user->setExpiration(strtotime($_POST['expiration']), $_POST['reason']);

            if ( $result['RESULT'] == true ) {
                header("Location: user.php?uid=" . $result['uid']);
                exit();
            } else if ( $result['RESULT'] == false ) {
                $error = $result['MESSAGE'];
            }
        }
    }
}

renderTwigTemplate('user/edit.html.twig', array(
    'siteArea'=>'users',
    'user'=>$user,
    'header'=>'Set Expiration',
    'inputs'=>array(
        array('attr'=>'expiration', 'name'=>'Expiration Date', 'type'=>'date', 'value'=>strtotime("+6 months")),
        array('attr'=>'reason', 'name'=>'Reason', 'type'=>'text', 'value'=>$user->getExpirationReason(), 'placeholder'=>'e.g. they brought us their exit form'),
    ),
	'button'=>array('color'=>'warning', 'text'=>'Set expiration'),
	'extraButtons'=>array(
			array('color'=>'danger', 'text'=>'Remove expiration', 'name'=>'remove'),
	),
	'error'=>$error,
));
