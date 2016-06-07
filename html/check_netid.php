<?php
	set_time_limit(10);
	if(isset($_REQUEST['username'])){
		include_once 'includes/main.inc.php';
		
		if(user::is_ad_user($adldap,$_REQUEST['username'])){
			echo '1';
		} else {
			echo '0';
		}
	}
	