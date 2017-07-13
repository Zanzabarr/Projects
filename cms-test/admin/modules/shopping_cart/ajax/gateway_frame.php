<?php
global $_config;
if(isset($_POST['gateway'])) {

	switch($_POST['gateway']) {
		case "nogateway":
			$gateurl = "";
			break;
		case "paypal":
			if(isset($_POST['submit_paypal'])) {
				include('inc-paypal-prefs.php');
			} else {
				$gateurl = $_config['admin_url']."ajax/inc-paypal-prefs.php";
				echo "<iframe src='{$gateurl}' id='{$_POST['gateway']}-frame'></iframe>";
			}
			break;
		case "moneris":
			if(isset($_POST['submit_moneris'])) {
				include('inc-moneris-prefs.php');
			} else {
				$gateurl = $_config['admin_url']."ajax/inc-moneris-prefs.php";
				echo "<iframe src='{$gateurl}' id='{$_POST['gateway']}-frame'></iframe>";
			}
			break;
	}
	
	
} else {
	echo "There has been an error.";
}
?>