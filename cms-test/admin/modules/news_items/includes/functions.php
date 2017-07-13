<?php

// get options for displaying news_items data from news_items_options table
function getnews_itemsOptions()
{
	$options = logged_query_assoc_array("
		SELECT * 
		FROM news_items_options
	");
	return $options[0];
}

function getnews_itemsData()
{
    global $_config;

	//create news_items array
	$news_items = array();
    $tmpnews_items=logged_query_assoc_array("SELECT * FROM `news_items` WHERE `id` > 0 ORDER BY `caption` ASC");
	foreach ($tmpnews_items as $news_item){
		// convert news_items data to useable format
		$news_item['status_word'] = $news_item['status'] == 0 ? 'Inactive' : 'Active';
        
        if($news_item['news_item_image'] == NULL) {
            // default image
            $news_item['image_path'] = $_config['admin_url'] . 'modules/news_items/images/news_itemimages/default_th35.jpg';  
        }
        else {
            $news_item['image_path'] = $_config['upload_url'] . 'news_item/thumb/' .  $news_item['news_item_image'] ; 
        }
 
		// set default data
		
		// update the news_items array
		$news_items[$news_item['id']] = $news_item;
	}	

	return $news_items;
}

function buildnews_itemsMenu($news_items)
{
	global $_config;
	$menu="";
	foreach($news_items as $news_item)
	{
	$menu .="<tr><td><span class='row_image'><img src=\"".$news_item['image_path']."\" alt=\"news_item img\" /></span></td>";
	$menu .="<td style=\"text-align:left\">{$news_item['news_item_title']}</td>";
	$menu .="<td style=\"text-align:center\">{$news_item['contributor_name']}</td>";
	$menu .= "<td style=\"text-align:center\">{$news_item['status_word']}</td>";
	$menu .="<td style=\"text-align:center\">
		<a target='_blank' href='{$_config['site_path']}news_items/#".$news_item['news_item_title']."'><img class='tipTop' title='See News Item page as it will appear live.' src=\"../../images/view_icon.png\" alt=\"View\"></a>
		<a href='news_items_new.php?news_itemid=".$news_item['id']."'><img class='tipTop' title='Edit' src=\"../../images/edit.png\" alt=\"Edit\"></a>
		<a  href='news_items.php?delete=".$news_item['id']."' class=\"deletenews_item\" rel=\"".$news_item['id']."\"><img class='tipTop' title='Delete' src=\"../../images/delete.png\" alt=\"Delete\"></a>
	</td></tr>";
	
	}
	return $menu;
}


function urlfriendly($mess)
{
	$x = ereg_replace("[^A-Za-z0-9_-]", "_", $mess);
	while(strpos($x, "__")){
		$clean = ereg_replace("__", "_", $x);
	}
	return $clean;

}

function errors()
{
	global $error;
	if(isset($error)){
		echo "<div class=\"error\">".$error."</div>";
	}
	if(isset($_GET['wrongfiletype'])){
		echo "<div class=\"error\">That file type is not supported</div>";
	}
	
	if(isset($_SESSION['SESS_MSG'])){
		echo "<div class=\"error\">".$_SESSION['SESS_MSG']."</div>";
		unset($_SESSION['SESS_MSG']);
	}
	
	if(isset($_SESSION['SESS_MSG_green'])){
		echo "<div class=\"success\">".$_SESSION['SESS_MSG_green']."</div>";
		unset($_SESSION['SESS_MSG_green']);
	}
}


function thumbmaker($source_image, $destination, $thumbSize)
{
    $info = getimagesize($source_image);
    $imgtype = image_type_to_mime_type($info[2]);

    #assuming the mime type is correct
    switch ($imgtype) {
        case 'image/jpeg':
            $source = imagecreatefromjpeg($source_image);
            break;
        case 'image/gif':
            $source = imagecreatefromgif($source_image);
            break;
        case 'image/png':
            $source = imagecreatefrompng($source_image);
            break;
        default:
            return false;
    }

    #Figure out the dimensions of the image and the dimensions of the desired thumbnail
    $width = imagesx($source);
    $height = imagesy($source);
	
    #Do some math to figure out which way we'll need to crop the image
    #to get it proportional to the new size, then crop or adjust as needed
	
	
	
///--------------------------------------------------------
//setting the crop size
//--------------------------------------------------------
if($width > $height) $biggestSide = $width;
else $biggestSide = $height;

//The crop size will be half that of the largest side
$cropPercent = .5;
$cropWidth   = $biggestSide*$cropPercent;
$cropHeight  = $biggestSide*$cropPercent;

//getting the top left coordinate
$c1 = array("x"=>($width-$cropWidth)/2, "y"=>($height-$cropHeight)/2);

//--------------------------------------------------------
// Creating the thumbnail
//--------------------------------------------------------

$thumb = imagecreatetruecolor($thumbSize, $thumbSize);
imagecopyresampled($thumb, $source, 0, 0, $c1['x'], $c1['y'], $thumbSize, $thumbSize, $cropWidth, $cropHeight);


	if (imagejpeg($thumb, $destination, 100)) {
        return true;
    }
    return false;
}

function resize($source_image, $destination, $tn_w)
{
    $info = getimagesize($source_image);
	
    $imgtype = image_type_to_mime_type($info[2]);


    #assuming the mime type is correct
    switch ($imgtype) {
        case 'image/jpeg':
            $source = imagecreatefromjpeg($source_image);
            break;
        case 'image/gif':
            $source = imagecreatefromgif($source_image);
            break;
        case 'image/png':
            $source = imagecreatefrompng($source_image);
            break;
        default:
            echo ('Invalid image type.' . $imgtype  . "  " . "  " . $source_image . "  " . $destination);
            return false;
    }

    #Figure out the dimensions of the image and the dimensions of the desired thumbnail
    $src_w = imagesx($source);
    $src_h = imagesy($source);
	
    #Do some math to figure out which way we'll need to crop the image
    #to get it proportional to the new size, then crop or adjust as needed
	
	$ratio = $src_h / $src_w;
	$new_w = $tn_w;
	$new_h = $tn_w * $ratio;
	
	$newpic = imagecreatetruecolor(round($new_w), round($new_h));
	imagecopyresampled($newpic, $source, 0, 0, 0, 0, $new_w, $new_h, $src_w, $src_h);
    $final = imagecreatetruecolor($new_w, $new_h);


    $backgroundColor = imagecolorallocate($final, 255, 255, 255);
    imagefill($final, 0, 0, $backgroundColor);
    imagecopy($final, $newpic, (($new_w - $new_w)/ 2), (($new_h - $new_h) / 2), 0, 0, $new_w, $new_h);

	if (imagejpeg($final, $destination, 100)) {
        return true;
    }
    return false;
}


function deletenews_itemimg($x)
{
	global $_config;	
	
	$oldimage = logged_query("SELECT `news_item_image` FROM `news_items` WHERE `id` = ".$x." LIMIT 1");

	$oimg = $oldimage[0];

	if($oimg != NULL) {
			@unlink($uploadPath .'original/'.$oimg);
			@unlink($uploadPath .'thumb/'.$oimg);
			@unlink($uploadPath .'mini/'.$oimg);
			@unlink($uploadPath .'gallery_thumb/'.$oimg);
	}	
			
}

function news_itemimageupload($imageId, &$news_itemImageString, &$errorMessage )
{
	global $_config;
	$uploadPath = $_config['upload_path'] . 'news_item/';
	
	$upload = new ajaxUpload(
		'news_items',
		false,
		'news_item_image',
		$_config['img_path'], 
		$_config['upload_url'],
		$uploadPath,
		'', 
		$imageId,
		false
		
	);

	
	if( $upload->error )
	{
		$errorMessage = $upload->error;
		return false;
	}	
	
	//if other images of item exist, delete them
	if($_FILES['upload_file']['name'] != NULL) deletenews_itemimg($imageId);
	
	// greatest of max width and height
	$upload->fullImage(180, 200, 'fullsize');
	
	// constrains on width only, don't use where height is a limitation!
	$upload->thumb(100, 'medium');
	
	// greatest of max width and height
	$upload->fullImage(50, 35, 'thumb');
	
	$upload->exactSize(125, 75, 'gallery_thumb');
	
	$query = "
		UPDATE `news_items`
		SET `news_item_image` = '{$upload->filename}'
		WHERE `id` = '{$imageId}' LIMIT 1
	";
	logged_query($query);
	
	$news_itemImageString = $upload->filename;
	
	return true;
	
	
}
?>
