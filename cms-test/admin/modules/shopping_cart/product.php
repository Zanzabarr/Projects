<?php 
// headerComponents must be defined before the include; it is used in the include as well as during instantiation of the header class.
$headerComponents = array();
$headerModule = 'shopping_cart';
include('../../includes/headerClass.php');

$pageInit = new headerClass($headerComponents, $headerModule);

// access user data from headerClass
global $curUser;
$isAdmin = $curUser['admin'] == 'yes';

// initialize error messages
$message = array();//

$products = ecom_functions::get_product_overview('ORDER BY cat_name, prod.title');
// var_dump($products);

//handle view
if(isset($_POST['view'])) {
	//get the products and sort by view
	if($_POST['view_category'] != "All Categories") {
		foreach($products as $k=>$v) {
			if($v['cat_name'] != $_POST['view_category']) {
				unset($products[$k]);
				array_values($products);
			}
		}
	}
	elseif($_POST['view_collection'] != "All Collections") {
		foreach($products as $key=>$value) {
			if(isset($value['col_names']) && !empty($value['col_names'])) {
				foreach($value['col_names'] as $k=>$v) {
					if($v != $_POST['view_collection']) {
						unset($products[$key]);
						array_values($products);
					}
				}
			} else {
				unset($products[$key]);
				array_values($products);
			}
		}
	}
}

// 	Category Select
$val = isset($_POST['view_category']) ? $_POST['view_category'] : 'All Categories';
$input_menu_posn = new inputField( 'View Category', 'view_category');
$input_menu_posn->toolTip("Select the Category" );
$input_menu_posn->type('select');
$input_menu_posn->selected($val);
$input_menu_posn->arErr($message);
	
//		build the option set
$objCategory = new ecom_category();
$prodCategories = $objCategory->get_nested();
//		create  options
$input_menu_posn->option("All Categories","All Categories");
foreach ($prodCategories as $category)
{
	$input_menu_posn->option(  
		$category['spaced_name'], 
		$category['spaced_name'] 
	);
}

//	Collection Select
$prodCollections = ecom_functions::get_all_collections();
$val = isset($_POST['view_collection']) ? $_POST['view_collection'] : 'All Collections';
$input_collections = new inputField( 'View Collection', 'view_collection');
$input_collections->toolTip("Select the Collection");	
$input_collections->type('select');
$input_collections->selected( $val );

//build option set
$input_collections->option("All Collections", "All Collections", array('class' => ""));
foreach ($prodCollections as $co){
	$input_collections->option($co['title'], $co['title'], array('class' => "" ));
}

// -----------------------------------------------html------------------------------------

// head/header/sidebar
$pageResources ="
<link rel='stylesheet' type='text/css' href='styles.css' />
<script type=\"text/javascript\" src=\"".$_config['admin_url']."modules/shopping_cart/js/product.js\"></script>
";
$pageInit->createPageTop($pageResources);
?>

<div class="page_container">
	<div id="h1"><h1>Products</h1></div>
    <div id="info_container" class='blogTable'>
<?php 
// add subnav
$pg = 'product';	include("includes/subnav.php");

// create a banner if $message['banner'] exists
createBanner($message);
?>

	<div id='selectsContainer'>
		<div class='viewSelect'>
			<form id="viewform" method="post" action="">
				<input type='hidden' name='view' value='1' />
	<?php $input_menu_posn->createInputField();
		echo "</div><div class='viewSelect'>";
		  $input_collections->createInputField();
	?>
			</form>
		</div>
	</div>
		<div class='clear'></div>

<?php
if (count($products) > 0 ) :
?>

		<div id='productTable'>
			<table cellspacing="0" cellpadding="0" style="border-top:1px solid #aaa;">
				<tr>
					<th width="164" style="text-align:left;">TITLE</th>
					<th width="164" style="text-align:left;">Category</th>
					<th width="164" style="text-align:left;">Collections</th>
					<th>Operation</th>
				</tr>
			</table>
<?php
	foreach($products as $prodid => $product)
	{
		$status = $product['status'] ? 'Published' : 'Draft';
?> 
			<div class='menu_row'> 
			<span class='title'><?php echo $product['title']; ?></span>
			<span class="prodCount"><?php echo $product['cat_name']; ?></span>
			<span class="collectionNames tipLeft" title="<?php ecom_functions::showCollectionNames($products[$prodid]['col_names']); ?>">
				<?php echo isset($product['col_names'][0]) ? $product['col_names'][0]:''; echo isset($product['col_names'][1]) ? '&hellip;' : ''; ?>
			</span>
			<span class="op_group">

<?php
		// only the admin can make favourite or change active status
		if($isAdmin) :
			if($product['featured'] == 1){ 
?>
				<a class="changeFeatured" rel='<?php echo $prodid ; ?>' href="#" >
					<img src="img/icon-star.png" class='tipTop' rel='0' title='Make Product No Longer Featured' alt='Not Featured'>
				</a>
<?php	
			} else{
?>
				<a class="changeFeatured" rel='<?php echo $prodid ; ?>' href="#" >
					<img src="img/icon-darkstar.png" class='tipTop' rel='1' title='Make Featured Product' alt='Featured'>
				</a>
<?php		
			}
		
			if($product['status'] == 1){ 
?>
				<a class="changeActive" rel='<?php echo $prodid ; ?>' href="#" >
					<img src="img/check.png" class='tipTop' rel="0" title='Make Product Inactive' alt="Active" >
				</a>
<?php	
			} elseif (ecom_functions::canActivateProduct($products[$prodid])) {
?>
				<a class="changeActive" rel='<?php echo $prodid ; ?>' href="#" >
					<img src="img/dislike.png" class='tipTop' rel="1" title='Make Product Active' alt="Inactive" >
				</a>
<?php
			} else { // unable to make it active because data is incomplete: go to edit page instead
?>				
				<a href="edit_product.php?prodid=<?php echo $prodid;?>" >
					<img src="img/darkdislike.png" class='tipTop' title='Cannot Make Product Active' alt="Inactive" >
				</a>
<?php
			}
		else : // non admin view of status/featured	
			if($product['featured'] == 1){ ?>
				<img src="img/icon-star.png" class='tipTop' title='Featured Product' alt='Featured'>
<?php		} else{ ?>
				<img src="img/icon-darkstar.png" class='tipTop' title='Not a Featured Product' alt='Not Featured'>
<?php		
			}
			if($product['status'] == 1){ ?>
				<img src="img/check.png" class='tipTop' title='Active Product' alt="Active" >
<?php		} else{ ?>
				<img src="img/dislike.png" class='tipTop' title='Inactive Product' alt="Inactive" >
<?php		
			}
		endif;
		
		// only admin can edit published items
		if ($isAdmin || ! $product['status']) :
		
?>				
				<a href="edit_product.php?prodid=<?php echo $prodid;?>">
					<img class='tipTop' title='Edit this Product' src="../../images/edit.png" alt="Edit">
				</a>
<?php	else: ?>
				
				<img class='tipTop' title='Only Administrators can Edit Active products' src="../../images/darkedit.png" alt="Edit">
				
<?php 	endif; 
	
		// only admin can delete published items
		if ($isAdmin || ! $product['status']) :
?>
				<a  href="#" class="deleteProduct" rel="<?php echo $prodid; ?>">
					<img class='tipTop' title='Permanently remove this Product' src="../../images/delete.png" alt="Delete">
				</a>
<?php 	else : ?>
					<img class='tipTop' title='Only Administrators can Delete Active products' src="../../images/darkdelete.png" alt="Delete">
<?php 	endif; ?>
				<div class='clearFix'></div>
			</span>
			
		</div>
		<div class='clearFix'></div>
<?php
	} ?>
			

			<?php // echo display_array($pages);
			// echo buildBlogMenu($pages, $admin) 
		?>
		</div> <!--end productTable -->
		<?php  else : ?>
		<p style='text-align:center;width:100%'>There are no products yet. Why not click "New Product" to create the first one?</p>
		<?php  endif; ?>

</div> <!--end infoContainer -->
 
<?php // echo display_array($pages);
include($_config['admin_includes']."footer.php"); ?>