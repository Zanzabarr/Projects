<?php
/* THESE ARE CURRENTLY TURNED OFF BECAUSE THEY NEED TO BE FIXED - NOT PROCESSING AT PAYPAL CORRECTLY 
$adminmodule = 'shopping_cart';

include('../../includes/headerClass.php');
$pageInit = new headerClass(array(), 'shopping_cart');

// set the header varables and create the header
$pageResources ="<link rel='stylesheet' type='text/css' href='styles.css' /><script type=\"text/javascript\" src=\"".$_config['admin_url']."modules/shopping_cart/js/edit_coupons.js\"></script>";

$pageInit->createPageTop($pageResources);
$table = "ecom_coupons";
$hasCart = $_config['shopping_cart']['preferences']['hasCart']==1 ? true : false;
$includePricing = $_config['shopping_cart']['preferences']['includePricing']==1 ? true : false;
?>

<div class="page_container">
	<div id="h1"><h1>Coupons</h1></div>
	<div id="info_container">
<?php 
// add subnav
$pg = 'coupons';	include("includes/subnav.php");

#==================
# process post
#==================
if(isset($_POST['submit-coupon'])){
	
	// validate:
	$errorMsgType = 'errorMsg';
	$errorType = 'error';
	$errorHeading = 'Could Not Save';
	$errorMessage = 'all fields must be complete, not saved';


//validation keys
	$required = array('code','exp','discount');

	$multi_input_index = array();

	$int_inputs = array();

	$decimal_inputs = array();

	$binary_inputs = array();


	$testData = $_POST;
	unset($testData['submit-coupon']);
	$testform = new Form($testData);
	$testform->set_required_input($required);
	$testform->set_multi_check_input($multi_input_index);
	$testform->set_integer_input($int_inputs);
	$testform->set_decimal_input($decimal_inputs);
	$testform->set_binary_input($binary_inputs);
	$tmplist = $testform->validate();
	$errors = $testform->get_errors();
	// merge the form data with pre-existing data
	$list = $tmplist;
	
	//check for dupes
	$check = logged_query_assoc_array("SELECT `id` FROM `ecom_coupons` WHERE `code` = :code",null,0,array(
		":code" => trim($_POST['code'])
	));
	if(isset($check[0]['id'])) $message['inline']['code'] = array('type' => 'errorMsg','msg' => 'Coupon already exists.');
	
	// if an error was found, create the error banner
	$errorsExist = isset($message['inline']) ? count($message['inline']) : 0 ;
	if ($errorsExist )
	{
		$message['banner'] = array ('heading' => $errorHeading, 'message' => $errorMessage, 'type' => $errorType);
	}
	else // set the success message
	{
		$message['banner'] = array ('heading' => 'Successfully Saved', 'type' => 'success');

		// get data to be posted to the db, and remove unwanted inputs
		$post_data = $list;
		
		unset(
			$post_data['submit-coupon']
		);
		$post_data['exp'] = date('Y-m-d H:i:s',strtotime($post_data['exp']));
		$result = logged_array_insert($table, $post_data);
		$saveError = (bool) $result['error'];
		
		// banners: if there was an error, overwrite the previously set success message
		if ($saveError)	$message['banner'] = array ('heading' => 'Error Saving Preferences', 'message' => mysql_error(), 'type' => 'error' );
	}
}

// create a banner if $message['banner'] exists
$message = isset($message) ? $message : array();
createBanner($message); 
$coupons = logged_query_assoc_array("SELECT * from `ecom_coupons` WHERE `id`>0 AND `exp` >= CurDate() ORDER BY `exp` asc",null,0,array());
		?>
		<table id='tblCouponPage' border="0" cellspacing="0" cellpadding="0">
			<tr>
				<th width="100">Code</th>
				<th width="120">Expiration</th>
				<th width="100">Discount %</th>
				<th style='text-align:center;'>Operation</th>
			</tr>
			<?php
			if(!is_array($coupons) || !count($coupons))
			{
				echo "<tr><td colspan='4' style='text-align:center'>No coupons are active at this time.</td></tr>";
			}
			else 
			{
				foreach ($coupons as $key => $value){
				?>            
				<tr>
					<td style='text-align:center'><?php echo $value['code']; ?></td>
					<td style='text-align:center'><?php echo date('Y-m-d',strtotime($value['exp'])); ?></td>
					<td style='text-align:center'><?php echo number_format($value['discount'], 0, '.', ','); ?>%</td>
					<td style='text-align:center;'>
						<span class="op_group">				  
						<a  href="#" class="deleteCoupon" rel="<?php echo $value['id']; ?>">
							<img class='tipTop' title='Permanently remove this Coupon' src="../../images/delete.png" alt="Delete"></a>
						</span>
					</td>
				</tr>
				<?php 
				} 
			}
		?>
		</table>
		<hr />
		<form action="<?php echo htmlentities($_SERVER['PHP_SELF']); ?>?option=edit" method="post" enctype="application/x-www-form-urlencoded" name="form_data" id="form_data" class="form">
	<?php
	// Coupon Options
	if ($hasCart) : 
		if($includePricing) {	//coupons only applied if pricing information included with cart
			?>			<!-- coupon area -->
			<h2 class="tiptip toggle" id="tax-toggle" ;" title="Set your coupon options">Coupon Options</h2><br />
			<div id="tax-toggle-wrap">
			<?php
					// Coupon Code
					$val = '';
					$input_code = new inputField( "Coupon Code", 'code' );	
					$input_code->toolTip('Enter Coupon Code here.');
					$input_code->value( $val );
					$input_code->counterMax(30);
					$input_code->arErr($message);
					$input_code->createInputField();
					
					// Expiration
					$val = '';
					$input_exp = new inputField('Expires', 'exp');
					$input_exp->size('verysmall');
					$input_exp->value($val);
					$input_exp->readonly('true');
					$input_exp->arErr($message);
					$input_exp->createInputField();
					
					// Discount Amount
					$val = '';
					$input_discount = new inputField( "Discount Amount: <span class='symbol'>%</span>", 'discount' );	
					$input_discount->toolTip('Set the Discount amount - Whole numbers only.');
					$input_discount->value( $val );
					$input_discount->counterMax(4);
					$input_discount->size('verysmall');
					$input_discount->arErr($message);
					$input_discount->createInputField();
					
			?>
			</div><!-- end tax area -->
		
	<?	}?>
	<?php endif; ?>
			<!-- page buttons -->
			<div class='clearFix' ></div>

			<input name="submit-coupon" type="hidden" value="submit" />
		</form>	
		<a id="submit-btn" class="blue button" href="#">Save</a>
		<a class="grey button" id="cancel-btn" href="<?php echo $parent_page; ?>">Cancel</a>
		<div class='clearFix' ></div> 
		<!-- end page buttons -->
		<hr />
	</div>
</div><!-- end page_container-->
<?php include($_config['admin_includes']."footer.php"); ?>
*/
?>