<?php
	include("../../../includes/config.php");
	include_once("../../../includes/functions.php");
	include_once($_config['admin_includes']."classes/form.php");
	$table = "ecom_moneris_prefs";	//table used
	
	//get existing prefs
	$monlist = logged_query("SELECT * FROM {$table} WHERE id = 1",0,array());
	//encrypted fields
	$enc_keys = array('profile_key','store_id','api_token','email');
	
	if(!empty($monlist)) {
		$monlist = $monlist[0];
		
		// decrypt encrypted values
		foreach($monlist as $k=>$v) {
			if(in_array($k,$enc_keys) && $v!="") {
				$monlist[$k] = decrypt($v);
			}
		}
	}
	
	/** process post **/
	if(isset($_POST['submit_moneris'])) {
	// validate:
		$errorMsgType = 'errorMsg';
		$errorType = 'error';
		$errorHeading = 'Could Not Save';
		$errorMessage = 'all fields must be complete, not saved';
		
		$required = array('profile_key','store_id','api_token','email','dev_url','live_url','crypt_type');
		
		//validate
		$monData = $_POST;
		unset($monData['submit_moneris']);
		unset($monData['gateway']);
		$testform = new Form($monData);
		$testform->set_required_input($required);
		$tmplist = $testform->validate();
		$errors = $testform->get_errors();
		
		// merge the form data with pre-existing data
		$monlist = array_merge($monlist, $tmplist);
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
			$mon_data = $monlist;
			
			$where_clause = "WHERE `id` = '1';";

			$result = logged_array_update($table, $mon_data, $where_clause, array(), $enc_keys);
			$saveError = (bool) $result['error'];
			
			// banners: if there was an error, overwrite the previously set success message
			if ($saveError)	$message['banner'] = array ('heading' => 'Error Saving Preferences', 'message' => 'Preferences were not saved', 'type' => 'error' );
		}
	}
	
if (!isset($message)) $message = array();

// profile_key
$val = isset($mon_data['profile_key']) ? $mon_data['profile_key'] : $monlist['profile_key'];
$input_profile_key = new inputField( 'Profile Key', 'profile_key' );	
$input_profile_key->toolTip('Tokenization Profile Key Received from Moneris');
$input_profile_key->value( $val );
$input_profile_key->counterWarning(45);
$input_profile_key->counterMax(50);
$input_profile_key->size('medium');
$input_profile_key->arErr($message);

// store_id
$val = isset($mon_data['store_id']) ? $mon_data['store_id'] : $monlist['store_id'];
$input_store_id = new inputField( 'Store ID', 'store_id' );	
$input_store_id->toolTip('ID Received from Moneris');
$input_store_id->value( $val );
$input_store_id->counterWarning(9);
$input_store_id->counterMax(10);
$input_store_id->size('medium');
$input_store_id->arErr($message);

// api_token
$val = isset($mon_data['api_token']) ? $mon_data['api_token'] : $monlist['api_token'];
$input_api_token = new inputField( 'API Token', 'api_token' );	
$input_api_token->toolTip('API Token Received from Moneris');
$input_api_token->value( $val );
$input_api_token->counterWarning(45);
$input_api_token->counterMax(50);
$input_api_token->size('medium');
$input_api_token->arErr($message);

// email
$val = isset($mon_data['email']) ? $mon_data['email'] : htmlspecialchars_decode($monlist['email']);
$input_email = new inputField( 'Email Address', 'email' );	
$input_email->toolTip('Email associated with Moneris account');
$input_email->value( $val );
$input_email->size('medium');
$input_email->counterWarning(150);
$input_email->counterMax(254);
$input_email->arErr($message);

// dev_url
$val = isset($mon_data['dev_url']) ? $mon_data['dev_url'] : htmlspecialchars_decode($monlist['dev_url']);
$input_dev_url = new inputField( 'Development URL', 'dev_url' );	
$input_dev_url->toolTip('Moneris Development Address. Do not change unless instructed to do so.');
$input_dev_url->value( $val );
$input_dev_url->size('medium');
$input_dev_url->counterWarning(150);
$input_dev_url->counterMax(254);
$input_dev_url->arErr($message);

// live_url
$val = isset($mon_data['live_url']) ? $mon_data['live_url'] : htmlspecialchars_decode($monlist['live_url']);
$input_live_url = new inputField( 'Live URL', 'live_url' );	
$input_live_url->toolTip('Moneris Live Address. Do not change unless instructed to do so.');
$input_live_url->value( $val );
$input_live_url->size('medium');
$input_live_url->counterWarning(150);
$input_live_url->counterMax(254);
$input_live_url->arErr($message);

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
	<h2 class="tiptip toggle" id="moneris-toggle" title="Moneris Settings">Moneris Settings<span style="font-size:.9em;color:#000;"> - MUST be saved from this form - will not save with the rest of the page</span></h2><br />
	<div id="moneris-toggle-wrap">
		<form action="gateway_frame.php" method="post" enctype="application/x-www-form-urlencoded" name="form_gateway" id="form_gateway" class="form">
			<input type="hidden" name="gateway" value="moneris" />
			<input type="hidden" name="submit_moneris" value="1" />
			<fieldset><legend>Development Details</legend>
			<?php
				$input_profile_key->createInputField();
				$input_store_id->createInputField();
				$input_api_token->createInputField();
				$input_email->createInputField();
				$input_dev_url->createInputField();
				$input_live_url->createInputField();
			?>
				<input type='hidden' name='crypt_type' value="7" /><!-- this value must be sent for all ssl-enabled merchants -->
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