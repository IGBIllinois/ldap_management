<?php
	$title = "Set Forwarding Email";
	require_once 'includes/header.inc.php';
	
	$message="";
	if (isset($_POST['change_emailforward'])) {
		foreach($_POST as $var){
			$var = trim(rtrim($var));
		}
		
		if ($_POST['username']=="" || !user::is_ldap_user($ldap,$_POST['username'])) {
			$message .= html::error_message("Invalid username. Please stop trying to break my web interface.");
		}
		$shells = explode(",", __SHELLS__);
		if( !($_POST['emailforward']=="" || preg_match('/^[a-zA-Z0-9\\._-]+@[a-zA-Z0-9\\._-]+\\.[a-zA-Z0-9\\._-]+$/u', $_POST['emailforward'])) ){
			$message .= html::error_message("Please enter a valid email address.");
		}
		
		if($message == ""){
			$user = new user($ldap,$_POST['username']);
			if($_POST['emailforward']==""){
				$result = $user->remove_emailforward();
			} else {
				$result = $user->set_emailforward($_POST['emailforward']);
			}
		
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
?>
<form class="form-horizontal" method="post" action="<?php echo $_SERVER['PHP_SELF'];?>" name="form">
	<fieldset>
		<legend>Change Forwarding Email</legend>
		<div class="row">
			<div class="col-sm-6">
				<div class="form-group">
					<label class="col-sm-4 control-label" for="username-input">Username:</label>
					<div class="col-sm-8">
						<?php echo $usershtml; ?>
					</div>
				</div>
				<div class="form-group">
					<label class="col-sm-4 control-label" for="group-input">Email:</label>
					<div class="col-sm-8">
						<input class="form-control" name="emailforward" id="emailforward_input" value="" oninput="change_emailforward_errors();"/>
					</div>
				</div>
				<div class="form-group">
					<div class="col-sm-8 col-sm-offset-4">
						<div class="btn-group">
							<input class="btn btn-info" type="submit" name="change_emailforward" id="change_emailforward_submit" value="Change Forwarding Email" /> <input class="btn btn-default" type="submit" name="cancel_user" value="Cancel" />
						</div>
					</div>
				</div>
			</div>
			<div class="col-sm-6">
				<p id="emailerror1" class="text-succes"><span class="glyphicon glyphicon-ok"></span> Valid forwarding email</p>
			</div>
		</div>
	</fieldset>
</form>

<script type="text/javascript">
	$(document).ready(function(){
		change_emailforward_errors();
	});
</script>
<?php
	if(isset($message))echo $message;
	require_once 'includes/footer.inc.php';
?>