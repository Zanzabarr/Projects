<?php
include("includes/config.php"); 
$msgs = array(); 	// temporary error reporting

$success = ''; 
$error = '';

if (isset($_POST['username']) && trim($_POST['username']) != "") {	
	// get user by username
	$user = logged_query('
	SELECT `user_id`, `email`, `username`, `tmp_password`, `tmp_password_date` 
	FROM auth_users 
	WHERE username=:username',
	0,array(
		":username" => trim($_POST['username']))
	);
	
	// if no email: can't complete request, no email address on record
	
	if(is_array($user) && count($user))
	{
		$user = $user[0];
		
		// whoa Nelly! Don't do it if we already have a tmp_password (they only last ten minutes)
		cleanup_admin_tmp_password();
		if( $user['tmp_password'])
		{
			$error = "<p>A reset email has recently been sent to this address. Be sure to check your spam folder. You can request another reset in a few minutes.</p>";
		}
		// must be valid id
		else
		{
			 
			
			// create tmp_password && (if confirm code presesnt) confirm_code
			$newPassword = rand_string();
			// encrypt password
			$encryptedPassword = hasher($newPassword); 
			
			// update member record in db
			$result = logged_query("UPDATE `auth_users` SET `tmp_password`=:encryptedPassword, `tmp_password_date`=NOW() WHERE `user_id`=:user_id && user_id >0 ",0,array(
				":encryptedPassword" => $encryptedPassword,
				":user_id" => $user['user_id']
			));
			if(!$result) 
			{	
				$error = "<p>There was an error setting your new Password. Try again later</p>";
			}
			// email details to user
			else
			{
				$user_fullname = $_config['company_name'] . " User";
				if((isset($user['first_name']) && $user['first_name']) 
				|| (isset($user['last_name']) && $user['last_name'])
				) 
				{
					$user_fullname = $user['first_name'] ? $user['first_name'] . ' ' . $user['last_name'] : $user['last_name'];
				}
			  	$from = array('name' => $_config['site_emails'][0]['name'], 'email' => $_config['site_emails'][0]['email']);

				$to = array('name' => $user_fullname, 'email' => $user['email']); 
				$mailer = new email($from);

				$message = "<p>Hello {$user_fullname},</p><p>We have received a request to provide you with a Temporary Password so you can log in to your Account.<br>The Temporary Password provided below will become invalid in a few minutes or after your next Login, so be sure to change your Password. You can Log In at <a href='{$_config['site_path']}admin'>{$_config['company_name']} Login</a></p><table><tr><td>Temporary Password</td><td>{$newPassword}</td></tr></table><br><p><em>Note:</em>If you did not make this request, you don't need to do anything. Your password will not be changed and the Temporary Password will expire in a few minutes.</p>";
					
				

				$result = $mailer->send($to, $_config['company_name'] . " Password Reset", $message);
					
				if(!$result) 
					$error = "There was an email delivery error, we were unable to send the request to the Username provided. Please try again or contact <a href='mailto:{$_config['site_emails'][0]['email']}'>{$_config['site_emails'][0]['name']}</a> for assistance.";
				else 
					$success = "<p>A Temporary Password has been sent to the Email Address provided.</p><p><em>Note:</em>For security purposes, the temporary password will expire in a few minutes. Be sure to sign in and change your password once the email arrives. Don't forget to check your spam folder if it doesn't arrive immediately</p>";
			}
			
			
		}
	}
	// if failed at any point, write error message
	if( !$success)	$error = $error ? $error : "<p>No valid user selected please try again</p>";

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


</head>

<body>
<!-- Site Login Wrapper -->
<div class="login_wrapper">

<!-- Login Error -->
<?php 	
$formData = array(
	'username' => isset($_POST['username']) ? $_POST['username'] : ''
);
if($success) echo "<div class='logout'>{$success}</div>";
if($error) echo "<div class='error'>{$error}</div>";
ajax_form("form_login.php #form_forgot", $formData);
 
writeMsg();	
?>
    	
 


    </div>
<!--  End Site Login Wrapper -->
</div>
<!-- Copyright -->
<div class="login_copyright">&copy; Copyright <?php echo date("Y"); ?> <a href="http://test.com">Test</a>.
</div>
<script type="text/javascript">
$('.ajaxForm').each(function(){
	$(this).load($(this).attr('data-location'),$.parseJSON($(this).attr('data-post')) )
});
</script>
</body>
</html>

<?php 

function addMsg($txt)
{
	global $msgs,$_config;
	if($_config['debug'])
	$msgs[] = $txt;
}

function writeMsg()
{
	global $msgs;
?>
<div style="background:white;">
<?php
	foreach($msgs as $msg)
		echo "<p>{$msg}</p>";
?>
</div>
<?php
}
