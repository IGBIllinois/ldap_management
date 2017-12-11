<?php
	$title = "Add Domain Computer";
	$sitearea = "domain";
	require_once 'includes/header.inc.php';

	$message="";
	if (isset($_POST['add_computer'])) {
		foreach($_POST as $var){
			$var = trim(rtrim($var));
		}
		
		if($message == ""){
			$computer = new computer($ldap);
			$result = $computer->create($_POST['new_name']);
		
			if($result['RESULT'] == true){
				header("Location: list_computers.php");
			} else if ($result['RESULT'] == false) {
				$message = $result['MESSAGE'];
			}
		}
	} else if (isset($_POST['cancel_computer'])) {
		header('location: index.php');
		unset($_POST);
	}
?>
<form class="mt-4" method="post" action="<?php echo $_SERVER['PHP_SELF'];?>" name="form">
	<fieldset>
		<legend>Add Domain Computer</legend>
		<hr>
		<div class="form-group row">
			<label class="col-sm-3 col-form-label" for="name-input">Name:</label>
			<div class="col-sm-6">
				<input class="form-control" type="text" name="new_name" id="name_input" value="<?php if (isset($_POST['new_name'])){echo $_POST['new_name'];}?>" autofocus />
			</div>
		</div>
		<div class="form-group row">
			<div class="col-sm-6 offset-sm-3">
				<div class="btn-group">
					<input class="btn btn-success" type="submit" name="add_computer" value="Add computer" /> <input class="btn btn-light" type="submit" name="cancel_computer" value="Cancel" />
				</div>
			</div>
		</div>
	</fieldset>
</form>

<?php
	if(isset($message))echo $message;
	require_once 'includes/footer.inc.php';
?>