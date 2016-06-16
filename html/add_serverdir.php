<?php
	$title = "Add Server Directory";
	require_once 'includes/header.inc.php';

	$message="";
	if (isset($_POST['add_dir'])) {
		foreach($_POST as $var){
			$var = trim(rtrim($var));
		}
		
		if($_POST['group']==""){
			$message .= html::error_message("Please select a group.");
		} elseif (!group::is_ldap_group($ldap,$_POST['group'])) {
			$message .= html::error_message("Invalid group name. Please stop trying to break my web interface.");
		}
		if($_POST['new_server']==""){
			$message .= html::error_message("Please enter a server.");
		}
		if($_POST['new_directory']==""){
			$message .= html::error_message("Please enter a directory.");
		}
		
		if($message == ""){
			$group = new group($ldap,$_POST['group']);
			$result = $group->add_serverdir($_POST['new_server'],$_POST['new_directory']);
		
			if($result['RESULT'] == true){
				if($_POST['from']==""){
					$_POST['from']="group.php?gid=".$_POST['group'];
				}
				header("Location: ".$_POST['from']);
			} else if ($result['RESULT'] == false) {
				$message = $result['MESSAGE'];
			}
		}
	} else if (isset($_POST['cancel_dir'])) {
		header("Location: ".$_POST['from']);
		unset($_POST);
		exit();
	}

	$from = "";	
	$gid = "";
	$groupusers = array();
	if(isset($_GET['gid']) || isset($_POST['group'])){
		$gid = isset($_GET['gid'])?$_GET['gid']:$_POST['group'];
		if(group::is_ldap_group($ldap,$gid)){
			$from = "group.php?gid=$gid";
		} else {
			header("location: index.php");
			exit();
		}
	} else {
		header("location: index.php");
		exit();
	}

	$groupshtml = "";
	if($gid != ""){
		$groupshtml = "<input type='hidden' name='group' value='$gid'/><label class='control-label'>$gid</label>";
	}
	
	$servers = explode(",",__SERVERS__);
	$serverhtml = "<select name='new_server' class='form-control server-select'><option></option>";
	foreach($servers as $server){
		$serverhtml .= "<option>".$server."</option>";
	}
	$serverhtml .= "</select>";
?>
<form class="form-horizontal" method="post" action="<?php echo $_SERVER['PHP_SELF'];?>" name="form">
	<fieldset>
		<legend>Add Server Directory to Group</legend>
		<div class="form-group">
			<label class="col-sm-2 control-label" for="group-input">Group:</label>
			<div class="col-sm-4">
				<?php echo $groupshtml; ?>
			</div>
		</div>
		<div class="form-group">
			<label class="col-sm-2 control-label" for="username-input">Server:</label>
			<div class="col-sm-4">
				<?php echo $serverhtml; ?>
			</div>
		</div>
		<div class="form-group">
			<label class="col-sm-2 control-label" for="username-input">Directory:</label>
			<div class="col-sm-4">
<!-- 				TODO this must not contain a : -->
				<input class="form-control" type="text" name="new_directory" value="<?php if(isset($_POST['new_directory'])){echo $_POST['new_directory'];} ?>" />
			</div>
		</div>
		<div class="form-group">
			<div class="col-sm-4 col-sm-offset-2">
				<input type="hidden" name="from" value="<?php echo $from; ?>"/>
				<div class="btn-group">
					<input class="btn btn-success" type="submit" name="add_dir" value="Add directory" /> <input class="btn btn-default" type="submit" name="cancel_dir" value="Cancel" />
				</div>
			</div>
		</div>
	</fieldset>
</form>
<script type="text/javascript">
	$(document).ready(function(){
		$(".server-select").select2({
			placeholder: "Please select a server"
		});
	});
</script>
<?php
	if(isset($message))echo $message;
	require_once 'includes/footer.inc.php';
?>