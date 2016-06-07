<?php
	$title = "Set User Name";
	require_once 'includes/header.inc.php';
	
	$message="";
	if (isset($_POST['change_name'])) {
		foreach($_POST as $var){
			$var = trim(rtrim($var));
		}
		
		if($_POST['new_firstname'] == ""){
			$message .= html::error_message("First name cannot be blank.");
		}
		if($_POST['new_lastname'] == ""){
			$message .= html::error_message("Last name cannot be blank.");
		}
		
		if($message == ""){
			$user = new user($ldap,$_POST['username']);
			$result = $user->set_name($_POST['new_firstname'],$_POST['new_lastname']);
		
			if($result['RESULT'] == true){
				header("Location: user.php?uid=".$result['uid']);
			} else if ($result['RESULT'] == false) {
				$message = $result['MESSAGE'];
			}
		}
	} else if (isset($_POST['cancel_user'])) {
		header('location: user.php?uid='.$_POST['username']);
		unset($_POST);
		exit();
	}
	
	$uid = "";
	if(isset($_GET['uid'])){
		$uid = $_GET['uid'];
	} else if (isset($_POST['username'])){
		$uid = $_POST['username'];
	}
	if($uid == ""){
		header('location: index.php');
	}
?>
<form class="form-horizontal" method="post" action="<?php echo $_SERVER['PHP_SELF'];?>" name="form">
	<fieldset>
		<legend>Change Name</legend>
		<div class="row">
			<div class="col-sm-6">
				<div class="form-group">
					<label class="col-xs-4 control-label" for="username-input">Username:</label>
					<div class="col-xs-8">
						<input type="hidden" name="username" value="<?php echo $uid; ?>" autofocus /><label class="control-label"><?php echo $uid; ?></label>
					</div>
				</div>
				<div class="form-group">
					<label class="col-sm-4 control-label" for="passworda-input">First Name:</label>
					<div class="col-sm-8">
						<input class="form-control" type="text" name="new_firstname" id="firstname_input" value="<?php if (isset($_POST['new_firstname'])){echo $_POST['new_firstname'];}?>" autofocus />
					</div>
				</div>
				<div class="form-group">
					<label class="col-sm-4 control-label" for="passwordb-input">Last Name:</label>
					<div class="col-sm-8">
						<input class="form-control" type="text" name="new_lastname" id="lastname_input" value="<?php if (isset($_POST['new_lastname'])){echo $_POST['new_lastname'];}?>" />
					</div>
				</div>
				<div class="form-group">
					<div class="col-sm-8 col-sm-offset-4">
						<div class="btn-group">
							<input class="btn btn-info" type="submit" name="change_name" value="Change name" id="change_name_submit" /> <input class="btn btn-default" type="submit" name="cancel_user" value="Cancel" />
						</div>
					</div>
				</div>
			</div>
			<div class="col-sm-6">
				
			</div>
		</div>
	</fieldset>
</form>
<?php
	if(isset($message))echo $message;
	require_once 'includes/footer.inc.php';
?>