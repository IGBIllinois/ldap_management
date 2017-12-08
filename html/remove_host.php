<?php
	$title = "Remove Host";
	$sitearea = "hosts";
	require_once 'includes/header.inc.php';
	
	$message="";
	if (isset($_POST['remove_host']) && isset($_POST['host'])) {
		foreach($_POST as $var){
			$var = trim(rtrim($var));
		}
		
		if($_POST['host'] == ""){
			$message .= "Hostname cannot be blank. Please stop trying to break my web interface.";
		}
		
		if($message == ""){
			$host = new host($ldap,$_POST['host']);
			$result = $host->remove();
		
			if($result['RESULT'] == true){
				header("Location: list_hosts.php");
			} else if ($result['RESULT'] == false) {
				$message = $result['MESSAGE'];
			}
		}
	} else if (isset($_POST['cancel_host'])) {
		header('location: host.php?hid='.$_POST['host']);
		unset($_POST);
		exit();
	}
	
	$hid = "";
	if(isset($_GET['hid'])){
		$hid = $_GET['hid'];
	} else if (isset($_POST['host'])){
		$hid = $_POST['host'];
	}
	if($hid == ""){
		header('location: index.php');
	}
?>
<form class="form-horizontal" method="post" action="<?php echo $_SERVER['PHP_SELF'];?>" name="form">
	<fieldset>
		<legend>Remove host</legend>
		<div class="form-group">
			<label class="col-sm-2 control-label">Hostname:</label>
			<div class="col-sm-4">
				<input type="hidden" name="host" value="<?php echo $hid; ?>" autofocus /><label class="control-label"><?php echo $hid; ?></label>
			</div>
		</div>
		<div class="form-group">
			<div class="col-sm-2"></div>
			<div class="col-sm-4">
				Are you sure you want to remove this host? Access to this host will be revoked for all users. This operation cannot be undone.
			</div>
		</div>
		<div class="form-group">
			<div class="col-sm-4 col-sm-offset-2">
				<div class="btn-group">
					<input class="btn btn-danger" type="submit" name="remove_host" value="Remove host" /> <input class="btn btn-default" type="submit" name="cancel_host" value="Cancel" />
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
