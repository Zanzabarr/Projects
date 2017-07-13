<?php 
// initialize the page
$headerComponents = array('revisions');
$headerModule = 'members';
include('../../includes/headerClass.php');
$pageInit = new headerClass($headerComponents,$headerModule);

$baseUrl = $_config['admin_url'];

// set the db variables
$table 				= 'members_signup';		// name of the table this page posts to
$mce_content_name	= 'desc';			// the tinyMCE editor always uses the id:content, but db tables usually use something specific to that table for main content,
$revision_table 	= 'members_signup_rev';	// name of the revision table
$revision_table_id 	= 'members_signup_id'; 	// the name of the field in the revision table that contains the id of the original table
$page_type			= 'members_signup';		// name of uploadPageType for uploads component, also used to differentiate kinds of delete on parent page
$parent_page		= 'members.php';		// the page that calls this one: used for delete and cancel form buttons, sets where to go afterwards
$message			= array();			// will hold error/success message info
$page_get_id_val = 1;

// this page has revisions, instaniate it
include_once($_config['components']."revisions/revisions.php");
$Revisions = new revisions($revision_table, $revision_table_id);

// if this arrived from revision history, get info
if ( $Revisions->isRevision() )
{
	$list = $Revisions->getRevisionData($page_get_id_val);
	$message = $Revisions->getResultMessage($page_get_id_val);
}
// show the current values
else
{	
	$new = logged_query("SELECT * FROM {$table} LIMIT 1",0,array()); 
	$list=isset($new[0]) ? $new[0] : array();
}

#==================
# process post
#==================
if(isset($_POST['submit-home'])){

	// validate:
	//	pick error type/messages based on if its status is draft or published
	if ($_POST['status'] == 1) // published: thus error
	{
		$errorMsgType = 'errorMsg';
		$errorType = 'error';
		$errorHeading = 'Blank Fields';
		$errorMessage = 'saved with Members Homepage disabled';
	}
	else  // draft: thus warning
	{
		$errorMsgType = 'warningMsg';
		$errorType = 'warning';
		$errorHeading = 'Successfully Saved';
		$errorMessage = 'with errors on the page';
	}
	$list = array();
	
	$formData = $_POST;
	unset($formData['submit-home']);
	$form = new Form($formData);
	
	$required = array('title', 'signup_from');
	$email = array('notify_to');
	// conditional validation
	if($formData['email_notification'])
	{
		$required[] = 'notify_from';
		$required[] = 'notify_to';
		$required[] = 'notify_title';
		$required[] = 'notify_body';
	}
	
	$form->set_required_input( $required );
//	$form->set_multi_check_input();
	$form->set_integer_input(array('signup_from', 'notify_from'));
//	$form->set_decimal_input();
//	$form->set_binary_input();
//	$form->set_greater_than_input();
	$count_site_emails = count($_config['site_emails']);
	$form->set_less_than_input(array('signup_from' => $count_site_emails,'notify_from' => $count_site_emails) );
	$form->set_email_input( $email );
	
	$list = $form->validate();
	$errors = $form->get_errors();
	
	// if an error was found, create the error banner
	if ($errors )
	{
		// translate errors to messages
		foreach($errors as $k => $v)
		{
			$message['inline'][$k] = array('type' => $errorMsgType, 'msg' => $v[0]);
		}
	
		$message['banner'] = array ('heading' => $errorHeading, 'message' => $errorMessage, 'type' => $errorType);
		$status = $list['status'] = 0; // saving as draft

		if (array_key_exists($revision_table_id, $_GET) && is_numeric($_GET[$revision_table_id]) ) $page_get_id_val = $_GET[$revision_table_id];
	}
	else // set the success message
	{
		
	
		if ($_POST['status'] == 1)	$message['banner'] = array ('heading' => 'Successfully Saved', 'type' => 'success');
		else $message['banner'] = array ('heading' => 'Saved as Disabled', 'message' => 'Set Status to Enabled to make Signup available', 'type' => 'warning');
	}
	
	$post_data = $list;
	$post_data['date'] = 'UTC_TIMESTAMP()';
	$where_clause = '';
	$where_bindings = array();
	
	// unset the email index and set the proper email info
	if(isset($errors['signup_from'])) $post_data['signup_from'] = 0;
	$post_data['signup_from_name'] = $_config['site_emails'][$post_data['signup_from']]['name'];
	$post_data['signup_from'] = $_config['site_emails'][$post_data['signup_from']]['email']; 

	if(isset($errors['notify_from'])) $post_data['notify_from'] = 0;
	$post_data['notify_from_name'] = $_config['site_emails'][$post_data['notify_from']]['name'];
	$post_data['notify_from'] = $_config['site_emails'][$post_data['notify_from']]['email']; 
	
	// update table
	$result = logged_array_update($table, $post_data, $where_clause, $where_bindings);
	$saveError = (bool) $result['error'];
	
	if(! $saveError)
	{
		// add in revision specific data
		$post_data[$revision_table_id] = $page_get_id_val;
		$result = logged_array_insert($revision_table, $post_data);
		// don't worry if there was an error inserting into rev table
	}
	
	if ($saveError)	$message['banner'] = array ('heading' => 'Error Saving Product', 'message' => 'there was an error writing to the database', 'type' => 'error' );

}
if(! isset($message))$message = array();
// page title
$input_homepage_title = new inputField( 'Signup Page Title', 'title' );	
$input_homepage_title->toolTip('Title as it appears at the top of the Online Signup Form.');
$input_homepage_title->value(htmlspecialchars_decode(isset($list['title']) ? $list['title'] : ''));
$input_homepage_title->counterMax(100);
$input_homepage_title->size('small');
$input_homepage_title->arErr($message);

// signup_from_email 
$input_signup_from_email = new inputField('Sign-up Email', 'signup_from' );	
$input_signup_from_email->toolTip('Select an email address from the list. Sign-up Confirmation and Password Reset Request Emails will be &apos;from&apos; this address<br />NOTE: to protect against being flagged as spam, this must be an address hosted on this server.');
$input_signup_from_email->type('select');
$input_signup_from_email->size('medium');
$input_signup_from_email->selected(isset($list['signup_from']) ? $list['signup_from'] : '');
$email_count=0;
foreach($_config['site_emails'] as $email)
{
	$input_signup_from_email->option( $email_count++, "{$email['name']}&nbsp;&nbsp; ({$email['email']})" );
}
$input_signup_from_email->arErr($message);

// status
$input_status = new inputField('Status', 'status' );	
$input_status->toolTip('Online Signup is not available if this is set to Disabled: make sure all required fields are properly completed.');
$input_status->type('select');
$input_status->selected(isset($list['status']) ? $list['status'] : '');
$input_status->option( 0, 'Disabled' );
$input_status->option( 1, 'Enabled' );
$input_status->arErr($message);

// notify from
$email_count=0;
$input_notify_from = new inputField('From Address', 'notify_from' );	
$input_notify_from->toolTip('Select an email address from the list. Your notifications will be &apos;from&apos; this address<br />NOTE: to protect against being flagged as spam, this must be an address hosted on this server.');
$input_notify_from->type('select');
$input_notify_from->size('medium');
$input_notify_from->selected(isset($list['notify_from']) ? $list['notify_from'] : '');
foreach($_config['site_emails'] as $email)
{
	$input_notify_from->option( $email_count++, "{$email['name']}&nbsp;&nbsp; ({$email['email']})" );
}
$input_notify_from->arErr($message);

// notify to
$input_notify_to = new inputField( 'To Address', 'notify_to' );	
$input_notify_to->toolTip('Enter a valid email address to receive these Notifications.');
$input_notify_to->size('medium');
$input_notify_to->value(htmlspecialchars_decode(isset($list['notify_to']) ? $list['notify_to'] : ''));
$input_notify_to->counterMax(250);
$input_notify_to->arErr($message);

// notify_title
$input_notify_title = new inputField( 'Email Title', 'notify_title' );	
$input_notify_title->toolTip('Title on your notification email.');
$input_notify_title->value(htmlspecialchars_decode(isset($list['notify_title']) ? $list['notify_title'] : ''));
$input_notify_title->counterMax(100);
$input_notify_title->size('small');
$input_notify_title->arErr($message);

// page title
$input_notify_body = new inputField( 'Email Body', 'notify_body' );	
$input_notify_body->toolTip('Provide an optional message to accompany the Members Applicant&apos;s Information.');
$input_notify_body->value(htmlspecialchars_decode(isset($list['notify_body']) ? $list['notify_body'] : ''));
$input_notify_body->counterMax(500);
$input_notify_body->type('textarea');
$input_notify_body->size('large');
$input_notify_body->arErr($message);

$pageResources ="
<link rel=\"stylesheet\" type=\"text/css\" href=\"".$_config['admin_url']."modules/members/style.css\" />
<script type=\"text/javascript\" src=\"".$_config['admin_url']."modules/members/js/signup.js\"></script>
";
$pageInit->createPageTop($pageResources);
 
 ?>
 <div class="page_container">
	<div id="h1"><h1>Members Signup</h1></div>
    <div id="info_container">
		<?php 
		// ------------------------------------sub nav----------------------------

		$selectedSignup = 'tabSel';
		include("includes/subnav.php"); 
		echo '<hr />';		
		
		//---------------------------------------Error Banner----------------------------- 
		// create a banner if $message['banner'] exists
		createBanner($message); 

		$parms = "?option=edit";
		?>
		<form action="<?php echo htmlentities($_SERVER['PHP_SELF']); ?><?php echo $parms; ?>" method="post" enctype="application/x-www-form-urlencoded" name="form_data" id="form_data" class="form">
  
			<!--  properties_wrap -->
			<h2 id="prop-toggle" class="tiptip toggle" title="Choose your Members page title and decide if it should appear in the frontend">Members Signup Page Properties</h2><br />
			<div id="prop-toggle-wrap">
			<?php
				$input_homepage_title->createInputField();
				$input_signup_from_email->createInputField();
								
				$nonechk = "";
				$notifychk = "";
				$approvechk = "";
				if($list['email_notification'] == 0) {
					$nonechk = "checked";
				} elseif($list['email_notification'] == 1) {
					$notifychk = "checked";
					
				} else {
					$approvechk = "checked";
				}
			?>
				<div class="input_wrap">
					<label class="tipRight" title="Do you want to receive email notifications of Signup Requests?">Email Notification</label>
					<div class="input_inner">
						<div class="message_wrap"><span id="err_environment"></span></div>
						<input id="email_notification" name="email_notification" class=" no_counter tipTop" type="radio" value="0" title="No email notification required<br>New Signups will be added automatically" <?php echo $nonechk; ?> /> None &nbsp;&nbsp;
						<input id="email_notification" name="email_notification" class=" no_counter tipTop" type="radio" value="1" title="Email a New Signup Notification to the address below<br>New Signups will be added automatically" <?php echo $notifychk; ?> /> Notify &nbsp;&nbsp;
						<input id="email_notification" name="email_notification" class=" no_counter tipTop" type="radio" value="2" title="Email a New Signup Notification to the address below<br>Do not add New Members until I have confirmed them."  <?php echo $approvechk; ?> /> Approval 
					</div>
				</div>
			<?php	
				
				$input_status->createInputField();
			?>
			</div><!-- end prop_wrap -->		   

             <!-- content area -->
			<h2 id="content-toggle" class="tiptip toggle" title="If this section is completed, this message will appear above the Member Signup Form.">Member Signup Description</h2>
			<?php if (isset($message['inline']) && array_key_exists($mce_content_name, $message['inline'])) :?>
				<span class="<?php echo $message['inline'][$mce_content_name]['type'] ;?>"><?php echo $message['inline'][$mce_content_name]['msg'] ;?> </span>
			<?php endif; ?>
			<br />
			<div id="content-toggle-wrap">
			<?php
			// create tinymce
			$editable = array(
				'editable_class' => 'mceUploadable',
				'attributes' => array(
					'name' => $mce_content_name,
					'id' => 'tiny_'.$mce_content_name
				),
				'secure_data' => array(
					'id-field' => 'id',				// req for save && upload
					'id-val' => $page_get_id_val,	// req for save && upload
					'upload-type' => $page_type			// req for upload
				)
			);
			$wrapper = getContentWrapper($editable);
			echo $wrapper['open'];
			echo isset($list[$mce_content_name]) ? htmlspecialchars_decode($list[$mce_content_name]) : '' ;
			echo $wrapper['close'];
			?>
			</div>
			<!-- end content area -->
			
			<!--  Email Notification's page properties_wrap -->
			<h2 id="notify-toggle" class="tiptip toggle" title="Provide Information about your Email Notifications.">Email Notifications</h2><br />
			<div id="notify-toggle-wrap">
			<?php
				$input_notify_from->createInputField();
				$input_notify_to->createInputField();
				$input_notify_title->createInputField();
				$input_notify_body->createInputField();
			?>
			</div><!-- end notify -->		   
			<div class='clearFix' ></div>
			
			  <!-- Success Message area -->
			<h2 id="message-toggle" class="tiptip toggle" title="Message Membership Applicant sees after successfully completing the Signup Form.<br>NOTE: this message should inform the user what happens next. Will they get a confirmation email automatically or does the admin need to &apos;OK&apos; is first?">Signup Success Message</h2>
			<div id="message-toggle-wrap">
			<?php if (isset($message['inline']) && array_key_exists('success', $message['inline'])) :?>
				<span class="<?php echo $message['inline']['success']['type'] ;?>"><?php echo $message['inline']['success']['msg'] ;?> </span>
			<?php endif; ?>
			<?php
			// create tinymce
			$editable = array(
				'editable_class' => 'mceUploadable',
				'attributes' => array(
					'name' => 'success',
					'id' => 'tiny_success'
				),
				'secure_data' => array(
					'id-field' => 'id',				// req for save && upload
					'id-val' => $page_get_id_val,	// req for save && upload
					'upload-type' => $page_type			// req for upload
				)
			);
			$wrapper = getContentWrapper($editable);
			echo $wrapper['open'];
			echo isset($list['success']) ? htmlspecialchars_decode($list['success']) : '' ;
			echo $wrapper['close'];
			?>			
			</div>
			<div class='clearFix' ></div>
			<!-- end Success area -->
			
			  <!-- Confirmed Message area -->
			<h2 id="confirmed-toggle" class="tiptip toggle" title="Message Membership Applicant sees after Confirming their Signup.<br>NOTE: this message should inform the user what happens next. <br>Are they finished? <br>Do they need to sign in to set their account up? <br>Do they still have to pay through paypal?">Confirmation Success Message</h2>
			<div id="confirmed-toggle-wrap">
			<?php if (isset($message['inline']) && array_key_exists('confirmed', $message['inline'])) :?>
				<span class="<?php echo $message['inline']['confirmed']['type'] ;?>"><?php echo $message['inline']['confirmed']['msg'] ;?> </span>
			<?php endif; ?>
			<?php
			// create tinymce
			$editable = array(
				'editable_class' => 'mceUploadable',
				'attributes' => array(
					'name' => 'confirmed',
					'id' => 'tiny_confirmed'
				),
				'secure_data' => array(
					'id-field' => 'id',				// req for save && upload
					'id-val' => $page_get_id_val,	// req for save && upload
					'upload-type' => $page_type			// req for upload
				)
			);
			$wrapper = getContentWrapper($editable);
			echo $wrapper['open'];
			echo isset($list['confirmed']) ? htmlspecialchars_decode($list['confirmed']) : '' ;
			echo $wrapper['close'];
			?>		
			</div>
			<div class='clearFix' ></div>
			<!-- end confirmed area -->			
			
			<!-- page buttons -->
			<input name="submit-home" type="hidden" value="submit" />
		</form>	
		<a id="submit-btn" class="blue button" href="#">Save</a>

		<a class="grey button" id="cancel-btn" href="<?php echo $parent_page; ?>">Cancel</a>
		<div class='clearFix' ></div> 
		<!-- end page buttons -->
		<?php 
			// ----------------------------------- revisions -------------------------------------
			// build the Revision Area
			$Revisions->createRevisionsArea($page_get_id_val, array(),array());
			// end revisions
?>	
	</div>
</div>	
	
<?php 

include($_config['admin_includes'] . "footer.php"); ?>
