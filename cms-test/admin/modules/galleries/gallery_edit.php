<?php 
// initialize the page
$headerComponents = array();
$headerModule = 'galleries';
include('../../includes/headerClass.php');
include('functions.php');

$validatePath = $_config['admin_path']."modules/galleries/system/install_module.php";
if(file_exists($validatePath)) include_once($validatePath);

$pageInit = new headerClass($headerComponents,$headerModule);

$gallery = new gallery(
	$_config['upload_path'] . "gallery/", 
	$_config['upload_url'] . "gallery/", 
	'gallery_image', 
	'gallery_id',
	$_config['gallery_img_text_desc'],
	$_config['gallery_img_html_desc'],
	'gallery'
);
$gallery->setOptions(array('has_url' => true));

// access user data from headerClass
global $curUser;

// set the db variables
$table 				= 'gallery';		// name of the table this page posts to


$page_type			= 'gallery';		// name of uploadPageType for uploads component, also used to differentiate kinds of delete on parent page
$parent_page		= 'gallery.php';		// the page that calls this one: used for delete and cancel form buttons, sets where to go afterwards
$page_get_id		= 'gallery_id';			// the GET key passed forward with the db id for this item(page/blog post/...)
$page_get_id_val	= '';				// this variable is used to store the GET id value of above

$newPost	 		= false;			// records that this is not a new Post/Page 
$message			= array();			// will hold error/success message info

// get gallery data
// no data if new gallery, set newPost as true
if ( array_key_exists('option', $_GET) && ($_GET['option'] == 'create') ) 
{
	$newPost = true;
}
// if this is an edit of an existing gallery, get the gallery info
elseif (array_key_exists($page_get_id, $_GET) && is_numeric($_GET[$page_get_id]) && ! array_key_exists('option', $_GET) ) {	
	$new = logged_query("SELECT * FROM {$table} WHERE id =:page_get_id ORDER BY id DESC LIMIT 1",0,array(":page_get_id" => $_GET[$page_get_id] )); 
	$list=$new[0];

	$page_get_id_val = $_GET[$page_get_id];
}
// if we aren't editing or creating either, we shouldn't be here
elseif (! array_key_exists('option', $_GET) ) 
{
	header( "Location: " . $_config['admin_url'] . "modules/galleries/". $parent_page );
	exit;
}	


#==================
# process post
#==================
if(isset($_POST['submit-post'])){

	$list = array();
	foreach($_POST as $k=>$v)
	{trim(htmlspecialchars($v ,ENT_QUOTES));
		${$k} = trim(htmlspecialchars($v ,ENT_QUOTES));
		$list[$k] = ${$k};
		if (${$k} == '' ) {$message['inline'][$k] = array ('type' => 'errorMsg', 'msg' => 'All fields must be completed');}
	}
	
	// $PAGE_ID MUST = 3 IF USING TAGGED_PAGES GALLERY
	//$page_id = 3;
	
	// if an error was found, create the error banner
	if (isset($message['inline']) && count($message['inline']))
	{
		$message['banner'] = array ('heading' => 'Missing Data', 'message' => 'could not update records', 'type' => 'error');
	}
	else // set the success message and save data
	{
		$message['banner'] = array ('heading' => 'Successfully Saved', 'type' => 'success');
		if (array_key_exists('option', $_GET) && ($_GET['option'] == 'edit'))
		{
			$success = logged_query("UPDATE `{$table}` SET 
	`user_id` =:user_id,
	`title`	= :title,
	`sort_type` = :sort_type,
	`status`	= :status
WHERE `id` = :page_get_id_val LIMIT 1;",0,array(
				":user_id" => $curUser['user_id'],
				":title" => $title,
				":sort_type" => $sort_type,
				":status" => $status,
				":page_get_id_val" => $page_get_id_val
));

			// banners: if there was an error, overwrite the previously set success message
			if ($success === false)	$message['banner'] = array ('heading' => 'Error Saving Gallery', 'message' => 'there was an error writing to the database', 'type' => 'error' );
		}
		elseif (array_key_exists('option', $_GET) && ($_GET['option'] == 'create'))
		{

			$success = logged_query("INSERT INTO `{$table}` (`user_id`, `title`, `sort_type`, `status`, `date`) 
VALUES (:user_id, :title, :sort_type, :status, UTC_TIMESTAMP());",0,array(
				":user_id" => $curUser['user_id'],
				":title" => $title,
				":sort_type" => $sort_type,
				":status" => $status
			)); 
						
			$page_get_id_val = $_config['db']->getLastInsertId();
			
			// banners: if there was an error, overwrite the previously set success message
			if ($success === false) $message['banner'] = array ('heading' => 'Error Saving Gallery', 'message' => 'there was an error writing to the datadbase', 'type' => 'error' );
			else $newPost = false;
		}
	}
}

if(! isset($message))$message = array();
$value = isset($list['title']) ? $list['title'] : '';
// Gallery Name
$input_name = new inputField( 'Gallery Name', 'title' );	
$input_name->toolTip('Gallery Name appears above the gallery image.');
$input_name->value( $value );
$input_name->counterMax(200);
$input_name->size('small');
$input_name->arErr($message);


// Sort Type
$value = isset($list['sort_type']) ? $list['sort_type'] : 2;	
$input_sort = new inputField( 'Sort By', 'sort_type' );	
$input_sort->toolTip('Choose the order in which you wish the images to appear.<br />Sorted Order displays the images as they appear in the Gallery Images Section');
$input_sort->type('select');
$input_sort->selected($value);
$input_sort->option( 2, 'Sorted Order' );
$input_sort->option( 0, 'Newest to Oldest' );
$input_sort->option( 1, 'Oldest to Newest' );
$input_sort->option( 3, 'Alphabetically' );
$input_sort->arErr($message);	

// status
$value = isset($list['status']) ? $list['status'] : 1;
$input_status = new inputField( 'Status', 'status' );	
$input_status->toolTip('Save as published when the Gallery is ready to be shown.');
$input_status->type('select');
$input_status->selected($value);
$input_status->option( 0, 'Draft' );
$input_status->option( 1, 'Published' );
$input_status->arErr($message);


// ----------------------------------------------------------build gallery info
// get data

$corralImgs = logged_query_assoc_array("SELECT * FROM gallery_image WHERE gallery_id=:page_get_id_val AND posn ='0'", 'id',0,array(":page_get_id_val" => $page_get_id_val));
$posnImgs = logged_query_assoc_array("SELECT * FROM gallery_image WHERE gallery_id=:page_get_id_val AND posn !='0' ORDER BY posn", 'id',0,array(":page_get_id_val" => $page_get_id_val));
?>

    


 <?php 
// set the header variables and create the header
$pageInit->createPageTop( $gallery->headData() . "<script type=\"text/javascript\" src=\"".$_config['admin_url']."modules/galleries/js/gallery_edit.js\"></script>");

 ?>
<script type="text/javascript">
	/* allows one-click selection of embed code for gallery BEH */
	function SelectCode(id)
	{
		document.getElementById(id).focus();
		document.getElementById(id).select();
	}
</script>
 <div class="page_container">
	<div id="h1"><h1>Gallery</h1></div>
    <div id="info_container">
		<?php

		// create a banner if $message['banner'] exists
		createBanner($message); 

		//                             Gallery Properties                    -->

		$parms = $newPost ? '?option=create' : "?{$page_get_id}={$page_get_id_val}&option=edit";
		if ( ! $newPost )
		{
			echo "<div style='margin:1em 0 0 0;'><label>Embed Code</label><input type='text' id='embed_code' onClick='SelectCode(\"embed_code\");' readonly=readonly style='margin-left:5.5em;' value='{{gallery/{$page_get_id_val}}}' /><br /><p style='margin:0 0 1em 14.7em;font-size:.7em;'>Copy above embed code and paste it into your page on<br />a line by itself where you want the gallery to be placed.</p></div>";
			
			$gallery->buildGalleryArea($page_get_id_val);
			echo "<hr />";
		}
		
		?>
		<form action="<?php echo htmlentities($_SERVER['PHP_SELF']); ?><?php echo $parms; ?>" method="post" enctype="application/x-www-form-urlencoded" name="form_data" id="form_data" class="form">
  
			<!--  properties_wrap -->
			<h2 id="prop-toggle" class="tiptip toggle" title="Information about the page.">Gallery Properties</h2><br />
			<div id="prop-toggle-wrap">
			<?php
				$input_name->createInputField();
				$input_sort->createInputField();
				$input_status->createInputField();
			?>
			</div><!-- end prop_wrap -->		   


                            
			<!-- page buttons -->
			<div class='clearFix' ></div>
			<?php if (! $newPost )  : ?>
			<input name="page_get_id_val" type="hidden" value="<?php echo $page_get_id_val; ?>"/>
			<?php endif ?>
			<input name="submit-post" type="hidden" value="submit" />
		</form>	
		<a id="submit-btn" class="blue button" href="#">Save</a>

		<?php /*  removed for now, handle delete from main page
		if (! $newPost )  : ?>
		<form name='delForm' id='delForm' action='<?php echo $parent_page; ?>?delete=<?php echo $page_get_id_val; ?>&type=<?php echo $page_type; ?>' method="post" enctype="application/x-www-form-urlencoded">
			<input type='submit' id="delete-btn" class="red button" value='Delete' />
		</form>

		<?php 
		
		endif ; */ ?>
		<a class="grey button" id="cancel-btn" href="<?php echo $parent_page; ?>">Cancel</a>
		<div class='clearFix' ></div> 
		<!-- end page buttons -->

		

	</div>	<!-- end info container -->
</div><!-- end page container -->



<?php 

include($_config['admin_includes'] . "footer.php"); ?>