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
$table 				= 'ecom_ship_prefs';		// name of the table this page posts to
$revision_table 	= 'ecom_ship_prefs_rev';	// name of the revision table
$revision_table_id 	= 'pref_id'; 		// the id in the revision table that points to the id of the original table
$page_type			= 'ecom_shipping';		// name of uploadPageType for uploads component, also used to differentiate kinds of delete on parent page
$parent_page		= 'index.php';				// the page that calls this one: used for delete and cancel form buttons, sets where to go afterwards
$page_get_id		= 'prefid';				// the GET key passed forward with the db id for this item(page/blog post/...)
$page_get_id_val	= '1';						// this variable is used to store the GET id value of above
$includePricing = $_config['shopping_cart']['preferences']['includePricing']==1 ? true : false;
//encrypted fields
$enc_keys = array('fedex_key','fedex_password','fedex_meter','fedex_shipaccount','fedex_billaccount','fedex_dutyaccount','fedex_freightaccount','fedex_trackaccount','cp_username','cp_password','cp_customerNumber');

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
// otherwise get the shipping preferences info
else {
	$new = logged_query("SELECT * FROM {$table} WHERE id = 1",0,array());
	$list=$new[0];
	if(empty($list['date_created'])) $list['date_created'] = date('Y-m-d H:i:s');
	$page_get_id_val = 1;
}
// decrypt encrypted values
	foreach($list as $k=>$v) {
		if(in_array($k,$enc_keys) && $v != "") {
			$list[$k] = decrypt($v);
		}
	}

#==================
# process post
#==================
if(isset($_POST['submit-post'])){
	//BOOLEAN CHECKBOXES - set value to 1 if present, 0 if not
	//$_POST['free_shipping'] = isset($_POST['free_shipping']) ? 1 : 0;
	//$_POST['fedex_on'] = isset($_POST['fedex_on']) ? 1 : 0;
	
	// validate:
	$errorMsgType = 'errorMsg';
	$errorType = 'error';
	$errorHeading = 'Could Not Save';
	$errorMessage = 'all fields must be complete, not saved';


//validation keys
	$required = array();

	$multi_input_index = array();

	$int_inputs = array();

	$decimal_inputs = array('free_ship', 'min_ship', 'ship_per', 'flat_rate');

	$binary_inputs = array('free_shipping', 'fedex_on', 'canpost_on');


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
	
	//special tests:
	// if it isn't free shipping, at least one of min_ship && ship_per must be set
	if (!$list['free_shipping'] && !$list['min_ship'] && !$list['ship_per'])
	{
		$message['inline']['ship_per'] = array('type' => 'errorMsg','msg' => 'At least one of Minimum Charge');
		$message['inline']['min_ship'] = array('type' => 'errorMsg','msg' => 'or Shipping Per must be set.');
	}
	
	// if an error was found, create the error banner
	$errorsExist = isset($message['inline']) ? count($message['inline']) : 0 ;
	if ($errorsExist )
	{
		$message['banner'] = array ('heading' => $errorHeading, 'message' => $errorMessage, 'type' => $errorType);

		if (array_key_exists($revision_table_id, $_GET) && is_numeric($_GET[$revision_table_id]) ) $page_get_id_val = $_GET[$revision_table_id];
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
		
		$post_data['date_updated'] = date('Y-m-d H:i:s');
		$post_data['id'] = $page_get_id_val;
		
		$where_clause = "WHERE `id` = '1';";
		
		$result = logged_array_update($table, $post_data, $where_clause, array(), $enc_keys );
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

		}
		
		// banners: if there was an error, overwrite the previously set success message
		if ($saveError)	$message['banner'] = array ('heading' => 'Error Saving Preferences', 'message' => mysql_error(), 'type' => 'error' );
	}

}
if (!isset($message)) $message = array();

// shipping preferences
//weightType
$weightType = htmlspecialchars_decode($list['weight_type']);
$input_weight_type = new inputField( 'Weight Unit', 'weight_type' );	
$input_weight_type->toolTip('Choose how to calculate weights');
$input_weight_type->tipType('top');
$input_weight_type->type('select');
$input_weight_type->selected( $weightType );
$input_weight_type->size('small');
$input_weight_type->option( 'LB' ,"LB" );
$input_weight_type->option( 'KG' ,"KG" );

//dimensionUnit
$dimensionUnit = htmlspecialchars_decode($list['dimensionUnit']);
$input_dimensionUnit = new inputField( 'Dimension Unit', 'dimensionUnit' );	
$input_dimensionUnit->toolTip('Choose how to measure package dimensions');
$input_dimensionUnit->tipType('top');
$input_dimensionUnit->type('select');
$input_dimensionUnit->selected( $dimensionUnit );
$input_dimensionUnit->size('small');
$input_dimensionUnit->option( 'CM', 'CM' );
$input_dimensionUnit->option( 'IN', 'IN' );

// free_shipping
$val = (isset($post_data['free_shipping'])) ? $post_data['free_shipping'] : $list['free_shipping'];
$input_free_shipping = new inputField( "Free Shipping", 'free_shipping' );	
$input_free_shipping->toolTip('Do not charge for shipping at all.');
$input_free_shipping->type( 'checkbox' );
$input_free_shipping->value($val);
$input_free_shipping->arErr($message);

// free shipping limit: (either this or min shipping charge can be set
$val = isset($list['free_ship']) ? htmlspecialchars_decode($list['free_ship']) : '';
$input_free_ship = new inputField( "Free Until: <span class='symbol'>\$</span>", 'free_ship' );	
$input_free_ship->toolTip('Do not charge for shipping unless it will cost more than this amount.');
$input_free_ship->value( $val );
$input_free_ship->tipType('top');
$input_free_ship->counterMax(14);
$input_free_ship->arErr($message);

// Shipping Charge Per Lb: (either this or min shipping charge can be set)
$val = isset($list['ship_per']) ? htmlspecialchars_decode($list['ship_per']) : '';
$input_ship_per = new inputField( "Shipping per <span class='weight_class'>{$weightType}</span>: <span class='symbol'>\$</span>", 'ship_per' );	
$input_ship_per->toolTip("Charge this amount by weight for shipping.");
$input_ship_per->value( $val );
$input_ship_per->tipType('top');
$input_ship_per->counterMax(14);
$input_ship_per->arErr($message);

// Flat Rate for fallback shipping
$val = isset($list['flat_rate']) ? htmlspecialchars_decode($list['flat_rate']) : '';
$input_flat_rate = new inputField( "Flat Rate Shipping: <span class='symbol'>\$</span>", 'flat_rate' );	
$input_flat_rate->toolTip('Fallback Flat Rate Shipping in case of Shipping service server failure.');
$input_flat_rate->value( $val );
$input_flat_rate->tipType('top');
$input_flat_rate->counterMax(14);
$input_flat_rate->arErr($message);

// Flat Rate Delivery Days
$val = isset($list['flat_rate_days']) ? htmlspecialchars_decode($list['flat_rate_days']) : '';
$input_flat_rate_days = new inputField( "Days to Deliver Flat Rate:","flat_rate_days" );
$input_flat_rate_days->toolTip("Number of Days to deliver Flat Rate Shipping.");
$input_flat_rate_days->value($val);
$input_flat_rate_days->tipType('top');
$input_flat_rate_days->arErr($message);

// Min Shipping Charge: (either this or min shipping charge can be set
$val = isset($list['min_ship']) ? htmlspecialchars_decode($list['min_ship']) : '';
$input_min_ship = new inputField( "Minimum Charge: <span class='symbol'>\$</span>", 'min_ship' );	
$input_min_ship->toolTip('Charge at least this amount for shipping.');
$input_min_ship->value( $val );
$input_min_ship->tipType('top');
$input_min_ship->counterMax(14);
$input_min_ship->arErr($message);

// Max Shipping Charge: (either this or min shipping charge can be set
$val = isset($list['max_ship']) ? htmlspecialchars_decode($list['max_ship']) : '';
$input_max_ship = new inputField( "Maximum Charge: <span class='symbol'>\$</span>", 'max_ship' );	
$input_max_ship->toolTip('Charge no more than this amount for shipping (or unlimited if not set).');
$input_max_ship->value( $val );
$input_max_ship->counterMax(14);
$input_max_ship->arErr($message);

// process_text	 - process time disclaimer for display on cart
$val = isset($list['process_text']) ? htmlspecialchars_decode($list['process_text']) : '';
$input_process_text = new inputField( "Order Process Time", "process_text" );
$input_process_text->toolTip("Disclaimer of Order Processing Time - ex: Please allow 24-48 hours for order processing.");
$input_process_text->size('large');
$input_process_text->value($val);
$input_process_text->counterMax(256);
$input_process_text->arErr($message);


// street_address
$val = isset($list['street_address']) ? htmlspecialchars_decode($list['street_address']) : '';
$input_street_address = new inputField( "Street Address", 'street_address' );
$input_street_address->toolTip('"Ship From" Street Address');
$input_street_address->size('large');
$input_street_address->value( $val );
$input_street_address->counterMax(254);
$input_street_address->arErr($message);

// city
$val = isset($list['city']) ? htmlspecialchars_decode($list['city']) : '';
$input_city = new inputField( "City", 'city' );	
$input_city->toolTip('City to ship from');
$input_city->value( $val );
$input_city->counterMax(120);
$input_city->arErr($message);

// province
$val = isset($list['province']) ? htmlspecialchars_decode($list['province']) : '';
$input_province = new inputField( "Province/State (2)", 'province' );	
$input_province->toolTip('Province/State - 2 characters');
$input_province->value( $val );
$input_province->counterMax(2);
$input_province->arErr($message);

// postal_code
$val = isset($list['postal_code']) ? htmlspecialchars_decode($list['postal_code']) : '';
$input_postal_code = new inputField( "Postal/Zip Code", 'postal_code' );	
$input_postal_code->toolTip('Postal/Zip Code');
$input_postal_code->value( $val );
$input_postal_code->counterMax(20);
$input_postal_code->arErr($message);

// country
$val = isset($list['country']) ? htmlspecialchars_decode($list['country']) : '';
$input_country = new inputField( "Country (2)", 'country' );	
$input_country->toolTip('Country - 2 characters');
$input_country->value( $val );
$input_country->counterMax(2);
$input_country->arErr($message);

/** end shipping preferences **/

/** Fedex Options **/
//fedex_on
$val = (isset($post_data['fedex_on'])) ? $post_data['fedex_on'] : $list['fedex_on'];
$input_fedex_on = new inputField( "Use Fedex?", 'fedex_on' );	
$input_fedex_on->toolTip('Allow customers to choose Fedex shipping and rates online.');
$input_fedex_on->type( 'checkbox' );
$input_fedex_on->value($val);
$input_fedex_on->arErr($message);

// Fedex Account Key
$val = isset($post_data['fedex_key']) ? $post_data['fedex_key'] : $list['fedex_key'];
$input_fedex_key = new inputField( "Account Key:", 'fedex_key' );	
$input_fedex_key->toolTip('Account Key obtained from Fedex Web Services');
$input_fedex_key->value( $val );
$input_fedex_key->counterMax(30);
$input_fedex_key->arErr($message);

//Fedex Account Password
$val = isset($post_data['fedex_password']) ? $post_data['fedex_password'] : $list['fedex_password'];
$input_fedex_password = new inputField( "Account Password:", 'fedex_password' );	
$input_fedex_password->toolTip('Obtained from Fedex Web Services.<br />THIS IS NOT THE LOGIN PASSWORD.');
$input_fedex_password->value( $val );
$input_fedex_password->counterMax(50);
$input_fedex_password->arErr($message);

//Fedex Meter Number
$val = isset($post_data['fedex_meter']) ? $post_data['fedex_meter'] : $list['fedex_meter'];
$input_fedex_meter = new inputField( "Meter Number:", 'fedex_meter' );	
$input_fedex_meter->toolTip('Obtained from Fedex Web Services.');
$input_fedex_meter->value( $val );
$input_fedex_meter->counterMax(15);
$input_fedex_meter->arErr($message);

//Fedex Ship Account Number
$val = isset($post_data['fedex_shipaccount']) ? $post_data['fedex_shipaccount'] : $list['fedex_shipaccount'];
$input_fedex_shipaccount = new inputField( "Shipping Account Number:", 'fedex_shipaccount' );	
$input_fedex_shipaccount->toolTip('Obtained from Fedex Web Services.');
$input_fedex_shipaccount->value( $val );
$input_fedex_shipaccount->counterMax(9);
$input_fedex_shipaccount->arErr($message);

//Fedex Billing Account Number
$val = isset($post_data['fedex_billaccount']) ? $post_data['fedex_billaccount'] : $list['fedex_billaccount'];
$input_fedex_billaccount = new inputField( "Billing Account Number:", 'fedex_billaccount' );	
$input_fedex_billaccount->toolTip('Obtained from Fedex Web Services.');
$input_fedex_billaccount->value( $val );
$input_fedex_billaccount->counterMax(9);
$input_fedex_billaccount->arErr($message);

//Fedex Duty Account Number
$val = isset($post_data['fedex_dutyaccount']) ? $post_data['fedex_dutyaccount'] : $list['fedex_dutyaccount'];
$input_fedex_dutyaccount = new inputField( "Duty Account Number:", 'fedex_dutyaccount' );	
$input_fedex_dutyaccount->toolTip('Obtained from Fedex Web Services.');
$input_fedex_dutyaccount->value( $val );
$input_fedex_dutyaccount->counterMax(9);
$input_fedex_dutyaccount->arErr($message);

//Fedex Freight Account Number
$val = isset($post_data['fedex_freightaccount']) ? $post_data['fedex_freightaccount'] : $list['fedex_freightaccount'];
$input_fedex_freightaccount = new inputField( "Freight Account Number:", 'fedex_freightaccount' );	
$input_fedex_freightaccount->toolTip('Obtained from Fedex Web Services.');
$input_fedex_freightaccount->value( $val );
$input_fedex_freightaccount->counterMax(9);
$input_fedex_freightaccount->arErr($message);

//Fedex Track Account Number
$val = isset($post_data['fedex_trackaccount']) ? $post_data['fedex_trackaccount'] : $list['fedex_trackaccount'];
$input_fedex_trackaccount = new inputField( "Track Account Number:", 'fedex_trackaccount' );
$input_fedex_trackaccount->toolTip('Obtained from Fedex Web Services.');
$input_fedex_trackaccount->value( $val );
$input_fedex_trackaccount->counterMax(9);
$input_fedex_trackaccount->arErr($message);

/** end Fedex Options **/
/** Canada Post Settings **/

//canpost_on
$val = (isset($post_data['canpost_on'])) ? $post_data['canpost_on'] : $list['canpost_on'];
$input_canpost_on = new inputField( "Use Canada Post?", 'canpost_on' );	
$input_canpost_on->toolTip('Allow customers to choose Canada Post shipping and rates online.');
$input_canpost_on->type( 'checkbox' );
$input_canpost_on->value($val);
$input_canpost_on->arErr($message);

//Canada Post username
$val = isset($post_data['cp_username']) ? $post_data['cp_username'] : $list['cp_username'];
$input_cp_username = new inputField( "Canada Post Username:", 'cp_username' );	
$input_cp_username->toolTip('Canada Post Account Username');
$input_cp_username->value( $val );
$input_cp_username->counterMax(50);
$input_cp_username->arErr($message);

//Canada Post password
$val = isset($post_data['cp_password']) ? $post_data['cp_password'] : $list['cp_password'];
$input_cp_password = new inputField( "Canada Post Password:", 'cp_password' );	
$input_cp_password->toolTip('Canada Post Account Password');
$input_cp_password->value( $val );
$input_cp_password->counterMax(50);
$input_cp_password->arErr($message);

//Canada Post customerNumber
$val = isset($post_data['cp_customerNumber']) ? $post_data['cp_customerNumber'] : $list['cp_customerNumber'];
$input_cp_customerNumber = new inputField( "Canada Post Customer Number:", 'cp_customerNumber' );	
$input_cp_customerNumber->toolTip('Obtained from Canada Post');
$input_cp_customerNumber->value( $val );
$input_cp_customerNumber->counterMax(50);
$input_cp_customerNumber->arErr($message);

/** end Canada Post Settings **/

// set the header varables and create the header
$pageResources ="<link rel='stylesheet' type='text/css' href='styles.css' />
<script type=\"text/javascript\" src=\"".$_config['admin_url']."modules/shopping_cart/js/edit_shipping.js\"></script>
";//
$pageInit->createPageTop($pageResources);
?>
 <div class="page_container">
	<div id="h1"><h1>Shipping Options</h1></div>
    <div id="info_container">
		<?php
		// add subnav
		$pg = 'shipping';	include("includes/subnav.php");
		
		//---------------------------------------Error Banner----------------------------- 
		// create a banner if $message['banner'] exists
		createBanner($message);

		?>
		<form action="<?php echo htmlentities($_SERVER['PHP_SELF']); ?>" method="post" enctype="application/x-www-form-urlencoded" name="form_data" id="form_data" class="form">
		<?php if($includePricing) {	//tax and shipping only applied if pricing information included with cart?>
			<!-- general preferences -->
			<h2 id="shipping-toggle" class="tiptip toggle" title="General Shipping Options">General Shipping</h2>
			<div id="shipping-toggle-wrap">
			<?php
				$input_dimensionUnit->createInputField();
				$input_weight_type->createInputField();
				$input_free_shipping->createInputField();
			?>
				<div id='shipping'>
				<?php
					$input_free_ship->createInputField();
				?>
				<span title="You can only have a FREE UNTIL amount or a MINIMUM CHARGE amount." class='tipRight'>or </span>
				<?php
					$input_ship_per->createInputField();
					echo "<hr />";
					$input_flat_rate->createInputField();
					$input_flat_rate_days->createInputField();
					echo "<hr />";
					$input_min_ship->createInputField();	
					$input_max_ship->createInputField();
					$input_process_text->createInputField();
				?>
				</div>
				<h2 id="address-toggle" class="tiptip toggle" title="Ship From Address">Shipping Address</h2>
				<div id='address-toggle-wrap'>
				<?php
					$input_street_address->createInputField();
					$input_city->createInputField();
					$input_province->createInputField();
					$input_postal_code->createInputField();
					$input_country->createInputField();
				?>
				</div>
			</div><!-- end general preferences -->
			
			<!-- fedex options -->
			<h2 id="fedex-toggle" class="tiptip toggle" title="Fedex Shipping Options">Fedex Options</h2>
			<div id="fedex-toggle-wrap">
				<?php
				$input_fedex_on->createInputField();
				?>
				<div id='fedex_prefs'>
					<fieldset><legend>Fedex Account</legend>
					<?php
						$input_fedex_key->createInputField();
						$input_fedex_password->createInputField();
						$input_fedex_meter->createInputField();
						echo "<fieldset id='fedex_accts'><legend> In most cases, some or all of the following account numbers are the same.</legend>";
						$input_fedex_shipaccount->createInputField();
						$input_fedex_billaccount->createInputField();
						$input_fedex_dutyaccount->createInputField();
						$input_fedex_freightaccount->createInputField();
						$input_fedex_trackaccount->createInputField();
						echo "</fieldset>";
					?>
					</fieldset>
				</div><!--end fedex_prefs-->
				<div class='clearFix' ></div>
			</div><!-- end fedex options -->
			<div style="clear:both;"></div>
			<!-- canada post options -->
			<h2 id="cp-toggle" class="tiptip toggle" title="Canada Post Shipping Options">Canada Post Options</h2>
			<div id="cp-toggle-wrap">
				<?php
				$input_canpost_on->createInputField();
				?>
				<div id='canpost_prefs'>
					<fieldset><legend>Canada Post Account</legend>
					<small><strong>This username and password are not the ones you use to log into your account. Get the proper username/password from your Canada Post account profile.</strong></small>
					<div class="clear"></div>
					<?php
						$input_cp_username->createInputField();
						$input_cp_password->createInputField();
						$input_cp_customerNumber->createInputField();
					?>
					</fieldset>
				</div><!--end canpost_prefs-->
				<div class='clearFix' ></div>
			</div><!-- end canada post options -->
			<?}?>

			<!-- page buttons -->
			<div class='clearFix' ></div>
			<input name="page_get_id_val" type="hidden" value="1"/>

			<input name="submit-post" type="hidden" value="submit" />
		</form>	
		<a id="submit-btn" class="blue button" href="#">Save</a>

		<a class="grey button" id="cancel-btn" href="<?php echo $parent_page; ?>">Cancel</a>
		<div class='clearFix' ></div> 
		<!-- end page buttons -->

		<?php	// ----------------------------------- revisions -------------------------------------
		// add GET data to the return url
		$extraGET = array($page_get_id => $page_get_id_val);

		// build the Revision Area
		$Revisions->createRevisionsArea($page_get_id_val, array(),$extraGET);
		// end revisions
?>
	</div>
</div>
<?php 

include($_config['admin_includes'] . "footer.php"); ?>