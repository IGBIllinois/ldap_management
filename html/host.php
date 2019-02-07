<?php
	$title = "Host Info";
	$sitearea = "hosts";
	require_once 'includes/header.inc.php';
		
	if (isset($_GET['hid'])) {
	    $hostname = $_GET['hid'];
	    if(!host::is_ldap_host($ldap,$hostname)){
		    header('location: list_hosts.php');
		    exit();
	    }
	}
	$host = new host($ldap,$hostname);
	
	$usershtml = "";
	$copytext = "";
	$users = $host->get_users();
	usort($users,"html::username_cmp");
	for($i=0; $i<count($users);$i++){
		$usershtml .= "<tr><td class='pl-2'><a class='align-middle' href='user.php?uid=".$users[$i]."'>".$users[$i]."</a>";
		if(!user::is_ldap_user($ldap,$users[$i])){
			$usershtml .= " <span class='fa fa-exclamation-triangle text-warning' title='User does not exist'></span>";
		}
		$userobj = new user($ldap,$users[$i]);
		if($userobj->is_expired()){
			$usershtml .= " <span class='far fa-clock text-danger' title='User has expired'></span>";
		}
		if($userobj->is_expiring()){
			$usershtml .= " <span class='far fa-clock text-warning' title='User is expiring'></span>";
		}
		if($userobj->get_leftcampus()){
			$usershtml .= " <span class='fa fa-graduation-cap text-warning' title='User has left UIUC'></span>";
		}
		$usershtml .= " <a href='remove_machinerights.php?uid=".$users[$i]."&hid=$hostname' class='btn btn-danger btn-sm float-right'><span class='fa fa-times'></span> Revoke access</a></td></tr>";
		$copytext .= $users[$i]."\n";
	}

	?>
	<style>
		tr.topborder {
			border-top: 2px solid darkgrey;
		}	
	</style>
	<div class="minijumbo"><div class="container"><?php echo $host->get_name(); ?>
	<nav><ol class="breadcrumb"><li class="breadcrumb-item"><a href="list_hosts.php">Hosts</a></li><li class="breadcrumb-item active"><?php echo $host->get_name(); ?></li></ol></nav>
	</div></div>
	<div class="container">
	<div class="card mt-4">
		<table class="table table-sm table-striped table-igb-bordered mb-0">
			<tr>
				<th><span class='align-middle'>Hostname:</span></th>
				<td><span class='align-middle'><?php echo $host->get_name(); ?></span> <a href="change_host_name.php?hid=<?php echo $host->get_name(); ?>" class="btn btn-info btn-sm float-right"><span class="fas fa-edit"></span> Change Hostname</a></td>
			</tr>
			<tr>
				<th><span class='align-middle'>IP:</span></th>
				<td><span class='align-middle'><?php echo $host->get_ip(); ?></span> <a href="change_ip.php?hid=<?php echo $host->get_name(); ?>" class="btn btn-info btn-sm float-right"><span class="fas fa-edit"></span> Change IP</a></td>
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
		<hr class="my-0">
		<div class="card-body">
			<a href="remove_host.php?hid=<?php echo $hostname; ?>" class="btn btn-danger"><span class="fa fa-times"></span> Remove Host</a>
		</div>
	</div>
	<div class="card mt-3">
		<div class="card-header d-flex align-items-center">
			<h3 class="mr-auto">Users <small class="text-muted">(<?php echo count($users); ?>)</small></h3>
			<div class="btn-group btn-group-sm">
				<a class='btn btn-success btn-sm' href='add_machinerights.php?hid=<?php echo $hostname; ?>'><span class='fa fa-plus'></span> Add user</a>
				<button class='btn btn-light btn-sm copy-button'><span class='fa fa-clipboard'></span> Copy</button>
			</div>
		</div>
		<textarea class='d-none copy-text'><?php echo $copytext; ?></textarea>
		<table class="table table-sm table-striped mb-0">
			<?php echo $usershtml; ?>
		</table>
	</div>

	<?php
	require_once 'includes/footer.inc.php';