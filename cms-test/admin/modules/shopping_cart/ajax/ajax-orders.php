<?php
/* if $ship_method is from Canada Post, it can be any of the following:
Domestic
DOM.RP 	Regular Parcel
DOM.EP 	Expedited Parcel
DOM.XP 	Xpresspost
DOM.PC 	Priority
USA
USA.PW.ENV 	Priority Worldwide envelope USA
USA.PW.PAK 	Priority Worldwide pak USA
USA.PW.PARCEL 	Priority Worldwide parcel USA
USA.XP 	Xpresspost USA
USA.EP 	Expedited Parcel USA
USA.SP.AIR 	Small Packet USA Air
USA.TP 	Tracked Packet – USA
*/

include_once('../../../includes/config.php');

$order = logged_query_assoc_array("SELECT * FROM ecom_orders WHERE id = :orderid",null,0,array(":orderid" => $_GET['id']));
$order = $order[0];

$enc_keys = array('first_name','last_name','address1','address2','city','province','postal_code','country','email','phone');
//these shipping methods are Canada Post and available to purchase shipping labels through system
$labels = array('Regular Parcel','Expedited Parcel','Xpresspost','Priority','Priority Worldwide envelope USA','Priority Worldwide pak USA','Priority Worldwide parcel USA','Xpresspost USA','Expedited Parcel USA','Small Packet USA Air','Tracked Packet – USA');

foreach($order as $k => $v) {
	if(in_array($k,$enc_keys) && $v!="") {
		$$k = decrypt($v);
	} else {
		$$k = $v;
	}
}
$confirm = $confirm==0 ? "No" : "Yes";
$shipped = $shipped==0 ? "No" : "Yes";
$info = unserialize($info);

$rate = logged_query("select tax from ecom_taxes where tax_name = '{$province}'",0,array());
if($rate !== false && !empty($rate)) {
	$rate = $rate[0]['tax'];
} else {
	$rate = 0;
}

if($shipped=="Yes") {
	echo "<h1 style='color:red;'>THIS ORDER HAS BEEN SHIPPED</h1>";
}

echo "<h2>Order Id: {$id}</h2>";

echo "<table class='ordertable' border=1><tr style='background:#eee;'>
<th>Product</th><th>Options</th><th>Count</th></tr>";
foreach($info as $item) {
	$item_id = logged_query("select id from ecom_product where url = '{$item['name']}' LIMIT 1",0, array());
	$item_id = $item_id[0]['id'];
	echo "<tr><td style='padding-left:5px;'>Item {$item_id}: {$item['name']}</td><td style='padding-left:5px;'>";
	if($item['option'] != "none") {
		$optionnum = 0;
		foreach($item['option'] as $k=>$v) {
			$optionnum++;
			echo "$k: $v";
			if($optionnum < count($item['option'])) {
				echo ", ";
			}
		}
	}
	echo "</td><td style='padding-left:5px;'>{$item['count']}</td></tr>";
}
echo "</table>";
echo "<div class='clear'></div>";
$taxes = $tax;
echo "<table class='ordertable' border=1><tr><th>SubTotal</th><th>Shipping</th><th>Taxes [{$rate}%]</th><th>Total</th></tr>
<tr><td style='text-align:center;'>\${$subtotal}</td><td style='text-align:center;'>\${$ship_price}</td><td style='text-align:center;'>\${$taxes}</td><td style='text-align:center;'>\${$total}</td></tr></table>";
echo "<div class='clear'></div>";
echo "<table class='ordertable' border=1><tr><th>Confirmed</th><th>Shipped</th><th>Shipping Method</th></tr>
<tr><td>$confirm</td><td>$shipped</td><td>";

if(in_array($ship_method, $labels) && $shipped == "No") {
	echo $ship_method." <span style='float:right;'><a class='orderlink' href='{$_config['admin_url']}modules/shopping_cart/ajax/shiporder.php?id={$id}'>Ship Order</a></span>";
} else {
	echo $ship_method;
}

echo "</td></tr></table>";
echo "<div class='clear'></div>";
echo "<table id='customertable' border=1>";
echo "<tr><th colspan=4>Customer Contact</th></tr>
<tr><th colspan=2 style='text-align:left;'>Email:</th><th colspan=2 style='text-align:left;'>Telephone:</th></tr>
<tr><td colspan=2 style='text-align:left;'><a href='mailto:$email' title='Requires Default Email Program'>$email</a></td>
<td colspan=2 style='text-align:left;'><a href='tel:$phone' title='Click to Call requires VOIP software'>$phone</a></td></tr>
<tr><th colspan=4>Customer Information</th></tr>
<tr><th style='text-align:left;'>Name:</th><td colspan=3 style='text-align:left;'>$first_name $last_name</td></tr>
<tr><th style='text-align:left;'>Address 1:</th><td colspan=3 style='text-align:left;'>$address1</td></tr>
<tr><th style='text-align:left;'>Address 2:</th><td colspan=3 style='text-align:left;'>$address2</td></tr>
<tr><th style='text-align:left;'>City:</th><th style='text-align:left;'>Province/State:</th>
<th style='text-align:left;'>Postal Code:</th><th style='text-align:left;'>Country:</th></tr>
<tr><td style='text-align:left;'>$city</td>
<td style='text-align:left;'>$province</td>
<td style='text-align:left;'>$postal_code</td>
<td style='text-align:left;'>$country</td></tr>";
echo "</table>";
?>
<script>
$(document).ready( function() {
	$(".orderlink").fancybox({
		'autoDimensions': false,
		'width': '70%',
		'height': '100%',
		'overlayColor': '#000',
		'overlayOpacity': 0.6,
		'transitionIn': 'elastic',
		'transitionOut': 'elastic',
		'centerOnScroll': true,
		'titlePosition': 'outside',
		'easingIn': 'easeOutBack',
		'easingOut': 'easeInBack'
	});
});
</script>