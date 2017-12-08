<?php
	$title = "Add Classroom Users";
	$sitearea = "classroom";
	require_once 'includes/header.inc.php';
	
	$message="";
	$show_users = false;
	if (isset($_POST['classroom_users'])) {
		foreach($_POST as $var){
			$var = trim(rtrim($var));
		}
		if(!isset($_POST['new_prefix'])){
			$message = html::error_message("Please enter a username prefix.");
		}
		if(!isset($_POST['new_start']) || !is_numeric($_POST['new_start']) || $_POST['new_start']<1){
			$message = html::error_message("Please enter a valid start number.");
		}
		if(!isset($_POST['new_end']) || !is_numeric($_POST['new_end']) || $_POST['new_end']<1){
			$message = html::error_message("Please enter a valid end number.");
		}
				
		if($message == ""){
			$_POST['new_start'] = intval($_POST['new_start']);
			$_POST['new_end'] = intval($_POST['new_end']);
			$passwords = array();
			$padlength = 2;
			for($i=$_POST['new_start']; $i<=$_POST['new_end']; $i++){
				$paddednum = str_pad($i,$padlength,"0",STR_PAD_LEFT);
				$username = $_POST['new_prefix'].$paddednum;
				$user = new user($ldap,$username);
				
				if(user::is_ldap_user($ldap,$username)){
					// Create user with random password
					$user->remove();
				}
			}
			
			$paddedstart = str_pad($_POST['new_start'],$padlength,"0",STR_PAD_LEFT);
			$paddedend = str_pad($_POST['new_end'],$padlength,"0",STR_PAD_LEFT);
			$message = html::success_message("Users ".$_POST['new_prefix']."$paddedstart-".$_POST['new_prefix']."$paddedend removed");
			unset($_POST);
		}
	} else if (isset($_POST['cancel_user'])) {
		header('location: index.php');
		unset($_POST);
	}

?>

<form class="form-horizontal" method="post" action="<?php echo $_SERVER['PHP_SELF'];?>" name="form">
	<fieldset>
		<legend>Remove Classroom Users</legend>
		<div class="row">
			<div class="col-sm-6">
				<div class="form-group">
					<label class="col-sm-4 control-label" for="prefix-input">Prefix:</label>
					<div class="col-sm-8">
						<input class="form-control" type="text" name="new_prefix" id="prefix-input" value="<?php if (isset($_POST['new_prefix'])){echo $_POST['new_prefix'];}?>" oninput="show_remove_classroom_text()" autofocus />
					</div>
				</div>
				<div class="form-group">
					<label class="col-sm-4 control-label" for="start-input">Range start:</label>
					<div class="col-sm-8">
						<input class="form-control" type="text" name="new_start" id="start-input" value="<?php if (isset($_POST['new_start'])){echo $_POST['new_start'];}?>" oninput="show_remove_classroom_text()" />
					</div>
				</div>
				<div class="form-group">
					<label class="col-sm-4 control-label" for="end-input">Range end:</label>
					<div class="col-sm-8">
						<input class="form-control" type="text" name="new_end" id="end-input" value="<?php if (isset($_POST['new_end'])){echo $_POST['new_end'];}?>" oninput="show_remove_classroom_text()" />
					</div>
				</div>
				<div class="form-group">
					<div class="col-sm-8 col-sm-offset-4">
						<div class="btn-group">
							<input class="btn btn-danger" type="submit" name="classroom_users" value="Remove classroom users" id="remove_user_submit" onclick="return confirm('Are you sure? This operation cannot be undone.');" /> <input class="btn btn-default" type="submit" name="cancel_user" value="Cancel" />
						</div>
					</div>
				</div>
			</div>
			<div class="col-sm-6">
				<p id="usernameerror1" class="text-success"><span class="fa fa-check"></span> <span class="text">Users do not exist</span></p>
			</div>
		</div>
	</fieldset>
</form>
	
<script type="text/javascript">
	$(document).ready(function(){show_remove_classroom_text();});
</script>
<?php
		if(isset($message))echo $message;
	
	require_once 'includes/footer.inc.php';
?>