<?php
	$title = "User LDAP Entry: ".$_GET['uid'];
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
	$ldapattributes = $user->get_ldap_attributes();
	
	$attributes = array();
	for($i=0; $i<$ldapattributes['count']; $i++){
		$attributes[] = $ldapattributes[$i];
	}
	sort($attributes);
	
	$searchdescription = html::get_list_users_description_from_cookies();
	?>
	<style>
		tr.topborder {
			border-top: 2px solid darkgrey;
		}	
	</style>
	<div class="minijumbo"><div class="container"><?php echo $user->get_name(); ?> - LDAP Entry
		<nav><ol class="breadcrumb"><li class="breadcrumb-item"><a href="<?php echo html::get_list_users_url_from_cookies(); ?>">Users<?php if($searchdescription!=""){echo " ($searchdescription)";} ?></a></li><li class="breadcrumb-item"><a href="user.php?uid=<?php echo $user->get_username(); ?>"><?php echo $user->get_username(); ?></a></li><li class="breadcrumb-item active">LDAP Entry</li></ol></nav>
	</div></div>
	<div class="container">
	<div class="card mt-4">
		<table class="table table-striped table-sm table-igb-bordered table-responsive-md mb-0">
			<?php
				for($i=0; $i<count($attributes); $i++){
					for($j=0; $j<$ldapattributes[$attributes[$i]]['count']; $j++){
						echo "<tr><th><span class='align-middle'>".$attributes[$i].":</span></th><td><span class='align-middle'>".$ldapattributes[$attributes[$i]][$j]."</span></td></tr>";
					}
				}
			?>
		</table>
	</div>
		
	<?php
	require_once 'includes/footer.inc.php';