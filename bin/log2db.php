<?php
ini_set("display_errors", 1);
chdir(dirname(__FILE__));
require_once '../conf/settings.inc.php';
require_once '../vendor/autoload.php';
$sapi_type = php_sapi_name();
// If run from command line
if ( $sapi_type != 'cli' ) {
    echo "Error: This script can only be run from the command line.\n";
} else {
    echo "Analyzing logs...";
    Ldap::init(__LDAP_HOST__, __LDAP_SSL__, __LDAP_PORT__, __LDAP_BASE_DN__);
    MySQL::init(__LOG_DB_HOST__, __LOG_DB_NAME__, __LOG_DB_USER__, __LOG_DB_PASS__);

    MySQL::getInstance()->query('truncate logs; truncate objects');

    $object = new Dummy(null);
    $related = new Dummy(null);

    $log_dir = dirname(__LOG_FILE__);
    $log_lines = [];
    $logs = scandir($log_dir);
    // Put logs in proper order
    $sortedLogs = [];
    $currentLog = null;
    foreach ( $logs as $log ) {
        if ( preg_match('/^' . basename(__LOG_FILE__) . '.+$/u', $log) ) {
            $sortedLogs[] = $log;
        } else if ( preg_match('/^' . basename(__LOG_FILE__) . '$/u', $log) ) {
            $currentLog = $log;
        }
    }
    if ( $currentLog != null ) {
        $sortedLogs[] = $currentLog;
    }
    $missed = 0;
    foreach ( $sortedLogs as $log ) {
        $fh = fopen($log_dir . "/" . $log, 'r');
        if ( $fh ) {
            while ( ($line = fgets($fh)) !== false ) {
                if ( preg_match(
                    '/^(\\d{4}-\\d{2}-\\d{2} \\d{2}:\\d{2}:\\d{2}) (.+?): (.*)$/u',
                    $line,
                    $matches) ) { // Pull out timestamp and user
                    $time = $matches[1];
                    $user = $matches[2];
                    $message = $matches[3];

                    if ( $user != "guest" ) {
                        $loggedInUser = new User($user);
                    } else {
                        $loggedInUser = null;
                    }

                    if ( preg_match('/Added domain computer ([^\\s]+)$/um', $message, $matches) ) {
                        $object->setId($matches[1]);
                        Log::saveToDatabase(
                            $loggedInUser,
                            $time,
                            $message,
                            Log::DOMAIN_ADD,
                            $object);
                    } else if ( preg_match('/Removed domain computer ([^\\s]+)$/um', $message, $matches) ) {
                        $object->setId($matches[1]);
                        Log::saveToDatabase(
                            $loggedInUser,
                            $time,
                            $message,
                            Log::DOMAIN_REMOVE,
                            $object);
                    } else if ( preg_match('/Added group (.+)$/um', $message, $matches) ) {
                        $object->setId($matches[1]);
                        Log::saveToDatabase(
                            $loggedInUser,
                            $time,
                            $message,
                            Log::GROUP_ADD,
                            $object);
                    } else if ( preg_match('/Added user ([^\\s]+) to group (.+)$/um', $message, $matches) ) {
                        $object->setId($matches[2]);
                        $related->setId($matches[1]);
                        Log::saveToDatabase(
                            $loggedInUser,
                            $time,
                            $message,
                            Log::GROUP_ADD_USER,
                            $object,
                            $related);
                    } else if ( preg_match(
                        '/Added server directory \'(.+)\' for ([^\\s]+)$/um',
                        $message,
                        $matches) ) {
                        $object->setId($matches[2]);
                        Log::saveToDatabase(
                            $loggedInUser,
                            $time,
                            $message,
                            Log::GROUP_ADD_DIR,
                            $object,
                            null,
                            $matches[1]);
                    } else if ( preg_match(
                        '/Removed user ([^\\s]+) from group (.+)$/um',
                        $message,
                        $matches) ) {
                        $object->setId($matches[2]);
                        $related->setId($matches[1]);
                        Log::saveToDatabase(
                            $loggedInUser,
                            $time,
                            $message,
                            Log::GROUP_REMOVE_USER,
                            $object,
                            $related);
                    } else if ( preg_match(
                        '/Removed server directory \'(.+)\' from ([^\\s]+)$/um',
                        $message,
                        $matches) ) {
                        $object->setId($matches[2]);
                        Log::saveToDatabase(
                            $loggedInUser,
                            $time,
                            $message,
                            Log::GROUP_REMOVE_DIR,
                            $object,
                            null,
                            $matches[1]);
                    } else if ( preg_match(
                        '/Changed group description for ([^\\s]+) to \'(.+)\'$/um',
                        $message,
                        $matches) ) {
                        $object->setId($matches[1]);
                        Log::saveToDatabase(
                            $loggedInUser,
                            $time,
                            $message,
                            Log::GROUP_SET_DESC,
                            $object,
                            null,
                            $matches[2]);
                    } else if ( preg_match(
                        '/Changed group name from (.+) to (.+)\\.$/um',
                        $message,
                        $matches) ) {
                        $object->setId($matches[2]);
                        Log::saveToDatabase(
                            $loggedInUser,
                            $time,
                            $message,
                            Log::GROUP_SET_NAME,
                            $object,
                            null,
                            $matches[2],
                            null,
                            $matches[1]);
                    } else if ( preg_match(
                        '/Set owner to ([^\\s]+) for group ([^\\s]+)$/um',
                        $message,
                        $matches) ) {
                        $object->setId($matches[2]);
                        $related->setId($matches[1]);
                        Log::saveToDatabase(
                            $loggedInUser,
                            $time,
                            $message,
                            Log::GROUP_SET_OWNER,
                            $object,
                            $related);
                    } else if ( preg_match('/Removed group (.+)$/um', $message, $matches) ) {
                        $object->setId($matches[1]);
                        Log::saveToDatabase(
                            $loggedInUser,
                            $time,
                            $message,
                            Log::GROUP_REMOVE,
                            $object);
                    } else if ( preg_match('/Added host ([^\\s]+)$/um', $message, $matches) ) {
                        $object->setId($matches[1]);
                        Log::saveToDatabase(
                            $loggedInUser,
                            $time,
                            $message,
                            Log::HOST_ADD,
                            $object);
                    } else if ( preg_match('/Removed host ([^\\s]+)$/um', $message, $matches) ) {
                        $object->setId($matches[1]);
                        Log::saveToDatabase(
                            $loggedInUser,
                            $time,
                            $message,
                            Log::HOST_REMOVE,
                            $object);
                    } else if ( preg_match('/Changed host ip for ([^\\s])+ to \'(.*)\'$/um', $message, $matches) ) {
                        $object->setId($matches[1]);
                        Log::saveToDatabase(
                            $loggedInUser,
                            $time,
                            $message,
                            Log::HOST_SET_IP,
                            $object,
                            null,
                            $matches[2]);
                    } else if ( preg_match(
                        '/Changed host name from ([^\\s]+) to ([^\\s]+)\\.$/um',
                        $message,
                        $matches) ) {
                        $object->setId($matches[2]);
                        Log::saveToDatabase(
                            $loggedInUser,
                            $time,
                            $message,
                            Log::HOST_SET_NAME,
                            $object,
                            null,
                            $matches[2],
                            null,
                            $matches[1]);
                    } else if ( preg_match('/Added user ([^\\s]+) \\(.*\\)$/um', $message, $matches) ) {
                        $object->setId($matches[1]);
                        Log::saveToDatabase(
                            $loggedInUser,
                            $time,
                            $message,
                            Log::USER_ADD,
                            $object);
                    } else if ( preg_match('/Added user ([^\\s]+) \\(.*\\)$/um', $message, $matches) ) {
                        $object->setId($matches[1]);
                        Log::saveToDatabase(
                            $loggedInUser,
                            $time,
                            $message,
                            Log::USER_ADD,
                            $object);
                    } else if ( preg_match('/Removed user ([^\\s]+)$/um', $message, $matches) ) {
                        $object->setId($matches[1]);
                        Log::saveToDatabase(
                            $loggedInUser,
                            $time,
                            $message,
                            Log::USER_REMOVE,
                            $object);
                    } else if ( preg_match(
                        '/Gave host access for ([^\\s]+) to ([^\\s]+)$/um',
                        $message,
                        $matches) ) {
                        $object->setId($matches[2]);
                        $related->setId($matches[1]);
                        Log::saveToDatabase(
                            $loggedInUser,
                            $time,
                            $message,
                            Log::USER_ADD_HOST,
                            $object,
                            $related);
                    } else if ( preg_match(
                        '/Removed host access to ([^\\s]+) from ([^\\s]+)$/um',
                        $message,
                        $matches) ) {
                        $object->setId($matches[2]);
                        $related->setId($matches[1]);
                        Log::saveToDatabase(
                            $loggedInUser,
                            $time,
                            $message,
                            Log::USER_REMOVE_HOST,
                            $object,
                            $related);
                    } else if ( preg_match('/User ([^\\s]+) locked$/um', $message, $matches) ) {
                        $object->setId($matches[1]);
                        Log::saveToDatabase(
                            $loggedInUser,
                            $time,
                            $message,
                            Log::USER_LOCK,
                            $object);
                    } else if ( preg_match('/User ([^\\s]+) unlocked$/um', $message, $matches) ) {
                        $object->setId($matches[1]);
                        Log::saveToDatabase(
                            $loggedInUser,
                            $time,
                            $message,
                            Log::USER_UNLOCK,
                            $object);
                    } else if ( preg_match(
                        '/Set (?:expiration|shadowexpire) for ([^\\s]+) to ([\\d\/]+)/um',
                        $message,
                        $matches) ) {
                        $object->setId($matches[1]);
                        Log::saveToDatabase(
                            $loggedInUser,
                            $time,
                            $message,
                            Log::USER_SET_EXP,
                            $object,
                            null,
                            null,
                            strftime("%Y-%m-%d %H:%M:%S", strtotime($matches[2])));
                    } else if ( preg_match(
                        '/(?:Cancelled expiration|Removed shadowexpire) for user ([^\\s]+)$/um',
                        $message,
                        $matches) ) {
                        $object->setId($matches[1]);
                        Log::saveToDatabase(
                            $loggedInUser,
                            $time,
                            $message,
                            Log::USER_REMOVE_EXP,
                            $object);
                    } else if ( preg_match(
                        '/Set (?:password expiration|facsimiletelephonenumber) for ([^\\s]+) to ([\\d\/]+)$/um',
                        $message,
                        $matches) ) {
                        $object->setId($matches[1]);
                        Log::saveToDatabase(
                            $loggedInUser,
                            $time,
                            $message,
                            Log::USER_SET_PASS_EXP,
                            $object,
                            null,
                            null,
                            strftime("%Y-%m-%d %H:%M:%S", strtotime($matches[2])));
                    } else if ( preg_match(
                        '/Cancelled password expiration for user ([^\\s]+)$/um',
                        $message,
                        $matches) ) {
                        $object->setId($matches[1]);
                        Log::saveToDatabase(
                            $loggedInUser,
                            $time,
                            $message,
                            Log::USER_REMOVE_PASS_EXP,
                            $object);
                    } else if ( preg_match('/Set classroom-user for ([^\\s]+) to (\d)$/um', $message, $matches) ) {
                        $object->setId($matches[1]);
                        Log::saveToDatabase(
                            $loggedInUser,
                            $time,
                            $message,
                            Log::USER_SET_CLASSROOM,
                            $object,
                            null,
                            $matches[2]);
                    } else if ( preg_match('/Set crashplan for ([^\\s]+) to (.+)$/um', $message, $matches) ) {
                        $object->setId($matches[1]);
                        Log::saveToDatabase(
                            $loggedInUser,
                            $time,
                            $message,
                            Log::USER_SET_CRASHPLAN,
                            $object,
                            null,
                            $matches[2] == 'active' ? 1 : 0);
                    } else if ( preg_match('/Set description for ([^\\s]+) to (.*)$/um', $message, $matches) ) {
                        $object->setId($matches[1]);
                        Log::saveToDatabase(
                            $loggedInUser,
                            $time,
                            $message,
                            Log::USER_SET_DESC,
                            $object,
                            null,
                            $matches[2]);
                    } else if ( preg_match('/Removed description for ([^\\s]+)$/um', $message, $matches) ) {
                        $object->setId($matches[1]);
                        Log::saveToDatabase(
                            $loggedInUser,
                            $time,
                            $message,
                            Log::USER_REMOVE_DESC,
                            $object);
                    } else if ( preg_match(
                        '/Set expiration reason for ([^\\s]+) to (.+)$/um',
                        $message,
                        $matches) ) {
                        $object->setId($matches[1]);
                        Log::saveToDatabase(
                            $loggedInUser,
                            $time,
                            $message,
                            Log::USER_SET_EXP_REASON,
                            $object,
                            null,
                            $matches[2]);
                    } else if ( preg_match('/Removed expiration reason for ([^\\s]+)$/um', $message, $matches) ) {
                        $object->setId($matches[1]);
                        Log::saveToDatabase(
                            $loggedInUser,
                            $time,
                            $message,
                            Log::USER_REMOVE_EXP_REASON,
                            $object);
                    } else if ( preg_match(
                        '/Set (?:email forwarding|postaladdress) for ([^\\s]+) to (.*)$/um',
                        $message,
                        $matches) ) {
                        $object->setId($matches[1]);
                        Log::saveToDatabase(
                            $loggedInUser,
                            $time,
                            $message,
                            Log::USER_SET_FORWARD,
                            $object,
                            null,
                            $matches[2]);
                    } else if ( preg_match('/Set home subfolder for ([^\\s]+) to (.+)$/um', $message, $matches) ) {
                        $object->setId($matches[1]);
                        Log::saveToDatabase(
                            $loggedInUser,
                            $time,
                            $message,
                            Log::USER_SET_SUBFOLDER,
                            $object,
                            null,
                            $matches[2]);
                    } else if ( preg_match('/Set left-campus for ([^\\s]+) to (.+)$/um', $message, $matches) ) {
                        $object->setId($matches[1]);
                        Log::saveToDatabase(
                            $loggedInUser,
                            $time,
                            $message,
                            Log::USER_SET_LEFT,
                            $object,
                            null,
                            $matches[2]);
                    } else if ( preg_match('/Set login shell to (.+) for ([^\\s]+)$/um', $message, $matches) ) {
                        $object->setId($matches[2]);
                        Log::saveToDatabase(
                            $loggedInUser,
                            $time,
                            $message,
                            Log::USER_SET_LOGIN,
                            $object,
                            null,
                            $matches[1]);
                    } else if ( preg_match('/Set login ?shell for ([^\\s]+) to (.+)$/um', $message, $matches) ) {
                        $object->setId($matches[1]);
                        Log::saveToDatabase(
                            $loggedInUser,
                            $time,
                            $message,
                            Log::USER_SET_LOGIN,
                            $object,
                            null,
                            $matches[2]);
                    } else if ( preg_match('/Changed name for ([^\\s]+) to "(.+)"$/um', $message, $matches) ) {
                        $object->setId($matches[1]);
                        Log::saveToDatabase(
                            $loggedInUser,
                            $time,
                            $message,
                            Log::USER_SET_NAME,
                            $object,
                            null,
                            $matches[2]);
                    } else if ( preg_match('/Set non-campus for ([^\\s]+) to (.+)$/um', $message, $matches) ) {
                        $object->setId($matches[1]);
                        Log::saveToDatabase(
                            $loggedInUser,
                            $time,
                            $message,
                            Log::USER_SET_NONCAMPUS,
                            $object,
                            null,
                            $matches[2]);
                    } else if ( preg_match('/Changed password for ([^\\s]+)$/um', $message, $matches) ) {
                        $object->setId($matches[1]);
                        Log::saveToDatabase(
                            $loggedInUser,
                            $time,
                            $message,
                            Log::USER_SET_PASS,
                            $object);
                    } else if ( preg_match(
                        '/Changed username for ([^\\s]+) to ([^\\s]+)\\.$/um',
                        $message,
                        $matches) ) {
                        $object->setId($matches[2]);
                        Log::saveToDatabase(
                            $loggedInUser,
                            $time,
                            $message,
                            Log::USER_SET_USERNAME,
                            $object,
                            null,
                            $matches[2],
                            null,
                            $matches[1]);
                    } else if ( preg_match('/Expiration email sent to ([^\\s]+)\\.$/um', $message, $matches) ) {
                        $object->setId($matches[1]);
                        Log::saveToDatabase(
                            $loggedInUser,
                            $time,
                            $message,
                            Log::EXP_EMAIL_SENT,
                            $object);
                    } else if ( preg_match(
                        '/Biocluster2? access given to user ([^\\s]+)$/um',
                        $message,
                        $matches) ) {
                        $object->setId($matches[1]);
                        Log::saveToDatabase(
                            $loggedInUser,
                            $time,
                            $message,
                            Log::USER_SET_BIOCLUSTER,
                            $object);
                    } else if ( preg_match('/Crashplan archive removed for ([^\\s]+)$/um', $message, $matches) ) {
                        $object->setId($matches[1]);
                        Log::saveToDatabase(
                            $loggedInUser,
                            $time,
                            $message,
                            Log::USER_REMOVE_CRASHPLAN,
                            $object);
                    } else if ( preg_match('/(?:Cleaned up|classroom_cleanup)/um', $message) ) {
                        // Ignore
                    } else {
                        echo $line;
                        $missed++;
                    }
                }
            }
        }
    }

    echo "Done!\n";
    echo "Missed $missed lines.\n";

    echo "Checking for users in ldap but not created in the logs...";
    $all_users = User::all();
    $select_user = "select * from logs join objects on logs.object_id=objects.id where objects.type='user' and logs.event_id=16 and objects.name=:name";
    $missed = 0;
    foreach ( $all_users as $uid ) {
        $userObj = MySQL::getInstance()->selectOne($select_user, [':name' => $uid]);
        if ( !$userObj ) {
            // User not in database, add their creation date
            $user = new User($uid);
            if ( preg_match("/uid=(.*?),/um", $user->getCreator()) ) {
                $creator = null;
            } else {
                $creator = new User($user->getCreator());
            }
            Log::saveToDatabase(
                $creator,
                strftime('%Y-%m-%d %H:%M:%S', $user->getCreateTime()),
                "Added user " . $user->getId() . " (" . $user->getName() . ")",
                Log::USER_ADD,
                $user);
            $missed++;
        }
    }
    echo "Done!\n";
    echo "Added $missed missed users.\n";
}