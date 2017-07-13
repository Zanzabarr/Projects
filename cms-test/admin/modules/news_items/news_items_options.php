<?php 

// initialize the page
$headerComponents = array();
$headerModule = 'news_items';
include('../../includes/headerClass.php');
$pageInit = new headerClass($headerComponents,$headerModule);

$baseUrl = $_config['admin_url'];

// set the db variables
$table 				= 'news_items_options';		// name of the table this page posts to

$parent_page		= 'news_items.php';		// the page that calls this one: used for delete and cancel form buttons, sets where to go afterwards

$message			= array();			// will hold error/success message info

$page_get_id_val = 1;

// show the current values
$new = logged_query_assoc_array("SELECT * FROM {$table} LIMIT 1"); 
$list=isset($new[0]) ? $new[0] : array();

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
		${$k} = trim(mysql_real_escape_string($v));
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
                logged_query("INSERT INTO `{$table}` (`news_items_per_pg`)
                              VALUES ('$news_items_per_pg');");
            }
            else {
			    logged_query("UPDATE `{$table}` SET 
			    `news_items_per_pg` = '$news_items_per_pg'
                WHERE `id` = '$page_get_id_val';");
			}

			$saveError = false;
			if(mysql_error()) $saveError = true;

			// banners: if there was an error, overwrite the previously set success message
			if ($saveError)	$message['banner'] = array ('heading' => 'Error Saving Options', 'message' => 'there was an error writing to the database', 'type' => 'error' );
		}
	}
}
if(! isset($message))$message = array();

// news_items per page
$input_news_items_per_page = new inputField( 'News Items Per Page', 'news_items_per_pg' );	
$input_news_items_per_page->toolTip('Set the number of News Items that will be displayed per page in the frontend.');
$input_news_items_per_page->type('select');
$input_news_items_per_page->size('tiny');
$input_news_items_per_page->selected(isset($list['news_items_per_pg']) ? $list['news_items_per_pg'] : '');
$input_news_items_per_page->option( 5, '5');
$input_news_items_per_page->option( 10, '10' );
$input_news_items_per_page->option( 15, '15' );
$input_news_items_per_page->option( 20, '20' );
$input_news_items_per_page->option( 25, '25' );
$input_news_items_per_page->option( 50, '50' );
$input_news_items_per_page->option( -1, 'All' );
$input_news_items_per_page->arErr($message);

$pageResources ="
<link rel=\"stylesheet\" type=\"text/css\" href=\"".$_config['admin_url']."modules/news_items/style.css\" />
<script type=\"text/javascript\" src=\"".$_config['admin_url']."modules/news_items/js/news_items_options.js\"></script>
";
$pageInit->createPageTop($pageResources);
 
 ?>
 <div class="page_container">
	<div id="h1"><h1>News Items Options</h1></div>
    <div id="info_container">
		<?php 
		// ------------------------------------sub nav----------------------------
		$selectedOpts = 'tabSel';
		$selectednews_items = '';
		include("includes/subnav.php"); 
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
			<h2 id="<?php echo $propId; ?>" class="tiptip toggle" title="Set the number of News Items displayed per page in the frontend News Item's roster.">News Items Page Properties</h2><br />
			<div id="prop-toggle-wrap">
			<?php
				$input_news_items_per_page->createInputField();
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
