<?php
include_once('includes/main.inc.php');
include_once('includes/session.inc.php');

$uid = requireGetKey('uid');
$user = new User($uid);

if ( count($_POST) > 0 ) {
    if ( isset($_POST['uid']) ) {
        $result = $user->setLoginShell('/usr/local/bin/system-specific');
        if ( $result['RESULT'] ) {
            $result = $user->addHost("biocluster2.igb.illinois.edu");
        }
        if ( $result['RESULT'] ) {
            $queuegroup = new Group('biocluster_queue');
            if ( !in_array($uid, $queuegroup->getMemberUIDs()) ) {
                $result = $queuegroup->addUser($uid);
            }
        }
        if ( __RUN_SHELL_SCRIPTS__ ) {
            $safeusername = escapeshellarg($uid);
            exec("sudo ../bin/setup_biocluster.pl $safeusername");
        }
        if ( $result['RESULT'] ) {
            Log::info('Biocluster2 access given to user ' . $uid, Log::USER_SET_BIOCLUSTER, $user);
        }
        header('location: user.php?uid=' . $user->getUsername());
    }
}

renderTwigTemplate(
    'user/edit.html.twig',
    array(
        'siteArea' => 'users',
        'user' => $user,
        'header' => 'Biocluster Access',
        'inputs' => array(),
        'message' => "This will set up the user's Biocluster account and give them access to the default queue. It will <strong>not</strong> add them to Biocluster Accounting.",
        'button' => array(
            'text' => 'Give Access',
        ),
    ));