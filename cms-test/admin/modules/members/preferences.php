<?php 

// initialize the page
$headerComponents = array();
$headerModule = 'members';
include('../../includes/headerClass.php');
include('includes/functions.php');
$pageInit = new headerClass($headerComponents,$headerModule);

$baseUrl = $_config['admin_url'];

// set the db variables
$table 				= 'members_options';		// name of the table this page posts to

$parent_page		= 'members.php';		// the page that calls this one: used for delete and cancel form buttons, sets where to go afterwards

$message			= array();			// will hold error/success message info

$page_get_id_val = 1;

$list = getMembersOptions();

#==================
# process options
#==================
if(isset($_POST['submit-options'])){
		
		//validation keys
		$required_input 	= array('members_per_page');
		$multi_check_input 	= array();
		$integer_input 		= array('members_per_page');
		$decimal_input 		= array();
		$binary_input 		= array('member_req_login', 'online_signup', 'pay_signup', 'renewal_req', 'ftp_front', 'members_front');
		$email_input		= array('confirm_from');
		
		// special conditions
		if(isset($_POST['online_signup']))
		{
			$required_input[] = 'confirm_from';
			$required_input[] = 'confirm_title';
			$required_input[] = 'confirm_body';
		}
	
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
				$message['inline'][$k] = array('type' => 'errorMsg', 'msg' => $v[0]);
			}
		
			$message['banner'] = array ('heading' => 'Incomplete Form', 'message' => 'Please provide the data required, below', 'type' => 'error');
		}
		else // insert
		{
		
			$result = logged_array_update($table, $list,'WHERE `id` = :page_get_id_val',array(":page_get_id_val" => $page_get_id_val));
			$saveError = (bool) $result['error'];
			if($saveError) $message['banner'] = array ('heading' => 'Save Error', 'message' => 'Error saving selections, please try again', 'type' => 'error');
			else $message['banner'] = array ('heading' => 'Successfully Saved', 'type' => 'success');
		}
}

//preferences
// online_signup
$input_online_signup = new inputField( 'Online Signup', 'online_signup' );
$input_online_signup->toolTip('Allow site visitors to use an online form to sign up for membership.');
$input_online_signup->type('checkbox');
$input_online_signup->value(isset($list['online_signup']) ? $list['online_signup'] : '');
$input_online_signup->arErr($message);

//confirmation_period
$input_confirmation_period = new inputField( 'Days to Confirm', 'confirmation_period' );
$input_confirmation_period->toolTip('Applicant has this many days to complete signup process after receiving a confirmation email with Confirmation Link.');
$input_confirmation_period->type('select');
$input_confirmation_period->selected( isset($list['confirmation_period']) ? $list['confirmation_period'] : 5 );
$input_confirmation_period->size('tiny');
for($int = 1; $int <= 30; $int++)
	$input_confirmation_period->option( $int, $int );
$input_confirmation_period->arErr($message);

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
$input_confirm_body->toolTip('Provide an optional message to accompany the Members Applicant&apos;s Information.');
$input_confirm_body->value(htmlspecialchars_decode(isset($list['confirm_body']) ? $list['confirm_body'] : ''));
$input_confirm_body->counterMax(500);
$input_confirm_body->type('textarea');
$input_confirm_body->size('large');
$input_confirm_body->arErr($message);

// pay_signup
$input_pay_signup = new inputField( 'Pay Membership', 'pay_signup' );
$input_pay_signup->toolTip('Charge customers for Membership <br>NOTE: Additional information required.');
$input_pay_signup->type('checkbox');
$input_pay_signup->value(isset($list['pay_signup']) ? $list['pay_signup'] : '');
$input_pay_signup->arErr($message);

// renewal
$input_renewal_req = new inputField( 'Renewal Required', 'renewal_req' );
$input_renewal_req->toolTip('Do your members need to renew their subscriptions periodically?');
$input_renewal_req->type('checkbox');
$input_renewal_req->value(isset($list['renewal_req']) ? $list['renewal_req'] : '');
$input_renewal_req->arErr($message);

// members_front
$input_members_front = new inputField( 'Display Members', 'members_front' );
$input_members_front->toolTip('Member Information is displayed on the site <br>NOTE: Addition information required');
$input_members_front->type('checkbox');
$input_members_front->value(isset($list['members_front']) ? $list['members_front'] : '');
$input_members_front->arErr($message);

// ftp_front
$input_ftp_front = new inputField( 'Enable File Sharing', 'ftp_front' );
$input_ftp_front->toolTip('Members can share files with the site and each other <br>NOTE: Additional information required');
$input_ftp_front->type('checkbox');
$input_ftp_front->value(isset($list['ftp_front']) ? $list['ftp_front'] : '');
$input_ftp_front->arErr($message);

// member requires login
$input_member_req_login = new inputField('Requires Login', 'member_req_login');
$input_member_req_login->toolTip("If checked, must be logged in as member to view the members&apos; pages.");
$input_member_req_login->type('checkbox');
$input_member_req_login->value(isset($list['member_req_login']) ? $list['member_req_login'] : '');
$input_member_req_login->arErr($message);


$pageResources ="
<link rel=\"stylesheet\" type=\"text/css\" href=\"".$_config['admin_url']."modules/members/style.css\" />
<script type=\"text/javascript\" src=\"".$_config['admin_url']."modules/members/js/preferences.js\"></script>
";
$pageInit->createPageTop($pageResources);
 
 ?>
 <div class="page_container">
	<div id="h1"><h1>Preferences</h1></div>
    <div id="info_container">
		<?php 
		// ------------------------------------sub nav----------------------------
		$selectedOpts = 'tabSel';
		$selectedMembers = '';
		$selectedHome = '';
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
			<?php
				$input_online_signup->createInputField();
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
// functionality not yet ready
//				$input_pay_signup->createInputField();
				
				$input_renewal_req->createInputField();
// further options will be implemented at a later date


				$input_ftp_front->createInputField();
				$input_members_front->createInputField();
			?>	
				<fieldset>
					<legend id="requires-toggle" class=" toggle">Display Details</legend>
					<div id="requires-toggle-wrap">
			<?php		
				$input_member_req_login->createInputField();
			?>	
					</div>
				</fieldset>

			</div><!-- end general preferences -->
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
