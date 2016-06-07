<?php
	$title = "Set Hostname";
	require_once 'includes/header.inc.php';
	
	$message="";
	if (isset($_POST['change_name'])) {
		foreach($_POST as $var){
			$var = trim(rtrim($var));
		}
		
		if($_POST['new_name'] == ""){
			$message .= html::error_message("Hostname cannot be blank.");
		}
		
		if($message == ""){
			$host = new host($ldap,$_POST['name']);
			$result = $host->set_name($_POST['new_name']);
		
			if($result['RESULT'] == true){
				header("Location: host.php?hid=".$result['hid']);
			} else if ($result['RESULT'] == false) {
				$message = $result['MESSAGE'];
			}
		}
	} else if (isset($_POST['cancel_host'])) {
		header('location: host.php?hid='.$_POST['name']);
		unset($_POST);
		exit();
	}
	
	$hid = "";
	if(isset($_GET['hid'])){
		$hid = $_GET['hid'];
	} else if (isset($_POST['name'])){
		$hid = $_POST['name'];
	}
	if($hid == ""){
		header('location: index.php');
	}
?>
<form class="form-horizontal" method="post" action="<?php echo $_SERVER['PHP_SELF'];?>" name="form">
	<fieldset>
		<legend>Change Hostname</legend>
		<div class="row">
			<div class="col-sm-6">
				<div class="form-group">
					<label class="col-xs-4 control-label" for="name-input">Host:</label>
					<div class="col-xs-8">
						<input type="hidden" name="name" value="<?php echo $hid; ?>" /><label class="control-label"><?php echo $hid; ?></label>
					</div>
				</div>
				<div class="form-group">
					<label class="col-sm-4 control-label" for="name-input">New Hostname:</label>
					<div class="col-sm-8">
						<input class="form-control" type="text" name="new_name" id="name_input" value="<?php if (isset($_POST['new_name'])){echo $_POST['new_name'];}?>" autofocus />
					</div>
				</div>
				<div class="form-group">
					<div class="col-sm-8 col-sm-offset-4">
						<div class="btn-group">
							<input class="btn btn-info" type="submit" name="change_name" value="Change name" id="host_submit" /> <input class="btn btn-default" type="submit" name="cancel_host" value="Cancel" />
						</div>
					</div>
				</div>
			</div>
			<div class="col-sm-6">
				
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