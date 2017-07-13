<?php
// figure out the different uri sets
//$keyAr = array_keys($uri,'remove');

if (uri::get(2) == 'remove')
{
	$action = 'remove';
//	$product_id = $uri[$uri[3]];
}
elseif (uri::get(2) == 'removecoupon')
{
	$action = 'removecoupon';
}
$keyAr = array_keys($uri,'opt');
if (count($keyAr) > 0)
{
	$remove_opt = $uri[$keyAr[0] + 1];
}

if(!isset($action)) $action = '';
switch($action) { //decide what to do

    case "add":
        $_SESSION['cart'][$product_id]['count']++; //add one to the quantity of the product with id $product_id
    break;

    case "remove":
		if($remove_opt == 'none'){
			$xx = $remove_opt;
		} else{
			$xx = $remove_opt." ";
		}
		unset($_SESSION['cart'][$remove_opt]); 
		
		foreach($_SESSION['cart'] as $a=>$b){
			if(empty($b)){
				unset($_SESSION['cart'][$a]);
			}
		}
		if(empty($_SESSION['cart'])) { //cart is empty - redirect to shopping page
			echo "<script>location.href = '{$_config['site_path']}shopping';</script>";
		}
    break;
	
	case "removecoupon":
        unset($_SESSION['coupon']); 
    break;

    case "empty":
        unset($_SESSION['cart']); //unset the whole cart, i.e. empty the cart.
    break;

}

if(isset($_POST['coupon']) && !isset($_SESSION['coupon'])){
	//get coupon info
	$couponinfo = logged_query_assoc_array("SELECT * FROM `ecom_coupons` WHERE `code` = :code",null,0,array(
		':code' => trim($_POST['coupon'])
	));
		if($couponinfo)	$couponinfo = $couponinfo[0];
		if(isset($couponinfo['id'])){
			my_log( $couponinfo['exp'] . ':' . date("Y-m-d H:i:s", time()) );
			if($couponinfo['exp'] < date("Y-m-d H:i:s", time())){
				// error - coupon code has expired	
				$couponerror = "Coupon code has expired";
			} else {
				$_SESSION['coupon'] = $couponinfo;
			}
		} else {
		// error - coupon code doesnt exist	 --------------------------------------------------------!
		$couponerror = "Invalid coupon code";
		}
	
} elseif(isset($_POST)) {
	$ship_prefs = logged_query_assoc_array("SELECT * FROM `ecom_ship_prefs` WHERE `id` = '1'",null,0,array());
	$ship_prefs = $ship_prefs[0];
	if(isset($_POST['update'])){
		foreach($_POST as $k=>$v){
			if($k != 'update' && $k != 'coupon'){
				$_SESSION['cart'][$k]['count'] = $v;
			}
		}
	}else{
		foreach($_POST as $x=>$y){
			$z = $x;
			if($x != 'option' && $x != 'coupon'){
				if(isset($_SESSION['cart'][$x])){//if item exists
					//-- check to see if same options
					if(
						( // options are same
							is_array($_SESSION['cart'][$x]['option']) &&
							is_array($_POST['option']) &&
							!array_diff($_SESSION['cart'][$x]['option'], $_POST['option'])
						)
						||
						(
						//post has no options
							!isset($_POST['option'])
						)
					){
						//same
						// 'Already set, add to quantity';
						
						//$_SESSION['cart'][$x]['count'] = $_SESSION['cart'][$x]['count'] + $_POST[$x];
						$skip = 1;
						
					}  else {
						
						// 'different';
						
						// if new...
						$count = 1;
						while(isset($_SESSION['cart'][$x.'-'.$count])){
							if(!array_diff($_SESSION['cart'][$x.'-'.$count]['option'], $_POST['option'])){
								//same
								
								//$x = $x.'-'.$count;
								$_SESSION['cart'][$x.'-'.$count]['count'] = $_SESSION['cart'][$x.'-'.$count]['count'] + $y;
								$_SESSION['cart'][$x.'-'.$count]['name'] = $x;
								break 2;
								//$skip = 1;
							}else{
							++$count;
							}
						}
						
						$x = $x.'-'.$count;
						//print_r($x);
					
					}
				}
				//changes x if was already set
					if(isset($_POST['option']) && !isset($skip)){
						foreach ($_POST['option'] as $nu=>$op){
							$_SESSION['cart'][$x]['option'][$nu] = $op;
							
							$_SESSION['cart'][$x]['name'] = $z;
							//array_push($optt, $nu.' - '.$op);
						}
					}elseif(!isset($skip)){
						$_SESSION['cart'][$x]['option'] = 'none';
						$_SESSION['cart'][$x]['name'] = $z;
					}
					$_SESSION['cart'][$x]['count'] = isset( $_SESSION['cart'][$x]['count'] ) ? $_SESSION['cart'][$x]['count'] + $y : $y;
			}
		}
	}
}

// title and content
echo "<h1>{$scData['title']}</h1>";
echo htmlspecialchars_decode($scData['content']);
if(isset($_SESSION['cart']) && $_SESSION['cart']) { //if the cart isn't empty

$formAction = $_config['path']['shopping_cart']."checkout/";
//show the cart
?><form class="shopform" id="cartupdate" action="<?php echo $formAction; ?>" method="post" enctype="application/x-www-form-urlencoded" name="cartupdate">
<fieldset><legend>Shopping Cart</legend>
   <?php
    echo "<table class=\"carttable\" width=\"100%\">"; //format the cart using a HTML table
?>
<tr>
	<th width="50%" style="text-align:left;"><strong>Product</strong></th>
	<th align="center"><strong>Quantity</strong></th>
	<th align="center"><strong>Total</strong></th>
	<th width="100" align="center"><strong>Remove</strong></th>
</tr>
<?php
$tr = 0;
    //iterate through the cart, the $product_id is the key and $quantity is the value
	$total = 0;
	$total_weight = 0;
	$total_qty = 0;
	
    foreach($_SESSION['cart'] as $product_id => $product_info) {
		
		$quantity = round($product_info['count']);
		$result = ecom_functions::getProductByUrl($product_info['name']);

		$price = $_config['shopping_cart']['preferences']['includePricing']==1 ? ecom_functions::getpricefromQ($product_info['name'], $quantity) : 0;
		$weight = $ship_prefs['weight_type']=="LB" ? $result['weight_lb'] : $result['weight_kg'];

		$optionprice = 0;
		$optionweight = 0;
		if($price!=0) {
			foreach($product_info as $k=>$v){
				if($k == 'option' && is_array($v)){
					foreach($v as $a=>$b){
						$a = addslashes($a);
						$b = addslashes($b);
						//--- options
						$optioninfo = logged_query_assoc_array("SELECT * FROM `ecom_product_options` WHERE `opt_name` = '$a' AND `option` = '$b' AND `prod_id` = '{$result['id']}'",null,0,array());
						
						$optioninfo = $optioninfo[0];
						$optionprice += $optioninfo['price'];
						$optionweight += $optioninfo['weight'];
					}
				}
			}
		}
		$line_cost = ($price + $optionprice) * $quantity; //work out the line cost
		$total = $total + $line_cost; //add to the total cost
		$line_weight = ($weight + $optionweight) * $quantity; //work out the line cost
		$total_weight = $total_weight + $line_weight;
		$total_qty += $quantity;
		
		//format table
		if($tr % 2 == 0){
			echo "<tr class=\"tr1\">";
		} else {
			echo "<tr class=\"tr2\">";
		}
		$tr++;
		
		//show this information in table cells
		echo "<td style=\"border-left:0;width:33%;\"><a href=\"".$_config['path']['shopping_cart']."products/".$result['url']."\">";

		/*if($result['img_name'] == ''){ ?>
			<img src="admin/images/default.jpg" class="itemimage" style="margin:5px 10px 10px 0;" />
		<?php }else{ ?>
			<img src="<?php echo $_config['upload_url']; ?>ecom/thumb/<?php echo $result['img_name']; ?>" class="itemimage" style="margin:5px 10px 10px 0;" />
		<?php } */
		
		echo "<strong>".$result['title']."</strong></a><br />";
		if($product_info['option'] && is_array($product_info['option']) ) : foreach($product_info['option'] as $o=>$c){
			echo $o.' - '.$c."<br />";
		} endif;
		echo "</td>";
		?>
		<td align="center" style="width:100px;">
		<input type="number" name="<?php echo $product_id; ?>" class="quantity" value="<?php echo $quantity; ?>" style="width:50px;"/></td>
		<input type="hidden" name="update" />
		<?php
		echo "<td align=\"center\" class=\"pink\">$&nbsp;".number_format($line_cost, 2, '.', ',')."</td>";
		echo "<td align=\"center\"><a href=\"{$_config['path']['shopping_cart']}cart/remove/{$product_id}/opt/".htmlentities($product_id)."\" onclick=\"return confirm('Are you sure?');\" ><img src=\"{$_config['site_path']}admin/images/delete.png\" alt=\"Remove\"/></a></td>";
		echo "</tr>";
    }
	echo "</form>";
	
	//coupon
	if(isset($_config['coupons']) && $_config['coupons'])
	{	?>
    <tr>
    	<td colspan="2" style="text-align:right;padding:15px 0;vertical-align:middle;">
		<p>Apply Coupon Code:&nbsp;</p>
		</td><td colspan="4" style="width:55%; padding:15px 0; vertical-align:middle;">
        <?php
		if(isset($_SESSION['coupon'])){
		$discount = number_format($_SESSION['coupon']['discount'], 0, '.', ',');
			echo "<span style='margin:4px 8px;display:inline-block'>Coupon: <strong>". strtoupper($_SESSION['coupon']['code'])."</strong>&nbsp;&nbsp;&nbsp; -{$discount}%</span> <a class='cartbutton' style=\"float:right;display:inline-block;\" href=\"{$_config['path']['shopping_cart']}cart/removecoupon\" onclick=\"return confirm('Are you sure?');\" >Remove Coupon</a>";
		} else { 
			if(isset($couponerror)){ ?>
				<script type="text/javascript">
					$(function() {
						alert('<?php echo $couponerror; ?>');
						return false;
					});
				</script>
			<?php }?>
            
			<form action="<?php echo $_config['path']['shopping_cart']; ?>cart/" method="post" enctype="multipart/form-data" name="addcoupon" style="display:inline-block;width:100%;">
                <input type="text" name="coupon" id="coupon" />
                &nbsp;
				<a href="#" class="cartbutton" onClick="document.addcoupon.submit();return false;" style="margin-top:0; display:inline-block;">Apply Coupon</a>
			</form>
            
			<?php
		} 
			?>
        </td>
    </tr>
<?php
	} // end optional coupons

    //show the total
    echo "<tr>";
	
	if(isset($_SESSION['coupon'])){
		if($_SESSION['coupon']['discount'] >= 100){echo 'ERROR';}
		if($_SESSION['coupon']['discount'] < 10){$x = '.'.'0'.$_SESSION['coupon']['discount'];}
		else{$x = '.'.$_SESSION['coupon']['discount'];}
		$y = $total * $x;
		$total = $total - $y;
	}
	$subtotal = $_config['shopping_cart']['preferences']['includePricing']==1 ? "Subtotal : <h3 style='display:inline-block;color:#000;margin-bottom:2px;'>$".number_format($total, 2, '.', ',')."</h3>" : $_config['shopping_cart']['preferences']['altPriceText']."<span style='margin-right:8em;'>&nbsp;</span>";
    ?><td colspan="6" align="right"><?php echo $subtotal;?></td><?php
    echo "</tr></table>";
?>
</fieldset>
<div style="text-align:right; margin:20px;">

<a href="#" id="checkout_cart" class="cartbutton">Checkout</a><?php /* posts to formAction */?>
<a href="#" id="update_cart" data-link="<?php echo $_config['path']['shopping_cart']; ?>cart/" class="cartbutton" >Update Cart</a>&nbsp;

<a href="<?php echo $_config['path']['shopping_cart']; ?>products" class="cartbutton">Continue Shopping</a>&nbsp;

</div>
<?php

// set Session data for quantity and total cost
$_SESSION['cart_qty'] = round($total_qty);
$_SESSION['cart_price'] =  number_format($total, 2, '.', ',');

}else{
//otherwise tell the user they have no items in their cart
    echo "You have no items in your shopping cart.";
	unset($_SESSION['cart_qty']);
	unset($_SESSION['cart_price']);
}

if (isset($_SESSION['cart_qty']) && $_SESSION['cart_qty'] > 0 )
{
	$cart_text = $_SESSION['cart_qty'] . ' Items: $' . $_SESSION['cart_price'];
}
else $cart_text = 'No Items';
// use js to update the cart values in the header
?>
<script type="text/javascript">
$(function() {
	$('#shopping-cart span a').html('SHOPPING CART: <?php echo $cart_text; ?>');
	return false;
});
</script>
<?php
?><div style="clear:both;"></div>
