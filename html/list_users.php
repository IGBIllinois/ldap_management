<?php
	$title = "User List";
	require_once 'includes/header.inc.php';

	$get_array = array();
	$start = 0;
	$count = 30;
	
	if ( isset($_GET['start']) && is_numeric($_GET['start']) ){
		$start = $_GET['start'];
		$get_array['start'] = $start;
	}
	
	$search = "";
	if ( isset($_GET['search']) ){
		$search = $_GET['search'];
		$get_array['search'] = $search;
	}
	
	$sort = 'username';
	if(isset($_GET['sort'])){
		$sort = $_GET['sort'];
		$get_array['sort'] = $sort;
	}
	
	$asc = "true";
	if(isset($_GET['asc'])){
		$asc = $_GET['asc'];
		$get_array['asc'] = $asc;
	}
	
	$all_users = user::get_search_users($ldap,$search,$start,$count,$sort,$asc);
	$num_users = user::get_search_users_count($ldap,$search);
	$pages_url = $_SERVER['PHP_SELF']."?".http_build_query($get_array);
	$pages_html = html::get_pages_html($pages_url,$num_users,$start,$count);
	$users_html = "";
	$user_count = 0;
	
	$users_html = html::get_users_rows($adldap,$all_users);
	
	?>
	
	<h3>List of Users</h3>
	<div class="row" style="margin-bottom:15px;">
		<form method="get" action='<?php echo $_SERVER['PHP_SELF']; ?>' class="form-inline">
			<div class="col-md-6">
				<div class="form-group">
					<input type="text" name="search" class="form-control" value="<?php if (isset($search)){echo $search; } ?>" placeholder="Search" />
				</div>
				
				<input type="hidden" name="sort" value="<?php echo $sort; ?>" />
				<input type="hidden" name="asc" value="<?php echo $asc; ?>" />
				<input type="submit" class="btn btn-primary" value="Go" />
			</div>
		</form>
	</div>
	
	<table class="table table-bordered table-condensed table-striped">
		<thead>
			<tr>
				<th class="sortable-th" onclick="sort_table('username')">NetID<?php echo html::sort_icon('username', $sort, $asc); ?></th>
				<th class="sortable-th" onclick="sort_table('name')">Name<?php echo html::sort_icon('name', $sort, $asc); ?></th>
				<th class="sortable-th" onclick="sort_table('email')">Email<?php echo html::sort_icon('email', $sort, $asc); ?></th>
			</tr>
		</thead>
		<tbody>
			<?php echo $users_html; ?>
		</tbody>
	</table>
	
	<?php echo $pages_html; ?>
<?php
	require_once 'includes/footer.inc.php';