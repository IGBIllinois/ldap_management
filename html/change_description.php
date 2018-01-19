<?php
	$title = "Set Group Description";
	$sitearea = "groups";
	require_once 'includes/header.inc.php';
	
	$message="";
	if (isset($_POST['change_description'])) {
		foreach($_POST as $var){
			$var = trim(rtrim($var));
		}
		
		if($_POST['new_description'] == ""){
			$message .= html::error_message("Description cannot be blank.");
		}
		
		if($message == ""){
			$group = new group($ldap,$_POST['name']);
			$result = $group->set_description($_POST['new_description']);
		
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
<div class="minijumbo"><div class="container">Change Group Description</div></div>
<div class="container">
<form class="mt-4" method="post" action="<?php echo $_SERVER['PHP_SELF'];?>" name="form">
	<fieldset>
		<div class="row">
			<div class="col-sm-8">
				<div class="form-group row">
					<label class="col-sm-4 col-form-label" for="name-input">Group:</label>
					<div class="col-sm-8">
						<input type="hidden" name="name" value="<?php echo $gid; ?>" /><label class="col-form-label"><?php echo $gid; ?></label>
					</div>
				</div>
				<div class="form-group row">
					<label class="col-sm-4 col-form-label" for="description-input">Description:</label>
					<div class="col-sm-8">
						<input class="form-control" type="text" name="new_description" id="description_input" value="<?php if (isset($_POST['new_description'])){echo $_POST['new_description'];}?>" autofocus />
					</div>
				</div>
				<div class="form-group row">
					<div class="col-sm-8 offset-sm-4">
						<div class="btn-group">
							<input class="btn btn-info" type="submit" name="change_description" value="Change description" id="change_groupname_submit" /> <input class="btn btn-light" type="submit" name="cancel_group" value="Cancel" />
						</div>
					</div>
				</div>
			</div>
			<div class="col-sm-4">
				
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