<?php
include_once("../includes/config.php"); 
include_once($_config['admin_includes'] . "functions.php");

// redirect to admin (redirects to login if not logged it) if already installed
if ( is_installed() ) header('Location: ' . $_config['admin_url']);
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "//www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="//www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>CMS Installation</title>
<link rel="stylesheet" href="css/style.css" type="text/css" media="screen" />
</head>
<?php

$counter = 0;
if (isset($_GET['action']) && $_GET['action'] == "installation") {
	$error ="<div class='error'><b>Oops!</b><br />";
	if ($_POST['first_name'] != "") {
		$counter++;
	} else {
	$error .="You Must Enter Your First Name<br />";	
	}
	if ($_POST['last_name'] != "") {
		$counter++;
	} else {
	$error .="You Must Enter Your Last Name<br />";	
	}
	if ($_POST['email'] != "") {
		$counter++;
	} else {
	$error .="You Must Enter An Email Address<br />";	
	}
	if ($_POST['username'] != "") {
		$counter++;
	} else {
	$error .="You Must Enter A Username<br />";	
	}
	if ($_POST['password'] != "") {
		$counter++;
		$pass = hasher(trim($_POST['password']));
	} else {
	$error .="You Must Enter A Password<br />";	
	}
	$error .="</div>";
}

//echo $counter;
if ($counter == 5) { 

	$moduleAdded = install_core_db($_config, $pass);


	// install all tables from modules
	$modules = getModules();
	foreach ( $modules as $module )
	{	
		include( $_config['admin_modules'] . "{$module}/system/install_module.php");
	}
	
	if(count($moduleAdded)) 
	{
		echo "<p>Modules and Components Added:</p><ul>";
		foreach($moduleAdded as $newMod => $dummy) 
			echo "<li>{$newMod}</li>";
		echo "</ul>";
	}
	// made it to the end, set the success message
	$success ="<div class='success'><b>Congratulations!</b><p>You can now log in to the administrative section using the username and password you just entered.</p><p><a href='{$_config['admin_url']}login.php'>Log in to Admin Panel</a></p></div>";

}

?>
<body>
<!-- Site Login Wrapper -->
<div class="install_wrapper">
	<!-- INFO DIV -->
    <div><?php if (!isset($success)) { echo isset($error) ? $error : '' ; } else { echo $success; }?></div>
	<?php if (!isset($success)) { ?>
	<h1>INSTALL CITYLINE CMS</h1>
        <!-- Login Box -->
    <div id="login_box">
    	<div id="login_title">
        	Login :: Website.com Backend
        </div>
        <div id="username">
            <form method="POST" action="?action=installation">
            <table>
                <tr>
                    <td>
                    	
                        First Name:
                    </td>
                    <td>
                   		
                        <input type="text" name="first_name" />
                    </td>
                </tr>
                                <tr>
                    <td>
                    	
                        Last Name:
                    </td>
                    <td>
                   		
                        <input type="text" name="last_name" />
                    </td>
                </tr>
                                <tr>
                    <td>
                    	
                        Email:
                    </td>
                    <td>
                   		
                        <input type="text" name="email" />
                    </td>
                </tr>
                                <tr>
                    <td>
                    	
                        Username:
                    </td>
                    <td>
                   		
                        <input type="text" name="username" />
                    </td>
                </tr>
                <tr>
                    <td>
                        Password:
                    </td>
                    <td>
                        <input type="password" name="password" />
                    </td>
                </tr>
                <tr>
                    <td>&nbsp;
                        
                    </td>
                    <td>
                        <input type="submit" id="submit" name="submit" value="INSTALL" />
                    </td>
                </tr>
             </table>
             </form>
         </div>
    </div>
<?php } ?>

<!--  End Site Login Wrapper -->
</div>
<!-- Copyright -->
<div class="login_copyright">&copy; Copyright <?php echo date("Y"); ?> <a href="http://www.Test.com">Test</a>.
</div>
</body>
</html>
