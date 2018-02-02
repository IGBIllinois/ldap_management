<?php
	$title = "Remove from Group";
	$sitearea = "groups";
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
				header("Location: ".$_POST['from']);
			} else if ($result['RESULT'] == false) {
				$message = $result['MESSAGE'];
			}
		}
	} else if (isset($_POST['cancel_user'])) {
		if(isset($_POST['username'])){
			header("location: ".$_POST['from']);
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
	
	$from = "group.php?gid=$gid";
	if( isset($_GET['from']) && $_GET['from']=='user' ) {
		$from = "user.php?uid=$uid";
	}
	if( isset($_POST['from']) ){
		$from = $_POST['from'];
	}
	
	$usershtml = "";
	if($gid == ""){
		$users = user::get_all_users($ldap);
	} else {
		$users = $groupusers;
	}
	if($uid!=""){
		$usershtml = "<input type='hidden' name='username' value='$uid'/><label class='col-form-label'>$uid</label>";
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
		$groupshtml = "<input type='hidden' name='group' value='$gid'/><label class='col-form-label'>$gid</label>";
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
	$searchdescription = html::get_list_users_description_from_cookies();
?>
<div class="minijumbo"><div class="container">Remove User from Group
	<?php if( isset($_GET['from']) && $_GET['from']=='user'){ ?>
	<nav><ol class="breadcrumb"><li class="breadcrumb-item"><a href="<?php echo html::get_list_users_url_from_cookies(); ?>">Users<?php if($searchdescription!=""){echo " ($searchdescription)";} ?></a></li><li class="breadcrumb-item"><a href="user.php?uid=<?php echo $uid; ?>"><?php echo $uid; ?></a></li><li class="breadcrumb-item active">Remove User from Group</li></ol></nav>
	<?php } else { ?>
	<nav><ol class="breadcrumb"><li class="breadcrumb-item"><a href="list_groups.php">Groups</a></li><li class="breadcrumb-item"><a href="group.php?gid=<?php echo $gid; ?>"><?php echo $gid; ?></a></li><li class="breadcrumb-item active">Remove User from Group</li></ol></nav>
	<?php } ?>
</div></div>
<div class="container">
<form class="mt-4" method="post" action="<?php echo $_SERVER['PHP_SELF'];?>" name="form">
	<fieldset>
		<div class="form-group row">
			<label class="col-sm-3 col-form-label" for="username-input">Username:</label>
			<div class="col-sm-5">
				<?php echo $usershtml; ?>
			</div>
		</div>
		<div class="form-group row">
			<label class="col-sm-3 col-form-label" for="group-input">Group:</label>
			<div class="col-sm-5">
				<?php echo $groupshtml; ?>
			</div>
		</div>
		<div class="form-group row">
			<div class="col-sm-5 offset-sm-3">
				<div class="btn-group">
					<input type="hidden" name="from" value="<?php echo $from; ?>">
					<input class="btn btn-danger" type="submit" name="remove_user" value="Remove user" /> <input class="btn btn-light" type="submit" name="cancel_user" value="Cancel" />
				</div>
			</div>
		</div>
	</fieldset>
</form>
<script type="text/javascript">
	$(document).ready(function(){
		$(".username-select").select2({
			placeholder: "Please select a user",
			width: 'element'
		});
		$(".group-select").select2({
			placeholder: "Please select a group",
			width: 'element'
		});
	});
</script>
<?php
	if(isset($message))echo $message;
	require_once 'includes/footer.inc.php';
?>