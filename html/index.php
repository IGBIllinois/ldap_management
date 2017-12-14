<?php 
	require_once 'includes/header.inc.php';
?>
</div>
<div class='jumbotron jumbotron-fluid'>
	<div class="container">
		<h1 class="display-3 igb-jumbo">
			<img src="images/imark_bw.gif" class="mx-2 align-text-top mt-2">
			IGB LDAP Account Management
		</h1>
		<p class="lead">View and manage IGB User Accounts</p>
	</div>
</div>
<div class="container">
<div class="row">
	<div class="col-md-4">
		<div class="card">
			<div class="card-body">
				<h2 class="display-5"><center><span class="fa fa-user"> </span><br/>Users</center></h1>
				<p><div class="btn-group d-flex">
					<a class="btn btn-info w-100" href="list_users.php"><span class="fa fa-list"></span> List</a>
					<a class="btn btn-success w-100" href="add_user.php"><span class="fa fa-plus-circle"></span> Add</a>
				</div></p>
			</div>
		</div>
	</div>
	<div class="col-md-4 mt-2 mt-md-0">
		<div class="card">
			<div class="card-body">
				<h2 class="display-5"><center><span class="fa fa-users"> </span><br/>Groups</center></h1>
				<p><div class="btn-group d-flex">
					<a class="btn btn-info w-100" href="list_groups.php"><span class="fa fa-list"></span> List</a>
					<a class="btn btn-success w-100" href="add_group.php"><span class="fa fa-plus-circle"></span> Add</a>
				</div></p>
			</div>
		</div>
	</div>
	<div class="col-md-4 mt-2 mt-md-0">
		<div class="card">
			<div class="card-body">
				<h2 class="display-5"><center><span class="fa fa-server"> </span><br/>Hosts</center></h1>
				<p><div class="btn-group d-flex">
					<a class="btn btn-info w-100" href="list_hosts.php"><span class="fa fa-list"></span> List</a>
					<a class="btn btn-success w-100" href="add_host.php"><span class="fa fa-plus-circle"></span> Add</a>
				</div></p>
			</div>
		</div>
	</div>
</div>
<div class="row mt-0 mt-md-4">
	<div class="col-md-4 mt-2 mt-md-0">
		<div class="card">
			<div class="card-body">
				<h2 class="display-5"><center><span class="fa fa-book"> </span><br/>Classroom Users</center></h1>
				<p><div class="btn-group d-flex">
<!-- 					<a class="btn btn-info w-100" href="#"><span class="fa fa-list"></span> List</a> -->
					<a class="btn btn-success w-100" href="add_classroom_users.php"><span class="fa fa-plus-circle"></span> Add</a>
					<a class="btn btn-danger w-100" href="remove_classroom_users.php"><span class="fa fa-minus-circle"></span> Delete</a>
				</div></p>
			</div>
		</div>
	</div>
	<div class="col-md-4 mt-2 mt-md-0">
		<div class="card">
			<div class="card-body">
				<h2 class="display-5"><center><span class="fa fa-desktop"> </span><br/>Domain Computers</center></h1>
				<p><div class="btn-group d-flex">
					<a class="btn btn-info w-100" href="list_domain_computers.php"><span class="fa fa-list"></span> List</a>
					<a class="btn btn-success w-100" href="add_domain_computer.php"><span class="fa fa-plus-circle"></span> Add</a>
				</div></p>
			</div>
		</div>
	</div>
	<div class="col-md-4 mt-2 mt-md-0">
		<div class="card">
			<div class="card-body">
				<h2 class="display-5"><center><span class="fa fa-bar-chart"> </span><br/>Statistics</center></h1>
				<p><div class="btn-group d-flex">
					<a class="btn btn-info w-100" href="statistics.php"><span class="fa fa-bar-chart"></span> View</a>
				</div></p>
			</div>
		</div>
	</div>
</div>
	
<?php
	require_once 'includes/footer.inc.php';