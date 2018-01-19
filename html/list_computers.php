<?php
	$title = "Domain Computer List";
	$sitearea = "domain";
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
	
	<div class="minijumbo"><div class="container">Domain Computers</div></div>
	<div class="container">
	<div class="card">
		<div class="card-body">
			<form method="get" action='<?php echo $_SERVER['PHP_SELF']; ?>' class="form-inline">
				<div class="col-md-6">
					<input type="text" name="search" class="form-control mb-2 mr-sm-2 mb-sm-0" value="<?php if (isset($search)){echo $search; } ?>" placeholder="Search" />
					
					<input type="hidden" name="sort" value="<?php echo $sort; ?>" />
					<input type="hidden" name="asc" value="<?php echo $asc; ?>" />
					<input type="submit" class="btn btn-primary" value="Go" />
				</div>
			</form>
		</div>
	
		<table class="table table-sm table-striped mb-0">
			<thead>
				<tr>
					<th class="sortable-th pl-2" onclick="sort_table('name')">Name<?php echo html::sort_icon('name', $sort, $asc); ?></th>
				</tr>
			</thead>
			<tbody>
				<?php echo $computers_html; ?>
			</tbody>
		</table>
	</div>
	
	<?php echo $pages_html; ?>
<?php
	require_once 'includes/footer.inc.php';