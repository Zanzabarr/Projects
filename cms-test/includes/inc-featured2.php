<link rel="stylesheet" type="text/css" href="css/shopping.css" />

	<h2>NEW SCRIPTS FOR KIDS TO ENJOY</h2>
	<div class="homePageFeaturedProducts">
<?php
		$products = logged_query_assoc_array("SELECT * FROM `ecom_product` WHERE `featured` > 0 AND `id`>0 AND `status`>0",null,0,array());
		if(!empty($products))
		{
			foreach($products as $product) {
				$images = logged_query("SELECT * FROM `ecom_image` WHERE `item_id` = {$product['id']}",0,array());
				$product['images'] = $images;
				echo "<div class='bordered'><div class='homeproduct-top'>";
					echo "<div class='homeproduct-left'>";
					if(isset($product['images'][0]) && $product['images'][0]['posn'] ) : buildSingleImage("singleImageWrap", $product['images'][0],'mini', $_config['ecom_img_text_desc']);
					else : ?>
					<div class="singleImage">
						<img src="admin/images/no_image.jpg" alt="No Image Available" >
					</div>
					<?php 
					endif;
					?>
					</div><!-- end homeproduct-left -->
					<div class="homeproduct-right">
					<h2><?php echo $product['title']; ?></h2>
					<?php display_content($product['short_desc']); ?>
					<div class="clear"></div>
					</div><!-- end homeproduct-right -->
					<div class="clear"></div>
				</div><!-- end homeproduct-top -->
				<div class="homeproduct-bottom">
				<div class="homeproduct-left">
				<?php
				$num_decimals = (intval($product['price']) == $product['price']) ? 0 :2;
				if(isset($product['price'])) {
					$priceClause = $product['price'] ?  " \$" . number_format($product['price'],$num_decimals) : "";
				} else {
					$priceClause = "";
				} ?>
				<p><span id='curPrice1' data-base-price='<?php echo $priceClause; ?>' data-low='<?php echo $priceClause; ?>' data-high='<?php echo $priceClause; ?>' class='price'>Price <?php echo $priceClause; ?></span></p>
				
				<form action="<?php echo $_config['cartName'] ?>/cart" method="post" enctype="application/x-www-form-urlencoded" name="addtocart">
					
					<input id="quantity" type="hidden" name="<?php echo $product['url']; ?>" class="addtocart" value="1"  />
					
					<a  href="#" class="addbutton">Add to Cart</a>
				</form>
				</div><!-- end homeproduct-left-->
				<div class="homeproduct-right">
					<p class="moreinfo"><a href="<?php echo $_config['path']['shopping_cart']; ?>products/<?php echo $product['url']; ?>">More Details...</a></p>
					<div class="clear"></div>
				</div><!-- end homeproduct-right -->
				<div class="clear"></div>
				</div><!-- end homeproduct-bottom -->
				<div class="clear"></div>
				</div><!-- end bordered -->
				<div class="clear"></div>
				<?php
			}
		}
?>
	</div>
