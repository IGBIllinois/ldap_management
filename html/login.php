<?php
include_once 'includes/main.inc.php';

$session = new session(__SESSION_NAME__);
$message = "";
$webpage = $dir = dirname($_SERVER['PHP_SELF']) . "/index.php";
if ($session->get_var('webpage') != "") {
	$webpage = $session->get_var('webpage');
}

if (isset($_POST['login'])) {

	$username = trim(rtrim($_POST['username']));
	$password = $_POST['password'];

	$error = false;
	if ($username == "") {
		$error = true;
		$message .= html::error_message("Please enter your username.");
	}
	if ($password == "") {
		$error = true;
		$message .= html::error_message("Please enter your password.");
	}
	if ($error == false) {
// 		$ldap = new ldap(__LDAP_HOST__,__LDAP_SSL__,__LDAP_PORT__,__LDAP_BASE_DN__);
		$login_user = new user($ldap,$username);
		$success = $login_user->authenticate($password);
		if ($success==0) {
			$session_vars = array('login'=>true,
                'username'=>$username,
                'password'=>$password,
                'timeout'=>time(),
                'ipaddress'=>$_SERVER['REMOTE_ADDR']
        	);
            $session->set_session($session_vars);
            $ldap->set_bind_user($login_user->get_user_rdn());
            $ldap->set_bind_pass($password);


			$location = "http://" . $_SERVER['SERVER_NAME'] . $webpage;
        	header("Location: " . $location);
		}
		else {
			$message .= html::error_message("Invalid username or password. Please try again. Error: ".$success);
		}
	}
}



?>

<!DOCTYPE html>
<html lang="en">
	<head>
		<meta charset="utf-8">
		<meta http-equiv="X-UA-Compatible" content="IE=edge">
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<title><?php echo __TITLE__; ?></title>
		<link rel="stylesheet" href="includes/bootstrap/css/bootstrap.css" type="text/css">
		<link rel="stylesheet" href="includes/font-awesome/css/font-awesome.css" type="text/css" />
		<link rel="stylesheet" href="includes/main.inc.css" type="text/css"/>
		<link rel="shortcut icon" href="images/favicon.ico" type="image/x-icon">
	</head>
	<body OnLoad="document.login.username.focus();">
		<nav class="navbar navbar-dark bg-dark navbar-expand-lg">
			<div class="container">
				<div class="navbar-brand">
					<?php echo __TITLE__; ?>
				</div>
			</div>
		</nav>
		</div>
		<div class='jumbotron jumbotron-fluid mainjumbo'>
			<div class="container">
				<h1 class="display-3 igb-jumbo">
					<img src="images/imark_bw.gif" class="mx-2 align-text-top mt-2">
					IGB LDAP Account Management
				</h1>
				<p class="lead">View and manage IGB User Accounts</p>
			</div>
		</div>
		<div class="container">
		<div class="container">
			<div class='row'>
				<div class='col-md-6 offset-md-3'>
					<form action='login.php' method='post' name='login' class="mx-auto">
						<div class="form-group row">
							<label for="username" class="col-sm-3 col-form-label">Username</label>
							<div class="col-sm-9">
								<div class="input-group">
									<input class='form-control' type='text' name='username' id="username" tabindex='1' placeholder='Username' value='<?php if (isset($username)) { echo $username; } ?>'> 
									<span class="input-group-addon"><span class='fa fa-user'></span></span>
								</div>
							</div>
						</div>
						<div class="form-group row">
							<label for="password" class="col-sm-3 col-form-label">Password</label>
							<div class="col-sm-9">
								<div class="input-group">
									<input class='form-control' type='password' name='password' placeholder='Password' tabindex='2'>
									<span class="input-group-addon"><span class='fa fa-lock'></span></span>
								</div>
							</div>
						</div>
						<div class="row">
							<div class="col-sm-9 offset-sm-3">
								<button type='submit' name='login' class='btn btn-primary'><span class="fa fa-sign-in"></span> Login</button>
							</div>
						</div>
					</form>
	
	
					<?php if (isset($message)) { 
						echo $message;
	} ?>
				</div>
			</div>
<?php include("includes/footer.inc.php");