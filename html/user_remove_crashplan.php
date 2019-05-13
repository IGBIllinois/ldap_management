<?php
include_once('includes/main.inc.php');
include_once('includes/session.inc.php');

$uid = requireGetKey('uid');
$user = new User($uid);

if ( count($_POST) > 0 ) {
    if ( isset($_POST['uid']) ) {
        if ( __RUN_SHELL_SCRIPTS__ ) {
            $safeusername = escapeshellarg($uid);
            exec("sudo ../bin/remove_crashplan.pl $safeusername");
        }
        $user = new User($uid);
        $user->setCrashplan(false);
        Log::info("Crashplan archive removed for " . $uid);
        header('location: user.php?uid=' . $user->getUsername());
    }
}

renderTwigTemplate('user/edit.html.twig', array(
    'siteArea' => 'users',
    'user' => $user,
    'header' => 'Remove Crashplan Archives',
    'inputs' => array(),
    'message' => "This will remove the user's backups from crashplan and free up their license. This operation <em>can</em> be undone.",
    'button' => array(
        'text' => 'Remove Archives',
        'color' => 'danger',
    ),
));