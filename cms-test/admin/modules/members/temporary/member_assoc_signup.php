<?php
$mail_title = 'Golf Beyond the Swing Membership';
$mail_message = '<p>Thankyou for signing up with <strong>Golf Beyond the Swing</strong>. To activate your membership, please follow the Paypal link provided and follow the instructions to pay the required amount with your Paypal account or Visa/Master Card.</p>';

// all required field's names
$required = array('email');

// if errors exist, $errors will become an array loaded with error data
$errors = false;

$mail_from_name 	= 'Golf Beyond the Swing - Membership';
$mail_from_address	= $_config['forms_email'];

$mail_from			= $mail_from_name . ' <' . $mail_from_address .'>';
													
$sent = false;
						
if(isset($_POST['submit-signup'])){
	
		$emailtxt = $mail_message;
		
		// validate form data
		foreach($_POST as $k => $v){
			if($k != 'submit-signup'){
				if (trim($v) == '' && in_array($k, $required) ) $errors[$k] = 'Required Field';
				elseif ($k == 'email' && !check_email_address( trim($v) ) ) $errors[$k] = 'Please Supply A Valid Email Address';
				elseif ($k == 'email' && !unique_email_address( trim($v) ) ) $errors[$k] = 'That Email Address Has Already Signed Up';
			}
		}
		
        $headers  = $mail_from ? "From: {$mail_from}\r\n" : '';  
		$headers .= 'MIME-Version: 1.0' . "\r\n";
		$headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n"; 
		
		// if it didn't validate, don't send
		if (!$errors)
		{
            // valid email. Send mail and setup member account

            // member account setup

            // set password
            $passwordLength = 8; 
            $passwordChars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
            
            $randomPassword = substr(str_shuffle($passwordChars), 0, $passwordLength);
            $encryptedPassword = hasher($randomPassword); // encrypt password

            // set email
            $email = $_POST['email'];
			$first_name = $_POST['first_name'];
			$last_name = $_POST['last_name'];

			/* NOT FOR THIS SITE 
            // set url
            $url = trim($email);
            $url = preg_replace('/\s+/', ' ', $url); // strip extra whitespace

            $urlArray = explode('@', $url);
            $url = $urlArray[0]; // strip all characters at and after '@' character

            $url = preg_replace('/[\s\W]/', '-', $url); // change any spaces or non-word chars to hyphens
            $url = preg_replace('/-+/', '-', $url); // get rid of consecutive hyphens
            $url = preg_replace('/^-/', '', $url); // get rid of leading hyphen
            $url = preg_replace('/-$/', '', $url); // get rid of trailing hyphen

            $members = logged_query_assoc_array("SELECT * FROM `members` WHERE `id` > 0 ORDER BY `email` ASC");
            $memberUrls = array();

            // get all member urls
            foreach ($members as $member)
            {
                $memberUrls[$member['url']] = $member;
            }

            $urlCount = 0;
            $tempUrl = $url;

            // check to see if url already exists for another member. If it does, increment counter
            while(array_key_exists($url, $memberUrls))
            {
                $urlCount++;
                $url = $tempUrl . '_' . $urlCount;
            }

            // set default membership expiry date for new members (end of current year)
            //$currYear = date('Y');*/
            $membership_expiry = '2100-12-31 23:59:59'; // end of civilization for this site

			
            // set status
            $status = 0;

            // set password change request
            $pw_change_request = 1;

            // set unpaid signup flag
            $unpaid_signup = 1;

            logged_query("INSERT INTO `members` (`first_name`,`last_name`, `email`, `password`, `status`, `pw_change_request`, `unpaid_signup`, `membership_expiry`) 
                          VALUES ('$first_name', '$last_name', '$email', '$encryptedPassword', '$status', '$pw_change_request', '$unpaid_signup', '$membership_expiry');");
       
            // set rest of email text
            $emailtxt .= '<br /><p>Paypal link: <a href="'.$_config['site_path'].'associate-membership-payment?email='.urlencode($email).'">One-time Membership Fee</a></p><br />';
            $emailtxt .= '<p>Once you have paid, you will be able to login using the login information below. Please note that the password provided is temporary, and that you will need to change your password upon login.</p><br /><p>The "fine print": By Joining <strong>Golf Beyond The Swing</strong> you agree to the Terms and Conditions outlined on the site and to maintain the security of your login and password, you are fully responsible for all use of your account. Sharing of content and/or your Membership login and password to any person for the purpose of enabling a non-member to utilize any or all membership content from the site is strictly prohibited. You further acknowledge breach of this agreement will result in termination of membership.</span></p></strong><br /><br />';
            $emailtxt .= '<strong>Username (Your email):</strong> ' . $email . '<br />';
            $emailtxt .= '<strong>Temporary Password:</strong> ' . $randomPassword;

            // send mail
			mail($email, $mail_title, $emailtxt, $headers);
			$sent = true;
		}
}
?>

<script type="text/javascript">
function isValidEmailAddress(emailAddress) {
	var pattern = new RegExp(/^[+a-zA-Z0-9._-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,4}$/i);
	return pattern.test(emailAddress);
};

$(document).ready(function() {
	$('#signup_form').submit(function () {
		// validate email address format
		var email = $('.email').val();
		if(!isValidEmailAddress(email)){
			alert('Please fill in a valid email.');
			return false;
		}
	});
});

</script>

<?php if ($sent) { ?>

                    <script type="text/javascript">
						<!--
						window.location = "signup-thankyou"
						//-->
					 </script>


<?php } else { ?>
    <form action="<?php echo $_SERVER['REQUEST_URI']; ?>" method="post" enctype="application/x-www-form-urlencoded" name="signup_form" id="signup_form" style="min-width:290px; width:60%; ">
    <?php
    if(isset($errors['email'])) { ?>
	<p class="error"><?php echo $errors['email']; ?></p>
    <?php } else { ?>

    <?php } ?>
	<div style="clear:both;height:1em;"></div>
	<input type="text" name="first_name" id="first_name" value="<?php echo isset($_POST['first_name']) && !$sent ? trim($_POST['first_name']) : '' ; ?>" placeholder="FIRST NAME *" required /><br />
	
	<input type="text" name="last_name" id="last_name" value="<?php echo isset($_POST['last_name']) && !$sent ? trim($_POST['last_name']) : '' ; ?>" placeholder="LAST NAME *" required /><br />
	
    <input type="text" name="email" id="email" class="email" value="<?php echo isset($_POST['email']) && !$sent ? trim($_POST['email']) : '' ; ?>" placeholder="EMAIL *" required /><br />
    <input name="submit-signup" type="submit" id="submit-signup" value="Signup" />
</form>
<?php } ?>


<?php

// PHP FUNCTIONS //
function unique_email_address($email_address) {
	$email_address = strtolower($email_address);
    $member = logged_query_assoc_array("SELECT * FROM `members` WHERE `id` > 0 AND LOWER(`email`) = '{$email_address}'");
    
	// if this is a non-member eBulletin subscriber, delete the subscription and ok them for application.
	/*if ($member && count($member) > 0 )
	{
		if ($member[0]['eBulletin'] == 2 ) 
		{
			cancel_member($member[0]['id']);
			return true;
		}	
		else return false;
	}*/
	if($member && count($member) > 0) {
		return false;
	} else {
		return true;
	}
	//return true;
}

function cancel_member($id)
{
	logged_query("
		UPDATE `members`
		SET `id` = -{$id}
		WHERE `id` = {$id}
	");
}
