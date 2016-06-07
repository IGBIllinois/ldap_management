<?php
	$title = "Set Username";
	require_once 'includes/header.inc.php';
	
	$message="";
	if (isset($_POST['change_username'])) {
		foreach($_POST as $var){
			$var = trim(rtrim($var));
		}
		
		if($_POST['new_username'] == ""){
			$message .= html::error_message("First name cannot be blank.");
		}
		
		if($message == ""){
			$user = new user($ldap,$_POST['username']);
			$result = $user->set_username($_POST['new_username']);
		
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
		<legend>Change Username</legend>
		<div class="row">
			<div class="col-sm-6">
				<div class="form-group">
					<label class="col-xs-4 control-label">Username:</label>
					<div class="col-xs-8">
						<input type="hidden" name="username" value="<?php echo $uid; ?>" /><label class="control-label"><?php echo $uid; ?></label>
					</div>
				</div>
				<div class="form-group">
					<label class="col-sm-4 control-label" for="username-input">New Username:</label>
					<div class="col-sm-8">
						<input class="form-control" type="text" name="new_username" id="username_input" value="<?php if (isset($_POST['new_username'])){echo $_POST['new_username'];}?>" oninput="change_username_errors();" autofocus />
					</div>
				</div>
				<div class="form-group">
					<div class="col-sm-8 col-sm-offset-4">
						<p>File-server and mail directories will be changed automatically. Directories the user owns on other servers will need to be renamed manually.</p>	
					</div>
				</div>
				<div class="form-group">
					<div class="col-sm-8 col-sm-offset-4">
						<div class="btn-group">
							<input class="btn btn-info" type="submit" name="change_username" value="Change username" id="change_username_submit" /> <input class="btn btn-default" type="submit" name="cancel_user" value="Cancel" />
						</div>
					</div>
				</div>
			</div>
			<div class="col-sm-6">
				<p id="usernameerror1" class="text-success"><span class="glyphicon glyphicon-ok"></span> <span class="text">Username not in use</span></p>
				<p id="usernamewarning1" class="text-warning"><span class="glyphicon glyphicon-alert"></span> <span class="text">Username does not match a UIUC netid</span></p>
				<p id="usernameerror2" class="text-danger"><span class="glyphicon glyphicon-remove"></span> <span class="text">Username must begin with a lowercase letter</span></p>
				<p id="usernameerror3" class="text-danger"><span class="glyphicon glyphicon-remove"></span> Username must be alphanumeric (letters, numbers, underscore)</p>
			</div>
		</div>
	</fieldset>
</form>
<script type="text/javascript">
	$(document).ready(function(){change_username_errors();});
</script>
<?php
	if(isset($message))echo $message;
	require_once 'includes/footer.inc.php';
?>