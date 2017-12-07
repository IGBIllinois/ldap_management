<?php
	$title = "Group Info";
	require_once 'includes/header.inc.php';
		
	if (isset($_GET['gid'])) {
	    $gid = $_GET['gid'];
	    if(!group::is_ldap_group($ldap,$gid)){
		    header('location: list_groups.php');
		    exit();
	    }
	}
	$group = new group($ldap,$gid);
	$isusergroup = user::is_ldap_user($ldap, $group->get_name());

	$usershtml = "";
	$userscopytext = "";
	$users = $group->get_users();
	sort($users);
	for($i=0; $i<count($users);$i++){
		$usershtml .= "<tr><td><a href='user.php?uid=".$users[$i]."'>".$users[$i]."</a>";
		if(!user::is_ldap_user($ldap,$users[$i])){
			$usershtml .= " <span class='fa fa-exclamation-triangle smallwarning' title='User does not exist'></span>";
		}
		$userobj = new user($ldap,$users[$i]);
		if($userobj->is_expired()){
			$usershtml .= " <span class='fa fa-clock-o smalldanger' title='User has expired'></span>";
		}
		if($userobj->is_expiring()){
			$usershtml .= " <span class='fa fa-clock-o smallwarning' title='User is expiring'></span>";
		}
		if($userobj->get_leftcampus()){
			$usershtml .= " <span class='fa fa-graduation-cap smallwarning' title='User has left UIUC'></span>";
		}
		if($group->get_name()!=$users[$i]){
			$usershtml .= " <a href='remove_from_group.php?uid=".$users[$i]."&gid=$gid' class='btn btn-danger btn-xs pull-right'><span class='fa fa-times'></span> Remove from group</a></td></tr>";
		}
		$userscopytext .= $users[$i]."\n";
	}
	
	$serverdirs = $group->get_serverdirs();
	$dirhtml = "";
	$dircopytext = "";
	for($i=0; $i<count($serverdirs); $i++){
		$dir = explode(':', $serverdirs[$i]);
		$dirhtml .= "<tr><td>".$dir[0]."</td><td>".$dir[1]." <a href='remove_serverdir.php?gid=$gid&serverdir=".urlencode($serverdirs[$i])."' class='btn btn-danger btn-xs pull-right'><span class='fa fa-times'></span> Remove directory</a></td></tr>";
		$dircopytext .= $serverdirs[$i]."\n";
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
				<td><?php echo $group->get_name(); if(!$isusergroup){ ?> <a href="change_group_name.php?gid=<?php echo $group->get_name(); ?>" class="btn btn-info btn-xs pull-right"><span class="fa fa-pencil"></span> Change Name</a><?php } ?></td>
			</tr>
			<tr>
				<th>Description:</th>
				<td><?php echo $group->get_description(); if(!$isusergroup){ ?> <a href="change_description.php?gid=<?php echo $group->get_name(); ?>" class="btn btn-info btn-xs pull-right"><span class="fa fa-pencil"></span> Change Description</a><?php } ?></td>
			</tr>
			<tr>
				<th>GID Number:</th>
				<td><?php echo $group->get_gidnumber(); ?></td>
			</tr>
			<tr>
				<th>Owner:</th>
				<td><?php echo $group->get_owner(); if(!$isusergroup){ ?> <a href="change_group_owner.php?gid=<?php echo $group->get_name(); ?>" class="btn btn-info btn-xs pull-right"><span class="fa fa-pencil"></span> Change Owner</a><?php } ?></td>
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
			<a href="remove_group.php?gid=<?php echo $gid; ?>" class="btn btn-danger"><span class="fa fa-times"></span> Remove Group</a>
		</div>
	</div>
	<div class="panel panel-default">
		<div class="panel-heading">
			<div class="btn-group btn-group-xs pull-right">
				<a class="btn btn-success btn-xs" href="add_serverdir.php?gid=<?php echo $gid; ?>"><span class='fa fa-plus'></span> Add directory</a>
				<button class='btn btn-default btn-xs copy-button'><span class='fa fa-clipboard'></span> Copy</button>
			</div>
			<h3 class='panel-title'>Managed Directories</h3>
		</div>
		<textarea class='hidden copy-text'><?php echo $dircopytext; ?></textarea>
		<table class="table table-bordered table-striped">
			<?php echo $dirhtml; ?>
		</table>
	</div>
	<div class="panel panel-default">
		<div class="panel-heading">
			<?php if(!$isusergroup){ ?>
			<div class="btn-group btn-group-xs pull-right">
				<a class='btn btn-success btn-xs' href='add_to_group.php?gid=<?php echo $gid; ?>'><span class='fa fa-plus'></span> Add member</a>
				<button class='btn btn-default btn-xs copy-button'><span class='fa fa-clipboard'></span> Copy</button>
			</div>
			<?php } ?>
			<h3 class="panel-title">Members</h3>
		</div>
		<textarea class='hidden copy-text'><?php echo $userscopytext; ?></textarea>
		<table class="table table-bordered table-striped">
			<?php echo $usershtml; ?>
		</table>
	</div>

	<?php
	require_once 'includes/footer.inc.php';