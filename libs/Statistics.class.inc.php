<?php

class Statistics
{

    public static function users()
    {
        $result = User::all();
        return count($result);
    }

    public static function expiringUsers()
    {
        $filter = "(&(shadowExpire=*)(!(employeetype=classroom)))";
        $result = Ldap::getInstance()->search($filter, __LDAP_PEOPLE_OU__, array('shadowExpire'));
        $count = 0;
        $time = time();
        for ( $i = 0; $i < $result['count']; $i++ ) {
            if ( $result[$i]['shadowexpire'][0] >= $time ) {
                $count++;
            }
        }
        return $count;
    }

    public static function expiredUsers()
    {
        $filter = "(&(shadowExpire=*)(!(employeetype=classroom)))";
        $result = Ldap::getInstance()->search($filter, __LDAP_PEOPLE_OU__, array('shadowExpire'));
        $count = 0;
        $time = time();
        for ( $i = 0; $i < $result['count']; $i++ ) {
            if ( $result[$i]['shadowexpire'][0] < $time ) {
                $count++;
            }
        }
        return $count;
    }

    public static function passwordExpiredUsers()
    {
        $filter = "(facsimileTelephoneNumber=*)";
        $result = Ldap::getInstance()->search($filter, __LDAP_PEOPLE_OU__, array('facsimileTelephoneNumber'));
        $count = 0;
        $time = time();
        for ( $i = 0; $i < $result['count']; $i++ ) {
            if ( $result[$i]['facsimiletelephonenumber'][0] < $time ) {
                $count++;
            }
        }
        return $count;
    }

    public static function leftCampusUsers()
    {
        $filter = "(employeeType=leftcampus)";
        $result = Ldap::getInstance()->search($filter, __LDAP_PEOPLE_OU__, array(''));
        return $result['count'];
    }

    public static function nonCampusUsers()
    {
        $filter = "(employeeType=noncampus)";
        $result = Ldap::getInstance()->search($filter, __LDAP_PEOPLE_OU__, array(''));
        return $result['count'];
    }

    public static function classroomUsers()
    {
        $filter = "(employeeType=classroom)";
        $result = Ldap::getInstance()->search($filter, __LDAP_PEOPLE_OU__, array(''));
        return $result['count'];
    }

    public static function lastMonthUsers()
    {
        $filter = "(authtimestamp=*)";
        $result = Ldap::getInstance()->search($filter, __LDAP_PEOPLE_OU__, array('authtimestamp'));
        $count = 0;
        $time = time() - 60 * 60 * 24 * 30;
        for ( $i = 0; $i < $result['count']; $i++ ) {
            if ( strtotime($result[$i]['authtimestamp'][0]) >= $time ) {
                $count++;
            }
        }
        return $count;
    }

    public static function neverLoggedInUsers()
    {
        $filter = "(!(authtimestamp=*))";
        $result = Ldap::getInstance()->search($filter, __LDAP_PEOPLE_OU__, array('authtimestamp'));
        return $result['count'];
    }

    public static function groups()
    {
        $filter = "(cn=*)";
        $result = Ldap::getInstance()->search($filter, __LDAP_GROUP_OU__, array(''));
        return $result['count'];
    }

    public static function emptyGroups()
    {
        $filter = "(!(memberUid=*))";
        $result = Ldap::getInstance()->search($filter, __LDAP_GROUP_OU__, array(''));
        return $result['count'];
    }
}