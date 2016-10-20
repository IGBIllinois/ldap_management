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
	
	$filter = 'none';
	if(isset($_GET['filter'])){
		$filter = $_GET['filter'];
		$get_array['filter'] = $filter;
	}
	
	$all_users = user::get_search_users($ldap,$adldap,$search,$start,$count,$sort,$asc,$filter);
	$num_users = user::get_search_users_count($ldap,$adldap,$search,$filter);
	$pages_url = $_SERVER['PHP_SELF']."?".http_build_query($get_array);
	$pages_html = html::get_pages_html($pages_url,$num_users,$start,$count);
	$users_html = "";
	$user_count = 0;
	
	$users_html = html::get_users_rows($adldap,$all_users);
	
	?>
	
	<h3>List of Users</h3>
	<div class="panel panel-default">
		<div class="panel-body">
<!-- 			<div class="row"> -->
				<form method="get" action='<?php echo $_SERVER['PHP_SELF']; ?>' class="form-inline">
<!-- 					<div class="col-md-12"> -->
				
						<div class="form-group">
							<input type="text" name="search" class="form-control" value="<?php if (isset($search)){echo $search; } ?>" placeholder="Search" />
						</div>
						
						<input type="hidden" name="sort" value="<?php echo $sort; ?>" />
						<input type="hidden" name="asc" value="<?php echo $asc; ?>" />
						<input type="submit" class="btn btn-primary" value="Go" />
						
						<div class="btn-group pull-right">
							<button type="button" class="btn btn-default<?php if($filter=='expiring'){echo ' active btn-warning';} ?>" id="expiring-button" onclick="filter_table('expiring')"><span class="glyphicon glyphicon-time"></span> Expiring</button>
							<button type="button" class="btn btn-default<?php if($filter=='expired'){echo ' active btn-danger';} ?>" id="expired-button" onclick="filter_table('expired')"><span class="glyphicon glyphicon-time"></span> Expired</button>
							<button type="button" class="btn btn-default<?php if($filter=='left'){echo ' active btn-warning';} ?>" id="ad-button" onclick="filter_table('left')"><span class="glyphicon glyphicon-education"></span> Left Campus</button>
						</div>
<!-- 					</div> -->
				</form>
<!-- 			</div> -->
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
	</div>
	<p>
		<span class="glyphicon glyphicon-time smallwarning"> </span>=expiration set &nbsp;
		<span class="glyphicon glyphicon-time smalldanger"> </span>=expired &nbsp;
		<span class="glyphicon glyphicon-education smallwarning"> </span>=left campus 
	</p>
	<?php echo $pages_html; ?>
	<script type="text/javascript">
		$('#expiring-button').on('click',function(){
			var $this = $(this);
			toggleClasses($this,'btn-default','btn-warning active');
		});
		$('#expired-button').on('click',function(){
			var $this = $(this);
			toggleClasses($this,'btn-default','btn-danger active');
		});
		$('#ad-button').on('click',function(){
			var $this = $(this);
			toggleClasses($this,'btn-default','btn-warning active');
		});
	</script>
<?php
	require_once 'includes/footer.inc.php';