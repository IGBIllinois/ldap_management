<?php
	$title = "Set Expiration";
	$sitearea = "users";
	require_once 'includes/header.inc.php';

	$message="";
	if (isset($_POST['expire_user'])) {
		$_POST = array_map("trim",$_POST);
		
		if($_POST['username']==""){
			$message .= html::error_message("Please select a user.");
		} elseif (!user::is_ldap_user($ldap,$_POST['username'])) {
			$message .= html::error_message("Invalid username. Please stop trying to break my web interface.");
		}
		if($_POST['expiration']==""){
			$message .= html::error_message("Please enter an expiration date.");
		} elseif (!strtotime($_POST['expiration'])) {
			$message .= html::error_message("Invalid date. Please stop trying to break my web interface.");
		}
		
		if($message == ""){
			$user = new user($ldap,$_POST['username']);
			$result = $user->set_expiration(strtotime($_POST['expiration']));
		
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
		$usershtml = "<input type='hidden' name='username' value='$uid'/><label class='col-form-label'>$uid</label>";
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
	$searchdescription = html::get_list_users_description_from_cookies();
	
?>
<div class="minijumbo"><div class="container">Set Expiration
	<?php if($uid != ""){ ?>
	<nav><ol class="breadcrumb"><li class="breadcrumb-item"><a href="<?php echo html::get_list_users_url_from_cookies(); ?>">Users<?php if($searchdescription!=""){echo " ($searchdescription)";} ?></a></li><li class="breadcrumb-item"><a href="user.php?uid=<?php echo $uid; ?>"><?php echo $uid; ?></a></li><li class="breadcrumb-item active">Set Expiration</li></ol></nav>
	<?php } ?>
</div></div>
<div class="container">
<form class="mt-4" method="post" action="<?php echo $_SERVER['PHP_SELF'];?>" name="form">
	<fieldset>
		<div class="form-group row">
			<label class="col-sm-3 col-form-label" for="username-input">Username:</label>
			<div class="col-sm-5">
				<?php echo $usershtml; ?>
			</div>
		</div>
		<div class="form-group row">
			<label class="col-sm-3 col-form-label" for="exp-input">Expiration Date:</label>
			<div class="col-sm-5">
				<input class="form-control" id="exp-input" name="expiration" value="<?php echo date('m/d/Y',strtotime("+6 months")); ?>" placeholder="MM/DD/YYYY"/>
			</div>
		</div>
		<div class="form-group row">
			<div class="col-sm-5 offset-sm-3">
				<div class="btn-group">
					<input class="btn btn-warning" type="submit" name="expire_user" value="Expire user" /> <input class="btn btn-light" type="submit" name="cancel_user" value="Cancel" />
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
		$(".group-select").select2({
			placeholder: "Please select a group",
			width: 'element'
		});
	});
</script>
<?php
	if(isset($message))echo $message;
	require_once 'includes/footer.inc.php';
?>