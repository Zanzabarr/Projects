<?php
include("includes/config.php"); 


if ( ! is_installed() ) echo "<script type=\"text/javascript\">window.location = \"{$_config['admin_url']}install/index.php\"</script>";

// On form ?action=login
if (isset($_GET['action']) && $_GET['action'] == "login") {
	$username = $_POST['username'];
	$password =  $_POST['password'];

	$error = authorizeUser($username, $password);
	
// On form ?action=logout
} else if (isset($_GET['action']) && $_GET['action'] == "logout") {
	session_unset();
	session_destroy();
	$logout ="<div class='logout'><b>You are now<br /> logged out</div>";
}

//Function to authorize the user
function authorizeUser($u,$p) {
	global $_config;
	$query=logged_query("
SELECT *
FROM auth_users 
WHERE username=:username 
LIMIT 1", 0, array(':username' => $u));

	if (isset($query[0])) $user=$query[0];
	else	$user = false;
	
	cleanup_admin_tmp_password();
	if ($user && (hasher($p, $user['password']) ) ) $password_matched = true;
	elseif ($user && $user['tmp_password'] && (hasher($p, $user['tmp_password']) ) ) 
	{ 
		$password_matched = true;
	}
	else $password_matched = false;
	
	if ($password_matched) 
	{
		// remove the tmp_password/date
		// set the pw_change_request to true
		logged_query("
		UPDATE `auth_users` 
		SET `tmp_password`='', `tmp_password_date`=''
		WHERE `username`=:u;",0,array(":u" => $u)
		);
		
		$valid_user = $user;
		$valid_user['user_id'];
		// set user's session data
		$_SESSION['uid'] = $valid_user['user_id'];
		$_SESSION['username'] = $valid_user['username'];
		$_SESSION['user'] = $valid_user;
		// don't want to reveal passwords in data stream
		unset( $_SESSION['user']['password']);
		
		// set active Modules session data && initialize if needed
		$activeModules = getModules();

		$moduleAdded = array(); // includes fill this array
		foreach($activeModules as $module)
		{
			include( $_config['admin_modules'] . "{$module}/system/install_module.php");
			$moduleData = getModuleData( $module );
			$_SESSION['modules'][$module] = $moduleData;
		}

		// set loaded data for use on dashboard
		flash('module_added', $moduleAdded);

		header("Location:dashboard.php");
	} else { 
		return "<div class='error'><b>ERROR!</b><br />Username/Password combination doesn't match!</div>";
    } 
} 
?>

<!DOCTYPE>
<html xmlns="//www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title><?php echo $_config['site']; ?> Admin Login</title>
<link rel="stylesheet" href="css/login.css" type="text/css" media="screen" />
<link href='//fonts.googleapis.com/css?family=Oswald' rel='stylesheet' type='text/css'>
<!-- jQuery Files -->
<script src="//ajax.googleapis.com/ajax/libs/jquery/1.7/jquery.min.js"></script>
<link media="screen" type="text/css" href="js/fancybox/jquery.fancybox-1.3.4.css" rel="stylesheet">
<script type="text/javascript">
$(document).ready(function() {
	$('.ajaxForm').each(function(){
		$(this).load($(this).attr('data-location'),$.parseJSON($(this).attr('data-post')) )
	});
});
</script>

</head>

<body>
<!-- Site Login Wrapper -->
<div class="login_wrapper">

    <!-- Login Error -->
    <?php 	
$formData = array(
	'username' => isset($_POST['username']) ? $_POST['username'] : ''
);
if(isset($error)) echo $error; 
if (isset($logout))echo $logout; 
ajax_form("form_login.php #form_login", $formData);
	
	?>
    	
 


    </div>
<!--  End Site Login Wrapper -->
</div>
<!-- Copyright -->
<div class="login_copyright">&copy; Copyright <?php echo date("Y"); ?> <a href="http://www.test.com">Test</a>.
</div>
</body>
</html>
