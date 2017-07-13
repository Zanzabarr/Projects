<?php
//echo print_r($_POST);
//echo print_r($_FILES);

// -----------------------------------------------save position-----------------------------------------
if (array_key_exists('option', $_POST) && $_POST['option'] == "save_posn")
{	
	
	include("../../includes/config.php");
	
	ajax_check_login();
	
	// update all in corralIds
	if (isset($_POST['corralIds']) && count($_POST['corralIds']) > 0 )
	{
		$arCorIds = $_POST['corralIds'];
		$firstId = $arCorIds[0];
		//Where clause
		$whereClause = "WHERE id=:firstId ";
		$bindings = array(":firstId" => $firstId);
		for ($index = 1;$index < count($arCorIds); $index++ )
		{ 
			$corId = $arCorIds[$index];
			$whereClause .= "OR id=:corId_{$index} ";
			$bindings[":corId_{$index}"] = $corId;
		}
	
		// do updates
		logged_query("UPDATE `banners` SET `posn`='0' {$whereClause}", 0,$bindings);
	}
	// update all in penIds
	if (isset($_POST['penIds']) && count($_POST['penIds']) > 0 )
	{
		$arPenIds = $_POST['penIds'];


		foreach ($arPenIds as $key =>$value )
		{ 
			$corId = $value;
			$posn = $key+1;
			logged_query("UPDATE `banners` SET `posn` = :posn WHERE id = :value;",0,array(
				":posn" => $posn,
				":value" => $value
			));
		}
	
		
	}
	

	die();
} 

// -----------------------------------------------edit image--------------------------------------------
if (array_key_exists('option', $_POST) && $_POST['option'] == "edit_image") 
{
	include("../../includes/config.php");
	global $_config;
	ajax_check_login();
	$error = array();
	if(! isset($_POST['image_id'])) $error['error']['alt'] = 'Missing Data';
	if ( ! isset($_POST['alt']) || ! trim($_POST['alt'])) $error['error']['alt'] = 'Please Provide an Alternate Description';
	$html_desc = isset($_POST['html_desc']) ? htmlspecialchars($_POST['html_desc'], ENT_QUOTES) : '';
	$alt = htmlspecialchars($_POST['alt'], ENT_QUOTES);
	$desc = htmlspecialchars($_POST['desc'], ENT_QUOTES);
	
	$link = trim($_POST['link'], '/');
	if($link)
	{
		if(	!filter_var($_POST['link'], FILTER_VALIDATE_URL, FILTER_FLAG_PATH_REQUIRED) &&
			!filter_var($_config['site_path'].$_POST['link'], FILTER_VALIDATE_URL, FILTER_FLAG_PATH_REQUIRED)
		) 
		{
			$error['error']['link'] = "Not a valid link";
		}
	}
	if(count($error))
	{
		echo json_encode($error);
		die();
	}
		
	$image_id = $_POST['image_id'];
	$result = logged_query("UPDATE `banners` SET `desc`=:desc, `html_desc`=:html_desc, `alt`=:alt, `link`=:link WHERE `id`=:image_id",0,array(
		":html_desc" => $html_desc,
		":desc" => $desc,
		":alt" => $alt,
		":link" => $link,
		":image_id" => $image_id,
		
	));
	if ($result === false) 
	{
		$result = array('error' => array('alt' =>'Error saving changes'));
	}
	else
	{
		$result = array('link' => $link);
	}
	echo json_encode($result);
	die();
}	

// ----------------------------------------------delete picture-----------------------------------------
if (array_key_exists('option', $_POST) 
	&& isset($_POST['uploadPath']) 
	&& isset($_POST['target_id_name']) 
	&& isset($_POST['image_table_name']) 
	&& $_POST['option'] == "removep"
) 
{
	include("../../includes/config.php");
	ajax_check_login();
		
	$uploadPath 		= mysql_real_escape_string($_POST['uploadPath']);
	$target_id_name 	= mysql_real_escape_string($_POST['target_id_name']);
	$image_table_name 	= mysql_real_escape_string($_POST['image_table_name']);
	
	$query = "SELECT name FROM {$image_table_name} WHERE id = '".mysql_real_escape_string($_POST['picture_id'])."'";
	$result = logged_query_assoc_array($query); 
	if (! $result) die ( 'Error: image not found.');

	$filename = $result[0]['name'];

	$query = "DELETE FROM {$image_table_name} WHERE id = '".mysql_real_escape_string($_POST['picture_id'])."'";
	$result = logged_query($query); 
	if (! $result ) die('Error: could not delete image.' );
	
	if(mysql_affected_rows() > 0) 
	{
		@unlink($uploadPath .'fullsize/'.$filename);
		@unlink($uploadPath .'original/'.$filename);
		@unlink($uploadPath .'thumb/'.$filename);
		@unlink($uploadPath .'mini/'.$filename);
		die('success');
	}
	else die('No such image.');
}

// -----------------------------------------------insert picture-----------------------------------------
if (array_key_exists('option', $_POST) 
	&& isset($_POST['uploadPath']) 
	&& isset($_POST['image_table_name']) 
	&& $_POST['option'] == "upload"
	)
{
	include("../../includes/config.php");

	ajax_check_login();
	
	$uploadPath 		= mysql_real_escape_string($_POST['uploadPath']);
	$image_table_name 	= mysql_real_escape_string($_POST['image_table_name']);

	$imageUrl = $_config['img_path'];
	$uploadUrl = $_config['upload_url'];
	
		// image table, file table, prefix indicates page id, pageID is part of the prefix
	$ajaxUp = new ajaxUpload(
		$image_table_name, 			//	image table name
		false,						// file uploads not enabled for galleries 
		'name',						// gallery_image name 
		$_config['img_path'], 
		$_config['upload_url'],
		$uploadPath,				// where to send the upload
		'b' 						// prefix tells us that this is a banner image
	);
	
	// save the different image types
	$ajaxUp->fullImage();
	$ajaxUp->thumb();
	$ajaxUp->sqrThumb();
	
	
	$query = "
		INSERT INTO {$image_table_name} 
		SET name = '{$ajaxUp->filename}',
			posn='0',
			alt='{$ajaxUp->filename}',
			date=UTC_TIMESTAMP()
	";
	logged_query($query);
	// set the name of the function that receives the js response
	$ajaxUp->uploadResultFnJS = 'bannerResult';
	$ajaxUp->successResponse( mysql_insert_id() );
}

//oversized uploads don't pass any Files or post data:
//if nothing else has happened return a trigger to write an error message for the uploader
?>
<!DOCTYPE html>
<html xmlns="//www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<script src="//ajax.googleapis.com/ajax/libs/jquery/1.7/jquery.min.js"></script>
<script type="text/javascript">
$(document).ready(function(){

parent.$(parent.document).trigger('failedUpload');

});
</script>
</head>  
<body>
</body>
</html>
