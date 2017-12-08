<?php
	$title = "Add Server Directory";
	$sitearea = "groups";
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
		if($_POST['host']==""){
			$message .= html::error_message("Please enter a server.");
		}
		if($_POST['new_directory']==""){
			$message .= html::error_message("Please enter a directory.");
		}
		
		if($message == ""){
			$group = new group($ldap,$_POST['group']);
			$result = $group->add_serverdir($_POST['host'],$_POST['new_directory']);
		
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
	
	$hostshtml = "";
	$hosts = host::get_all_hosts($ldap);
	
	$igb = array();
	$biotech = array();
	$other = array();
	foreach($hosts as $host){
		if(strpos($host['name'],"biotec") !== false){
			$biotech[] = $host['name'];
		} else if(strpos($host['name'],"igb") !== false){
			$igb[] = $host['name'];
		} else {
			$other[] = $host['name'];
		}
	}
	sort($igb);
	sort($biotech);
	sort($other);
	
	$hostshtml .= "<select name='host' class='form-control host-select'><option></option>";
	$hostshtml .= "<optgroup label='IGB Hosts'>";
	foreach($igb as $host){
		$hostshtml .= "<option value='".$host."'";
		$hostshtml .= ">".$host."</option>";
	}
	$hostshtml .= "/<optgroup>";
	$hostshtml .= "<optgroup label='Biotech Hosts'>";
	foreach($biotech as $host){
		$hostshtml .= "<option value='".$host."'";
		$hostshtml .= ">".$host."</option>";
	}
	$hostshtml .= "/<optgroup>";
	if(count($other)!=0){
		$hostshtml .= "<optgroup label='Other Hosts'>";
		foreach($other as $host){
			$hostshtml .= "<option value='".$host."'";
			$hostshtml .= ">".$host."</option>";
		}
		$hostshtml .= "/<optgroup>";
	}
		$hostshtml .= "</select>";

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
				<?php echo $hostshtml; ?>
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
		$(".host-select").select2({
			placeholder: "Please select a server",
			width: 'element'
		});
	});
</script>
<?php
	if(isset($message))echo $message;
	require_once 'includes/footer.inc.php';
?>