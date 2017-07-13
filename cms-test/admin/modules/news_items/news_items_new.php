<?php 
// initialize the page
$headerComponents = array('uploads');
$headerModule = 'news_items';
include('../../includes/headerClass.php');
include('includes/query.php');
include('includes/functions.php');

$pageInit = new headerClass($headerComponents,$headerModule);

$baseUrl = $_config['admin_url'];
?>

<?php

$newnews_item = false;
$news_itemImageString = NULL;
if ( array_key_exists('option', $_GET) && ($_GET['option'] == 'create') ) 
{
	$result = logged_query('
		SELECT MAX( CAST( SUBSTRING( `url` , 5 ) AS Unsigned ) ) AS num
		FROM `news_items`
		WHERE `id` >0
		AND `url` REGEXP "^new_[0-9]*$"',0,array()
	);

	$slug_num = is_array($result) && count($result) ? (int)$result[0]['num'] : 0;
	$tmp_slug = 'new_' . ++$slug_num;
	$result = logged_query("INSERT INTO `news_items` SET news_item_title=:tmp_slug, url=:tmp_slug",0,array(":tmp_slug" => $tmp_slug));
	$news_item_id = $_config['db']->getLastInsertId();
	$list = logged_query("SELECT * FROM `news_items` WHERE `id` =:news_item_id",0,array(":news_item_id" => $news_item_id));
	$list = $list[0];	
}

// if this is an edit of an existing news_item, get the news_item info
elseif (array_key_exists('news_itemid', $_GET) && is_numeric($_GET['news_itemid']) && ! array_key_exists('option', $_GET) ) {	
	$new = logged_query("SELECT * FROM news_items WHERE id = {$_GET['news_itemid']} ORDER BY id DESC LIMIT 1;"); 
	$list=$new[0];
	
	$news_itemImageString = $list['news_item_image'];
	$news_item_id = $_GET['news_itemid'];
}
// if we aren't editing or creating either, we shouldn't be here
elseif (! array_key_exists('option', $_GET) ) 
{
	header( "Location: " . $_config['admin_url'] . "modules/news_items/news_items.php" );
	exit;
}	


#==================
# process news_item info
#==================
if(isset($_POST['submit-news_item'])) {

	// validate:
	//	pick error type/messages based on if its status is Inactive or Active
	if ($_POST['status'] == 1) // Active: thus error
	{
		$errorMsgType = 'errorMsg';
		$errorType = 'error';
		$errorHeading = 'Active Version';
		$errorMessage = 'all fields must be complete, saved as Inactive';
	}
	else  // Inactive: thus warning
	{
		$errorMsgType = 'warningMsg';
		$errorType = 'warning';
		$errorHeading = 'Inactive Version';
		$errorMessage = 'saved with incomplete fields on page';
	}

	$list = array();

	foreach($_POST as $k=>$v)
	{
			if($k == 'content') ${$k} = trim(htmlspecialchars($v, ENT_QUOTES));
			else ${$k} = trim($v);
			$list[$k] = $v;
			if (${$k} == '' && $k != 'submit-news_item' && $k != 'news_item_id' && $k != 'news_itemid' && $k != 'email' && $k != 'news_item_title' && $k != 'content' && $k != 'news_item_image' && $k != 'news_item_image_string') {$message['inline'][$k] = array ('type' => $errorMsgType, 'msg' => 'Required field');}
		
	}
	$list['content'] = $list['content'];	
	$news_itemImageString = $list['news_item_image_string'];

    // set url
    $url = trim($contributor_name) . '-' . trim($caption);
    $url = preg_replace('/\s+/', ' ', $url); // strip extra whitespace
    $url = preg_replace('/[\s\W]/', '-', $url); // change any spaces or non-word chars to hyphens
    
    do {
        $oldUrl = $url;
        $url = preg_replace('/--/', '-', $url); // get rid of consecutive hyphens
    } while($url != $oldUrl);
    
    $url = preg_replace('/^-/', '', $url); // get rid of leading hyphen
    $url = preg_replace('/-$/', '', $url); // get rid of trailing hyphen

    $news_items = getnews_itemsData();
    $news_itemUrls = array();

    // get all news_item urls
    foreach ($news_items as $news_item)
    {
        $news_itemUrls[$news_item['url']] = $news_item;
    }

    // check to see if url already exists for another news_item. If it does, append an underscore
    while(array_key_exists($url, $news_itemUrls) && ($_GET['option'] == 'create' || $news_itemUrls[$url]['id'] != $news_item_id))
	{
        $url .= '_';
    }

	// if an error was found, create the error banner
	if ($errorsExist = count(isset($message['inline']) ? $message['inline'] : array()))
	{
		$message['banner'] = array ('heading' => $errorHeading, 'message' => $errorMessage, 'type' => $errorType);
		$status = $list['status'] = 0; // saving as Inactive
	}
	else // set the success message
	{
		$message['banner'] = array ('heading' => 'Successfully Saved', 'type' => 'success');
	}

	// save even if errors exist: but save as draft
	if (array_key_exists('option', $_GET) && ($_GET['option'] == 'create'))
	{

		$saved = logged_query("INSERT INTO `news_items` (`news_item_title`, `url`, `contributor_name`, `email`, `caption`, `content`, `status`) VALUES (:news_item_title, :url, :contributor_name, :email, :caption,  :content, :status);",0,array(
			":news_item_title" => $news_item_title,
			":url" => $url,
			":contributor_name" => $contributor_name,
			":email" => $email,
			":caption" => $caption,
			":content" => $content,
			":status" => $status
		)); 
		$news_item_id = $_config['db']->getLastInsertId();
			
		$saveError = false;
		if($saved === false) $saveError = true;
			
		// banners: if there was an error, overwrite the previously set success message
		if ($saveError)	$message['banner'] = array ('heading' => 'Error Saving News Item', 'message' => 'there was an error writing to the database', 'type' => 'error' );
	
        if(is_uploaded_file($_FILES['upload_file']['tmp_name'])) {
            // upload news_item image given
            if(!(news_itemimageupload($news_item_id, $news_itemImageString, $errorMessage))) {
                // image upload failed   
                if($saveError || $errorsExist) {
                    $message['banner']['message'] .= '. {$errorMessage}';
                }
                else {
		            $message['banner'] = array ('heading' => 'Error Saving News Item Image', 'message' => '{$errorMessage}', 'type' => 'error' );
                }  
            }
        }

			// successfully created news_item: no longer a new page!
			$newnews_item = false;
	}
	elseif (array_key_exists('option', $_GET) && ($_GET['option'] == 'edit'))
	{
        $news_item_id = $_GET['news_itemid'];

		$saved = logged_query("UPDATE `news_items` SET
		`news_item_title` = :news_item_title,
        `url` = :url, 
		`contributor_name` = :contributor_name, 
		`email` = :email,
		`caption` = :caption,
		`content` = :content,
		`status` = :status
         WHERE `id` = :id LIMIT 1;",0,array(
			":news_item_title" => $news_item_title,
			":url" => $url,
			":contributor_name" => $contributor_name,
			":email" => $email,
			":caption" => $caption,
			":content" => $content,
			":status" => $status,
			":id" => $news_item_id
		 ));
			
		$saveError = false;
		if($saved === false) $saveError = true;
			
		// banners
		// banners: if there was an error, overwrite the previously set success message
		if ($saveError)	$message['banner'] = array ('heading' => 'Error Saving News Item', 'message' => 'there was an error writing to the database', 'type' => 'error' );
        
        if(is_uploaded_file($_FILES['upload_file']['tmp_name'])) {
            // upload news_item image given

            if(!(news_itemimageupload($news_item_id, $news_itemImageString, $errorMessage))) {
                // image upload failed   
                if($saveError || $errorsExist) {
                    $message['banner']['message'] .= ' . {$errorMessage}';
                }
                else {
		            $message['banner'] = array ('heading' => 'Error Saving News Item Image', 'message' => $errorMessage, 'type' => 'error' );
                }  
            }
        }
	}
}	

if (! isset($message)) $message=array();

// news_item title
$input_news_item_title = new inputField('News Item Title', 'news_item_title');
$input_news_item_title->toolTip('News Item Title');
$input_news_item_title->value(htmlspecialchars_decode(isset($list['news_item_title']) ? $list['news_item_title'] : '', ENT_QUOTES));
$input_news_item_title->counterMax(300);

$input_news_item_title->arErr($message);

// Contributor name
$input_contributor_name = new inputField('Contributor Name', 'contributor_name');
$input_contributor_name->toolTip('Contributor Name');
$input_contributor_name->value(htmlspecialchars_decode(isset($list['contributor_name']) ? $list['contributor_name'] : '', ENT_QUOTES));
$input_contributor_name->counterMax(40);
$input_contributor_name->size('small');
$input_contributor_name->arErr($message);

// Contributor email
$input_email = new inputField('Contributor Email', 'email');
$input_email->toolTip('Contributor Email Address (optional)');
$input_email->value(htmlspecialchars_decode(isset($list['email']) ? $list['email'] : '', ENT_QUOTES));
$input_email->counterMax(75);
$input_email->size('small');
$input_email->arErr($message);

// last name
$input_caption = new inputField('Image Caption', 'caption');
$input_caption->toolTip('Image Caption - (optional)');
$input_caption->value(htmlspecialchars_decode(isset($list['caption']) ? $list['caption'] : '', ENT_QUOTES));
$input_caption->counterMax(40);
$input_caption->size('small');
$input_caption->arErr($message);

// status
$input_status = new inputField( 'Status', 'status' );	
$input_status->toolTip('An Inactive News Item will not show up on the website.');
$input_status->type('select');
$input_status->selected(isset($list['status']) ? $list['status'] : '');
$input_status->option( 0, 'Inactive' );
$input_status->option( 1, 'Active' );
$input_status->arErr($message);
?>

<?php
$pageResources ="
<link rel=\"stylesheet\" type=\"text/css\" href=\"".$_config['admin_url']."modules/news_items/style.css\" />
<script type=\"text/javascript\" src=\"".$_config['admin_url']."modules/news_items/js/news_items_new.js\"></script>
";
$pageInit->createPageTop($pageResources);
 
 ?>
 <div id="news_item_new_page_container" class="page_container">
    <div id="h1">
        <?php if($newnews_item) {
            echo '<h1>Add News Item</h1>';
        } else {
            echo '<h1>Edit News Item</h1>';
        } ?>
    </div>
    <div id="info_container">
		<?php 
		// ----------------------------------------subnav--------------------------------
		$selectednews_items = 'tabSel';
		$selectedOpts = '';
		$selectedPosts = 'tabSel';
		$selectedCats = '';
		include("includes/subnav.php"); 
		echo '<hr />';
		//---------------------------------------Error Banner----------------------------- 
		// create a banner if $message['banner'] exists
            echo '<div id="new_news_items_banner">';
            createBanner($message); 
            echo '</div>';

		$parms = $newnews_item ? '?option=create' : "?news_itemid={$news_item_id}&option=edit";
		?>
		<form action="news_items_new.php<?php echo $parms; ?>" method="post" enctype="multipart/form-data" name="addnews_item" id="addnews_item" class="form">
            <input type="hidden" name="news_item_id" id="news_item_id" value="<?php echo isset($news_item_id) ? $news_item_id : ''; ?>" /> 
			<!--  properties_wrap -->
			<h2 id="prop-toggle" class="tiptip toggle" title="Information about the News Item.">News Item Properties</h2><br />
			<div id="prop-toggle-wrap">
			<?php
				$input_news_item_title->createInputField();

                if(!$newnews_item && $news_itemImageString != NULL) {

                    $news_itemImageName =  $_config['upload_url'] . 'news_item/fullsize/' . $news_itemImageString ; 
                    $fileInputText = 'Click to select a different image'; 
                    $hasProfileImage = true;
                }
                else {
                    // default news_item image
                    $news_itemImageName = $_config['admin_url'].'modules/news_items/images/news_itemimages/default_s.jpg'; 
                    $fileInputText = 'Click to select a new image'; 
                    $hasProfileImage = false;
                }
               
                echo '<div id="profile-image-container">';
                
                if($hasProfileImage) {
                    // display ability to delete image
                    echo '<a href="news_items_new.php" class="tipTop deletenews_itemimage" title="Delete Image" rel="'.$news_item_id.'">';
                    echo '<img src="'.$_config['admin_url'].'modules/news_items/images/delete.png" alt="Delete">';
                    echo '</a>';
                }

                echo '<img id="profile-image" src="'.$news_itemImageName.'" alt="News Item image" />'."\n";
                // display image upload box
                if($hasProfileImage) {
                    // display toggle wrap for file input box
                    echo '<div id="togglenews_itemimage" class="toggle"><a class="addForm"><img src="'.$_config['admin_url'].'modules/news_items/images/img-icon16.jpg" alt="image icon" />Change News Item Image</a></div>';
                    echo '<div id="togglenews_itemimage-wrap">';
                }
                else {
                    echo "<label class=\"tipRight\" title=\"News Item's image (optional).\">News Item Image</label>";
                }
                
                echo '<div id="file-input-container">';
                
                if (using_ie()) {
                    echo '<input id="news_item_image_ie" name="upload_file" type="file" />';
                } 
                else {
                    echo '<input id="news_item_image" name="upload_file" type="file" />';
                    echo '<div class="fakefile"><input type="text" Value="'.$fileInputText.'" /></div>';
                }

                echo '</div><!-- END file-input-container -->';

                if($hasProfileImage) {
                    echo '</div><!-- END togglenews_itemimage-wrap-->';
                }

                echo '</div><!-- END profile-image-container -->';  

				$input_contributor_name->createInputField();
				$input_caption->createInputField();
				$input_email->createInputField();
                $input_status->createInputField();
			?>
			</div><!-- end prop_wrap -->		   

             <!-- content area -->
			<h2 id="content-toggle" class="tiptip toggle" title="News Item content.">News Item Content</h2>
			<?php if (isset($message['inline']) && array_key_exists('content', $message['inline'])) :?>
				<span class="<?php echo $message['inline']['content']['type'] ;?>"><?php echo $message['inline']['content']['msg'] ;?> </span>
			<?php endif; ?>
			<br />
	<!--		
			<div id="content-toggle-wrap">
				<textarea class="mceEditor" name="content"><?php echo htmlspecialchars_decode(isset($list['content']) ? $list['content'] : ''); ?></textarea>
			</div>
	-->
	<?php
			$editable = array(
				'editable_class' => 'mceUploadable',
				'attributes' => array('name' => 'content', 'id' => 'content'),
				'secure_data' => array(
					'id-field' => 'id',				// req for save && upload
					'id-val' => $news_item_id,	// req for save && upload
					'upload-type' => 'news_items',		// req for upload
				)
			);
			$wrapper = getContentWrapper($editable);
			echo $wrapper['open'];
			echo isset($list['content']) ? htmlspecialchars_decode($list['content']) : '' ;
			echo $wrapper['close'];
			?>
			<!-- end content area -->
			
			<!-- page buttons -->
			<div class='clearFix' ></div>
			<input name="submit-news_item" type="hidden" />
			<input type="hidden" name="news_item_image_string" value="<?php echo $news_itemImageString; ?>" />
		</form>	
		<a id="submit-btn" class="blue button" href="#">Save</a>

		<a class="grey button" id="cancel-btn" href="news_items.php">Cancel</a>
		<div class='clearFix' ></div> 
		<!-- end page buttons 
		<hr />
		-->
		<?php // include the image gallery 
/*		
		if ( ! $newnews_item )
		{
			// $uploadPage and uploadPageType must be set before including uploads (they are page_id & page_type in tables: pictures & uploads
			$uploadPage = $news_item_id;
			$uploadPageType = 'news_items';
			include("{$_config['components']}uploading/uploads.php"); 
		} else {
			echo '<div class="clearFix"></div><h2 class="tiptip" id="image-toggle" style="color: grey;" title="New page must be saved before images can be added." >Page Images</h2>';
		}
*/
		?>
		
	</div>
</div>	
	

<?php 
include($_config['admin_includes'] . "footer.php"); 
?>


