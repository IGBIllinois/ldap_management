<?php
	$title = "Add Host";
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
<form class="form-horizontal" method="post" action="<?php echo $_SERVER['PHP_SELF'];?>" name="form">
	<fieldset>
		<legend>Add Host</legend>
		<div class="row">
			<div class="col-sm-6">
				<div class="form-group">
					<label class="col-sm-4 control-label" for="name-input">Hostname:</label>
					<div class="col-sm-8">
						<input class="form-control" type="text" name="new_name" id="name_input" value="<?php if (isset($_POST['new_name'])){echo $_POST['new_name'];}?>" oninput="change_group_errors();" autofocus />
					</div>
				</div>
				<div class="form-group">
					<label class="col-sm-4 control-label" for="ip-input">IP:</label>
					<div class="col-sm-8">
						<input class="form-control" type="text" name="new_ip" id="ip_input" value="<?php if (isset($_POST['new_ip'])){echo $_POST['new_ip'];}?>" placeholder="Leave blank to set automatically" autofocus />
					</div>
				</div>
				<div class="form-group">
					<div class="col-sm-8 col-sm-offset-4">
						<div class="btn-group">
							<input class="btn btn-success" type="submit" name="add_host" id="host_submit" value="Add host" /> <input class="btn btn-default" type="submit" name="cancel_host" value="Cancel" />
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