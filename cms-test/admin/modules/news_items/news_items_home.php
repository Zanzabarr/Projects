<?php 

// initialize the page
$headerComponents = array('revisions','uploads');
$headerModule = 'news_items';
include('../../includes/headerClass.php');
$pageInit = new headerClass($headerComponents,$headerModule);

$baseUrl = $_config['admin_url'];

// set the db variables
$table 				= 'news_items_home';		// name of the table this page posts to
$mce_content_name	= 'desc';			// the tinyMCE editor always uses the id:content, but db tables usually use something specific to that table for main content,
$revision_table 	= 'news_items_home_rev';	// name of the revision table
$revision_table_id 	= 'news_items_home_id'; 	// the name of the field in the revision table that contains the id of the original table
$page_type			= 'news_items_home';		// name of uploadPageType for uploads component, also used to differentiate kinds of delete on parent page
$parent_page		= 'news_items.php';		// the page that calls this one: used for delete and cancel form buttons, sets where to go afterwards
$message			= array();			// will hold error/success message info
$page_get_id_val = 1;

// this page has revisions, instaniate it
include_once($_config['components']."revisions/revisions.php");
$Revisions = new revisions($revision_table, $revision_table_id);

// if this arrived from revision history, get info
if ( $Revisions->isRevision() )
{
	$list = $Revisions->getRevisionData($page_get_id_val);
	$message = $Revisions->getResultMessage($page_get_id_val);
}
// show the current values
else
{	
	$new = logged_query_assoc_array("SELECT * FROM {$table} LIMIT 1"); 
	$list=isset($new[0]) ? $new[0] : array();
    
    if(array_key_exists('id', $list)) {
        $homepageExists = true;
    }
    else {
        $homepageExists = false;
    }
}

#==================
# process post
#==================
if(isset($_POST['submit-home'])){

	// validate:
	//	pick error type/messages based on if its status is draft or published
	if ($_POST['status'] == 1) // published: thus error
	{
		$errorMsgType = 'errorMsg';
		$errorType = 'error';
		$errorHeading = 'Blank Fields';
		$errorMessage = 'saved with News Items Homepage disabled';
	}
	else  // draft: thus warning
	{
		$errorMsgType = 'warningMsg';
		$errorType = 'warning';
		$errorHeading = 'Successfully Saved';
		$errorMessage = 'with News Items Homepage disabled';
	}
	$list = array();
	foreach($_POST as $k=>$v)
	{
		// convert tinyMCE's content id to the related data table's element name
		if($k == 'content') 
		{
			${$mce_content_name} = trim(htmlspecialchars($v, ENT_QUOTES));
			$k = $mce_content_name;
		}
		else ${$k} = trim(htmlspecialchars($v, ENT_QUOTES));
		$list[$k] = ${$k};
		if (${$k} == '' && $k != 'submit-home' ) {$message['inline'][$k] = array ('type' => $errorMsgType, 'msg' => 'Required field');}
	}

	// if an error was found, create the error banner
	if (isset($message['inline']) && count($message['inline']))
	{
		$message['banner'] = array ('heading' => $errorHeading, 'message' => $errorMessage, 'type' => $errorType);
		$status = $list['status'] = 0; // saving as draft

		if (array_key_exists($revision_table_id, $_GET) && is_numeric($_GET[$revision_table_id]) ) $page_get_id_val = $_GET[$revision_table_id];
	}
	else // set the success message
	{
		$message['banner'] = $list['status'] ? 
			array ('heading' => 'Successfully Saved', 'message' => 'with News Items Homepage enabled','type' => 'success') : 
			array('heading' => 'Successfully Saved', 'message' => 'with News Items Homepage disabled', 'type' => 'warning');
	}

	// save even if errors exist: but save as draft
		if (array_key_exists('option', $_GET) && ($_GET['option'] == 'edit'))
		{
            if(!($homepageExists)) {
                // news_items homepage does not exist yet
                logged_query("INSERT INTO `{$table}` (`title`, `desc`, `status`, `date`)
                              VALUES ('$title', '${$mce_content_name}', '$status', NOW());");
            }
            else {
                // update existing news_items homepage
			    logged_query("UPDATE `{$table}` SET 
			    `title` = '$title', 
			    `$mce_content_name` = '${$mce_content_name}',
			    `status` = '$status',
			    `date` = NOW()
			    WHERE `id` = '$page_get_id_val' LIMIT 1;");
            }
			
			$saveError = false;
			if(mysql_error()) $saveError = true;
			
			if(! $saveError)
			{
				logged_query("INSERT INTO `{$revision_table}` (`{$revision_table_id}`, `title`, `$mce_content_name`, `status`, `date`) 
							VALUES ('$page_get_id_val', '$title', '${$mce_content_name}',  '$status', NOW());"); 
			}
			
			// banners
			// banners: if there was an error, overwrite the previously set success message
			if ($saveError)	$message['banner'] = array ('heading' => 'Error Saving News Items Home', 'message' => 'there was an error writing to the database', 'type' => 'error' );
		}
}
if(! isset($message))$message = array();
// page title
$input_homepage_title = new inputField( 'Homepage Title', 'title' );	
$input_homepage_title->toolTip('Title as it appears at the top of the News Items Page.');
$input_homepage_title->value(htmlspecialchars_decode(isset($list['title']) ? $list['title'] : ''));
$input_homepage_title->counterMax(100);
$input_homepage_title->size('small');
$input_homepage_title->arErr($message);

// status
$input_status = new inputField('Status', 'status' );	
$input_status->toolTip('When enabled, the News Items description appears above the list of News Items in the frontend News Items roster.<br /> If enabled, "News Items Description" must be completed.');
$input_status->type('select');
$input_status->selected(isset($list['status']) ? $list['status'] : '');
$input_status->option( 0, 'Disabled' );
$input_status->option( 1, 'Enabled' );
$input_status->arErr($message);
 

$pageResources ="
<link rel=\"stylesheet\" type=\"text/css\" href=\"".$_config['admin_url']."modules/news_items/style.css\" />
<script type=\"text/javascript\" src=\"".$_config['admin_url']."modules/news_items/js/news_items_home.js\"></script>
";
$pageInit->createPageTop($pageResources);
 
 ?>
 <div class="page_container">
	<div id="h1"><h1>News Items Homepage</h1></div>
    <div id="info_container">
		<?php 
		// ------------------------------------sub nav----------------------------
		$selectednews_items = '';
		$selectedOpts = '';
		$selectedPosts = '';
		include("includes/subnav.php"); 
		echo '<hr />';		
		
		//---------------------------------------Error Banner----------------------------- 
		// create a banner if $message['banner'] exists
		createBanner($message); 

		$parms = "?option=edit";
		?>
		<form action="<?php echo htmlentities($_SERVER['PHP_SELF']); ?><?php echo $parms; ?>" method="post" enctype="application/x-www-form-urlencoded" name="form_data" id="form_data" class="form">
  
			<!--  properties_wrap -->
			<h2 id="prop-toggle" class="tiptip toggle" title="Choose your News Items page title and decide if it should appear in the frontend">News Items Homepage Properties</h2><br />
			<div id="prop-toggle-wrap">
			<?php
				$input_homepage_title->createInputField();
				$input_status->createInputField();
			?>
			</div><!-- end prop_wrap -->		   

             <!-- content area -->
			<h2 id="content-toggle" class="tiptip toggle" title="If this section is completed, the News Items Homepage section will be available to visitors.">News Items Homepage Description</h2>
			<?php if (isset($message['inline']) && array_key_exists($mce_content_name, $message['inline'])) :?>
				<span class="<?php echo $message['inline'][$mce_content_name]['type'] ;?>"><?php echo $message['inline'][$mce_content_name]['msg'] ;?> </span>
			<?php endif; ?>
			<br />
			<div id="content-toggle-wrap">
				<textarea class="mceEditor" name="content"><?php echo htmlspecialchars_decode(isset($list[$mce_content_name]) ? $list[$mce_content_name] : ''); ?></textarea>
			</div>
			<!-- end content area -->
			
			<!-- page buttons -->
			<div class='clearFix' ></div>

			<input name="submit-home" type="hidden" value="submit" />
		</form>	
		<a id="submit-btn" class="blue button" href="#">Save</a>

		<a class="grey button" id="cancel-btn" href="<?php echo $parent_page; ?>">Cancel</a>
		<div class='clearFix' ></div> 
		<!-- end page buttons -->
		<hr />
		
		<?php // include the image gallery 
			// $uploadPage and uploadPageType must be set before including uploads (they are page_id & page_type in tables: pictures & uploads
			$uploadPage = $page_get_id_val;
			$uploadPageType = $page_type;
			include("{$_config['components']}uploading/uploads.php"); 
		?>
		
		<?php	// ----------------------------------- revisions -------------------------------------
		// build the Revision Area
		$Revisions->createRevisionsArea($page_get_id_val, array(),array());
		// end revisions
?>	
	</div>
</div>	
	
<?php 

include($_config['admin_includes'] . "footer.php"); ?>
