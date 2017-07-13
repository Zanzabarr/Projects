<?php 
// initialize the page
$headerComponents = array();
$headerModule = 'blog';
include('../../includes/headerClass.php');
$pageInit = new headerClass($headerComponents,$headerModule);

// access user data from headerClass
global $curUser;

// set the db variables
$table 				= 'blog_cat';		// name of the table this page posts to
$page_type			= 'blog_cat';		// name of uploadPageType for uploads component, also used to differentiate kinds of delete on parent page
$parent_page		= 'blog.php';		// the page that calls this one: used for delete and cancel form buttons, sets where to go afterwards
$page_get_id		= 'catid';			// the GET key passed forward with the db id for this item(page/blog post/...)
$page_get_id_val	= '';				// this variable is used to store the GET id value of above

$newPost	 		= false;			// records that this is not a new Post/Page 

if ( array_key_exists('option', $_GET) && ($_GET['option'] == 'create') ) 
{
	$newPost = true;
}

// if this is an edit of an existing post, get the post info
elseif (array_key_exists($page_get_id, $_GET) && is_numeric($_GET[$page_get_id]) && ! array_key_exists('option', $_GET) ) {
	$list = logged_query("SELECT * FROM {$table} WHERE id = {$_GET[$page_get_id]} ORDER BY id DESC LIMIT 1",0,array());
	$list = $list[0];
	$page_get_id_val = $_GET[$page_get_id];
}
// if we aren't editing or creating either, we shouldn't be here
elseif (! array_key_exists('option', $_GET) ) 
{
	header( "Location: " . $_config['admin_url'] . "modules/blog/". $parent_page );
	exit;
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
		${$k} = trim(htmlspecialchars($v, ENT_QUOTES));
		$list[$k] = ${$k};
		if (${$k} == '' && $k != 'submit-post' && $k != $page_get_id) {$message['inline'][$k] = array ('type' => $errorMsgType, 'msg' => 'Required field');}
	}
	
	// validate uniqueness of url / title
	//get all existing categories
	$categories = logged_query_assoc_array("SELECT `id`, `user_id`, `url`, `title` FROM `blog_cat`",null,0,array());
	$catUrl = array();
	$catTitle = array();
	foreach ($categories as $category)
	{
		$catUrl[$category['url']] = $category;
		$catTitle[$category['title']] = $category;
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
		$errorMessage = 'category name or url already exists, saved as draft with modified names';
	}
	
	
	// if an error was found, create the error banner
	$errorsExist = isset($message['inline']) ? count($message['inline']) : 0 ;
	if ($errorsExist )
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
			$result = logged_query("INSERT INTO `{$table}` (`user_id`, `title`, `seo_title`, `seo_keywords`, `seo_description`, `date`, `status`, `url`) 
						VALUES (:id , :title, :seo_title, :seo_keywords, :seo_description, UTC_TIMESTAMP(), :status, :url);",0,
						array(
							":id" => $curUser['user_id'],
							":title" => $title,
							":seo_title" => $seo_title,
							":seo_keywords" => $seo_keywords,
							":seo_description" => $seo_description,
							":status" => $status,
							":url" => $url
						)); 
			$page_get_id_val = $_config['db']->getLastInsertId();

			// banners: if there was an error, overwrite the previously set success message
			if ($result === false)	$message['banner'] = array ('heading' => 'Error Saving Category', 'message' => 'there was an error writing to the database', 'type' => 'error' );
			
			
			// successfully created the page: no longer a new page!
			$newPost = false;
		}
		if (array_key_exists('option', $_GET) && ($_GET['option'] == 'edit'))
		{
			$result = logged_query("UPDATE `{$table}` SET 
			`user_id` = :user_id ,
			`title` = :title,
			`seo_title` = :seo_title,
			`seo_keywords` = :seo_keywords,
			`seo_description` = :seo_description,
			`date` = UTC_TIMESTAMP(),
			`status` = :status,
			`url` = :url WHERE `id` = :page_get_id_val LIMIT 1;",0,array(
				":user_id" => $curUser['user_id'],
				":title" => $title,
				":seo_title" => $seo_title,
				":seo_keywords" => $seo_keywords,
				":seo_description" => $seo_description,
				":status" => $status,
				":url" => $url,
				":page_get_id_val" => $page_get_id_val
			));

			// banners: if there was an error, overwrite the previously set success message
			if ($result===false)	$message['banner'] = array ('heading' => 'Error Saving Category', 'message' => 'there was an error writing to the database', 'type' => 'error' );
		}
	}
	
// $_SESSION['SESS_MSG_green'] = "Blog Updated";

}
if (! isset($message)) $message=array();
// page title
$val = isset($list['title']) ? htmlspecialchars_decode($list['title']) : '';
$input_blog_title = new inputField( 'Category Title', 'title' );	
$input_blog_title->toolTip('Title as it appears in the blog.');
$input_blog_title->value( $val );
$input_blog_title->counterMax(100);
$input_blog_title->size('small');
$input_blog_title->arErr($message);

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
$pageResources ="
<link rel='stylesheet' type='text/css' href='style.css' />
<script type=\"text/javascript\" src=\"".$_config['admin_url']."modules/blog/js/blog_category.js\"></script>
";
$pageInit->createPageTop($pageResources);

$usedSlugs = array();  // array of slugs that have already been used
$slugs=logged_query("SELECT `url` FROM blog_cat WHERE id > 0",0,array());

if(count($slugs)>0) {
	foreach($slugs as $slug) {
		$usedSlugs[] = $slug['url'];
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
	<div id="h2"><h2>Blog Category</h2></div>
    <div id="info_container">
		<?php
		// ----------------------------------------subnav--------------------------------
		$selectedOpts = '';
		$selectedPosts = '';
		$selectedCats = 'tabSel';
		include("blog/subnav.php"); 
		echo '<hr />';
		//---------------------------------------Error Banner----------------------------- 
		// create a banner if $message['banner'] exists
		createBanner($message); 

		$parms = $newPost ? '?option=create' : "?{$page_get_id}={$page_get_id_val}&option=edit";
		?>
		<form action="<?php echo htmlentities($_SERVER['PHP_SELF']); ?><?php echo $parms; ?>" method="post" enctype="application/x-www-form-urlencoded" name="form_data" id="form_data" class="form">
  
			<!--  properties_wrap -->
			<h2 id="prop-toggle" class="tiptip toggle" title="Information about the page.">Category Properties</h2><br />
			<div id="prop-toggle-wrap">
			<?php
				$input_blog_title->createInputField();
				$input_url->createInputField();
				$input_status->createInputField();
			?>
			</div><!-- end prop_wrap -->	
			
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
		<hr />
	</div>
</div>	
	

<?php 

include($_config['admin_includes'] . "footer.php"); ?>