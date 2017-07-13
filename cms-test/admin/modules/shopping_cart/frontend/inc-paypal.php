
	<div style="text-align:right; margin:6px 20px;">
	<form action="https://www<?php echo isset($_config['sandbox']) && $_config['sandbox'] ? '.sandbox' : ''; ?>.paypal.com/cgi-bin/webscr" method="post" name="paypal" style="display:inline;">
	<input type="hidden" name="cmd" value="_cart">
	<input type="hidden" name="upload" value="1">
	<input type="hidden" name="business" value="<?php echo isset($_config['sandbox']) && $_config['sandbox'] ? $_config['store_id'] : decrypt($_config['store_id']); ?>">
	<input type="hidden" name="lc" value="EN">
	<input type="hidden" name="custom" value="<?php echo $check['id']; ?>">
	<input type="hidden" name="currency_code" value="<?php echo $_config['currency']; ?>">
	<?php echo $itemlist; 
		echo $autofill; ?>
	<!--<input type="hidden" name="amount" value="< ?php echo $finalcost - $shipping - $total_tax; ?>">-->
	<input type="hidden" name="notify_url" value="<?php echo $_config['admin_url']; ?>modules/shopping_cart/ajax/listener.php">
	<input type="hidden" name="no_note" value="<?php echo $_config['no_note']; ?>">
	<input type="hidden" name="cn" value="<?php echo $_config['note_comment']; ?>">
	<input type="hidden" name="no_shipping" value="<?php echo $_config['no_shipping']; ?>">
	<?php 
	if($check['ship_price'] > 0) : ?><input type="hidden" name="handling_cart" value="<?php echo $check['ship_price']; ?>"><?php endif;
	if($total_tax > 0) : ?><input type="hidden" name="tax_cart" value="<?php echo $total_tax; ?>"><?php endif;
	?>
	<input type="hidden" name="rm" value="2">
	<input type="hidden" name="return" value="<?php echo $_config['path']['shopping_cart']; ?>success">
	<input type="hidden" name="cancel_return" value="<?php echo $_config['path']['shopping_cart']; ?>cart">
	<a href="#" class="cartbutton" name="submit" onClick="document.paypal.submit();return false;">Payment</a>
	<img alt="" border="0" src="https://www.paypalobjects.com/WEBSCR-640-20110429-1/en_US/i/scr/pixel.gif" width="1" height="1" />
	</form>
	</div>