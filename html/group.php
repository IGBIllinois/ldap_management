<?php
	$title = "Group Info";
	require_once 'includes/header.inc.php';
		
	if (isset($_GET['gid'])) {
	    $gid = $_GET['gid'];
	}
	$group = new group($ldap,$gid);
	
	$usershtml = "";
	$copytext = "";
	$users = $group->get_users();
	sort($users);
	for($i=0; $i<count($users);$i++){
		$usershtml .= "<tr><td><a href='user.php?uid=".$users[$i]."'>".$users[$i]."</a>";
		if(!user::is_ldap_user($ldap,$users[$i])){
			$usershtml .= " <span class='glyphicon glyphicon-alert smallwarning' title='User does not exist'></span>";
		}
		$usershtml .= " <a href='remove_from_group.php?uid=".$users[$i]."&gid=$gid' class='btn btn-danger btn-xs pull-right'><span class='glyphicon glyphicon-remove'></span> Remove from group</a></td></tr>";
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
				<th>Name:</th>
				<td><?php echo $group->get_name(); ?> <a href="change_group_name.php?gid=<?php echo $group->get_name(); ?>" class="btn btn-info btn-xs pull-right"><span class="glyphicon glyphicon-pencil"></span> Change Name</a></td>
			</tr>
			<tr>
				<th>Description:</th>
				<td><?php echo $group->get_description(); ?> <a href="change_description.php?gid=<?php echo $group->get_name(); ?>" class="btn btn-info btn-xs pull-right"><span class="glyphicon glyphicon-pencil"></span> Change Description</a></td>
			</tr>
			<tr>
				<th>GID Number:</th>
				<td><?php echo $group->get_gidnumber(); ?></td>
			</tr>
			<tr>
				<th>Created By:</th>
				<td><?php echo $group->get_creator(); ?></td>
			</tr>
			<tr>
				<th>Created:</th>
				<td><?php echo strftime('%m/%d/%Y %I:%M:%S %p', $group->get_createTime()); ?></td>
			</tr>
			<tr>
				<th>Modified By:</th>
				<td><?php echo $group->get_modifier(); ?></td>
			</tr>
			<tr>
				<th>Last Modified:</th>
				<td><?php echo strftime('%m/%d/%Y %I:%M:%S %p',$group->get_modifyTime()); ?></td>
			</tr>
		</table>
		<div class="panel-body">
			<a href="remove_group.php?gid=<?php echo $gid; ?>" class="btn btn-danger"><span class="glyphicon glyphicon-remove"></span> Remove Group</a>
		</div>
	</div>
	<div class="panel panel-default">
		<div class="panel-heading">
			<div class="btn-group btn-group-xs pull-right">
				<a class='btn btn-success btn-xs' href='add_to_group.php?gid=<?php echo $gid; ?>'><span class='glyphicon glyphicon-plus'></span> Add member</a>
				<button class='btn btn-default btn-xs copy-button'><span class='glyphicon glyphicon-copy'></span> Copy</button>
			</div>
			<h3 class="panel-title">Members</h3>
		</div>
		<textarea class='hidden copy-text'><?php echo $copytext; ?></textarea>
		<table class="table table-bordered table-striped">
			<?php echo $usershtml; ?>
		</table>
	</div>

	<?php
	require_once 'includes/footer.inc.php';