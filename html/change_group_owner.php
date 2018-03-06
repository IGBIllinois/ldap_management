<?php
	$title = "Change Group Owner";
	$sitearea = "groups";
	require_once 'includes/header.inc.php';
	
	$message="";
	if (isset($_POST['change_owner'])) {
		$_POST = array_map("trim",$_POST);
		
		if($_POST['username']==""){
			$message .= html::error_message("Please select a user.");
		} elseif (!user::is_ldap_user($ldap,$_POST['username'])) {
			$message .= html::error_message("Invalid username. Please stop trying to break my web interface.");
		}
		if($_POST['group']==""){
			$message .= html::error_message("Please select a group.");
		} elseif (!group::is_ldap_group($ldap,$_POST['group'])) {
			$message .= html::error_message("Invalid group name. Please stop trying to break my web interface.");
		}
		
		if($message == ""){
			$group = new group($ldap,$_POST['group']);
			$result = $group->set_owner($_POST['username']);
		
			if($result['RESULT'] == true){
				if($_POST['from']==""){
					$_POST['from']="group.php?gid=".$_POST['group'];
				}
				header("Location: ".$_POST['from']);
				exit();
			} else if ($result['RESULT'] == false) {
				$message = $result['MESSAGE'];
			}
		}
	} elseif (isset($_POST['cancel_user'])) {
		header("Location: ".$_POST['from']);
		unset($_POST);
		exit();
	}
	
	$from = "";
	$uid = "";
	$usergroups = array();
	if(isset($_GET['uid']) && user::is_ldap_user($ldap,$_GET['uid'])){
		$uid = $_GET['uid'];
		$usertoadd = new user($ldap,$uid);
		$from = "user.php?uid=$uid";
	}
	
	$gid = "";
	$groupusers = array();
	if( (isset($_GET['gid']) && group::is_ldap_group($ldap,$_GET['gid'])) || (isset($_POST['group']) && group::is_ldap_group($ldap,$_POST['group'])) ){
		$gid = $_GET['gid'];
		$grouptoadd = new group($ldap,$gid);
		$from = "group.php?gid=$gid";
	}
	
	$usershtml = "";
	$users = user::get_all_users($ldap);
	if($uid != ""){
		$usershtml = "<input type='hidden' name='username' value='$uid'/><label class='col-form-label'>$uid</label>";
	} else {
		$usershtml .= "<select name='username' class='form-control username-select'><option></option>";
		foreach($users as $user){
			$usershtml .= "<option value='".$user."'";
			if($grouptoadd->get_owner() == $user){
				$usershtml .= " selected";
			}
			$usershtml .= ">".$user."</option>";
		}
		$usershtml .= "</select>";
	}
	
	$groupshtml = "";
	$groups = group::get_search_groups($ldap,"",-1,-1,'name',"true",1);
	if($gid != ""){
		$groupshtml = "<input type='hidden' name='group' value='$gid'/><label class='col-form-label'>$gid</label>";
	} else {
		$groupshtml .= "<select name='group' class='form-control group-select'><option></option>";
		foreach($groups as $group){
			$groupshtml .= "<option value='".$group['name']."'";
			if($gid == $group['name']){
				$groupshtml .= " selected";
			}
			$groupshtml .= ">".$group['name']."</option>";
		}
		$groupshtml .= "</select>";
	}
?>
<div class="minijumbo"><div class="container">Change Group Owner
	<?php if($gid != "") { ?>
	<nav><ol class="breadcrumb"><li class="breadcrumb-item"><a href="list_groups.php">Groups</a></li><li class="breadcrumb-item"><a href="group.php?gid=<?php echo $gid; ?>"><?php echo $gid; ?></a></li><li class="breadcrumb-item active">Change Owner</li></ol></nav>
	<?php } ?>
</div></div>
<div class="container">
<form class="mt-4" method="post" action="<?php echo $_SERVER['PHP_SELF'];?>" name="form">
	<fieldset>
		<div class="form-group row">
			<label class="col-sm-3 col-form-label" for="group-input">Group:</label>
			<div class="col-sm-5">
				<?php echo $groupshtml; ?>
			</div>
		</div>
		<div class="form-group row">
			<label class="col-sm-3 col-form-label" for="username-input">Username:</label>
			<div class="col-sm-5">
				<?php echo $usershtml; ?>
			</div>
		</div>
		<div class="form-group row">
			<div class="col-sm-5 offset-sm-3">
				<input type="hidden" name="from" value="<?php echo $from; ?>"/>
				<div class="btn-group">
					<input class="btn btn-success" type="submit" name="change_owner" value="Change owner" /> <input class="btn btn-light" type="submit" name="cancel_user" value="Cancel" />
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