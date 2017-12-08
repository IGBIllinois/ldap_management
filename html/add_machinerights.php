<?php
	$title = "Add Host Access";
	$sitearea = "hosts";
	require_once 'includes/header.inc.php';

	$message="";
	if (isset($_POST['add_user'])) {
		foreach($_POST as $var){
			$var = trim(rtrim($var));
		}
		
		if($_POST['username']==""){
			$message .= html::error_message("Please select a user.");
		} elseif (!user::is_ldap_user($ldap,$_POST['username'])) {
			$message .= html::error_message("Invalid username. Please stop trying to break my web interface.");
		}
		
		if($_POST['host']==""){
			$message .= html::error_message("Please select a host.");
		} elseif (!host::is_ldap_host($ldap,$_POST['host'])) {
			$message .= html::error_message("Invalid host name. Please stop trying to break my web interface.");
		}
		
		if($message == ""){
			$user = new user($ldap,$_POST['username']);
			$result = $user->add_machinerights($_POST['host']);
		
			if($result['RESULT'] == true){
				header("Location: ".$_POST['from']);
			} else if ($result['RESULT'] == false) {
				$message = html::error_message($result['MESSAGE']);
			}
		}
	} else if (isset($_POST['cancel_user'])) {
		header("Location: ".$_POST['from']);
		unset($_POST);
	}
	
	$uid = "";
	$userrights = array();
	if(isset($_GET['uid'])){
		$uid = $_GET['uid'];
		$usertoadd = new user($ldap,$uid);
		$userrights = $usertoadd->get_machinerights();
		$from = "user.php?uid=$uid";
	}
	
	$hid = "";
	$machineusers = array();
	if(isset($_GET['hid'])){
		$hid = $_GET['hid'];
		$machineusers = $ldap->search("(host=".$hid.")", __LDAP_PEOPLE_OU__, array('uid'));
		$from = "host.php?hid=$hid";
	}
	
	$usershtml = "";
	$users = user::get_all_users($ldap);
	if($uid != ""){
		$usershtml = "<input type='hidden' name='username' value='$uid'/><label class='control-label'>$uid</label>";
	} else {
		$usershtml .= "<select name='username' class='form-control username-select'><option></option>";
		foreach($users as $user){
			
			$usershtml .= "<option value='".$user."'";
			if($uid == $user){
				$usershtml .= " selected";
			}
			$usershtml .= ">".$user."</option>";
		}
		$usershtml .= "</select>";
	}
	
// 	TODO move this out into html.class
	$hostshtml = "";
	if($hid != ""){
		$hostshtml = "<input type='hidden' name='host' value='$hid'/><label class='control-label'>$hid</label>";
	} else {
		$hosts = host::get_all_hosts($ldap);
		
		$igb = array();
		$biotech = array();
		$other = array();
		foreach($hosts as $host){
			if(strpos($host['name'],"biotec") !== false){
				$biotech[] = $host['name'];
			} else if(strpos($host['name'],"igb") !== false){
				$igb[] = $host['name'];
			} else {
				$other[] = $host['name'];
			}
		}
		sort($igb);
		sort($biotech);
		sort($other);
		
		$hostshtml .= "<select name='host' class='form-control host-select'><option></option>";
		$hostshtml .= "<optgroup label='IGB Hosts'>";
		foreach($igb as $host){
			if(!$userrights || !in_array($host,$userrights)){
				$hostshtml .= "<option value='".$host."'";
				if($hid == $host){
					$hostshtml .= " selected";
				}
				$hostshtml .= ">".$host."</option>";
			}
		}
		$hostshtml .= "/<optgroup>";
		$hostshtml .= "<optgroup label='Biotech Hosts'>";
		foreach($biotech as $host){
			if(!$userrights || !in_array($host,$userrights)){
				$hostshtml .= "<option value='".$host."'";
				if($hid == $host){
					$hostshtml .= " selected";
				}
				$hostshtml .= ">".$host."</option>";
			}
		}
		$hostshtml .= "/<optgroup>";
		if(count($other)!=0){
			$hostshtml .= "<optgroup label='Other Hosts'>";
			foreach($other as $host){
				if(!$userrights || !in_array($host,$userrights)){
					$hostshtml .= "<option value='".$host."'";
					if($hid == $host){
						$hostshtml .= " selected";
					}
					$hostshtml .= ">".$host."</option>";
				}
			}
			$hostshtml .= "/<optgroup>";
		}
		$hostshtml .= "</select>";
	}
?>
<form class="form-horizontal" method="post" action="<?php echo $_SERVER['PHP_SELF'];?>" name="form">
	<fieldset>
		<legend>Add Machine Rights</legend>
		<div class="form-group">
			<label class="col-sm-2 control-label" for="username-input">Username:</label>
			<div class="col-sm-4">
				<?php echo $usershtml; ?>
			</div>
		</div>
		<div class="form-group">
			<label class="col-sm-2 control-label" for="group-input">Host:</label>
			<div class="col-sm-4">
				<?php echo $hostshtml; ?>
			</div>
		</div>
		<div class="form-group">
			<div class="col-sm-4 col-sm-offset-2">
				<input type="hidden" name="from" value="<?php echo $from; ?>"/>
				<div class="btn-group">
					<input class="btn btn-primary" type="submit" name="add_user" value="Give Access" /> <input class="btn btn-default" type="submit" name="cancel_user" value="Cancel" />
				</div>
			</div>
		</div>
	</fieldset>
</form>
<script type="text/javascript">
	$(document).ready(function(){
		$(".username-select").select2({
			placeholder: "Please select a user",
			width: 'element'
		});
		$(".host-select").select2({
			placeholder: "Please select a host",
			width: 'element'
		});
	});
</script>
<?php
	if(isset($message))echo $message;
	require_once 'includes/footer.inc.php';
?>