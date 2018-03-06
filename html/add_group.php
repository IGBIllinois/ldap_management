<?php
	$title = "Add Group";
	$sitearea = "groups";
	require_once 'includes/header.inc.php';

	$message="";
	if (isset($_POST['add_group'])) {
		$_POST = array_map("trim",$_POST);
		
		if($message == ""){
			$group = new group($ldap);
			$result = $group->create($_POST['new_name'],$_POST['new_description']);
		
			if($result['RESULT'] == true){
				header("Location: group.php?gid=".$result['gid']);
			} else if ($result['RESULT'] == false) {
				$message = $result['MESSAGE'];
			}
		}
	} else if (isset($_POST['cancel_group'])) {
		header('location: index.php');
		unset($_POST);
	}
?>
<div class="minijumbo"><div class="container">Add Group</div></div>
<div class="container">
<form class="mt-4" method="post" action="<?php echo $_SERVER['PHP_SELF'];?>" name="form">
	<fieldset>
		<div class="row">
			<div class="col-sm-8">
				<div class="form-group row">
					<label class="col-sm-4 col-form-label" for="name-input">Name:</label>
					<div class="col-sm-8">
						<input class="form-control" type="text" name="new_name" id="name_input" value="<?php if (isset($_POST['new_name'])){echo $_POST['new_name'];}?>" oninput="change_group_errors();" autofocus />
					</div>
				</div>
				<div class="form-group row">
					<label class="col-sm-4 col-form-label" for="description-input">Description:</label>
					<div class="col-sm-8">
						<input class="form-control" type="text" name="new_description" id="description_input" value="<?php if (isset($_POST['new_description'])){echo $_POST['new_description'];}?>" oninput="change_group_errors();" />
					</div>
				</div>
				<div class="form-group row">
					<div class="col-sm-8 offset-sm-4">
						<div class="btn-group">
							<input class="btn btn-success" type="submit" name="add_group" id="group_submit" value="Add group" /> <input class="btn btn-light" type="submit" name="cancel_group" value="Cancel" />
						</div>
					</div>
				</div>
			</div>
			<div class="col-sm-4">
				<p id="groupnameerror1" class="text-success"><span class="fa fa-check"></span> <span class="text">Name not in use</span></p>
				<p id="groupnameerror2" class="text-danger"><span class="fa fa-times"></span> <span class="text">Name must begin with a lowercase letter</span></p>
				<p id="groupnameerror3" class="text-danger"><span class="fa fa-times"></span> Name must be alphanumeric (letters, numbers, underscore)</p>
				<p id="groupdescriptionerror1" class="text-danger"><span class="fa fa-times"></span> Description must not be blank</p>
			</div>
		</div>
	</fieldset>
</form>
<script type="text/javascript">
	$(document).ready(function(){change_group_errors();});
</script>
<?php
	if(isset($message))echo $message;
	require_once 'includes/footer.inc.php';
?>