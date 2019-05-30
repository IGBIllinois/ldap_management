<?php
	set_time_limit(10);
	if(isset($_REQUEST['username'])){
		require_once 'includes/main.inc.php';
		
		if(User::isInAD($_REQUEST['username'])){
			echo '1';
		} else {
			echo '0';
		}
	}
	