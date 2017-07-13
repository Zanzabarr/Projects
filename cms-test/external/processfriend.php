<?php
include('../includes/config.php');
include('../admin/includes/html2text.php');

	if(isset($_POST['friendsubmit'])) {
		unset($_POST['friendsubmit']);
		$required = array("from_name", "from_email", "to_email");
		foreach($required as $k=>$v) {
			if(!isset($_POST[$v]) || $_POST[$v]=="") {
				echo "<strong>All fields must be completed.<br />Please try again.</strong><br /><br />";
				die("<button onclick='location.reload();'>Close Window</button>");
			}
		}
		foreach($_POST as $k=>$v) {
			switch($k) {
				case "ref":
					$$k = filter_var(trim($v), FILTER_SANITIZE_URL);
					break;
				case "from_name":
					$$k = filter_var(trim($v), FILTER_SANITIZE_STRING);
					break;
				case "from_email":
					if(filter_var(trim($v),FILTER_VALIDATE_EMAIL)) {
						$$k = filter_var(trim($v), FILTER_SANITIZE_EMAIL);
					} else {
						echo "<strong class='red'>All fields must be completed.<br />Please use valid email address.</strong><br /><br />";
						die("<button onclick='location.reload();'>Close Window</button>");
					}
					break;
				case "to_email":
					if(filter_var(trim($v),FILTER_VALIDATE_EMAIL)) {
						$$k = filter_var(trim($v), FILTER_SANITIZE_EMAIL);
					} else {
						echo "<strong class='red'>All fields must be completed.<br />Please use valid email address.</strong><br /><br />";
						die("<button onclick='location.reload();'>Close Window</button>");
					}
					break;
			}
		}
		
		$url = $_config['site_path']."shopping/products/".$ref;
		$product = logged_query("SELECT * FROM ecom_product WHERE url = :url",0,array(":url"=>$ref));
		$product = $product[0];
		
		//create headers and mail
		$subject = $from_name." recommended an item at {$_config['company_name']}";
		$random_hash = md5(date('r', time()));		// simple hash for multipart boundary
		$headers  = "From: {$_config['orderemail']}\n";
		$headers .= 'MIME-Version: 1.0' . "\n";
		$headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
		
		$html_string = "<p>{$from_name} at <a href='mailto:{$from_email}'>{$from_email}</a> feels you would be interested in the following item at <a href='{$url}'>{$url}</a>.</p>";
		$html_string .= "<h2>{$product['title']}</h2><h3>Description</h3>".htmlspecialchars_decode($product['short_desc']);
		if(isset($product['specs']) && $product['specs']!="") {
			$html_string .= "<h3>Specifications</h3>".htmlspecialchars_decode($product['specs']);
		}
		$html_string .= "<br /><br /><br />";
		$html_string .= "<p>Privacy - This email was sent to you at the request of {$from_name} as stated above. No information or email addresses were stored as a result of this request.</p>";
		
		$mail_sent = @mail( $to_email, $subject, $html_string, $headers );
		echo $mail_sent ? "Recommendation has been sent." : "There were errors found. Recommendation not sent.";
	}
?>
<div class="clear"></div>
<button onclick='location.reload();'>Close Window</button>
