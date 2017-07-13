<?php
// passed from shopping_cart/frontend/head: 
//	$searchData			array of search parameters for get_products function
//  $searchError		contains error code if an error occured while parsing the uri, false otherwise
//	$heading['name']	If a category or collection was specified, the name is stored here
//  $heading['desc']		likewise for the description

// get the images
$itemImages = $item_data['images'];
// check for sale item
if(isset($item_data['sale']) && $item_data['sale']>0) {
	$num_decimals = (intval($item_data['sale']) == $item_data['sale']) ? 0 :2;
	$h2Price = ($item_data['sale'] && $_config['shopping_cart']['preferences']['includePricing']==1) ?  "<span> $" . number_format($item_data['sale'],$num_decimals) . "</span>" : "";
} else {
	$num_decimals = (intval($item_data['price']) == $item_data['price']) ? 0 :2;
	$h2Price = ($item_data['price'] && $_config['shopping_cart']['preferences']['includePricing']==1) ?  "<span> $" . number_format($item_data['price'],$num_decimals) . "</span>" : "";
}
?>
<style>
.jsGallery .galleryNavWrap .galleryNavInner {
	border:none;
}
.jsGallery .galleryNavWrap li img {
	width:80%;
}
.jsGallery .galleryNavWrap .gallery-previous a, .jsGallery .galleryNavWrap .gallery-next a {
	opacity:0.7;
}
.jsGallery .gallery-previous img, .jsGallery .gallery-next img {
	margin-top:2.7em;
}
iframe.twitter-share-button {
	position:relative;
}
#socialbuttons a {
	top:0;
}
.fb_iframe_widget {
	top:-.3em;
}
</style>
<h1 class="mobileOnly" style="font-weight:800;"><?php echo $item_data['title']; // . $h2Price;?></h1>
<section id="socialbuttons" class="mobileOnly">
		<?php //facebook ?>
		<script src="http://connect.facebook.net/en_US/all.js#xfbml=1"></script><fb:like href="<?php echo rawurlencode('http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']); ?>" layout="button_count" share="false" show_faces="false" width=”450?></fb:like>
		
		<?php //twitter 
			$tweet = $item_data['title']." at ".$_config['site_path']."shopping/products/".$item_data['url'];
		?>
		<a href="https://twitter.com/share" class="twitter-share-button" data-text="<?php echo $tweet; ?>" data-lang="en" data-size="medium" data-count="none" data-url="<?php echo rawurlencode('http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']); ?>">Tweet</a>
		<script>!function(d,s,id){var js,fjs=d.getElementsByTagName(s)[0];if(!d.getElementById(id)){js=d.createElement(s);js.id=id;js.src="https://platform.twitter.com/widgets.js";fjs.parentNode.insertBefore(js,fjs);}}(document,"script","twitter-wjs");</script>
		
		<?php //pinterest ?>
		<a href="//www.pinterest.com/pin/create/button/?url=<?php echo rawurlencode('http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']); ?>&media=<?php echo rawurlencode('http://' . $_SERVER['HTTP_HOST'] . 'uploads/ecom/mini/'. $itemImages[0]['name']); ?>&description=Next%20stop%3A%20Pinterest" data-pin-do="buttonPin" data-pin-config="none"><img src="//assets.pinterest.com/images/pidgets/pinit_fg_en_rect_gray_20.png" /></a>
		<!-- Please call pinit.js only once per page -->
		<script type="text/javascript" async src="//assets.pinterest.com/js/pinit.js"></script>
	</section>
<div class="jsGallery miniGallery">
<?php
if(count($itemImages)>1) {
    $galleryoptions = array(
        'galleryId'         => $item_data['id'] ,
        'leftArrow' => '<img src="admin/images/arrow-left-on.png">',
        'rightArrow' => '<img src="admin/images/arrow-right-on.png">',
        'leftThumbArrow' => '<img src="admin/images/gray-previous.png">',
        'rightThumbArrow' => '<img src="admin/images/gray-next.png">',
        'displaySizeFolder' => 'fullsize',
        'placeHolder'        => 'admin/images/resp_gallery_placeholder.gif',
        'cycleSpeed'        => 0
    );
} else {
    $galleryoptions = array(
        'galleryId' => $item_data['id'],
        'noScrollArrows' => true,
        'displaySizeFolder' => 'fullsize',
        'placeHolder'        => 'admin/images/resp_gallery_placeholder.gif',
        'cycleSpeed'        => 0
    );
}

$gallery = new displayGallery(
    $itemImages,
    'uploads/ecom/',
    $galleryoptions
);
	
    $gallery->buildGallery();
    if(isset($_config['gallery_img_text_desc']) && $_config['gallery_img_text_desc'])$gallery->buildCaption();
    $gallery->buildNav();
	if(isset($_config['gallery_img_html_desc']) && $_config['gallery_img_html_desc']) $gallery->buildHTMLCaption();
    //$gallery->buildStackedNav();
	
	//social
	?>
	<section id="socialbuttons" class="notMobileOnly">
		<?php //facebook ?>
		<script src="http://connect.facebook.net/en_US/all.js#xfbml=1"></script><fb:like href="<?php echo rawurlencode('http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']); ?>" layout="button_count" share="false" show_faces="false" width=”450?></fb:like>
		
		<?php //twitter 
			$tweet = $item_data['title']." at ".$_config['site_path']."shopping/products/".$item_data['url'];
		?>
		<a href="https://twitter.com/share" class="twitter-share-button" data-text="<?php echo $tweet; ?>" data-lang="en" data-size="medium" data-count="none" data-url="<?php echo rawurlencode('http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']); ?>">Tweet</a>
		<script>!function(d,s,id){var js,fjs=d.getElementsByTagName(s)[0];if(!d.getElementById(id)){js=d.createElement(s);js.id=id;js.src="https://platform.twitter.com/widgets.js";fjs.parentNode.insertBefore(js,fjs);}}(document,"script","twitter-wjs");</script>
		
		<?php //pinterest ?>
		<a href="//www.pinterest.com/pin/create/button/?url=<?php echo rawurlencode('http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']); ?>&media=<?php echo rawurlencode('http://' . $_SERVER['HTTP_HOST'] . 'uploads/ecom/mini/'. $itemImages[0]['name']); ?>&description=Next%20stop%3A%20Pinterest" data-pin-do="buttonPin" data-pin-config="none"><img src="//assets.pinterest.com/images/pidgets/pinit_fg_en_rect_gray_20.png" /></a>
		<!-- Please call pinit.js only once per page -->
		<script type="text/javascript" async src="//assets.pinterest.com/js/pinit.js"></script>
	</section>
	
<?php

$jumpfile = "edit_product.php?prodid={$item_data['id']}";
	
$editable = array(
	'editable_class' => 'inlineStandard',
	'attributes' => array(
		'id' => "ecom_description",
		'data-jump' => "admin/modules/shopping_cart/{$jumpfile}#content",
		'data-jump-type' => 'back'
	),
	'secure_data' => array(
		'table' => 'ecom_product',				// req for save
		'id-field' => "id",				// req for save && upload
		'id-val' => $item_data['id'],	// req for save && upload
		'field' => "desc"			// req for save
	)
);

$editspecs = array(
	'editable_class' => 'inlineStandard',
	'attributes' => array(
		'id' => "ecom_specs",
		'data-jump' => "admin/modules/shopping_cart/{$jumpfile}#specs",
		'data-jump-type' => 'back'
	),
	'secure_data' => array(
		'table' => 'ecom_product',				// req for save
		'id-field' => "id",				// req for save && upload
		'id-val' => $item_data['id'],	// req for save && upload
		'field' => "specs"			// req for save
	)
);
	
?>
</div>
<div id="proddetails">
<h1 class="notMobileOnly" style="font-weight:800;"><?php echo $item_data['title']; // . $h2Price;?></h1>
<?php do_pricing($item_data); ?>
<hr class='lightrule notMobileOnly'/>
<?php if(isset($item_data['part_number']) && $item_data['part_number'] != "") { ?>
	<p>Part Number:&nbsp;&nbsp;<?php echo $item_data['part_number']; ?></p>
<?php } ?>
<strong>QUICKVIEW:</strong><br />
<p style="float:left;display:inline-block;font-size:.9em;"><?php echo display_content($item_data['short_desc']); ?></p>
<hr class='lightrule clear'/>
<?php if($item_data['price'] > 0) { ?>
<form action="<?php echo $_config['cartName'] ?>/cart" method="post" enctype="application/x-www-form-urlencoded" name="addtocart" id="addtocart"><div id='optionsContainer'>
				<?php 
				//get and display options if any exist
				$product_options = get_options($item_data['id']);

				//build the option selects
				foreach ($product_options as $opt_name => $options)
				{ 
					echo "<div class='selectContainer'><select name='option[{$opt_name}]' title='{$opt_name}' class='product_options'>";
					//echo "<option value='' selected='selected'>{$opt_name}</option>";
					foreach ($options as $option => $opt_data)
					{
						echo"<option value=\"{$option}\" data-price=\"{$opt_data['price']}\" data-weight=\"{$opt_data['weight']}\" >{$option}</option>";
					}
					echo "</select></div>";
				}
				?>
				<span class="optionselect" style="margin-right:0;position:relative;margin-top:.6em;margin-bottom:1em;">
					<label for="quantity" style="width:auto;">QTY</label><input id="quantity" type="number" step="1" name="<?php echo $item_data['url']; ?>" class="addtocart" value="1" style="margin-top:0;float:right;"  />
				</span>
			</div><!-- end optionsContainer -->
			
			<?php // comment the span.optionselect above and uncomment the one below to bring the Quantity field to a line of its own below the options 
			
			/*<div class="clear"></div>
			<span class="optionselect" style="margin-right:0;position:relative;margin-top:.5em;margin-bottom:1em;">
				<label for="quantity" style="width:auto;">QTY</label><input id="quantity" type="text" name="<?php echo $item_data['url']; ?>" class="addtocart" value="1" style="width:2em;margin-top:0;float:right;"  />
			</span> <!-- end quatity -->*/ ?>
			<div class="clear"></div>
			<hr class='lightrule'/>
	<?php	if($_config['shopping_cart']['preferences']['hasCart']==1) { ?>
				<a  href="#" class="addbutton" style="float:left;">ADD TO CART</a>
	<?php	} else { ?>
				<a href="shopping/" class="shoppingbutton" style="float:left;">Back to Products</a>
	<?php	} ?>
        </form>
	<?php } else {
		echo "<p>This item not available online.</p>";
	}
	?>
	<div class="clear"></div>
		<hr class='lightrule'/>
			<a rel="nofollow" href="external/sendtofriend.php?ref=<?php echo $uri[2]; ?>" class="fmail" title="Email This to a Friend">+ Email to a Friend</a>
		<div class="clear"></div>
		<hr class='lightrule'/>
</div><!-- end #proddetails -->
<div class="clear" style="height:1em;"></div>
<div id="fullprod">
	<ul id='prodtabs'>
		<li class="prodtab tabselect" rel="description"><div>Product Description</div></li>
		<?php if(isset($item_data['specs']) && $item_data['specs']!=="") { ?>
			<li class="prodtab" rel="specs"><div>Technical Specifications</div></li>
		<?php } ?>
		<?php /*<li class="prodtab" rel="reviews">Reviews<span class='tabplus'>+</span></li>
		<li class="prodtab" rel="tags">Product Tags<span class='tabplus'>+</span></li>*/ ?>
	</ul>
	<div class="clear"></div>
	<section class="prodsection" id="description">
		<?php echo display_content($item_data['desc'],$editable); ?>
	</section>
	<?php if(isset($item_data['specs']) && $item_data['specs']!=="") { ?>
		<section class="prodsection" id="specs">
		<?php echo display_content($item_data['specs'],$editspecs); ?>
		</section>
	<?php } ?>
	
	<?php /*<section class="prodsection" id="reviews">
	</section>
	<section class="prodsection" id="tags">
	</section>*/ ?>
</div><!-- end #fullprod -->
<script>
$(document).ready(function(){	
    if (!$.browser.opera) {
        $('select.product_options').each(function(){
            var title = $(this).attr('title');
            //if( $('option:selected', this).val() != ''  ) title = $('option:selected',this).text();
            $(this)
                .css({'z-index':10,'opacity':0,'-khtml-appearance':'none'})
                .after('<span class="optionselect">' + title + '<img src="<?php echo $_config['admin_url']."modules/shopping_cart/images/down-arrow.png"; ?>" /></span>')
                .change(function(){
                    val = $('option:selected',this).text() + '<img src="<?php echo $_config['admin_url']."modules/shopping_cart/images/down-arrow.png"; ?>" />';
                    $(this).next().html(val);
                })
        });

    };
	
	$("a.fmail").each(function(){
		var attr = $(this).attr('href');

		// For some browsers, `attr` is undefined; for others,
		// `attr` is false.  Check for both.
		if (typeof attr !== 'undefined' && attr !== false) {
			$(this).fancybox({
				'transitionIn'	:	'elastic',
				'transitionOut'	:	'fade',
				'speedIn'		:	600, 
				'speedOut'		:	400,
				'autoDimensions': false,
				'width': '30%',
				'height': '35%',
				'titlePosition': 'outside'
			});
		}
	});
	
	$('#fullprod .prodsection').each( function() {
		if($(this).attr('id')=="description") {
			$(this).show();
		} else {
			$(this).hide();
		}
	});
	
	$('li.prodtab').click( function() {
		var section = $(this).attr('rel');
		$(this).addClass('clicked');
		$('li.prodtab').each( function() {
			if(!$(this).hasClass('clicked')) {
				$(this).removeClass('tabselect');
			} else {
				$(this).addClass('tabselect');
				$(this).removeClass('clicked');
			}
		});
		$('span.tabplus').each( function() {
			if($(this).parent('li').hasClass('tabselect')) {
				$(this).html('-');
			} else {
				$(this).html('+');
			}
		});
		$('#fullprod .prodsection').each( function() {
			if($(this).attr('id')==section) {
				$(this).slideDown();
			} else {
				$(this).hide();
			}
		});
	});
});
</script>
<?php

function do_pricing($item_data)
{
	global $_config;
	if($_config['shopping_cart']['preferences']['includePricing']==1) {
		if($item_data['sale'] > 0) {
			echo "<p><span id='curPrice1' data-base-price='{$item_data['sale']}' data-low='{$item_data['sale']}' data-high='{$item_data['q1']}' class='price'>\$ {$item_data['sale']}</span><span class='saletext'>SALE PRICE</span></p>";
		}
		if($item_data['price'] > 0 && $item_data['sale']<=0)
		{
			echo "<p>";
			if($item_data['q2'] != '99999999')
			{
				// do the range
				echo "<span class='range'>1 ";
				if ($item_data['q2'] > 1) echo "to {$item_data['q2']}";
				echo "</span>@ ";
			}
			echo "<span id='curPrice1' data-base-price='{$item_data['price']}' data-low='{$item_data['q1']}' data-high='{$item_data['q2']}' class='price'>\${$item_data['price']}</span>";
			if($item_data['q2'] != '1') echo ' each';
			echo "</p>";
		}
		
		if($item_data['price2'] > 0 && $item_data['sale']<=0)
		{
			echo "<p>";

			// do the range
			echo "<span class='range'>{$item_data['q3']}";
			if ($item_data['q3'] == $item_data['q4']) echo '</span>@ ';
			elseif ($item_data['q4'] == '99999999') echo '+</span>@ ';
			else {
				echo " to {$item_data['q4']} </span>@ ";
			}

			echo "<span id='curPrice2' data-base-price='{$item_data['price2']}' data-low='{$item_data['q3']}' data-high='{$item_data['q4']}' class='price'>\${$item_data['price2']}</span>";
			echo ' each';
			echo "</p>";
		}
		
		if($item_data['price3'] > 0 && $item_data['sale']<=0)
		{
			echo "<p>";

			// do the range
			echo "<span class='range'>{$item_data['q5']}+</span>@ ";


			echo "<span id='curPrice3' data-base-price='{$item_data['price3']}' data-low='{$item_data['q5']}' data-high='{$item_data['q6']}' class='price'>\${$item_data['price3']}</span>";
			echo ' each';
			echo "</p>";
		}
	}
	

	else {
		echo "<p>";
		$alt_text = $_config['shopping_cart']['preferences']['altPriceText'];
		echo "<span>{$alt_text}</span>";
		echo "</p>";
	}
}




function get_options($id)
{
	if(! is_pos_int($id)) return array();
	
	$result = logged_query_assoc_array("
		SELECT * FROM `ecom_product_options` WHERE `prod_id`={$id} ORDER BY `id` ASC
	");
	
	if(!$result || ! count($result) ) return array();

	$ar_opt_data = array();
	foreach($result as $opt_data)
	{
		$ar_opt_data[$opt_data['opt_name']][$opt_data['option']] = array(
			'price' => $opt_data['price'],
			'weight' => $opt_data['weight']
		);
	}
	return $ar_opt_data;
}
?>