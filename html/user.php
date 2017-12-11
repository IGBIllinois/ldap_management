<?php
	$title = "User Info: ".$_GET['uid'];
	$sitearea = "users";
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
			$machinerightshtml .= "<tr><td class='pl-2'><a class='align-middle' href='host.php?hid=".$machinerights[$i]."'>".$machinerights[$i]."</a>";
			if(!host::is_ldap_host($ldap,$machinerights[$i])){
				$machinerightshtml .= " <span class='fa fa-exclamation-triangle smallwarning' title='Host does not exist'></span>";
			}
			$machinerightshtml .= " <a class='btn btn-danger btn-sm pull-right' href='remove_machinerights.php?uid=$username&hid=".$machinerights[$i]."&from=user'><span class='fa fa-times'> </span> Remove host</a></td></tr>";
			$machinecopytext .= $machinerights[$i]."\n";
		}
	}
	
	$groupshtml = "";
	$groupcopytext = "";
	$groups = $user->get_groups();
	sort($groups);
	for($i=0; $i<count($groups);$i++){
		$groupshtml .= "<tr><td class='pl-2'><a class='align-middle' href='group.php?gid=".$groups[$i]."'>".$groups[$i]."</a>";
		if($username != $groups[$i]){
			$groupshtml .= " <a class='btn btn-danger btn-sm pull-right' href='remove_from_group.php?uid=$username&gid=".$groups[$i]."&from=user'><span class='fa fa-times'> </span> Remove from group</a>";
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
		$prevUid = user::get_previous_user($ldap,$username,$search,$sort,$asc,$filter);
		$nextUid = user::get_next_user($ldap,$username,$search,$sort,$asc,$filter);
	}
	?>
	<style>
		tr.topborder {
			border-top: 2px solid darkgrey;
		}	
	</style>
	<div class="card mt-4">
		<?php if($prevUid!=null || $nextUid!=null){ ?>
		<div class="row">
			<div class="col-sm-3 col-lg-2">
				<a <?php if($prevUid!=null){echo 'href="user.php?uid='.$prevUid.'"';} ?> class="btn btn-light btn-prev btn-block<?php if($prevUid==null){echo " disabled";}?>">
					<span class="fa fa-chevron-left"></span> Previous
				</a>
			</div>
			<div class="col-sm-6 col-lg-8">
			</div>
			<div class="col-sm-3 col-lg-2">
				<a <?php if($nextUid!=null){echo 'href="user.php?uid='.$nextUid.'"';} ?> class="btn btn-light btn-next btn-block<?php if($nextUid==null){echo " disabled";}?>">
					Next <span class="fa fa-chevron-right"></span>
				</a>
			</div>
		</div>
		<?php } ?>
		<table class="table table-striped table-igb-bordered table-responsive-md mb-0">
			<?php if($user->islocked()) { ?>
				<tr>
					<td colspan="2" class="table-danger">User is locked</td>
				</tr>
			<?php	} ?>
			<?php if($user->is_password_expired()) { ?>
				<tr>
					<td colspan="2" class="table-danger">Password has expired</td>
				</tr>
			<?php	} ?>
			<?php if($user->get_leftcampus()) { ?>
				<tr>
					<td colspan="2" class="table-warning">User has left UIUC</td>
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
							$button = " <a href='$url?attr=".$attributes[$attr]['name']."&uid=".$user->get_username()."' class='btn btn-sm pull-right ".$color."'>".$icon." ".$text."</a>";
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
						echo "<tr><th><span class='align-middle'>".$attributes[$attr]['fullname'].":</span></th><td><span class='align-middle'>".$field."</span>".$button."</td></tr>";
					}
				}
			?>
		</table>
		<hr class="my-0">
		<div class="card-body">
			<a href="change_password.php?uid=<?php echo $username; ?>" class="btn btn-info mt-1 mt-md-0"><span class="fa fa-pencil"></span> Change Password</a>
			<a href="expire_user.php?uid=<?php echo $username; ?>" class="btn btn-warning mt-1 mt-md-0"><span class="fa fa-clock-o"></span> Set Expiration</a>
			<?php if($user->islocked()){ ?>
			<a href="unlock_user.php?uid=<?php echo $username; ?>" class="btn btn-warning mt-1 mt-md-0"><span class="fa fa-unlock-alt"></span> Unlock User</a>
			<?php } else { ?>
			<a href="lock_user.php?uid=<?php echo $username; ?>" class="btn btn-danger mt-1 mt-md-0"><span class="fa fa-lock"></span> Lock User</a>
			<?php } ?>
			<a href="remove_user.php?uid=<?php echo $username; ?>" class="btn btn-danger mt-1 mt-md-0"><span class="fa fa-trash"></span> Remove User</a>
		</div>
	</div>
	<div class="card mt-3">
		<div class="card-header d-flex align-items-center">
			<h4 class="mr-auto">Groups</h4>
			<div class="btn-group btn-group-sm">
				<a class='btn btn-success btn-sm' href='add_to_group.php?uid=<?php echo $username; ?>'><span class='fa fa-plus'></span> Add to group</a>
				<button class='btn btn-light btn-sm copy-button'><span class='fa fa-clipboard'></span> Copy</button>
			</div>
		</div>
		<textarea class='d-none copy-text'><?php echo $groupcopytext; ?></textarea>
		<?php if(count($groups) > 16){
			echo '<div class="card-body bg-warning"><span class="fa fa-exclamation-triangle"> </span> User is a member of >16 groups. This may cause issues with NFS.</div>';	
		} ?>
		<table class="table table-sm table-striped mb-0">
			<?php echo $groupshtml; ?>
		</table>
	</div>
	<div class="card mt-3">
		<div class="card-header d-flex align-items-center">
			<h4 class="mr-auto">Machine Rights</h4>
			<div class="btn-group btn-group-sm">
				<a class='btn btn-success btn-sm' href='add_machinerights.php?uid=<?php echo $username; ?>'><span class='fa fa-plus'></span> Add machine rights</a>
				<button class='btn btn-light btn-sm copy-button'><span class='fa fa-clipboard'></span> Copy</button>
			</div>
		</div>
		<textarea class='d-none copy-text'><?php echo $machinecopytext; ?></textarea>
		<table class="table table-sm table-striped mb-0">
			<?php echo $machinerightshtml; ?>
		</table>
	</div>
	
	<?php
	require_once 'includes/footer.inc.php';