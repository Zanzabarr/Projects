<link rel="stylesheet" type="text/css" href="<?php echo $_config['admin_url']; ?>modules/shopping_cart/frontend/style.css" />
<?php include('admin/modules/shopping_cart/frontend/includes/functions.php'); ?>
<h2>Featured Products</h2>
		<ul class='justify'>
		
		<?php
		$productPages = logged_query_assoc_array("SELECT * FROM `ecom_product` WHERE `featured` > 0 AND `id`>0 AND `status`>0 LIMIT 9",null,0,array());
		
		
		// Loop through all the items in the array
		foreach ($productPages as $productArray) {
			$images = logged_query("SELECT * FROM `ecom_image` WHERE `item_id` = {$productArray['id']}",0,array());
			$productArray['images'] = $images;
		?>
		
		<li>
			<div class="itemcontainer">
			<?php
			
				// IMAGE
				// display the image: enable or disable text descriptions based on shopping_cart config 
				if(isset($productArray['images'][0])) {
					$productArray['images'][0]['url'] = $productArray['url'];
				}
				
				if(isset($productArray['images'][0]) && $productArray['images'][0]['posn'] ) { 
					// frontend/includes/functions.php
					productSingleImage("singleImageWrap", $productArray['images'][0],'mini', $_config['ecom_img_text_desc']);
				}
				else { echo "
				<div class='singleImage'>
					<a href='{$_config['path']['shopping_cart']}products/{$productArray['url']}'>
						<img src='admin/images/no_image.jpg' alt='No Image Available' />
					</a>
				</div>";
				}
				
				// TEXT
				$num_decimals = 2; //(intval($productArray['price']) == $productArray['price']) ? 0 :2;
				if($_config['shopping_cart']['preferences']['includePricing']==1) {
					$priceClause = $productArray['price'] ?  " \$" . number_format($productArray['price'],$num_decimals) : "";
					
					$saleprice = ($productArray['sale']>0) ? "\$" .number_format($productArray['sale'],$num_decimals) : "";
					
					$strike = $productArray['price'] && $productArray['sale']>0 ? "strikethru" : "";
				} else {
					$priceClause = "";
					$saleprice = "";
				}
				
			?>
			
				<div class="infocont">
				
					<div class="itemtitle">
						<!-- title -->
						<span class="itemspan">
							<a href="<?php echo $_config['path']['shopping_cart']; ?>products/<?php echo $productArray['url']; ?>">
								<?php echo $productArray['title']; ?>
							</a>
						</span>
					</div>
					
					<!-- short_desc -->
					<div class="itemtitle itemdesc">
						<p>
							<?php display_content($productArray['short_desc']); ?>
						</p>
					</div>
					
					<!-- price [, saleprice ] -->
					<div class="priceBGcover">
						<div class="itemtitle">
							<span class="pricespan <?php echo $strike; ?>"><?php echo $priceClause; ?></span>
							<span class="pricespan red"><?php echo $saleprice; ?></span>
						</div>
					</div>
					
				</div>
				
			</div>
		</li>
		<?php
			/*if($prodcount % $prodperrow ==0 || $prodcount == $per_page) {	//we've hit the number of products per row, keep them lined up in rows
				echo "<div class='clear notBelow910'></div>";
			}*/
		}
		?>
		</ul>
