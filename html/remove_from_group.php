<?php
	$title = "Remove from Group";
	require_once 'includes/header.inc.php';
	
	$message="";
	if (isset($_POST['remove_user'])) {
		foreach($_POST as $var){
			$var = trim(rtrim($var));
		}
		
		if($_POST['group']==""){
			$message .= html::error_message("Please select a group.");
		} elseif (!group::is_ldap_group($ldap,$_POST['group'])) {
			$message .= html::error_message("Invalid group name. Please stop trying to break my web interface.");
		}
		$group = new group($ldap,$_POST['group']);
		
		if($_POST['username']==""){
			$message .= html::error_message("Please select a user.");
		} elseif (!user::is_ldap_user($ldap,$_POST['username']) && !in_array($_POST['username'],$group->get_users())) {
			$message .= html::error_message("Invalid username. Please stop trying to break my web interface.");
		}
		
		if($message == ""){
			$result = $group->remove_user($_POST['username']);
		
			if($result['RESULT'] == true){
				header("Location: group.php?gid=".$result['gid']);
			} else if ($result['RESULT'] == false) {
				$message = $result['MESSAGE'];
			}
		}
	} else if (isset($_POST['cancel_user'])) {
		if(isset($_POST['username'])){
			header("location: user.php?uid=".$_POST['username']);
		}
		unset($_POST);
	}
	
	$gid = "";
	$groupusers = array();
	if(isset($_GET['gid']) && group::is_ldap_group($ldap,$_GET['gid'])){
		$gid = $_GET['gid'];
		$grouptoadd = new group($ldap,$gid);
		$groupusers = $grouptoadd->get_users();
	}
	
	$uid = "";
	$usergroups = array();
	if(isset($_GET['uid']) && (user::is_ldap_user($ldap,$_GET['uid']) || in_array($_GET['uid'], $grouptoadd->get_users()))){
		$uid = $_GET['uid'];
		$usertoadd = new user($ldap,$uid);
		$usergroups = $usertoadd->get_groups();
	}
	
	$usershtml = "";
	if($gid == ""){
		$users = user::get_all_users($ldap);
	} else {
		$users = $groupusers;
	}
	if($uid!=""){
		$usershtml = "<input type='hidden' name='username' value='$uid'/><label class='control-label'>$uid</label>";
	} else {
		$usershtml .= "<select name='username' class='form-control username-select'><option></option>";
		foreach($users as $user){
			$usershtml .= "<option value='".$user."'";
			if($uid == $user){
				$usershtml .= " selected";
			}
			$usershtml .= ">".$user."</option>";
		}
		$usershtml .= "</select>";
	}
	
	$groupshtml = "";
	if($uid == ""){
		$groups = group::get_all_groups($ldap);
	} else {
		$groups = $usergroups;
	}
	if($gid!=""){
		$groupshtml = "<input type='hidden' name='group' value='$gid'/><label class='control-label'>$gid</label>";
	} else {
		$groupshtml .= "<select name='group' class='form-control group-select'><option></option>";
		foreach($groups as $group){
			if(!$usergroups || in_array($group,$usergroups)){
				$groupshtml .= "<option value='".$group."'";
				if($gid == $group){
					$groupshtml .= " selected";
				}
				$groupshtml .= ">".$group."</option>";
			} 
		}
		$groupshtml .= "</select>";
	}
?>
<form class="form-horizontal" method="post" action="<?php echo $_SERVER['PHP_SELF'];?>" name="form">
	<fieldset>
		<legend>Remove User from Group</legend>
		<div class="form-group">
			<label class="col-sm-2 control-label" for="username-input">Username:</label>
			<div class="col-sm-4">
				<?php echo $usershtml; ?>
			</div>
		</div>
		<div class="form-group">
			<label class="col-sm-2 control-label" for="group-input">Group:</label>
			<div class="col-sm-4">
				<?php echo $groupshtml; ?>
			</div>
		</div>
		<div class="form-group">
			<div class="col-sm-4 col-sm-offset-2">
				<div class="btn-group">
					<input class="btn btn-danger" type="submit" name="remove_user" value="Remove user" /> <input class="btn btn-default" type="submit" name="cancel_user" value="Cancel" />
				</div>
			</div>
		</div>
	</fieldset>
</form>
<script type="text/javascript">
	$(document).ready(function(){
		$(".username-select").select2({
			placeholder: "Please select a user"
		});
		$(".group-select").select2({
			placeholder: "Please select a group"
		});
	});
</script>
<?php
	if(isset($message))echo $message;
	require_once 'includes/footer.inc.php';
?>