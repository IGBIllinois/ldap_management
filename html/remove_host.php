<?php
	$title = "Remove Host";
	$sitearea = "hosts";
	require_once 'includes/header.inc.php';
	
	$message="";
	if (isset($_POST['remove_host']) && isset($_POST['host'])) {
		$_POST = array_map("trim",$_POST);
		
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
<div class="minijumbo"><div class="container">Remove Host
	<?php if($hid != "") { ?>
	<nav><ol class="breadcrumb"><li class="breadcrumb-item"><a href="list_hosts.php">Hosts</a></li><li class="breadcrumb-item"><a href="host.php?hid=<?php echo $hid; ?>"><?php echo $hid; ?></a></li><li class="breadcrumb-item active">Remove</li></ol></nav>
	<?php } ?>
</div></div>
<div class="container">
<form class="mt-4" method="post" action="<?php echo $_SERVER['PHP_SELF'];?>" name="form">
	<fieldset>
		<div class="form-group row">
			<label class="col-sm-3 col-form-label">Hostname:</label>
			<div class="col-sm-5">
				<input type="hidden" name="host" value="<?php echo $hid; ?>" autofocus /><label class="col-form-label"><?php echo $hid; ?></label>
			</div>
		</div>
		<div class="form-group row">
			<div class="col-sm-3"></div>
			<div class="col-sm-5">
				Are you sure you want to remove this host? Access to this host will be revoked for all users. This operation cannot be undone.
			</div>
		</div>
		<div class="form-group row">
			<div class="col-sm-5 offset-sm-3">
				<div class="btn-group">
					<input class="btn btn-danger" type="submit" name="remove_host" value="Remove host" /> <input class="btn btn-light" type="submit" name="cancel_host" value="Cancel" />
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
