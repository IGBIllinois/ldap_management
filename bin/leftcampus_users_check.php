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

$sapi_type = php_sapi_name();
// If run from command line
if ( $sapi_type != 'cli' ) {
    echo "Error: This script can only be run from the command line.\n";
} else {
    echo "Connecting to IGB LDAP...\n";

    // Connect to ldap
    Ldap::init(__LDAP_HOST__, __LDAP_SSL__, __LDAP_PORT__, __LDAP_BASE_DN__, __LDAP_TLS__);
    Ldap::getInstance()->set_bind_user(__LDAP_BIND_USER__);
    Ldap::getInstance()->set_bind_pass(__LDAP_BIND_PASS__);

    echo "Connecting to AD...\n";
    $adldap = new Ldap(__AD_LDAP_HOST__, __AD_LDAP_SSL__, __AD_LDAP_PORT__, __AD_LDAP_PEOPLE_OU__, __AD_LDAP_TLS__);
    $adldap->set_bind_user(__AD_LDAP_BIND_USER__);
    $adldap->set_bind_pass(__AD_LDAP_BIND_PASS__);

    echo "Fetching IGB Users...\n";
    $users_group = new Group('igb_users');
    $igb_users = $users_group->getMemberUIDs();
    $users = array();
    foreach ( $igb_users as $igb_user ) {
        $users[$igb_user] = false;
    }


    echo "Fetching AD Users...\n";
    $moreADUsers = true;
    $start = 0;
    $range = 1500;
    while ( $moreADUsers ) {
        $attr = 'member;range=' . $start . '-' . ($start + $range - 1);
        $ad_attributes = array($attr);
        $campus_members = $adldap->search("(cn=UIUC Campus Accounts)", __AD_LDAP_GROUP_OU__, $ad_attributes);

        $member_attr = $campus_members[0][0];
//        echo $member_attr."\n";

        for ( $i = 0; $i < $campus_members[0][$member_attr]['count']; $i++ ) {
            $matches = array();
            preg_match('/^CN=([a-zA-Z0-9\\-_\\.]+),/u', $campus_members[0][$member_attr][$i], $matches);
            if ( !isset($matches[1]) ) {
                echo $campus_members[0][$member_attr][$i] . "\n";
            }
            if ( in_array($matches[1], $igb_users) ) {
                $users[$matches[1]] = true;
            }
        }

        $start += $range;
        if ( $campus_members[0][$member_attr]['count'] < $range ) {
            $moreADUsers = false;
        }
    }
    echo "Done.\n";
    foreach ( $users as $user => $active ) {
        $user_obj = new User($user);
        echo $user . ": ";
        if ( !$user_obj->isClassroom() ) {
            if ( !$user_obj->getNonCampus() ) {
                if ( $active ) {
                    // Active AD user
                    if ( $user_obj->getLeftCampus() ) {
                        echo "on campus";
                        $user_obj->setLeftCampus(false);
                    } else {
                        echo "on campus (already knew)";
                    }
                } else {
                    // Not active AD user
                    if ( $user_obj->getLeftCampus() ) {
                        echo "left campus (already knew)";
                    } else {
                        echo "left campus";
                        $user_obj->setLeftCampus(true);
                    }
                }
            } else {
                echo "non-campus";
            }
        } else {
            echo "classroom";
        }
        echo "\n";
    }
}