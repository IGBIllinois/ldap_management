<?php
	$title = "Multi-user action";
	$sitearea = "";
	require_once 'includes/header.inc.php';

	$users = null;
	$actionhtml = "";
	$from = "list_users.php?search=".urlencode($_POST['search'])."&start=".urlencode($_POST['start'])."&sort=".urlencode($_POST['sort'])."&asc=".urlencode($_POST['asc'])."&filter=".urlencode($_POST['filter']);
	$message="";
	if (isset($_POST['action']) && isset($_POST['selected']) && is_array($_POST['selected'])) {
		// Coming from list, build the form
		$users = array_keys($_POST['selected']);
		if($users == null || count($users) == 0){
			$message .= html::error_message("Please select at least one user");
		}
		
		if($_POST['action'] == 'add-to-group'){
			$actionhtml = '<div class="form-group row"><label class="col-sm-3 col-form-label" for="group-input">Group:</label><div class="col-sm-5">';
			$groups = group::get_search_groups($ldap,"",-1,-1,'name',"true",1);
			$actionhtml .= "<select name='group' class='form-control group-select'><option></option>";
			foreach($groups as $group){
				$actionhtml .= "<option value='".$group['name']."'";
				$actionhtml .= ">".$group['name']."</option>";
			}
			$actionhtml .= "</select>";
			$actionhtml .= '</div></div>';
		} else {
			$message .= html::error_message("Invalid action");
		}
		
		if($message!=""){
			header("Location: $from&message=".urlencode($message));
			exit();
		}
	} else if (isset($_POST['action']) && isset($_POST['users']) && isset($_POST['do_action'])) {
		// Coming from confirmation page, do the action
		if($_POST['action'] == 'add-to-group'){
			if(!is_array($_POST['users']) || count($_POST['users']) ==0){
				$message .= html::error_message("No users selected.");
			}
			if(!isset($_POST['group']) || !group::is_ldap_group($ldap, $_POST['group'])){
				$message .= html::error_message("Invalid group");
			}
			if($message == ""){
				$group = new group($ldap, $_POST['group']);
				for($i=0; $i<count($_POST['users']); $i++){
					$group->add_user($_POST['users'][$i]);
				}
				header("Location: $from&message=".urlencode(html::success_message("Successfully added ".count($_POST['users'])." users to group ".$_POST['group'])));
				exit();
			}
		} else {
			$message .= html::error_message("Invalid action");
		}
	} else if (isset($_POST['cancel_user'])) {
		header("Location: $from");
		unset($_POST);
		exit();
	}
	
	$uid = "";
	$usershtml = "";
	if($users != null && count($users) > 0){
		for($i=0; $i<count($users); $i++){
			$usershtml .= $users[$i]."<input type='hidden' name='users[$i]' value='".$users[$i]."'/>";
			if($i+1<count($users)){
				$usershtml .= ", ";
			}
		}
	}
	$searchdescription = html::get_list_users_description_from_cookies();
?>
<div class="minijumbo"><div class="container">Add Users to Group
	<nav><ol class="breadcrumb"><li class="breadcrumb-item"><a href="<?php echo html::get_list_users_url_from_cookies(); ?>">Users<?php if($searchdescription!=""){echo " ($searchdescription)";} ?></a></li><li class="breadcrumb-item active">Add Users to Group</li></ol></nav>
</div></div>
<div class="container">
<form class="mt-4" method="post" action="<?php echo $_SERVER['PHP_SELF'];?>" name="form">
	<input type="hidden" name="action" value="<?php echo $_POST['action']; ?>"/>
	<fieldset>
		<div class="form-group row">
			<label class="col-sm-3 align-middle" for="username-input">Usernames:</label>
			<div class="col-sm-5">
				<?php echo $usershtml; ?>
			</div>
		</div>
		<input type="hidden" name="sort" value="<?php echo $_POST['sort']; ?>" />
		<input type="hidden" name="asc" value="<?php echo $_POST['asc']; ?>" />
		<input type="hidden" name="search" value="<?php echo $_POST['search']; ?>" />
		<input type="hidden" name="start" value="<?php echo $_POST['start']; ?>" />
		<input type="hidden" name="filter" value="<?php echo $_POST['filter']; ?>" />
		
		<?php echo $actionhtml; ?>
			
		<div class="form-group row">
			<div class="col-sm-5 offset-sm-3">
				<input type="hidden" name="from" value="<?php echo $from; ?>"/>
				<div class="btn-group">
					<input class="btn btn-success" type="submit" name="do_action" value="Add users" /> <input class="btn btn-light" type="submit" name="cancel_user" value="Cancel" />
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
		$(".group-select").select2({
			placeholder: "Please select a group",
			width: 'element'
		});
	});
</script>
<?php
	if(isset($message))echo $message;
	require_once 'includes/footer.inc.php';
?>