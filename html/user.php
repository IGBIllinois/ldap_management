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
				$machinerightshtml .= " <span class='fa fa-exclamation-triangle smallwarning' title='Host does not exist'></span>";
			}
			$machinerightshtml .= " <a class='btn btn-danger btn-xs pull-right' href='remove_machinerights.php?uid=$username&hid=".$machinerights[$i]."&from=user'><span class='fa fa-times'> </span> Remove host</a></td></tr>";
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
			$groupshtml .= " <a class='btn btn-danger btn-xs pull-right' href='remove_from_group.php?uid=$username&gid=".$groups[$i]."&from=user'><span class='fa fa-times'> </span> Remove from group</a>";
		}
		$groupcopytext .= $groups[$i]."\n";
		$groupshtml .= "</td></tr>";
	}

// 	Next/prev buttons
	if(isset($_COOKIE['lastUserSearchFilter']) and isset($_COOKIE['lastUserSearchSort']) and isset($_COOKIE['lastUserSearchAsc'])){
		// Cookies are set, let's do this.
		$filter = $_COOKIE['lastUserSearchFilter'];
		$sort =   $_COOKIE['lastUserSearchSort'];
		$asc =    $_COOKIE['lastUserSearchAsc'] == 'true';
		$search = isset($_COOKIE['lastUserSearch'])?$_COOKIE['lastUserSearch']:'';
		$prevUid = user::get_previous_user($ldap,$adldap,$username,$search,$sort,$asc,$filter);
		$nextUid = user::get_next_user($ldap,$adldap,$username,$search,$sort,$asc,$filter);
	}
	?>
	<style>
		tr.topborder {
			border-top: 2px solid darkgrey;
		}	
	</style>
	<div class="panel panel-default">
		<?php if(isset($prevUid) && ($prevUid!=null || $nextUid!=null)){ ?>
		<div class="row">
			<div class="col-sm-2">
				<a <?php if($prevUid==null){echo "disabled";} else { echo 'href="user.php?uid='.$prevUid.'"';} ?> class="btn btn-default btn-prev btn-block">
					<span class="fa fa-chevron-left"></span> Previous
				</a>
			</div>
			<div class="col-sm-8">
			</div>
			<div class="col-sm-2">
				<a <?php if($nextUid==null){echo "disabled";} else { echo 'href="user.php?uid='.$nextUid.'"';} ?> class="btn btn-default btn-next btn-block">
					Next <span class="fa fa-chevron-right"></span>
				</a>
			</div>
		</div>
		<?php } ?>
		<table class="table table-bordered table-striped">
			<?php if($user->islocked()) { ?>
				<tr class="danger">
					<td colspan="2" class="text-danger">User is locked</td>
				</tr>
			<?php	} ?>
			<?php if($user->is_password_expired()) { ?>
				<tr class="danger">
					<td colspan="2" class="text-danger">Password has expired</td>
				</tr>
			<?php	} ?>
			<?php if($user->get_leftcampus()) { ?>
				<tr class="warning">
					<td colspan="2" class="text-warning">User has left UIUC</td>
				</tr>
			<?php } ?>
			<?php
				if($attributes = extensions::get_attributes('user')){
					for($attr=0; $attr<count($attributes); $attr++){
						// TODO get uid field programmatically
						$classname = 'ext_'.$attributes[$attr]['ext'];
						// Generate button, if necessary
						$button = "";
						$funcname = $attributes[$attr]['name'].'_button';
						if(method_exists($classname, $funcname)){
							// Use custom method, if available
							$button = $classname::$funcname($ldap,$user->get_username());
						} else if(isset($attributes[$attr]['button'])){
							// Generate from JSON attributes
							$color = 'btn-primary';
							$icon = '';
							$text = '';
							$url = '';
							if(isset($attributes[$attr]['button']['type']) && $attributes[$attr]['button']['type'] == 'edit'){
								$color = 'btn-info';
								$icon = '<span class="fa fa-pencil"></span>';
								$text = "Change ".$attributes[$attr]['fullname'];
								$url = 'edit_user_attribute.php';
							}
							if(isset($attributes[$attr]['button']['type']) && $attributes[$attr]['button']['type'] == 'remove'){
								$color = 'btn-danger';
								$icon = '<span class="fa fa-times-circle"></span>';
								$text = "Remove ".$attributes[$attr]['fullname'];
								$url = 'remove_user_attribute.php';
							}
							if(isset($attributes[$attr]['button']['color'])){
								$color = 'btn-'.$attributes[$attr]['button']['color'];
							}
							if(isset($attributes[$attr]['button']['icon'])){
								$icon = '<span class="fa fa-'.$attributes[$attr]['button']['icon'].'"></span>';
							}
							if(isset($attributes[$attr]['button']['text'])){
								$text = $attributes[$attr]['button']['text'];
							}
							if(isset($attributes[$attr]['button']['url'])){
								$url = $attributes[$attr]['button']['text'];
							}
							$button = " <a href='$url?attr=".$attributes[$attr]['name']."&uid=".$user->get_username()."' class='btn btn-xs pull-right ".$color."'>".$icon." ".$text."</a>";
						}
						// Fetch field
						$field = '';
						if(isset($attributes[$attr]['field'])){
							if($user->get_attribute($attributes[$attr]['field'])==NULL && isset($attributes[$attr]['button']) && $attributes[$attr]['button']['type']!='edit'){
								continue;
							}
							// LDAP field given, fetch it.
							$field = extensions::format($user->get_attribute($attributes[$attr]['field']),isset($attributes[$attr]['format'])?$attributes[$attr]['format']:'');
						} else {
							// No LDAP field given, look for a function
							$funcname = $attributes[$attr]['name'].'_field';
							if(method_exists($classname, $funcname)){
								$field = $classname::$funcname($ldap,$user->get_username());
							}
						}
						if($field == '' && isset($attributes[$attr]['button']) && $attributes[$attr]['button']['type']!='edit'){
							continue;
						}
						echo "<tr><th>".$attributes[$attr]['fullname'].":</th><td>".$field.$button."</td></tr>";
					}
				}
			?>
		</table>

		<div class="panel-body">
			<a href="change_password.php?uid=<?php echo $username; ?>" class="btn btn-info"><span class="fa fa-pencil"></span> Change Password</a>
			<a href="expire_user.php?uid=<?php echo $username; ?>" class="btn btn-warning"><span class="fa fa-clock-o"></span> Set Expiration</a>
			<?php if($user->islocked()){ ?>
			<a href="unlock_user.php?uid=<?php echo $username; ?>" class="btn btn-warning"><span class="fa fa-unlock-alt"></span> Unlock User</a>
			<?php } else { ?>
			<a href="lock_user.php?uid=<?php echo $username; ?>" class="btn btn-danger"><span class="fa fa-lock"></span> Lock User</a>
			<?php } ?>
			<a href="remove_user.php?uid=<?php echo $username; ?>" class="btn btn-danger"><span class="fa fa-trash"></span> Remove User</a>
		</div>
	</div>
	<div class="panel panel-default">
		<div class="panel-heading">
			<div class="btn-group btn-group-xs pull-right">
				<a class='btn btn-success btn-xs' href='add_to_group.php?uid=<?php echo $username; ?>'><span class='fa fa-plus'></span> Add to group</a>
				<button class='btn btn-default btn-xs copy-button'><span class='fa fa-clipboard'></span> Copy</button>
			</div>
			<h3 class="panel-title">Groups</h3>
		</div>
		<textarea class='hidden copy-text'><?php echo $groupcopytext; ?></textarea>
		<?php if(count($groups) > 16){
			echo '<div class="panel-body bg-warning"><span class="fa fa-exclamation-triangle"> </span> User is a member of >16 groups. This may cause issues with NFS.</div>';	
		} ?>
		<table class="table table-bordered table-striped">
			<?php echo $groupshtml; ?>
		</table>
	</div>
	<div class="panel panel-default">
		<div class="panel-heading">
			<div class="btn-group btn-group-xs pull-right">
				<a class='btn btn-success btn-xs' href='add_machinerights.php?uid=<?php echo $username; ?>'><span class='fa fa-plus'></span> Add machine rights</a>
				<button class='btn btn-default btn-xs copy-button'><span class='fa fa-clipboard'></span> Copy</button>
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