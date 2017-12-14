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
		<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
		<title><?php echo __TITLE__.(isset($title)?" - $title":''); ?></title>
		<link rel="stylesheet" href="includes/bootstrap/css/bootstrap.min.css" type="text/css"/>
		<link rel="stylesheet" href="includes/select2/css/select2.css" type="text/css" />
		<link rel="stylesheet" href="includes/font-awesome/css/font-awesome.css" type="text/css" />
		<link rel="stylesheet" href="includes/main.inc.css" type="text/css"/>
		<link rel="shortcut icon" href="images/favicon.ico" type="image/x-icon"/>
		
		<script src="includes/jquery-3.2.1.js"></script>
	</head>
	<body>
		<nav class="navbar navbar-dark bg-dark navbar-expand-lg">
			<div class="container">
				<a class="navbar-brand" href="index.php">
					<?php echo __TITLE__; ?>
				</a>
				<button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#igblam-nav-collapse" aria-controls="navbarText" aria-expanded="false" aria-label="Toggle navigation">
					<span class="navbar-toggler-icon"></span>
				</button>
					
				<div class="collapse navbar-collapse" id="igblam-nav-collapse">
					<ul class="navbar-nav mr-auto mt-2 mt-lg-0">
						<li class="nav-item dropdown<?php if( isset($sitearea) && ($sitearea=='users' || $sitearea=='classroom') ){echo ' active';} ?>">
							<a href="#" class="dropdown-toggle nav-link" data-toggle="dropdown" role="button"><span class="fa fa-user"></span> Users</a>
							<div class="dropdown-menu">
								<a class="dropdown-item" href="list_users.php"><span class="fa fa-list"></span> List Users</a>
								<a class="dropdown-item" href="add_user.php"><span class="fa fa-plus"></span> Add User</a>
								<div class="dropdown-divider"></div>
								<a class="dropdown-item" href="add_classroom_users.php"><span class="fa fa-plus"></span> Add Classroom Users</a>
								<a class="dropdown-item" href="remove_classroom_users.php"><span class="fa fa-minus"></span> Remove Classroom Users</a>
							</div>
						</li>
						<li class="nav-item dropdown<?php if(isset($sitearea) && $sitearea=='groups'){echo ' active';} ?>">
							<a href="#" class="dropdown-toggle nav-link" data-toggle="dropdown" role="button"><span class="fa fa-users"></span> Groups <span class="caret"></span></a>
							<div class="dropdown-menu">
								<a class="dropdown-item" href="list_groups.php"><span class="fa fa-list"></span> List Groups</a>
								<a class="dropdown-item" href="add_group.php"><span class="fa fa-plus"></span> Add Group</a>
							</div>
						</li>
						<li class="nav-item dropdown<?php if(isset($sitearea) && $sitearea=='hosts'){echo ' active';} ?>">
							<a href="#" class="dropdown-toggle nav-link" data-toggle="dropdown" role="button"><span class="fa fa-server"></span> Hosts <span class="caret"></span></a>
							<div class="dropdown-menu">
								<a class="dropdown-item" href="list_hosts.php"><span class="fa fa-list"></span> List Hosts</a>
								<a class="dropdown-item" href="add_host.php"><span class="fa fa-plus"></span> Add Host</a>
							</div>
						</li>
						<li class="nav-item dropdown<?php if(isset($sitearea) && $sitearea=='domain'){echo ' active';} ?>">
							<a href="#" class="dropdown-toggle nav-link" data-toggle="dropdown" role="button"><span class="fa fa-desktop"></span> Domain <span class="caret"></span></a>
							<div class="dropdown-menu">
								<a class="dropdown-item" href="list_computers.php"><span class="fa fa-list"></span> List Domain Computers</a>
								<a class="dropdown-item" href="add_computer.php"><span class="fa fa-plus"></span> Add Domain Computer</a>
							</div>
						</li>
						<li class="nav-item<?php if(isset($sitearea) && $sitearea=='statistics'){echo ' active';} ?>"><a href="statistics.php" class="nav-link"><span class="fa fa-bar-chart"></span> Statistics</a></li>
					</ul>
					
					<form class="form-inline mt-2 mt-lg-0" id="searchbar-container">
						<input type="search" class="form-control" name="searchbar" id="searchbar" placeholder="Search"/>
						<div id="searchbar-results" class="dropdown-menu"></div>
					</form>
					<a class="btn btn-outline-secondary my-2 my-lg-0 ml-lg-1" style="margin-right:0" href="logout.php"><span class="fa fa-sign-out"></span> Logout</a>
				</div>
			</div>
		</nav>
		
		<div class="container">