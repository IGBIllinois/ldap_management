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
		if(!isset($_POST['new_start']) || !is_numeric($_POST['new_start'])){
			$message = html::error_message("Please enter a valid start.");
		}
		if(!isset($_POST['new_end']) || !is_numeric($_POST['new_end'])){
			$message = html::error_message("Please enter a valid end.");
		}
		
		if($message == ""){
			$_POST['new_start'] = intval($_POST['new_start']);
			$_POST['new_end'] = intval($_POST['new_end']);
			$passwords = array();
			$padlength = 2;
			$classroom_queue = new group($ldap,'classroom_queue');
			$show_users = true;
			$users_html = "<pre>";
			for($i=$_POST['new_start']; $i<=$_POST['new_end']; $i++){
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
					// Run script to add user to file-server
					if(__RUN_SHELL_SCRIPTS__){
						$safeusername = escapeshellarg($username);
						exec("sudo ../bin/add_user.pl $safeusername --classroom",$shellout);
					}
				}
				// Give user biocluster access
				$user->set_loginShell('/usr/local/bin/system-specific');
				$user->add_machinerights('biocluster2.igb.illinois.edu');

				// Add user to classroom queue
				$classroom_queue->add_user($username);
			}
			$users_html .= "</pre>";

			$subject = "IGB Classroom Users ".$_POST['new_prefix'].str_pad($_POST['new_start'],$padlength,"0",STR_PAD_LEFT).'-'.$_POST['new_prefix'].str_pad($_POST['new_end'],$padlength,"0",STR_PAD_LEFT);
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

<form class="mt-4" method="post" action="<?php echo $_SERVER['PHP_SELF'];?>" name="form">
	<fieldset>
		<legend>Add Classroom Users</legend>
		<hr>
		<div class="row">
			<div class="col-sm-8">
				<div class="form-group row">
					<label class="col-sm-4 col-form-label" for="prefix-input">Prefix</label>
					<div class="col-sm-8">
						<input class="form-control" type="text" name="new_prefix" id="prefix-input" value="<?php if (isset($_POST['new_prefix'])){echo $_POST['new_prefix'];}?>" oninput="show_add_classroom_text()" autofocus />
					</div>
				</div>
				<div class="form-group row">
					<label class="col-sm-4 col-form-label" for="start-input">Range start</label>
					<div class="col-sm-8">
						<input class="form-control" type="text" name="new_start" id="start-input" value="<?php if (isset($_POST['new_start'])){echo $_POST['new_start'];} else { echo 1; }?>" oninput="show_add_classroom_text()" />
					</div>
				</div>
				<div class="form-group row">
					<label class="col-sm-4 col-form-label" for="end-input">Range end</label>
					<div class="col-sm-8">
						<input class="form-control" type="text" name="new_end" id="end-input" value="<?php if (isset($_POST['new_end'])){echo $_POST['new_end'];}?>" oninput="show_add_classroom_text()" />
					</div>
				</div>
				<div class="form-group row">
					<div class="col-sm-8 offset-sm-4">
						<div class="btn-group">
							<input class="btn btn-success" type="submit" name="classroom_users" value="Add classroom users" id="add_user_submit" /> <input class="btn btn-light" type="submit" name="cancel_user" value="Cancel" />
						</div>
					</div>
				</div>
			</div>
			<div class="col-sm-4">
				<p id="usernameerror1" class="text-success"><span class="fa fa-check"></span> <span class="text">Users do not exist</span></p>
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