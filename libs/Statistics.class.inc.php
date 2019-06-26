<?php

class Statistics
{

    public static function users() {
        $result = User::all();
        return count($result);
    }

    public static function expiringUsers() {
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

    public static function expiredUsers() {
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

    public static function passwordExpiredUsers() {
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

    public static function leftCampusUsers() {
        $filter = "(employeeType=leftcampus)";
        $result = Ldap::getInstance()->search($filter, __LDAP_PEOPLE_OU__, array(''));
        return $result['count'];
    }

    public static function nonCampusUsers() {
        $filter = "(employeeType=noncampus)";
        $result = Ldap::getInstance()->search($filter, __LDAP_PEOPLE_OU__, array(''));
        return $result['count'];
    }

    public static function classroomUsers() {
        $filter = "(employeeType=classroom)";
        $result = Ldap::getInstance()->search($filter, __LDAP_PEOPLE_OU__, array(''));
        return $result['count'];
    }

    public static function lastMonthUsers() {
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

    public static function neverLoggedInUsers() {
        $filter = "(!(authtimestamp=*))";
        $result = Ldap::getInstance()->search($filter, __LDAP_PEOPLE_OU__, array('authtimestamp'));
        return $result['count'];
    }

    public static function groups() {
        $filter = "(cn=*)";
        $result = Ldap::getInstance()->search($filter, __LDAP_GROUP_OU__, array(''));
        return $result['count'];
    }

    public static function emptyGroups() {
        $filter = "(!(memberUid=*))";
        $result = Ldap::getInstance()->search($filter, __LDAP_GROUP_OU__, array(''));
        return $result['count'];
    }

    public static function usersOverTime() {
        MySQL::init(__LOG_DB_HOST__, __LOG_DB_NAME__, __LOG_DB_USER__, __LOG_DB_PASS__);
        $sql = sprintf('select * from logs where event_id=%d or event_id=%d order by logtime desc',Log::USER_ADD, Log::USER_REMOVE);
        $events = MySQL::getInstance()->select($sql);
        $deltas = array();
        foreach ($events as $event){
            $date = strftime('%Y-%m-%d',strtotime($event['logtime']));
            if(!isset($deltas[$date])){
                $deltas[$date] = 0;
            }
            if($event['event_id'] == Log::USER_ADD){
                $deltas[$date] += 1;
            }
            if($event['event_id'] == Log::USER_REMOVE){
                $deltas[$date] -= 1;
            }
        }
        $totalUsersNow = self::users();
        $usersOverTime = array( array(strftime('%Y-%m-%d'), $totalUsersNow) );
        foreach($deltas as $date=>$delta){
            $dayBefore = strftime('%Y-%m-%d', strtotime($date)-(60*60*24));
            $totalUsersNow -= $delta;
            $usersOverTime[] = array($dayBefore, $totalUsersNow);
        }
        return $usersOverTime;
    }
}