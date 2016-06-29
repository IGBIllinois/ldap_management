<?php
	$title = "Add Biocluster Access";
	require_once 'includes/header.inc.php';
	
	$message="";
	if (isset($_POST['biocluster_user']) && isset($_POST['username'])) {
		foreach($_POST as $var){
			$var = trim(rtrim($var));
		}
		
		if($_POST['username'] == ""){
			$message .= "Username cannot be blank. Please stop trying to break my web interface.";
		}
		
		if($message == ""){
			$user = new user($ldap,$_POST['username']);
			$result = $user->give_biocluster_access();
		
			if($result['RESULT'] == true){
				header("Location: user.php?uid=".$_POST['username']);
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
		exit();
	}
?>
<form class="form-horizontal" method="post" action="<?php echo $_SERVER['PHP_SELF'];?>" name="form">
	<fieldset>
		<legend>Give Biocluster Access</legend>
		<div class="form-group">
			<label class="col-sm-2 control-label" for="username-input">Username:</label>
			<div class="col-sm-4">
				<input type="hidden" name="username" value="<?php echo $uid; ?>" autofocus /><label class="control-label"><?php echo $uid; ?></label>
			</div>
		</div>
		<div class="form-group">
			<div class="col-sm-2"></div>
			<div class="col-sm-4">
				When you click Give access, you will be taken to the Biocluster Accounting site to enter billing details.
			</div>
		</div>
		<div class="form-group">
			<div class="col-sm-4 col-sm-offset-2">
				<div class="btn-group">
					<input onclick="window.open('https://biocluster.igb.illinois.edu/accounting/add_user.php?new_username=<?php echo $uid; ?>','_blank');" class="btn btn-primary" type="submit" name="biocluster_user" value="Give access" /> <input class="btn btn-default" type="submit" name="cancel_user" value="Cancel" />
				</div>
			</div>
		</div>
	</fieldset>
</form>
<script type="text/javascript">

</script>
<?php
	if(isset($message))echo $message;
	require_once 'includes/footer.inc.php';
?>