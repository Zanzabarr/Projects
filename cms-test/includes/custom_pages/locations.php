<?php 
// process the email inquiry

$errors = false;
$sent = false;
if(isset($_POST['emailform'])){
	$mail_to = $_config['forms_email']; // recipient of the sign-up form
	$mail_title = 'Web Enquiry to Peak Golf';
	

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


$locations_page = $pages->get_page_by_slug(uri::get(0));
$content = $locations_page['content'];
$hasSidebar = false;

?>
<!-- ************* START OF CONTENT AREA ********************* -->

<div id="content_area" class="wrap">


	<div id="content" style="float:left;  margin-left:3%; margin-right:3%; width:94%">
		<?php display_content($content); 
				
		?>
		
		
		
<div class="locationColLeft">

<p><strong>PEAK GOLF North Vancouver</strong><br />
Phone: <a href="tel:1.604.980.8899">604-980-8899</a><br />
Address:<br />
1199 Marine Drive<br />
North Vancouver BC<br />
V7P 1T1</p>

<p><a href="mailto:shop@peakgolf.ca">shop@peakgolf.ca</a><br />
Monday to Saturday 9:30 to 6pm<br />
Sunday 10am to 5pm</p>

</div><!--left col-->
		
		
<div class="locationColRight">

<iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d2600.494074294042!2d-123.1076144!3d49.32386410000002!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x548671d246b9da99%3A0x15f9888cdd868e95!2s1199+Marine+Dr%2C+North+Vancouver%2C+BC+V7P+1S8!5e0!3m2!1sen!2sca!4v1405883124042" width="100%" height="450" frameborder="0" style="border:0"></iframe>

</div><!--right col-->		
		
<div style="clear: both; margin-bottom: 3.5em;"></div>		
		
		
<div class="locationColLeft">

<p><strong>PEAK GOLF KITSILANO</strong><br />
Phone: <a href="tel:1.604.980.8899">604-980-8899</a><br />
Closed for the Season<br />
See you in April 2015</p>

<!--<p><a href="mailto:kits@peakgolf.ca">kits@peakgolf.ca</a><br />
Store Hours:<br />
Monday to Saturday 10am to 6pm<br />
Sunday 11am to 5pm</p>-->

</div><!--left col-->		
		
		
<div class="locationColRight">

<iframe src="https://www.google.com/maps/embed?pb=!1m14!1m8!1m3!1d20824.955924729788!2d-123.14927099999998!3d49.274133!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x548673b42d1db77f%3A0x100bd3631b9ea91d!2s2077+W+4th+Ave%2C+Vancouver%2C+BC+V6J+1N3!5e0!3m2!1sen!2sca!4v1405883376253" width="100%" height="450" frameborder="0" style="border:0"></iframe>

</div><!--right col-->		
		
<div style="clear: both;"></div>		
		
	
	
		
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
		
		
		
    </div><!-- content -->

	<div style="clear:both"></div>
</div>	<!-- content_area -->


<!-- ************* END OF CONTENT AREA ********************* -->
<?php 
