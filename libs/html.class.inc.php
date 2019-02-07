<?php
// Various html-generation functions
class html {

	//get_pages_html()
	//$url - url of page
	//$num_records - number of items
	//$start - start index of items
	//$count - number of items per page
	//returns pagenation to navigate between pages of devices
	public static function get_pages_html($url,$num_records,$start,$count) {

	        $num_pages = ceil($num_records/$count);
        	$current_page = $start / $count + 1;
	        if (strpos($url,"?")) {
        	        $url .= "&start=";
	        }
	        else {
        	        $url .= "?start=";
	        }

        	$pages_html = "<nav class='mt-2'><ul class='pagination justify-content-center'>";

	        if ($current_page > 1) {
        	        $start_record = $start - $count;
        	        $pages_html .= "<li class='page-item'><a class='page-link' href='".$url."0'><span class='fa fa-angle-double-left'></span></a></li> ";
                	$pages_html .= "<li class='page-item'><a class='page-link' href='" . $url . $start_record . "'><span class='fa fa-angle-left'></span></a></li> ";
	        }
        	else {
	        		$pages_html .= "<li class='page-item disabled'><a class='page-link' href='#'><span class='fa fa-angle-double-left'></span></a></li> ";
                	$pages_html .= "<li class='page-item disabled'><a class='page-link' href='#'><span class='fa fa-angle-left'></span></a></li>";
	        }

			$pages_to_show = 10;
			$pages_to_pad = 5;

			$start_page = max(0,$current_page-$pages_to_pad);
			$end_page = min($num_pages, $start_page+$pages_to_show);
			if($end_page - $start_page < $pages_to_show){
				$start_page = max(0,$current_page-$pages_to_show);
			}
			if($start_page>0){
				$pages_html .= "<li class='page-item disabled'><a class='page-link' href='#'>...</a></li>";
			}
        	for ($i=$start_page; $i<$end_page; $i++) {
                	$start_record = $count * $i;
	                if ($i == $current_page - 1) {
        	                $pages_html .= "<li class='page-item active'>";
                	}
	                else {
        	                $pages_html .= "<li class='page-item'>";
                	}
	                $page_number = $i + 1;
        	        $pages_html .= "<a class='page-link' href='" . $url . $start_record . "'>" . $page_number . "</a></li>";
        	}
        	
        	if ($num_pages > $end_page){
	        	$pages_html .= "<li class='page-item disabled'><a class='page-link' href='#'>...</a></li>";
        	}

	        if ($current_page < $num_pages) {
        	        $start_record = $start + $count;
                	$pages_html .= "<li class='page-item'><a class='page-link' href='" . $url . $start_record . "'><span class='fa fa-angle-right'></span></a></li> ";
                	$pages_html .= "<li class='page-item'><a class='page-link' href='".$url.(($num_pages-1)*$count)."'><span class='fa fa-angle-double-right'></span></a></li> ";
	        }
        	else {
                	$pages_html .= "<li class='page-item disabled'><a class='page-link' href='#'><span class='fa fa-angle-right'></span></a></li>";
                	$pages_html .= "<li class='page-item disabled'><a class='page-link' href='#'><span class='fa fa-angle-double-right'></span></a></li> ";
	        }
        	$pages_html .= "</ul></nav>";
	        return $pages_html;

	}
	
	public static function get_list_users_description($search,$filter,$sort,$asc){
		$description = array();
		if($search != ""){
			$description[] = "Search: '$search'";
		}
		if($filter != "none"){
			switch($filter){
				case 'expiring':
					$description[] = "expiring users only";
					break;
				case 'expired':
					$description[] = "expired users only";
					break;
				case 'passwordexpired':
					$description[] = "only expired passwords";
					break;
				case 'left':
					$description[] = "left-campus only";
					break;
			}
		}
		if($sort != "username" || $asc != "true"){
			switch($sort){
				case 'username':
					$description[] = "sorted by username";
					break;
				case 'name':
					$description[] = "sorted by name";
					break;
				case 'emailforward':
					$description[] = "sorted by email";
					break;
				case 'password_expiration':
					$description[] = "sorted by password expiration";
					break;
				case 'expiration':
					$description[] = "sorted by expiration";
					break;
			}
		}
		if($asc != "true"){
			$description[] = "descending";
		}
		
		return implode(", ", $description);
	}
	public static function get_list_users_description_from_cookies(){
		$search = (isset($_COOKIE['lastUserSearch'])?$_COOKIE['lastUserSearch']:'');
		$filter = (isset($_COOKIE['lastUserSearchFilter'])?$_COOKIE['lastUserSearchFilter']:'none');
		$sort = (isset($_COOKIE['lastUserSearchSort'])?$_COOKIE['lastUserSearchSort']:'username');
		$asc = (isset($_COOKIE['lastUserSearchAsc'])?$_COOKIE['lastUserSearchAsc']:'true');
		return self::get_list_users_description($search,$filter,$sort,$asc);
	}
	public static function get_list_users_url($search,$filter,$sort,$asc){
		$get_array = array('search'=>$search,'filter'=>$filter,'sort'=>$sort,'asc'=>$asc);
		return 'list_users.php?'.http_build_query($get_array);
	}
	public static function get_list_users_url_from_cookies(){
		$search = (isset($_COOKIE['lastUserSearch'])?$_COOKIE['lastUserSearch']:'');
		$filter = (isset($_COOKIE['lastUserSearchFilter'])?$_COOKIE['lastUserSearchFilter']:'none');
		$sort = (isset($_COOKIE['lastUserSearchSort'])?$_COOKIE['lastUserSearchSort']:'username');
		$asc = (isset($_COOKIE['lastUserSearchAsc'])?$_COOKIE['lastUserSearchAsc']:'true');
		return self::get_list_users_url($search,$filter,$sort,$asc);
	}

	// Returns the number of pages for a given number of records and count per page
	public static function get_num_pages($numRecords,$count) {
	        $numPages = floor($numRecords/$count);
        	$remainder = $numRecords % $count;
	        if ($remainder > 0) {
        	        $numPages++;
	        }
        	return $numPages;
	}

	// Calculates and returns the urls to go back or forward from the given month
	public static function get_url_navigation($url,$start_date,$end_date,$get_array = array()) {
	        $previous_end_date = date('Ymd',strtotime('-1 second', strtotime($start_date)));
        	$previous_start_date = substr($previous_end_date,0,4) . substr($previous_end_date,4,2) . "01";
	        $next_start_date = date('Ymd',strtotime('+1 day', strtotime($end_date)));
        	$next_end_date = date('Ymd',strtotime('-1 second',strtotime('+1 month',strtotime($next_start_date))));
	        $next_get_array = array_merge(array('start_date'=>$next_start_date,'end_date'=>$next_end_date),$get_array);
        	$previous_get_array = array_merge(array('start_date'=>$previous_start_date,'end_date'=>$previous_end_date),$get_array);
	        $back_url = $_SERVER['PHP_SELF'] . "?" . http_build_query($previous_get_array);
        	$forward_url = $_SERVER['PHP_SELF'] . "?" . http_build_query($next_get_array);
	        return array('back_url'=>$back_url,'forward_url'=>$forward_url);

	}

	/**
	 * Returns trs for the given users list
	 * @param user[] $users
	 * @param bool $showexpiration
	 * @param bool $showpwdlastset
	 * @return string
	 */
	public static function get_users_rows($users,$showexpiration=false, $showpwdlastset=true) {
		$i_start = 0;
		$i_count = count($users);
		
		$users_html = "";
		for ($i=$i_start;$i<$i_count;$i++) {
	        if (array_key_exists($i,$users)) {
        		$users_html .= "<tr>";
        		$users_html .= "<td width='14px'><input type='checkbox' name='selected[".$users[$i]->get_username()."]'/></td>";
            	$users_html .= "<td class='pl-2 d-flex'><a class='mr-auto' href='user.php?uid=" . $users[$i]->get_username() . "'>";
				$users_html .= $users[$i]->get_username() . "</a>";
				if(!$users[$i]->get_classroom()){ // Don't show expiration warnings for classroom users
					if($users[$i]->is_expired()){
						$users_html .= " <span class='my-auto ml-1 far fa-clock text-danger' title='User expired'></span>";
					} else if($users[$i]->is_expiring()) {
						$users_html .= " <span class='my-auto ml-1 far fa-clock text-warning' title='User set to expire'></span>";
					}
				}
				if($users[$i]->get_leftcampus()){
					$users_html .= " <span class='my-auto ml-1 fa fa-graduation-cap text-warning' title='User left UIUC'></span>";
				}
				if($users[$i]->get_noncampus()){
					$users_html .= " <span class='my-auto ml-1 fa fa-graduation-cap text-info' title='User not from UIUC'></span>";
				}
				if($users[$i]->get_crashplan()){
					$users_html .= " <span class='my-auto ml-1 far fa-hdd text-success' title='User has Crashplan'></span>";
				}
				if($users[$i]->is_password_expired()){
					$users_html .= " <span class='my-auto ml-1 fa fa-key text-danger' title='Password expired'></span>";
				} else if($users[$i]->get_password_expiration() != null && $users[$i]->get_password_expiration() < time()+(60*60*24*30)){
					$users_html .= " <span class='my-auto ml-1 fa fa-key text-warning' title='Password expiring soon'></span>";
				}
				if($users[$i]->get_classroom()){
					$users_html .= " <span class='my-auto ml-1 fa fa-book text-info' title='Classroom User'></span>";
				}
				if($users[$i]->get_last_login() >= time()-60*60*24*30){
					$users_html .= " <span class='my-auto ml-1 fas fa-sign-in-alt text-success' title='Logged in within past 30 days'></span>";
				}
				if($users[$i]->get_last_login() === null){
					$users_html .= " <span class='my-auto ml-1 fas fa-sign-in-alt text-danger' title='Never logged in'></span>";
				}
				$users_html .= "</td>";
                $users_html .= "<td>" . $users[$i]->get_name(). "</td>";
				$users_html .= "<td>" . $users[$i]->get_emailforward(). "</td>";
				$users_html .= "<td>" . ($users[$i]->get_password_expiration()===null?'':date('m/d/Y',$users[$i]->get_password_expiration())). "</td>";
				if($showpwdlastset){
					$users_html .= "<td class='d-xxl-table-cell d-none'>" . date('m/d/Y', $users[$i]->get_passwordSet()) . "</td>";
				}
				if($showexpiration){
					$users_html .= "<td>" . date('m/d/Y',$users[$i]->get_expiration()). "</td>";
					$users_html .= "<td class='d-xxl-table-cell d-none'>".$users[$i]->get_expiration_reason()."</td>";
				}
				$users_html .= "<td>" . ($users[$i]->get_last_login()===null?'':date('m/d/Y', $users[$i]->get_last_login()))."</td>";
        		$users_html .= "</tr>";
			}
        }
		return $users_html;
	}
	
	public static function get_classroom_users_rows($users){
		$users_html = "";
		for($i=0; $i<count($users);$i++){
			if(array_key_exists($i,$users)){
				$groups = $users[$i]->get_groups();
				usort($groups,'html::username_cmp');
				$users_html .= "<tr>";
				$users_html .= "<td width='14px'><input type='checkbox' name='selected[".$users[$i]->get_username()."]'/></td>";
            	$users_html .= "<td class='pl-2 d-flex'><a class='mr-auto' href='user.php?uid=" . $users[$i]->get_username() . "'>";
				$users_html .= $users[$i]->get_username() . "</a>";
				if($users[$i]->get_expiration() != null){ 
					if($users[$i]->is_expired()){
						$users_html .= " <span class='my-auto ml-1 far fa-clock text-danger' title='User expired'></span>";
					}
				}
				$users_html .= "</td>";
				$users_html .= "<td>" . $users[$i]->get_description(). "</td>";
				$users_html .= "<td>" . implode(', ', $groups). "</td>";
				$users_html .= "<td>" . ($users[$i]->get_expiration()!=null?date('m/d/Y',$users[$i]->get_expiration()):'') . "</td>";
				$users_html .="</tr>";
			}
		}
		return $users_html;
	}
	
	// Returns trs for the given users list
	public static function get_groups_rows($groups) {
		$i_start = 0;
		$i_count = count($groups);
		
		$groups_html = "";
		for ($i=$i_start;$i<$i_count;$i++) {
		        if (array_key_exists($i,$groups)) {			        
            		$groups_html .= "<tr>";
                	$groups_html .= "<td class='pl-2'><a href='group.php?gid=" . $groups[$i]['name'] . "'>";
					$groups_html .= $groups[$i]['name'] . "</a></td>";
	                $groups_html .= "<td>" . $groups[$i]['description']. "</td>";
	                $groups_html .= "<td>" . $groups[$i]['owner'] . "</td>";
	                $groups_html .= "<td>" . implode(", ", $groups[$i]['dirs']). "</td>";
					$groups_html .= "<td>" . $groups[$i]['members']. "</td>";
            		$groups_html .= "</tr>";
			}
        }
		return $groups_html;
	}
	// Returns trs for the given computers list
	public static function get_computers_rows($computers) {
		$i_start = 0;
		$i_count = count($computers);
		
		$computers_html = "";
		for ($i=$i_start;$i<$i_count;$i++) {
		        if (array_key_exists($i,$computers)) {
                		$computers_html .= "<tr>";
	                	$computers_html .= "<td class='pl-2'>";
	                	$computers_html .= "<a href='computer.php?uid=" . $computers[$i]['name'] . "'>";
						$computers_html .= $computers[$i]['name'];
						$computers_html .= "</a>";
						$computers_html .= "</td>";
                		$computers_html .= "</tr>";
			}
        }
		return $computers_html;
	}
	
	public static function get_hosts_rows($hosts){
		$igb = array();
		$biotech = array();
		$other = array();
		foreach($hosts as $host){
			if(strpos($host['name'],".biotec") !== false){
				$biotech[] = $host;
			} else if(strpos($host['name'],".igb") !== false){
				$igb[] = $host;
			} else {
				$other[] = $host;
			}
		}
		sort($igb);
		sort($biotech);
		sort($other);
		
		$hostshtml = "<tr><th class='pl-2'>IGB Hosts</th><th>IP</th><th># of users</th></tr>";
		foreach($igb as $host){
			$hostshtml .= "<tr><td class='pl-2'><a href='host.php?hid=".$host['name']."'>".$host['name']."</a></td><td>".$host['ip']."</td><td>".$host['numusers']."</td></tr>";
		}

		$hostshtml .= "<tr><th class='pl-2'>Biotech Hosts</th><th>IP</th><th># of users</th></tr>";
		foreach($biotech as $host){
			$hostshtml .= "<tr><td class='pl-2'><a href='host.php?hid=".$host['name']."'>".$host['name']."</a></td><td>".$host['ip']."</td><td>".$host['numusers']."</td></tr>";
		}

		if(count($other)!=0){
			$hostshtml .= "<tr><th class='pl-2'>Other Hosts</th><th>IP</th><th># of users</th></tr>";
			foreach($other as $host){
				$hostshtml .= "<tr><td class='pl-2'><a href='host.php?hid=".$host['name']."'>".$host['name']."</a></td><td>".$host['ip']."</td><td>".$host['numusers']."</td></tr>";
			}
		}
		
		return $hostshtml;
	}
	
	public static function sort_icon($column,$sort,$asc){
		if($sort==$column){
			return " <span class='fa fa-sort-amount-".($asc=="false"?'desc':'asc')."'> </span>";
		}
		return '';
	}
	
	public static function username_cmp($a,$b){
		if($a==$b){
			return 0;
		}
		// Empty strings should show up at the end of the list
		if($a == ''){
			return 1;
		}
		if($b == ''){
			return -1;
		}
		$aalpha = strcspn($a,'0123456789');
		$balpha = strcspn($b,'0123456789');
		if($aalpha==$balpha && substr($a, 0, $aalpha)==substr($b,0,$balpha)){
			$anum = substr($a,$aalpha);
			$bnum = substr($b,$balpha);
			if(is_numeric($anum) && is_numeric($bnum)){
				return intval($anum)<intval($bnum)?-1:(intval($anum)==intval($bnum)?0:1);
			} else {
				return strcasecmp($a,$b);
			}
		} else {
			return strcasecmp($a,$b);
		}
	}

	// Takes a date given as 'YYYYmmdd' and returns 'mm/dd/YYYY'
	public static function get_pretty_date($date) {
		return substr($date,4,2) . "/" . substr($date,6,2) . "/" . substr($date,0,4);
	}
	// Takes a date given as YYYY-mm-dd HH:MM:SS and returns mm/dd/YYYY
	public static function get_pretty_date_mysql($date){
		$date_arr = date_parse($date);
		$date_str = $date_arr['year'].($date_arr['month']<10?'0':'').$date_arr['month'].($date_arr['day']<10?'0':'').$date_arr['day'];
		return self::get_pretty_date($date_str);
	}

	// Takes a size and the unit for that size ('B', 'KB', 'MB', 'GB') and returns a human-readable size
	public static function human_readable_size($usage,$unit='MB',$decimal=4){
		$units = array('B','KB','MB','GB','TB','PB');
		$i = array_search(strtoupper($unit),$units);
		while(abs($usage)>1024){
			$usage /= 1024.0;
			$i++;
		}
		return number_format($usage,$decimal).' '.$units[$i];
	}
	
		
	public static function success_message($message){
		return "<div class='alert alert-success'>".$message."</div>";
	}
	public static function error_message($message){
		return "<div class='alert alert-danger'>".$message."</div>";
	}
	public static function warning_message($message){
		return "<div class='alert alert-warning'>".$message."</div>";
	}
}

?>
