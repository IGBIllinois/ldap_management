<?php

require_once 'includes/main.inc.php';
if ($_REQUEST['graph'] == 'usercal') {
    $filter = "(!(employeeType=classroom))";
    $attributes = ['createTimestamp'];
    $result = Ldap::getInstance()->search($filter, __LDAP_PEOPLE_OU__, $attributes);
    $calendar = [];
    $minYear = 3000;
    $maxYear = 0;
    for ($i = 0; $i < $result['count']; $i++) {
        $date = substr($result[$i]['createtimestamp'][0], 0, 4) . "/" . substr(
                $result[$i]['createtimestamp'][0],
                4,
                2
            ) . "/" . substr(
                    $result[$i]['createtimestamp'][0],
                    6,
                    2
                );
        $year = substr($date, 0, 4);
        if ($date != "2014/08/12") {
            $minYear = $minYear > $year ? $year : $minYear;
            $maxYear = $maxYear < $year ? $year : $maxYear;
            if (isset($calendar[$date])) {
                $calendar[$date]++;
            } else {
                $calendar[$date] = 1;
            }
        }
    }
    $calarray = [];
    foreach ($calendar as $key => $value) {
        $calarray[] = [$key, $value];
    }
    echo json_encode(['data' => $calarray, 'years' => $maxYear - $minYear + 1]);
} else {
    if ($_REQUEST['graph'] == "passcal") {
        Ldap::getInstance()->set_bind_user(__LDAP_BIND_USER__);
        Ldap::getInstance()->set_bind_pass(__LDAP_BIND_PASS__);
        $filter = "(cn=igb_users)";
        $attributes = ['memberUid'];
        $groupmembers = Ldap::getInstance()->search($filter, __LDAP_GROUP_OU__, $attributes);

        $attributes = ['sambapwdlastset'];
        $calendar = [];
        $minYear = 3000;
        $maxYear = 0;
        for ($i = 0; $i < $groupmembers[0]['memberuid']['count']; $i++) {
            $filter = "(uid=" . $groupmembers[0]['memberuid'][$i] . ")";
            $result = Ldap::getInstance()->search($filter, __LDAP_PEOPLE_OU__, $attributes);
            if (isset($result[0]['sambapwdlastset'])) {
                $date = strftime('%Y/%m/%d', $result[0]['sambapwdlastset'][0]);
                $year = strftime('%Y', $result[0]['sambapwdlastset'][0]);
                if ($year >= 2000) {
                    $minYear = $minYear > $year ? $year : $minYear;
                    $maxYear = $maxYear < $year ? $year : $maxYear;
                    if (isset($calendar[$date])) {
                        $calendar[$date]++;
                    } else {
                        $calendar[$date] = 1;
                    }
                }
            }
        }
        $calarray = [];
        foreach ($calendar as $key => $value) {
            $calarray[] = [$key, $value];
        }
        echo json_encode(['data' => $calarray, 'years' => $maxYear - $minYear + 1]);
    } else {
        if ($_REQUEST['graph'] == 'userline') {
            echo json_encode(Statistics::usersOverTime());
        } else {
            if ($_REQUEST['graph'] == 'memberline') {
                echo json_encode(Statistics::membersOverTime($_REQUEST['group']));
            } else {
                if ($_REQUEST['graph'] == "d3passcal") {
                    Ldap::getInstance()->set_bind_user(__LDAP_BIND_USER__);
                    Ldap::getInstance()->set_bind_pass(__LDAP_BIND_PASS__);
                    $filter = "(cn=igb_users)";
                    $attributes = ['memberUid'];
                    $groupmembers = Ldap::getInstance()->search($filter, __LDAP_GROUP_OU__, $attributes);

                    $attributes = ['sambapwdlastset'];
                    $calendar = [];
                    for ($i = 0; $i < $groupmembers[0]['memberuid']['count']; $i++) {
                        $filter = "(uid=" . $groupmembers[0]['memberuid'][$i] . ")";
                        $result = Ldap::getInstance()->search($filter, __LDAP_PEOPLE_OU__, $attributes);
                        if (isset($result[0]['sambapwdlastset'])) {
                            $date = strftime('%Y-%m-%d', $result[0]['sambapwdlastset'][0]);
                            $year = strftime('%Y', $result[0]['sambapwdlastset'][0]);
                            if ($year >= 2000) {
                                $years[$year] = 1;
                                if (isset($calendar[$date])) {
                                    $calendar[$date]++;
                                } else {
                                    $calendar[$date] = 1;
                                }
                            }
                        }
                    }

                    header("Content-type: text/csv");
                    echo "date,count\n";
                    foreach ($calendar as $key => $value) {
                        echo $key . " 11:00:00,$value\n";
                    }
                }
            }
        }
    }
}