<?php 
// headerComponents must be defined before the include; it is used in the include as well as during instantiation of the header class.
$headerComponents = array();
$headerModule = 'blog';
include('../../includes/headerClass.php');
include('blog/functions.php');

$pageInit = new headerClass($headerComponents, $headerModule);

// get all categories
$categories = logged_query_assoc_array('SELECT * FROM `blog_cat`',null,0,array());
foreach($categories as $cat)
{
	$tmp_categories[$cat['id']] = $cat;
}
$categories = isset($tmp_categories) ? $tmp_categories :array();
global $categories;



function writeCategory($catVal)
{
	global $categories;
	$return = '';
	foreach ($catVal as $cat)
	{	
		if (array_key_exists($cat, $categories) )
		$return .= "{$categories[$cat]['title']} <br />";
	}
	return $return;
}

// -----------------------------------------------html------------------------------------

// head/header/sidebar
$pageResources ="
<link rel='stylesheet' type='text/css' href='style.css' />
<script type=\"text/javascript\" src=\"".$_config['admin_url']."modules/blog/js/blog.js\"></script>
";
$pageInit->createPageTop($pageResources);
?>

<div class="page_container">
	<div id="h1"><h1>Categories</h1></div>
    <div id="info_container" class='blogTable'>

		<?php 
		$selectedOpts = '';
		$selectedPosts = '';
		$selectedCats = 'tabSel';
		include("blog/subnav.php"); ?>

		 <?php 

		if (count($categories) > 0 ) :
		?>

		<div id='blogCats'>
			<table cellspacing="0" cellpadding="0" style="border-top:1px solid #aaa;">
				<tr>
					<th width="370" style="text-align:left;padding-left:90px;">TITLE</th>
					<th >STATUS</th>
					<th>Operation</th>
				</tr>
			</table>

			<div class='cat_grp'> 
				<?php	
				buildCatRows($categories)
				?>
			</div> <!--end cat_grp -->
		</div> <!--end blogCats -->
		
		<?php else : ?>
			<p style='text-align:center;width:100%'>There are no categories yet. Why not click "New Category" to start organizing your blog posts?</p>
		<?php endif; ?>
</div> <!--end infoContainer -->
 
<?php 
include($_config['admin_includes']."footer.php"); ?>