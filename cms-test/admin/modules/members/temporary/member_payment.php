<?php
	// relevent paypal data
    $paypalEmail = 'golfbeyondtheswing@gmail.com';
    $itemName = 'GBTS Membership';
    $memberEmail = isset($_GET['email']) ? urldecode(mysql_real_escape_string($_GET['email'])) : '';
    $membershipCost = 19.95;
    $notifyURL = $_config['site_path'] . 'includes/members/paypal/listener.php';
    $successURL = $_config['site_path'];
    $cancelURL = $_config['site_path'];
	
	// check to see that memberEmail is in database as an upnaid signup
	$members=logged_query_assoc_array("SELECT * FROM `members` WHERE `id` > 0 AND `unpaid_signup` = 1 AND `email` = '{$memberEmail}' ORDER BY `email` ASC LIMIT 1; ");
	
	if(count($members) > 0) {
		$isUnpaidSignup = true;
	}
	else {
		$isUnpaidSignup = false;
	}
?>

<?php  if($isUnpaidSignup) { ?>
	<h1 style="margin-bottom:0;"><strong>$19.95</strong><span style="font-style:italic;"> ONE-TIME</span> MEMBERSHIP FEE</H1>
	<h2 style="margin-bottom:1em;font-style:italic;font-weight:normal;">gives you access to member-only content, instructional videos, tips, and more!</h2>
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
		<input type="hidden" name="rm" value="1">
		<input type="hidden" name="return" value="<?php echo $successURL; ?>">
		<input type="hidden" name="cancel_return" value="<?php echo $cancelURL; ?>">
		<input type="hidden" name="tax_rate" value="0.000">
		<input type="hidden" name="shipping" value="0.00">
		<input type="hidden" name="bn" value="PP-BuyNowBF:btn_paynowCC_LG.gif:NonHosted">
		<input type="image" src="https://www.paypal.com/en_US/i/btn/btn_paynowCC_LG.gif" border="0" name="submit" alt="PayPal - The safer, easier way to pay online!">
		<img alt="" border="0" src="https://www.paypal.com/en_US/i/scr/pixel.gif" width="1" height="1">
	</form>

		
<?php } else { ?>
	<p class="error">Membership Payment Page Error</p>
<?php } ?>