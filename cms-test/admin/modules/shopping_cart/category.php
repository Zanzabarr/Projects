<?php 
// headerComponents must be defined before the include; it is used in the include as well as during instantiation of the header class.
$headerComponents = array();
$headerModule = 'shopping_cart';
include('../../includes/headerClass.php');

// must have admin privileges to access this page
if ($_SESSION['user']['admin'] != 'yes')
{
	header( "Location: " . $_config['admin_url'] . "modules/{$headerModule}/index.php" );
	exit;
}

$pageInit = new headerClass($headerComponents, $headerModule);

// initialize category object
$objCategory = new ecom_category();

// initialize error messages
$message = array();

// do delete
if (isset($_GET['delete'] ) )
{
	$dirtyId = $_GET['delete'];
	$delcat = $_GET['cat'];
	$numDeleted = $objCategory->delete_shift_up( $dirtyId );
	
	if ($numDeleted )
	{
		// get related page and make draft, change slug to '--slug'
		$relatedPage = logged_query("select id, slug from pages where id > 0 && status = 1 && slug = :slug LIMIT 1",0,array(":slug" => "shopping/category/{$delcat}"));
		
		if(!empty($relatedPage)) {
			if(substr($relatedPage[0]['slug'],0,1) != "--") {
				$updatePage = logged_query("UPDATE pages SET status = 0, slug = '--{$relatedPage[0]['slug']}' WHERE id = {$relatedPage[0]['id']}",0,array());
			}
		}
		
		// success message
		$message['banner'] = array ('heading' => "Category Deleted", 'type' => "success");
		
	} else {
		$message['banner'] = array ('heading' => "Error Deleting Category", 'message' => "could not find Category", 'type' => 'error');
	}
}

// get all categories
$categories = $objCategory ->get_full_cat_data();

global $categories;
// -----------------------------------------------html------------------------------------

// head/header/sidebar
$pageResources ="
<link rel='stylesheet' type='text/css' href='styles.css' />
<script type=\"text/javascript\" src=\"".$_config['admin_url']."modules/shopping_cart/js/category.js\"></script>
";
$pageInit->createPageTop($pageResources);
?>

<div class="page_container">
	<div id="h1"><h1>Categories</h1></div>
    <div id="info_container" class='blogTable'>
	<?php 
	// add subnav
	$pg = 'category';	include("includes/subnav.php");
	
	// create a banner if $message['banner'] exists
	createBanner($message); 
	?>
		
		

		 <?php 
		//errors(); 
		if (count($categories) > 0 ) :
		?>
			
		<div id='categoryTable'>
			<table cellspacing="0" cellpadding="0" style="border-top:1px solid #aaa;">
				<tr>
					<th width="200" style="text-align:left;">TITLE</th>
					<th width="230">Products in Category</th>
					<th >STATUS</th>
					<th>Operation</th>
				</tr>
			</table>
				<?php
					foreach($categories as $category)
	{
		$status = $category['status'] ? 'Published' : 'Draft';
?> 
			<div class='menu_row'> 
			<span class='title'><?php echo $category['spaced_name']; ?></span>
			<span class="prodCount"><?php echo $category['product_count']; ?></span>
			<span class="status"><?php echo $status; ?></span>
			<span class="op_group">

				<a href="<?php echo $_config['admin_url']; ?>modules/shopping_cart/edit_category.php?cat_id=<?php echo $category['id'];?>">
					<img class='tipTop' title='Edit this Category' src="../../images/edit.png" alt="Edit">
				</a>
				<a  href="<?php echo $_config['admin_url']; ?>modules/shopping_cart/category.php?delete=<?php echo $category['id'];?>&cat=<?php echo $category['url']; ?>" class="deleteCategory" rel="<?php echo $category['id']; ?>">
					<img class='tipTop' title='Permanently remove this category. <br /> note: related Products will not be lost.' src="../../images/delete.png" alt="Delete">
				</a>
				<div class='clearFix'></div>
			</span>
			
		</div>
		<div class='clearFix'></div>
<?php
	} ?>
			

			<?php // echo display_array($pages);
			// echo buildBlogMenu($pages, $admin) 
		?>
		</div> <!--end categoryTable -->
		<?php  else : ?>
		<p style='text-align:center;width:100%'>There are no categories yet. Why not click "New Category" to create the first one?</p>
		<?php  endif; ?>

</div> <!--end infoContainer -->
 
<?php // echo display_array($pages);
include($_config['admin_includes']."footer.php"); ?>