<?php 
// initialize the page
$headerComponents = array('revisions');
$headerModule = 'shopping_cart';
include('../../includes/headerClass.php');

// must have admin privileges to access this page
if ($_SESSION['user']['admin'] != 'yes')
{
	header( "Location: " . $_config['admin_url'] . "modules/{$headerModule}/index.php" );
	exit;
}

$pageInit = new headerClass($headerComponents,$headerModule);

// access user data from headerClass
global $curUser;

//create collection tables
$query ="CREATE TABLE IF NOT EXISTS `ecom_collection` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `url` text NOT NULL,
  `title` text NOT NULL,
  `desc` text NOT NULL,
  `seo_title` text NOT NULL,
  `seo_keywords` text NOT NULL,
  `seo_description` text NOT NULL,
  `status` int(1) NOT NULL,
  `date_created` varchar(30) NOT NULL,
  `date_updated` varchar(30) NOT NULL,
  `valid_category` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;";
$result = logged_query($query,0,array());
if($result === false) die('Error Creating Collection Tables');

$query ="CREATE TABLE IF NOT EXISTS `ecom_collection_rev` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `ecom_col_id` int(11) NOT NULL,
  `url` text NOT NULL,
  `title` text NOT NULL,
  `desc` text NOT NULL,
  `seo_title` text NOT NULL,
  `seo_keywords` text NOT NULL,
  `seo_description` text NOT NULL,
  `status` int(1) NOT NULL,
  `date_created` varchar(30) NOT NULL,
  `date_updated` varchar(30) NOT NULL,
  `valid_category` varchar(250) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;";
$result = logged_query($query,0,array());
if($result === false) die('Error Creating Collection Tables');


// set the db variables
$table 				= 'ecom_collection';		// name of the table this page posts to
$mce_content_name	= 'desc';					// the tinyMCE editor always uses the id:content, 
												//   but db tables usually use something specific to that table for main content,
												//   eg, blog has 'post', category has 'desc', but page does use 'content': set the value here
$revision_table 	= 'ecom_collection_rev';	// name of the revision table
$revision_table_id 	= 'ecom_col_id'; 			// the id in the revision table that points to the id of the original table
$page_type			= 'ecom_collection';		// name of uploadPageType for uploads component, also used to differentiate kinds of delete on parent page
$parent_page		= 'collections.php';		// the page that calls this one: used for delete and cancel form buttons, sets where to go afterwards
$page_get_id		= 'colid';					// the GET key passed forward with the db id for this item(page/blog post/...)
$page_get_id_val	= '';						// this variable is used to store the GET id value of above

$newPost	 		= false;			// records that this is not a new Post/Page 

// this page has revisions, instaniate it
include_once($_config['components']."revisions/revisions.php");
$Revisions = new revisions($revision_table, $revision_table_id)	;
$Revisions->setOrderByField('date_updated');


if ( array_key_exists('option', $_GET) && ($_GET['option'] == 'create') ) 
{
	$newPost = true;
	
	$result = logged_query('
		SELECT MAX( CAST( SUBSTRING( `url` , 5 ) AS Unsigned ) ) AS num
		FROM `ecom_collection`
		WHERE `id` >0
		AND `url` REGEXP "^newcategory_[0-9]*$"',0,array()
	);
	$slug_num = is_array($result) && count($result) ? (int)$result[0]['num'] : 0;
	$tmp_slug = 'newcollection_' . ++$slug_num;
	$result = logged_query("INSERT INTO `ecom_collection` SET  title=:tmp_slug, url=:tmp_slug",0,array(":tmp_slug" => $tmp_slug));
	$page_get_id_val = $_config['db']->getLastInsertId();
	$list = logged_query("SELECT * FROM `ecom_collection` WHERE `id` =:col_id",0,array(":col_id" => $page_get_id_val));
	$list = $list[0];
}
// if this arrived from revision history, get info
elseif ( $Revisions->isRevision() )
{
	$page_get_id_val = trim($_GET[$page_get_id]);
	$list = $Revisions->getRevisionData($page_get_id_val);
	$message = $Revisions->getResultMessage($page_get_id_val);
}
// if this is an edit of an existing post, get the post info
elseif (array_key_exists($page_get_id, $_GET) && is_numeric($_GET[$page_get_id])) {	
	$new = logged_query("SELECT * FROM {$table} WHERE id = {$_GET[$page_get_id]} ORDER BY id DESC LIMIT 1",0,array(
	)); 
	$list=$new[0];

	$page_get_id_val = $_GET[$page_get_id];
}
// if we aren't editing or creating either, we shouldn't be here
elseif (! array_key_exists('option', $_GET) )
{
	header( "Location: " . $_config['admin_url'] . "modules/shopping_cart/". $parent_page );
	exit;
}	

#==================
# process post
#==================
if(isset($_POST['submit-post'])){

	// validate:
	$catDrafted = false;
	$pageConnected = false;
	//	pick error type/messages based on if its status is draft or published
	if ($_POST['status'] == 1) // published: thus error
	{
		$errorMsgType = 'errorMsg';
		$errorType = 'error';
		$errorHeading = 'Published Version';
		$errorMessage = 'all fields must be complete, saved as draft';
		
		// check for related page with draft status
		$checkdraft = logged_query("SELECT id, slug, page_title FROM pages WHERE id > 0 && status = 0 && slug = '--shopping/collection/{$_POST['url']}' LIMIT 1",0,array());
		$hasDraft = !empty($checkdraft) ? true : false;
		
		if($_POST['status'] == 1 && $hasDraft) {
			
			$relatedPageTitle = $checkdraft[0]['page_title'];
			$newSlug = substr($checkdraft[0]['slug'],2);
			$connectPage = logged_query("UPDATE pages SET status = 1, slug = '{$newSlug}' WHERE id = {$checkdraft[0]['id']}",0,array());
			if($connectPage !== false) $pageConnected = true;
		}
		
	}
	else  // draft: thus warning
	{
		$errorMsgType = 'warningMsg';
		$errorType = 'warning';
		$errorHeading = 'Draft Version';
		$errorMessage = 'saved with incomplete fields on page';
		
		//check for related page with published status
		$checkpage = logged_query("SELECT id, page_title FROM pages WHERE id > 0 && status = 1 && slug = 'shopping/collection/{$_POST['url']}'",0,array());
		$hasPage = !empty($checkpage) ? true : false;
		
		
		if($hasPage) {
			$_POST['status'] = $status = 1;
			$relatedPageTitle = $checkpage[0]['page_title'];
			$catDrafted = true;
		}
	}

	foreach($_POST as $k=>$v)
	{
		// convert tinyMCE's content id to the related data table's element name
		if($k == 'content') 
		{
			${$mce_content_name} = trim(htmlspecialchars($v, ENT_QUOTES));
			$k = $mce_content_name;
		}
		elseif ($k == 'valid_category') ${$k} = pack_multi_index($v); 
		else ${$k} = trim(htmlspecialchars($v, ENT_QUOTES));
		$list[$k] = ${$k};
		if (${$k} == '' && $k != 'submit-post' && $k != $page_get_id) {$message['inline'][$k] = array ('type' => $errorMsgType, 'msg' => 'Required field');}
	}
	
	// if valid category isn't set: set it as empty
	if (!isset($valid_category) || $valid_category === false) 
	{
		$valid_category = '';
		$list['valid_category'] = $valid_category;
	}

	// validate uniqueness of url / title
	//get all existing collections
	$collections = logged_query_assoc_array("SELECT `id`, `user_id`, `url`, `title` FROM `{$table}`",null,0,array());
	$catUrl = array();
	$catTitle = array();
	foreach ($collections as $collection)
	{
		$catUrl[$collection['url']] = $collection;
		$catTitle[$collection['title']] = $collection;
	}
	// while not unique, append!
	$nameingError = false;
	while ( array_key_exists($url, $catUrl) && ($_GET['option'] == 'create' || $catUrl[$url]['id'] != $page_get_id_val ) ) 
	{
		$namingError = true;
		$url .= '-';
		$list['url'] = $url;
		$message['inline']['url'] = array ('type' => 'errorMsg', 'msg' => 'Url altered to be unique');
	}
	
	// if renaming occured, set alerts
	if (isset($namingError) && $namingError)
	{
		$errorMsgType = 'errorMsg';
		$errorType = 'error';
		$errorHeading = 'Naming Error';
		$errorMessage = 'collection name or url already exists, saved as draft with modified names';
	}
	
	
	// if an error was found, create the error banner
	$errorsExist = isset($message['inline']) ? count($message['inline']) : 0 ;
	if ($errorsExist )
	{
		$message['banner'] = array ('heading' => $errorHeading, 'message' => $errorMessage, 'type' => $errorType);
		$status = $list['status'] = 0; // saving as draft

		if (array_key_exists($revision_table_id, $_GET) && is_numeric($_GET[$revision_table_id]) ) $page_get_id_val = $_GET[$revision_table_id];
	}
	else // set the success message
	{
		if($catDrafted || $pageConnected) {
			if($catDrafted) {
				$message['banner'] = array ('heading' => 'Successfully Saved', 'message' => "with Status: Published. This Collection has a related page and cannot be made Draft.<br />Please make the page '{$relatedPageTitle}' Draft, or delete this collection entirely.", 'type' => 'warning');
			}
			elseif($pageConnected) {
				$message['banner'] = array ('heading' => 'Successfully Saved', 'message' => "Page '{$relatedPageTitle}' has been re-connected to this collection.", 'type' => 'success');
			}
		} else {
			$message['banner'] = array ('heading' => 'Successfully Saved', 'type' => 'success');
		}
	}

	// save even if errors exist: but save as draft
	{
		if (array_key_exists('option', $_GET) && ($_GET['option'] == 'create'))
		{
			$result = logged_query("
				INSERT INTO `{$table}` (`user_id`, `title`, `{$mce_content_name}`,  `date_created`, `date_updated`, `status`, `seo_description`, `seo_keywords`, `seo_title`, `url`, `valid_category`) 
				VALUES (:user_id , :title, :content, UTC_TIMESTAMP(), UTC_TIMESTAMP(), :status, :seo_description, :seo_keywords, :seo_title, :url, :valid_category);",0,array(
					":user_id" => $curUser['user_id'],
					":title" => $title,
					":content" => $$mce_content_name,
					":status" => $status,
					":seo_description" => $seo_description,
					":seo_keywords" => $seo_keywords,
					":seo_title" => $seo_title,
					":url" => $url,
					":valid_category" => $valid_category
				)); 
			$page_get_id_val = $_config['db']->getLastInsertId();
						
			if($result !== false)
			{
				$result = logged_query("INSERT INTO `{$revision_table}` (`user_id`, `{$revision_table_id}`, `title`, `{$mce_content_name}`, 	`date_created`, `date_updated`, `status`, `seo_description`, `seo_keywords`, `seo_title`, `url`, `valid_category`) VALUES (:user_id, :page_get_id_val, :title, :content, UTC_TIMESTAMP(), UTC_TIMESTAMP(), :status, :seo_description, :seo_keywords, :seo_title, :url, :valid_category);",0,array(
					":user_id" => $curUser['user_id'],
					":page_get_id_val" => $revision_table_id,
					":title" => $title,
					":content" => $$mce_content_name,
					":status" => $status,
					":seo_description" => $seo_description,
					":seo_keywords" => $seo_keywords,
					":seo_title" => $seo_title,
					":url" => $url,
					":valid_category" => $valid_category
				)); 
			}
			
			// banners: if there was an error, overwrite the previously set success message
			if ($result===false)	$message['banner'] = array ('heading' => 'Error Saving Collection', 'message' => 'there was an error writing to the database', 'type' => 'error' );
			
			
			// successfully created the page: no longer a new page!
			$newPost = false;
		}
		if (array_key_exists('option', $_GET) && ($_GET['option'] == 'edit'))
		{
			$result = logged_query("
			UPDATE `{$table}` SET 
			`user_id` = :user_id ,
			`title` = :title, 
			`{$mce_content_name}` = :content,
			`date_updated` = UTC_TIMESTAMP(),
			`seo_description` = :seo_description,
			`seo_keywords` = :seo_keywords,
			`seo_title` = :seo_title,
			`status` = :status,
			`url` = :url,
			`valid_category` = :valid_category
			WHERE `id` = :page_get_id_val LIMIT 1;",0,array(
				":user_id" => $curUser['user_id'],
				":page_get_id_val" => $page_get_id_val,
				":title" => $title,
				":content" => $$mce_content_name,
				":status" => $status,
				":seo_description" => $seo_description,
				":seo_keywords" => $seo_keywords,
				":seo_title" => $seo_title,
				":url" => $url,
				":valid_category" => $valid_category
			));
						
			if($result !== false)
			{
				$result = logged_query("INSERT INTO `{$revision_table}` (`user_id`, `{$revision_table_id}`, `title`, `{$mce_content_name}`,  	`date_created`, `date_updated`, `status`, `seo_description`, `seo_keywords`, `seo_title`, `url`, `valid_category`) VALUES (:user_id, :page_get_id_val, :title, :content, :date_created, UTC_TIMESTAMP(), :status, :seo_description, :seo_keywords, :seo_title, :url, :valid_category);",0,array(
					":user_id" => $curUser['user_id'],
					":page_get_id_val" => $page_get_id_val,
					":title" => $title,
					":content" => $$mce_content_name,
					":date_created" => $list['date_created'],
					":status" => $status,
					":seo_description" => $seo_description,
					":seo_keywords" => $seo_keywords,
					":seo_title" => $seo_title,
					":url" => $url,
					":valid_category" => $valid_category
				)); 
			}
			
			// banners
			// banners: if there was an error, overwrite the previously set success message
			if ($result===false)	$message['banner'] = array ('heading' => 'Error Saving Collection', 'message' => 'there was an error writing to the database', 'type' => 'error' );
		}
	}
	
// $_SESSION['SESS_MSG_green'] = "Blog Updated";

}
if (! isset($message)) $message=array();
// page title
$val = isset($list['title']) ? htmlspecialchars_decode($list['title']) : '';
$input_ecom_title = new inputField( 'Collection Title', 'title' );	
$input_ecom_title->toolTip('Title as it appears in the collection homepage.');
$input_ecom_title->value( $val );
$input_ecom_title->counterMax(100);
$input_ecom_title->size('small');
$input_ecom_title->arErr($message);

// url	
$val = isset($list['url']) ? htmlspecialchars_decode($list['url']) : '';
$input_url = new inputField( 'Url', 'url' );	
$input_url->toolTip('Title as it appears in the url:<br />Use only letters, numbers, underscores and hyphens. No Spaces!');
$input_url->value( $val );
$input_url->counterMax(100);
$input_url->size('small');
$input_url->arErr($message);	

// status
$val = isset($list['status']) ? htmlspecialchars_decode($list['status']) : '';
$input_status = new inputField( 'Status', 'status' );	
$input_status->toolTip('A draft version can have blank fields. A published version must have all fields completed.');
$input_status->type('select');
$input_status->selected( $val );
$input_status->option( 0, 'Draft' );
$input_status->option( 1, 'Published' );
$input_status->arErr($message);

// seo title
$val = isset($list['seo_title']) ? htmlspecialchars_decode($list['seo_title']) : '';
$input_seo_title = new inputField( 'SEO Title', 'seo_title' );	
$input_seo_title->toolTip('Start with most important words<br />65 characters or less.');
$input_seo_title->value( $val );
$input_seo_title->counterWarning(65);
$input_seo_title->counterMax(100);
$input_seo_title->size('large');
$input_seo_title->arErr($message);

// seo description
$val = isset($list['seo_description']) ? htmlspecialchars_decode($list['seo_description']) : '';
$input_seo_description = new inputField( 'Description', 'seo_description' );	
$input_seo_description->toolTip('Start with the same words used in the title<br />150 characters or less.');
$input_seo_description->type('textarea');
$input_seo_description->value( $val );
$input_seo_description->counterWarning(150);
$input_seo_description->counterMax(250);
$input_seo_description->size('large');
$input_seo_description->arErr($message);

// seo seo_keywords
$val = isset($list['seo_keywords']) ? htmlspecialchars_decode($list['seo_keywords']) : '';
$input_seo_keywords = new inputField( 'Keywords', 'seo_keywords' );	
$input_seo_keywords->toolTip('List of phrases, separated by commas, with most important phrases first. <br /> Note: the words have to appear somewhere on the page.');
$input_seo_keywords->type('textarea');
$input_seo_keywords->value( $val );
$input_seo_keywords->counterMax(1000);
$input_seo_keywords->size('large');
$input_seo_keywords->arErr($message);


// set the header varables and create the header
$pageResources ="<link rel='stylesheet' type='text/css' href='styles.css' />
<script type=\"text/javascript\" src=\"".$_config['admin_url']."modules/shopping_cart/js/edit_collection.js\"></script>
";//
$pageInit->createPageTop($pageResources);

$usedSlugs = array();  // array of slugs that have already been used
$slugs=logged_query("SELECT `url` FROM {$table}  WHERE id > 0",0,array());
if(count($slugs) > 0) {
	foreach($slugs as $row) {
		$usedSlugs[] = $row['url'];
	}
}

?>
<script type="text/javascript">
	curSlug = "<?php echo isset($list['url']) ? $list['url'] : ''; ?>";
	usedSlugs = new Array();
<?php foreach($usedSlugs as $slug) : ?>
	usedSlugs.push("<?php echo $slug; ?>");
<?php endforeach; ?>
</script>
 
 <div class="page_container">
	<div id="h1"><h1>Collections</h1></div>
    <div id="info_container">
		<?php
		// add subnav
		$pg = 'collection';	include("includes/subnav.php");
		
		echo "<hr>";
		
		//---------------------------------------Error Banner----------------------------- 
		// create a banner if $message['banner'] exists
		createBanner($message); 

		$parms = "?{$page_get_id}={$page_get_id_val}&option=edit";
		?>
		<form action="<?php echo htmlentities($_SERVER['PHP_SELF']); ?><?php echo $parms; ?>" method="post" enctype="application/x-www-form-urlencoded" name="form_data" id="form_data" class="form">
  
			<!--  properties_wrap -->
			<h2 id="prop-toggle" class="tiptip toggle" title="Information about the page.">Collection Properties</h2><br />
			<div id="prop-toggle-wrap">
			<?php
				$input_ecom_title->createInputField();
				$input_url->createInputField();
				$input_status->createInputField();
			?>
				<p class="infop"><img class="info-icon" src="../../images/info.png" alt="info icon" /><em>If you want a page for this collection on the menu, please save this collection as status "Published". Then go to Pages, create a page and choose this collection from the Special Pages selection.</em></p>
			<?php
				// if this site limits collection availability
				if(isset($_config['limit_collection']) && $_config['limit_collection'])
				{
					//get all available categories
					$tmp_cats = ecom_functions::get_all_categories();
					$check_options = array();
					foreach( $tmp_cats as $tmp_cat )
					{
						$check_options[] = array(
							'id' => $tmp_cat['id'],
							'title' => $tmp_cat['name']
						);
					}
					
					
					$checked_items = isset($list['valid_category']) ? extract_multi_index($list['valid_category']) : array();
					if ($checked_items === false) $checked_items = array();
					$check_group_name = 'valid_category';
					$check_group_label = 'Valid Categories';
					$check_group_table_id = 'valid_category_table';
					$check_group_columns = 2;
					$check_group_title = 'Select all categories that can use this collection type.<br>If none are selected, this collection is available to all categories.';
					
					display_checkbox_group(
						$check_options,
						$check_group_name,
						$check_group_label,
						$checked_items,
						$check_group_table_id,
						$check_group_title,
						$check_group_columns
					);
				}
			?>
			</div><!-- end prop_wrap -->		   

             <!-- content area -->
			<h2 id="content-toggle" class="tiptip toggle" title="The actual content displayed on the page.">Collection Description</h2>
			<?php if (isset($message['inline']) && array_key_exists($mce_content_name, $message['inline'])) :?>
				<span class="<?php echo $message['inline'][$mce_content_name]['type'] ;?>"><?php echo $message['inline'][$mce_content_name]['msg'] ;?> </span>
			<?php endif; ?>
			<br />
			
			<?php
			// create tinymce
			$jump_target = 'shopping/collection/'.$list['url'];
			
			$editable = array(
				'editable_class' => 'mceUploadable',
				'attributes' => array(
					'name' => 'content',
					'id' => 'content',
					'data-jump-type' => "front",
					'data-jump' => "../../../".$jump_target
				),
				'secure_data' => array(
					'id-field' => 'id',				// req for save && upload
					'id-val' => $page_get_id_val,	// req for save && upload
					'upload-type' => 'ecom_collection'		// req for upload
				)
			);
			$wrapper = getContentWrapper($editable);
			echo $wrapper['open'];
			echo isset($list['desc']) ? htmlspecialchars_decode($list['desc']) : '' ;
			echo $wrapper['close'];
			?>
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
			<?php if (! $newPost )  : ?>
			<input name="page_get_id_val" type="hidden" value="<?php echo $page_get_id_val; ?>"/>
			<?php endif ?>
			<input name="submit-post" type="hidden" value="submit" />
		</form>	
		<a id="submit-btn" class="blue button" href="#">Save</a>

		<a class="grey button" id="cancel-btn" href="<?php echo $parent_page; ?>">Cancel</a>
		<div class='clearFix' ></div> 
		<!-- end page buttons -->
		
		<?php	// ----------------------------------- revisions -------------------------------------

		// add GET data to the return url
		$extraGET = array($page_get_id => $page_get_id_val);

		// build the Revision Area
		$Revisions->createRevisionsArea($page_get_id_val, array(),$extraGET);
		// end revisions
?>	
	</div>
</div>	
	

<?php 

include($_config['admin_includes'] . "footer.php"); ?>