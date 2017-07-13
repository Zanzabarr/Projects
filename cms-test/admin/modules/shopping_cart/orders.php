<?php
$adminmodule = 'shopping_cart';

include('../../includes/headerClass.php');
$pageInit = new headerClass(array(), 'shopping_cart');

// this is not a core page and must be enabled in themes
// this page is enable by theme: hasCart
ecom_functions::redirect_invalid_shop_page('orders.php');

// start date
$input_start_date = new inputField('Start Date', 'start_date');
$input_start_date->size('verysmall');
$input_start_date->value(isset($list['start_date']) ? $list['start_date'] : '');
$input_start_date->readonly('true');


// end date
$input_end_date = new inputField('End Date', 'end_date');
$input_end_date->size('verysmall');
$input_end_date->value(isset($list['end_date']) ? $list['end_date'] : '');
$input_end_date->readonly('true');

// head/header/sidebar
$pageResources ="
<link rel='stylesheet' type='text/css' href='styles.css' />
<link rel='stylesheet' href='{$_config['site_path']}js/fancybox/jquery.fancybox-1.3.4.css' type='text/css' media='screen' />
<script type=\"text/javascript\" src=\"".$_config['admin_url']."modules/events/js/jquery.ui.datepicker.js\"></script>
<script type='text/javascript' src='{$_config['admin_url']}modules/shopping_cart/js/orders.js'></script>
<script type='text/javascript' src='{$_config['site_path']}js/fancybox/jquery.fancybox-1.3.4.pack.js'></script>
";
$pageInit->createPageTop($pageResources);
?>

<div class="page_container">
	<div id="h1"><h1>Products</h1></div>
	<div id="info_container">
<?php 
// add subnav
$pg = 'orders';	include("includes/subnav.php");

// create a banner if $message['banner'] exists
$message = isset($message) ? $message : array();
createBanner($message);
$filters = array('all','confirmed','shipped','not confirmed','not shipped','archives');
	if(isset($_POST['end_date']) && isset($_POST['start_date'])) {
		$orders = logged_query_assoc_array("SELECT * FROM ecom_orders WHERE timestamp BETWEEN '{$_POST['start_date']}' AND '{$_POST['end_date']}' ORDER BY timestamp DESC",null,0,array());
		$filter = 'all';
	}
	else if(isset($_POST['filter'])) {
		$filter = $_POST['filter'];
		switch($filter) {
			case 'confirmed':
				$orders = logged_query_assoc_array("SELECT * from `ecom_orders` WHERE `id`>0 AND `confirm`=1 ORDER BY `id` desc",null,0,array());
				break;
			case 'not confirmed':
				$orders = logged_query_assoc_array("SELECT * from `ecom_orders` WHERE `id`>0 AND `confirm`=0 ORDER BY `id` desc",null,0,array());
				break;
			case 'shipped':
				$orders = logged_query_assoc_array("SELECT * from `ecom_orders` WHERE `id`>0 AND `shipped`=1 ORDER BY `id` desc",null,0,array());
				break;
			case 'not shipped':
				$orders = logged_query_assoc_array("SELECT * from `ecom_orders` WHERE `id`>0 AND `shipped`=0 ORDER BY `id` desc",null,0,array());
				break;
			case 'archives':
				$orders = logged_query_assoc_array("SELECT * from `ecom_orders` WHERE `id`<0 ORDER BY `id` asc",null,0,array());
				break;
			default:
				$orders = logged_query_assoc_array("SELECT * from `ecom_orders` WHERE `id`>0 ORDER BY `id` desc",null,0,array());
				break;
		}
	}
	else {
		$filter = 'all';
		$orders = logged_query_assoc_array("SELECT * from `ecom_orders` WHERE `id`>0 ORDER BY `id` desc",null,0,array());
	}
		?>
        <table id='tblOrderPage' border="0" cellspacing="0" cellpadding="0">
			<tr class="nobg"><td colspan=4><form action='<?php echo $_SERVER['PHP_SELF']; ?>' method='POST'>
				<label class="filterLabel">FILTER ORDERS:</label>
				<div class="filterContainer">
			<?php
			foreach($filters as $f) {
				$filtered = $filter==$f ? " filtered" : "";
				echo "<input type='submit' name='filter' value='$f' class='filter{$filtered}' />";
			}
			?>
				</div><!-- end .filterContainer -->
			</form></td></tr>
			<tr class="nobg"><td colspan=4><form action='<?php echo $_SERVER['PHP_SELF']; ?>' method='POST'>
				<label class="datesearchLabel">SEARCH BY DATE:</label>
					<?php
					// active search
					if(isset($_POST['start_date'])) {
						echo "<div class='datesearch'>{$_POST['start_date']} through {$_POST['end_date']}</div><div class='datesearch'><input style='margin-top:5px;' type='submit' value='Clear' /></div>";
					}
					/**/
					// no active search
					else {
						echo "<div class='datesearch'><label for='start_date'>Start Date</label><input type='text' id='start_date' name='start_date' />";
							//$input_start_date->createInputField();
						echo "</div><div class='datesearch'><label for='end_date'>End Date</label><input type='text' id='end_date' name='end_date' />";
							//$input_end_date->createInputField();
						echo "</div><input style='margin-top:5px;' class='right' type='submit' value='Search' />";
					}
					/**/
					?>
			</form></td></tr>
            <tr>
                <th width="50">Order Number</th>
                <th width="180">Time</th>
                <th width="75">Total</th>
                <th style='text-align:center;'>Operation</th>
            </tr>
		<?php
			if(!is_array($orders) || !count($orders))
			{
				echo "<tr><td colspan='4' style='text-align:center'>No orders have been placed.</td></tr>";
			}
			else 
			{
				foreach ($orders as $key => $value){
				?>            
				<tr>
					<td style='text-align:center'><?php echo $value['id']; ?></td>
					<td style='text-align:center'><?php echo $value['timestamp']; ?></td>
					<td style='text-align:center'>$ <?php echo $value['total']; ?></td>
					<td style='text-align:center;'>
						<span class="op_group">
						<a class='orderlink tipTop' title="View Order <?php echo $value['id']; ?> Details" href="<?php echo $_config['admin_url'];?>modules/shopping_cart/ajax/ajax-orders.php?id=<?php echo $value['id']; ?>"><img src="img/view_icon.png" alt='View' /></a>
				   <?php
					if($value['confirm'] == 1){ 
					?>
						<a class="changeConfirm" rel='<?php echo $value['id']; ?>' href="#" >
							<img src="img/icon-star.png" class='tipTop' rel='0' title='Unconfirm Order / Not Paid' alt='Confirmed' /></a>
					<?php	
					} else {
					?>
						<a class="changeConfirm" rel='<?php echo $value['id']; ?>' href="#" >
							<img src="img/icon-darkstar.png" class='tipTop' rel='1' title='Confirm Order / Paid' alt='Not Confirmed' /></a>
					<?php		
					}
					if($value['shipped'] == 1){ 
					?>
						<a class="changeShipped" rel='<?php echo $value['id']; ?>' href="#" >
							<img src="img/shipped.png" class='tipTop' rel="0" title='Mark Order Not Shipped' alt="Shipped" /></a>
					<?php	
					} else {
					?>
						<a class="changeShipped" rel='<?php echo $value['id']; ?>' href="#" >
							<img src="img/unshipped.png" class='tipTop' rel="1" title='Mark Order Shipped' alt="Not Shipped" /></a>
					<?php
					}
						if($value['id']>0) {
					?>
						<a  href="#" class="archiveOrder" rel="<?php echo $value['id']; ?>">
							<img class='tipTop' title='Archive this Order' src="img/archive.png" alt="Archive"></a>
					<?php
						}
					?>
						<a  href="#" class="deleteOrder" rel="<?php echo $value['id']; ?>">
							<img class='tipTop' title='Permanently remove this Order' src="../../images/delete.png" alt="Delete"></a>
						</span>
					</td>
				</tr>
				<?php 
				} 
			}
		?>
		</table>
	</div>
</div>
<?php include($_config['admin_includes']."footer.php"); ?>