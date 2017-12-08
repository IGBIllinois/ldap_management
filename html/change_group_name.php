<?php
	$title = "Set Group Name";
	$sitearea = "groups";
	require_once 'includes/header.inc.php';
	
	$message="";
	if (isset($_POST['change_name'])) {
		foreach($_POST as $var){
			$var = trim(rtrim($var));
		}
		
		if($_POST['new_name'] == ""){
			$message .= html::error_message("Name cannot be blank.");
		}
		
		if($message == ""){
			$group = new group($ldap,$_POST['name']);
			$result = $group->set_name($_POST['new_name']);
		
			if($result['RESULT'] == true){
				header("Location: group.php?gid=".$result['gid']);
			} else if ($result['RESULT'] == false) {
				$message = $result['MESSAGE'];
			}
		}
	} else if (isset($_POST['cancel_group'])) {
		header('location: group.php?gid='.$_POST['name']);
		unset($_POST);
		exit();
	}
	
	$gid = "";
	if(isset($_GET['gid'])){
		$gid = $_GET['gid'];
	} else if (isset($_POST['name'])){
		$gid = $_POST['name'];
	}
	if($gid == ""){
		header('location: index.php');
	}
?>
<form class="form-horizontal" method="post" action="<?php echo $_SERVER['PHP_SELF'];?>" name="form">
	<fieldset>
		<legend>Change Group Name</legend>
		<div class="row">
			<div class="col-sm-6">
				<div class="form-group">
					<label class="col-xs-4 control-label" for="name-input">Group:</label>
					<div class="col-xs-8">
						<input type="hidden" name="name" value="<?php echo $gid; ?>" /><label class="control-label"><?php echo $gid; ?></label>
					</div>
				</div>
				<div class="form-group">
					<label class="col-sm-4 control-label" for="name-input">New Group Name:</label>
					<div class="col-sm-8">
						<input class="form-control" type="text" name="new_name" id="name_input" value="<?php if (isset($_POST['new_name'])){echo $_POST['new_name'];}?>" oninput="change_groupname_errors();" autofocus />
					</div>
				</div>
				<div class="form-group">
					<div class="col-sm-8 col-sm-offset-4">
						<div class="btn-group">
							<input class="btn btn-info" type="submit" name="change_name" value="Change name" id="group_submit" /> <input class="btn btn-default" type="submit" name="cancel_group" value="Cancel" />
						</div>
					</div>
				</div>
			</div>
			<div class="col-sm-6">
				<p id="groupnameerror1" class="text-success"><span class="fa fa-check"></span> <span class="text">Name not in use</span></p>
				<p id="groupnameerror2" class="text-danger"><span class="fa fa-times"></span> <span class="text">Name must begin with a lowercase letter</span></p>
				<p id="groupnameerror3" class="text-danger"><span class="fa fa-times"></span> Name must be alphanumeric (letters, numbers, underscore)</p>
			</div>
		</div>
	</fieldset>
</form>
<script type="text/javascript">
	$(document).ready(function(){change_groupname_errors();});
</script>
<?php
	if(isset($message))echo $message;
	require_once 'includes/footer.inc.php';
?>