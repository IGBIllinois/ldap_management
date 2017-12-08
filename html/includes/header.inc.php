<?php
	require_once 'includes/main.inc.php';
	require_once 'includes/session.inc.php';
	ob_start();
?>
<!DOCTYPE html>
<html lang="en">
	<head>
		<meta charset="utf-8">
		<meta http-equiv="X-UA-Compatible" content="IE=edge">
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<title><?php echo __TITLE__.(isset($title)?" - $title":''); ?></title>
		<link rel="stylesheet" href="includes/bootstrap/css/bootstrap.min.css" type="text/css"/>
		<link rel="stylesheet" href="includes/select2/css/select2.css" type="text/css" />
		<link rel="stylesheet" href="includes/font-awesome/css/font-awesome.css" type="text/css" />
		<link rel="stylesheet" href="includes/main.inc.css" type="text/css"/>
		<link rel="shortcut icon" href="images/favicon.ico" type="image/x-icon"/>
		<!-- jQuery (necessary for Bootstrap's JavaScript plugins) -->
		<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.3/jquery.min.js"></script>
		<script type="text/javascript" src="https://www.google.com/jsapi"></script>
		<script type="text/javascript" src="includes/select2/js/select2.full.js"></script>
		<script type="text/javascript" src="includes/main.inc.js"></script>
		<script type="text/javascript" src="includes/searchbar.js"></script>
	</head>
	<body>
		<nav class="navbar navbar-inverse navbar-static-top">
			<div class="container">
				<div class="navbar-header">
					<button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#archive-accounting-nav-collapse" aria-expanded="false">
				        <span class="sr-only">Toggle navigation</span>
				        <span class="icon-bar"></span>
				        <span class="icon-bar"></span>
						<span class="icon-bar"></span>
				    </button>
					<a class="navbar-brand" href="index.php">
						<?php echo __TITLE__; ?>
					</a>
				</div>
				<div class="collapse navbar-collapse" id="archive-accounting-nav-collapse">
					<ul class="nav navbar-nav">
						<li <?php if($sitearea=='users' || $sitearea=='classroom'){echo 'class="active"';} ?> class="dropdown">
							<a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button"><span class="fa fa-user"></span> Users <span class="caret"></span></a>
							<ul class="dropdown-menu">
								<li><a href="list_users.php"><span class="fa fa-list"></span> List Users</a></li>
								<li><a href="add_user.php"><span class="fa fa-plus"></span> Add User</a></li>
								<li role="separator" class="divider"></li>
								<li><a href="add_classroom_users.php"><span class="fa fa-plus"></span> Add Classroom Users</a></li>
								<li><a href="remove_classroom_users.php"><span class="fa fa-minus"></span> Remove Classroom Users</a></li>
							</ul>
						</li>
						<li <?php if($sitearea=='groups'){echo 'class="active"';} ?> class="dropdown">
							<a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button"><span class="fa fa-users"></span> Groups <span class="caret"></span></a>
							<ul class="dropdown-menu">
								<li><a href="list_groups.php"><span class="fa fa-list"></span> List Groups</a></li>
								<li><a href="add_group.php"><span class="fa fa-plus"></span> Add Group</a></li>
							</ul>
						</li>
						<li <?php if($sitearea=='hosts'){echo 'class="active"';} ?> class="dropdown">
							<a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button"><span class="fa fa-server"></span> Hosts <span class="caret"></span></a>
							<ul class="dropdown-menu">
								<li><a href="list_hosts.php"><span class="fa fa-list"></span> List Hosts</a></li>
								<li><a href="add_host.php"><span class="fa fa-plus"></span> Add Host</a></li>
							</ul>
						</li>
<!--
						<li <?php if($sitearea=='classroom'){echo 'class="active"';} ?> class="dropdown">
							<a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button"><span class="fa fa-book"></span> Classroom <span class="caret"></span></a>
							<ul class="dropdown-menu">
								<li><a href="add_classroom_users.php"><span class="fa fa-plus"></span> Add Classroom Users</a></li>
								<li><a href="remove_classroom_users.php"><span class="fa fa-minus"></span> Remove Classroom Users</a></li>
							</ul>
						</li>
-->
						<li <?php if($sitearea=='domain'){echo 'class="active"';} ?> class="dropdown">
							<a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button"><span class="fa fa-desktop"></span> Domain <span class="caret"></span></a>
							<ul class="dropdown-menu">
								<li><a href="list_computers.php"><span class="fa fa-list"></span> List Domain Computers</a></li>
								<li><a href="add_computer.php"><span class="fa fa-plus"></span> Add Domain Computer</a></li>
							</ul>
						</li>
						<li <?php if($sitearea=='statistics'){echo 'class="active"';} ?>><a href="statistics.php"><span class="fa fa-bar-chart"></span> Statistics</a></li>
					</ul>
					
					<a type="button" class="btn btn-danger btn-sm navbar-btn navbar-right hidden-xs" style="margin-right:0" href="logout.php"><span class="fa fa-sign-out"></span> Logout</a>
					<form class="navbar-form navbar-right">
						<div class="form-group" id="searchbar-container">
							<input type="search" class="form-control" name="searchbar" id="searchbar" placeholder="Search"/>
							<div id="searchbar-results"></div>
						</div>
					</form>
					<a type="button" class="btn btn-danger btn-sm btn-block visible-xs" style="margin-bottom:7px" href="logout.php"><span class="fa fa-sign-out"></span> Logout</a>
				</div>
			</div>
		</nav>
		
		<div class="container">