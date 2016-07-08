<?php
	$title = "Add Classroom Users";
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
		if(!isset($_POST['new_num']) || !is_numeric($_POST['new_num'])){
			$message = html::error_message("Please enter a valid number.");
		}
		
		if($message == ""){
			$_POST['new_num'] = intval($_POST['new_num']);
			$passwords = array();
			$padlength = 2;
			$classroom_queue = new group($ldap,'classroom_queue');
			$show_users = true;
			$users_html = "<pre>";
			for($i=1; $i<=$_POST['new_num']; $i++){
				$paddednum = str_pad($i,$padlength,"0",STR_PAD_LEFT);
				$username = $_POST['new_prefix'].$paddednum;
				$users_html .= $username." ";
				$password = user::random_password();
				$users_html .= $password."\n";
				$user = new user($ldap,$username);
				
				if(user::is_ldap_user($ldap,$username)){
					// clear out the biocluster/file-server home folder, if it exists
					if(__RUN_SHELL_SCRIPTS__){
						$safeusername = escapeshellarg($username);
						exec("sudo ../bin/classroom_cleanup.pl $safeusername 2>&1",$output);
						log::log_message("Cleaned up file-server and biocluster directories for $username");
					}
					// Set the password
					$user->set_password($password);
				} else {
					// Create user with random password
					$user->create($username,$username,$username,$password);
				}
				// Give user biocluster access
				$user->give_biocluster_access();

				// Add user to classroom queue
				$classroom_queue->add_user($username);
			}
			$users_html .= "</pre>";

			$subject = "IGB Classroom Users ".$_POST['new_prefix'].'01-'.$_POST['new_prefix'].str_pad($_POST['new_num'],$padlength,"0",STR_PAD_LEFT);
			$to = $login_user->get_email();
			$emailmessage = "<p>The following classroom users have been added.</p>";
			$emailmessage .= $users_html;
	
			$headers = "From: " . __ADMIN_EMAIL__ . "\r\n";
			$headers .= "Content-Type: text/html; charset=iso-8859-1" . "\r\n";
			mail($to,$subject,$emailmessage,$headers," -f " . __ADMIN_EMAIL__);
		}
	} else if (isset($_POST['cancel_user'])) {
		header('location: index.php');
		unset($_POST);
	}

	if($show_users){
		echo "<legend>Add Classroom Users</legend><p>The following classroom users have been added. This list has also been emailed to you for your records.";
		echo $users_html;
		echo $message;
?>

<?php } else { ?>

<form class="form-horizontal" method="post" action="<?php echo $_SERVER['PHP_SELF'];?>" name="form">
	<fieldset>
		<legend>Add Classroom Users</legend>
		<div class="row">
			<div class="col-sm-6">
				<div class="form-group">
					<label class="col-sm-4 control-label" for="prefix-input">Prefix:</label>
					<div class="col-sm-8">
						<input class="form-control" type="text" name="new_prefix" id="prefix-input" value="<?php if (isset($_POST['new_prefix'])){echo $_POST['new_prefix'];}?>" oninput="show_add_classroom_text()" autofocus />
					</div>
				</div>
				<div class="form-group">
					<label class="col-sm-4 control-label" for="num-input">Number of users:</label>
					<div class="col-sm-8">
						<input class="form-control" type="text" name="new_num" id="num-input" value="<?php if (isset($_POST['new_num'])){echo $_POST['new_num'];}?>" oninput="show_add_classroom_text()" />
					</div>
				</div>
				<div class="form-group">
					<div class="col-sm-8 col-sm-offset-4">
						<div class="btn-group">
							<input class="btn btn-success" type="submit" name="classroom_users" value="Add classroom users" id="add_user_submit" /> <input class="btn btn-default" type="submit" name="cancel_user" value="Cancel" />
						</div>
					</div>
				</div>
			</div>
			<div class="col-sm-6">
				<p id="classroom-txt"></p>
			</div>
		</div>
	</fieldset>
</form>
	
<script type="text/javascript">
	$(document).ready(function(){show_add_classroom_text();});
</script>
<?php
		if(isset($message))echo $message;
	}
	require_once 'includes/footer.inc.php';
?>