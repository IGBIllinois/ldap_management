<?php

require_once('includes/main.inc.php');
require_once('includes/session.inc.php');

$uid = requireGetKey('uid', 'User');
$user = new User($uid);

if (count($_POST) > 0) {
    if (isset($_POST['loginShell'])) {
        $user->setLoginShell($_POST['loginShell']);
        header('location: user.php?uid=' . $user->getUsername());
    }
}

renderTwigTemplate(
    'user/edit.html.twig',
    [
        'siteArea' => 'users',
        'user' => $user,
        'header' => 'Edit Login Shell',
        'inputs' => [
            [
                'attr' => 'loginShell',
                'name' => 'Login Shell',
                'type' => 'select',
                'value' => $user->getLoginShell(),
                'options' => [
                    "/usr/libexec/openssh/sftp-server",
                    "/bin/bash",
                    "/usr/local/bin/system-specific",
                    "/sbin/nologin",
                ],
            ],
        ],
    ]
);