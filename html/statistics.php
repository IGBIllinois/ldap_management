<?php
	$title = "Statistics";
	$sitearea = "statistics";
	require 'includes/header.inc.php';
?>
	</div>
	<div class="minijumbo"><div class="container">Statistics</div></div>
	<div class="container">
		<table class='table table-condensed table-striped table-bordered table-igb-bordered'>
			<tr>
				<td class="d-flex"><span class="mr-auto">Users</span> <i class="my-auto fa fa-user text-dark"></i></td>
				<td><?php echo statistics::users($ldap); ?></td>
			</tr>
			<tr>
				<td class="d-flex"><span class="mr-auto">Expired Passwords</span> <i class="my-auto fa fa-key text-danger"></i></td>
				<td><?php echo statistics::password_expired_users($ldap); ?></td>
			</tr>
			<tr>
				<td class="d-flex"><span class="mr-auto">Expiring Users</span> <i class="my-auto fa fa-clock-o text-warning"></i></td>
				<td><?php echo statistics::expiring_users($ldap)-statistics::expired_users($ldap); ?></td>
			</tr>
			<tr>
				<td class="d-flex"><span class="mr-auto">Expired Users</span> <i class="my-auto fa fa-clock-o text-danger"></i></td>
				<td><?php echo statistics::expired_users($ldap); ?></td>
			</tr>
			<tr>
				<td class="d-flex"><span class="mr-auto">Users who have left UIUC</span> <i class="my-auto fa fa-graduation-cap text-warning"></i></td>
				<td><?php echo statistics::leftcampus_users($ldap); ?></td>
			</tr>
			<tr>
				<td class="d-flex"><span class="mr-auto">Non-UIUC Users</span> <i class="my-auto fa fa-graduation-cap text-info"></i></td>
				<td><?php echo statistics::noncampus_users($ldap); ?></td>
			</tr>
			<tr>
				<td class="d-flex"><span class="mr-auto">Classroom Users</span> <i class="my-auto fa fa-book text-info"></i></td>
				<td><?php echo statistics::classroom_users($ldap); ?></td>
			</tr>
			<tr>
				<td class="d-flex"><span class="mr-auto">Groups</span> <i class="my-auto fa fa-users text-dark"></i></td>
				<td><?php echo statistics::groups($ldap); ?></td>
			</tr>
			<tr>
				<td class="d-flex"><span class="mr-auto">Empty Groups</span> <i class="my-auto fa fa-users text-muted"></i></td>
				<td><?php echo statistics::empty_groups($ldap); ?></td>
			</tr>
		</table>
		<div class="row">
			<div class="col-xxl-6">
				<div id="created-chart"></div>
			</div>
			<div class="col-xxl-6">
				<div id="password-chart"></div>
			</div>
		</div>
		<script type="text/javascript">
			$(document).ready(function(){
				google.charts.setOnLoadCallback(drawCreatedUserChart);
				google.charts.setOnLoadCallback(drawPasswordSetChart);
			});
		</script>
<?php
	require 'includes/footer.inc.php';