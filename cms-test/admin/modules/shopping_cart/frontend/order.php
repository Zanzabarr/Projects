<?php
//set title text
$formtitle = $_config['shopping_cart']['preferences']['shipping_on']==1 ? "Ship To:" : "Order Information:";
$countries = array('','CA','US');
$provinces = array('','AB','BC','MB','NB','NL','NT','NS','NU','ON','PE','QC','SK','YT','AL','AK','AZ','AR','CA','CO','CT','DE','FL','GA','HI','ID','IL','IN','IA','KS','KY','LA','ME','MD','MA','MI','MN','MS','MO','MT','NE','NV','NH','NJ','NM','NY','NC','ND','OH','OK','OR','PA','RI','SC','SD','TN','TX','UT','VT','VA','WA','WV','WI','WY');

if(isset($_POST['orderform'])) {
	$frmfields = $_POST;
} else if(isset($check['first_name']) && $check['first_name']!="") {
	foreach($check as $k=>$v) {
		if(trim($v) != "") {
			$frmfields[$k] = decrypt($v);
		} else {
			$frmFields[$k] = trim($v);
		}
	}
} else {
	$frmfields = false;
}

$divname = "orderform";
$btntext = $_config['shopping_cart']['preferences']['purchasing_on']==1 ? "Submit Address" : "Submit Order";
$ship_prefs = logged_query_assoc_array("SELECT * FROM `ecom_ship_prefs` WHERE `id` = '1'",null,0,array());
$ship_prefs = $ship_prefs[0];
$discountText = (isset($ship_prefs['discount_min']) && $ship_prefs['discount_min'] > 0 && $ship_prefs['discount_min'] < $total) ? "A discount of up to \${$ship_prefs['discount']} will be applied to shipping charges." : "";
?>
		<div class="clear2em"></div>
		<strong><?php echo $discountText; ?></strong>
		<form action="<?php echo $_SERVER['REQUEST_URI']; ?>" method="post" enctype="application/x-www-form-urlencoded" name="orderform" id="orderform">
			<div id="order_error"><?php
				if($ordererrors) {
					foreach($ordererrors as $k=>$v) {
						echo "<p class='clear'>$v</p>";
					}
				}
			?></div>
			<fieldset><legend><?php echo $formtitle; ?></legend>
			
			<input type="hidden" name="<?php echo $divname; ?>">
			<table>
				<tr><td><label for=first_name>FIRST NAME</label></td><td><label for=last_name>LAST NAME</label></td></tr>
				<tr><td><input <?php echo isset($ordererrors['first_name']) ? 'class="formerror"'  : ""; ?> type="text" name="first_name" value="<?php echo isset($frmfields['first_name']) ? trim($frmfields['first_name']) : '' ; ?>" required /></td>
				<td><input <?php echo isset($ordererrors['last_name']) ? 'class="formerror"'  : ""; ?> type="text" name="last_name" value="<?php echo isset($frmfields['last_name']) ? trim($frmfields['last_name']) : '' ; ?>" required /></td></tr>
				<tr><td colspan=2><label for=address1>ADDRESS LINE 1</label></td></tr>
				<tr><td colspan=2><input class="full<?php echo isset($ordererrors['address1']) ? ' formerror'  : ""; ?>" type="text" name="address1" value="<?php echo isset($frmfields['address1']) ? trim($frmfields['address1']) : '' ; ?>" required /></td></tr>
				<tr><td colspan=2><label for=address2>ADDRESS LINE 2 (optional)</label></td></tr>
				<tr><td colspan=2><input class="full<?php echo isset($ordererrors['address2']) ? ' formerror'  : ""; ?>" type="text" name="address2" value="<?php echo isset($frmfields['address2']) ? trim($frmfields['address2']) : '' ; ?>" /></td></tr>
				<tr><td><label for=city>CITY</label></td><td><label for=province>PROVINCE/STATE (2)</label></td></tr>
				<tr><td><input <?php echo isset($ordererrors['city']) ? 'class="formerror"'  : ""; ?> type="text" name="city" value="<?php echo isset($frmfields['city']) ? trim($frmfields['city']) : '' ; ?>" required /></td>
				<?php
					$curProvince = isset($frmfields['province']) ? trim($frmfields['province']) : '' ;
				?>
				<td><select <?php echo isset($ordererrors['province']) ? 'class="formerror"'  : ""; ?> name="province" required>
				<?php
					foreach($provinces as $p) {
						$selProvince = $p == $curProvince ? "selected='selected'" : "";
						echo "<option value='$p' $selProvince>$p</option>";
					}
				?>
				</select></td></tr>
				<tr><td><label for=postal_code>POSTAL/ZIP CODE</label></td><td><label for=country>COUNTRY (2)</label></td></tr>
				<tr><td><input <?php echo isset($ordererrors['postal_code']) ? 'class="formerror"'  : ""; ?> type="text" name="postal_code" value="<?php echo isset($frmfields['postal_code']) ? trim($frmfields['postal_code']) : '' ; ?>" required /></td>
				<?php
					$curCountry = isset($frmfields['country']) ? trim($frmfields['country']) : '' ;
				?>
				<td><select <?php echo isset($ordererrors['country']) ? 'class="formerror"'  : ""; ?> name="country" required>
				<?php
					foreach($countries as $c) {
						$selCountry = $c == $curCountry ? "selected='selected'" : "";
						echo "<option value='$c' $selCountry>$c</option>";
					}
				?>
				</select></td></tr>
				<tr><td><label for=email>EMAIL</label></td><td><label for=phone>PHONE</label></td></tr>
				<tr><td><input <?php echo isset($ordererrors['email']) ? 'class="formerror"'  : ""; ?> type="text" name="email" value="<?php echo isset($frmfields['email']) ? trim($frmfields['email']) : '' ; ?>" required /></td>
				<td><input <?php echo isset($ordererrors['phone']) ? 'class="formerror"'  : ""; ?> type="text" name="phone" value="<?php echo isset($frmfields['phone']) ? trim($frmfields['phone']) : '' ; ?>" required /></td></tr>
				<tr><td colspan=2><label for=notes>SPECIAL INSTRUCTIONS</label></td></tr>
				<tr><td colspan=2><textarea name='notes' cols=40 rows=10><?php echo isset($frmfields['notes']) ? trim($frmfields['notes']) : '' ; ?></textarea></td></tr>
				<tr><td colspan=2><input type='submit' name='order_submit' class='cartbutton' value='<?php echo $btntext; ?>' /></td></tr>
			</table></fieldset>
		</form>