<?php
	$title = "Classroom User List";
	$sitearea = "users";
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
		$search = trim($_GET['search']);
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
	
	$filter = 'classroom';
	setcookie("lastUserSearchSort",$sort);
	setcookie("lastUserSearchAsc",$asc);
	setcookie("lastUserSearchFilter",$filter);
	setcookie("lastUserSearch",$search);
	$all_users = user::get_search_users($ldap,$search,$start,$count,$sort,$asc,$filter);
	$num_users = user::get_search_users_count($ldap,$search,$filter);
	$pages_url = $_SERVER['PHP_SELF']."?".http_build_query($get_array);
	$pages_html = html::get_pages_html($pages_url,$num_users,$start,$count);
	$users_html = "";
	$user_count = 0;
	
	$users_html = html::get_classroom_users_rows($all_users,($filter=='expiring'||$filter=='expired'));
	?>
	
	<div class="minijumbo"><div class="container">Classroom Users</div></div>
	<div class="container">
	
	<?php if(isset($_GET['message'])){
		echo $_GET['message'];
	} ?>
	<div class="card">
		<div class="card-body">
			<form method="get" action='<?php echo $_SERVER['PHP_SELF']; ?>' class="form-inline">
				<div class="form-group">
					<input type="text" name="search" class="form-control" value="<?php if (isset($search)){echo $search; } ?>" placeholder="Search" />
				</div>
				
				<input type="hidden" name="sort" value="<?php echo $sort; ?>" />
				<input type="hidden" name="asc" value="<?php echo $asc; ?>" />
				<input type="submit" class="btn btn-primary ml-1 mr-auto" value="Go" />
			</form>
		</div>
		<form action="multi_user_action.php" method="post">
			<table class="table table-sm table-striped table-responsive-md table-igb-bordered mb-0">
				<thead>
					<tr>
						<th><input type="checkbox" id="select-all"/></th>
						<th class="sortable-th pl-2" onclick="sort_table('username')">NetID<?php echo html::sort_icon('username', $sort, $asc); ?></th>
						<th class="sortable-th" onclick="sort_table('description')">Description<?php echo html::sort_icon('description', $sort, $asc); ?></th>
						<th>Groups</th>
						<th class="sortable-th" onclick="sort_table('expiration')">Expiration<?php echo html::sort_icon('expiration',$sort,$asc); ?></th>
					</tr>
				</thead>
				<tbody>
					<?php echo $users_html; ?>
				</tbody>
			</table>
			
			<div class="card-footer text-muted">
				<input type="hidden" name="sort" value="<?php echo $sort; ?>" />
				<input type="hidden" name="asc" value="<?php echo $asc; ?>" />
				<input type="hidden" name="search" value="<?php echo $search; ?>" />
				<input type="hidden" name="start" value="<?php echo $start; ?>" />
				<input type="hidden" name="filter" value="<?php echo $filter; ?>" />
				<label>All checked: </label> <select class="custom-select" style="max-width:100%; width: unset" name="action"><option hidden>Select an action...</option><option value="add-to-group">Add to group</option></select> <input type="submit" class="btn btn-primary">
			</div>
		</form>
	</div>
			<?php echo $pages_html; ?>

	<script type="text/javascript">
		$('#expiring-button').on('click',function(){
			var $this = $(this);
			toggleClasses($this,'btn-light','btn-warning active');
		});
		$('#expired-button').on('click',function(){
			var $this = $(this);
			toggleClasses($this,'btn-light','btn-danger active');
		});
		$('#ad-button').on('click',function(){
			var $this = $(this);
			toggleClasses($this,'btn-light','btn-warning active');
		});
		$('#noncampus-button').on('click',function(){
			var $this = $(this);
			toggleClasses($this,'btn-light','btn-info active');
		});
		$('#select-all').change(function(){
			var $checkboxes = $(this).closest("form").find("input[type=checkbox]");
			if(this.checked){
				$checkboxes.prop("checked", true);
			} else {
				$checkboxes.prop("checked", false);
			}
		})
	</script>
<?php
	require_once 'includes/footer.inc.php';