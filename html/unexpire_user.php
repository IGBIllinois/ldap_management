<?php
	$title = "Cancel Expiration";
	require_once 'includes/header.inc.php';

	$message="";
	if (isset($_POST['unexpire_user'])) {
		foreach($_POST as $var){
			$var = trim(rtrim($var));
		}
		
		if($_POST['username']==""){
			$message .= html::error_message("Please select a user.");
		} elseif (!user::is_ldap_user($ldap,$_POST['username'])) {
			$message .= html::error_message("Invalid username. Please stop trying to break my web interface.");
		}
		
		if($message == ""){
			$user = new user($ldap,$_POST['username']);
			$result = $user->cancel_expiration();
		
			if($result['RESULT'] == true){
				header("Location: user.php?uid=".$result['uid']);
			} else if ($result['RESULT'] == false) {
				$message = $result['MESSAGE'];
			}
		}
	} else if (isset($_POST['cancel_user'])) {
		header("Location: user.php?uid=".$_POST['username']);
		unset($_POST);
	}
	
	$usershtml = "";
	$uid = "";
	if(isset($_GET['uid'])){
		$uid = $_GET['uid'];
	}
	if($uid != ""){
		$usershtml = "<input type='hidden' name='username' value='$uid'/><label class='control-label'>$uid</label>";
	} else {
		$users = user::get_all_users($ldap);
		$usershtml .= "<select name='username' class='form-control username-select'><option></option>";
		foreach($users as $user){
			$usershtml .= "<option value='".$user."'";
			if($uid == $user){
				$usershtml .= " selected";
			}
			$usershtml .= ">".$user."</option>";
		}
		$usershtml .= "</select>";
	}
	
	
?>
<form class="form-horizontal" method="post" action="<?php echo $_SERVER['PHP_SELF'];?>" name="form">
	<fieldset>
		<legend>Cancel Expiration</legend>
		<div class="form-group">
			<label class="col-sm-2 control-label" for="username-input">Username:</label>
			<div class="col-sm-4">
				<?php echo $usershtml; ?>
			</div>
		</div>
		<div class="form-group">
			<div class="col-sm-4 col-sm-offset-2">
				<div class="btn-group">
					<input class="btn btn-danger" type="submit" name="unexpire_user" value="Cancel Expiration" /> <input class="btn btn-default" type="submit" name="cancel_user" value="Cancel" />
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
	});
</script>
<?php
	if(isset($message))echo $message;
	require_once 'includes/footer.inc.php';
?>