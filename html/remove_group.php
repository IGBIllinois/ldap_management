<?php
	$title = "Remove Group";
	$sitearea = "groups";
	require_once 'includes/header.inc.php';
	
	$message="";
	if (isset($_POST['remove_group']) && isset($_POST['group'])) {
		foreach($_POST as $var){
			$var = trim(rtrim($var));
		}
		
		if($_POST['group'] == ""){
			$message .= "Group cannot be blank. Please stop trying to break my web interface.";
		}
		
		if($message == ""){
			$group = new group($ldap,$_POST['group']);
			$result = $group->remove();
		
			if($result['RESULT'] == true){
				header("Location: list_groups.php");
			} else if ($result['RESULT'] == false) {
				$message = $result['MESSAGE'];
			}
		}
	} else if (isset($_POST['cancel_group'])) {
		header('location: group.php?gid='.$_POST['group']);
		unset($_POST);
		exit();
	}
	
	$gid = "";
	if(isset($_GET['gid'])){
		$gid = $_GET['gid'];
	} else if (isset($_POST['group'])){
		$gid = $_POST['group'];
	}
	if($gid == ""){
		header('location: index.php');
	}
?>
<div class="minijumbo"><div class="container">Remove Group
	<?php if($gid != "") { ?>
	<nav><ol class="breadcrumb"><li class="breadcrumb-item"><a href="list_groups.php">Groups</a></li><li class="breadcrumb-item"><a href="group.php?gid=<?php echo $gid; ?>"><?php echo $gid; ?></a></li><li class="breadcrumb-item active">Remove</li></ol></nav>
	<?php } ?>
</div></div>
<div class="container">
<form class="mt-4" method="post" action="<?php echo $_SERVER['PHP_SELF'];?>" name="form">
	<fieldset>
		<div class="form-group row">
			<label class="col-sm-3 col-form-label">Group:</label>
			<div class="col-sm-5">
				<input type="hidden" name="group" value="<?php echo $gid; ?>" autofocus /><label class="col-form-label"><?php echo $gid; ?></label>
			</div>
		</div>
		<div class="form-group row">
			<div class="col-sm-3"></div>
			<div class="col-sm-5">
				Are you sure you want to remove this group? This operation cannot be undone.
			</div>
		</div>
		<div class="form-group row">
			<div class="col-sm-5 offset-sm-3">
				<div class="btn-group">
					<input class="btn btn-danger" type="submit" name="remove_group" value="Remove group" /> <input class="btn btn-light" type="submit" name="cancel_group" value="Cancel" />
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
