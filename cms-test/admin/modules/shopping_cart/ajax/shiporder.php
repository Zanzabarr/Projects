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

if(isset($_GET['id'])) {
	include_once('../../../includes/config.php');

	//get shipping preferences
	$ship_prefs = logged_query_assoc_array("SELECT * FROM `ecom_ship_prefs` WHERE `id` = '1'",null,0,array());
	$ship_prefs = $ship_prefs[0];
	//get order details
	$order = logged_query_assoc_array("SELECT * FROM ecom_orders WHERE id = :orderid",null,0,array(":orderid" => $_GET['id']));
	$order = $order[0];
	//get order shipping details
	$oship = logged_query_assoc_array("SELECT * FROM ecom_orders_shipping WHERE order_id = :orderid",null,0,array(":orderid" => $_GET['id']));
	$oship = $oship[0];

	if(is_null($order['shipment_id'])) {	//shipment has not yet been created with Canada Post
		//these database columns are encrypted
		$enc_keys = array('first_name','last_name','address1','address2','city','province','postal_code','country','email','phone');
		//these shipping methods are Canada Post and available to purchase shipping labels through system
		$methods = array('Regular Parcel','Expedited Parcel','Xpresspost','Priority','Priority Worldwide envelope USA','Priority Worldwide pak USA','Priority Worldwide parcel USA','Xpresspost USA','Expedited Parcel USA','Small Packet USA Air','Tracked Packet – USA');
		//these service-codes are the Canada Post codes for the above $labels
		$servicecodes = array('DOM.RP','DOM.EP','DOM.XP','DOM.PC','USA.PW.ENV','USA.PW.PAK','USA.PW.PARCEL','USA.XP','USA.EP','USA.SP.AIR','USA.TP');
		
		foreach($oship as $k => $v) {
			if(in_array($k,$enc_keys) && $v!="") {
				$oship[$k] = decrypt($v);
			}
		}

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
		
	/* print_r($info)
	Array ( [that-product] => Array ( [option] => Array ( [Colors] => White [Size] => Approve ) [name] => that-product [count] => 1 ) ) 
	*/

		//get item information
		foreach($info as $in) {
			$items = logged_query_assoc_array("SELECT * FROM ecom_product WHERE url = '{$in['name']}'",null,0,array());
		}
		
		//get shipping preferences
		foreach($ship_prefs as $k => $v) {
			if(substr($k, 0, 3) == "cp_") {
				$ship_prefs[$k] = decrypt($v);
			}
		}
		//get shipping service code
		foreach($methods as $k=>$v) {
			if($v==$ship_method) $servicecode = $servicecodes[$k];
		}
		
		$currency = $oship['country']=="CA" ? "CAD" : "USD";
		
		//get current usd->cad exchange rate required for customs blah
		if($currency != "CAD") {
			$yql_query_url = "https://query.yahooapis.com/v1/public/yql?q=select%20*%20from%20yahoo.finance.xchange%20where%20pair%20in%20(%22USDCAD%22)&format=json&diagnostics=true&env=store%3A%2F%2Fdatatables.org%2Falltableswithkeys&callback=";
			$session = curl_init($yql_query_url);
			curl_setopt($session, CURLOPT_RETURNTRANSFER,true);
			$json = curl_exec($session);
			$response = json_decode($json);
			if(!is_null($response->query->results)){
				$xrate = $response->query->results->rate->Rate;
			}
		} else {
			$xrate = "1.00";
		}
	?>
	<style>
	input.readonly {
		background:none;
		border:none;
		font-size:15px;
	}
	.tblOrderDetails th {
		padding:5px;
		background:#ccc;
		border-radius: 5px;
	}
	td {
		padding:5px;
		text-align:left;
	}
	td:nth-child(2n) {
		text-align:left;
		background:none;
	}
	tr:nth-child(2n+1) {
		background-color:#fff;
	}
	</style>
	<?php
		$form = "<form id='shipForm' action='{$_config['admin_url']}modules/shopping_cart/ajax/shiporder_result.php' method='post'><fieldset><legend>Order Id: {$id}</legend>";
		//company info
		$form .= "<input type='hidden' name='orderid' value='{$id}' />";
		$form .= "<input type='hidden' name='username' value='{$ship_prefs['cp_username']}' />";
		$form .= "<input type='hidden' name='password' value='{$ship_prefs['cp_password']}' />";
		$form .= "<input type='hidden' name='customerNumber' value='{$ship_prefs['cp_customerNumber']}' />";
		$form .= "<input type='hidden' name='company_name' value='{$_config['company_name']}' />";
		$form .= "<input type='hidden' name='phone' value='{$_config['shopping_cart']['preferences']['phone']}' />";
		$form .= "<input type='hidden' name='addressLine1' value='{$ship_prefs['street_address']}' />";
		$form .= "<input type='hidden' name='city' value='{$ship_prefs['city']}' />";
		$form .= "<input type='hidden' name='province' value='{$ship_prefs['province']}' />";
		$form .= "<input type='hidden' name='postal_code' value='{$ship_prefs['postal_code']}' />";
		$form .= "<input type='hidden' name='currency' value='{$currency}' />";
		$form .= "<input type='hidden' name='conversion-from-cad' value='{$xrate}' />";
		
		//order info
		$form .= "<fieldset><legend>Order Details</legend><table class='tblOrderDetails' style='width:50%;'>";
		$form .= "<tr><th style='width:80%;'>Product</th><th>Qty</th></tr>";
		$weight = 0;
		
	/* print_r($items)
	Array ( [0] => Array ( [id] => 1 [url] => that-product [title] => That Product [desc] => <p>This is the long description of First Product. It's the long description because it describes First Product in a longer sentence than the short description. This is the long description of First Product.</p> [short_desc] => <p>This is the short description of First Product.</p> [price] => 4.00 [q1] => 0 [q2] => 99999999 [price2] => 0.00 [q3] => 0 [q4] => 0 [price3] => 0.00 [q5] => 0 [q6] => 0 [seo_title] => First Product [seo_keywords] => First Product [seo_description] => First Product [category_id] => 5 [product_type] => 1 [featured] => 0 [status] => 1 [date_created] => [date_updated] => 2014-05-30 22:21:36 [weight] => 1.7000 [agent] => 1 ) )
	*/
		//hack the weight
		$weightkey = $ship_prefs['weight_type'] == "LB" ? "weight_lb" : "weight_kg";
		foreach($items as $item) {
			$qty = "";
			foreach($info as $in) {
				if($item['url']==$in['name']) $qty = $in['count'];
			}
			$form .= "<tr><td style='border-bottom:1px solid #ccc;'>{$item['title']}";
			// item values required for customs
			$form .= "<input type='hidden' name='items[{$item['url']}][customs-description]' value='{$item['title']}' />";
			$form .= "<input type='hidden' name='items[{$item['url']}][unit-weight]' value='{$item[$weightkey]}' />";
			$form .= "<input type='hidden' name='items[{$item['url']}][customs-value-per-unit]' value='{$item['price']}' />";
			$form .= "<input type='hidden' name='items[{$item['url']}][customs-number-of-units]' value='{$qty}' />";
			
			$form .= "</td><td style='text-align:center; border-bottom:1px solid #ccc;'>{$qty}</td></tr>";
			$weight = $weight + ($item[$weightkey]*$qty);
		}
		$form .= "</table></fieldset>";

		//ship-to info
		$form .= "<fieldset><legend>Ship to</legend>";
		$form .= "<label for=cust_name>Customer Name:</label>&nbsp;&nbsp;<input type='text' name='cust_name' value='{$oship['first_name']} {$oship['last_name']}' class='readonly' readonly /><br /><br />";
		//$form .= "<span style='display:inline-block; width:1em;'>&nbsp;</span>";
		$form .= "<label for=cust_address>Customer Address:</label>&nbsp;&nbsp;<input type='text' name='cust_address' value='{$oship['address1']}, {$oship['address2']}' class='readonly' readonly />&nbsp;<input type='text' name='cust_city' value='{$oship['city']}' class='readonly' readonly />&nbsp;<input type='text' style='width:2em;' name='cust_province' value='{$oship['province']}' class='readonly' readonly />&nbsp;<input type='text' style='width:4em;' name='cust_postal_code' value='{$oship['postal_code']}' class='readonly' readonly />&nbsp;<input type='text' style='width:2em;' name='cust_country' value='{$oship['country']}' class='readonly' readonly /><br />";
		$form .= "<label for=cust_phone>Customer Phone:</label>&nbsp;&nbsp;<input type='text' name='cust_phone' value='{$phone}' class='readonly' readonly />";
		$form .= "</fieldset>";
		//shipment info
		$form .= "<fieldset><legend>Shipment Details</legend>";
		$form .= "<table><tr><td><label for=servicecode>Shipping Method:</label></td><td colspan=5><input type='text' style='width:10em;' name='servicecode' value='{$servicecode}' class='readonly' readonly />&nbsp;=&nbsp;<input type='text' name='ship_method' value='{$ship_method}' class='readonly' readonly /></td></tr>";
		$form .= "<tr><td><label for=netweight>Net Weight:</label></td><td style='width:10em;'><input type='text' style='width:2em;' name='netweight' value='{$weight}' class='readonly' readonly />&nbsp;{$ship_prefs['weight_type']}</td><td colspan=4><label for=weight>Total Packaged Weight: <small style='margin:0 1em;'>Net Weight + packaging</small></label><input type='text' style='width:4em;' name='weight'  />&nbsp;{$ship_prefs['weight_type']}</td></tr>";
		
		$form .= "<tr><td><label>Package Dimensions:</label></td><td colspan=5><label for=length style='margin:0 1em;'>Length</label><input type='text' style='width:2em;' name='length' />&nbsp;{$ship_prefs['dimensionUnit']}<label for=width style='margin:0 1em;'>Width</label><input type='text' style='width:2em;' name='width' />&nbsp;{$ship_prefs['dimensionUnit']}<label for=height style='margin:0 1em;'>Height</label><input type='text' style='width:2em;' name='height' />&nbsp;{$ship_prefs['dimensionUnit']}</td></tr>";
		$form .= "<tr><td colspan=6><button id='createshipment' type='button'>Create Shipment</button></td></tr>";
		
		$form .= "</table></fieldset></fieldset></form>";
		
		echo $form;
	} else {	//shipment has already been created with Canada Post
		echo "<div style='width:40%;margin:0 auto;'><h2>Shipment for Order #{$order['id']} Created Successfully</h2><p><strong>Shipment ID:</strong>&nbsp;{$order['shipment_id']}</p>";
		$form = "";
		$form .= "<form id='labelForm' action='{$_config['admin_url']}modules/shopping_cart/ajax/getartifact.php' method='post'>";
		$username = decrypt($ship_prefs['cp_username']);
		$password = decrypt($ship_prefs['cp_password']);
		$customerNumber = decrypt($ship_prefs['cp_customerNumber']);
		$form .= "<input type='hidden' name='username' value='{$username}' />";
		$form .= "<input type='hidden' name='password' value='{$password}' />";
		$form .= "<input type='hidden' name='customerNumber' value='{$customerNumber}' />";
		$form .= "<input type='hidden' name='artifact_id' value='{$order['artifact_id']}' />";
		$form .= "<button id='getartifact' type='button'>Create Shipping Label</button>";
		$form .= "</form></div>";
		echo $form;
	}
}
?>
<script>
$(document).ready( function() {
	$('#createshipment').click( function() {
		$(this).replaceWith("<img src='../../images/loader_light_blue.gif' />");
		$.ajax({
			url: $('#shipForm').attr("action"),
			type: 'POST',
			dataType: 'html',
			data: $('#shipForm').serialize(),
			success: function(data, textStatus, xhr) {
				$.fancybox({
					'content': data,
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
			},
			error: function(xhr, textStatus, errorThrown) {
			alert("An error occurred.");
			}
		});
		return false;
	});
	
	$('#getartifact').on('click', function() {
		$(this).replaceWith("<img src='../../images/loader_light_blue.gif' />");
		$.ajax({
			url: $('#labelForm').attr("action"),
			type: 'POST',
			dataType: 'html',
			data: $('#labelForm').serialize(),
			success: function(data, textStatus, xhr) {
				$.fancybox({
					'content': data,
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
			},
			error: function(xhr, textStatus, errorThrown) {
			alert("An error occurred.");
			}
		});
		return false;
	});
});
</script>