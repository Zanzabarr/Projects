<?php 
// tell PHP to log errors to ipn_errors.log in this directory
ini_set('log_errors', true);
ini_set('error_log', 'ipn_errors.txt');

include('../../config.php');

error_reporting(E_ALL ^ E_NOTICE);
$email = $_POST['custom'];

$header = "";
$emailtext = "";
$mail_From = "From: test@test.com";

// Read the post from PayPal and add 'cmd'
$req = 'cmd=_notify-validate';
foreach ($_POST as $key => $value) {
	$value = urlencode(stripslashes($value));
	$req .= "&$key=$value";
}

// post back to PayPal system to validate
$header = "POST /cgi-bin/webscr HTTP/1.0\r\n";
$header .= "Host: www.paypal.com\r\n";
$header .= "Content-Type: application/x-www-form-urlencoded\r\n";
$header .= "Content-Length: " . strlen($req) . "\r\n\r\n";

$fp = fsockopen ('ssl://www.paypal.com', 443, $errno, $errstr, 30);

if (!$fp) {
	mail($email,'Membership Payment Error' , 'There was an error processing your payment', $mail_From);	
	
// HTTP ERROR
} else {
	fputs ($fp, $header . $req);
	while (!feof($fp)) {
		$res = fgets ($fp, 1024);
		
		if (strcmp ($res, "VERIFIED") == 0) {
			$headers  = $mail_from ? "From: {$mail_from}\n" : '';  
			$headers .= 'MIME-Version: 1.0' . "\n";
			$headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n"; 
			
		    $emailtext .= '<p>Your membership has been paid for successfully. Your account is now active, and you may login to the website with the credentials sent in the previous email.</p><p>Please be aware that you will be asked to change your password when you login.</p><br />
			<a href="http://www.golfbeyondtheswing.com">Golf Beyond the Swing</a>';
	
    		mail($email, $_config['company_name']." Membership Payment", $emailtext, $headers);
								
			mysql_query("UPDATE `members` SET 
            		    `status` = 1,
               			`payment_status` = 1,
                		`unpaid_signup` = 0
               			 WHERE `email` = '{$_POST['custom']}'");
	
		} elseif (strcmp ($res, "INVALID") == 0) {
    		// If 'INVALID', send an email. TODO: Log for manual investigation.
			mail($email,'Member Subscription Payment Error' , 'There was an error processing your payment. Not verified', $mail_From);
		}
	}
	fclose ($fp); 
}

?>
