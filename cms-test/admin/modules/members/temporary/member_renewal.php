<?php

if(!array_key_exists('submit-renewal', $_POST)) {
	// show renew form
	showRenewForm();
}
else {
	$errors = validateRenewForm();
	if(count($errors) != 0) {
		showRenewForm($errors);
	}
	else {
		processRenewForm();
	}
}
?>

<?php
/*****************/
/* PHP FUNCTIONS */
/*****************/

//---------------------------------------------------------------//
function showRenewForm($errors = array()) { 
    $formActionUrlArr = explode('?', $_SERVER['REQUEST_URI']);
    $formActionUrl = $formActionUrlArr[0];

    if(!empty($errors)) {
        foreach($errors as $error) {
            echo '<p class="error">'.$error.'</p>';
        }
    }
	else {
		echo '<p>Please enter your username to renew your Alpine Garden Club membership. Note that only existing memberships that are deemed eligible for renewal will be accepted (an expired membership or one that is within 1 month of expiry).</p>';	
	}
    ?>	
    
    <form id="renewal-form" action="<?php echo $formActionUrl; ?>" method="POST">
        <label>Username:</label>
        <input type="email" name="renew_email" id="email" value="<?php echo (isset($_POST['rewnew_email']) && empty($errors))? $_POST['renew_email'] : ''; ?>" />

        <input type="submit" name="submit-renewal" id="submit-renewal" value="Renew Membership" />
    </form>

<?php } 


//---------------------------------------------------------------//
function validateRenewForm() {
    $errors = array();
	
    if(!isset($_POST['renew_email']) || !preg_match('/^[a-zA-Z0-9._-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,4}$/', $_POST['renew_email'])) {
        $errors['renewal'] = 'Invalid Username';
    }    
	else {
		$monthInSeconds = 60 * 60 * 24 * 30;
		$renewalWindow = $monthInSeconds * 10; // 300 days
		$member_email = mysql_real_escape_string($_POST['renew_email']);
		
		// username of valid format (email), check to see if exists and eligible for renewal
		$members=logged_query_assoc_array("SELECT * FROM `members` WHERE `id` > 0 AND `email` = '{$member_email}' ORDER BY `email` ASC LIMIT 1; ");
		
		if(count($members) <= 0) {
			$errors['renewal'] = 'Username does not exist';
		}
		elseif(time() < (strtotime($members[0]['membership_expiry']) - $renewalWindow)) {
	    	$errors['renewal'] = 'Membership not eligible for renewal (Needs to be closer to expiry date or expired)';	
		}
	}

    return $errors;
}


//---------------------------------------------------------------//
function processRenewForm() {
	// output paypal pay button for renewal
	    
	global $_config;
		
	// relevent paypal data
    $paypalEmail = 'agcbc@shaw.ca';
    $itemName = 'Alpine Garden Club Membership Renewal';
    $memberEmail = isset($_POST['renew_email']) ? mysql_real_escape_string($_POST['renew_email']) : '';
    $membershipCost = 30.00;
    $notifyURL = $_config['site_path'] . 'includes/members/paypal/listenerRenewal.php';
    $successURL = $_config['site_path'] . 'membership-renewal-success';
    $cancelURL = $_config['site_path'];
	?>
    
    <p>To renew your membership, please click the link below to paypal and proceed with payment.</p>
    
	<form id="payment-form" action="https://www.paypal.com/cgi-bin/webscr" method="post" name="paypal">
    	<input type="hidden" name="cmd" value="_xclick">
        <input type="hidden" name="business" value="<?php echo $paypalEmail; ?>">
        <input type="hidden" name="lc" value="CA">
        <input type="hidden" name="item_name" value="<?php echo $itemName; ?>">
        <input type="hidden" name="custom" value="<?php echo $memberEmail; ?>">
        <input type="hidden" name="amount" value="<?php echo $membershipCost; ?>">
        <input type="hidden" name="notify_url" value="<?php echo $notifyURL; ?>">
        <input type="hidden" name="currency_code" value="CAD">
        <input type="hidden" name="button_subtype" value="services">
        <input type="hidden" name="no_note" value="1">
        <input type="hidden" name="no_shipping" value="1">
        <input type="hidden" name="rm" value="2">
        <input type="hidden" name="return" value="<?php echo $successURL; ?>">
        <input type="hidden" name="cancel_return" value="<?php echo $cancelURL; ?>">
        <input type="hidden" name="bn" value="PP-BuyNowBF:btn_buynow_LG.gif:NonHosted">
    
        <input type="image" src="https://www.paypalobjects.com/en_US/i/btn/btn_buynowCC_LG.gif" border="0" name="submit" alt="PayPal Buy Now">
    
        <img style="border:none;" alt="" border="0" src="https://www.paypalobjects.com/en_US/i/scr/pixel.gif" width="1" height="1" />

	</form>
    
<?php } ?>