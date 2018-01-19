<?php
	$title = "Domain Computer Info";
	$sitearea = "domain";
	require_once 'includes/header.inc.php';
		
	if (isset($_GET['uid'])) {
	    $uid = $_GET['uid'];
	    if(!computer::is_ldap_computer($ldap,$uid)){
		    header('location: list_computers.php');
		    exit();
	    }
	}
	$computer = new computer($ldap,$uid);
	
	?>
	<style>
		tr.topborder {
			border-top: 2px solid darkgrey;
		}	
	</style>
	<div class="minijumbo"><div class="container"><?php echo $computer->get_name(); ?>
		<nav><ol class="breadcrumb"><li class="breadcrumb-item"><a href="list_computers.php">Domain Computers</a></li><li class="breadcrumb-item active"><?php echo $computer->get_name(); ?></a></li></ol></nav>
	</div></div>
	<div class="container">
	<div class="card mt-4">
		<table class="table table-sm table-striped mb-0">
			<tr>
				<th>Name:</th>
				<td><?php echo $computer->get_name(); ?></td>
			</tr>
			<tr>
				<th>UID Number:</th>
				<td><?php echo $computer->get_uidnumber(); ?></td>
			</tr>
			<tr>
				<th>Created By:</th>
				<td><?php echo $computer->get_creator(); ?></td>
			</tr>
			<tr>
				<th>Created:</th>
				<td><?php echo strftime('%m/%d/%Y %I:%M:%S %p', $computer->get_createTime()); ?></td>
			</tr>
			<tr>
				<th>Modified By:</th>
				<td><?php echo $computer->get_modifier(); ?></td>
			</tr>
			<tr>
				<th>Last Modified:</th>
				<td><?php echo strftime('%m/%d/%Y %I:%M:%S %p',$computer->get_modifyTime()); ?></td>
			</tr>
		</table>
		<hr class="my-0">
		<div class="card-body">
			<a href="remove_computer.php?uid=<?php echo $uid; ?>" class="btn btn-danger"><span class="fa fa-times"></span> Remove Computer</a>
		</div>
	</div>
	
	<?php
	require_once 'includes/footer.inc.php';