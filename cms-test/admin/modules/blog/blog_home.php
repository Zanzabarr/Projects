<?php 

// initialize the page
$headerComponents = array('revisions','uploads');
$headerModule = 'blog';
include('../../includes/headerClass.php');
$pageInit = new headerClass($headerComponents,$headerModule);

$baseUrl = $_config['admin_url'];
global $curUser;

// set the db variables
$table 				= 'blog_home';		// name of the table this page posts to
$mce_content_name	= 'desc';			// the tinyMCE editor always uses the id:content, but db tables usually use something specific to that table for main content,
										// eg, blog has 'post', category has 'desc', but page does use 'content': set the value here
$revision_table 	= 'blog_home_rev';	// name of the revision table
$revision_table_id 	= 'blog_home_id'; 	// the id in the revision table that points to the id of the original table
$page_type			= 'blog_home';		// name of uploadPageType for uploads component, also used to differentiate kinds of delete on parent page
$parent_page		= 'blog_options.php';		// the page that calls this one: used for delete and cancel form buttons, sets where to go afterwards
//$page_get_id		= 'catid';			// the GET key passed forward with the db id for this item(page/blog post/...)
$page_get_id_val	= 1;				// this variable is used to store the GET id value of above. Or, in single element sections(like blog comments) enter the value of the default id

//$newPost	 		= false;			// records that this is not a new Post/Page 
$message			= array();			// will hold error/success message info

// this page has revisions, instaniate it
include_once($_config['components']."revisions/revisions.php");
$Revisions = new revisions($revision_table, $revision_table_id)	;

// if this arrived from revision history, get info
if ( $Revisions->isRevision() )
{
	$list = $Revisions->getRevisionData($page_get_id_val);
	$message = $Revisions->getResultMessage($page_get_id_val);
}
// show the current values
else
{	
	$new = logged_query_assoc_array("SELECT * FROM {$table} LIMIT 1",null,0,array()); 
	$list=$new[0];
}

#==================
# process post
#==================
if(isset($_POST['submit-post'])){

	// validate:
	//	pick error type/messages based on if its status is draft or published
	if ($_POST['status'] == 1) // published: thus error
	{
		$errorMsgType = 'errorMsg';
		$errorType = 'error';
		$errorHeading = 'Blank Fields';
		$errorMessage = 'saved with "About the Blog" disabled';
	}
	else  // draft: thus warning
	{
		$errorMsgType = 'warningMsg';
		$errorType = 'warning';
		$errorHeading = 'Successfully Saved';
		$errorMessage = 'with "About the Blog" disabled';
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
		if (${$k} == '' && $k != 'submit-post' ) {$message['inline'][$k] = array ('type' => $errorMsgType, 'msg' => 'Required field');}
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
			array ('heading' => 'Successfully Saved', 'message' => 'with "About the Blog" enabled','type' => 'success') : 
			array('heading' => 'Successfully Saved', 'message' => 'with "About the Blog" disabled', 'type' => 'warning');
	}

	// save even if errors exist: but save as draft
	{
		if (array_key_exists('option', $_GET) && ($_GET['option'] == 'edit'))
		{
			$result = logged_query("UPDATE `{$table}` SET 
			`user_id` = :user_id,
			`title` = :title, 
			`url` = :url,
			`$mce_content_name` = :mce_content_name,
			`date` = UTC_TIMESTAMP(),
			`seo_description` = :seo_description,
			`seo_keywords` = :seo_keywords,
			`seo_title` = :seo_title,
			`status` = :status
			WHERE `id` = :page_get_id_val LIMIT 1;",0,array(
				":user_id" => $curUser['user_id'],
				":title" => $title,
				":url" => $url,
				":mce_content_name" => $$mce_content_name,
				":seo_description" => $seo_description,
				":seo_keywords" => $seo_keywords,
				":seo_title" => $seo_title,
				":status" => $status,
				":page_get_id_val" => $page_get_id_val
			));
			
			if($result !== false)
			{
				$result = logged_query("INSERT INTO `{$revision_table}` (`{$revision_table_id}`, `user_id`, `title`,`url`, `$mce_content_name`,  	`date`, `status`, `seo_description`, `seo_keywords`, `seo_title`) VALUES (:page_get_id_val, :user_id, :title, :url, :mce_content_name,  UTC_TIMESTAMP(), :status, :seo_description, :seo_keywords, :seo_title);",0,array(
					":page_get_id_val" => $page_get_id_val,
					":user_id" => $curUser['user_id'],
					":title" => $title,
					":url" => $url,
					":mce_content_name" => $$mce_content_name,
					":status" => $status,
					":seo_description" => $seo_description,
					":seo_keywords" => $seo_keywords,
					":seo_title" => $seo_title
				)); 
			}
			
			// banners
			// banners: if there was an error, overwrite the previously set success message
			if ($result === false)	$message['banner'] = array ('heading' => 'Error Saving Options', 'message' => 'there was an error writing to the database', 'type' => 'error' );
		}
	}
	
// $_SESSION['SESS_MSG_green'] = "Blog Updated";

}
if(! isset($message))$message = array();
// page title
$input_blog_title = new inputField( 'Blog Title', 'title' );	
$input_blog_title->toolTip('Title as it appears at the top of the blog.');
$input_blog_title->value( htmlspecialchars_decode($list['title']) );
$input_blog_title->counterMax(100);
$input_blog_title->size('small');
$input_blog_title->arErr($message);

// url	
$input_url = new inputField( 'Url', 'url' );	
$input_url->toolTip('Title as it appears in the url:<br />Use only letters, numbers, underscores and hyphens. No Spaces!');
$input_url->value( htmlspecialchars_decode($list['url']) );
$input_url->counterMax(100);
$input_url->size('small');
$input_url->arErr($message);

// status
$input_status = new inputField( 'About Blog', 'status' );	
$input_status->toolTip('When enabled, an "About The Blog" link appears in the blog.<br /> If enabled, "Blog Description" and "Meta Tags" must be completed.');
$input_status->type('select');
$input_status->selected($list['status']);
$input_status->option( 0, 'Disabled' );
$input_status->option( 1, 'Enabled' );
$input_status->arErr($message);

// seo title
$input_seo_title = new inputField( 'SEO Title', 'seo_title' );	
$input_seo_title->toolTip('Start with most important words<br />65 characters or less.');
$input_seo_title->value( htmlspecialchars_decode($list['seo_title']) );
$input_seo_title->counterWarning(65);
$input_seo_title->counterMax(100);
$input_seo_title->size('large');
$input_seo_title->arErr($message);

// seo description
$input_seo_description = new inputField( 'Description', 'seo_description' );	
$input_seo_description->toolTip('Start with the same words used in the title<br />150 characters or less.');
$input_seo_description->type('textarea');
$input_seo_description->value( htmlspecialchars_decode($list['seo_description']) );
$input_seo_description->counterWarning(150);
$input_seo_description->counterMax(250);
$input_seo_description->size('large');
$input_seo_description->arErr($message);

// seo seo_keywords
$input_seo_keywords = new inputField( 'Keywords', 'seo_keywords' );	
$input_seo_keywords->toolTip('List of phrases, separated by commas, with most important phrases first. <br /> Note: the words have to appear somewhere on the page.');
$input_seo_keywords->type('textarea');
$input_seo_keywords->value( htmlspecialchars_decode($list['seo_keywords']) );
$input_seo_keywords->counterMax(1000);
$input_seo_keywords->size('large');
$input_seo_keywords->arErr($message);
?>

    


 <?php 

$pageResources ="
<link rel='stylesheet' type='text/css' href='style.css' />
<script type=\"text/javascript\" src=\"".$_config['admin_url']."modules/blog/js/blog_home.js\"></script>
";
$pageInit->createPageTop($pageResources);
 
 ?>
 <div class="page_container">
	<div id="h1"><h1>Blog Options</h1></div>
    <div id="info_container">
		<?php 
		// ------------------------------------sub nav----------------------------
		$selectedOpts = 'tabSel';
		$selectedPosts = '';
		$selectedCats = '';
		include("blog/subnav.php"); 
		echo '<hr />';		
		
		//---------------------------------------Error Banner----------------------------- 
		// create a banner if $message['banner'] exists
		createBanner($message); 

		$parms = "?option=edit";
		?>
		<form action="<?php echo htmlentities($_SERVER['PHP_SELF']); ?><?php echo $parms; ?>" method="post" enctype="application/x-www-form-urlencoded" name="form_data" id="form_data" class="form">
  
			<!--  properties_wrap -->
			<h2 id="prop-toggle" class="tiptip toggle" title="Choose your blog's title, decide how to handle user comments and decide if your 'About the Blog' link is ready to be viewed.">Blog Properties</h2><br />
			<div id="prop-toggle-wrap">
			<?php
				$input_blog_title->createInputField();
				$input_url->createInputField();

				$input_status->createInputField();
			?>
			</div><!-- end prop_wrap -->		   

             <!-- content area -->
			<h2 id="content-toggle" class="tiptip toggle" title="If this section and the Meta Tags sections are completed, an 'About the Blog' section will be available to visitors.">Blog Description</h2>
			<?php if (isset($message['inline']) && array_key_exists($mce_content_name, $message['inline'])) :?>
				<span class="<?php echo $message['inline'][$mce_content_name]['type'] ;?>"><?php echo $message['inline'][$mce_content_name]['msg'] ;?> </span>
			<?php endif; ?>
			<br />
			<div id="content-toggle-wrap">
				<textarea class="mceEditor" name="content"><?php echo htmlspecialchars_decode($list[$mce_content_name]); ?></textarea>
			</div>
			<!-- end content area -->
			
			<!-- SEO area -->
			<h2 class="tiptip toggle" id="seo-toggle" ;" title="Search Engine Optimization fields">Meta Tags</h2><br />
			<div id="seo-toggle-wrap">
			<?php
				$input_seo_title->createInputField();
				$input_seo_description->createInputField();
				$input_seo_keywords->createInputField();
			?>
			</div><!-- end SEO area -->
                       
                            
			<!-- page buttons -->
			<div class='clearFix' ></div>

			<input name="submit-post" type="hidden" value="submit" />
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