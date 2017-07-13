<?php 
// headerComponents must be defined before the include; it is used in the include as well as during instantiation of the header class.
$headerComponents = array();
$headerModule = 'events';
include('../../includes/headerClass.php');
include('inc/functions.php');

$pageInit = new headerClass($headerComponents, $headerModule);

// get all categories
$categories = getCategoriesData();

// -----------------------------------------------html------------------------------------

// head/header/sidebar
$pageResources ="
<link rel='stylesheet' type='text/css' href='style.css' />
<script type=\"text/javascript\" src=\"".$_config['admin_url']."modules/events/js/events_category.js\"></script>
";
$pageInit->createPageTop($pageResources);
?>

<div class="page_container">
	<div id="h1"><h1>Categories</h1></div>
    <div id="info_container" class='eventsTable'>

		<?php 
		$selectedEvents = '';
		$selectedCats = 'tabSel';
		$selectedOpts = '';

        echo '<div id="catsnavhome">';

		include("inc/subnav.php"); 

        echo '</div>'; ?>

		 <?php 

		if (count($categories) > 0 ) :
		?>

		<div id='eventsCats'>
			<table cellspacing="0" cellpadding="0" style="border-top:1px solid #aaa;">
				<tr>
					<th width="370" style="text-align:left;padding-left:90px;">TITLE</th>
					<th >STATUS</th>
					<th>OPERATION</th>
				</tr>
			</table>

			<div class='cat_grp'> 
				<?php	
				buildCatRows($categories)
				?>
			</div> <!--end cat_grp -->
		</div> <!--end eventsCats -->
		
		<?php else : ?>
			<p style='text-align:center;width:100%'>There are no categories yet. Why not click "Add Category" to start organizing your events?</p>
		<?php endif; ?>
</div> <!--end infoContainer -->
</div>
<?php 
include($_config['admin_includes']."footer.php"); ?>
