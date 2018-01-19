<?php
	$title = "Remove Domain Computer";
	$sitearea = "domain";
	require_once 'includes/header.inc.php';
	
	$message="";
	if (isset($_POST['remove_computer']) && isset($_POST['computer'])) {
		foreach($_POST as $var){
			$var = trim(rtrim($var));
		}
		
		if($_POST['computer'] == ""){
			$message .= "Computer cannot be blank. Please stop trying to break my web interface.";
		}
		
		if($message == ""){
			$computer = new computer($ldap,$_POST['computer']);
			$result = $computer->remove();
		
			if($result['RESULT'] == true){
				header("Location: list_computers.php");
			} else if ($result['RESULT'] == false) {
				$message = $result['MESSAGE'];
			}
		}
	} else if (isset($_POST['cancel_computer'])) {
		header('location: computer.php?uid='.$_POST['computer']);
		unset($_POST);
		exit();
	}
	
	$uid = "";
	if(isset($_GET['uid'])){
		$uid = $_GET['uid'];
	} else if (isset($_POST['computer'])){
		$uid = $_POST['computer'];
	}
	if($uid == ""){
		header('location: index.php');
	}
?>
<div class="minijumbo"><div class="container">Remove Domain Computer</div></div>
<div class="container">
<form class="mt-4" method="post" action="<?php echo $_SERVER['PHP_SELF'];?>" name="form">
	<fieldset>
		<div class="form-group row">
			<label class="col-sm-3 col-form-label">Computer:</label>
			<div class="col-sm-5">
				<input type="hidden" name="computer" value="<?php echo $uid; ?>" autofocus /><label class="col-form-label"><?php echo $uid; ?></label>
			</div>
		</div>
		<div class="form-group row">
			<div class="col-sm-3"></div>
			<div class="col-sm-5">
				Are you sure you want to remove this computer? This operation cannot be undone.
			</div>
		</div>
		<div class="form-group row">
			<div class="col-sm-5 offset-sm-3">
				<div class="btn-group">
					<input class="btn btn-danger" type="submit" name="remove_computer" value="Remove computer" /> <input class="btn btn-light" type="submit" name="cancel_computer" value="Cancel" />
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
