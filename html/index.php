<?php 
	require_once 'includes/header.inc.php';
?>

<div class='jumbotron'>
	<h1>
		<img src="images/imark_bw.gif" style="padding: 0 10px 10px 0; vertical-align: text-top;">
		IGB LDAP Account Management
	</h1>
	<p>View and manage IGB User Accounts</p>
</div>
<!--
	<div class="panel panel-danger">
		<div class="panel-heading"><h3 class="panel-title">Notice</h3></div>
		<div class="panel-body">
			<p>Until future notice: when adding new users, the new user still needs to be manually added to the mail aliases.</p>
		</div>
	</div>
-->
<div class="row">
	<div class="col-sm-4">
		<div class="panel panel-default">
			<div class="panel-body">
				<h1><center><span class="fa fa-user"> </span><br/>Users</center></h1>
				<p><div class="btn-group btn-group-justified">
					<a class="btn btn-info" href="list_users.php"><span class="fa fa-list"></span> List</a>
					<a class="btn btn-success" href="add_user.php"><span class="fa fa-plus-circle"></span> Add</a>
				</div></p>
			</div>
		</div>
	</div>
	<div class="col-sm-4">
		<div class="panel panel-default">
			<div class="panel-body">
				<h1><center><span class="fa fa-users"> </span><br/>Groups</center></h1>
				<p><div class="btn-group btn-group-justified">
					<a class="btn btn-info" href="list_groups.php"><span class="fa fa-list"></span> List</a>
					<a class="btn btn-success" href="add_group.php"><span class="fa fa-plus-circle"></span> Add</a>
				</div></p>
			</div>
		</div>
	</div>
	<div class="col-sm-4">
		<div class="panel panel-default">
			<div class="panel-body">
				<h1><center><span class="fa fa-server"> </span><br/>Hosts</center></h1>
				<p><div class="btn-group btn-group-justified">
					<a class="btn btn-info" href="list_hosts.php"><span class="fa fa-list"></span> List</a>
					<a class="btn btn-success" href="add_host.php"><span class="fa fa-plus-circle"></span> Add</a>
				</div></p>
			</div>
		</div>
	</div>
</div>
<div class="row">
	<div class="col-sm-4">
		<div class="panel panel-default">
			<div class="panel-body">
				<h1><center><span class="fa fa-book"> </span><br/>Classroom Users</center></h1>
				<p><div class="btn-group btn-group-justified">
<!-- 					<a class="btn btn-info" href="#"><span class="fa fa-list"></span> List</a> -->
					<a class="btn btn-success" href="add_classroom_users.php"><span class="fa fa-plus-circle"></span> Add</a>
					<a class="btn btn-danger" href="remove_classroom_users.php"><span class="fa fa-minus-circle"></span> Delete</a>
				</div></p>
			</div>
		</div>
	</div>
</div>
	
<?php
	require_once 'includes/footer.inc.php';
	?>