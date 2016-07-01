<?php
	$title = "Domain Computer Info";
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
	<div class="panel panel-default">
		<table class="table table-bordered table-condensed table-striped">
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
		<div class="panel-body">
			<a href="remove_computer.php?uid=<?php echo $uid; ?>" class="btn btn-danger"><span class="glyphicon glyphicon-remove"></span> Remove Computer</a>
		</div>
	</div>
	
	<?php
	require_once 'includes/footer.inc.php';