<?php
$countries = array('','CA','US');
$provinces = array('','AB','BC','MB','NB','NL','NT','NS','NU','ON','PE','QC','SK','YT','AL','AK','AZ','AR','CA','CO','CT','DE','FL','GA','HI','ID','IL','IN','IA','KS','KY','LA','ME','MD','MA','MI','MN','MS','MO','MT','NE','NV','NH','NJ','NM','NY','NC','ND','OH','OK','OR','PA','RI','SC','SD','TN','TX','UT','VT','VA','WA','WV','WI','WY');
$expmonths = array('01','02','03','04','05','06','07','08','09','10','11','12');
$curYear = date('Y');
$expyears = array($curYear,$curYear+1,$curYear+2,$curYear+3,$curYear+4,$curYear+5,$curYear+6,$curYear+7);

if(isset($_POST['orderform'])) {
	$frmfields = $_POST;
} else if(isset($check['first_name']) && $check['first_name']!="") {
	foreach($check as $k=>$v) {
		if($v != "") {
			$frmfields[$k] = decrypt($v);
		} else {
			$frmfields[$k] = $v;
		}
	}
} else {
	$frmfields = false;
}
$monprefs = logged_query("SELECT profile_key FROM ecom_moneris_prefs WHERE id = 1",0,array());
$profile_key = decrypt($monprefs[0]['profile_key']);
$divname = "paymentform";
$btntext = "Submit Payment";

?>
<script>
	function doMonerisSubmit() {
		var monFrameRef = document.getElementById('monerisFrame').contentWindow;
		monFrameRef.postMessage('','https://esqa.moneris.com/HPPtoken/index.php');
		return false;
	}
	var respMsg = function(e) {
		var respData = eval("(" + e.data + ")");
		if(respData.responseCode == "001") {
			//document.getElementById("data_key").innerHTML = respData.dataKey;
			$('#data_key').val(respData.dataKey);
			$('#orderform').submit();
		} else {
			document.getElementById("monerisResponse").innerHTML = e.origin + " SENT " + " - " +
			respData.responseCode + "-" + respData.dataKey + "-" + respData.errorMessage;
		}
		document.getElementById("monerisFrame").style.display = 'none';
	}
	
	$(document).ready( function() {
		$("#cvd_value").keyup(function() {
			$("#cvd_value").val(this.value.match(/[0-9]*/));
		});
	});

	window.onload = function() {
		if (window.addEventListener) {
			window.addEventListener ("message", respMsg, false);
		} else {
			if (window.attachEvent) {
				window.attachEvent("onmessage", respMsg);
			}
		}
	}
	
	function fillbill() {
		$("input#first_name").val("<?php echo trim($frmfields['first_name']); ?>");
		$("input#last_name").val("<?php echo trim($frmfields['last_name']); ?>");
		$("input#address1").val("<?php echo trim($frmfields['address1']); ?>");
		$("input#address2").val("<?php echo trim($frmfields['address2']); ?>");
		$("input#city").val("<?php echo trim($frmfields['city']); ?>");
		$("input#postal_code").val("<?php echo trim($frmfields['postal_code']); ?>");
		return false;
	}
</script>
		
		<form action="<?php echo $_config['path']['shopping_cart']."payment/"; ?>" method="post" enctype="application/x-www-form-urlencoded" name="billingform" id="orderform">
			<div id="order_error"><?php
				if($ordererrors) {
					foreach($ordererrors as $k=>$v) {
						echo "<p class='clear'>$v</p>";
					}
				}
			?></div>
			<input type="hidden" name="order_id" value="<?php echo $check['id']; ?>" />
			<input type="hidden" name="total" value="<?php echo $check['total']; ?>" />
			<fieldset><legend>Payment Informaton</legend>
			<table id='card_table'>
				<tr><td /><td><img src="<?php echo $_config['admin_url'];?>modules/shopping_cart/images/credit_cards.png" /></td>
				</tr><tr><td colspan=2><div id=monerisResponse></div></td></tr>
				<tr><td style="vertical-align:top;"><label>Credit Card Number</label></td><td><input type="hidden" id="data_key" name="data_key" value="" /><iframe id=monerisFrame src="https://esqa.moneris.com/HPPtoken/index.php?id=<?php echo $profile_key; ?>&css_body=background:none;&css_textbox=width:100%;border-radius: 4px; margin-bottom:1em; border:1px solid silver; padding:4px;font-size:1.05em; background:#fcfcfc; font-family: arial,sans-serif;overflow-y:hidden;" frameborder='0' width="230px" height="30px"></iframe></td><td style="vertical-align:top;"><label style="font-size:.8em;">(no spaces or punctuation)</label></td></tr>
				<tr><td><label>Expiration Date</label></td>
				<td style="padding-top:12px;"><select name="expmonth">
				<?php foreach($expmonths as $m) {
					echo "<option value='$m'>$m</option>";
				}
				?>
				</select><span style="display:inline-block; width:1.5em; font-weight:600; font-size:1.10em; text-align:center;">/</span>
				<select name="expyear">
				<?php foreach($expyears as $y) {
					echo "<option value='$y'>$y</option>";
				}
				?>
				</select>
				</td><td style="vertical-align:top;"><label style="font-size:.8em;">(month / year)</label></td></tr>
				<tr><td><label>Verification Code</label></td><td><input type="text" maxlength="3" style="max-width:30px;" id="cvd_value" name="cvd_value" required /></td><td><label style="font-size:.8em;">What's this?</label></td></tr>
			</table>
			</fieldset>
			<div class="clear2em"></div>
			<fieldset><legend>Billing Information</legend>
			<button id="sameasshipping" style="margin-bottom:1em; float:right;" onclick="fillbill(); return false;">Same as Shipping</button>
			<input type="hidden" name="<?php echo $divname; ?>">
			<table>
				<tr><td><label for=first_name>First Name</label></td><td><label for=last_name>Last Name</label></td></tr>
				<tr><td><input <?php echo isset($ordererrors['first_name']) ? 'class="formerror"'  : ""; ?> type="text" name="first_name" id="first_name" value="<?php echo isset($ordererrors['first_name']) ? trim($frmfields['first_name']) : '' ; ?>" required /></td>
				<td><input <?php echo isset($ordererrors['last_name']) ? 'class="formerror"'  : ""; ?> type="text" name="last_name" id="last_name" value="<?php echo isset($ordererrors['last_name']) ? trim($frmfields['last_name']) : '' ; ?>" required /></td></tr>
				<tr><td colspan=2><label for=address1>Address Line 1</label></td></tr>
				<tr><td colspan=2><input class="full<?php echo isset($ordererrors['address1']) ? ' formerror'  : ""; ?>" type="text" name="address1" id="address1" value="<?php echo isset($ordererrors['address1']) ? trim($frmfields['address1']) : '' ; ?>" required /></td></tr>
				<tr><td colspan=2><label for=address2>Address Line 2 (optional)</label></td></tr>
				<tr><td colspan=2><input class="full<?php echo isset($ordererrors['address2']) ? ' formerror'  : ""; ?>" type="text" name="address2" id="address2" value="<?php echo isset($ordererrors['address2']) ? trim($frmfields['address2']) : '' ; ?>" /></td></tr>
				<tr><td><label for=city>City</label></td><td><label for=province>Province/State (2)</label></td></tr>
				<tr><td><input <?php echo isset($ordererrors['city']) ? 'class="formerror"'  : ""; ?> type="text" id="city" name="city" value="<?php echo isset($ordererrors['city']) ? trim($frmfields['city']) : '' ; ?>" required /></td>
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
				<tr><td><label for=postal_code>Postal/Zip Code</label></td><td><label for=country>Country (2)</label></td></tr>
				<tr><td><input <?php echo isset($ordererrors['postal_code']) ? 'class="formerror"'  : ""; ?> type="text" name="postal_code" id="postal_code" value="<?php echo isset($ordererrors['postal_code']) ? trim($frmfields['postal_code']) : '' ; ?>" required /></td>
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
				<tr><td><label for=email>Email</label></td><td><label for=phone>Phone</label></td></tr>
				<tr><td><input <?php echo isset($ordererrors['email']) ? 'class="formerror"'  : ""; ?> type="text" name="email" value="<?php echo isset($frmfields['email']) ? trim($frmfields['email']) : '' ; ?>" required /></td>
				<td><input <?php echo isset($ordererrors['phone']) ? 'class="formerror"'  : ""; ?> type="text" name="phone" value="<?php echo isset($frmfields['phone']) ? trim($frmfields['phone']) : '' ; ?>" required /></td></tr>
				<tr><td colspan=2><input type='button' onClick=doMonerisSubmit() name='order_submit' class='cartbutton' value='<?php echo $btntext; ?>' /></td></tr>
			</table>
			</fieldset>
		</form>

