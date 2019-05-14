<?php
include_once('includes/main.inc.php');
include_once('includes/session.inc.php');

$errors = array();
$show_users = false;
if ( count($_POST) > 0 ) {
    $_POST = array_map("trim", $_POST);
    if ( !isset($_POST['prefix']) ) {
        $errors[] ="Please enter a username prefix.";
    }
    if ( !isset($_POST['start']) || !is_numeric($_POST['start']) ) {
        $errors[] ="Please enter a valid start.";
    }
    if ( !isset($_POST['end']) || !is_numeric($_POST['end']) ) {
        $errors[] ="Please enter a valid end.";
    }

    if ( $errors == "" ) {
        $_POST['start'] = intval($_POST['start']);
        $_POST['end'] = intval($_POST['end']);
        $passwords = array();
        $padLength = 2;
        $classroom_queue = new Group('classroom_queue');
        $show_users = true;
        $added_users = array();
        $grouptoadd = null;
        if ( isset($_POST['group']) && $_POST['group'] != "" ) {
            // Initialize group object, if we're adding a group
            $grouptoadd = new Group($_POST['group']);
        }
        for ( $i = $_POST['start']; $i <= $_POST['end']; $i++ ) {
            $paddednum = str_pad($i, $padLength, "0", STR_PAD_LEFT);
            $username = $_POST['prefix'] . $paddednum;
            $password = User::randomPassword();
            $added_users[] = array('username' => $username, 'password' => $password);
            $user = new User($username);

            if ( User::exists($username) ) {
                // User already exists, clean it out
                // clear out the biocluster/file-server home folder, if it exists
                if ( __RUN_SHELL_SCRIPTS__ ) {
                    $safeusername = escapeshellarg($username);
                    exec("sudo ../bin/classroom_cleanup.pl $safeusername 2>&1", $output);
                    Log::info("sudo ../bin/classroom_cleanup.pl $safeusername 2>&1");
                    Log::info("Cleaned up file-server and biocluster directories for $username");
                }
                // Set the password
                $user->setPassword($password);

                // Clear out any extraneous groups the user is in
                $groups = $user->getGroups();
                $grouptoremove = new Group();
                for ( $j = 0; $j < count($groups); $j++ ) {
                    if ( $groups[$j] != $username && $groups[$j] != 'classroom_queue' ) {
                        $grouptoremove->load_by_id($groups[$j]);
                        $grouptoremove->removeUser($username);
                    }
                }
            } else {
                // Create user with random password
                $user->create($username, $username, $username, $password);
                // Run script to add user to file-server
                if ( __RUN_SHELL_SCRIPTS__ ) {
                    $safeusername = escapeshellarg($username);
                    exec("sudo ../bin/add_user.pl $safeusername --classroom", $shellout);
                }
            }
            // Give user biocluster access
            $user->setLoginShell('/usr/local/bin/system-specific');
            $user->addHost('biocluster2.igb.illinois.edu');

            // Add user to classroom queue
            $classroom_queue->addUser($username);

            // Set classroom-user status
            $user->setClassroom(true);

            // Set description
            $user->setDescription($_POST['desc']);

            // Set expiration
            if ( isset($_POST['exp']) ) {
                if ( $_POST['exp'] != "" ) {
                    $user->setExpiration(strtotime($_POST['exp']));
                } else {
                    $user->removeExpiration();
                }
            }

            // Set extra group
            if ( $grouptoadd != null ) {
                $grouptoadd->addUser($username);
            }
        }

        $subject = "IGB Classroom Users " . $_POST['prefix'] . str_pad($_POST['start'], $padLength, "0", STR_PAD_LEFT) . '-' . $_POST['prefix'] . str_pad($_POST['end'], $padLength, "0", STR_PAD_LEFT);
        $to = $login_user->getEmail();
        $boundary = uniqid('mp');

        $emailMessage = "\r\n\r\n--" . $boundary . "\r\n";
        $emailMessage .= "Content-type: text/plain; charset=utf-8\r\n\r\n";

        $txtTemplate = $twig->load('email/add_classroom_users.txt.twig');
        $emailMessage .= $txtTemplate->render(array(
            'added_users' => $added_users,
        ));

        $emailMessage .= "\r\n\r\n--" . $boundary . "\r\n";
        $emailMessage .= "Content-type: text/html; charset=utf-8\r\n\r\n";

        $htmlTemplate = $twig->load('email/add_classroom_users.html.twig');
        $emailMessage .= $htmlTemplate->render(array(
            'added_users' => $added_users,
        ));

        $emailMessage .= "\r\n\r\n--" . $boundary . "--";

        $headers = "MIME-Version: 1.0\r\n";
        $headers .= "From: " . __ADMIN_EMAIL__ . "\r\n";
        $headers .= "Content-Type: multipart/alternative;boundary=" . $boundary . "\r\n";
        // TODO this multipart email thing is confusing and should be broken out into its own function, or maybe use a library
        mail($to, $subject, $emailMessage, $headers, " -f " . __ADMIN_EMAIL__);

        renderTwigTemplate('user/add_classroom.html.twig', array(
            'siteArea' => 'users',
            'added_users' => $added_users,
        ));
        exit();
    }
}

$allGroups = Group::search("");
$groups = array();
foreach ( $allGroups as $group ) {
    $groups[] = $group->getName();
}

renderTwigTemplate('edit.html.twig', array(
    'siteArea' => 'users',
    'header' => 'Add Classroom Users',
    'inputs' => array(
        array('attr' => 'prefix', 'name' => 'Prefix', 'type' => 'text'),
        array('attr' => 'start', 'name' => 'Range Start', 'type' => 'text'),
        array('attr' => 'end', 'name' => 'Range End', 'type' => 'text'),
        array('attr' => 'desc', 'name' => 'Description', 'type' => 'text'),
        array('attr' => 'exp', 'name' => 'Expiration', 'type' => 'date'),
        array('attr' => 'group', 'name' => 'Group', 'type' => 'select', 'options' => $groups, 'blankOption' => true),
    ),
    'button' => array('color' => 'success', 'text' => 'Add classroom users'),
    'errors' => $errors,
    'validation' => 'show_add_classroom_text',
));