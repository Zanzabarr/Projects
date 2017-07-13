<?php
	include("../../../includes/config.php");
	include_once("../../../includes/functions.php");
	include_once($_config['admin_includes']."classes/form.php");
	$table = "ecom_paypal_prefs";	//table used
	
	//get existing prefs
	$pplist = logged_query("SELECT * FROM {$table} WHERE id = 1",0,array());
	//encrypted fields
	$enc_keys = array('live_paypal','live_orderemail');
	
	if(!empty($pplist)) {
		$pplist = $pplist[0];
		
		// decrypt encrypted values
		foreach($pplist as $k=>$v) {
			if(in_array($k,$enc_keys) && $v!="") {
				$pplist[$k] = decrypt($v);
			}
		}
	}
	
	/** process post **/
	if(isset($_POST['submit_paypal'])) {
	// validate:
		$errorMsgType = 'errorMsg';
		$errorType = 'error';
		$errorHeading = 'Could Not Save';
		$errorMessage = 'all fields must be complete, not saved';
	
		if($_config['environment'] == 'dev')
		{
			$required[] = 'dev_paypal';
			$required[] = 'dev_orderemail';
		} 
		else
		{
			$required[] = 'live_paypal';
			$required[] = 'live_orderemail';
		}
		
		//validate
		$ppData = $_POST;
		unset($ppData['submit_paypal']);
		unset($ppData['gateway']);
		$testform = new Form($ppData);
		$testform->set_required_input($required);
		$tmplist = $testform->validate();
		$errors = $testform->get_errors();
		
		// merge the form data with pre-existing data
		$pplist = array_merge($pplist, $tmplist);
		// if an error was found, create the error banner
		if ($errors )
		{
			// translate errors to messages
			foreach($errors as $k => $v)
			{
				$message['inline'][$k] = array('type' => $errorMsgType, 'msg' => $v[0]);
			}
		
			$message['banner'] = array ('heading' => $errorHeading, 'message' => $errorMessage, 'type' => $errorType);
		} else {	// set the success message
			$message['banner'] = array ('heading' => 'Successfully Saved', 'type' => 'success');
			
			// get data to be posted to the db, and remove unwanted inputs
			$pp_data = $pplist;
			
			$where_clause = "WHERE `id` = '1';";

			$result = logged_array_update($table, $pp_data, $where_clause, array(), $enc_keys);
			$saveError = (bool) $result['error'];
			
			// banners: if there was an error, overwrite the previously set success message
			if ($saveError)	$message['banner'] = array ('heading' => 'Error Saving Preferences', 'message' => 'Preferences were not saved', 'type' => 'error' );
		}
	}
	
if (!isset($message)) $message = array();

// dev_paypal
$val = isset($pplist['dev_paypal']) ? htmlspecialchars_decode($pplist['dev_paypal']) : "";
$input_dev_paypal = new inputField( 'Sandbox Account', 'dev_paypal' );	
$input_dev_paypal->toolTip('Paypal Seller account for sandbox use');
$input_dev_paypal->value( $val );
$input_dev_paypal->counterWarning(150);
$input_dev_paypal->counterMax(254);
$input_dev_paypal->size('medium');
$input_dev_paypal->arErr($message);

// live_paypal
$val = isset($pplist['live_paypal']) ? htmlspecialchars_decode($pplist['live_paypal']) : "";
$input_live_paypal = new inputField( 'Paypal Account', 'live_paypal' );	
$input_live_paypal->toolTip('Actual Live Paypal Account');
$input_live_paypal->value( $val );
$input_live_paypal->counterWarning(150);
$input_live_paypal->counterMax(254);
$input_live_paypal->size('medium');
$input_live_paypal->arErr($message);

// dev_orderemail
$val = isset($pplist['dev_orderemail']) ? htmlspecialchars_decode($pplist['dev_orderemail']) : "";
$input_dev_orderemail = new inputField( 'Sandbox Order Email', 'dev_orderemail' );	
$input_dev_orderemail->toolTip('Email to receive order details for test server');
$input_dev_orderemail->value( $val );
$input_dev_orderemail->counterWarning(150);
$input_dev_orderemail->counterMax(254);
$input_dev_orderemail->size('medium');
$input_dev_orderemail->arErr($message);

// live_orderemail
$val = isset($pplist['live_orderemail']) ? htmlspecialchars_decode($pplist['live_orderemail']) : "";
$input_live_orderemail = new inputField( 'Paypal Order Email', 'live_orderemail' );	
$input_live_orderemail->toolTip('Email to receive order details for live server');
$input_live_orderemail->value( $val );
$input_live_orderemail->size('medium');
$input_live_orderemail->counterWarning(150);
$input_live_orderemail->counterMax(254);
$input_live_orderemail->arErr($message);

?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<link rel='stylesheet' type='text/css' href='<?php echo $_config['admin_url']; ?>css/styles.css' />
<link rel='stylesheet' type='text/css' href='<?php echo $_config['admin_url']; ?>css/tipTip.css' />
<script src="http://ajax.googleapis.com/ajax/libs/jquery/1.7/jquery.min.js"></script>
<script src="http://ajax.googleapis.com/ajax/libs/jqueryui/1.8/jquery-ui.min.js"></script>
<script type="text/javascript" src="<?php echo $_config['admin_url']; ?>js/jquery.tiptip.js"></script>
<script type="text/javascript" src="<?php echo $_config['admin_url']; ?>js/jquery.jqEasyCharCounter.min.js"></script>
<script type="text/javascript" src="inc-gateway.js"></script>
<style>
body {min-width:200px;background:#FFFFDF;}
h2 {font-size:1em;font-weight:400;}
#gateway-section {padding:0 4em;}
form {background:#e2e2e2;padding:8px;border-radius:5px;}
fieldset {overflow-x:hidden;width:100%;padding:0 0 0 .5em;margin:0;border:none;}
.msg-wrap{display:block;}
</style>
</head>
<body>

<section id="gateway-section">
<!-- gateway settings -->
	<?php
		if(!empty($message)) { ?>
		<div class="msg-wrap">
			<div class="close-message"></div>
			<div id="bannerType" class="<?php echo isset($message['banner']['type']) ? $message['banner']['type'] : '' ; ?>">
				<h2><?php echo isset($message['banner']['heading']) ? $message['banner']['heading'] : ''; ?></h2>
				<span><?php echo isset($message['banner']['message']) ? $message['banner']['message'] : ''; ?></span>
			</div>
		</div>
	<?php
	}
	?>
	<h2 class="tiptip toggle" id="paypal-toggle" title="Paypal Settings">Paypal Settings<span style="font-size:.9em;color:#000;"> - MUST be saved from this form - will not save with the rest of the page</span></h2><br />
	<div id="paypal-toggle-wrap">

		<form action="gateway_frame.php" method="post" enctype="application/x-www-form-urlencoded" name="form_gateway" id="form_gateway" class="form">
			<input type="hidden" name="gateway" value="paypal" />
			<input type="hidden" name="submit_paypal" value="1" />
			<fieldset><legend>Development Details</legend>
			<?php
				$input_dev_paypal->createInputField();
				$input_dev_orderemail->createInputField();
			?>
			</fieldset>
			<hr style="margin:.25em 0 1em 0;"/>
			
			<fieldset><legend>Production Details</legend>
			<?php
				$input_live_paypal->createInputField();
				$input_live_orderemail->createInputField();
			?>
			</fieldset>
		</form>
		<a id="submit-btn" class="blue button" href="#">Save</a>
		<div class="clearFix"></div>
	</div><!-- end gateway settings -->

</section><!-- end gateway-section -->
<script>
$(document).ready( function() {
	$('.msg-wrap').fadeOut(3000);
});
</script>
</body>
</html>