<?php 

// initialize the page
$headerComponents = array('revisions');
$headerModule = 'members';
include('../../includes/headerClass.php');
$pageInit = new headerClass($headerComponents,$headerModule);

$baseUrl = $_config['admin_url'];

// set the db variables
$table 				= 'members_home';		// name of the table this page posts to
$mce_content_name	= 'desc';			// the tinyMCE editor always uses the id:content, but db tables usually use something specific to that table for main content,
$revision_table 	= 'members_home_rev';	// name of the revision table
$revision_table_id 	= 'members_home_id'; 	// the name of the field in the revision table that contains the id of the original table
$page_type			= 'members_home';		// name of uploadPageType for uploads component, also used to differentiate kinds of delete on parent page
$parent_page		= 'members.php';		// the page that calls this one: used for delete and cancel form buttons, sets where to go afterwards
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
	$new = logged_query("SELECT * FROM {$table} LIMIT 1",0,array()); 
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
		$errorMessage = 'saved with Members Homepage disabled';
	}
	else  // draft: thus warning
	{
		$errorMsgType = 'warningMsg';
		$errorType = 'warning';
		$errorHeading = 'Successfully Saved';
		$errorMessage = 'with Members Homepage disabled';
	}
	$list = array();
	$_POST['first_page_only'] = isset($_POST['first_page_only']) ? 1 : 0; 
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
	//	if (${$k} == '' && $k != 'submit-home' && $k != 'submit-home' ) {$message['inline'][$k] = array ('type' => $errorMsgType, 'msg' => 'Required field');}
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
			array ('heading' => 'Successfully Saved', 'message' => 'with Members Homepage enabled','type' => 'success') : 
			array('heading' => 'Successfully Saved', 'message' => 'with Members Homepage disabled', 'type' => 'warning');
	}

	// save even if errors exist: but save as draft
		if (array_key_exists('option', $_GET) && ($_GET['option'] == 'edit'))
		{
            if(!($homepageExists)) {
                // members homepage does not exist yet
                $result = logged_query("INSERT INTO `{$table}` (`title`, `desc`, `status`, `first_page_only`, `members_per_pg`, `date`)
VALUES (:title, :mce_content_name, :status, :first_page_only, :members_per_pg, NOW());",0,array(
					":title" => $title,
					":mce_content_name" => $$mce_content_name,
					":status" => $status,
					":first_page_only" => $first_page_only,
					":members_per_pg" => $members_per_pg
				));
            }
            else {
                // update existing members homepage
			    $result = logged_query("UPDATE `{$table}` SET 
	`title` = :title, 
	`$mce_content_name` = :mce_content_name,
	`status` = :status,
	`first_page_only` = :first_page_only,
	`members_per_pg` = :members_per_pg,
	`date` = NOW()
	WHERE `id` = :page_get_id_val LIMIT 1;",0,array(
					":page_get_id_val" => $page_get_id_val,
					":title" => $title,
					":mce_content_name" => $$mce_content_name,
					":status" => $status,
					":first_page_only" => $first_page_only,
					":members_per_pg" => $members_per_pg
				));
            }
			
			if($result !== false)
			{
				$result = logged_query("INSERT INTO `{$revision_table}` (`{$revision_table_id}`, `title`, `{$mce_content_name}`, `status`, `first_page_only`, `members_per_pg`, `date`) 
VALUES (:page_get_id_val, :title, :mce_content_name,  :status, :first_page_only, :members_per_pg, NOW());",0,array(
					":page_get_id_val" => $page_get_id_val,
					":title" => $title,
					":mce_content_name" => $$mce_content_name,
					":status" => $status,
					":first_page_only" => $first_page_only,
					":members_per_pg" => $members_per_pg
				)); 
			}
			
			// banners
			// banners: if there was an error, overwrite the previously set success message
			if ($result === false)	$message['banner'] = array ('heading' => 'Error Saving Members Home', 'message' => 'there was an error writing to the database', 'type' => 'error' );
		}
}
if(! isset($message))$message = array();
// page title
$input_homepage_title = new inputField( 'Homepage Title', 'title' );	
$input_homepage_title->toolTip('Title as it appears at the top of the Members Roster.');
$input_homepage_title->value(htmlspecialchars_decode(isset($list['title']) ? $list['title'] : ''));
$input_homepage_title->counterMax(100);
$input_homepage_title->size('small');
$input_homepage_title->arErr($message);

// status
$input_status = new inputField('Status', 'status' );	
$input_status->toolTip('When enabled, the Members Roster description appears above the list of members in the frontend members roster.<br /> If enabled, "Members Description" must be completed.');
$input_status->type('select');
$input_status->selected(isset($list['status']) ? $list['status'] : '');
$input_status->option( 0, 'Disabled' );
$input_status->option( 1, 'Enabled' );
$input_status->arErr($message);

// Front Page Only	
$input_first_page_only = new inputField( 'First Page Only', 'first_page_only' );
$input_first_page_only->toolTip('Only show this message on the first page of a multi-page Members List.');
$input_first_page_only->type('checkbox');
$input_first_page_only->value(isset($list['first_page_only']) ? $list['first_page_only'] : '');
$input_first_page_only->arErr($message);
 
// members per page
$input_members_per_pg = new inputField( 'Members Per Page', 'members_per_pg' );	
$input_members_per_pg->toolTip('Set the number of members that will be displayed per page in the frontend.');
$input_members_per_pg->type('select');
$input_members_per_pg->size('tiny');
$input_members_per_pg->selected(isset($list['members_per_pg']) ? $list['members_per_pg'] : '');
$input_members_per_pg->option( 1, '1' );
$input_members_per_pg->option( 2, '2' );
$input_members_per_pg->option( 3, '3' );
$input_members_per_pg->option( 4, '4' );
$input_members_per_pg->option( 5, '5');
$input_members_per_pg->option( 10, '10' );
$input_members_per_pg->option( 15, '15' );
$input_members_per_pg->option( 20, '20' );
$input_members_per_pg->option( 25, '25' );
$input_members_per_pg->option( -1, 'All' );
$input_members_per_pg->arErr($message);

$pageResources ="
<link rel=\"stylesheet\" type=\"text/css\" href=\"".$_config['admin_url']."modules/members/style.css\" />
<script type=\"text/javascript\" src=\"".$_config['admin_url']."modules/members/js/members_home.js\"></script>
";
$pageInit->createPageTop($pageResources);
 
 ?>
 <div class="page_container">
	<div id="h1"><h1>Members Homepage</h1></div>
    <div id="info_container">
		<?php 
		// ------------------------------------sub nav----------------------------
		$selectedMembers = '';
		$selectedOpts = '';
		$selectedPosts = '';
		$selectedHome = 'tabSel';
		include("includes/subnav.php"); 
		echo '<hr />';		
		
		//---------------------------------------Error Banner----------------------------- 
		// create a banner if $message['banner'] exists
		createBanner($message); 

		$parms = "?option=edit";
		?>
		<form action="<?php echo htmlentities($_SERVER['PHP_SELF']); ?><?php echo $parms; ?>" method="post" enctype="application/x-www-form-urlencoded" name="form_data" id="form_data" class="form">
  
			<!--  properties_wrap -->
			<h2 id="prop-toggle" class="tiptip toggle" title="Choose your Members page title and decide if it should appear in the frontend">Members Homepage Properties</h2><br />
			<div id="prop-toggle-wrap">
			<?php
				$input_homepage_title->createInputField();
				$input_status->createInputField();
				$input_first_page_only->createInputField();
				$input_members_per_pg->createInputField();
			?>
			</div><!-- end prop_wrap -->		   

             <!-- content area -->
			<h2 id="content-toggle" class="tiptip toggle" title="If this section is completed, the Members Homepage section will be available to visitors.">Members Homepage Description</h2>
			<?php if (isset($message['inline']) && array_key_exists($mce_content_name, $message['inline'])) :?>
				<span class="<?php echo $message['inline'][$mce_content_name]['type'] ;?>"><?php echo $message['inline'][$mce_content_name]['msg'] ;?> </span>
			<?php endif; ?>
			<br />
			<div id="content-toggle-wrap">
			<?php
			// create tinymce
			$editable = array(
				'editable_class' => 'mceUploadable',
				'attributes' => array(
					'name' => 'content',
					'id' => 'content'
				),
				'secure_data' => array(
					'id-field' => 'id',				// req for save && upload
					'id-val' => $page_get_id_val,	// req for save && upload
					'upload-type' => $page_type			// req for upload
				)
			);
			$wrapper = getContentWrapper($editable);
			echo $wrapper['open'];
			echo isset($list[$mce_content_name]) ? htmlspecialchars_decode($list[$mce_content_name]) : '' ;
			echo $wrapper['close'];
			?>
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
		<?php 

			// ----------------------------------- revisions -------------------------------------
			// build the Revision Area
			
			$Revisions->createRevisionsArea($page_get_id_val, array(),array());
			// end revisions
?>	
	</div>
</div>	
	
<?php 

include($_config['admin_includes'] . "footer.php"); ?>
