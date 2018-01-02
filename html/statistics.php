<?php
	$title = "Statistics";
	$sitearea = "statistics";
	require 'includes/header.inc.php';
?>
	<h3 class="mt-4">Statistics</h3>
	<table class='table table-condensed table-striped table-bordered'>
		<tr>
			<td>Users</td>
			<td><?php echo statistics::users($ldap); ?></td>
		</tr>
		<tr>
			<td>Expired Passwords</td>
			<td><?php echo statistics::password_expired_users($ldap); ?></td>
		</tr>
		<tr>
			<td>Expiring Users</td>
			<td><?php echo statistics::expiring_users($ldap)-statistics::expired_users($ldap); ?></td>
		</tr>
		<tr>
			<td>Expired Users</td>
			<td><?php echo statistics::expired_users($ldap); ?></td>
		</tr>
		<tr>
			<td>Users who have left UIUC</td>
			<td><?php echo statistics::leftcampus_users($ldap); ?></td>
		</tr>
		<tr>
			<td>Non-UIUC Users</td>
			<td><?php echo statistics::noncampus_users($ldap); ?></td>
		</tr>
		<tr>
			<td>Classroom Users</td>
			<td><?php echo statistics::classroom_users($ldap); ?></td>
		</tr>
		<tr>
			<td>Groups</td>
			<td><?php echo statistics::groups($ldap); ?></td>
		</tr>
		<tr>
			<td>Empty Groups</td>
			<td><?php echo statistics::empty_groups($ldap); ?></td>
		</tr>
	</table>
	
<?php
	require 'includes/footer.inc.php';