<?php 
// initialize the page
$headerComponents = array();
$headerModule = 'events';
include('../../includes/headerClass.php');
include('inc/functions.php');
$pageInit = new headerClass($headerComponents,$headerModule);

// set the db variables
$table 				= 'events_cat';		// name of the table this page posts to
$mce_content_name	= 'desc';			// the tinyMCE editor always uses the id:content, but db tables usually use something specific to that table for main content,
										// eg, blog has 'post', category has 'desc', but page does use 'content': set the value here
$page_type			= 'events_cat';		// name of uploadPageType for uploads component, also used to differentiate kinds of delete on parent page
$parent_page		= 'events_category.php';		// the page that calls this one: used for delete and cancel form buttons, sets where to go afterwards
$page_get_id		= 'catid';			// the GET key passed forward with the db id
$page_get_id_val    = '';
$newCat	 		= false;			// records that this is not a new Category 


if (array_key_exists('option', $_GET) && ($_GET['option'] == 'create')) 
{
//'id-val' => $page_get_id_val,	// req for save && upload
//'upload-type' => $page_type			// req for upload
	$newCat = true;
	$result = logged_query('
		SELECT MAX( CAST( SUBSTRING( `url` , 5 ) AS Unsigned ) ) AS num
		FROM `'.$table.'`
		WHERE `id` >0
		AND `url` REGEXP "^new_[0-9]*$"',0,array()
	);
	$slug_num = is_array($result) && count($result) ? (int)$result[0]['num'] : 0;
	$tmp_slug = 'new_' . ++$slug_num;
	$result = logged_query("INSERT INTO `{$table}` SET title=:tmp_slug, url=:tmp_slug",0,array(":tmp_slug" => $tmp_slug));
	$page_get_id_val = $_config['db']->getLastInsertId();
	$list = logged_query("SELECT * FROM `{$table}` WHERE `id` =:page_id",0,array(":page_id" => $page_get_id_val));
	$list = $list[0];
}
// if this is an edit of an existing category, get the category info
elseif (array_key_exists($page_get_id, $_GET) && is_numeric($_GET[$page_get_id]) && ! array_key_exists('option', $_GET) ) {	
	$new = logged_query("SELECT * FROM {$table} WHERE id =:get_page_id ORDER BY id DESC LIMIT 1",0,array(":get_page_id" => $_GET[$page_get_id])); 
	if(! $new || !count($new))
	{
		header( "Location: " . $_config['admin_url'] . "modules/events/". $parent_page );
		exit;
	}
	
	$list=$new[0];
	$newcolor = logged_query("SELECT color FROM events_colors WHERE cat_id =:get_page_id",0,array(":get_page_id" => $_GET[$page_get_id])); 
	$color = isset($newcolor[0]['color']) ? $newcolor[0]['color'] : "#ffffff"; 
	
	$page_get_id_val = $_GET[$page_get_id];
}
// if we aren't editing or creating either, we shouldn't be here
elseif (! array_key_exists('option', $_GET) ) 
{
	header( "Location: " . $_config['admin_url'] . "modules/events/". $parent_page );
	exit;
}	


#==================
# process category info
#==================
if(isset($_POST['submit-cat'])){

	// validate:
	//	pick error type/messages based on if its status is draft or published
	if ($_POST['status'] == 1) // published: thus error
	{
		$errorMsgType = 'errorMsg';
		$errorType = 'error';
		$errorHeading = 'Published Version';
		$errorMessage = 'all fields must be complete, saved as draft';
	}
	else  // draft: thus warning
	{
		$errorMsgType = 'warningMsg';
		$errorType = 'warning';
		$errorHeading = 'Draft Version';
		$errorMessage = 'saved with incomplete fields on page';
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
		elseif($k == 'color') {
			$color = $v;
		}
		else ${$k} = trim(htmlspecialchars($v, ENT_QUOTES));
		$list[$k] = ${$k};
		if (${$k} == '' && $k != 'submit-cat' && $k != $page_get_id && $k != 'page_get_id_val') {$message['inline'][$k] = array ('type' => $errorMsgType, 'msg' => 'Required field');}
	}

	// validate uniqueness of url / title
	// get all existing categories
	$categories = getCategoriesData();
	$catUrls = array();
	$catTitles = array();
	foreach ($categories as $category)
	{
		$catUrls[$category['url']] = $category;
		$catTitles[$category['title']] = $category;
	}
	// while not unique, append!
	$namingError = false;
	while ( array_key_exists($title, $catTitles) && ($_GET['option'] == 'create' || $catTitles[$title]['id'] != $page_get_id_val ) ) 
	{
		$namingError = true;
		$title .= '_';
		$list['title'] = $title;
		$message['inline']['title'] = array ('type' => 'errorMsg', 'msg' => 'Title altered to be unique');		
	}
	
    while ( array_key_exists($url, $catUrls) && ($_GET['option'] == 'create' || $catUrls[$url]['id'] != $page_get_id_val ) ) 
	{
		$namingError = true;
		$url .= '_';
		$list['url'] = $url;
		$message['inline']['url'] = array ('type' => 'errorMsg', 'msg' => 'Url altered to be unique');
	}
	
	// if renaming occured, set alerts
	if ($namingError)
	{
		$errorMsgType = 'errorMsg';
		$errorType = 'error';
		$errorHeading = 'Naming Error';
		$errorMessage = 'category name or url already exists, saved as draft with modified names';
	}
	
	
	// if an error was found, create the error banner
	if ($errorsExist = count(isset($message['inline']) ? $message['inline'] : array()))
	{
		$message['banner'] = array ('heading' => $errorHeading, 'message' => $errorMessage, 'type' => $errorType);
		$status = $list['status'] = 0; // saving as draft
	}
	else // set the success message
	{
		$message['banner'] = array ('heading' => 'Successfully Saved', 'type' => 'success');
	}

	// save even if errors exist: but save as draft
	{
		if (array_key_exists('option', $_GET) && ($_GET['option'] == 'create'))
		{
			$result = logged_query("INSERT INTO `{$table}` (`title`, `url`, `$mce_content_name`, `events_per_pg`, `status`, `seo_description`, `seo_keywords`, `seo_title`) 
	VALUES (:title, :url, :mce_content_name, :events_per_pg, :status, :seo_description, :seo_keywords, :seo_title);",0,array(
				":title" => $title, 
				":url" => $url, 
				":mce_content_name" => $$mce_content_name, 
				":events_per_pg" => $events_per_pg, 
				":status" => $status, 
				":seo_description" => $seo_description, 
				":seo_keywords" => $seo_keywords, 
				":seo_title" => $seo_title
			)); 
			
            $page_get_id_val = $_config['db']->getLastInsertId();
			
			if($result !== false && isset($color)) {
				$result = logged_query("INSERT INTO `events_colors` (`cat_id`, `color`) VALUES (:page_get_id_val,:color);",0,array(
					":page_get_id_val" => $page_get_id_val,
					":color" => $color
				));
			}
			
			// banners: if there was an error, overwrite the previously set success message
			if ($result === false)	
				$message['banner'] = array (
					'heading' => 'Error Saving Category', 
					'message' => 'there was an error writing to the database', 
					'type' => 'error' 
				);
			
			
			// successfully created the category: no longer a new category!
			$newCat = false;
		}
		if (array_key_exists('option', $_GET) && ($_GET['option'] == 'edit'))
		{
            $page_get_id_val = $_GET[$page_get_id];

			$result = logged_query("UPDATE `{$table}` SET 
			`title` = :title, 
			`url` = :url,
			`$mce_content_name` = :mce_content_name,
            `events_per_pg` = :events_per_pg,
			`status` = :status,
			`seo_description` = :seo_description,
			`seo_keywords` = :seo_keywords,
			`seo_title` = :seo_title
			WHERE `id` = :page_get_id_val LIMIT 1;",0,array(
				":title" => $title, 
				":url" => $url, 
				":mce_content_name" => $$mce_content_name, 
				":events_per_pg" => $events_per_pg,
				":status" => $status,
				":seo_description" => $seo_description, 
				":seo_keywords" => $seo_keywords, 
				":seo_title" => $seo_title,
				":page_get_id_val" => $page_get_id_val
			));
			
			
			if($result !== false && isset($color)) {
				$check = logged_query("SELECT * FROM events_colors WHERE cat_id = :page_get_id_val",0,array(":page_get_id_val" => $page_get_id_val));
				if($check && count($check) ) {
					$result = logged_query("UPDATE `events_colors` SET
					`color` = :color
					WHERE `cat_id` = :page_get_id_val;",0,array(":color" => $color, ":page_get_id_val" => $page_get_id_val));
				} else {
					$result = logged_query("INSERT INTO `events_colors` (`cat_id`, `color`) VALUES (:page_get_id_val, :color);",0,array(
						":color" => $color, 
						":page_get_id_val" => $page_get_id_val
					));
				}
			}
			
			// banners
			// banners: if there was an error, overwrite the previously set success message
			if ($result === false)	$message['banner'] = array ('heading' => 'Error Saving Category', 'message' => mysql_error(), 'type' => 'error' );
		}
	}
	
}

if (! isset($message)) $message=array();

// title
$input_title = new inputField( 'Title', 'title' );	
$input_title->toolTip('Title as it appears in the events category list.');
$input_title->value( htmlspecialchars_decode(isset($list['title']) ? $list['title'] : '') );
$input_title->counterMax(100);
$input_title->size('small');
$input_title->arErr($message);

// color
$input_color = new inputField( 'Category Color', 'color' );
$input_color->toolTip('Color associated with this category on calendar and events list.');
$input_color->value( isset($color) ? $color : '#ffffff');
$input_color->size('tiny');
$input_color->arErr($message);

// url	
$input_url = new inputField( 'URL', 'url' );	
$input_url->toolTip('Title as it appears in the url:<br />Use only letters, numbers, underscores and hyphens. No Spaces!');
$input_url->value( htmlspecialchars_decode(isset($list['url']) ? $list['url'] : '') );
$input_url->counterMax(100);
$input_url->size('small');
$input_url->arErr($message);	

// events per page
$input_events_per_pg = new inputField( 'Events Per Page', 'events_per_pg' );	
$input_events_per_pg->toolTip('The number of events displayed per page with this category in the events sidebar in the frontend');
$input_events_per_pg->type('select');
$input_events_per_pg->selected(isset($list['events_per_pg']) ? $list['events_per_pg'] : '');
$input_events_per_pg->size('tiny');
$input_events_per_pg->option( 1, '1' );
$input_events_per_pg->option( 2, '2' );
$input_events_per_pg->option( 3, '3' );
$input_events_per_pg->option( 4, '4' );
$input_events_per_pg->option( 5, '5' );
$input_events_per_pg->arErr($message);

// status
$input_status = new inputField( 'Status', 'status' );	
$input_status->toolTip('A draft version can have blank fields. A published version must have all fields completed.');
$input_status->type('select');
$input_status->selected(isset($list['status']) ? $list['status'] : '');
$input_status->option( 0, 'Draft' );
$input_status->option( 1, 'Published' );
$input_status->arErr($message);

// seo title
$input_seo_title = new inputField( 'SEO Title', 'seo_title' );	
$input_seo_title->toolTip('Start with most important words<br />65 characters or less.');
$input_seo_title->value( htmlspecialchars_decode(isset($list['seo_title']) ? $list['seo_title'] : '') );
$input_seo_title->counterWarning(65);
$input_seo_title->counterMax(100);
$input_seo_title->size('large');
$input_seo_title->arErr($message);

// seo description
$input_seo_description = new inputField( 'Description', 'seo_description' );	
$input_seo_description->toolTip('Start with the same words used in the title<br />150 characters or less.');
$input_seo_description->type('textarea');
$input_seo_description->value( htmlspecialchars_decode(isset($list['seo_description']) ? $list['seo_description'] : '') );
$input_seo_description->counterWarning(150);
$input_seo_description->counterMax(250);
$input_seo_description->size('large');
$input_seo_description->arErr($message);

// seo seo_keywords
$input_seo_keywords = new inputField( 'Keywords', 'seo_keywords' );	
$input_seo_keywords->toolTip('List of phrases, separated by commas, with most important phrases first. <br /> Note: the words have to appear somewhere on the page.');
$input_seo_keywords->type('textarea');
$input_seo_keywords->value( htmlspecialchars_decode(isset($list['seo_keywords']) ? $list['seo_keywords'] : '') );
$input_seo_keywords->counterMax(1000);
$input_seo_keywords->size('large');
$input_seo_keywords->arErr($message);
   
  
// set the header varables and create the header
$pageResources ="
<link rel='stylesheet' type='text/css' href='style.css' />
<link rel='stylesheet' type='text/css' href='colorPicker.css' />
<script type=\"text/javascript\" src=\"".$_config['admin_url']."modules/events/js/events_category_new.js\"></script>
<script type=\"text/javascript\" src=\"".$_config['admin_url']."modules/events/js/jquery.colorPicker.js\"></script>
";
$pageInit->createPageTop($pageResources);

 ?>
<script>
$(document).ready( function() {
	$(color).colorPicker({showHexField: false});
});
</script>
 <div class="page_container">
	<div id="h1"><h1>Events Category</h1></div>
    <div id="info_container">
		<?php
		// ----------------------------------------subnav--------------------------------
		$selectedEvents = '';
		$selectedCats = 'tabSel';
		$selectedOpts = '';
		include("inc/subnav.php"); 
		echo '<hr />';
		//---------------------------------------Error Banner----------------------------- 
		// create a banner if $message['banner'] exists
		createBanner($message); 

		$parms = "?{$page_get_id}={$page_get_id_val}&option=edit";
		?>
		<form action="<?php echo htmlentities($_SERVER['PHP_SELF']); ?><?php echo $parms; ?>" method="post" enctype="application/x-www-form-urlencoded" name="addcat" id="addcat" class="form">
            <input type="hidden" name="page_get_id_val" id="page_get_id_val" value="<?php echo $page_get_id_val; ?>" />
  
			<!--  properties_wrap -->
			<h2 id="prop-toggle" class="tiptip toggle" title="Information about the category.">Category Properties</h2><br />
			<div id="prop-toggle-wrap">
			<?php
				$input_title->createInputField();
				$input_url->createInputField();
				$input_events_per_pg->createInputField();
				$input_status->createInputField();
				$input_color->createInputField();
			?>
			</div><!-- end prop_wrap -->	   

             <!-- content area -->
			<h2 id="content-toggle" class="tiptip toggle" title="The actual content displayed for the category.">Category Description</h2>
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
			
			<!-- SEO area -->
			<h2 class="tiptip toggle" id="seo-toggle" title="Search Engine Optimization fields">Meta Tags</h2><br />
			<div id="seo-toggle-wrap">
			<?php
				$input_seo_title->createInputField();
				$input_seo_description->createInputField();
				$input_seo_keywords->createInputField();
			?>
			</div><!-- end SEO area -->
                       
                            
			<!-- page buttons -->
			<div class='clearFix' ></div>
			<input name="submit-cat" type="hidden" value="submit" />
		</form>	
		<a id="submit-btn" class="blue button" href="#">Save</a>

		<a class="grey button" id="cancel-btn" href="<?php echo $parent_page; ?>">Cancel</a>
		<div class='clearFix' ></div> 
		<!-- end page buttons -->
	</div>
</div>	
	

<?php 

include($_config['admin_includes'] . "footer.php"); ?>
