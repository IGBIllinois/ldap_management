<?php
	$title = "Host Info";
	require_once 'includes/header.inc.php';
		
	if (isset($_GET['hid'])) {
	    $hostname = $_GET['hid'];
	}
	$host = new host($ldap,$hostname);
	
	$usershtml = "";
	$copytext = "";
	$users = $host->get_users();
	sort($users);
	for($i=0; $i<count($users);$i++){
		$usershtml .= "<tr><td><a href='user.php?uid=".$users[$i]."'>".$users[$i]."</a>";
		if(!user::is_ldap_user($ldap,$users[$i])){
			$usershtml .= " <span class='glyphicon glyphicon-alert smallwarning' title='User does not exist'></span>";
		}
		$usershtml .= " <a href='remove_machinerights.php?uid=".$users[$i]."&hid=$hostname' class='btn btn-danger btn-xs pull-right'><span class='glyphicon glyphicon-remove'></span> Revoke access</a></td></tr>";
		$copytext .= $users[$i]."\n";
	}

	?>
	<style>
		tr.topborder {
			border-top: 2px solid darkgrey;
		}	
	</style>
	<div class="panel panel-default">
		<table class="table table-bordered table-condensed table-striped">
			<tr>
				<th>Hostname:</th>
				<td><?php echo $host->get_name(); ?> <a href="change_host_name.php?hid=<?php echo $host->get_name(); ?>" class="btn btn-info btn-xs pull-right"><span class="glyphicon glyphicon-pencil"></span> Change Hostname</a></td>
			</tr>
			<tr>
				<th>IP:</th>
				<td><?php echo $host->get_ip(); ?> <a href="change_ip.php?hid=<?php echo $host->get_name(); ?>" class="btn btn-info btn-xs pull-right"><span class="glyphicon glyphicon-pencil"></span> Change IP</a></td>
			</tr>
			<tr>
				<th>Created By:</th>
				<td><?php echo $host->get_creator(); ?></td>
			</tr>
			<tr>
				<th>Created:</th>
				<td><?php echo strftime('%m/%d/%Y %I:%M:%S %p', $host->get_createTime()); ?></td>
			</tr>
			<tr>
				<th>Modified By:</th>
				<td><?php echo $host->get_modifier(); ?></td>
			</tr>
			<tr>
				<th>Last Modified:</th>
				<td><?php echo strftime('%m/%d/%Y %I:%M:%S %p',$host->get_modifyTime()); ?></td>
			</tr>
		</table>
		<div class="panel-body">
			<a href="remove_host.php?hid=<?php echo $hostname; ?>" class="btn btn-danger"><span class="glyphicon glyphicon-remove"></span> Remove Host</a>
		</div>
	</div>
	<div class="panel panel-default">
		<div class="panel-heading">
			<div class="btn-group btn-group-xs pull-right">
				<a class='btn btn-success btn-xs' href='add_machinerights.php?hid=<?php echo $hostname; ?>'><span class='glyphicon glyphicon-plus'></span> Add user</a>
				<button class='btn btn-default btn-xs copy-button'><span class='glyphicon glyphicon-copy'></span> Copy</button>
			</div>
			<h3 class="panel-title">Users</h3>
		</div>
		<textarea class='hidden copy-text'><?php echo $copytext; ?></textarea>
		<table class="table table-bordered table-striped">
			<?php echo $usershtml; ?>
		</table>
	</div>

	<?php
	require_once 'includes/footer.inc.php';