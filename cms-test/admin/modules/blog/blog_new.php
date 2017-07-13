<?php
// initialize the page
$headerComponents = array('revisions');
$headerModule = 'blog';
include('../../includes/headerClass.php');
$pageInit = new headerClass($headerComponents,$headerModule);

$uploadPath = $_config['upload_path'] . "blog_post/";
$uploadUrl	= $_config['upload_url'] . "blog_post/";
$tableName	= 'blog_post';
$target_id_name = 'id';
$target_image_name = 'image_name';
$target_image_alt = 'image_alt';
$file_name = "blog_post";
$default_image_path = "/admin/modules/blog/images/default_post_image/";
$default_image_name = 'blog_post_dflt.jpg';
$default_image_alt = 'Blog Image';
$uploader = new single_image_upload($uploadPath, $uploadUrl, $tableName, $target_id_name, $target_image_name, $target_image_alt,$file_name,$default_image_path,$default_image_name,$default_image_alt);

$baseUrl = $_config['admin_url'];
global $curUser;

// this page has revisions, instaniate it
include_once($_config['components']."revisions/revisions.php");
$Revisions = new revisions('blog_post_rev', 'post_id')	;

$newPost = false;
if ( array_key_exists('option', $_GET) && ($_GET['option'] == 'create') )
{
	$newPost = true;
	$result = logged_query('
		SELECT MAX( CAST( SUBSTRING( `url` , 5 ) AS Unsigned ) ) AS num
		FROM `blog_post`
		WHERE `id` >0
		AND `url` REGEXP "^new_[0-9]*$"',0,array()
	);
	$slug_num = is_array($result) && count($result) ? (int)$result[0]['num'] : 0;
	$tmp_slug = 'new_' . ++$slug_num;
	$result = logged_query("INSERT INTO `blog_post` SET user_id=:user_id, title=:tmp_slug, url=:tmp_slug",0,array(":tmp_slug" => $tmp_slug, ":user_id" => $curUser['user_id']));
	$blogpost_id = $_config['db']->getLastInsertId();
	$list = logged_query("SELECT * FROM `blog_post` WHERE `id` =:blogpost_id",0,array(":blogpost_id" => $blogpost_id));
	$list = $list[0];

}
// if this arrived from revision history, get info
elseif ( $Revisions->isRevision() )
{
	$blogpost_id = $_GET['blogid'];
	$list = $Revisions->getRevisionData($blogpost_id);
	$message = $Revisions->getResultMessage($blogpost_id);
}
// if this is an edit of an existing post, get the post info
elseif (array_key_exists('blogid', $_GET) && is_numeric($_GET['blogid']) && ! array_key_exists('option', $_GET) ) {
	$list = logged_query("SELECT * FROM blog_post WHERE id = {$_GET['blogid']} AND `user_id` = '{$curUser['user_id']}' ORDER BY id DESC LIMIT 1",0,array());
	$list = $list[0];
	$cats = explode(".", $list['cate']);

	$blogpost_id = $_GET['blogid'];
}
// if we aren't editing or creating either, we shouldn't be here
elseif (! array_key_exists('option', $_GET) )
{
	header( "Location: " . $_config['admin_url'] . "modules/blog/blog.php" );
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
		if($k == 'content') ${$k} = trim(htmlspecialchars($v, ENT_QUOTES));
		elseif($k == 'intro') ${$k} = trim(htmlspecialchars($v, ENT_QUOTES));
		elseif($k == 'title') ${$k} = trim(htmlspecialchars($v, ENT_QUOTES));
		elseif($k == 'seo_title') ${$k} = trim(htmlspecialchars($v, ENT_QUOTES));
		elseif($k == 'cate') ${$k} = $v;
		else ${$k} = trim($v);
		$list[$k] = ${$k};
		if (${$k} == '' && $k != 'cate' && $k != 'submit-post' && $k != 'blogpost_id') {$message['inline'][$k] = array ('type' => $errorMsgType, 'msg' => 'Required field');}
	}
	$list['post'] = $list['content'];

	if (isset($_POST['featured']) && $_POST['featured'] == '1') {
		$list['featured'] = $featured = 1;
    }
    else {
		$list['featured'] = $featured = 0;
    }

	// validate uniqueness of url / title
	//get all existing categories
	$categories = logged_query_assoc_array("SELECT `id`, `user_id`, `url`, `title` FROM `blog_post`",null,0,array());
	$catUrl = array();
	$catTitle = array();
	foreach ($categories as $category)
	{
		$catUrl[$category['url']] = $category;
		$catTitle[$category['title']] = $category;
	}
	// while not unique, append!
	$nameingError = false;
	while ( array_key_exists($title, $catTitle) && ($_GET['option'] == 'create' || $catTitle[$title]['id'] != $blogpost_id ) )
	{
		$namingError = true;
		$title .= '_';
		$list['title'] = $title;
		$message['inline']['title'] = array ('type' => 'errorMsg', 'msg' => 'Title altered to be unique');
	}
		$nameingError = false;
	while ( array_key_exists($url, $catUrl) && ($_GET['option'] == 'create' || $catUrl[$url]['id'] != $blogpost_id ) )
	{
		$namingError = true;
		$url .= '_';
		$list['url'] = $url;
		$message['inline']['url'] = array ('type' => 'errorMsg', 'msg' => 'Url altered to be unique');
	}

	// if renaming occured, set alerts
	if (isset($namingError) && $namingError)
	{
		$errorMsgType = 'errorMsg';
		$errorType = 'error';
		$errorHeading = 'Naming Error';
		$errorMessage = 'post title or url already exists, saved as draft with modified names';
	}

	// if an error was found, create the error banner
	$errorsExist = isset($message['inline']) ? count($message['inline']) : false;
	if ($errorsExist)
	{
		$message['banner'] = array ('heading' => $errorHeading, 'message' => $errorMessage, 'type' => $errorType);
		$status = $list['status'] = 0; // saving as draft

		if (array_key_exists('blog_id', $_GET) && is_numeric($_GET['blog_id']) ) $blogpost_id = $_GET['blog_id'];
	}
	else // set the success message
	{
		$message['banner'] = array ('heading' => 'Successfully Saved', 'type' => 'success');
	}
	// set category data
	$cats = array();
	foreach($_POST['cate'] as $c=>$i){
	array_push($cats, $i);
	}
	$cleancate = '.';
	$cleancate .= implode(".", $cats);
	$cleancate .=  '.';

	// save even if errors exist: but save as draft
	{
		if (array_key_exists('option', $_GET) && ($_GET['option'] == 'create'))
		{
			$result = logged_query("INSERT INTO `blog_post` (`user_id`, `title`, `intro`, `post`, `cate`, `date`, `status`, `featured`, `seo_description`, `seo_keywords`, `seo_title`, `url`) VALUES (:user_id, :title, :intro, :content, :cleancate, UTC_TIMESTAMP(), :status, :featured, :seo_description, :seo_keywords, :seo_title, :url);",0,array(
				":user_id" => $curUser['user_id'],
				":title" => $title,
				":intro" => $intro,
				":content" => $content,
				":cleancate" => $cleancate,
				":status" => $status,
				":featured" => $featured,
				":seo_description" => $seo_description,
				":seo_keywords" => $seo_keywords,
				":seo_title" => $seo_title,
				":url" => $url
			));

			$blogpost_id = $_config['db']->getLastInsertId();

			if($result !== false)
			{
				$result = logged_query("INSERT INTO `blog_post_rev` (`user_id`, `post_id`, `title`, `intro`, `post`, `cate`, `date`, `status`, `featured`, `seo_description`, `seo_keywords`, `seo_title`, `url`) VALUES (:user_id, :blogpost_id, :title, :intro, :content, :cleancate, UTC_TIMESTAMP(), :status, :featured, :seo_description, :seo_keywords, :seo_title, :url);",0,array(
					":user_id" => $curUser['user_id'],
					":blogpost_id" => $blogpost_id,
					":title" => $title,
					":intro" => $intro,
					":content" => $content,
					":cleancate" => $cleancate,
					":status" => $status,
					":featured" => $featured,
					":seo_description" => $seo_description,
					":seo_keywords" => $seo_keywords,
					":seo_title" => $seo_title,
					":url" => $url
				));
			}

			// banners: if there was an error, overwrite the previously set success message
			if ($result === false)	$message['banner'] = array ('heading' => 'Error Saving Post', 'message' => 'there was an error writing to the database', 'type' => 'error' );


			// successfully created the page: no longer a new page!
			$newPost = false;
		}
		if (array_key_exists('option', $_GET) && ($_GET['option'] == 'edit'))
		{
			$result = logged_query("UPDATE `blog_post` SET
			`title` = :title,
			`intro` = :intro,
			`post` = :content,
			`cate` = :cleancate,
			`date` = UTC_TIMESTAMP(),
			`seo_description` = :seo_description,
			`seo_keywords` = :seo_keywords,
			`seo_title` = :seo_title,
			`status` = :status,
			`featured` = :featured,
			`url` = :url WHERE `id` = :blogpost_id AND `user_id` = :user_id LIMIT 1;",0,array(
				":user_id" => $curUser['user_id'],
				":blogpost_id" => $blogpost_id,
				":title" => $title,
				":intro" => $intro,
				":content" => $content,
				":cleancate" => $cleancate,
				":status" => $status,
				":featured" => $featured,
				":seo_description" => $seo_description,
				":seo_keywords" => $seo_keywords,
				":seo_title" => $seo_title,
				":url" => $url
			));

			if($result !== false)
			{
				$result = logged_query("INSERT INTO `blog_post_rev` (`user_id`, `post_id`, `title`, `intro`, `post`, `cate`, `date`, `status`, `featured`, `seo_description`, `seo_keywords`, `seo_title`, `url`) VALUES (:user_id, :blogpost_id, :title, :intro, :content, :cleancate, UTC_TIMESTAMP(), :status, :featured, :seo_description, :seo_keywords, :seo_title, :url);",0,array(
					":user_id" => $curUser['user_id'],
					":blogpost_id" => $blogpost_id,
					":title" => $title,
					":intro" => $intro,
					":content" => $content,
					":cleancate" => $cleancate,
					":status" => $status,
					":featured" => $featured,
					":seo_description" => $seo_description,
					":seo_keywords" => $seo_keywords,
					":seo_title" => $seo_title,
					":url" => $url
				));
			}

			// banners
			// banners: if there was an error, overwrite the previously set success message
			if ($result===false)	$message['banner'] = array ('heading' => 'Error Saving Post', 'message' => 'there was an error writing to the database', 'type' => 'error' );
		}
	}

// $_SESSION['SESS_MSG_green'] = "Blog Updated";

}
if (! isset($message)) $message=array();
// page title
$val = isset($list['title']) ? htmlspecialchars_decode($list['title']) : '';
$input_blog_title = new inputField( 'Blog Title', 'title' );
$input_blog_title->toolTip('Title as it appears in the blog.');
$input_blog_title->value( $val );
$input_blog_title->counterMax(100);
$input_blog_title->size('small');
$input_blog_title->arErr($message);

// url
$val = isset($list['url']) ? htmlspecialchars_decode($list['url']) : '';
$input_url = new inputField( 'Url', 'url' );
$input_url->toolTip('Title as it appears in the url:<br />Use only letters, numbers, underscores and hyphens. No Spaces!');
$input_url->value($val );
$input_url->counterMax(100);
$input_url->size('small');
$input_url->arErr($message);

// Menu Position  -- create category list
$cats = isset($cats) ? $cats : '-1';
$input_cate = new inputField( 'Category', 'cate' );
$input_cate->toolTip("Select the category, if any, to which this post belongs.<br />Hold down CTRL to select more than one." );
$input_cate->type('multiselect');
$input_cate->selected($cats);
$input_cate->arErr($message);
// Position options
$val = isset($page['title']) ? htmlspecialchars_decode($page['title']) : '';
$input_cate->option(  -1, 'No Category' );
$maincats = logged_query_assoc_array("SELECT `id`, `title` FROM `blog_cat` WHERE `id` > 0 ORDER BY `title`",null,0,array());
foreach($maincats as $maincat)
{
	$input_cate->option(  $maincat['id'], $maincat['title'] );
}

// status
$val = isset($list['status']) ? htmlspecialchars_decode($list['status']) : 0;
$input_status = new inputField( 'Status', 'status' );
$input_status->toolTip('A draft version can have blank fields. A published version must have all fields completed.');
$input_status->type('select');
$input_status->selected( $val );
$input_status->option( 0, 'Draft' );
$input_status->option( 1, 'Published' );
$input_status->arErr($message);

// featured
$input_featured = new inputField('Featured', 'featured');
$input_featured->toolTip('Featured Posts are displayed in the featured posts area.<br>The most recent published posts are displayed first<br>If insufficient posts are featured, the most recent non-featured post is used');
$input_featured->type('checkbox');
$input_featured->value(isset($list['featured']) ? $list['featured'] : '0');
$input_featured->arErr($message);

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

$pageResources = $uploader->headData() . "
<link rel='stylesheet' type='text/css' href='style.css' />
<script type=\"text/javascript\" src=\"".$_config['admin_url']."modules/blog/js/blog_new.js\"></script>
";
$pageInit->createPageTop($pageResources);

$usedSlugs = array('category');  // array of slugs that have already been used
$slugs=logged_query("SELECT `url` FROM blog_post WHERE id > 0",0,array());

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
	<div id="h2"><h2>Add Blog Post</h2></div>
    <div id="info_container">
		<?php
		// ----------------------------------------subnav--------------------------------
		$selectedOpts = '';
		$selectedPosts = 'tabSel';
		$selectedCats = '';
		include("blog/subnav.php");
		echo '<hr />';
		//---------------------------------------Error Banner-----------------------------
		// create a banner if $message['banner'] exists
		createBanner($message);
		$parms = "?blogid={$blogpost_id}&option=edit";

		?>
		<form action="blog_new.php<?php echo $parms; ?>" method="post" enctype="application/x-www-form-urlencoded" name="addblog" id="addblog" class="form">

			<!--  properties_wrap -->
			<h2 id="prop-toggle" class="tiptip toggle" title="Information about the page.">Page Properties</h2><br />
			<div id="prop-toggle-wrap">
			<?php
				$input_blog_title->createInputField();
				$input_url->createInputField();
				$input_cate->createInputField();
				$input_status->createInputField();
				$input_featured->createInputField();



			?>
			</div><!-- end prop_wrap -->

             <!-- content area -->
			<h2 id="content-toggle" class="tiptip toggle" title="The actual content displayed on the page.">Blog Post</h2>
			<div id="content-toggle-wrap">

			<?php
			echo "<h3 class='tiptip' style='float:left;' title='Short introductory blurb for article'>Short Intro/Excerpt (max 256)</h3><div style='clear:both;height:1em;'></div>";
			if (isset($message['inline']) && array_key_exists('intro', $message['inline'])) :?>
				<span class="<?php echo $message['inline']['intro']['type'] ;?>"><?php echo $message['inline']['intro']['msg']; ?> </span>
			<?php endif;
			// create tinymce
			$editable = array(
				'editable_class' => 'mceEditor',
				'attributes' => array('name' => 'intro', 'id' => 'intro'),
				'secure_data' => array(
					'id-field' => 'id',				// req for save && upload
					'id-val' => $blogpost_id,	// req for save && upload
					'upload-type' => 'blog',		// req for upload
				)
			);
			$wrapper = getContentWrapper($editable);
			echo $wrapper['open'];
			echo isset($list['intro']) ? htmlspecialchars_decode($list['intro']) : '' ;
			echo $wrapper['close'];
			echo "<h3 class='tiptip' style='float:left;' title='Full Blog Article'>Full Article</h3><div style='clear:both;height:1em;'></div>";
			if (isset($message['inline']) && array_key_exists('content', $message['inline'])) :?>
				<span class="<?php echo $message['inline']['content']['type'] ;?>"><?php echo $message['inline']['content']['msg'] ;?> </span>
			<?php endif;
			// create tinymce
			$editable = array(
				'editable_class' => 'mceUploadable',
				'attributes' => array('name' => 'content', 'id' => 'content'),
				'secure_data' => array(
					'id-field' => 'id',				// req for save && upload
					'id-val' => $blogpost_id,	// req for save && upload
					'upload-type' => 'blog_post',		// req for upload
				)
			);
			$wrapper = getContentWrapper($editable);
			echo $wrapper['open'];
			echo isset($list['post']) ? htmlspecialchars_decode($list['post']) : '' ;
			echo $wrapper['close'];
			?>
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
			<input name="blogpost_id" type="hidden" value="<?php echo $blogpost_id; ?>"/>
			<input name="submit-post" type="hidden" />
		</form>
		<a id="submit-btn" class="blue button" href="#">Save</a>
		<a class="grey button" id="cancel-btn" href="blog.php">Cancel</a>
		<div class='clearFix' ></div>
		<!-- end page buttons -->

		<?php // include the image gallery
		/*if ( ! $newPost )
		{
			// $uploadPage and uploadPageType must be set before including uploads (they are page_id & page_type in tables: pictures & uploads
			$uploadPage = $blogpost_id;
			$uploadPageType = 'blog';
			include("{$_config['components']}uploading/uploads.php");
		} else {
			echo '<div class="clearFix"></div><h2 class="tiptip" id="image-toggle" style="color: grey;" title="New page must be saved before images can be added." >Page Images</h2>';
		}*/
		echo "<br><hr><br>";
		$uploader->buildUploadArea($blogpost_id);

	// ----------------------------------- revisions -------------------------------------

		// add GET data to the return url
		$blogpost_id = isset($blogpost_id) ? $blogpost_id : '';
		$extraGET = array('post_id' => $blogpost_id);

		// build the Revision Area
		$Revisions->createRevisionsArea($blogpost_id, array(),$extraGET);
		// end revisions
?>
	</div>
</div>
<?php
include($_config['admin_includes'] . "footer.php"); ?>
