<?php
	$title = "Statistics";
	require 'includes/header.inc.php';
?>
	<h3>Statistics</h3>
	<table class='table table-condensed table-striped table-bordered'>
		<tr>
			<td># of Users</td>
			<td><?php echo statistics::users($ldap); ?></td>
		</tr>
		<tr>
			<td># of Expired Passwords</td>
			<td><?php echo statistics::password_expired_users($ldap); ?></td>
		</tr>
		<tr>
			<td># of Expiring Users</td>
			<td><?php echo statistics::expiring_users($ldap)-statistics::expired_users($ldap); ?></td>
		</tr>
		<tr>
			<td># of Expired Users</td>
			<td><?php echo statistics::expired_users($ldap); ?></td>
		</tr>
		<tr>
			<td># of Users who have left UIUC</td>
			<td><?php echo statistics::leftcampus_users($ldap); ?></td>
		</tr>
		<tr>
			<td># of Groups</td>
			<td><?php echo statistics::groups($ldap); ?></td>
		</tr>
		<tr>
			<td># of Empty Groups</td>
			<td><?php echo statistics::empty_groups($ldap); ?></td>
		</tr>
	</table>
	
<?php
	require 'includes/footer.inc.php';