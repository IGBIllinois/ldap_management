<?php
	$title = "Set Password";
	$sitearea = "users";
	require_once 'includes/header.inc.php';
	
	$message="";
	if (isset($_POST['change_password'])) {
		foreach($_POST as $var){
			$var = trim(rtrim($var));
		}
		
		if($_POST['username'] == ""){
			$message .= html::error_message("Username cannot be blank. Please stop trying to break my web interface.");
		}
		if($_POST['new_passworda'] == ""){
			$message .= html::error_message("Password cannot be blank.");
		} else if($_POST['new_passworda'] != $_POST['new_passwordb']){
			$message .= html::error_message("Passwords do not match.");
		}
		
		if($message == ""){
			$user = new user($ldap,$_POST['username']);
			$result = $user->set_password($_POST['new_passworda']);
		
			if($result['RESULT'] == true){
				header("Location: user.php?uid=".$result['uid']);
			} else if ($result['RESULT'] == false) {
				$message = html::error_message($result['MESSAGE']);
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
<div class="minijumbo"><div class="container">Change Password
	<?php if($uid != ""){ ?>
	<nav><ol class="breadcrumb"><li class="breadcrumb-item"><a href="list_users.php">Users</a></li><li class="breadcrumb-item"><a href="user.php?uid=<?php echo $uid; ?>"><?php echo $uid; ?></a></li><li class="breadcrumb-item active">Change Password</li></ol></nav>
	<?php } ?>
</div></div>
<div class="container">
<form class="mt-4" method="post" action="<?php echo $_SERVER['PHP_SELF'];?>" name="form">
	<fieldset>
		<div class="row">
			<div class="col-sm-8">
				<div class="form-group row">
					<label class="col-sm-4 col-form-label" for="username-input">Username:</label>
					<div class="col-sm-8">
						<input type="hidden" name="username" value="<?php echo $uid; ?>" autofocus /><label class="col-form-label"><?php echo $uid; ?></label>
					</div>
				</div>
				<div class="form-group row">
					<label class="col-sm-4 col-form-label" for="passworda-input">Password:</label>
					<div class="col-sm-8">
						<input class="form-control" type="password" name="new_passworda" id="passworda_input" value="<?php if (isset($_POST['new_passworda'])){echo $_POST['new_passworda'];}?>" oninput="change_password_errors()" autofocus />
					</div>
				</div>
				<div class="form-group row">
					<label class="col-sm-4 col-form-label" for="passwordb-input">Confirm Password:</label>
					<div class="col-sm-8">
						<input class="form-control" type="password" name="new_passwordb" id="passwordb_input" value="<?php if (isset($_POST['new_passwordb'])){echo $_POST['new_passwordb'];}?>" oninput="change_password_errors()"/>
					</div>
				</div>
				<div class="form-group row">
					<div class="col-sm-8 offset-sm-4">
						<div class="btn-group">
							<input class="btn btn-info" type="submit" name="change_password" value="Update password" id="change_password_submit" /> <input class="btn btn-light" type="submit" name="cancel_user" value="Cancel" />
						</div>
					</div>
				</div>
			</div>
			<div class="col-sm-4">
				<p id="passworderror1" class="text-danger"><span class="fa fa-times"></span> Password must be between 8 and 127 characters in length</p>
				<p id="passworderror2" class="text-danger"><span class="fa fa-times"></span> Password must contain at least 1 uppercase letter</p>
				<p id="passworderror3" class="text-danger"><span class="fa fa-times"></span> Password must contain at least 1 lowercase letter</p>
				<p id="passworderror4" class="text-danger"><span class="fa fa-times"></span> Password must contain at least 1 number or special character (no spaces)</p>
				<p id="passworderror5" class="text-danger"><span class="fa fa-times"></span> Password and confirm password must match</p>
				<p><button class="btn btn-light" id="password-button" type="button">Generate Password</button> <span id="password-text"></span></p>
			</div>
		</div>
	</fieldset>
</form>
<script type="text/javascript">
	$(document).ready(function(){
		change_password_errors();
		$('#password-button').on('click',function(){generate_password();change_password_errors();});
	});
</script>
<?php
	if(isset($message))echo $message;
	require_once 'includes/footer.inc.php';
?>