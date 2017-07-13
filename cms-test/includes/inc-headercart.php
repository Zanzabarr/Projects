<style>
#myCart { clear:both; position:relative; }

#myCart #cart_text {
	width: 70%;
	float: right;
	display:inline-block;
	/*border:1px solid #ddd;*/
	height:40px;
	color:#faef03;
	-webkit-box-sizing: border-box; /* Safari/Chrome, other WebKit */
	-moz-box-sizing: border-box;    /* Firefox, other Gecko */
	box-sizing: border-box;         /* Opera/IE 8+ */
	padding: 1.25em .55em 0 .55em;
	background:none;
}
#cartsubmit {
	float: right;
	width:33px;
	height:26px;
	border:none;
	background:url('images/cart-icon.png') no-repeat center;
	opacity:1.0;
	cursor:pointer;
	cursor: hand;
	margin-top: 1em;
}
#cartsubmit:hover {
	opacity: .7;
}
</style>

<section id="myCart">
	<div id="cart_text"></div><button id="cartsubmit" name="cartsubmit" value="" onclick="location.href='shopping/cart';"></button>
</section>
<?php
if (isset($_SESSION['cart_qty']) && $_SESSION['cart_qty'] > 0 )
{
	$cart_text = $_SESSION['cart_qty'] . ' Items: $' . $_SESSION['cart_price'];
}
else $cart_text = 'Empty &nbsp; &nbsp; $0.00';
?>
<script>
$(document).ready( function() {
	$('#myCart #cart_text').html('<?php echo $cart_text; ?>');
});
</script>