<?php 
// process the email inquiry

$errors = false;
$sent = false;
if(isset($_POST['emailform'])){
	$mail_to = $_config['forms_email']; // recipient of the sign-up form
	$mail_title = 'Web Enquiry to Hawkeye Logs';
	

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
		/*
		?>
		
		<script type="text/javascript">
						<!--
						window.location = "thank-you"
						//-->
		</script>
        <?php  */
	}
	//header('Location: thank-you');
}

$contact_page = $pages->get_page_by_slug(uri::get(0));
$content = $contact_page['content'];
$hasSidebar = true;
?>
<!-- ************* START OF CONTENT AREA ********************* -->


<div id="content_area" class="wrap contact">

	<div id="sidebar">
        <!--sidebar info here-->
	</div>
    

	<div id="content"> 

        
		<?php display_content($content);  ?>
		
			
		
		
		</div><!-- content -->
		
		<div style="clear: both; margin-bottom: 3.5em;"></div>
		
		
		
		
		
		


<div id="inquiry">
		
		<?php 
		if(!empty($errors)) {
			echo "<script>$('html, body').animate({scrollTop: $(\"#inquiry\").offset().top}, 2000);</script>";
			foreach($errors as $field => $value) {
				echo "<h3 style='color:red;font-weight:bold;'>$field : $value</h3>";
			}
		}
		echo $sent ? "<p style='margin-top:1em; color:green;'>Your request for information has been sent. <br />We will be in touch with you soon. Thank you for your interest.</p>" : "" ; 
		
		$formData = array(
			'errors' => $errors,
			'post' => $_POST,
			'sent' => (int) $sent,
			'referrer' => $_SERVER['REQUEST_URI']
		);
		ajax_form($_config['site_path']."external/contact_form.php", $formData);
		?>
		<!-- <p class="required">* Required</p> -->
		
	</div>

	
		
<div style="clear: both; margin-bottom: 1.5em;"></div>		
		
		
	

		
		
		
		
	
	<div style="clear:both"></div>	
	
	



	
</div>	<!-- content_area -->
<div style="clear:both"></div>

<!-- ************* END OF CONTENT AREA ********************* -->
