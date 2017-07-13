<?php 
// process the email inquiry
$errors = false;
$sent = false;
if(isset($_POST['emailform'])){
	$mail_to = $_config['forms_email']; // recipient of the sign-up form
	$mail_title = 'Newsletter Signup for Peak Golf';
	

	// all required field's names
	$required = array('name', 'email');

	// all email field's names
	$validate_email = array('email');
	// if errors exist, $errors will become an array loaded with error data
	

	$mail_from   	= $mail_to;

/*
	$mail_to		 	= $mail_to_name ? $mail_to_name . '<' . $mail_to_address .'>' 
							: $mail_to_address;
	$mail_from			= $mail_from_name ? $mail_from_name . '<' . $mail_from_address .'>' 
							: $mail_from_address;							
*/
	$sent = false;		
		
	$emailtxt = '<table>';
		
		
	// gather email data and validation data
	foreach($_POST as $k => $v){
			
		if($k != 'submit'){
			// validate form data
			if (trim($v) == '' && in_array($k, $required) ) $errors[$k] = 'Required Field';
			elseif (in_array($k, $validate_email) && ! check_email_address( trim($v) ) ) $errors[$k] = 'Please Supply A Valid Email Address';
				
			$heading = ucwords(str_replace('_', ' ', $k));
			$message[$k] = htmlspecialchars(trim($v));
			$emailtxt .= $v ? "<tr><td style='padding-right:5px;'>{$heading}:</td><td>{$v}</td></tr>" : '';
		}
			
	}$emailtxt .= "</table>";
	$headers  = $mail_from ? "From: {$mail_from}\n" : '';  
	$headers .= 'MIME-Version: 1.0' . "\n";
	$headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n"; 
	
	// if it didn't validate, don't send: duh
	if (! $errors)
	{
		mail($mail_to, $mail_title, $emailtxt, $headers);
		$sent = true;
		?>
		<script type="text/javascript">
						<!--
						window.location = "thank-you"
						//-->
		</script>
        <?php
	}
	//header('Location: thank-you');
}
?>