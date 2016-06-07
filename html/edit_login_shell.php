<?php
	$title = "Set Login Shell";
	require_once 'includes/header.inc.php';
	
	$message="";
	if (isset($_POST['change_shell'])) {
		foreach($_POST as $var){
			$var = trim(rtrim($var));
		}
		
		if ($_POST['username']=="" || !user::is_ldap_user($ldap,$_POST['username'])) {
			$message .= html::error_message("Invalid username. Please stop trying to break my web interface.");
		}
		$shells = explode(",", __SHELLS__);
		if($_POST['shell']==""){
			$message .= html::error_message("Please select a shell.");
		} elseif (!in_array($_POST['shell'], $shells)) {
			$message .= html::error_message("Invalid login shell. Please stop trying to break my web interface.");
		}
		
		if($message == ""){
			$user = new user($ldap,$_POST['username']);
			$result = $user->set_loginShell($_POST['shell']);
		
			if($result['RESULT'] == true){
				header("Location: user.php?uid=".$result['uid']);
			} else if ($result['RESULT'] == false) {
				$message = html::error_message($result['MESSAGE']);
			}
		}
	} else if (isset($_POST['cancel_user'])) {
		header('location: user.php?uid='.$_POST['username']);
		unset($_POST);
		exit;
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
	
	$usershtml = "";
	$user = new user($ldap,$uid);
	$usershtml .= "<input type='hidden' name='username' value='$uid'/><label class='control-label'>$uid</label>";
	
	$shellshtml = "";
	$shells = explode(",", __SHELLS__);
	
	$shell = "";
	$shellshtml .= "<select name='shell' class='form-control shell-select'><option></option>";
	foreach($shells as $sh){
		$shellshtml .= "<option value='".$sh."'";
		if($sh == $user->get_loginShell()){
			$shellshtml .= " selected";
		}
		$shellshtml .= ">".$sh."</option>";
	}
	$shellshtml .= "</select>";
?>
<form class="form-horizontal" method="post" action="<?php echo $_SERVER['PHP_SELF'];?>" name="form">
	<fieldset>
		<legend>Change Login Shell</legend>
		<div class="form-group">
			<label class="col-sm-2 control-label" for="username-input">Username:</label>
			<div class="col-sm-4">
				<?php echo $usershtml; ?>
			</div>
		</div>
		<div class="form-group">
			<label class="col-sm-2 control-label" for="group-input">Shell:</label>
			<div class="col-sm-4">
				<?php echo $shellshtml; ?>
			</div>
		</div>
		<div class="form-group">
			<div class="col-sm-4 col-sm-offset-2">
				<div class="btn-group">
					<input class="btn btn-info" type="submit" name="change_shell" value="Change Shell" /> <input class="btn btn-default" type="submit" name="cancel_user" value="Cancel" />
				</div>
			</div>
		</div>
	</fieldset>
</form>
<script type="text/javascript">
	$(document).ready(function(){
		$(".shell-select").select2({
			placeholder: "Please select a shell"
		});
	});
</script>
<?php
	if(isset($message))echo $message;
	require_once 'includes/footer.inc.php';
?>