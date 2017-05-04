<?php
	$title = "User Info: ".$_GET['uid'];
	require_once 'includes/header.inc.php';
	
	$username = $login_user->get_username();
	if (isset($_GET['uid'])) {
	    $username = $_GET['uid'];
	    if(!user::is_ldap_user($ldap,$username)){
		    header('location: list_users.php');
		    exit();
	    }
	}

	$user = new user($ldap,$username);
	
	$machinerightshtml = "";
	$machinecopytext = "";
	$machinerights = $user->get_machinerights();
	if($machinerights){
		sort($machinerights);
		for($i=0; $i<count($machinerights);$i++){
			$machinerightshtml .= "<tr><td><a href='host.php?hid=".$machinerights[$i]."'>".$machinerights[$i]."</a>";
			if(!host::is_ldap_host($ldap,$machinerights[$i])){
				$machinerightshtml .= " <span class='glyphicon glyphicon-alert smallwarning' title='Host does not exist'></span>";
			}
			$machinerightshtml .= " <a class='btn btn-danger btn-xs pull-right' href='remove_machinerights.php?uid=$username&hid=".$machinerights[$i]."&from=user'><span class='glyphicon glyphicon-remove'> </span> Remove host</a></td></tr>";
			$machinecopytext .= $machinerights[$i]."\n";
		}
	}
	
	$groupshtml = "";
	$groupcopytext = "";
	$groups = $user->get_groups();
	sort($groups);
	for($i=0; $i<count($groups);$i++){
		$groupshtml .= "<tr><td><a href='group.php?gid=".$groups[$i]."'>".$groups[$i]."</a>";
		if($username != $groups[$i]){
			$groupshtml .= " <a class='btn btn-danger btn-xs pull-right' href='remove_from_group.php?uid=$username&gid=".$groups[$i]."&from=user'><span class='glyphicon glyphicon-remove'> </span> Remove from group</a>";
		}
		$groupcopytext .= $groups[$i]."\n";
		$groupshtml .= "</td></tr>";
	}

	?>
	<style>
		tr.topborder {
			border-top: 2px solid darkgrey;
		}	
	</style>
	<div class="panel panel-default">
		<table class="table table-bordered table-striped">
			<tr>
				<th>Name:</th>
				<td><?php echo $user->get_name(); ?> <a href="change_name.php?uid=<?php echo $user->get_username(); ?>" class="btn btn-info btn-xs pull-right"><span class="glyphicon glyphicon-pencil"></span> Change Name</a></td>
			</tr>
			<tr>
				<th>Username:</th>
				<td><?php echo $user->get_username(); ?> <a href="change_username.php?uid=<?php echo $user->get_username(); ?>" class="btn btn-info btn-xs pull-right"><span class="glyphicon glyphicon-pencil"></span> Change Username</a></td>
			</tr>
			<tr>
				<th>UID Number:</th>
				<td><?php echo $user->get_uidnumber(); ?></td>
			</tr>
			<tr>
				<th>Email:</th>
				<td><?php echo $user->get_email(); ?></td>
			</tr>
			<tr>
				<th>Forwarding Email:</th>
				<td><?php if($user->get_emailforward() != null){
						echo $user->get_emailforward();
					} else {
						echo "None";
					} ?> 
					<a class='btn btn-info btn-xs pull-right' href='change_email_forward.php?uid=<?php echo $user->get_username(); ?>'><span class='glyphicon glyphicon-pencil'> </span> Change Forwarding Email</a></td>
			</tr>
			<tr>
				<th>Home Directory:</th>
				<td><a href="https://www-app2.igb.illinois.edu/file-server/user.php?username=<?php echo $user->get_username(); ?>" target="_blank"><?php echo $user->get_homeDirectory(); ?></a></td>
			</tr>
			<tr>
				<th>Login Shell:</th>
				<td><?php echo $user->get_loginShell(); ?> <a class='btn btn-info btn-xs pull-right' href='edit_login_shell.php?uid=<?php echo $user->get_username(); ?>'><span class='glyphicon glyphicon-pencil'> </span> Change Login Shell</a></td>
			</tr>
			
			<?php if($user->get_passwordSet() != null){ ?>
			<tr>
				<th>Password Last Set:</th>
				<td><?php echo strftime('%m/%d/%Y %I:%M:%S %p', $user->get_passwordSet() ); ?></td>
			</tr>
			<?php } ?>
			<tr>
				<th>Biocluster Access:</th>
				<td><?php if($user->get_machinerights() && in_array('biocluster.igb.illinois.edu', $user->get_machinerights())){
					echo "<a target='_blank' href='https://biocluster.igb.illinois.edu/accounting/user.php?username=$username'>Yes</a>";
				} else {
					echo "No <a href='add_to_biocluster.php?uid=$username' class='btn btn-primary btn-xs pull-right'><span class='glyphicon glyphicon-plus-sign'></span> Give Biocluster Access</a>";
				}
				?></td>
			</tr>
			<?php if($user->get_expiration() != null){
				?>
			<tr>
				<th>Expiration:</th>
				<td><?php echo strftime('%m/%d/%Y', $user->get_expiration()); ?> <a href="unexpire_user.php?uid=<?php echo $username; ?>" class="btn btn-xs btn-danger pull-right"><span class="glyphicon glyphicon-remove-circle"></span> Cancel Expiration</a></td>
			</tr>
				<?php
			} ?>
			<tr>
				<th>Created By:</th>
				<td><?php echo $user->get_creator(); ?></td>
			</tr>
			<tr>
				<th>Created:</th>
				<td><?php echo strftime('%m/%d/%Y %I:%M:%S %p', $user->get_createTime()); ?></td>
			</tr>
			<tr>
				<th>Modified By:</th>
				<td><?php echo $user->get_modifier(); ?></td>
			</tr>
			<tr>
				<th>Last Modified:</th>
				<td><?php echo strftime('%m/%d/%Y %I:%M:%S %p',$user->get_modifyTime()); ?></td>
			</tr>
			<?php if($user->islocked()) { ?>
				<tr class="danger">
					<td colspan="2" class="text-danger">User is locked</td>
				</tr>
			<?php	} ?>
			<?php if($user->get_leftcampus()) { ?>
				<tr class="warning">
					<td colspan="2" class="text-warning">User has left UIUC</td>
				</tr>
			<?php } ?>
		</table>

		<div class="panel-body">
			<a href="change_password.php?uid=<?php echo $username; ?>" class="btn btn-info"><span class="glyphicon glyphicon-pencil"></span> Change Password</a>
			<a href="expire_user.php?uid=<?php echo $username; ?>" class="btn btn-warning"><span class="glyphicon glyphicon-time"></span> Set Expiration</a>
			<?php if($user->islocked()){ ?>
			<a href="unlock_user.php?uid=<?php echo $username; ?>" class="btn btn-warning"><span class="glyphicon glyphicon-lock"></span> Unlock User</a>
			<?php } else { ?>
			<a href="lock_user.php?uid=<?php echo $username; ?>" class="btn btn-danger"><span class="glyphicon glyphicon-lock"></span> Lock User</a>
			<?php } ?>
			<a href="remove_user.php?uid=<?php echo $username; ?>" class="btn btn-danger"><span class="glyphicon glyphicon-trash"></span> Remove User</a>
		</div>
	</div>
	<div class="panel panel-default">
		<div class="panel-heading">
			<div class="btn-group btn-group-xs pull-right">
				<a class='btn btn-success btn-xs' href='add_to_group.php?uid=<?php echo $username; ?>'><span class='glyphicon glyphicon-plus'></span> Add to group</a>
				<button class='btn btn-default btn-xs copy-button'><span class='glyphicon glyphicon-copy'></span> Copy</button>
			</div>
			<h3 class="panel-title">Groups</h3>
		</div>
		<textarea class='hidden copy-text'><?php echo $groupcopytext; ?></textarea>
		<?php if(count($groups) > 16){
			echo '<div class="panel-body bg-warning"><span class="glyphicon glyphicon-alert"> </span> User is a member of >16 groups. This may cause issues with NFS.</div>';	
		} ?>
		<table class="table table-bordered table-striped">
			<?php echo $groupshtml; ?>
		</table>
	</div>
	<div class="panel panel-default">
		<div class="panel-heading">
			<div class="btn-group btn-group-xs pull-right">
				<a class='btn btn-success btn-xs' href='add_machinerights.php?uid=<?php echo $username; ?>'><span class='glyphicon glyphicon-plus'></span> Add machine rights</a>
				<button class='btn btn-default btn-xs copy-button'><span class='glyphicon glyphicon-copy'></span> Copy</button>
			</div>
			<h3 class="panel-title">Machine Rights</h3>
		</div>
		<textarea class='hidden copy-text'><?php echo $machinecopytext; ?></textarea>
		<table class="table table-bordered table-striped">
			<?php echo $machinerightshtml; ?>
		</table>
	</div>
	
	<?php
	require_once 'includes/footer.inc.php';