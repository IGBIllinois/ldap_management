<?php
// Various html-generation functions
class html {

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
