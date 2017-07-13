<?php
// passed from shopping_cart/frontend/head: 
//	$searchData			array of search parameters for get_products function
//  $searchError		contains error code if an error occured while parsing the uri, false otherwise
//	$heading['name']	If a category or collection was specified, the name is stored here
//  $heading['desc']		likewise for the description

/*
echo 'search data:';
print_r($searchData);
echo 'search error:';
print_r($searchError);
*/
//array pagination class
include($_config['admin_path']."modules/shopping_cart/classes/pagination.php");
// get all products
if($searchError) {
	$products = ecom_functions::get_products();
} else {
	//if(isset($_POST['filter'])) 
	$products = ecom_functions::get_products($searchData);
}

/*	ENABLE/DISABLE PRODUCTS PAGE SORT/FILTER FEATURE	*/
$useFilter = true; //false to disable
/***/

/* TODO: complete products pagination */

$per_page = $_config['shopping_cart']['preferences']['prod_per_page'];

/* INCOMPLETE - add category and collection selection 
$cats = logged_query_assoc_array("SELECT * FROM ecom_category_data");
$colls = logged_query_assoc_array("SELECT * FROM ecom_collection");
$categoryTitle = "";
foreach($cats as $cat) {
	if(isset($uri[2]) && $cat['url']==$uri[2]) $categoryTitle = $cat['name'];
}
foreach($colls as $coll) {
	if(isset($uri[2]) && $coll['url']==$uri[2]) $categoryTitle = $coll['title'];
}
if($categoryTitle == "") $categoryTitle = "By Category";

echo "<div id='ecom-searchbar'>";
	echo "<div id='ecom-cat-toggle'>$categoryTitle</div>";
	echo "<ul id='catlist'>";
	foreach($cats as $cat) {
		echo "<li><a href='{$_config['site_path']}shopping/category/{$cat['url']}'>{$cat['name']}</a></li>";
	}
	echo "<li style='height:5px; margin-top:5px; border-top:1px solid #000;'>&nbsp;</li>";
	foreach($colls as $coll) {
		echo "<li><a href='{$_config['site_path']}shopping/collection/{$coll['url']}'>{$coll['title']}</a></li>";
	}
	echo "</ul>";
echo "</div><!-- end ecom-searchbar -->";

 end category and collection selection */
 
echo "<div class='clear' style='height:2em;'></div>";

if(isset($uri[1])) {
	switch($uri[1]) {
		case "category":
			if(isset($uri[2])) {
				$getid = logged_query("SELECT id FROM ecom_category_data WHERE url = '{$uri[2]}'",0,array());
				$catid = $getid[0]['id'];
			} else {
				$jumpfile = "edit_preferences.php";
				$tbl = "ecom_preferences";
				$idval = 1;
				break;
			}
			$jumpfile = "edit_category.php?cat_id={$catid}";
			$tbl = "ecom_category_data";
			$idval = $catid;
			break;
		case "collection":
			if(isset($uri[2])) {
				$getid = logged_query("SELECT id FROM ecom_collection WHERE url = '{$uri[2]}'",0,array());
				$colid = $getid[0]['id'];
			} else {
				$jumpfile = "edit_preferences.php";
				$tbl = "ecom_preferences";
				$idval = 1;
				break;
			}
			$jumpfile = "edit_collection.php?colid={$colid}";
			$tbl = "ecom_collection";
			$idval = $colid;
			break;
		default:
			$jumpfile = "edit_preferences.php";
			$tbl = "ecom_preferences";
			$idval = 1;
			break;
	}
} else {
	$jumpfile = "edit_preferences.php";
	$tbl = "ecom_preferences";
	$idval = 1;
}

$editable = array(
	'editable_class' => 'inlineStandard',
	'attributes' => array(
		'data-jump' => "admin/modules/shopping_cart/{$jumpfile}#content",
		'data-jump-type' => 'back'
	),
	'secure_data' => array(
		'table' => $tbl,				// req for save
		'id-field' => "id",				// req for save && upload
		'id-val' => $idval,	// req for save && upload
		'field' => "desc"			// req for save
	)
);

// build the heading (if applicable)
if(! $searchError && ($heading['name'] != '' || $heading['desc'] != '') )
{ ?>
	<div id="heading-container">
		<div class="clear" style="height:1em;"></div>
		<?php /*
		<h1><?php echo $heading['name']; ?></h1>
		<div><?php display_content($heading['desc'],$editable); ?></div> */ ?>
	</div> <!-- end heading container -->
<?php	

} elseif ( isset($_POST['search']) ) { ?>
	<div id="heading-container">
		<h2>Search Results</h2>
		<?php if($_POST['search']) echo "<div>Showing results for '{$_POST['search']}'</div>"; ?>
	</div> <!-- end heading container -->
<?php
}

if($searchError) // need an error message here
{ 
	if (in_array('product_not_found', $searchError))
	{ ?>
		<div>Oops, we could not find that item. Here is our complete product line instead.</div>
	<?php
	}
	else
	{
?>
	<div>Oops, we could not find those items. Here is our complete product line instead.</div>
<?php
	}
}
if(count($products) == 0) // need an empty search message here
{ ?>
	<h2 style="margin-top:1em;">RESULTS</h2>
	<div>We're sorry, no items could be found.</div>
<?php
}
?>
<div class="container_wrap">
<?php
$uriString = rtrim(implode('/',$uri),'/');
if (count($products)) {
	// Create the pagination object
	$pagination = new pagination($products, (isset($_GET['pg']) ? $_GET['pg'] : 1), $per_page, $uriString);
	// Decide if the first and last links should show
	$pagination->setShowFirstAndLast(false);
	// You can overwrite the default seperator
	$pagination->setMainSeperator(' ');
	// Parse through the pagination class
	$productPages = $pagination->getResults();
	// If we have items 
	if (count($productPages) != 0) {
		// make the filters dropdown if enabled	- SEE NEAR TOP OF PAGE FOR $useFilter VARIABLE
		if($useFilter) {
			$filters = array('Featured','Price: Low','Price: High');
			$boxAction = implode("/",$uri);
			$filterBox = "<form id='sortform' action='{$boxAction}' method='post' style='float:left;'><select id='filterbox' name='filter'>";
			foreach($filters as $k=>$v) {
				$selfil = (isset($_REQUEST['filter']) && $_REQUEST['filter']==$k) ? " selected = 'selected'" : "";
				$filterBox .= "<option value='{$k}'{$selfil}>{$v}</option>";
			}
			$filterBox .= "</select></form>";
		} else {
			$filterBox = "";
		}
		// Create the page numbers
		$pageNumbers = '<div class="numbers">'.$pagination->getLinks($_REQUEST).'<div class="clear"></div></div>';
		// build control bar
		echo "<div id='pageBar'><small style='font-weight:600;float:left;margin-right:1em;'>SORT BY</small> {$filterBox}{$pageNumbers}<div class='clear'></div></div><div class='clear1em'></div>";
		
		$prodperrow = 3;	//how many products will be listed per row on the page?
		$numblanks = $prodperrow - 2; //number blanks needed to fill the row at the end
		$prodcount = 0;		//keep track
		// Loop through all the items in the array
		foreach ($productPages as $productArray) {
			$prodcount++;
		?>
			<div class="itemcontainer">
			<?php
				// display the image: enable or disable text descriptions based on shopping_cart config 
				if(isset($productArray['images'][0])) $productArray['images'][0]['url'] = $productArray['url'];
				if(isset($productArray['images'][0]) && $productArray['images'][0]['posn'] ) : ecom_functions::productSingleImage("singleImageWrap", $productArray['images'][0],'mini', $_config['ecom_img_text_desc']);
				else : ?>
				<div class="singleImage">
					<a href="<?php echo $_config['path']['shopping_cart']; ?>products/<?php echo $productArray['url']; ?>"><img src="admin/images/no_image.jpg" alt="No Image Available" ></a>
				</div>
				<?php 
				endif;
				$num_decimals = 2; //(intval($productArray['price']) == $productArray['price']) ? 0 :2;
				if($_config['shopping_cart']['preferences']['includePricing']==1 && $productArray['price'] > 0) {
					$priceClause = $productArray['price'] ?  " \$" . number_format($productArray['price'],$num_decimals) : "";
					$saleprice = ($productArray['sale']>0) ? "SALE \$" .number_format($productArray['sale'],$num_decimals) : "";
					$strike = $productArray['price'] && $productArray['sale']>0 ? " strikethru" : "";
					$moreClause = "<a class='moreinfo right' href='{$_config['path']['shopping_cart']}products/{$productArray['url']}'>Read More</a>";
				} else {
					$priceClause = "<a href='{$_config['path']['shopping_cart']}products/{$productArray['url']}' title='Read More'>{$_config['shopping_cart']['preferences']['altPriceText']}</a>";
					//$priceClause = $_config['shopping_cart']['preferences']['altPriceText'];
					$saleprice = "";
					$strike = "";
					$moreClause = "";
				}
			?>
				<div class="clear"></div>
				<div class="infocont">
					<div class="itemtitle"><span class="itemspan"><a href="<?php echo $_config['path']['shopping_cart']; ?>products/<?php echo $productArray['url']; ?>"><?php echo $productArray['title']; ?></a></span></div>
					
					<div class="itemtitle itemdesc" style="line-height:1;"><?php echo substr(strip_tags(htmlspecialchars_decode($productArray['short_desc'])),0,50); ?>...</div>
					
					<!-- price [, saleprice ] -->
					<div class="priceBGcover">
						<div class="itemtitle">
							<span class="pricespan <?php echo $strike; ?>"><?php echo $priceClause; ?></span>
							<span class="pricespan red"><?php echo $saleprice; ?></span>
							<?php echo $moreClause; ?>
						</div>
					</div>
				</div>
			</div>
		<?php
		}
		for($n = 0; $n < $numblanks; $n++) {
			echo "<div class='blankcontainer'></div>";
		}
		echo "<div class='stretch'></div>";
	}
} ?>

<?php
// build control bar
		echo isset($pageNumbers) ? "<div id='bottomcontrols'><div id='pageBar'>{$pageNumbers}<div class='clear'></div></div></div>" : "";
?>
</div>
<div class="clear"></div>
<script>
function Go(url){
	if(url=="") {
		return;
	}else{
		location=url;
	}
}
$(document).ready( function() {
	var containers = $('.itemcontainer').length;
	if( containers <= 6) $("#bottomcontrols").css('display','none');
});
</script>
<?php /*	SCRIPT FOR CATEGORY AND COLLECTION SELECTION
<script>
$(document).ready( function() {
	$('#ecom-cat-toggle').click( function() {
		$(this).toggleClass('toggled-on');
		$('#catlist').toggleClass('toggled-on');
	});
	$('#ecom-coll-toggle').click( function() {
		$(this).toggleClass('toggled-on');
		$('#colllist').toggleClass('toggled-on');
	});
});
</script>
*/ ?>