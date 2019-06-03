<?php
ini_set("display_errors", 1);
chdir(dirname(__FILE__));
set_include_path(get_include_path() . ':../libs');
function __autoload($class_name) {
    if ( file_exists("../libs/" . $class_name . ".class.inc.php") ) {
        require_once $class_name . '.class.inc.php';
    }
}

require_once '../conf/settings.inc.php';

function has_crashplan($uid) {
    $ch = curl_init(__CRASHPLAN_URL__ . "/api/User?username=$uid");
    curl_setopt($ch, CURLOPT_USERPWD, __CRASHPLAN_USER__ . ':' . __CRASHPLAN_PASS__);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HEADER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    $jsonstr = curl_exec($ch);
    curl_close($ch);
    $json = json_decode($jsonstr, true);
    if ( isset($json['data']) && isset($json['data']['totalCount']) && $json['data']['totalCount'] > 0 && $json['data']['users'][0]['status'] == 'Active' ) {
        return true;
    }
    return false;
}

$sapi_type = php_sapi_name();
// If run from command line
if ( $sapi_type != 'cli' ) {
    echo "Error: This script can only be run from the command line.\n";
} else {
    echo "Analyzing users...\n";

    // Connect to ldap
    Ldap::init(__LDAP_HOST__, __LDAP_SSL__, __LDAP_PORT__, __LDAP_BASE_DN__);
    Ldap::getInstance()->set_bind_user(__LDAP_BIND_USER__);
    Ldap::getInstance()->set_bind_pass(__LDAP_BIND_PASS__);
    $users = User::all();

    foreach ( $users as $uid ) {
        $user = new User($uid);
        echo $user->getUsername() . ": \t";
        if ( has_crashplan($uid) ) {
            // Crashplan inactive
            if ( $user->getCrashplan() == false ) {
                echo "crashplan activated\n";
                $user->setCrashplan(true);
            } else {
                echo "crashplan active (already knew)\n";
            }
        } else {
            if ( $user->getCrashplan() == true ) {
                echo "crashplan deactivated\n";
                $user->setCrashplan(false);
            } else {
                echo "crashplan inactive (already knew)\n";
            }
        }
    }
}