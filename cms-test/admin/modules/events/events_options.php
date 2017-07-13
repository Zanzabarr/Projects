<?php 

// initialize the page
$headerComponents = array();
$headerModule = 'events';
include('../../includes/headerClass.php');
include('inc/functions.php');
$pageInit = new headerClass($headerComponents,$headerModule);

$baseUrl = $_config['admin_url'];

// set the db variables
$table 				= 'events_options';		// name of the table this page posts to
$parent_page		= 'events.php';		// the page that calls this one: used for delete and cancel form buttons, sets where to go afterwards
$message			= array();			// will hold error/success message info

$page_get_id_val = 1;

// show the current values
$options = getEventsOptions(); 
$list=$options;

$optionsExist = false;

if(array_key_exists('id', $list)) {
    $optionsExist = true;
}

#==================
# process options
#==================
if(isset($_POST['submit-options'])){

	$list = array();
	foreach($_POST as $k=>$v)
	{
		${$k} = trim($v);
		$list[$k] = ${$k};
		if (! is_numeric(${$k})) {$message['inline'][$k] = array ('type' => 'errorMsg', 'msg' => 'Invalid Data');}
	}
	// if an error was found, create the error banner
	if (isset($message['inline']) && count($message['inline']))
	{
		$message['banner'] = array ('heading' => 'Unexpected Values', 'message' => 'could not update records', 'type' => 'error');
	}
	else // set the success message and save data
	{
		$message['banner'] = array ('heading' => 'Successfully Saved', 'type' => 'success');
		if (array_key_exists('option', $_GET) && ($_GET['option'] == 'edit'))
		{
            if(!($optionsExist)) {
                $result = logged_query("INSERT INTO `{$table}` (`events_per_pg`)
                              VALUES (:events_per_pg);",0,array(":events_per_pg" => $events_per_pg));
            }
            else {
				$result = logged_query("UPDATE `{$table}` SET 
			    `events_per_pg` = :events_per_pg
                WHERE `id` = :page_get_id_val;",0,array(":events_per_pg" => $events_per_pg, ":page_get_id_val" => $page_get_id_val));
			}

			// banners: if there was an error, overwrite the previously set success message
			if ($result === false)	$message['banner'] = array ('heading' => 'Error Saving Options', 'message' => 'there was an error writing to the database', 'type' => 'error' );
		}
	}
}
if(! isset($message))$message = array();

// events per page
$input_events_per_page = new inputField( 'Events Per Page', 'events_per_pg' );	
$input_events_per_page->toolTip('Set the number of events that will be displayed per page in the frontend.');
$input_events_per_page->type('select');
$input_events_per_page->size('tiny');
$input_events_per_page->selected(isset($list['events_per_pg']) ? $list['events_per_pg'] : '');
$input_events_per_page->option( 1, '1');
$input_events_per_page->option( 2, '2' );
$input_events_per_page->option( 3, '3' );
$input_events_per_page->option( 4, '4' );
$input_events_per_page->option( 5, '5' );
$input_events_per_page->arErr($message);

$pageResources ="
<link rel=\"stylesheet\" type=\"text/css\" href=\"".$_config['admin_url']."modules/events/style.css\" />
<script type=\"text/javascript\" src=\"".$_config['admin_url']."modules/events/js/events_options.js\"></script>
";
$pageInit->createPageTop($pageResources);
 
 ?>
 <div class="page_container">
	<div id="h1"><h1>Events Options</h1></div>
    <div id="info_container">
		<?php 
		// ------------------------------------sub nav----------------------------
		$selectedEvents = '';
		$selectedCats = '';
		$selectedOpts = 'tabSel';
		include("inc/subnav.php"); 
		echo '<hr />';		
		
		//---------------------------------------Error Banner----------------------------- 
		// create a banner if $message['banner'] exists
		createBanner($message); 

		
		$propId = '';
		$propId = 'prop-toggle'; // currently disabled since there is only one heading.
		
		$parms = "?option=edit";
		?>
		<form action="<?php echo htmlentities($_SERVER['PHP_SELF']); ?><?php echo $parms; ?>" method="post" enctype="application/x-www-form-urlencoded" name="form_data" id="form_data" class="form">
  
			<!--  properties_wrap -->
			<h2 id="<?php echo $propId; ?>" class="tiptip toggle" title="Set the number of events displayed per page in the frontend events sidebar.">Events Page Properties</h2><br />
			<div id="prop-toggle-wrap">
			<?php
				$input_events_per_page->createInputField();
			?>
			</div><!-- end prop_wrap -->		   

			<!-- page buttons -->
			<div class='clearFix' ></div>

			<input name="submit-options" id="submit-options" type="hidden" value="1" />
		</form>	
		<a id="submit-btn" class="blue button" href="#">Save</a>

		<a class="grey button" id="cancel-btn" href="<?php echo $parent_page; ?>">Cancel</a>
		<div class='clearFix' ></div> 
		<!-- end page buttons -->
	</div>
</div>	
	
<?php 
include($_config['admin_includes'] . "footer.php"); ?>
