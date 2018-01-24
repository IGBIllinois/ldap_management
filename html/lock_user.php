<?php
	$title = "Remove User";
	$sitearea = "users";
	require_once 'includes/header.inc.php';
	
	$message="";
	if (isset($_POST['lock_user']) && isset($_POST['username'])) {
		foreach($_POST as $var){
			$var = trim(rtrim($var));
		}
		
		if($_POST['username'] == ""){
			$message .= "Username cannot be blank. Please stop trying to break my web interface.";
		}
		
		if($message == ""){
			$user = new user($ldap,$_POST['username']);
			$result = $user->lock();
		
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
	}
?>
<div class="minijumbo"><div class="container">Lock User
	<?php if($uid != ""){ ?>
	<nav><ol class="breadcrumb"><li class="breadcrumb-item"><a href="<?php echo html::get_list_users_url_from_cookies(); ?>">Users</a></li><li class="breadcrumb-item"><a href="user.php?uid=<?php echo $uid; ?>"><?php echo $uid; ?></a></li><li class="breadcrumb-item active">Lock</li></ol></nav>
	<?php } ?>
</div></div>
<div class="container">
<form class="mt-4" method="post" action="<?php echo $_SERVER['PHP_SELF'];?>" name="form">
	<fieldset>
		<div class="form-group row">
			<label class="col-sm-3 col-form-label" for="username-input">Username:</label>
			<div class="col-sm-5">
				<input type="hidden" name="username" value="<?php echo $uid; ?>" autofocus /><label class="col-form-label"><?php echo $uid; ?></label>
			</div>
		</div>
		<div class="form-group row">
			<div class="col-sm-3"></div>
			<div class="col-sm-5">
				Are you sure you want to lock this user? The user will not be able to log in or change their password until their account is unlocked.
			</div>
		</div>
		<div class="form-group row">
			<div class="col-sm-5 offset-sm-3">
				<div class="btn-group">
					<input class="btn btn-danger" type="submit" name="lock_user" value="Lock user" /> <input class="btn btn-light" type="submit" name="cancel_user" value="Cancel" />
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