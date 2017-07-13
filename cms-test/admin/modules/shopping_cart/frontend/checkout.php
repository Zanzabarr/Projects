<h1><?php echo $scData['title']; ?></h1>
<?php
echo htmlspecialchars_decode($scData['content']);
	//get shipping preferences
	$ship_prefs = logged_query_assoc_array("SELECT * FROM `ecom_ship_prefs` WHERE `id` = '1'",null,0,array());
	$ship_prefs = $ship_prefs[0];

if(isset($_SESSION['cart']) && !empty($_SESSION['cart'])) {
	
?>
<form action="<?php echo $_SERVER['REQUEST_URI']; ?>" method="post" enctype="application/x-www-form-urlencoded" name="cartupdate" class="shopform">
<fieldset><legend>Your Cart</legend>
<table class="carttable" width="97%">

<tr>
	<th width="80" style="text-align:center;">Qty</th>
	<th>Product</th>
	<th width="100" style="text-align:right; padding-right:6px;">Price</th>
</tr>

<?php
$tr = 0;
	//iterate through the cart, the $product_id is the key and $quantity is the value
	$total = 0;
	$total_weight = 0;
	$total_qty = 0;
	//create array for itemized list
	$itemized = array();
	$inum = 0;
    foreach($_SESSION['cart'] as $product_id => $product_info) {
		/*$product_info = ( [option] => none [name] => black-bullet-proof-backpack-insert [count] => 3 )*/
		$quantity = $product_info['count'];
		$result = ecom_functions::getProductByUrl($product_info['name']);
		
		$price = $_config['shopping_cart']['preferences']['includePricing']==1 ? ecom_functions::getpricefromQ($product_info['name'], $quantity) : 0;
		$weight = number_format($result['weight_kg'], 2, '.', ',');
		
		$optionprice = 0;
		$optionweight = 0;
		$optionName = array();
		
		foreach($product_info as $k=>$v){
			if($k == 'option' && is_array($v)){
				foreach($v as $a=>$b){
					$a = addslashes($a);
					$b = addslashes($b);
					//--- options
					$optioninfo = logged_query_assoc_array("SELECT * FROM `ecom_product_options` WHERE `opt_name` = '$a' AND `option` = '$b' AND `prod_id` = '{$result['id']}'",null,0,array());
					$optioninfo = $optioninfo[0];
					
					if($_config['shopping_cart']['preferences']['includePricing']==1) {
						$optionprice += $optioninfo['price'];
					} else {
						$optionprice += 0;
					}
					$optionweight += $optioninfo['weight'];
					$optionName[$optioninfo['opt_name']] = $optioninfo['option'];
				}
			}
		}
		$line_cost = ($price + $optionprice) * $quantity; //work out the line cost
		
		$inum++;
		$itemized[$inum] = array(
			'id' => $result['id'],
			'name' => $result['title'],
			'price' => $price + $optionprice,
			'count' => $product_info['count'],
			'options' => $optionName
		);
		
		$li = 0;
		$itemlist = "";
		// loop thru the items and create the cart upload
		foreach($itemized as $item) {
			$li++;
			$itemlist.= "<input type='hidden' name='item_number_$li' value='{$item['id']}'>";
			$itemlist.= "<input type='hidden' name='item_name_$li' value='{$item['name']}'>";
			$itemlist.= "<input type='hidden' name='amount_$li' value='{$item['price']}'>";
			$itemlist.= "<input type='hidden' name='quantity_$li' value='{$item['count']}'>";
			$osnum = 0;
			if(!empty($item['options'])) {
				foreach($item['options'] as $k => $v) {
					$k = urlencode($k);
					$v = urlencode($v);
					$itemlist.= "<input type='hidden' name='on{$osnum}_{$li}' value='".$k."' />";
					$itemlist.= "<input type='hidden' name='os{$osnum}_{$li}' value='".$v."' />";
					$osnum++;
				}
			}
		}
		
		$total = $total + $line_cost; //add to the total cost
		$line_weight = ($weight + $optionweight) * $quantity; //work out the line cost
		$total_weight = $total_weight + $line_weight;
		$total_qty += $quantity;

		if($tr % 2 == 0){
			echo "<tr class=\"tr1\">";
		}else{
			echo "<tr class=\"tr2\">";
		}
		$tr++;
		//show this information in table cells

		echo "<td style=\"text-align:center;\">$quantity</td>";
		echo "<td><span style='font-weight:bold'>".$result['title'] . "</span>" ;
		foreach ($optionName as $nm => $vl)
		{
			echo "<br><span style='padding-left:1em'>{$nm}: {$vl}</span>";
		}
		echo "</td>";
		echo "<td align=\"right\" style=\"text-align:right; padding-right:6px;\">$ ".number_format($line_cost, 2, '.', ',')."</td>";
		echo "</tr>";
    }
	
	//Cart buttons
	echo "<tr><td colspan=3 style='height:1em;' /></tr><tr><td colspan=3 style='text-align:center;'><em>{$ship_prefs['process_text']}</em></td></tr>";
	echo "<tr><td colspan=3><hr style='width:90%;float:right;' /></td></tr><tr><td colspan=3 style='padding-bottom:1em;'>";
	echo "<a href='{$_config['path']['shopping_cart']}cart/' class='cartbutton'>Edit Cart</a>";
	echo "<a href='{$_config['path']['shopping_cart']}products' class='cartbutton'>Continue Shopping</a>";
	echo "</td></tr>"; //close it up
	
	//check for existing saved cart
	if(!isset($_SESSION['md5id'])){
		$_SESSION['md5id'] = md5(microtime());
	}
	if (isset($_SERVER['HTTP_X_FORWARD_FOR'])) {
		$ip = $_SERVER['HTTP_X_FORWARD_FOR'];
	} else {
		$ip = $_SERVER['REMOTE_ADDR'];
	}
	$sterl = serialize($_SESSION['cart']);
	//understanding order info
	/*echo $sterl."<div style='clear:both;height:1em;'></div>";
	print_r(unserialize($sterl));*/
	$check = logged_query_assoc_array("SELECT * FROM `ecom_orders` WHERE `md5` = :md5 AND `ip` = :ip AND confirm = 0",null,0,array(
		':md5' => $_SESSION['md5id'],
		':ip' => $ip
	));
	$check = isset($check[0]) ? $check[0] : array();
	/**/
	
	//do coupons
	//print_r($_SESSION['coupon']);
	if(isset($_SESSION['coupon'])){
		if($_SESSION['coupon']['discount'] >= 100){echo 'ERROR';}
		if($_SESSION['coupon']['discount'] < 10){$x = '.'.'0'.$_SESSION['coupon']['discount'];}
		else{$x = '.'.$_SESSION['coupon']['discount'];}
		$y = $total * $x;
		$total = $total - $y;
		?>
		<tr height="10px"><td></td></tr>
		<tr>
		<td colspan="2" align="right">
		Coupon Code (<strong><?php echo strtoupper($_SESSION['coupon']['code']); ?></strong>)
		</td>
		<td style="text-align:right;padding-right:6px;">- $<?php echo number_format($y, 2, '.', ','); ?></td>
		</tr><?php
	}
	
	$subtotal = number_format($total, 2, '.', ',');	//set initial cart subtotal
	$ordererrors = false;
	/* process $_POST */
	if(isset($_POST['orderform'])){
		/* ADDRESS FORM HAS BEEN POSTED - CHECK AND SAVE IF VALID */
		
		// all required fields
		$required = array('first_name', 'last_name', 'address1', 'city', 'province', 'postal_code', 'country', 'email', 'phone');

		//get ready for data save
		$saved = false;
		$fields = "";	//string of fields for db
		$shipfields = ""; // string of fields for shipping table
		$bindvars = "";	//string to build insert query
		$shipbindvars = ""; //string to build shipping insert
		$update = "";	//string for update query
		$shipupdate = ""; // string for shipping update
		$bind = array();	//array for pdo bindings
		$shipbind = array(); //array for shipping pdo bindings

		$_SESSION['province'] = $_POST['province'];
		
		// gather save and validation data
		$autofill = "<input type='hidden' name='address_override' value='1' />";
		foreach($_POST as $k => $v){
			if($k != "orderform" && $k != 'order_submit'){
				//load shipping table vars
				if($k != "email" && $k != "phone") {
					if($shipfields=="") $shipfields = "`$k`";
					else $shipfields .= ", `$k`";
					
					if($shipupdate=="") $shipupdate = "`$k` = :$k";
					else $shipupdate .= ", `$k` = :$k";
					
					if($shipbindvars=="") $shipbindvars = ":$k";
					else $shipbindvars .= ", :$k";
					
					$shipbind[":{$k}"] = encrypt($v);
				}
				//all fields required except address2 and notes
				if (trim($v) == "" && $k != "address2" && $k != "notes" && $k != "email") $ordererrors[$k] = 'Required Field';
				elseif ($k == "email" && ! check_email_address(trim($v))) $ordererrors[$k] = 'Please Supply A Valid Email Address';
				if($k != "notes") {
					//load fields list
					if($fields == "") $fields = "`$k`";
					else $fields .= ",`$k`";
					//load bindvars list
					if($bindvars == "") $bindvars = ":$k";
					else $bindvars .= ", :$k";
					//load update list
					if($update == "") $update = "`$k` = :$k";
					else $update .= ", `$k` = :$k";
					//load variables
					$$k = $v;
					//load bindings
					$bind[":{$k}"] = encrypt($v);
				}
				// create paypal auto-fill
				if($k == "province") {
					$autofill .= "<input type='hidden' name='state' value='{$v}' />";
				}
				else if($k == "postal_code") {
					$autofill .= "<input type='hidden' name='zip' value='{$v}' />";
				}
				else if($k == "phone") {
					$autofill .= "<input type='hidden' name='night_phone_b' value='{$v}' />";
				}
				else {
					$autofill .= "<input type='hidden' name='{$k}' value='{$v}' />";
				}
			}
		}
		if (!$ordererrors)	//rather than an empty array, $ordererrors starts out false
		{
			// taxes
			if($_config['shopping_cart']['preferences']['purchasing_on']==1) {
				$total_tax = 0;
				$tax_chk = logged_query_assoc_array("SELECT * FROM `ecom_taxes` WHERE `tax_name` = '{$province}'",null,0,array());
				if(!empty($tax_chk)) {
					$tax_chk = $tax_chk[0];
				} else {
					$tax_chk = array('tax'=>0,'tax_name'=>'Sales Tax');
				}
				if ($tax_chk['tax'] >= 0)
				{
					$tax = number_format($total * $tax_chk['tax']/100, 2, '.', ',');
					$total_tax = $tax;
					$tax_name = $tax_chk['tax_name'] ? $tax_chk['tax_name'] : 'Tax';
					
					if($tax > 0) {
						echo "<tr>";
							echo "<td align=\"right\" colspan=\"2\">{$tax_name} Sales Tax : </td>";
							echo "<td style=\"text-align:right; padding-right:6px;\">$ ".$tax."</td>";
						echo "</tr>";
					}

				}
			} else {
				$total_tax = 0;
			}
			//do totals
			$subtotal = number_format($total + $total_tax, 2, '.', ',');
			/* save form details to order */
			if(empty($check) || !isset($check['id'])){
				$vals = array(
					':info' => $sterl,
					':ip' => $ip,
					':md5' => $_SESSION['md5id'],
					':subtotal' => $subtotal,
					':total' => $total,
					':tax_name' => $tax_name,
					':tax' => $tax
				);
				$bindings = array_merge($vals,$bind);
				$savedata = logged_query("INSERT INTO `ecom_orders` (`info` ,`ip`,`md5`,`total`,`subtotal`,`tax_name`,`tax`, $fields) VALUES (:info, :ip, :md5, :subtotal, :total, :tax_name, :tax, $bindvars)",0,$bindings);
				$check = array("id" => $_config['db']->getLastInsertId());
			} else {
				$vals = array(
					':info' => $sterl,
					':total' => $total,
					':tax_name' => $tax_name,
					':tax' => $tax,
					':id' => $check['id']
				);
				$bindings = array_merge($vals,$bind);
				$savedata = logged_query("UPDATE `ecom_orders` SET `info` = :info, `total` = :total, `subtotal` = :total, `tax_name` = :tax_name, `tax` = :tax, $update WHERE `id` = :id",0,$bindings);
			}
			$check['id'] = isset($check['id']) ? $check['id'] : $_config['db']->getLastInsertId();
			
			//look for $check['id'] in ecom_orders_shipping.order_id
			$check_ship_info = logged_query("SELECT id FROM ecom_orders_shipping WHERE order_id = '{$check['id']}'",0,array());
			$check_ship_info = isset($check_ship_info[0]) ? $check_ship_info[0] : array();
			
			if(empty($check_ship_info) || !isset($check_ship_info['id'])) {
				$shipfields .= ", `order_id`";
				$shipbindvars .= ", :order_id";
				$shipbind[":order_id"] = $check['id'];
				$shipdata = logged_query("INSERT INTO `ecom_orders_shipping` ($shipfields) VALUES ($shipbindvars)",0,$shipbind);
			}

			// do the shipping function
			$ship_prefs = logged_query_assoc_array("SELECT * FROM `ecom_ship_prefs` WHERE `id` = '1'",null,0,array());
			$ship_prefs = $ship_prefs[0];
			//print_r($ship_prefs);
			$discountText = (isset($ship_prefs['discount_min']) && $ship_prefs['discount_min'] > 0 && $ship_prefs['discount_min'] < $total) ? "A discount of up to \${$ship_prefs['discount']} will be applied to shipping charges." : "";
			
			if($_config['shopping_cart']['preferences']['shipping_on']==1 && $total_weight>0) {
				//deal with Fedex
				if($ship_prefs['fedex_on']==1) {
					//complete cart table
					$costhtml = $_config['shopping_cart']['preferences']['includePricing']==1 ? "<strong>$ {$subtotal}</strong>" : "<strong>".$_config['shopping_cart']['preferences']['altPriceText']."</strong>";
					echo "<tr><td align=\"right\" colspan=\"2\">Subtotal : </td><td style='text-align:right; padding-right:6px;'>$costhtml</td></tr></table></fieldset></form>";
					/* 	END CART CONTENTS */
					
					//do fedex functionality
					include($_config['admin_path'] . 'modules/shopping_cart/frontend/includes/fedex_available.php');
					//set $shipping and method variables until set by system
					$ship_method = "";
					$shipping = 0.00;
				}
				//deal with Canada Post
				elseif($ship_prefs['canpost_on']==1 && $ship_prefs['fedex_on']==0) {
					//complete cart table
					$costhtml = $_config['shopping_cart']['preferences']['includePricing']==1 ? "<strong>$ {$subtotal}</strong>" : "<strong>".$_config['shopping_cart']['preferences']['altPriceText']."</strong>";
					echo "<tr><td align=\"right\" colspan=\"2\">Subtotal : </td><td style='text-align:right; padding-right:6px;'>$costhtml</td></tr></table></fieldset></form>";
					/* 	END CART CONTENTS */
					
					echo '<h2>Shipping Method:</h2>';
					echo "<strong>{$discountText}</strong>";
					echo "<form method='POST' id='frmFedex' name='frmFedex' action='{$_SERVER['REQUEST_URI']}' enctype='application/x-www-form-urlencoded'><input type='hidden' name='province' value='{$_SESSION['province']}' />";
					echo '<table border="1" width="100%">';
					echo '<tr><th style="padding-left:5px;">Service</th><th>Amount</th><th>Delivery Estimate</th></tr>';
					
					//offer local/in-house delivery
					$inhouseshipping = number_format(ecom_functions::calculate_shipping($total_weight, $ship_prefs),2,".",",");
					
					//if($inhouseshipping > 0) {
						echo "<tr><td><input type='radio' class='ship_radio' name='ship_method' data-price='". $inhouseshipping ."' value='{$ship_prefs['inhousemethod']}' />{$ship_prefs['inhousemethod']}</td><td>".$inhouseshipping."</td><td>{$ship_prefs['inhousemethod']}</td></tr>";
					//}
					//do Canada Post functionality
					include($_config['admin_path'] . 'modules/shopping_cart/frontend/includes/cp_getrates.php');
					
					echo "<tr><td colspan=3 style='text-align:right;'><input type='submit' class='cartbutton' value='Add Shipping' /></td></tr>";
					echo '</table></form><br />';
					//set $shipping and method variables
					$ship_method="";
					$shipping = 0.00;
				}
				elseif($ship_prefs['fedex_on']==0 && $ship_prefs['canpost_on']==0) {
					$ship_method = "{$ship_prefs['inhousemethod']}";	//must be able to set a default shipping method in the system
					$shipping = ecom_functions::calculate_shipping($total_weight, $ship_prefs);
					$shipping = number_format(($shipping), 2, '.', ',');
					$shippingFree = $shipping == 0.00 ? true : false;

					//shipping
					echo "<tr>";
						echo "<td align=\"right\" colspan=\"2\">Ship via $ship_method : </td>";
						if($shippingFree)
						{
							echo "<td align=\"center\" style=\"text-align:right;\">Free&nbsp;</td>";
						} else {
							echo "<td style=\"text-align:right; padding-right:6px;\">$ ".$shipping."</td>";
						}
					echo "</tr><tr height=\"10px\"><td></td></tr><tr style=\"background-color:#efefef\">";
					
					//do totals
					$subtotal = number_format($total + $total_tax + $shipping, 2, '.', ',');
					$updateshipping = logged_query("UPDATE `ecom_orders` SET `ship_method` = :ship_method, `ship_price` = :shipping, `total` = :subtotal WHERE `id` = :id",0,array(
						':ship_method' => $ship_method,
						':shipping' => $shipping,
						':subtotal' => $subtotal,
						':id' => $check['id']
					));
					
					//complete cart table
					$costhtml = $_config['shopping_cart']['preferences']['includePricing']==1 ? "<strong>$ {$subtotal}</strong>" : "<strong>".$_config['shopping_cart']['preferences']['altPriceText']."</strong>";
					echo "<tr><td align=\"right\" colspan=\"2\">Subtotal : </td><td style='text-align:right; padding-right:6px;'>$costhtml</td></tr></table></fieldset></form>";
					/* 	END CART CONTENTS */
					if($_config['gateway']=="paypal") {
						if($ship_prefs['fedex_on']==0 || $total_weight<=0) {
							include_once($_config['admin_path'] . 'modules/shopping_cart/frontend/inc-paypal.php');
						}
					}
					else if($_config['gateway']=="moneris") {
						if($ship_prefs['fedex_on']==0 || $total_weight<=0) {
							include($_config['admin_path'] . 'modules/shopping_cart/frontend/inc-moneris.php');
						}
					}
				}
			} else {
				$ship_method = "No Shipping";
				//do totals
				$shipping = 0.00;
				$subtotal = number_format($total + $total_tax + $shipping, 2, '.', ',');
				$updateshipping = logged_query("UPDATE `ecom_orders` SET `ship_method` = :ship_method, `ship_price` = :shipping, `total` = :subtotal WHERE `id` = :id",0,array(
						':ship_method' => $ship_method,
						':shipping' => $shipping,
						':subtotal' => $subtotal,
						':id' => $check['id']
					));
					
				//complete cart table
				$costhtml = $_config['shopping_cart']['preferences']['includePricing']==1 ? "<strong>$ {$subtotal}</strong>" : "<strong>".$_config['shopping_cart']['preferences']['altPriceText']."</strong>";
				echo "<tr><td align=\"right\" colspan=\"2\">Subtotal : </td><td style='text-align:right; padding-right:6px;'>$costhtml</td></tr></table></fieldset></form>";
				/* 	END CART CONTENTS */
				if($_config['shopping_cart']['preferences']['purchasing_on']==0) {
					$mail_to = $_config['orderemail']; // recipient of the submission
					$mail_title = $_config['company_name'] . ' Web Order';
					$mail_from = $mail_to;
					//build email
					$emailtxt = "<p style='font-weight:600;'>The following web order has been placed at {$_config['site_path']}.</p>";
					$emailtxt .= "<h3>Order : {$check['id']}</h3>";
					$emailtxt .= "<table border=1><tr style='background:#eee;'><th style='text-align:left;padding:0 3px;'>Product ID</th>
					<th style='text-align:left;padding:0 3px;'>Name</th>
					<th style='text-align:left;padding:0 3px;'>Options</th>
					<th style='text-align:left;padding:0 3px;'>Price</th>
					<th style='text-align:left;padding:0 3px;'>Number</th></tr>";
					foreach($itemized as $item) {
						$emailtxt .= "<tr><td style='padding:0 5px;'>{$item['id']}</td><td style='padding-left:0 5px;'>{$item['name']}</td><td style='padding:0 5px;'>";
						if(!empty($item['options'])) {
							$optcount = 0;
							$maxcount = count($item['options']);
							foreach($item['options'] as $k => $v) {
								$optcount++;
								$emailtxt .= "{$k}: {$v}";
								if($optcount < $maxcount) {
									$emailtxt .= ", ";
								}
							}
						}
						$emailtxt .= "</td><td style='padding:0 5px;'>$ {$item['price']}</td><td style='padding-left:5px;'>{$item['count']}</td></tr>";
					}
					$emailtxt .= "</table><div style='clear:both;height:1em;'></div><table border=1><tr style='background:#eee;'>
					<th style='min-width:20%;text-align:left;padding-left:3px;'>Sub-Total</th>
					<th style='min-width:20%;text-align:left;padding-left:3px;'>{$check['tax_name']}</th>
					<th style='min-width:20%;text-align:left;padding-left:3px;'>Shipping Charges</th>
					<th style='min-width:20%;text-align:left;padding-left:3px;'>Total Charges</th></tr>
					<tr><td style='padding-left:5px;'>$ {$subtotal}</td>
					<td style='padding-left:5px;'>$ {$check['tax']}</td>
					<td style='padding-left:5px;'>$ {$shipping}</td>
					<td style='padding-left:5px;'>$ {$check['total']}</td></tr></table>
					<div style='clear:both;height:1em;'></div>
					<table border=1><tr style='background:#eee;'>
					<th style='text-align:left;padding-left:3px;'>Shipping Method</th></tr>
					<tr><td style='padding:0 5px;'>{$ship_method}</td></tr></table>
					<div style='clear:both;'></div><h3>Customer Information</h3><table>";
					foreach($_POST as $k => $v){
						if($k != "orderform" && $k != 'order_submit'){
							//finish building email
							$heading = ucwords(str_replace('_', ' ', $k));
							$message[$k] = htmlspecialchars(trim($v));
							$emailtxt .= $v ? "<tr><td style='padding-right:5px;'>{$heading}:</td><td>{$v}</td></tr>" : '';
						}
					}
					$emailtxt .= "</table>";
					$headers  = $mail_from ? "From: {$mail_from}\n" : '';
					$headers .= 'MIME-Version: 1.0' . "\n";
					$headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
					mail($mail_to, $mail_title, $emailtxt, $headers);
					?>
					<script type="text/javascript">
					<!--
					window.location = "shopping/success"
					//-->
					</script>
					<?php
				} else {
					if($_config['gateway']=="paypal") {
						if($ship_prefs['fedex_on']==0 || $total_weight<=0) {
							include_once($_config['admin_path'] . 'modules/shopping_cart/frontend/inc-paypal.php');
						}
					}
					else if($_config['gateway']=="moneris") {
						if($ship_prefs['fedex_on']==0 || $total_weight<=0) {
							include($_config['admin_path'] . 'modules/shopping_cart/frontend/inc-moneris.php');
						}
					}
				}
			}
		} else {
		//complete cart table
			$costhtml = $_config['shopping_cart']['preferences']['includePricing']==1 ? "Subtotal : <strong>$ {$subtotal}</strong>" : "<strong>".$_config['shopping_cart']['preferences']['altPriceText']."</strong>";
			echo "<tr><td colspan='3' style='text-align:right; padding-right:6px;'>$costhtml</td></tr></table></fieldset></form>";
		/* 	END CART CONTENTS */
		//show address form for editing with $ordererrors
			include_once($_config['admin_modules']."shopping_cart/frontend/order.php");
		}
	} elseif(isset($_POST['ship_price'])) {
	/* $_POST comes from shipping method form */
		$ship_prefs = logged_query_assoc_array("SELECT * FROM `ecom_ship_prefs` WHERE `id` = '1'",null,0,array());
			$ship_prefs = $ship_prefs[0];
		$ship_method =$_POST['ship_method'];
		$shipping = $_POST['ship_price'];
		$shipdiscount = 0;
		if($ship_prefs['discount'] > 0 && $ship_prefs['discount_min'] > 0 && $total > $ship_prefs['discount_min']) {
			$shipdiscount = $ship_prefs['discount'] < $shipping ? $ship_prefs['discount'] : $shipping;
			$shipping = $shipping - $shipdiscount;
		}
		
		//do taxes again - must apply taxes to shipping charges
		$subsubtotal = $total + $shipping;
		$tax_chk = logged_query_assoc_array("SELECT * FROM `ecom_taxes` WHERE `tax_name` = '{$_POST['province']}'",null,0,array());
		if(!empty($tax_chk)) {
			$tax_chk = $tax_chk[0];
		} else {
			$tax_chk = array('tax'=>0,'tax_name'=>'Sales Tax');
		}
		if ($tax_chk['tax'] >= 0)
		{
			if(strstr($subsubtotal,',')) $subsubtotal = str_replace(',','',$subsubtotal);  
			$tax = $subsubtotal * $tax_chk['tax']/100;
			$total_tax = $tax;
			
		}
		$subtotal = $subsubtotal + $total_tax;
		$address_fields = array('first_name','last_name','address1','address2','city','province','postal_code','country','email','phone');
		
		//update
		$updateshipping = logged_query("UPDATE `ecom_orders` SET `tax` = :tax, `ship_method` = :ship_method, `ship_price` = :shipping, `total` = :subtotal, `shipdiscount` = :shipdiscount WHERE `id` = :id",0,array(
						':ship_method' => $ship_method,
						':shipping' => $shipping,
						':subtotal' => $subtotal,
						':tax' => $tax,
						':shipdiscount' => $shipdiscount,
						':id' => $check['id']
					));
		
		//reload $check
		$check = logged_query_assoc_array("SELECT * FROM `ecom_orders` WHERE `md5` = :md5 AND `ip` = :ip AND confirm = 0",null,0,array(
			':md5' => $_SESSION['md5id'],
			':ip' => $ip
		));
		$check = isset($check[0]) ? $check[0] : array();
		
		//create paypal autofill
		$autofill = "<input type='hidden' name='address_override' value='1' />";
		foreach($check as $k=>$v) {
			if(in_array($k,$address_fields)) {
				$val = decrypt($v);
				if($k == "province") {
					$autofill .= "<input type='hidden' name='state' value='{$val}' />";
				}
				else if($k == "postal_code") {
					$autofill .= "<input type='hidden' name='zip' value='{$val}' />";
				}
				else if($k == "phone") {
					$autofill .= "<input type='hidden' name='night_phone_b' value='{$val}' />";
				}
				else {
					$autofill .= "<input type='hidden' name='{$k}' value='{$val}' />";
				}
			}
		}
		//do totals
		$total_tax = $check['tax'];
		$subtotal = number_format($check['total'],2,'.',',');
		
		//tax lines
		if($check['tax']>0) {
			echo "<tr><td align=\"right\" colspan=\"2\">{$check['tax_name']} Sales Tax : </td><td style=\"text-align:right; padding-right:6px;\">$ {$check['tax']}</td></tr>";
		}
		
		//shipping line
		echo "<tr><td align=\"right\" colspan=\"2\">Ship via {$check['ship_method']} : </td><td style=\"text-align:right; padding-right:6px;\">$ {$check['ship_price']}</td></tr>";
		
		if($shipdiscount > 0) {
			echo "<tr><td align=\"right\" colspan=\"2\">Discount Applied : </td><td style=\"text-align:right; padding-right:6px;\">$ {$shipdiscount}</td></tr>";
		}
		
		//complete cart table
		$costhtml = $_config['shopping_cart']['preferences']['includePricing']==1 ? "<strong>$ {$subtotal}</strong>" : "<strong>".$_config['shopping_cart']['preferences']['altPriceText']."</strong>";
		echo "<tr><td align=\"right\" colspan=\"2\">Subtotal : </td><td style='text-align:right; padding-right:6px;'>$costhtml</td></tr></table></fieldset></form>";
		/* 	END CART CONTENTS */
		if($_config['gateway']=="paypal") {
			include_once($_config['admin_path'] . 'modules/shopping_cart/frontend/inc-paypal.php');
		} else if($_config['gateway']=="moneris") {
			include_once($_config['admin_path'] . 'modules/shopping_cart/frontend/inc-moneris.php');
		}
	} elseif(isset($_POST['paymentform'])) {
	/* $_POST IS FROM MONERIS PAYMENT FORM */
		$frmfields = $_POST;
		//print_r($frmfields);
	} else {
	/* NO $_POST = INITIAL CHECKOUT PAGE - SERVE ADDRESS FORM */
		 $costhtml = $_config['shopping_cart']['preferences']['includePricing']==1 ? "Subtotal : <strong>$ {$subtotal}</strong>" : "<strong>".$_config['shopping_cart']['preferences']['altPriceText']."</strong>";
		echo "<tr><td colspan='3' style='text-align:right; padding-right:6px;'>$costhtml</td></tr></table></fieldset></form>";
	/* 	END CART CONTENTS */
		//include address form
		include_once($_config['admin_modules']."shopping_cart/frontend/order.php");
	}
} else {
//user has no items in the cart
    echo "You have no items in your shopping cart.";
}
?><div style="clear:both;"></div>