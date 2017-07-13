<?php	/* this creates a scrollable widget from featured products */

$products = logged_query_assoc_array("SELECT * FROM `ecom_product` WHERE `featured` > 0 AND `id`>0 AND `status`>0",null,0,array());
if(!empty($products)) {
?>

<link rel="stylesheet" type="text/css" href="css/scroll-gallery.css" />
<link rel="stylesheet" type="text/css" href="css/shopping.css" />

<div id='gallery-title' class="clear">
FEATURED PRODUCTS
<div id='gallery-controls'><a class="prev browse left"></a>
<a class="next browse right"></a></div>
</div>
<div class="clear"></div>
<div id="scroll-gallery">
	<!-- "previous page" action moved to titlebar-->
	
	 
	<!-- root element for scrollable -->
	<div class="scrollable" id="scrollable">
	  <!-- root element for the items -->
		<div class="items">
<?php
			foreach($products as $product) {
				$images = logged_query("SELECT * FROM `ecom_image` WHERE `item_id` = {$product['id']}",0,array());
				$product['images'] = $images;
				echo "<div class='homeproduct-top'>";
					echo "<div class='homeproduct-left'>";
					if(isset($product['images'][0]) && $product['images'][0]['posn'] ) : buildSingleImage("singleImageWrap", $product['images'][0],'mini', $_config['ecom_img_text_desc']);
					else : ?>
					
					<div class="singleImage">
						<img src="admin/images/no_image.jpg" alt="No Image Available" >
					</div>
					<br />
					<?php 
					endif;
					?>
					<h5><?php echo $product['title']; ?></h5>
					<?php display_content($product['short_desc']); ?>
					<p class="moreinfo"><a href="<?php echo $_config['path']['shopping_cart']; ?>products/<?php echo $product['url']; ?>">More Details...</a></p>
					
									
				<?php
				$num_decimals = (intval($product['price']) == $product['price']) ? 0 :2;
				if(isset($product['price'])) {
					$priceClause = $product['price'] ?  " \$" . number_format($product['price'],$num_decimals) : "";
					$saleprice = ($product['sale']>0) ? "\$" .number_format($product['sale'],$num_decimals) : "";
				} else {
					$priceClause = "";
					$saleprice = "";
				} 
				if($saleprice == "") {
				?>
				<p><span id='curPrice1' data-base-price='<?php echo $priceClause; ?>' data-low='<?php echo $priceClause; ?>' data-high='<?php echo $priceClause; ?>' class='price'> <?php echo $priceClause; ?></span></p>
				<?php
				} else {
				?>
					<p><span id='curPrice1' data-base-price='<?php echo $saleprice; ?>' data-low='<?php echo $saleprice; ?>' data-high='<?php echo $saleprice; ?>' class='price'> <?php echo $saleprice; ?></span></p>
				<?php
				}
				?>
				<form action="<?php echo $_config['cartName'] ?>/cart" method="post" enctype="application/x-www-form-urlencoded" name="addtocart">
					<input id="quantity" type="hidden" name="<?php echo $product['url']; ?>" class="addtocart" value="1"  />
					
					<a  href="#" class="addbutton">Add to Cart</a>
				</form>
				</div><!-- end homeproduct-left-->
				
				<div class="clear"></div>
				</div><!-- end homeproduct-bottom -->
				
				<?php
			}
?>
		</div><!-- end .items -->

	</div><!-- end #scrollable -->

	<!-- "next page" action moved to titlebar -->
	
</div><!-- end #scroll-gallery -->

<script src="js/jquery.tools.min.js"></script>
<script>
$(document).ready( function() {
	$(".scrollable").scrollable();
	
  // Get the Scrollable control
  var scrollable = $(".scrollable").data("scrollable");

  // Set to the number of visible items
  var size = 1;

  // Handle the Scrollable control's onSeek event
  scrollable.onSeek(function(event, index) {

    // Check to see if we're at the end
    if (this.getIndex() >= this.getSize() - size) {

      // Disable the Next link
      $("a.next.browse.right").addClass("disabled");

    }

  });

  // Handle the Scrollable control's onBeforeSeek event
  scrollable.onBeforeSeek(function(event, index) {

    // Check to see if we're at the end
    if (this.getIndex() >= this.getSize() - size) {
      
      // Check to see if we're trying to move forward
      if (index > this.getIndex()) {

        // Cancel navigation
        return false;

      }

    }

  });
 	// add to cart
	$('.addbutton').on('click', function(e) {
		e.preventDefault();
		$(this).parents('form').submit();
		//$('#addtocart').submit();
	});
});
</script>
<?php
}
?>