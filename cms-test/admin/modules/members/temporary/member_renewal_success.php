<?php
	if(logged_in_as_member()) {
		// modify session variables to match database
		$members = logged_query_assoc_array("SELECT * FROM `members` WHERE `id` > 0 AND `email` = '{$_SESSION['member']['email']}' LIMIT 1;");
		$expiry_date = strtotime($members[0]['membership_expiry']);
		
		$_SESSION['member']['payment_status'] = $members[0]['payment_status'];
		$_SESSION['member']['membership_expiry'] = date('Y-m-d H:i:s', $expiry_date);	
	}
?>

<p>Thankyou for renewing your membership.</p>