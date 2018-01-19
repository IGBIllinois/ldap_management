<?php
	$title = "Host List";
	$sitearea = "hosts";
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
	
	$hosts = host::get_all_hosts($ldap,true);
	$hosts_html = "";
	
	$hosts_html = html::get_hosts_rows($hosts);
	
	?>
	
	<div class="minijumbo"><div class="container">Hosts</div></div>
	<div class="container">
	
	<div class="card">
		<table class="table table-sm table-striped table-igb-bordered table-responsive-md mb-0">
			<?php echo $hosts_html; ?>
		</table>
	</div>
<?php
	require_once 'includes/footer.inc.php';