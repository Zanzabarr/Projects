<?php 
// headerComponents must be defined before the include; it is used in the include as well as during instantiation of the header class.
$headerComponents = array();
$headerModule = 'news_items';
include('../../includes/headerClass.php');
include('includes/functions.php');

$pageInit = new headerClass($headerComponents, $headerModule);

// get news_items data
$news_items =  getnews_itemsData();

// -----------------------------------------------html------------------------------------

// head/header/sidebar
$pageResources ="
<link rel=\"stylesheet\" type=\"text/css\" href=\"".$_config['admin_url']."modules/news_items/style.css\" />
<script type=\"text/javascript\" src=\"".$_config['admin_url']."modules/news_items/js/news_items.js\"></script>
";
$pageInit->createPageTop($pageResources);
?>

<div class="page_container">
	<div id="h1"><h1>News Items</h1></div>
    <div id="info_container" class='news_itemsTable'>

		<?php 
		$selectedOpts = '';
		$selectednews_items = 'tabSel';
        
        echo '<div id="news_itemsnavhome">'; 
        
		include("includes/subnav.php"); 

        echo '</div>'; ?>

		<div class='clearFix' ></div> 

		 <?php 
		errors(); 
        if (count($news_items) > 0 ) :

		?>

		<div id='news_items-list'>
			<table cellspacing="0" cellpadding="0" style="border-top:1px solid #aaa; table-layout:fixed; width:600px;font-size:14px;">
				<tr>
                    <th width="50">IMAGE</th>
					<th width="275">NEWS ITEM TITLE</th>
					<th width="100">CONTRIBUTOR</th>
					<th width="50">STATUS</th>
					<th width="125">OPERATION</th>
				</tr>
            <?php echo buildnews_itemsMenu($news_items);; ?>    
			</table>

		</div> <!--end news_items-list -->
		<?php else : ?>
		<p style='text-align:center;width:100%'>There are no News Items yet. Why not click "Add News Item" to add your first one?</p>
		<?php endif; ?>

</div> <!--end infoContainer -->
 
<?php 
include($_config['admin_includes']."footer.php"); 
?>
