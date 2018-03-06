<?php
	$title = "Set Host IP";
	$sitearea = "hosts";
	require_once 'includes/header.inc.php';
	
	$message="";
	if (isset($_POST['change_ip'])) {
		$_POST = array_map("trim",$_POST);
		
		if($_POST['new_ip'] == ""){
			$_POST['new_ip'] = gethostbyname($_POST['name']);
		}
		
		if($message == ""){
			$host = new host($ldap,$_POST['name']);
			$result = $host->set_ip($_POST['new_ip']);
		
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
<div class="minijumbo"><div class="container">Change Host IP
	<?php if($hid != "") { ?>
	<nav><ol class="breadcrumb"><li class="breadcrumb-item"><a href="list_hosts.php">Hosts</a></li><li class="breadcrumb-item"><a href="host.php?hid=<?php echo $hid; ?>"><?php echo $hid; ?></a></li><li class="breadcrumb-item active">Change IP</li></ol></nav>
	<?php } ?>
</div></div>
<div class="container">
<form class="mt-4" method="post" action="<?php echo $_SERVER['PHP_SELF'];?>" name="form">
	<fieldset>
		<div class="row">
			<div class="col-sm-8">
				<div class="form-group row">
					<label class="col-sm-4 col-form-label" for="name-input">Host:</label>
					<div class="col-sm-8">
						<input type="hidden" name="name" value="<?php echo $hid; ?>" /><label class="col-form-label"><?php echo $hid; ?></label>
					</div>
				</div>
				<div class="form-group row">
					<label class="col-sm-4 col-form-label" for="ip-input">New IP:</label>
					<div class="col-sm-8">
						<input class="form-control" type="text" name="new_ip" id="ip_input" value="<?php if (isset($_POST['new_ip'])){echo $_POST['new_ip'];}?>" placeholder="Leave blank to set automatically" autofocus />
					</div>
				</div>
				<div class="form-group row">
					<div class="col-sm-8 offset-sm-4">
						<div class="btn-group">
							<input class="btn btn-info" type="submit" name="change_ip" value="Change IP" id="host_submit" /> <input class="btn btn-light" type="submit" name="cancel_host" value="Cancel" />
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