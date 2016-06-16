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

        	$pages_html = "<nav><ul class='pagination pagination-centered'>";

	        if ($current_page > 1) {
        	        $start_record = $start - $count;
                	$pages_html .= "<li><a href='" . $url . $start_record . "'>&laquo;</a></li> ";
	        }
        	else {
                	$pages_html .= "<li class='disabled'><a href='#'>&laquo;</a></li>";
	        }

        	for ($i=0; $i<$num_pages; $i++) {
                	$start_record = $count * $i;
	                if ($i == $current_page - 1) {
        	                $pages_html .= "<li class='active'>";
                	}
	                else {
        	                $pages_html .= "<li>";
                	}
	                $page_number = $i + 1;
        	        $pages_html .= "<a href='" . $url . $start_record . "'>" . $page_number . "</a></li>";
        	}

	        if ($current_page < $num_pages) {
        	        $start_record = $start + $count;
                	$pages_html .= "<li><a href='" . $url . $start_record . "'>&raquo;</a></li> ";
	        }
        	else {
                	$pages_html .= "<li class='disabled'><a href='#'>&raquo;</a></li>";
	        }
        	$pages_html .= "</ul></nav>";
	        return $pages_html;

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

	// Returns trs for the given users list
	public static function get_users_rows($adldap,$users) {
		$i_start = 0;
		$i_count = count($users);
		
		$users_html = "";
		for ($i=$i_start;$i<$i_count;$i++) {
	        if (array_key_exists($i,$users)) {
        		$users_html .= "<tr>";
            	$users_html .= "<td><a href='user.php?uid=" . $users[$i]['username'] . "'>";
				$users_html .= $users[$i]['username'] . "</a>";
				if($users[$i]['shadowexpire']!=''){
					if($users[$i]['shadowexpire'] <= time()){
						$users_html .= " <span class='glyphicon glyphicon-time smalldanger' title='User expired'></span>";
					} else {
						$users_html .= " <span class='glyphicon glyphicon-time smallwarning' title='User set to expire'></span>";
					}
				}
				if(!user::is_ad_user($adldap,$users[$i]['username'])){
					$users_html .= " <span class='glyphicon glyphicon-education smallwarning' title='User not in campus AD'></span>";
				}
				$users_html .= "</td>";
                $users_html .= "<td>" . $users[$i]['name']. "</td>";
				$users_html .= "<td>" . $users[$i]['email']. "</td>";
        		$users_html .= "</tr>";
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
					$descObj = json_decode($groups[$i]['description']);
					if($descObj == NULL){
						$description = $groups[$i]['description'];
						$dirs = array();
					} else {
						$description = $descObj->description;
						$dirs = $descObj->directories;
						sort($dirs);
					}
			        
            		$groups_html .= "<tr>";
                	$groups_html .= "<td><a href='group.php?gid=" . $groups[$i]['name'] . "'>";
					$groups_html .= $groups[$i]['name'] . "</a></td>";
	                $groups_html .= "<td>" . $description. "</td>";
	                $groups_html .= "<td>" . implode(", ", $dirs). "</td>";
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
	                	$computers_html .= "<td>";
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
		
		$hostshtml = "<tr><th>IGB Hosts</th><th>IP</th><th># of users</th></tr>";
		foreach($igb as $host){
			$hostshtml .= "<tr><td><a href='host.php?hid=".$host['name']."'>".$host['name']."</a></td><td>".$host['ip']."</td><td>".$host['numusers']."</td></tr>";
		}

		$hostshtml .= "<tr><th>Biotech Hosts</th><th>IP</th><th># of users</th></tr>";
		foreach($biotech as $host){
			$hostshtml .= "<tr><td><a href='host.php?hid=".$host['name']."'>".$host['name']."</a></td><td>".$host['ip']."</td><td>".$host['numusers']."</td></tr>";
		}

		if(count($other)!=0){
			$hostshtml .= "<tr><th>Other Hosts</th><th>IP</th><th># of users</th></tr>";
			foreach($other as $host){
				$hostshtml .= "<tr><td><a href='host.php?hid=".$host['name']."'>".$host['name']."</a></td><td>".$host['ip']."</td><td>".$host['numusers']."</td></tr>";
			}
		}
		
		return $hostshtml;
	}
	
	public static function sort_icon($column,$sort,$asc){
		if($sort==$column){
			return " <span class='glyphicon glyphicon-sort-by-attributes".($asc=="false"?'-alt':'')."'> </span>";
		}
		return '';
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
}

?>
