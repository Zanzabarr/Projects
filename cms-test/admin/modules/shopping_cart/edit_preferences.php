<?php 
// initialize the page
$headerComponents = array('revisions');
$headerModule = 'shopping_cart';
include('../../includes/headerClass.php');

// must have admin privileges to access this page
if ($_SESSION['user']['admin'] != 'yes')
{
	header( "Location: " . $_config['admin_url'] . "modules/{$headerModule}/index.php" );
	exit;
}

$pageInit = new headerClass($headerComponents,$headerModule);

// access user data from headerClass
global $curUser;

// set the db variables
$table 				= 'ecom_preferences';		// name of the table this page posts to
$mce_content_name	= 'desc';					// the tinyMCE editor always uses the id:content, 
												//   but db tables usually use something specific to that table for main content,
												//   eg, blog has 'post', category has 'desc', but page does use 'content': set the value here
$revision_table 	= 'ecom_preferences_rev';	// name of the revision table
$revision_table_id 	= 'pref_id'; 		// the id in the revision table that points to the id of the original table
$page_type			= 'ecom_preferences';		// name of uploadPageType for uploads component, also used to differentiate kinds of delete on parent page
$parent_page		= 'index.php';				// the page that calls this one: used for delete and cancel form buttons, sets where to go afterwards
$page_get_id		= 'prefid';				// the GET key passed forward with the db id for this item(page/blog post/...)
$page_get_id_val	= '1';						// this variable is used to store the GET id value of above

$hasCart = $_config['shopping_cart']['preferences']['hasCart']==1 ? true : false;
$includePricing = $_config['shopping_cart']['preferences']['includePricing']==1 ? true : false;

//encrypted fields
$enc_keys = array();

include_once($_config['admin_includes']."classes/form.php");

// this page has revisions, instantiate it
include_once($_config['components']."revisions/revisions.php");
$Revisions = new revisions($revision_table, $revision_table_id)	;
$Revisions->setOrderByField('date_updated');

// if this arrived from revision history, get info
if ( $Revisions->isRevision() )
{
	$list = $Revisions->getRevisionData($page_get_id_val);
	$message = $Revisions->getResultMessage($page_get_id_val);
}
// otherwise get the preferences info
else {
	$new = logged_query("SELECT * FROM {$table} WHERE id = 1",0,array()); 
	$list=$new[0];
	if(empty($list['date_created'])) $list['date_created'] = date('Y-m-d H:i:s');
	$page_get_id_val = 1;
}
// decrypt encrypted values
	foreach($list as $k=>$v) {
		if(in_array($k,$enc_keys) && $v!="") {
			$list[$k] = decrypt($v);
		}
	}
	
#==================
# process post
#==================
if(isset($_POST['submit-post'])){
//set no_shipping as there is no choice
	$_POST['no_shipping'] = 1;
	
	if($_POST['phone']!="") {	//remove symbols from phone number
		$_POST['phone'] = preg_replace("/[^a-zA-Z0-9]/", "", $_POST['phone']);
	}

// validate:
	$errorMsgType = 'errorMsg';
	$errorType = 'error';
	$errorHeading = 'Could Not Save';
	$errorMessage = 'all fields must be complete, not saved';

//validation keys
	$required = array('seo_title', 'seo_description', 'seo_keywords','returns_policy','phone');
	$multi_input_index = array();
	$int_inputs = array();
	$decimal_inputs = array();
	$binary_inputs = array('hasCart','includePricing','limit_collection','one_collection_per_product','purchasing_on','shipping_on');

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

		if (array_key_exists($revision_table_id, $_GET) && is_numeric($_GET[$revision_table_id]) ) $page_get_id_val = $_GET[$revision_table_id];
	}
	else // set the success message
	{
		$message['banner'] = array ('heading' => 'Successfully Saved', 'type' => 'success');
		
		// get data to be posted to the db, and remove unwanted inputs
		$post_data = $list;
		
		unset(
			$post_data['page_get_id_val'],
			$post_data['submit-post'],
			$post_data['date_created']
		);
		
		$post_data['date_updated'] = date('Y-m-d H:i:s');
		$post_data['id'] = $page_get_id_val;
		
		$where_clause = "WHERE `id` = '1';";
		
		$result = logged_array_update($table, $post_data, $where_clause, array(), $enc_keys);
		$saveError = (bool) $result['error'];
		
		if(! $saveError)
		{
			// add in revision specific data
			$post_data['date_created'] = $list['date_created'];
			$post_data[$revision_table_id] = $page_get_id_val;
			unset($post_data['id']);
			$result = logged_array_insert($revision_table, $post_data, $enc_keys);
			// don't worry if there was an error inserting into rev table
			$rev_ins_id = $result['insert_id'];
			
			//dynamic values - set $_config to match new values
			$_config['shopping_cart']['preferences']['hasCart'] = $post_data['hasCart'];
			$hasCart = $post_data['hasCart'];
			$_config['shopping_cart']['preferences']['includePricing'] = $post_data['includePricing'];
			$includePricing = $post_data['includePricing']==1 ? true : false;
			$_config['limit_collection'] = $post_data['limit_collection'];
			$_config['one_collection_per_product'] = $post_data['one_collection_per_product'];
			$_config['currency'] = $post_data['currency'];
			$_config['shopping_cart']['preferences']['purchasing_on'] = $post_data['purchasing_on'];
			$_config['shopping_cart']['preferences']['shipping_on'] = $post_data['shipping_on'];
			$_config['no_note'] = $post_data['no_note'];
			$_config['note_comment'] = $post_data['note_comment'];
			$_config['no_shipping'] = $post_data['no_shipping'];
			
			//dynamic environment changes
			$_config['environment'] = $post_data['environment'];
			//will be 'live' if sending to real payment processor, 'dev' for sandbox
			$_config['sandbox'] = $_config['environment']=="live" ? false : true;
			$_config['orderemail'] = $post_data['orderemail'];
			
			//gateway
			if(isset($post_data['gateway'])) {
				$_config['gateway'] = $post_data['gateway'];
			}
		}
		
		// banners: if there was an error, overwrite the previously set success message
		if ($saveError)	$message['banner'] = array ('heading' => 'Error Saving Preferences', 'message' => 'Preferences were not saved', 'type' => 'error' );
	}
}
if (!isset($message)) $message = array();

// preferences
// hasCart
$val = (isset($post_data['hasCart'])) ? $post_data['hasCart'] : $list['hasCart'];
$input_hasCart = new inputField( 'Use Cart', 'hasCart' );
$input_hasCart->toolTip('Allow products to be added to cart? Can be used with or without online purchasing. Required for Online Purchasing and Shipping.');
$input_hasCart->type('checkbox');
$input_hasCart->value($val);
$input_hasCart->arErr($message);

// includePricing
$val = (isset($post_data['includePricing'])) ? $post_data['includePricing'] : $list['includePricing'];
$input_includePricing = new inputField( 'Include Pricing', 'includePricing' );
$input_includePricing->toolTip('Include pricing information in cart? Required for online purchasing.');
$input_includePricing->type('checkbox');
$input_includePricing->value($val);
$input_includePricing->arErr($message);

// purchasing_on
$val = (isset($post_data['purchasing_on'])) ? $post_data['purchasing_on'] : $list['purchasing_on'];
$input_purchasing_on = new inputField( 'Online Purchasing', 'purchasing_on' );
$input_purchasing_on->toolTip('Customers purchase from your cart?');
$input_purchasing_on->type('checkbox');
$input_purchasing_on->value($val);
$input_purchasing_on->arErr($message);

// shipping_on
$val = (isset($post_data['shipping_on'])) ? $post_data['shipping_on'] : $list['shipping_on'];
$input_shipping_on = new inputField( 'Use Shipping', 'shipping_on' );
$input_shipping_on->toolTip('Products will need to be shipped / delivered.');
$input_shipping_on->type('checkbox');
$input_shipping_on->value($val);
$input_shipping_on->arErr($message);

// alternate text when Pricing not included - altPriceText
$val = isset($list['altPriceText']) ? htmlspecialchars_decode($list['altPriceText']) : "call for pricing";
$input_altPriceText = new inputField( 'Alt Price Text', 'altPriceText' );	
$input_altPriceText->toolTip('Alternative text if pricing not included in cart');
$input_altPriceText->value( $val );
$input_altPriceText->counterWarning(30);
$input_altPriceText->counterMax(50);
$input_altPriceText->size('medium');
$input_altPriceText->arErr($message);

//limit_collection
$val = isset($post_data['limit_collection']) ? $post_data['limit_collection'] : $list['limit_collection'];
$input_limit_collection = new inputField( 'Limit Collection', 'limit_collection' );
$input_limit_collection->toolTip('If not selected, all categories can be part of all collections.');
$input_limit_collection->type('checkbox');
$input_limit_collection->value($val);
$input_limit_collection->arErr($message);

//one_collection_per_product
$val = isset($post_data['one_collection_per_product']) ? $post_data['one_collection_per_product'] : $list['one_collection_per_product'];
$input_one_collection_per_product = new inputField( 'One Collection Per Product', 'one_collection_per_product' );
$input_one_collection_per_product->toolTip('If selected, each product can only be part of one collection.');
$input_one_collection_per_product->type('checkbox');
$input_one_collection_per_product->value($val);
$input_one_collection_per_product->arErr($message);

//currency
$val = isset($post_data['currency']) ? $post_data['currency'] : $list['currency'];
$input_currency = new inputField( 'Currency', 'currency' );
$input_currency->toolTip('Select appropriate currency code: USD = $US, CAD = $Canada');
$input_currency->type('select');
$input_currency->selected( $val );
$input_currency->option( 'CAD', 'CAD' );
$input_currency->option( 'USD', 'USD' );
$input_currency->arErr($message);

// orderemail
$val = isset($list['orderemail']) ? htmlspecialchars_decode($list['orderemail']) : "";
$input_orderemail = new inputField( 'Order Email', 'orderemail' );	
$input_orderemail->toolTip('Email to receive web orders');
$input_orderemail->value( $val );
$input_orderemail->counterWarning(150);
$input_orderemail->counterMax(254);
$input_orderemail->size('medium');
$input_orderemail->arErr($message);

//phone
$val = isset($post_data['phone']) ? $post_data['phone'] : $list['phone'];
$input_phone = new inputField( 'Contact Phone', 'phone' );
$input_phone->toolTip('Contact Phone Number');
$input_phone->value($val);
$input_phone->counterWarning(15);
$input_phone->counterMax(25);
$input_phone->size('medium');
$input_phone->arErr($message);

//allow customer note
$val = isset($post_data['no_note']) ? $post_data['no_note'] : $list['no_note'];
$input_no_note = new inputField( 'Customer Note', 'no_note' );
$input_no_note->toolTip('Allow customer to include a note with payment?');
$input_no_note->type('select');
$input_no_note->selected( $val );
$input_no_note->option( 0, 'Yes' );
$input_no_note->option( 1, 'No' );
$input_no_note->arErr($message);

//payment gateway
$val = isset($post_data['gateway']) ? $post_data['gateway'] : $list['gateway'];
$input_gateway = new inputField('Payment Processor','gateway');
$input_gateway->toolTip('Choose your payment processor');
$input_gateway->type('select');
$input_gateway->selected($val);
$input_gateway->option('nogateway','None');
$input_gateway->option('moneris','Moneris');
$input_gateway->option('paypal','Paypal');
$input_gateway->arErr($message);

// comment for customer note
$val = isset($list['note_comment']) ? htmlspecialchars_decode($list['note_comment']) : "Add special instructions for {$_config['company_name']}";
$input_note_comment = new inputField( 'Note Comment', 'note_comment' );	
$input_note_comment->toolTip('Instructional prompt that will appear above customer note field');
$input_note_comment->value( $val );
$input_note_comment->counterWarning(65);
$input_note_comment->counterMax(100);
$input_note_comment->size('medium');
$input_note_comment->arErr($message);

// seo title
$val = isset($list['seo_title']) ? htmlspecialchars_decode($list['seo_title']) : '';
$input_seo_title = new inputField( 'SEO Title', 'seo_title' );	
$input_seo_title->toolTip('Start with most important words<br />65 characters or less.');
$input_seo_title->value( $val );
$input_seo_title->counterWarning(65);
$input_seo_title->counterMax(100);
$input_seo_title->size('large');
$input_seo_title->arErr($message);

// seo description
$val = isset($list['seo_description']) ? htmlspecialchars_decode($list['seo_description']) : '';
$input_seo_description = new inputField( 'Description', 'seo_description' );	
$input_seo_description->toolTip('Start with the same words used in the title<br />150 characters or less.');
$input_seo_description->type('textarea');
$input_seo_description->value( $val );
$input_seo_description->counterWarning(150);
$input_seo_description->counterMax(250);
$input_seo_description->size('large');
$input_seo_description->arErr($message);

// seo seo_keywords
$val = isset($list['seo_keywords']) ? htmlspecialchars_decode($list['seo_keywords']) : '';
$input_seo_keywords = new inputField( 'Keywords', 'seo_keywords' );	
$input_seo_keywords->toolTip('List of phrases, separated by commas, with most important phrases first. <br /> Note: the words have to appear somewhere on the page.');
$input_seo_keywords->type('textarea');
$input_seo_keywords->value( $val );
$input_seo_keywords->counterMax(1000);
$input_seo_keywords->size('large');
$input_seo_keywords->arErr($message);

// returns_policy
$val = isset($list['returns_policy']) ? htmlspecialchars_decode($list['returns_policy']) : '';
$input_returns_policy = new inputField( 'Returns Policy', 'returns_policy' );	
$input_returns_policy->toolTip('This policy required by both Visa and Mastercard.');
$input_returns_policy->type('textarea');
$input_returns_policy->value( $val );
$input_returns_policy->counterWarning(150);
$input_returns_policy->counterMax(250);
$input_returns_policy->size('large');
$input_returns_policy->arErr($message);

/** end preferences **/

// set the header varables and create the header
$pageResources ="<link rel='stylesheet' type='text/css' href='styles.css' />
<script type=\"text/javascript\" src=\"".$_config['admin_url']."modules/shopping_cart/js/edit_preferences.js\"></script>
";//
$pageInit->createPageTop($pageResources);
?>
 <div class="page_container">
	<div id="h1"><h1>Ecommerce Preferences</h1></div>
    <div id="info_container">
		<?php
		// add subnav
		$pg = 'preferences';	include("includes/subnav.php");
		
		//---------------------------------------Error Banner----------------------------- 
		// create a banner if $message['banner'] exists
		createBanner($message);

		?>
		<form action="<?php echo htmlentities($_SERVER['PHP_SELF']); ?>" method="post" enctype="application/x-www-form-urlencoded" name="form_data" id="form_data" class="form">
		
			<!-- general preferences -->
			<h2 class="tiptip toggle" id="prefs-toggle" title="Set your general preferences">General Preferences</h2><br />
			<div id="prefs-toggle-wrap">
			<?php
				$input_hasCart->createInputField();
				$input_includePricing->createInputField();
				$input_altPriceText->createInputField();
				$input_purchasing_on->createInputField();
				$input_shipping_on->createInputField();
				$input_limit_collection->createInputField();
				$input_one_collection_per_product->createInputField();
				$input_currency->createInputField();
				$input_orderemail->createInputField();
				$input_phone->createInputField();
				$input_returns_policy->createInputField();

				if ($hasCart) {
					if($list['environment'] == "live") {
						$livechk = "checked";
						$devchk = "";
					} else {
						$livechk = "";
						$devchk = "checked";
					}
				} else {
					$livechk = "";
					$devchk = "checked";
				}
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
			</div><!-- end general preferences -->
			<section id="gateway-options">
				<h2 class="tiptip toggle" id="gateway-toggle" title="Payment Gateway">Payment Gateway</h2><br />
				<?php $input_gateway->createInputField();?>
				<div id="gateway-toggle-wrap">
					
				</div>
			</section><!-- end gateway-options -->
			<div class='clearFix' ></div>

			<h2 id="content-toggle" class="tiptip toggle" title="Content shown above product listings.">Products Page Description</h2>
			<?php if (isset($message['inline']) && array_key_exists($mce_content_name, $message['inline'])) :?>
				<span class="<?php echo $message['inline'][$mce_content_name]['type'] ;?>"><?php echo $message['inline'][$mce_content_name]['msg'] ;?> </span>
			<?php endif; ?>
			<br />
			
			<?php
			// create tinymce
			$jump_target = 'shopping';
			
			$editable = array(
				'editable_class' => 'mceEditor',
				'attributes' => array(
					'name' => 'desc',
					'id' => 'content',
					'data-jump-type' => "front",
					'data-jump' => "../../../".$jump_target
				),
				'secure_data' => array(
					'id-field' => 'id',				// req for save && upload
					'id-val' => $page_get_id_val	// req for save && upload
				)
			);
			$wrapper = getContentWrapper($editable);
			echo $wrapper['open'];
			echo isset($list['desc']) ? htmlspecialchars_decode($list['desc']) : '' ;
			echo $wrapper['close'];
						
			?>

			<!-- end content area -->

			<!-- SEO area -->
			<h2 class="tiptip toggle" id="seo-toggle" ;" title="Search Engine Optimization fields">Search Engine Optimization</h2><br />
			<div id="seo-toggle-wrap">
			<?php
			
				$val = isset($list['name']) ? htmlspecialchars_decode($list['name']) : '';
				$input_name = new inputField( "Heading", 'name' );	
				$input_name->toolTip('Heading that appears over product listings.');
				$input_name->value( $val );
				$input_name->counterMax(250);
				$input_name->arErr($message);
				$input_name->createInputField();
				
				$input_seo_title->createInputField();
				$input_seo_description->createInputField();
				$input_seo_keywords->createInputField();
			?>
			</div><!-- end SEO area -->
			
			<!-- page buttons -->
			<div class='clearFix' ></div>
			<input name="page_get_id_val" type="hidden" value="1"/>

			<input name="submit-post" type="hidden" value="submit" />
		</form>	
		<a id="submit-btn" class="blue button" href="#">Save</a>

		<a class="grey button" id="cancel-btn" href="<?php echo $parent_page; ?>">Cancel</a>
		<div class='clearFix' ></div> 
		<!-- end page buttons -->

		<?php 
			
	// ----------------------------------- revisions -------------------------------------
		// add GET data to the return url
		$extraGET = array($page_get_id => $page_get_id_val);

		// build the Revision Area
		$Revisions->createRevisionsArea($page_get_id_val, array(),$extraGET);
		// end revisions
?>
	</div>
</div>
<script>
$(document).ready( function() {
	var gateway = "<?php echo $_config['gateway']; ?>";
	if(gateway != "nogateway") {
		$.ajax({
			type: "POST",
			url: "ajax/gateway_frame.php",
			data:{ gateway: gateway },
			cache: false,
			success: function(result){
				$('#gateway-toggle-wrap').html(result);
				$('#gateway-toggle-wrap').slideDown();
			}
		});
	} else {
		$('#gateway-toggle-wrap').slideUp();
	}
});
</script>
<?php 

include($_config['admin_includes'] . "footer.php"); ?>