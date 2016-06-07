<?php
	$title = "Remove Group";
	require_once 'includes/header.inc.php';
	
	$message="";
	if (isset($_POST['remove_group']) && isset($_POST['group'])) {
		foreach($_POST as $var){
			$var = trim(rtrim($var));
		}
		
		if($_POST['group'] == ""){
			$message .= "Group cannot be blank. Please stop trying to break my web interface.";
		}
		
		if($message == ""){
			$group = new group($ldap,$_POST['group']);
			$result = $group->remove();
		
			if($result['RESULT'] == true){
				header("Location: list_groups.php");
			} else if ($result['RESULT'] == false) {
				$message = $result['MESSAGE'];
			}
		}
	} else if (isset($_POST['cancel_user'])) {
		header('location: group.php?uid='.$_POST['group']);
		unset($_POST);
		exit();
	}
	
	$gid = "";
	if(isset($_GET['gid'])){
		$gid = $_GET['gid'];
	} else if (isset($_POST['group'])){
		$gid = $_POST['group'];
	}
	if($gid == ""){
		header('location: index.php');
	}
?>
<form class="form-horizontal" method="post" action="<?php echo $_SERVER['PHP_SELF'];?>" name="form">
	<fieldset>
		<legend>Remove group</legend>
		<div class="form-group">
			<label class="col-sm-2 control-label">Group:</label>
			<div class="col-sm-4">
				<input type="hidden" name="group" value="<?php echo $gid; ?>" autofocus /><label class="control-label"><?php echo $gid; ?></label>
			</div>
		</div>
		<div class="form-group">
			<div class="col-sm-2"></div>
			<div class="col-sm-4">
				Are you sure you want to remove this group? This operation cannot be undone.
			</div>
		</div>
		<div class="form-group">
			<div class="col-sm-4 col-sm-offset-2">
				<div class="btn-group">
					<input class="btn btn-danger" type="submit" name="remove_group" value="Remove group" /> <input class="btn btn-default" type="submit" name="cancel_group" value="Cancel" />
				</div>
			</div>
		</div>
	</fieldset>
</form>
<script type="text/javascript">

</script>
<?php
	if(isset($message))echo $message;
	require_once 'includes/footer.inc.php';
?>
