<?php
	$title = "Group LDAP Entry";
	$sitearea = "groups";
	require_once 'includes/header.inc.php';
		
	if (isset($_GET['gid'])) {
	    $gid = $_GET['gid'];
	    if(!group::is_ldap_group($ldap,$gid)){
		    header('location: list_groups.php');
		    exit();
	    }
	}
	$group = new group($ldap,$gid);
	$ldapattributes = $group->get_ldap_attributes();
	
	$attributes = array();
	for($i=0; $i<$ldapattributes['count']; $i++){
		$attributes[] = $ldapattributes[$i];
	}
	sort($attributes);

	?>

	<div class="minijumbo"><div class="container"><?php echo $group->get_name(); ?>
		<nav><ol class="breadcrumb"><li class="breadcrumb-item"><a href="list_groups.php">Groups</a></li><li class="breadcrumb-item"><a href="group.php?gid=<?php echo $group->get_name(); ?>"><?php echo $group->get_name(); ?></a></li><li class="breadcrumb-item active">LDAP Entry</li></ol></nav>
	</div></div>
	<div class="container">
	<div class="card mt-4">
		<table class="table table-striped table-sm table-igb-bordered mb-0">
			<?php
				for($i=0; $i<count($attributes); $i++){
					for($j=0; $j<$ldapattributes[$attributes[$i]]['count']; $j++){
						echo "<tr><th><span class='align-middle'>".$attributes[$i].":</span></th><td><span class='align-middle'>".$ldapattributes[$attributes[$i]][$j]."</span></td></tr>";
					}
				}
			?>
		</table>
	</div>

	<?php
	require_once 'includes/footer.inc.php';