<?php
	set_time_limit(10);
	if(isset($_REQUEST['username'])){
		include_once 'includes/main.inc.php';
		$adldap = new ldap(__AD_LDAP_HOST__,false,__AD_LDAP_PORT__,__AD_LDAP_PEOPLE_OU__);
		$filter = "(uid=".$_REQUEST['username'].")";
		$results = $adldap->search($filter);
		if($results && $results['count']>0){
			echo '1';
		} else {
			echo '0';
		}
	}
	