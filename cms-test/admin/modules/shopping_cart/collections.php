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
$message = array();

// get all collections
$collections = ecom_functions::get_col_data();
global $collections;

// -----------------------------------------------html------------------------------------

// head/header/sidebar
$pageResources ="
<link rel='stylesheet' type='text/css' href='styles.css' />
<script type=\"text/javascript\" src=\"".$_config['admin_url']."modules/shopping_cart/js/collections.js\"></script>
";
$pageInit->createPageTop($pageResources);
?>

<div class="page_container">
	<div id="h1"><h1>Collections</h1></div>
    <div id="info_container" class='blogTable'>
	<?php 
	// add subnav
	$pg = 'collection';	include("includes/subnav.php");

	// create a banner if $message['banner'] exists
	createBanner($message);	
	?>
		
		

		 <?php 
		//errors(); 
		if (count($collections) > 0 ) :
		?>
			
		<div id='collectionTable'>
			<table cellspacing="0" cellpadding="0" style="border-top:1px solid #aaa;">
				<tr>
					<th width="200" style="text-align:left;">TITLE</th>
					<th width="230">Products in Collection</th>
					<th >STATUS</th>
					<th>Operation</th>
				</tr>
			</table>
				<?php
					foreach($collections as $collection)
	{
		$status = $collection['status'] ? 'Published' : 'Draft';
?> 
			<div class='menu_row'> 
			<span class='title'><?php echo $collection['title']; ?></span>
			<span class="prodCount"><?php echo $collection['product_count']; ?></span>
			<span class="status"><?php echo $status; ?></span>
			<span class="op_group">
			<!--	<a target='_blank' href='<?php echo $_config['site_path']; ?>blog/about/collection/<?php echo $collection['url'];?>' >
TO DO				<img class='tipTop' title='See it as it will appear live.' src="../../images/view_icon.png" alt="View">
				</a>
			-->	
				<a href="<?php echo $_config['admin_url']; ?>modules/shopping_cart/edit_collection.php?colid=<?php echo $collection['id'];?>">
					<img class='tipTop' title='Edit this Collection' src="../../images/edit.png" alt="Edit">
				</a>
				<a  href="#" class="deleteCollection" rel="<?php echo $collection['id']; ?>" data-url="<?php echo $collection['url']; ?>">
					<img class='tipTop' title='Permanently remove this collection. <br /> note: related Products will not be lost.' src="../../images/delete.png" alt="Delete">
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
		</div> <!--end collectionTable -->
		<?php  else : ?>
		<p style='text-align:center;width:100%'>There are no Collections yet. Why not click "New Collection" to create the first one?</p>
		<?php  endif; ?>

</div> <!--end infoContainer -->
 
<?php // echo display_array($pages);
include($_config['admin_includes']."footer.php"); ?>