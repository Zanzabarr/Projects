<?php
include("../../../includes/config.php");
$notifyURL = $_config['admin_url'] . 'modules/members/paypal/listener.php';
$successURL = $_config['site_path'] . "members/payment";
$cancelURL = $_config['site_path'] . "members/payment";




// create a new cURL resource
$ch = curl_init();

// set URL and other appropriate options
curl_setopt($ch, CURLOPT_URL, $_config['admin_url']);
curl_setopt($ch,CURLOPT_POST, 6);
curl_setopt($ch,CURLOPT_POSTFIELDS, "cmd=_s-xclick?business=can-business@hotmail.com?amount=12?notify_url={$notifyURL}?item_name=test?submit");
//curl_setopt($ch, CURLOPT_HEADER, 0);

// grab URL and pass it to the browser
curl_exec($ch);

// close cURL resource, and free up system resources
curl_close($ch);
die();


?>
	<form id="payment-form" action="https://www.paypal.com/cgi-bin/webscr" method="post" name="paypal">
		<input type="hidden" name="cmd" value="_xclick">
		<input type="hidden" name="business" value="can-business@hotmail.com">
		<input type="hidden" name="lc" value="CA">
		<input type="hidden" name="item_name" value="test">
		<input type="hidden" name="custom" value="rosler19@hotmail.com">
		<input type="hidden" name="amount" value="12">
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
	<script>
        document.paypal.submit();
    </script>
