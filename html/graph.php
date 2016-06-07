<?php
	include 'includes/main.inc.php';
	if($_REQUEST['graph'] == 'usercal'){
		$filter = "(uid=*)";
		$attributes = array('createTimestamp');
		$result = $ldap->search($filter, __LDAP_PEOPLE_OU__, $attributes);
		$calendar = array();
		for($i=0;$i<$result['count'];$i++){
			$date = substr($result[$i]['createtimestamp'][0],0,4)."/".substr($result[$i]['createtimestamp'][0],4,2)."/".substr($result[$i]['createtimestamp'][0],6,2);
			if($date != "2014/08/12"){
			if(isset($calendar[$date])){
				$calendar[$date]++;
			} else {
				$calendar[$date] = 1;
			}
			}
		}
		echo json_encode($calendar);
	}