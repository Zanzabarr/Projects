<?php 
// tell PHP to log errors to ipn_errors.log in this directory
ini_set('log_errors', true);
ini_set('error_log', 'ipn_errors.txt');
error_reporting(E_ALL ^ E_NOTICE);

include('../../../includes/config.php');


$isSandbox = true;
$paypal_email = "can-business@hotmail.com";

$validated = validate_paypal_listener($paypal_email, $isSandbox);


my_log("status: " . $validated['status']);
$output = '';
foreach($validated['ipn_data'] as $key => $data)
{
	$output .= "\r\n{$key}: {$data}";
}
my_log("ipn_data: " . $output);
my_log("error_msg: " . $validated['error_msg']);
my_log("error_no: " . $validated['error_no']);


	
function validate_paypal_listener($paypal_email, $isSandbox = false)
{
	$sandboxWord = $isSandbox ? '.sandbox' : '';
	$ipn_data = array();
	
	// Reading POSTed data directly from $_POST causes serialization issues with array data in the POST.
	// Instead, read raw POST data from the input stream.
	$raw_post_data = file_get_contents('php://input');

	$raw_post_array = explode('&', $raw_post_data);
	$myPost = array();
	foreach ($raw_post_array as $keyval) {
		$keyval = explode ('=', $keyval);
		if (count($keyval) == 2)
			$myPost[$keyval[0]] = urldecode($keyval[1]);
	}

	// read the IPN message sent from PayPal and prepend 'cmd=_notify-validate'
	$req = 'cmd=_notify-validate';
	if(function_exists('get_magic_quotes_gpc')) 
	{
		$get_magic_quotes_exists = true;
	}
	foreach ($myPost as $key => $value) 
	{
		if($get_magic_quotes_exists == true && get_magic_quotes_gpc() == 1) 
		{
			$ipn_data[$key] = stripslashes($value);
			$value = urlencode(stripslashes($value));
		} else {
			$ipn_data[$key] = $value;
			$value = urlencode($value);
		}
		$req .= "&$key=$value";
	}

	// post back to PayPal system to validate
	$header = "POST /cgi-bin/webscr HTTP/1.0\r\n";
	$header .= "Host: www{$sandboxWord}.paypal.com\r\n";
	$header .= "Content-Type: application/x-www-form-urlencoded\r\n";
	$header .= "Content-Length: " . strlen($req) . "\r\n\r\n";
	
	$fp = fsockopen ("ssl://www{$sandboxWord}.paypal.com", 443, $errno, $errstr, 30);

	if (!$fp) {
		return array("status" => false, "ipn_data" => $ipn_data, "error_msg" => $errstr, "error_no" => $errno);
	} 
	else 
	{
		fputs ($fp, $header . $req);
		while (!feof($fp)) 
		{
			$res = fgets ($fp, 1024);
			
			// we are expecting only two values here, if not 'verified' this is bad data
			$status = false;
			if (strcmp ($res, "INVALID") == 0) 
			{
				return array("status" => false, "ipn_data" => $ipn_data, "error_msg" => 'Not Verified By Paypal', "error_no" => '');
			} 
			elseif(strcmp ($res, "VERIFIED") == 0)
			{
				$status = true;
			}
		}
		fclose ($fp); 
		
		if(!$status)
				return array("status" => false, "ipn_data" => $ipn_data, "error_msg" => 'Not Verified By Paypal', "error_no" => '');
		
		// ok, now some validation against the table that I'm setting up
		logged_query("
CREATE TABLE IF NOT EXISTS `paypal_ipn` (
  `txn_id` varchar(250) COLLATE utf8_unicode_ci NOT NULL,
  `date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`txn_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
",0,array());
		
		// first, do some maintenance on the table:
		// get rid of all records older than 4 days: paypal stops sending Instant Processing Notifications after 5 days.
		logged_query("DELETE FROM `paypal_ipn` WHERE `date` < NOW() - interval 5",0,array());
		
		// if this transaction's payment_status isn't complete return false
		// we are only interested in completed payments		
		if(strtolower($ipn_data['payment_status']) != 'completed')
			return array("status" => false, "ipn_data" => $ipn_data, "error_msg" => 'Payment not Completed', "error_no" => '');
		
		// if receiver_email doesn't match this email: error_log and return false
		if(strtolower($ipn_data['receiver_email']) != $paypal_email)
		{
			return array("status" => false, "ipn_data" => $ipn_data, "error_msg" => "Payment sent to wrong user", "error_no" => $ipn_data['receiver_email']);
			
			// log the hell out of this
			
			// email ??
		}
		
		// if this transaction already exists and is complete return false (duplicate notification)
		$result = logged_query("SELECT * FROM `paypal_ipn` WHERE `txn_id` = :txn_id",0,array(":txn_id" => $ipn_data['txn_id']));
		if($result === false) 
		{
			// there was an error writing to the db: mail and log full details of this ipn in case server issues has led to a missed payment
			
			return array("status" => false, "ipn_data" => $ipn_data, "error_msg" => "Payment already processed", "error_no" => '');
		}
		elseif (is_array($return) && count($return))
		{	
			// a paid transaction already exists: don't do anything more with this one...we've already processed it!
			return array("status" => false, "ipn_data" => $ipn_data, "error_msg" => "Payment already processed", "error_no" => '');
			
				
		}
		
		
		// otherwise post and return true but more validation required
		$result = logged_query("INSERT INTO `paypal_ipn` (`txn_id`) VALUES (:txn_id)",0,array(":txn_id" => $ipn_data['txn_id']));
		
		
		return array("status" => true, "ipn_data" => $ipn_data, "error_msg" => '', "error_no" => '');
	}
}
