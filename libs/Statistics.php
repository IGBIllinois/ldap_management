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
        $result = Ldap::getInstance()->search($filter, __LDAP_PEOPLE_OU__, ['shadowExpire']);
        $count = 0;
        $time = time();
        for ($i = 0; $i < $result['count']; $i++) {
            if ($result[$i]['shadowexpire'][0] >= $time) {
                $count++;
            }
        }
        return $count;
    }

    public static function expiredUsers()
    {
        $filter = "(&(shadowExpire=*)(!(employeetype=classroom)))";
        $result = Ldap::getInstance()->search($filter, __LDAP_PEOPLE_OU__, ['shadowExpire']);
        $count = 0;
        $time = time();
        for ($i = 0; $i < $result['count']; $i++) {
            if ($result[$i]['shadowexpire'][0] < $time) {
                $count++;
            }
        }
        return $count;
    }

    public static function passwordExpiredUsers()
    {
        $filter = "(facsimileTelephoneNumber=*)";
        $result = Ldap::getInstance()->search($filter, __LDAP_PEOPLE_OU__, ['facsimileTelephoneNumber']);
        $count = 0;
        $time = time();
        for ($i = 0; $i < $result['count']; $i++) {
            if ($result[$i]['facsimiletelephonenumber'][0] < $time) {
                $count++;
            }
        }
        return $count;
    }

    public static function leftCampusUsers()
    {
        $filter = "(employeeType=leftcampus)";
        $result = Ldap::getInstance()->search($filter, __LDAP_PEOPLE_OU__, ['']);
        return $result['count'];
    }

    public static function nonCampusUsers()
    {
        $filter = "(employeeType=noncampus)";
        $result = Ldap::getInstance()->search($filter, __LDAP_PEOPLE_OU__, ['']);
        return $result['count'];
    }

    public static function classroomUsers()
    {
        $filter = "(employeeType=classroom)";
        $result = Ldap::getInstance()->search($filter, __LDAP_PEOPLE_OU__, ['']);
        return $result['count'];
    }

    public static function lastMonthUsers()
    {
        $filter = "(authtimestamp=*)";
        $result = Ldap::getInstance()->search($filter, __LDAP_PEOPLE_OU__, ['authtimestamp']);
        $count = 0;
        $time = time() - 60*60*24*30;
        for ($i = 0; $i < $result['count']; $i++) {
            if (strtotime($result[$i]['authtimestamp'][0]) >= $time) {
                $count++;
            }
        }
        return $count;
    }

    public static function neverLoggedInUsers()
    {
        $filter = "(!(authtimestamp=*))";
        $result = Ldap::getInstance()->search($filter, __LDAP_PEOPLE_OU__, ['authtimestamp']);
        return $result['count'];
    }

    public static function groups()
    {
        $filter = "(cn=*)";
        $result = Ldap::getInstance()->search($filter, __LDAP_GROUP_OU__, ['']);
        return $result['count'];
    }

    public static function emptyGroups()
    {
        $filter = "(!(memberUid=*))";
        $result = Ldap::getInstance()->search($filter, __LDAP_GROUP_OU__, ['']);
        return $result['count'];
    }

    private static function changeOverTime($events, $up_event, $down_event, $initial)
    {
        $deltas = [];
        foreach ($events as $event) {
            $date = strftime('%Y-%m-%d', strtotime($event['logtime']));
            if (!isset($deltas[$date])) {
                $deltas[$date] = 0;
            }
            if ($event['event_id'] == $up_event) {
                $deltas[$date] += 1;
            }
            if ($event['event_id'] == $down_event) {
                $deltas[$date] -= 1;
            }
        }
        $totalNow = $initial;
        $totalOverTime = [[strftime('%Y-%m-%d'), $totalNow]];
        foreach ($deltas as $date => $delta) {
            if (!isset($delta[$date])) {
                // If there's not already a data point for this date, add one
                $totalOverTime[] = [$date, $totalNow];
            }
            $dayBefore = strftime('%Y-%m-%d', strtotime($date) - (60*60*24));
            $totalNow -= $delta;
            $totalOverTime[] = [$dayBefore, $totalNow];
        }
        return $totalOverTime;
    }

    public static function usersOverTime()
    {
        MySQL::init(__LOG_DB_HOST__, __LOG_DB_NAME__, __LOG_DB_USER__, __LOG_DB_PASS__);
        $sql = sprintf(
            'select * from logs where event_id=%d or event_id=%d order by logtime desc',
            Log::USER_ADD,
            Log::USER_REMOVE
        );
        $events = MySQL::getInstance()->select($sql);
        return self::changeOverTime($events, Log::USER_ADD, Log::USER_REMOVE, self::users());
    }

    public static function membersOverTime($groupName)
    {
        $group = new Group($groupName);
        MySQL::init(__LOG_DB_HOST__, __LOG_DB_NAME__, __LOG_DB_USER__, __LOG_DB_PASS__);
        $sql = sprintf(
            "select logs.* from logs join objects on objects.id=logs.object_id where objects.name=:group and objects.type='%s' and (event_id=%d or event_id=%d) order by logtime desc",
            Log::TYPE_GROUP,
            Log::GROUP_ADD_USER,
            Log::GROUP_REMOVE_USER
        );
        $events = MySQL::getInstance()->select($sql, [':group' => $groupName]);
        return self::changeOverTime(
            $events,
            Log::GROUP_ADD_USER,
            Log::GROUP_REMOVE_USER,
            count($group->getMemberUIDs())
        );
    }
}