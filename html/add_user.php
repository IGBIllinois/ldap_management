<?php
	$title = "Add User";
	require_once 'includes/header.inc.php';
	
	$message="";
	if (isset($_POST['add_user'])) {
		foreach($_POST as $var){
			$var = trim(rtrim($var));
		}
		
		if($_POST['new_passworda'] != $_POST['new_passwordb']){
			$message = html::error_message("Passwords do not match.");
		}
		
		if($message == ""){
			$user = new user($ldap);
			$result = $user->create($_POST['new_username'],$_POST['new_firstname'],$_POST['new_lastname'],$_POST['new_passworda']);
		
			if($result['RESULT'] == true){
				header("Location: user.php?uid=".$result['uid']);
			} else if ($result['RESULT'] == false) {
				$message = $result['MESSAGE'];
			}
		}
	} else if (isset($_POST['cancel_user'])) {
		header('location: index.php');
		unset($_POST);
	}
?>

<form class="form-horizontal" method="post" action="<?php echo $_SERVER['PHP_SELF'];?>" name="form">
	<fieldset>
		<legend>Add User</legend>
		<div class="row">
			<div class="col-sm-6">
				<div class="form-group">
					<label class="col-sm-4 control-label" for="username-input">Username:</label>
					<div class="col-sm-8">
						<input class="form-control" type="text" name="new_username" id="username_input" value="<?php if (isset($_POST['new_username'])){echo $_POST['new_username'];}?>" oninput="[username_errors,password_errors]=add_user_errors(null,password_errors);" autofocus />
					</div>
				</div>
				<div class="form-group">
					<label class="col-sm-4 control-label" for="name-input">First Name:</label>
					<div class="col-sm-8">
						<input class="form-control" type="text" name="new_firstname" id="name-input" value="<?php if (isset($_POST['new_firstname'])){echo $_POST['new_firstname'];}?>" />
					</div>
				</div>
				<div class="form-group">
					<label class="col-sm-4 control-label" for="lastname-input">Last Name:</label>
					<div class="col-sm-8">
						<input class="form-control" type="text" name="new_lastname" id="lastname-input" value="<?php if(isset($_POST['new_lastname'])){echo $_POST['new_lastname'];}?>" />
					</div>
				</div>
				<div class="form-group">
					<label class="col-sm-4 control-label" for="passworda-input">Password:</label>
					<div class="col-sm-8">
						<input class="form-control" type="password" name="new_passworda" id="passworda_input" value="<?php if (isset($_POST['new_passworda'])){echo $_POST['new_passworda'];}?>" oninput="[username_errors,password_errors]=add_user_errors(username_errors,null);" />
					</div>
				</div>
				<div class="form-group">
					<label class="col-sm-4 control-label" for="passwordb-input">Confirm Password:</label>
					<div class="col-sm-8">
						<input class="form-control" type="password" name="new_passwordb" id="passwordb_input" value="<?php if (isset($_POST['new_passwordb'])){echo $_POST['new_passwordb'];}?>" oninput="[username_errors,password_errors]=add_user_errors(username_errors,null);" />
					</div>
				</div>
				<div class="form-group">
					<div class="col-sm-8 col-sm-offset-4">
						<div class="btn-group">
							<input class="btn btn-success" type="submit" name="add_user" value="Add user" id="add_user_submit" /> <input class="btn btn-default" type="submit" name="cancel_user" value="Cancel" />
						</div>
					</div>
				</div>
			</div>
			<div class="col-sm-6">
				<p id="usernameerror1" class="text-success"><span class="glyphicon glyphicon-ok"></span> <span class="text">Username not in use</span></p>
				<p id="usernamewarning1" class="text-warning"><span class="glyphicon glyphicon-alert"></span> <span class="text">Username does not match a UIUC netid</span></p>
				<p id="usernameerror2" class="text-danger"><span class="glyphicon glyphicon-remove"></span> <span class="text">Username must begin with a lowercase letter</span></p>
				<p id="usernameerror3" class="text-danger"><span class="glyphicon glyphicon-remove"></span> Username must be alphanumeric (letters, numbers, underscore)</p>
				<p id="passworderror1" class="text-danger"><span class="glyphicon glyphicon-remove"></span> Password must be between 8 and 15 characters in length</p>
				<p id="passworderror2" class="text-danger"><span class="glyphicon glyphicon-remove"></span> Password must contain at least 1 uppercase letter</p>
				<p id="passworderror3" class="text-danger"><span class="glyphicon glyphicon-remove"></span> Password must contain at least 1 lowercase letter</p>
				<p id="passworderror4" class="text-danger"><span class="glyphicon glyphicon-remove"></span> Password must contain at least 1 number or special character (no spaces)</p>
				<p id="passworderror5" class="text-danger"><span class="glyphicon glyphicon-remove"></span> Password and confirm password must match</p>
				<p><button class="btn btn-default" id="password-button" type="button">Generate Password</button> <span id="password-text"></span></p>
			</div>
		</div>
	</fieldset>
</form>
	
<script type="text/javascript">
	username_errors = null;
	password_errors = null;
	$(document).ready(function(){
		[username_errors,password_errors]=add_user_errors();
		$('#password-button').on('click',function(){generate_password();[username_errors,password_errors]=add_user_errors();});
	});
</script>
<?php
	if(isset($message))echo $message;
	require_once 'includes/footer.inc.php';
?>