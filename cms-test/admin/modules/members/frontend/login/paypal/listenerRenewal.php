<?php 
// tell PHP to log errors to ipn_errors.log in this directory
ini_set('log_errors', true);
ini_set('error_log', 'ipn_errors.txt');

include('../../config.php');

error_reporting(E_ALL ^ E_NOTICE); 
$email = $_POST['custom'];

$header = ""; 
$emailtext = ""; 
$mail_From = "From: info@agc.com";

// Read the post from PayPal and add 'cmd' 
$req = 'cmd=_notify-validate';
foreach ($_POST as $key => $value) {
	$value = urlencode(stripslashes($value));
	$req .= "&$key=$value";
}

// post back to PayPal system to validate
$header = "POST /cgi-bin/webscr HTTP/1.0\r\n";
$header .= "Content-Type: application/x-www-form-urlencoded\r\n";
//$header .= "Host: www.sandbox.paypal.com\r\n";
$header .= "Content-Length: " . strlen($req) . "\r\n\r\n";

$fp = fsockopen ('ssl://www.paypal.com', 443, $errno, $errstr, 30);

if (!$fp) {
	mail($email,'Member Renewal Payment Error' , 'There was an error processing your payment', $mail_From); 	
	
// HTTP ERROR
} else {
	fputs ($fp, $header . $req);
	while (!feof($fp)) {
		$res = fgets ($fp, 1024);
		if (strcmp ($res, "VERIFIED") == 0) {			
		    $emailtext .= 'Your membership has been renewed successfully.';
	
    		mail($email, $_config['company_name']." Membership Renewal", $emailtext, $mail_From); 
	
			// determine expiry date
			$members = logged_query_assoc_array("SELECT * FROM `members` WHERE `id` > 0 AND `email` = '{$_POST['custom']}' LIMIT 1;");
			$expiry_date = strtotime($members[0]['membership_expiry']);
			
			$monthInSeconds = 60 * 60 * 24 * 30;
			$renewalWindow = $monthInSeconds * 10; // 300 days
        	$currYear = date('Y');

			if(time() >= $expiry_date) {
            	$new_expiry_date = $currYear . '-12-31 23:59:59';   
			}
			elseif(time() >= ($expiry_date - $renewalWindow)) {
            	$new_expiry_date = ($currYear + 1) . '-12-31 23:59:59';  
			} 
								
			mysql_query("UPDATE `members` SET 
               			`payment_status` = 1,
         				`membership_expiry` = '{$new_expiry_date}' 
               			 WHERE `email` = '{$_POST['custom']}'");
	
	
		} elseif (strcmp ($res, "INVALID") == 0) {
    		// If 'INVALID', send an email. TODO: Log for manual investigation. 
			mail($email,'Member Renewal Payment Error' , 'There was an error processing your payment. Not verified', $mail_From); 	
		} 
	}
	fclose ($fp); 
}

?>
