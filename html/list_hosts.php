<?php
	$title = "Host List";
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
	
	$hosts = host::get_all_hosts($ldap);
	$hosts_html = "";
	
	$hosts_html = html::get_hosts_rows($hosts);
	
	?>
	
	<h3>List of Hosts</h3>
	
	<table class="table table-bordered table-condensed table-striped">
		<?php echo $hosts_html; ?>
	</table>
<?php
	require_once 'includes/footer.inc.php';