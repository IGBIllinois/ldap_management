<?php
	include 'includes/main.inc.php';
	if($_REQUEST['graph'] == 'usercal'){
		$filter = "(!(employeeType=classroom))";
		$attributes = array('createTimestamp');
		$result = Ldap::getInstance()->search($filter, __LDAP_PEOPLE_OU__, $attributes);
		$calendar = array();
		$years = array();
		for($i=0;$i<$result['count'];$i++){
			$date = substr($result[$i]['createtimestamp'][0],0,4)."/".substr($result[$i]['createtimestamp'][0],4,2)."/".substr($result[$i]['createtimestamp'][0],6,2);
			$year = substr($date,0,4);
			if($date != "2014/08/12"){
				$years[$year] = 1;
				if(isset($calendar[$date])){
					$calendar[$date]++;
				} else {
					$calendar[$date] = 1;
				}
			}
		}
		$calarray = array();
		foreach($calendar as $key=>$value){
			$calarray[] = array($key,$value);
		}
		echo json_encode(array('data'=>$calarray,'years'=>count(array_keys($years))));
	}
	if($_REQUEST['graph'] == "passcal"){
		Ldap::getInstance()->set_bind_user(__LDAP_BIND_USER__);
		Ldap::getInstance()->set_bind_pass(__LDAP_BIND_PASS__);
		$filter = "(cn=igb_users)";
		$attributes = array('memberUid');
		$groupmembers = Ldap::getInstance()->search($filter, __LDAP_GROUP_OU__, $attributes);
		
		$attributes = array('sambapwdlastset');
		$calendar = array();
		$years = array();
		for($i=0; $i<$groupmembers[0]['memberuid']['count']; $i++){
			$filter = "(uid=".$groupmembers[0]['memberuid'][$i].")";
			$result = Ldap::getInstance()->search($filter, __LDAP_PEOPLE_OU__, $attributes);
			if(isset($result[0]['sambapwdlastset'])){
				$date = strftime('%Y/%m/%d', $result[0]['sambapwdlastset'][0]);
				$year = strftime('%Y',$result[0]['sambapwdlastset'][0]);
				if($year >= 2000){
					$years[$year] = 1;
					if(isset($calendar[$date])){
						$calendar[$date]++;
					} else {
						$calendar[$date] = 1;
					}
				}
			}
		}
		$calarray = array();
		foreach($calendar as $key=>$value){
			$calarray[] = array($key,$value);
		}
		echo json_encode(array('data'=>$calarray, 'years'=>count(array_keys($years))));

	}
	if($_REQUEST['graph'] == "d3passcal"){
		Ldap::getInstance()->set_bind_user(__LDAP_BIND_USER__);
		Ldap::getInstance()->set_bind_pass(__LDAP_BIND_PASS__);
		$filter = "(cn=igb_users)";
		$attributes = array('memberUid');
		$groupmembers = Ldap::getInstance()->search($filter, __LDAP_GROUP_OU__, $attributes);

		$attributes = array('sambapwdlastset');
		$calendar = array();
		for($i=0; $i<$groupmembers[0]['memberuid']['count']; $i++){
			$filter = "(uid=".$groupmembers[0]['memberuid'][$i].")";
			$result = Ldap::getInstance()->search($filter, __LDAP_PEOPLE_OU__, $attributes);
			if(isset($result[0]['sambapwdlastset'])){
				$date = strftime('%Y-%m-%d', $result[0]['sambapwdlastset'][0]);
				$year = strftime('%Y',$result[0]['sambapwdlastset'][0]);
				if($year >= 2000){
					$years[$year] = 1;
					if(isset($calendar[$date])){
						$calendar[$date]++;
					} else {
						$calendar[$date] = 1;
					}
				}
			}
		}

		header("Content-type: text/csv");
		echo "date,count\n";
		foreach($calendar as $key=>$value){
			echo $key." 11:00:00,$value\n";
		}
	}