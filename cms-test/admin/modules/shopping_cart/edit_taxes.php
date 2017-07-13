<?php
$headerModule = 'shopping_cart';

include('../../includes/headerClass.php');

// must have admin privileges to access this page
if ($_SESSION['user']['admin'] != 'yes')
{
	header( "Location: " . $_config['admin_url'] . "modules/{$headerModule}/index.php" );
	exit;
}

$pageInit = new headerClass(array(), $headerModule);

// access user data from headerClass
global $curUser;

// set the header varables and create the header
$pageResources ="<link rel='stylesheet' type='text/css' href='styles.css' /><script type=\"text/javascript\" src=\"".$_config['admin_url']."modules/shopping_cart/js/edit_taxes.js\"></script>";

$pageInit->createPageTop($pageResources);
$table = "ecom_taxes";
$parent_page		= 'index.php';				// the page that calls this one: used for delete and cancel form buttons, sets where to go afterwards

$hasCart = $_config['shopping_cart']['preferences']['hasCart']==1 ? true : false;
$includePricing = $_config['shopping_cart']['preferences']['includePricing']==1 ? true : false;

$provinces = array('','AB','BC','MB','NB','NL','NT','NS','NU','ON','PE','QC','SK','YT','AL','AK','AZ','AR','CA','CO','CT','DE','FL','GA','HI','ID','IL','IN','IA','KS','KY','LA','ME','MD','MA','MI','MN','MS','MO','MT','NE','NV','NH','NJ','NM','NY','NC','ND','OH','OK','OR','PA','RI','SC','SD','TN','TX','UT','VT','VA','WA','WV','WI','WY');
?>

<div class="page_container">
	<div id="h1"><h1>Taxes</h1></div>
	<div id="info_container">
<?php 
// add subnav
$pg = 'taxes';	include("includes/subnav.php");

#==================
# process post
#==================
if(isset($_POST['submit-tax'])){
	
	// validate:
	$errorMsgType = 'errorMsg';
	$errorType = 'error';
	$errorHeading = 'Could Not Save';
	$errorMessage = 'all fields must be complete, not saved';


//validation keys
	$required = array('tax','tax_name');

	$multi_input_index = array();

	$int_inputs = array();

	$decimal_inputs = array();

	$binary_inputs = array();


	$testData = $_POST;
	unset($testData['submit-tax']);
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
	$check = logged_query_assoc_array("SELECT `id` FROM {$table} WHERE `tax_name` = :tax_name",null,0,array(
		":tax_name" => trim($_POST['tax_name'])
	));
	if(isset($check[0]['id'])) {
		$errorMsgType = 'errorMsg';
		$errorType = 'error';
		$errorHeading = 'Tax Exists';
		$errorMessage = 'Delete existing tax and re-enter the new value.';
		$message['inline']['tax_name'] = array('type' => 'errorMsg','msg' => 'Tax Location already exists. Delete it and re-enter the new value.');
	}
	
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
			$post_data['submit-tax']
		);
		$result = logged_array_insert($table, $post_data);
		$saveError = (bool) $result['error'];
		
		// banners: if there was an error, overwrite the previously set success message
		if ($saveError)	$message['banner'] = array ('heading' => 'Error Saving Tax', 'message' => mysql_error(), 'type' => 'error' );
	}
}

// create a banner if $message['banner'] exists
$message = isset($message) ? $message : array();
createBanner($message); 
$taxes = logged_query_assoc_array("SELECT * from {$table} WHERE `id`>0 ORDER BY `tax_name` asc",null,0,array());
		?>
		<table id='tblCouponPage' border="0" cellspacing="0" cellpadding="0">
			<tr>
				<th style="width:30%;text-align:left;">Purchase Location</th>
				<th style="width:30%;">Tax Rate %</th>
				<th style='text-align:center;'>Operation</th>
			</tr>
			<?php
			if(!is_array($taxes) || !count($taxes))
			{
				echo "<tr><td colspan='4' style='text-align:center'>No taxes are active at this time.</td></tr>";
			}
			else 
			{
				foreach ($taxes as $tax){
				?>            
				<tr>
					<td style='text-align:left'><?php echo $tax['tax_name']; ?></td>
					<td style='text-align:center'><?php echo number_format($tax['tax'], 2, '.', ','); ?>%</td>
					<td style='text-align:center;'>
						<span class="op_group">				  
						<a  href="#" class="deleteTax" rel="<?php echo $tax['id']; ?>">
							<img class='tipTop' title='Permanently remove this Tax?' src="../../images/delete.png" alt="Delete"></a>
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
	// Tax Options
	if ($hasCart) : 
		if($includePricing) {	//taxes only applied if pricing information included with cart
			?>			<!-- tax area -->
			<h2 class="tiptip toggle" id="tax-toggle" ;" title="Set your taxes by purchase location.">Sales Taxes</h2><br />
			<div id="tax-toggle-wrap" style="width:85%;">
				<div class="input_wrap very_small">
				<label for="tax_name" class="tipRight" title="Purchaser Location to be taxed">Purchase Location: Province/State</label>
				<div class="input_inner"><div class="message_wrap"><span id="err_tax"></span></div>
				<select name="tax_name" style="width:25%;">
			<?php
					foreach($provinces as $p) {
						echo "<option value='$p'>$p</option>";
					}
			?>
				</select>
				</div>
				</div>
			<?php
					// Tax Amount
					$val = '';
					$input_discount = new inputField( "Tax Amount: <span class='symbol'>%</span>", 'tax' );	
					$input_discount->toolTip('Set the tax amount (ex: tax is 12.2%, enter 12.2)');
					$input_discount->value( $val );
					$input_discount->counterMax(5);
					$input_discount->size('verysmall');
					$input_discount->arErr($message);
					$input_discount->createInputField();
					
			?>
			</div><!-- end tax area -->
		
	<?	}?>
	<?php endif; ?>
			<!-- page buttons -->
			<div class='clearFix' ></div>

			<input name="submit-tax" type="hidden" value="submit" />
		</form>	
		<a id="submit-btn" class="blue button" href="#">Save</a>
		<a class="grey button" id="cancel-btn" href="<?php echo $parent_page; ?>">Cancel</a>
		<div class='clearFix' ></div> 
		<!-- end page buttons -->
		<hr />
	</div>
</div><!-- end page_container-->
<?php include($_config['admin_includes']."footer.php"); ?>