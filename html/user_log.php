<?php
$title = "User Logs: ".$_GET['uid'];
$sitearea = "users";
require_once 'includes/header.inc.php';

$username = $login_user->get_username();
if (isset($_GET['uid'])) {
	$username = $_GET['uid'];
	if(!user::is_ldap_user($ldap,$username)){
		header('location: list_users.php');
		exit();
	}
}

$user = new user($ldap,$username);

$logs = log::search_logs($user->get_username());

$searchdescription = html::get_list_users_description_from_cookies();
?>
	<style>
		tr.topborder {
			border-top: 2px solid darkgrey;
		}
	</style>
	<div class="minijumbo"><div class="container"><?php echo $user->get_name(); ?> - Logs
			<nav><ol class="breadcrumb"><li class="breadcrumb-item"><a href="<?php echo html::get_list_users_url_from_cookies(); ?>">Users<?php if($searchdescription!=""){echo " ($searchdescription)";} ?></a></li><li class="breadcrumb-item"><a href="user.php?uid=<?php echo $user->get_username(); ?>"><?php echo $user->get_username(); ?></a></li><li class="breadcrumb-item active">Logs</li></ol></nav>
		</div></div>
	<div class="container">
	<div class="card mt-4">
		<table class="table table-striped table-sm table-igb-bordered table-responsive-md mb-0">
			<tr>
				<th>Time</th>
				<th>User</th>
				<th>Message</th>
			</tr>
			<?php
			for($i=0; $i<count($logs); $i++){
				echo "<tr><td class='shrink'>".strftime('%D %r',$logs[$i]['time'])."</td>";
				if($logs[$i]['uid'] == 'guest'){
					echo "<td class='shrink'>script</td>";
				} else {
					echo "<td><a href='user.php?uid=" . $logs[$i]['uid'] . "'>" . $logs[$i]['uid'] . "</a></td>";
				}
				echo "<td>".$logs[$i]['msg']."</td>";
			}
			?>
		</table>
	</div>

<?php
require_once 'includes/footer.inc.php';