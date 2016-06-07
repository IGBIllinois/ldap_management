<?php
	$title = "Domain Computer List";
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
	
	$sort = 'name';
	if(isset($_GET['sort'])){
		$sort = $_GET['sort'];
		$get_array['sort'] = $sort;
	}
	
	$asc = "true";
	if(isset($_GET['asc'])){
		$asc = $_GET['asc'];
		$get_array['asc'] = $asc;
	}
	
	$all_computers = computer::get_search_computers($ldap,$search,$start,$count,$sort,$asc);
	$num_computers = count($all_computers);
	$pages_url = $_SERVER['PHP_SELF']."?".http_build_query($get_array);
	$pages_html = html::get_pages_html($pages_url,$num_computers,$start,$count);
	$computers_html = "";
	
	$computers_html = html::get_computers_rows($all_computers);
	
	?>
	
	<h3>List of Domain Computers</h3>
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
				<th class="sortable-th" onclick="sort_table('name')">Name<?php echo html::sort_icon('name', $sort, $asc); ?></th>
			</tr>
		</thead>
		<tbody>
			<?php echo $computers_html; ?>
		</tbody>
	</table>
	
	<?php echo $pages_html; ?>
<?php
	require_once 'includes/footer.inc.php';