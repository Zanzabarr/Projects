<?php
// tell PHP to log errors to ipn_errors.log in this directory
ini_set('log_errors', true);
ini_set('error_log', 'ipn_errors.txt');

include('../../../includes/config.php');

error_reporting(E_ALL);
$email = $_config['orderemail'];
$mail_from = $email;
$headers  = $mail_from ? "From: {$mail_from}\n" : '';
$headers .= 'MIME-Version: 1.0' . "\n";

$emailtext = "";

// information about the posted data
$errorPOST = "CONFIRMATION DATA from Paypal\n\nThis is the complete set of confirmation data sent from Paypal that resulted in this error.\n\n";
foreach ($_POST as $key => $value){
	$errorPOST .= $key . " = " .$value ."\n\n";
}
//mail($email, 'post data', $errorPOST);

// STEP 1: Read POST data
 
// reading posted data directly from $_POST causes serialization
// issues with array data in POST
// reading raw POST data from input stream instead.
$raw_post_data = file_get_contents('php://input');
$raw_post_array = explode('&', $raw_post_data);

$myPost = array();
foreach ($raw_post_array as $keyval) {
  $keyval = explode ('=', $keyval);
  if (count($keyval) == 2)
     $myPost[$keyval[0]] = urldecode($keyval[1]);
}

// read the post from PayPal system and add 'cmd'
$req = 'cmd=_notify-validate';
if(function_exists('get_magic_quotes_gpc')) {
   $get_magic_quotes_exists = true;
} 
foreach ($myPost as $key => $value) {        
   if($get_magic_quotes_exists == true && get_magic_quotes_gpc() == 1) { 
        $value = urlencode(stripslashes($value)); 
   } else {
        $value = urlencode($value);
   }
   $req .= "&$key=$value";
}

// STEP 2: Post IPN data back to paypal to validate
$sandbox = isset($_config['sandbox']) && $_config['sandbox'] ? '.sandbox' : '';
$ch = curl_init("https://www{$sandbox}.paypal.com/cgi-bin/webscr");
curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
curl_setopt($ch, CURLOPT_POSTFIELDS, $req);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 1);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
curl_setopt($ch, CURLOPT_FORBID_REUSE, 1);
curl_setopt($ch, CURLOPT_HTTPHEADER, array('Connection: Close'));
 
// In wamp like environments that do not come bundled with root authority certificates,
// please download 'cacert.pem' from "http://curl.haxx.se/docs/caextract.html" and set the directory path 
// of the certificate as shown below.
// curl_setopt($ch, CURLOPT_CAINFO, dirname(__FILE__) . '/cacert.pem');
if( !($res = curl_exec($ch)) ) {
    // error_log("Got " . curl_error($ch) . " when processing IPN data");
	my_log("Curl Error Got " . curl_error($ch) . " when processing IPN data");	
    curl_close($ch);
    exit;
}
curl_close($ch);

// STEP 3: Inspect IPN validation result and act accordingly
 
if (strcmp ($res, "VERIFIED") == 0) {
    // check whether the payment_status is Completed
    // check that txn_id has not been previously processed
    // check that receiver_email is your Primary PayPal email
    // check that payment_amount/payment_currency are correct
    // process payment

    // assign posted variables to local variables
	foreach($_POST as $k => $v) {
		if($k != "mc_gross" && $k != "mc_currency") {
			$$k = $v;
		} else {
			switch($k) {
				case "mc_gross":
					$payment_amount = $v;
					break;
				case "mc_currency":
					$payment_currency = $v;
					break;
			}
		}
	}
	// Set Our Local Variables
	$paypal_email = isset($_config['sandbox']) && $_config['sandbox'] ? strtolower($_config['store_id']) : strtolower(decrypt($_config['store_id']));
	
	// STEP 3a: Validate Results

	//does this transaction id already exist?
	$txn_exists = logged_query("SELECT * FROM `ecom_orders` WHERE `txn_id` = '{$txn_id}'",0,array());
	// if transaction has already been processed, don't do anything more: probably just an asynchronous second signam from paypal
	if($txn_exists) die();
	
	$error_str = '';
	if(strtolower($payment_status) != 'completed') $error_str .= "Transaction Not Completed by Purchaser\n\n";
	// check email
	if($receiver_email != $paypal_email) 
		$error_str .= "Receiver Email: {$receiver_email}\ndoes not match\nPaypal Account Email: {$paypal_email}\n\n";
	
	// get the order data from db
	$order = logged_query("SELECT * FROM `ecom_orders` WHERE `id` = :custom",0,array(":custom" => $custom));
	$order = $order[0];
	
	if(!$order) $error_str .= "Purchase #{$_POST['custom']} not found.\n\n";
	else // can only check this error if there is order info found
	{
	// check amount charged
		if($payment_amount != $order['total']) $error_str .= "Customer was charged \${$order['total']} but paid \${$payment_amount} through paypal. Possibly caused by customer hacking the order amount.\n\n";
	}
	// check currency type
	if( $payment_currency != $_config['currency']) $error_str .= "Expecting currency in {$_config['currency']} but was paid in {$payment_currency} through paypal. Possibly caused by customer hacking the currency type.\n\n";
	
	if ($error_str)
	{
		$error_intro = "There was an error receiving purchase confirmation from PAYPAL for {$_config['company_name']}.\n\nPLEASE NOTE THE FOLLOWING ERRORS:\n\n";
		mail($email, 'Purchase Error', $error_intro . $error_str . $errorPOST);
		die();
	}
		
	// no errors, lets get the data
	$x = unserialize($order['info']);
	
	$emailtext .= "ITEMS \n ----------- \n\n";
	
	foreach ($x as $key => $value){
		$emailtext .= $key . ":\n";
		$emailtext .= "-Quantity: ".$value['count']."\n";
		if( isset($value['option']) && $value['option'] != 'none'){
			$emailtext .= "-Options: ";
			foreach ($value['option'] as $g => $h){ 
				$emailtext .= $g . " = " .$h ."\n";
			}
		}else{
			$emailtext .= "-Options: None\n";
		}
		$emailtext .= "\n\n";
	} 
	//$emailtext .= $order;
	$tax = isset($_POST['tax']) && $_POST['tax'] ? $_POST['tax'] : 0;
	$shipping = isset($_POST['mc_handling']) && $_POST['mc_handling'] > 0 ? $_POST['mc_handling'] : 0;
	if( $tax > 0 || $shipping > 0) 
	{
		$subtot = $order['total'] - $tax - $shipping;
		$emailtext .= "SUB-TOTAL : \${$subtot}\n\n ";
		if($tax > 0) $emailtext .= "SALES TAX : \${$tax}\n\n ";
		if($shipping > 0) $emailtext .= "SHIPPING : {$order['ship_method']} :  \${$shipping}\n\n ";
	}
	$emailtext .= "TOTAL : $".$order['total']."\n\n" ;
	$emailtext .= "\nADDRESS \n ----------- \n\n";			
	$emailtext .= $_POST['address_name']."\n";
	$emailtext .= $_POST['address_street']."\n";
	$emailtext .= $_POST['address_city']." ,".$_POST['address_state']." ".$_POST['address_zip']."\n";
	$emailtext .= $_POST['address_country']."\n";
	$emailtext .= "\n\n Customer Email \n ----------- \n\n";
	$emailtext .= $_POST['payer_email']."\n";
	$emailtext .= "\n\n Order ID \n ----------- \n\n";
	$emailtext .= $_POST['custom']."\n";
	
	// email the success message
	mail($email, $_config['company_name']." Order # {$_POST['custom']}", $emailtext, $headers);
	
	// email full $_POST return for debug purposes
	/*$pp_return = "";
	foreach($_POST as $k=>$v) {
		$pp_return .= "$k : $v\n";
	}
	mail($email, $_config['company_name']." Paypal IPN Return", $pp_return, $headers);*/
	
	// update the purchase as confirmed
	logged_query("UPDATE `ecom_orders` SET `confirm` = '1' WHERE `id` = :custom",0,array(":custom" => $_POST['custom']));
	
	/* send receipt/download email to customer */
	$headers .= 'Content-type: text/plain; charset=iso-8859-1' . "\r\n";
	
	$custmailtxt = "Thank you for ordering from {$_config['company_name']}\r\n\r\n";
	$custmailtxt .= $emailtext;

	mail($_POST['payer_email'],"Your Order from {$_config['company_name']}", $custmailtxt, $headers);
	/**/
} else if (strcmp ($res, "INVALID") == 0) {
    // log for manual investigation
	$logtext = "POST DATA\n\n";
	foreach ($_POST as $key => $value){
		$logtext .= $key . " = " .$value ."\n\n";
	}
	my_log('Invalid Ecom Order Error' . $logtext);
}
?>