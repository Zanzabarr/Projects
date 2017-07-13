<?php 
// initialize the page
$headerComponents = array();
$headerModule = 'newsletter';
include('../../includes/headerClass.php');
include('includes/functions.php');
include('classes/MailChimp.php');

$pageInit = new headerClass($headerComponents,$headerModule);

// access user data from headerClass
global $curUser;

// set the db variables
$table 				= 'newsletter_settings';		// name of the table this page posts to
$page_type			= 'newsletter_settings';		// name of uploadPageType for uploads component, also used to differentiate kinds of delete on parent page
$parent_page		= 'newsletter.php';				// the page that calls this one: used for delete and cancel form buttons, sets where to go afterwards
$page_get_id		= 'setid';				// the GET key passed forward with the db id for this item(page/blog post/...)
$page_get_id_val	= '1';						// this variable is used to store the GET id value of above

//encrypted fields
$enc_keys = array('apikey');

include_once($_config['admin_includes']."classes/form.php");

//get settings info
$new = logged_query("SELECT * FROM {$table} WHERE id = 1",0,array());
$list = array();
if(!empty($new)) $list=$new[0];

$page_get_id_val = 1;

// decrypt encrypted values
foreach($list as $k=>$v) {
	if(in_array($k,$enc_keys) && $v!="") {
		$list[$k] = decrypt($v);
	}
}
if($list['apikey']!="") {
	$apikey = $list['apikey'];
	$chimp = new MailChimp($apikey);
} else {
	$apikey = "";
	$message['banner'] = array ('heading' => 'Error Saving Settings', 'message' => 'You must complete Settings - Please follow Instructions below', 'type' => 'error' );
}

#==================
# process post
#==================
if(isset($_POST['submit-post'])){

// validate:
	$errorMsgType = 'errorMsg';
	$errorType = 'error';
	$errorHeading = 'Could Not Save';
	$errorMessage = 'all fields must be complete, not saved';

//validation keys
	$required = array('apikey', 'to_name');
	$multi_input_index = array();
	$int_inputs = array();
	$decimal_inputs = array();
	$binary_inputs = array();

	$testData = $_POST;
	unset($testData['submit-post']);
	$testform = new Form($testData);
	$testform->set_required_input($required);
	$testform->set_multi_check_input($multi_input_index);
	$testform->set_integer_input($int_inputs);
	$testform->set_decimal_input($decimal_inputs);
	$testform->set_binary_input($binary_inputs);
	$tmplist = $testform->validate();
	$errors = $testform->get_errors();
	
	// merge the form data with pre-existing data
	$list = array_merge($list, $tmplist);
	
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
	}
	else // set the success message
	{
		$message['banner'] = array ('heading' => 'Successfully Saved', 'type' => 'success');
		
		// get data to be posted to the db, and remove unwanted inputs
		$post_data = $list;
		
		unset(
			$post_data['page_get_id_val'],
			$post_data['submit-post']
		);
		
		$post_data['id'] = $page_get_id_val;
		
		$where_clause = "WHERE `id` = '1';";
		
		$result = logged_array_update($table, $post_data, $where_clause, array(), $enc_keys);
		$saveError = (bool) $result['error'];
		
		//get new $list
		$list = logged_query("SELECT * FROM {$table} WHERE id = 1",0,array());
		$list = $list[0];
		// decrypt encrypted values
		foreach($list as $k=>$v) {
			if(in_array($k,$enc_keys) && $v!="") {
				$list[$k] = decrypt($v);
			}
		}
		
		// banners: if there was an error, overwrite the previously set success message
		if ($saveError)	$message['banner'] = array ('heading' => 'Error Saving Settings', 'message' => 'Settings were not saved', 'type' => 'error' );
	}
}

//get api endpoint
$pos = strpos($apikey, "-");
$endpoint = substr($apikey,$pos+1);

if(isset($chimp)) {
	$clists = $chimp->call('/lists/list',array('apikey'=>$apikey));
	$data = $clists['data'];
} else {
	$data = array();
}

if (!isset($message)) $message = array();

//apikey
$val = isset($list['apikey']) ? $list['apikey'] : "";
$input_apikey = new inputField( 'API Key', 'apikey' );	
$input_apikey->toolTip('Recieve API Key from Mailchimp Account');
$input_apikey->value( $val );
$input_apikey->counterWarning(150);
$input_apikey->counterMax(254);
$input_apikey->size('medium');
$input_apikey->arErr($message);

//to_name
$val = isset($list['to_name']) ? $list['to_name'] : "";
$input_to_name = new inputField( 'To Name', 'to_name' );
$input_to_name->toolTip('Default Name for email - Recipients will see');
$input_to_name->value( $val );
$input_to_name->counterWarning(150);
$input_to_name->counterMax(254);
$input_to_name->size('medium');
$input_to_name->arErr($message);

// set the header varables and create the header
$pageResources ="<link rel='stylesheet' type='text/css' href='{$_config['admin_url']}modules/newsletter/style.css' />
<script type=\"text/javascript\" src=\"".$_config['admin_url']."modules/newsletter/js/settings.js\"></script>
";
$pageInit->createPageTop($pageResources);

?>
	<div class="page_container">
		<div id="h1"><h1>Newsletter Settings</h1></div>
		<div id="info_container">
			<?php
			// add subnav
			$pg = 'settings';	include("includes/subnav.php");
			
			//---------------------------------------Error Banner----------------------------- 
			// create a banner if $message['banner'] exists
			createBanner($message);

			?>
			<section class="explain">
				<p style="font-size:.9em;"><strong>Prerequisite:</strong> In order to use the newsletter module, you must have a Mailchimp account. Register or log in at <a href="http://mailchimp.com/" target="_blank">http://mailchimp.com/</a>. You do not have to be logged in at Mailchimp to use this application.</p>
			</section>
			<form action="<?php echo htmlentities($_SERVER['PHP_SELF']); ?>" method="post" enctype="application/x-www-form-urlencoded" name="settings_form" id="settings_form" class="form">

				<!-- general preferences -->
				<h2 class="tiptip toggle" id="prefs-toggle" title="Set your Mailchimp settings">Mailchimp</h2>
				<div id="prefs-toggle-wrap">
				<h2 class="tiptip toggle smallh2" id="explain-toggle" title="API Key Insructions">Instructions</h2>
				<div id="explain-toggle-wrap">
					<section class="explain">
						<p>To get your API Key:<br />If the field below is blank, go to <a href='http://mailchimp.com' target='_blank'>http://mailchimp.com</a> in your browser. Log in, click your name in the left column, and choose "Account Settings". Choose the menu "Extras" and then "API Keys".</p><p>Copy, Paste, and Save your Mailchimp API Key here: </p>
					</section>
				</div>
				<?php
					$input_apikey->createInputField();
					$input_to_name->createInputField();
				?>
					
				</div><!-- end mailchimp settings -->
				<!-- page buttons -->
				<div class='clearFix' ></div>
				<input name="page_get_id_val" type="hidden" value="1"/>

				<input name="submit-post" type="hidden" value="submit" />
			</form>	
			<a id="submit-btn" class="blue button formbutton" href="#">Save</a>

			<a class="grey button formbutton" id="cancel-btn" href="<?php echo $parent_page; ?>">Cancel</a>
			<div class="clear"></div>
			<hr />
			
			<div class='clear1em' ></div> 
			<h2>Current Lists</h2>
			<!-- end page buttons -->
			<section class='explain'>
			<?php
			if(isset($clists) && !$clists['total']>0) {
			?>
			<p>You must have at least one list at Mailchimp. Login at <a href='https://login.mailchimp.com/' target="_blank">https://login.mailchimp.com/</a> and create your first list.</p>
			<?php
			} else {
			?>
			<p>Login at <a href='https://login.mailchimp.com/' target="_blank">https://login.mailchimp.com/</a> to create lists.</p>
			<?php
			}
			?>
			</section>
			<table id='tblCouponPage' border="0" cellspacing="0" cellpadding="0">
			<tr><th colspan=2 style="text-align:left;">List Name</th></tr>
				<?php
				if(!is_array($data) || !count($data))
				{
					echo "<tr><td colspan='4' style='text-align:center'>No lists are active at this time.</td></tr>";
				}
				else 
				{
					foreach ($data as $d){
					?>
						<tr>
							<td colspan=2 style='text-align:left;padding-left:2em;'><?php echo $d['name']; ?></td>
						</tr>
				<?php
					}
				}
			?>
			</table>
			<div class="clearFix"></div>
			<hr />

			<div class='clearFix'></div> 
		</div>
	</div>

<?php 
include($_config['admin_includes']."footer.php"); 
?>