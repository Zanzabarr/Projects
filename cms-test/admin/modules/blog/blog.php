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

// get admin info
$admin = getBlogOptions();

global $admin;

// get page data
$pages =  getPageData($admin);

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
	<div id="h1"><h1>Posts</h1></div>
    <div id="info_container" class='blogTable'>

		<?php 
		$selectedOpts = '';
		$selectedPosts = 'tabSel';
		$selectedCats = '';
		include("blog/subnav.php"); ?>



		<div class='clearFix' ></div> 
		

		 <?php 
		errors(); 
		if (count($pages) >0 ) :
		?>
			
		<div id='plogPosts'>
			<table cellspacing="0" cellpadding="0" style="border-top:1px solid #aaa;">
				<tr>
					<th width="200">TITLE</th>
					<th width="230">Categories</th>
					<th >STATUS</th>
					<th>Operation</th>
				</tr>
			</table>

			<?php // echo display_array($pages);
			echo buildBlogMenu($pages, $admin) 
		?>
		</div> <!--end blogPosts -->
		<?php else : ?>
		<p style='text-align:center;width:100%'>There are no blog posts yet. Why not click "New Blog Post" to write your first one?</p>
		<?php endif; ?>

</div> <!--end infoContainer -->
 
<?php // echo display_array($pages);
include($_config['admin_includes']."footer.php"); ?>