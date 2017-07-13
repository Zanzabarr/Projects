<?php 
// headerComponents must be defined before the include; it is used in the include as well as during instantiation of the header class.
$headerComponents = array();
$headerModule = 'events';
include('../../includes/headerClass.php');
include('inc/functions.php');

$pageInit = new headerClass($headerComponents, $headerModule);

// get events data
$events = getEventsData();

// get categories data
$categories = getCategoriesData();

// -----------------------------------------------html------------------------------------

// head/header/sidebar
$pageResources ="
<link rel=\"stylesheet\" type=\"text/css\" href=\"".$_config['admin_url']."modules/events/style.css\" />
<script type=\"text/javascript\" src=\"".$_config['admin_url']."modules/events/js/events.js\"></script>
";
$pageInit->createPageTop($pageResources);
?>

<div class="page_container">
	<div id="h1"><h1>Events</h1></div>
    <div id="info_container" class='eventsTable'>

		<?php 
		$selectedEvents = 'tabSel';
		$selectedCats = '';
		$selectedOpts = '';
        
        echo '<div id="eventsnavhome">'; 
        
		include("inc/subnav.php"); 

        echo '</div>'; ?>

		<div class='clearFix' ></div> 

		 <?php 
		errors(); 
        if (count($events) > 0 ) :
		?>
			
		<div id='events-list'>
			<table cellspacing="0" cellpadding="0" style="border-top:1px solid #aaa;">
				<tr>
					<th width="165">TITLE</th>
					<th width="165">CATEGORY</th>
					<th width="165">START DATE</th>
					<th width="75">STATUS</th>
					<th width="135">OPERATION</th>
				</tr>
			</table>

			<?php 
			echo buildEventsMenu($events, $categories); 
		?>
		</div> <!--END events-list -->
		<?php else : ?>
		<p style='text-align:center;width:100%'>There are no events yet. Why not click "Add Event" to add your first one?</p>
		<?php endif; ?>

	</div> <!--end infoContainer -->
 </div>
<?php 
include($_config['admin_includes']."footer.php"); 
?>
