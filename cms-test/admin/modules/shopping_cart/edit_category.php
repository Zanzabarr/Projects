<?php 

// INITIALIZE the category
$headerComponents = array('revisions');
$headerModule = "shopping_cart";
include_once('../../includes/headerClass.php');

// must have admin privileges to access this page
if ($_SESSION['user']['admin'] != 'yes')
{
	header( "Location: " . $_config['admin_url'] . "modules/{$headerModule}/index.php" );
	exit;
}

$pageInit = new headerClass($headerComponents, $headerModule);
$pageResources ="
<link rel='stylesheet' type='text/css' href='styles.css' />
<script type=\"text/javascript\" src=\"".$baseUrl."modules/shopping_cart/js/edit_category.js\"></script>
";
$pageInit->createPageTop($pageResources);
$baseUrl = $_config['admin_url'];

// access user data from headerClass
global $curUser;
$isAdmin = $curUser['admin'] == 'yes';


// lets start playing with new class
$objCategory = new ecom_category();
$objCategory->create_nested_table();
$objCategory->create_ecom_category_data_table();

// VARIABLES
$message = array(); 	// initialize error message

// this page has revisions, instaniate it
include_once($_config['components']."revisions/revisions.php");
$Revisions = new revisions('ecom_category_data_rev','ecom_cat_data_id')	;
$Revisions->setOrderByField('date_updated');

// we have arrived here in one of three ways: 
//		creating new page from pages,
//		loading a revision from this current item
//		loading an existing page from pages
if ( array_key_exists('option', $_GET) && $_GET['option'] == "new_cat" )
{
	// create the default one-up category name
	$result = logged_query('
		SELECT MAX( CAST( SUBSTRING( `url` , 13 ) AS Unsigned ) ) AS num
		FROM `ecom_category_data`
		WHERE `id` >0
		AND `url` REGEXP "^newcategory_[0-9]*$"',0,array()
	);
	$slug_num = is_array($result) && count($result) ? (int)$result[0]['num'] : 0;
	$tmp_slug = 'newcategory_' . ++$slug_num;
	
	// save initial url and position in `ecom_category`
	//		also creates entry in `ecom_category_data`
	$parent_id 			= 0;
	$new_cat_id 		= 0;
	$has_sibling 		= $_config['db']->count("
		SELECT 1 FROM dual
		WHERE EXISTS( SELECT * FROM `ecom_category`)
	");
	$after_is_parent 	= 1;
	$after_id 			= 0;
	$cat = array(				
		"url" => $tmp_slug,
		"name" => $tmp_slug,
		"desc" => '',
		"seo_keywords" => '',
		"seo_description" => '',
		"seo_title" => ''
	);
	$cat_id = $objCategory->maintain_category($parent_id, $new_cat_id, $has_sibling, $after_is_parent, $after_id, $cat);
	
	// get the completed category data
	$cat = logged_query("SELECT * FROM `ecom_category_data` WHERE `id` =:cat_id",0,array(":cat_id" => $cat_id));
	$cat = $cat[0];
} 	
// if this arrived from revision history, get info
elseif ( $Revisions->isRevision() ) {

	// $cat_id is used later in the page, must be maintained consistantly across all three cases
	$cat_id = trim($_GET['cat_id']);
	$revise_id = trim($_POST['revise_id']);

	$cat = $Revisions->getRevisionData($cat_id,$revise_id);
	$message = $Revisions->getResultMessage();
}
// if this is an edit of an existing page, get the page info
elseif (array_key_exists('cat_id', $_GET) && is_numeric($_GET['cat_id']) ) {
		
	$cat_id = trim($_GET['cat_id']);
	/* Grabs Page Info from Database */
	$cat=logged_query_assoc_array("SELECT * FROM ecom_category_data WHERE id = {$cat_id}",null,0,array());
	$cat = $cat[0];

}
// if we aren't editing or creating either, we shouldn't be here
elseif ( $_GET['option'] != "edit" ) 
{
	header( "Location: " . $baseUrl . "modules/shopping_cart/index.php" );
	exit;
}	

// is page being saved?
if ( isset($_GET['option']) && $_GET['option'] == "edit"  )
{

	// get inputs
	$cat['url'] = $url = trim(htmlspecialchars($_POST['url'],ENT_QUOTES));	
	$cat['name'] = $name = trim(htmlspecialchars($_POST['name'],ENT_QUOTES));
	$cat['desc'] = $desc = htmlspecialchars($_POST['content'],ENT_QUOTES);
	$cat['seo_keywords'] =$seo_keywords = trim(htmlspecialchars($_POST['seo_keywords'],ENT_QUOTES));
	$cat['seo_description'] =$seo_description = trim(htmlspecialchars($_POST['seo_description'],ENT_QUOTES));	
	$cat['seo_title'] =$seo_title = trim(htmlspecialchars($_POST['seo_title'],ENT_QUOTES));
	$cat['status'] =$status = trim(htmlspecialchars($_POST['status'],ENT_QUOTES));
	
	// check if this category has a page
	$checkpage = logged_query("SELECT id, page_title FROM pages WHERE id > 0 && status = 1 && slug = 'shopping/category/{$cat['url']}'",0,array());
	$hasPage = !empty($checkpage) ? true : false;
	
	$checkdraft = logged_query("SELECT id, slug, page_title FROM pages WHERE id > 0 && status = 0 && slug = '--shopping/category/{$cat['url']}' LIMIT 1",0,array());
	$hasDraft = !empty($checkdraft) ? true : false;
	
	$pageConnected = false;
	if($cat['status'] == 1 && $hasDraft) {
		$relatedPageTitle = $checkdraft[0]['page_title'];
		$newSlug = substr($checkdraft[0]['slug'],2);
		$connectPage = logged_query("UPDATE pages SET status = 1, slug = '{$newSlug}' WHERE id = {$checkdraft[0]['id']}",0,array());
		if($connectPage !== false) $pageConnected = true;
	}
	
	$catDrafted = false;
	if($cat['status'] == 0 && $hasPage) {
		$cat['status'] = $status = 1;
		$relatedPageTitle = $checkpage[0]['page_title'];
		$catDrafted = true;
	}
	
	// validate:
	//	pick error type/messages based on if its status is draft or published
	if ($status == 1) // published: thus error
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
	
	// validation tests
	if ($seo_title == '') {$message['inline']['seo_title'] = array ('type' => $errorMsgType, 'msg' => 'Required field');}
	if ($name == '') {$message['inline']['name'] = array ('type' => $errorMsgType, 'msg' => 'Required field');}

	
	if ($seo_keywords == '') {$message['inline']['seo_keywords'] = array ('type' => $errorMsgType, 'msg' => 'Required field');}
	if ($desc == '') {$message['inline']['desc'] = array ('type' => $errorMsgType, 'msg' => 'Please enter Category Description below.');}
	if ($seo_description == '') {$message['inline']['seo_description'] = array ('type' => $errorMsgType, 'msg' => 'Required field');}
	
// TODO validate uniqueness of url if the posted url doesn't equal the page's url

	//if (validateUrl( $cat['url'] ) ) die( $cat['url'] );

//TODO validate category posn

	// if there are any errors, set the banner message: we always save the changes, but if there are errors: save as draft
	if (isset ($message['inline']) && count($message['inline']))
	{
		$message['banner'] = array ('heading' => $errorHeading, 'message' => $errorMessage, 'type' => $errorType);
		$status = $cat['status'] = 0; // saving as draft
	}
	else // set the success message
	{
		if($catDrafted || $pageConnected) {
			if($catDrafted) {
				$message['banner'] = array ('heading' => 'Successfully Saved', 'message' => "with Status: Published. This Category has a related page and cannot be made Draft.<br />Please make the page '{$relatedPageTitle}' Draft, or delete this category entirely.", 'type' => 'warning');
			}
			elseif($pageConnected) {
				$message['banner'] = array ('heading' => 'Successfully Saved', 'message' => "Page '{$relatedPageTitle}' has been re-connected to this category.", 'type' => 'success');
			}
		} else {
			$message['banner'] = array ('heading' => 'Successfully Saved', 'type' => 'success');
		}
	}
	// save edit (as draft if errors existed)
	
	//here we go!
	$parent_id 			= $_POST['cur_parent_id'];
	$new_cat_id 		= $_POST['new_cat_id'];
	$has_sibling 		= $_POST['has_sibling'];
	$after_is_parent 	= $_POST['after_is_parent'];
	$after_id 			= $_POST['after_id'];
	$cat['id'] = $objCategory->maintain_category($parent_id, $new_cat_id, $has_sibling, $after_is_parent, $after_id, $cat);
	

	if($_GET['option'] == "edit") {
		$update = "UPDATE ecom_category_data SET `url` = :url, `name` = :name, `desc` = :desc, `seo_keywords` = :seo_keywords, `seo_description` = :seo_description, `seo_title` = :seo_title, `status` = :status, `date_created` = :date_created, `date_updated` = UTC_TIMESTAMP() WHERE `id` = :cat_id LIMIT 1;";
		logged_query($update,0,array(
			":cat_id" => $cat['id'],
			":url" => $url,
			":name" => $name,
			":desc" => $desc,
			":seo_keywords" => $seo_keywords,
			":seo_description" => $seo_description,
			":seo_title" => $seo_title,
			":status" => $status,
			":date_created" => $cat['date_created']
		));
		
		$revision = "INSERT INTO ecom_category_data_rev (`ecom_cat_data_id`, `url`, `name`, `desc`, `seo_keywords`, `seo_description`, `seo_title`, `status`, `date_created`, `date_updated`) VALUES (:cat_id, :url, :name, :desc, :seo_keywords, :seo_description, :seo_title, :status,  UTC_TIMESTAMP(), UTC_TIMESTAMP());";
		logged_query($revision,0,array(
			":cat_id" => $cat['id'],
			":url" => $url,
			":name" => $name,
			":desc" => $desc,
			":seo_keywords" => $seo_keywords,
			":seo_description" => $seo_description,
			":seo_title" => $seo_title,
			":status" => $status
		));
	}

}

// prepare all input fields

// seo title
$val = isset($cat['seo_title']) ? htmlspecialchars_decode($cat['seo_title']) : '';
$input_seo_title = new inputField( 'SEO Title', 'seo_title' );	
$input_seo_title->toolTip('Start with the most important words.');
$input_seo_title->value( $val  );
$input_seo_title->counterWarning(65);
$input_seo_title->counterMax(100);
$input_seo_title->size('large');
$input_seo_title->arErr($message);

// seo description
$val = isset($cat['seo_description']) ? htmlspecialchars_decode($cat['seo_description']) : '';
$input_seo_description = new inputField( 'Description', 'seo_description' );	
$input_seo_description->toolTip('Start with the same words used in the title.');
$input_seo_description->type('textarea');
$input_seo_description->value(  $val);
$input_seo_description->counterWarning(150);
$input_seo_description->counterMax(250);
$input_seo_description->size('large');
$input_seo_description->arErr($message);


// seo description
$val = isset($cat['seo_keywords']) ? htmlspecialchars_decode($cat['seo_keywords']) : '';
$input_seo_keywords = new inputField( 'Keywords', 'seo_keywords' );	
$input_seo_keywords->toolTip('List of phrases, separated by commas, with most important phrases first. <br /> Note: the words have to appear somewhere on the page.');
$input_seo_keywords->type('textarea');
$input_seo_keywords->value(  $val );
$input_seo_keywords->counterMax(1000);
$input_seo_keywords->size('large');
$input_seo_keywords->arErr($message);

// category name
$val = isset($cat['name']) ? htmlspecialchars_decode($cat['name']) : '';
$input_name = new inputField( 'Category Name', 'name' );	
$input_name->toolTip('Start with the most important words.');
$input_name->value(  $val );
$input_name->counterMax(100);
$input_name->size('small');
$input_name->arErr($message);

// url	
$val = isset($cat['url']) ? htmlspecialchars_decode($cat['url']) : '';
$input_url = new inputField( 'Url', 'url' );	
$input_url->toolTip('Title as it appears in the url:<br />Use only letters, numbers, underscores and hyphens. No Spaces!');
$input_url->value($val );
$input_url->counterMax(100);
$input_url->size('small');
$input_url->arErr($message);

// status
$val = isset($cat['status']) ? htmlspecialchars_decode($cat['status']) : '';
$input_status = new inputField( 'Status', 'status' );	
$input_status->toolTip('A draft version can have blank fields. A published version must have all fields completed.');
$input_status->type('select');
$input_status->selected( $val);
$input_status->option( 0, 'Draft' );
$input_status->option( 1, 'Published' );
$input_status->arErr($message);

// Menu Position
//TODO menuposn

//** create a list of reserved page names: **//
// can't be claimed system names like:	'images'...
// or customNames like: 				'blog/news', 'cart'...
// or config defined names like:		'admin_folder : admin', 'home_slug : home'
// or existing page names
// get system reserved slugs
$usedSlugs = array();
// get used slugs from ecom_category_data
$slugs=logged_query("SELECT `url` FROM ecom_category_data WHERE id > 0",0,array());
if($slugs && count($slugs) > 0) {
	foreach($slugs as $row) {
		$usedSlugs[] = $row['url'];
	}
}

?>
<script type="text/javascript">
	curSlug = "<?php echo isset($cat['url']) ? $cat['url'] : '';?>";
	usedSlugs = new Array();
<?php foreach($usedSlugs as $slug) : ?>
	usedSlugs.push("<?php echo $slug; ?>");
<?php endforeach; ?>
	
</script>
<div class="page_container">
	<div id="h1">
		<h1>
			<?php echo 'Edit Category - ' . $cat['name']; ?>
		</h1>
	</div>

	<div class="edit_container" style="margin-bottom: 15px">
		<span style="float:right; margin: -15px 15px 0 0;font-size: 12px"> <?php echo 'Page Url: ' . $cat['url']; ?> </span>
		
		<?php 
		// add subnav
		$pg = 'category';	include("includes/subnav.php");
		
		echo "<hr>";

		$formOpt = "option=edit&cat_id={$cat['id']}";
		?>
		<form action="edit_category.php?<?php echo $formOpt;?>" method="post" enctype="application/x-www-form-urlencoded" id="form-page" name="edititem">
			
			<?php //---------------------------------------Error Banner----------------------------- 
			// create a banner if $message['banner'] exists
			createBanner($message);
			
		?>

			<!-- properties area -->
			<h2 id="prop-toggle" class="tiptip toggle" title="Information about the page.">Category Properties</h2><br />
			<div id="prop-toggle-wrap">
			<?php
				$input_name->createInputField();

				$input_url->createInputField();
				
				// build the category selector
				$tmpParentId = $cat['id'];
				$tmpChildId = false;
				$objCategory->build_category_selector($tmpParentId, $tmpChildId);
				echo "<div class='clear'></div>";
				
				$input_status->createInputField();
			?>
				<p class="infop"><img class="info-icon" src="../../images/info.png" alt="info icon" /><em>If you want a page for this category on the menu, please save this category as status "Published". Then go to Pages and create a page. On that page, choose this category from the Special Pages selection.</em></p>
			</div><!-- end prop_wrap -->
			
			<!-- desc area -->
			<h2 id="content-toggle" class="tiptip toggle" title="The actual description displayed on the page.">Category Description</h2>
			<?php if (isset ($message['inline']) && array_key_exists('desc', $message['inline'])) :?>
				<span class="<?php echo $message['inline']['desc']['type'] ;?>"><?php echo $message['inline']['desc']['msg'] ;?> </span>
			<?php endif; ?>
			<br />
			
			<?php
			// create tinymce
			$editable = array(
				'editable_class' => 'mceUploadable',
				'attributes' => array(
					'name' => 'content',
					'id' => 'content',
					'data-jump-type' => "front",
					'data-jump' => "../../../shopping/category/".$cat['url']
				),
				'secure_data' => array(
					'id-field' => 'id',				// req for save && upload
					'id-val' => $cat['id'],	// req for save && upload
					'upload-type' => 'ecom_category_data'		// req for upload
				)
			);
			$wrapper = getContentWrapper($editable);
			echo $wrapper['open'];
			echo isset($cat['desc']) ? htmlspecialchars_decode($cat['desc']) : '' ;
			echo $wrapper['close'];
			?>
			<!-- end desc area -->
			
			<!-- SEO area -->
			<h2 class="tiptip toggle" id="seo-toggle" ;" title="Search Engine Optimization fields">Meta Tags</h2><br />

			<div id="seo-toggle-wrap">
				<?php
				// create the previously prepared input fields
				$input_seo_title->createInputField();
				$input_seo_description->createInputField();
				$input_seo_keywords->createInputField();
				?>    
			</div><!-- end SEO area -->
		</form>	
			<!-- page buttons -->
			<div class='clearFix' ></div>
				<a id="submit-btn" class="blue button" href="#">Save</a>
			
				<a class="grey button" id="cancel-btn" href="modules/shopping_cart/category.php">Cancel</a>
			<div class='clearFix' ></div> 
			<!-- end page buttons -->
		
		<?php // --------------------------- include the image gallery ---------------------------------
		/*if (isset($cat['id']) && $cat['id'] > 0)
		{
			// $uploadPage and uploadPageType must be set before including uploads (they are cat_id & page_type in tables: pictures & uploads
			$uploadPage = $cat['id'];
			$uploadPageType = 'pictures';
			include_once("{$_config['components']}uploading/uploads.php"); 
		} else {
			echo '<div class="clearFix"></div><h2 class="tiptip" id="image-toggle" style="color: grey;" title="New page must be saved before images can be added." >Page Images</h2>';
		}*/
		?>
		
		
		
<?php	// ----------------------------------- revisions -------------------------------------

		$extraPOST = array();
		// add GET data to the return url
		$extraGET = array('ecom_cat_data_id' => isset($cat['id']) ? $cat['id'] : '');
		// build the Revision Area
		$Revisions->createRevisionsArea(isset($cat['id']) ? $cat['id'] : '',$extraPOST,$extraGET);
		// end revisions
?>
		


	</div> <!-- end edit_container -->
 </div> <!-- end page_container -->

<?php include_once("../../includes/footer.php"); ?>