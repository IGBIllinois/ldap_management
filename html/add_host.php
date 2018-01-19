<?php
	$title = "Add Host";
	$sitearea = "hosts";
	require_once 'includes/header.inc.php';

	$message="";
	if (isset($_POST['add_host'])) {
		foreach($_POST as $var){
			$var = trim(rtrim($var));
		}
		if($_POST['new_name']==""){
			$message = html::error_message("Hostname cannot be blank");
		}
		if($_POST['new_ip']==""){
			$_POST['new_ip'] = gethostbyname($_POST['new_name']);
		}
		
		if($message == ""){
			$host = new host($ldap);
			$result = $host->create($_POST['new_name'],$_POST['new_ip']);
		
			if($result['RESULT'] == true){
				header("Location: host.php?hid=".$result['hid']);
			} else if ($result['RESULT'] == false) {
				$message = $result['MESSAGE'];
			}
		}
	} else if (isset($_POST['cancel_host'])) {
		header('location: index.php');
		unset($_POST);
	}
?>
<div class="minijumbo"><div class="container">Add Host</div></div>
<div class="container">
<form class="mt-4" method="post" action="<?php echo $_SERVER['PHP_SELF'];?>" name="form">
	<fieldset>
		<div class="row">
			<div class="col-sm-8">
				<div class="form-group row">
					<label class="col-sm-4 col-form-label" for="name-input">Hostname:</label>
					<div class="col-sm-8">
						<input class="form-control" type="text" name="new_name" id="name_input" value="<?php if (isset($_POST['new_name'])){echo $_POST['new_name'];}?>" autofocus />
					</div>
				</div>
				<div class="form-group row">
					<label class="col-sm-4 col-form-label" for="ip-input">IP:</label>
					<div class="col-sm-8">
						<input class="form-control" type="text" name="new_ip" id="ip_input" value="<?php if (isset($_POST['new_ip'])){echo $_POST['new_ip'];}?>" placeholder="Leave blank to set automatically" autofocus />
					</div>
				</div>
				<div class="form-group row">
					<div class="col-sm-8 offset-sm-4">
						<div class="btn-group">
							<input class="btn btn-success" type="submit" name="add_host" id="host_submit" value="Add host" /> <input class="btn btn-light" type="submit" name="cancel_host" value="Cancel" />
						</div>
					</div>
				</div>
			</div>
			<div class="col-sm-4">
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