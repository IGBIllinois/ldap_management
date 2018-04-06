<?php
	$title = "Group Info";
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
	$isusergroup = user::is_ldap_user($ldap, $group->get_name());

	$usershtml = "";
	$userscopytext = "";
	$users = $group->get_users();
	usort($users,"html::username_cmp");
	for($i=0; $i<count($users);$i++){
		$usershtml .= "<tr><td class='pl-2'><a class='align-middle' href='user.php?uid=".$users[$i]."'>".$users[$i]."</a>";
		if(!user::is_ldap_user($ldap,$users[$i])){
			$usershtml .= " <span class='fa fa-exclamation-triangle text-warning' title='User does not exist'></span>";
		}
		$userobj = new user($ldap,$users[$i]);
		if($userobj->is_expired()){
			$usershtml .= " <span class='fa fa-clock-o text-danger' title='User has expired'></span>";
		}
		if($userobj->is_expiring()){
			$usershtml .= " <span class='fa fa-clock-o text-warning' title='User is expiring'></span>";
		}
		if($userobj->get_leftcampus()){
			$usershtml .= " <span class='fa fa-graduation-cap text-warning' title='User has left UIUC'></span>";
		}
		if($group->get_name()!=$users[$i]){
			$usershtml .= " <a href='remove_from_group.php?uid=".$users[$i]."&gid=$gid' class='btn btn-danger btn-sm pull-right'><span class='fa fa-times'></span> Remove from group</a></td></tr>";
		}
		$userscopytext .= $users[$i]."\n";
	}
	
	$serverdirs = $group->get_serverdirs();
	$dirhtml = "";
	$dircopytext = "";
	for($i=0; $i<count($serverdirs); $i++){
		$dir = explode(':', $serverdirs[$i]);
		$dirhtml .= "<tr><td class='pl-2'>".$dir[0]."</td><td>".$dir[1]." <a href='remove_serverdir.php?gid=$gid&serverdir=".urlencode($serverdirs[$i])."' class='btn btn-danger btn-sm pull-right'><span class='fa fa-times'></span> Remove directory</a></td></tr>";
		$dircopytext .= $serverdirs[$i]."\n";
	}

	?>
	<style>
		tr.topborder {
			border-top: 2px solid darkgrey;
		}	
	</style>
	<div class="minijumbo"><div class="container"><?php echo $group->get_name(); ?>
		<nav><ol class="breadcrumb"><li class="breadcrumb-item"><a href="list_groups.php">Groups</a></li><li class="breadcrumb-item active"><?php echo $group->get_name(); ?></a></li></ol></nav>
	</div></div>
	<div class="container">
	<div class="card mt-4">
		<table class="table table-striped table-igb-bordered mb-0">
			<tr>
				<th><span class='align-middle'>Name:</span></th>
				<td><span class='align-middle'><?php echo $group->get_name(); if(!$isusergroup){ ?></span> <a href="change_group_name.php?gid=<?php echo $group->get_name(); ?>" class="btn btn-info btn-sm pull-right"><span class="fa fa-pencil"></span> Change Name</a><?php } ?></td>
			</tr>
			<tr>
				<th><span class='align-middle'>Description:</span></th>
				<td><span class='align-middle'><?php echo $group->get_description(); if(!$isusergroup){ ?></span> <a href="change_description.php?gid=<?php echo $group->get_name(); ?>" class="btn btn-info btn-sm pull-right"><span class="fa fa-pencil"></span> Change Description</a><?php } ?></td>
			</tr>
			<tr>
				<th>GID Number:</th>
				<td><?php echo $group->get_gidnumber(); ?></td>
			</tr>
			<tr>
				<th><span class='align-middle'>Owner:</span></th>
				<td><span class='align-middle'><?php echo $group->get_owner(); if(!$isusergroup){ ?></span> <a href="change_group_owner.php?gid=<?php echo $group->get_name(); ?>" class="btn btn-info btn-sm pull-right"><span class="fa fa-pencil"></span> Change Owner</a><?php } ?></td>
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
		<hr class="my-0">
		<div class="card-body">
			<a href="remove_group.php?gid=<?php echo $gid; ?>" class="btn btn-danger mt-1 mt-md-0"><span class="fa fa-times"></span> Remove Group</a>
			<a href="group_advanced.php?gid=<?php echo $gid; ?>" class="btn btn-light mt-1 mt-md-0"><span class="fa fa-user-secret"></span> LDAP Entry</a>
		</div>
	</div>
	<div class="card mt-3">
		<div class="card-header d-flex align-items-center">
			<h3 class='mr-auto'>Managed Directories</h3>
			<div class="btn-group btn-group-sm">
				<a class="btn btn-success btn-sm" href="add_serverdir.php?gid=<?php echo $gid; ?>"><span class='fa fa-plus'></span> Add directory</a>
				<button class='btn btn-light btn-sm copy-button'><span class='fa fa-clipboard'></span> Copy</button>
			</div>
		</div>
		<textarea class='d-none copy-text'><?php echo $dircopytext; ?></textarea>
		<table class="table table-sm table-striped mb-0">
			<?php echo $dirhtml; ?>
		</table>
	</div>
	<div class="card mt-3">
		<div class="card-header d-flex align-items-center">
			<h3 class="mr-auto">Members <small class="text-muted">(<?php echo count($users); ?>)</small></h3>
			<?php if(!$isusergroup){ ?>
			<div class="btn-group btn-group-sm">
				<a class='btn btn-success btn-sm' href='add_to_group.php?gid=<?php echo $gid; ?>'><span class='fa fa-plus'></span> Add member</a>
				<button class='btn btn-light btn-sm copy-button'><span class='fa fa-clipboard'></span> Copy</button>
			</div>
			<?php } ?>
		</div>
		<textarea class='d-none copy-text'><?php echo $userscopytext; ?></textarea>
		<table class="table table-sm table-striped mb-0">
			<?php echo $usershtml; ?>
		</table>
	</div>

	<?php
	require_once 'includes/footer.inc.php';