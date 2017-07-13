<?php 

// initialize the page
$headerComponents = array();
$headerModule = 'members';
include('../../includes/headerClass.php');
include('includes/functions.php');
$pageInit = new headerClass($headerComponents,$headerModule);

$baseUrl = $_config['admin_url'];

// set the db variables
$table 				= 'members_paypal';		// name of the table this page posts to

$parent_page		= 'members.php';		// the page that calls this one: used for delete and cancel form buttons, sets where to go afterwards

$message			= array();			// will hold error/success message info

$page_get_id_val = 1;

// show the current values
/*
$list= $_config['members']['options'];

$optionsExist = false;

if(array_key_exists('id', $list)) {
    $optionsExist = true;
}

//encrypted fields

// decrypt encrypted values
foreach($list as $k=>$v) {
	if(in_array($k,$enc_keys)) {
		$list[$k] = decrypt($v);
	}
}
*/

$enc_keys = array('live_paypal','live_orderemail');
$list = getPaypalData();
if(!$list) 
{
	$encryptedBlank = encrypt('');
	$result = logged_query("INSERT INTO `members_paypal` (`live_paypal`,`live_orderemail`) VALUES ('{$encryptedBlank}', '{$encryptedBlank}')",0,array());
	if($result !== 1) die('No options found, error initializing options');
	
	$list = getPaypalData();
}




#==================
# process options
#==================
if(isset($_POST['submit-options'])){
		
		//validation keys
		$required_input 	= array();
		$multi_check_input 	= array();
		$integer_input 		= array();
		$decimal_input 		= array();
		$binary_input 		= array();
		$email_input		= array('orderemail', 'live_orderemail', 'dev_orderemail', 'live_paypal', 'dev_paypal');
		
		// special conditions
		if((bool) $_POST['paypal_on'])
		{
			if($_POST['environment'] == 'dev')
			{
				$required_input[] = 'dev_paypal';
				$required_input[] = 'dev_orderemail';
			} 
			else
			{
				$required_input[] = 'live_paypal';
				$required_input[] = 'live_orderemail';
			} 
			
			if(!$_POST['no_note'])
			{
				$required_input[] = "note_comment";
			}
		}
	/*	
		// special conditions
		if(isset($_POST['online_signup']))
		{
			$required_input[] = 'confirm_from';
			$required_input[] = 'confirm_title';
			$required_input[] = 'confirm_body';
		}
	*/
		unset($_POST['submit-options']);
		$objForm = new Form($_POST);
		$objForm->set_required_input($required_input);
		$objForm->set_multi_check_input($multi_check_input);
		$objForm->set_integer_input($integer_input);
		$objForm->set_decimal_input($decimal_input);
		$objForm->set_binary_input($binary_input);
		$objForm->set_email_input($email_input);
		//$objForm->set_password_pair('password', 're_pass', 'alerts');
		$list = $objForm->validate();
		$errors = $objForm->get_errors();

		if ( $errors )
		{
			// translate errors to messages
			foreach($errors as $k => $v)
			{	
				if ($k == 'dev_paypal' || $k == 'live_paypal') $v[0] = "Provide a valid Paypal Email Account";
				if ($k == 'note_comment') $v[0] = "'Customer Note' selected: required field";
				$message['inline'][$k] = array('type' => 'errorMsg', 'msg' => $v[0]);
			}
		
			$message['banner'] = array ('heading' => 'Incomplete Form', 'message' => 'Please provide the data required, below', 'type' => 'error');
		}
		else // insert
		{
		
			$result = logged_array_update($table, $list,'WHERE `id` = :page_get_id_val',array(":page_get_id_val" => $page_get_id_val), $enc_keys);
			$saveError = (bool) $result['error'];
			if($saveError) $message['banner'] = array ('heading' => 'Save Error', 'message' => 'Error saving selections, please try again', 'type' => 'error');
			else $message['banner'] = array ('heading' => 'Successfully Saved', 'type' => 'success');
		}
}

//preferences
/*
// notify from
$input_confirm_from = new inputField('From Address', 'confirm_from' );	
$input_confirm_from->toolTip('Select an email address from the list. Your Confirmations will be &apos;from&apos; this address<br />NOTE: to protect against being flagged as spam, this must be an address hosted on this server.');
$input_confirm_from->type('select');
$input_confirm_from->selected(isset($list['confirm_from']) ? $list['confirm_from'] : '');
foreach($_config['site_emails'] as $email)
{
	$input_confirm_from->option( $email['email'], $email['email'] );
}
$input_confirm_from->arErr($message);

// notify_title
$input_confirm_title = new inputField( 'Email Title', 'confirm_title' );	
$input_confirm_title->toolTip('Title on your Confirmation Email.');
$input_confirm_title->value(htmlspecialchars_decode(isset($list['confirm_title']) ? $list['confirm_title'] : ''));
$input_confirm_title->counterMax(100);
$input_confirm_title->size('small');
$input_confirm_title->arErr($message);

// page title
$input_confirm_body = new inputField( 'Email Body', 'confirm_body' );	
$input_confirm_body->toolTip('Provide an optional message to accompany the Members Applicant&apros;s Information.');
$input_confirm_body->value(htmlspecialchars_decode(isset($list['confirm_body']) ? $list['confirm_body'] : ''));
$input_confirm_body->counterMax(500);
$input_confirm_body->type('textarea');
$input_confirm_body->size('large');
$input_confirm_body->arErr($message);
*/


// dev_paypal
$val = isset($list['dev_paypal']) ? htmlspecialchars_decode($list['dev_paypal']) : "";
$input_dev_paypal = new inputField( 'Sandbox Account', 'dev_paypal' );	
$input_dev_paypal->toolTip('Paypal Seller account for sandbox use');
$input_dev_paypal->value( $val );
$input_dev_paypal->counterWarning(150);
$input_dev_paypal->counterMax(254);
$input_dev_paypal->size('medium');
$input_dev_paypal->arErr($message);

// live_paypal
$val = isset($list['live_paypal']) ? htmlspecialchars_decode($list['live_paypal']) : "";
$input_live_paypal = new inputField( 'Paypal Account', 'live_paypal' );	
$input_live_paypal->toolTip('Actual Live Paypal Account');
$input_live_paypal->value( $val );
$input_live_paypal->counterWarning(150);
$input_live_paypal->counterMax(254);
$input_live_paypal->size('medium');
$input_live_paypal->arErr($message);

// dev_orderemail
$val = isset($list['dev_orderemail']) ? htmlspecialchars_decode($list['dev_orderemail']) : "";
$input_dev_orderemail = new inputField( 'Sandbox Order Email', 'dev_orderemail' );	
$input_dev_orderemail->toolTip('Email to receive order details for test server');
$input_dev_orderemail->value( $val );
$input_dev_orderemail->counterWarning(150);
$input_dev_orderemail->counterMax(254);
$input_dev_orderemail->size('medium');
$input_dev_orderemail->arErr($message);

// live_orderemail
$val = isset($list['live_orderemail']) ? htmlspecialchars_decode($list['live_orderemail']) : "";
$input_live_orderemail = new inputField( 'Paypal Order Email', 'live_orderemail' );	
$input_live_orderemail->toolTip('Email to receive order details for live server');
$input_live_orderemail->value( $val );
$input_live_orderemail->size('medium');
$input_live_orderemail->counterWarning(150);
$input_live_orderemail->counterMax(254);
$input_live_orderemail->arErr($message);

//paypal_on
$val = isset($list['paypal_on']) ? $list['paypal_on'] : 0;
$input_paypal_on = new inputField( 'Use Paypal?', 'paypal_on' );
$input_paypal_on->toolTip('Process payments with Paypal? Requires "Include Pricing", "Use Cart", and "Online Purchasing"');
$input_paypal_on->type('select');
$input_paypal_on->selected( $val );
$input_paypal_on->size('small');
$input_paypal_on->option( 0, 'No' );
$input_paypal_on->option( 1, 'Yes' );
$input_paypal_on->arErr($message);

//allow customer note
$val = isset($list['no_note']) ? $list['no_note'] : 0;
$input_no_note = new inputField( 'Customer Note', 'no_note' );
$input_no_note->toolTip('Allow customer to include a note with payment?');
$input_no_note->type('select');
$input_no_note->selected( $val );
$input_no_note->option( 0, 'Yes' );
$input_no_note->option( 1, 'No' );
$input_no_note->arErr($message);

// comment for customer note
$val = isset($list['note_comment']) ? htmlspecialchars_decode($list['note_comment']) : "Add special instructions for {$_config['company_name']}";
$input_note_comment = new inputField( 'Note Comment', 'note_comment' );	
$input_note_comment->toolTip('Instructional prompt that will appear above customer note field');
$input_note_comment->value( $val );
$input_note_comment->counterWarning(65);
$input_note_comment->counterMax(100);
$input_note_comment->size('medium');
$input_note_comment->arErr($message);


$pageResources ="
<link rel=\"stylesheet\" type=\"text/css\" href=\"".$_config['admin_url']."modules/members/style.css\" />
<script type=\"text/javascript\" src=\"".$_config['admin_url']."modules/members/js/paypal.js\"></script>
";
$pageInit->createPageTop($pageResources);
 
 ?>
 <div class="page_container">
	<div id="h1"><h1>Paypal</h1></div>
    <div id="info_container">
		<?php 
		// ------------------------------------sub nav----------------------------
		$selectedPaypal = 'tabSel';
		include("includes/subnav.php"); 
		echo '<hr />';		
		
		//---------------------------------------Error Banner----------------------------- 
		// create a banner if $message['banner'] exists
		createBanner($message); 
		
		$parms = "?option=edit";
		?>
		<form action="<?php echo htmlentities($_SERVER['PHP_SELF']); ?><?php echo $parms; ?>" method="post" enctype="application/x-www-form-urlencoded" name="form_data" id="form_data" class="form">
			
			<!-- general preferences -->
			<h2 class="tiptip toggle" id="prefs-toggle" title="Set your general preferences">General Preferences</h2><br />
			<div id="prefs-toggle-wrap">
			may need this later
			<?php
	/*
			?>	
				<fieldset>
					<legend id="confirm-toggle" class="tipTop toggle" title="Online Signups send a Confirmation Email to new applicants. This email provides a link and confirmation code to complete the signup process.">Confirmation Details</legend>
					<div id="confirm-toggle-wrap">
			<?php	
				$input_confirmation_period->createInputField();
				$input_confirm_from->createInputField();
				$input_confirm_title->createInputField();
				$input_confirm_body->createInputField();
			?>	
					</div>
				</fieldset>
			<?php
	*/
?>

			</div><!-- end general preferences -->
			<div class='clearFix' ></div>
			

						<?php 
						
		$hasCart = true;				
			if ($hasCart) : 
				if($list['environment'] == "live") {
					$livechk = "checked";
					$devchk = "";
				} else {
					$livechk = "";
					$devchk = "checked";
				}

				$pp_txt = $list['paypal_on']==1 ? "Paypal is <span style='color:green;'>ON</span>" : "Paypal is <span style='color:red;'>OFF</span>";
				
			?>
			<h2 class="tiptip toggle" id="paypal-toggle" title="Paypal Settings - Do not change unless instructed to do so">Paypal Settings - <span id="paypal_state"><?php echo $pp_txt;?></span></h2><br />
			<div id="paypal-toggle-wrap">
				<?php
					$input_paypal_on->createInputField();
				?>
				<div class="input_wrap">
					<label class="tipRight" title="Is this site live or in development?">Server Type</label>
					<div class="input_inner">
						<div class="message_wrap"><span id="err_environment"></span></div>
						<input id="environment" name="environment" class=" no_counter" type="radio" value="live" <?php echo $livechk; ?> /> Production &nbsp;&nbsp;
						<input id="environment" name="environment" class=" no_counter" type="radio" value="dev" <?php echo $devchk; ?> /> Development
					</div>
				</div>
				<div class="clearFix"></div>
				<fieldset><legend>Development Details</legend>
				<?php
					$input_dev_paypal->createInputField();
					$input_dev_orderemail->createInputField();
				?>
				</fieldset>
				<div class="clearFix" style="height:.5em;"></div>
				<fieldset><legend>Production Details</legend>
				<?php
					$input_live_paypal->createInputField();
					$input_live_orderemail->createInputField();
				?>
				</fieldset>
				<div class="clearFix" style="height:.5em;"></div>
				<fieldset><legend>Paypal Cart Options</legend>
				<?php
					$input_no_note->createInputField();
					$input_note_comment->createInputField();
				?>
				</fieldset>
				<div class="clearFix"></div>
			<?php
			
			?>
			</div><!-- end paypal settings --><?php 
			endif; ?>
			<div class='clearFix' ></div>
			
			
			<!-- page buttons -->
			

			<input name="submit-options" id="submit-options" type="hidden" value="1" />
		</form>	
		<a id="submit-btn" class="blue button" href="#">Save</a>

		<a class="grey button" id="cancel-btn" href="<?php echo $parent_page; ?>">Cancel</a>
		<div class='clearFix' ></div> 
		<!-- end page buttons -->
	</div>
</div>	
	
<?php 
include($_config['admin_includes'] . "footer.php"); ?>
