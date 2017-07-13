<?php
//echo print_r($_POST);
//echo print_r($_FILES);

// -----------------------------------------------save position-----------------------------------------
if (array_key_exists('option', $_POST) && $_POST['option'] == "save_posn")
{
	include("../../includes/config.php");
	include( $_config['admin_includes'].'functions.php' );
	session_start();
	ajax_check_login();
	
	// update all in corralIds
	if (isset($_POST['corralIds']) && count($_POST['corralIds']) > 0 )
	{
		$arCorIds = $_POST['corralIds'];
		$firstId = trim($arCorIds[0]);
		//Where clause
		$whereClause = "WHERE id='{$firstId}' ";

		for ($index = 1;$index < count($arCorIds); $index++ )
		{ 
			$corId = trim($arCorIds[$index]);
			$whereClause .= "OR id='{$corId}' ";
		}
	
		// do updates
		logged_query("UPDATE gallery_image SET `posn`='0' {$whereClause}",0,array());
	}
	// update all in penIds
	if (isset($_POST['penIds']) && count($_POST['penIds']) > 0 )
	{
		$arPenIds = $_POST['penIds'];
		foreach ($arPenIds as $key =>$value )
		{ 
			$corId = trim($value);
			$posn = $key+1;
			logged_query("UPDATE gallery_image SET `posn` = {$posn} WHERE id = {$value};",0,array());
		}
		// do updates
	}
	die();
} 

// -----------------------------------------------edit image--------------------------------------------
if (array_key_exists('option', $_POST) && $_POST['option'] == "edit_image") 
{
	include("../../includes/config.php");
	include( $_config['admin_includes'].'functions.php' );
	session_start();
	ajax_check_login();
	
	
	if (!isset($_POST['desc']) || ! isset($_POST['alt']) || ! isset($_POST['image_id'])) die('Missing Data');
	$desc = trim($_POST['desc']);
	$alt = trim($_POST['alt']);
	$image_id = trim($_POST['image_id']);
	$result = logged_query("UPDATE gallery_image SET `desc`=':desc', `alt`=':alt' WHERE `id`=':image_id'",0,array(
		":desc" => $desc,
		":alt" => $alt,
		":image_id" => $image_id
	));
	if ($result===false) die("There has been an error.");
	die( 'success');
}	
// ----------------------------------------------delete picture-----------------------------------------
if (array_key_exists('option', $_POST) && $_POST['option'] == "removep") 
{
	include("../../includes/config.php");
	include( $_config['admin_includes'].'functions.php' );
	session_start();
	ajax_check_login();
	
	$query = "SELECT name FROM gallery_image WHERE id = ':picture_id'";
	
	$result = logged_query_assoc_array($query,null,0,array(":picture_id"=> trim($_POST['picture_id'])));
	if ($result===false) die ( 'Error: image not found.');

	$filename = $result[0]['name'];

	$query = "DELETE FROM gallery_image WHERE id = ':picture_id'";
	$result = logged_query($query,0,array(":picture_id" => trim($_POST['picture_id']))); 
	if ($result===false) {
		die('Error: could not delete image.' );
	
	if($result) 
	{
		unlink($_config['upload_path'].'galleries/fullsize/'.$filename);
		unlink($_config['upload_path'].'galleries/original/'.$filename);
		unlink($_config['upload_path'].'galleries/thumb/'.$filename);
		die('success');
	}
	else die('No such image.');
}

// ----------------------------------------------delete file-----------------------------------------
if (array_key_exists('option', $_POST) && $_POST['option'] == "removef") 
{
	include("../../includes/config.php");
	include( $_config['admin_includes'].'functions.php' );
	session_start();
	ajax_check_login();
	
	$query = "SELECT filename FROM upload_file WHERE id = ':file_id'";
	$result = logged_query_assoc_array($query,null,0,array(":file_id" => trim($_POST['file_id']))); 
	if ($result===false) die ( 'Error: file not found.');

	$filename = $result[0]['filename'];

	$query = "DELETE FROM upload_file WHERE id = ':file_id'";
	$result = logged_query($query,0,array(":file_id" => trim($_POST['file_id']))); 
	if ($result===false) die('Error: could not delete file.' );
	
	if($result)
	{
		unlink($_config['upload_path'].'files/'.$filename);
		die('success');
	}
	else die('No such file.');
}

// -----------------------------------------------insert picture-----------------------------------------
if (array_key_exists('option', $_POST) && $_POST['option'] == "upload")
{
	include("../../includes/config.php");
	include( $_config['admin_includes'].'functions.php' );
	$baseUrl = $_config['admin_url'];
	session_start();
	ajax_check_login();
	
	/* Image Re-Sizer */
	class imageResizer {
	   public $image,$image_type;

	   public function load ($filename) {
		  $image_info = getimagesize($filename);
		  $this->image_type = $image_info[2];
		  if($this->image_type == IMAGETYPE_JPEG) { $this->image = imagecreatefromjpeg($filename); }
		  elseif($this->image_type == IMAGETYPE_GIF) { $this->image = imagecreatefromgif($filename); }
		  elseif($this->image_type == IMAGETYPE_PNG) { $this->image = imagecreatefrompng($filename); }
	   }

	   public function save($filename,$image_type = IMAGETYPE_JPEG,$compression = 100,$permissions = null) {
		  if($image_type == IMAGETYPE_JPEG) { imagejpeg($this->image,$filename,$compression); }
		  elseif($image_type == IMAGETYPE_GIF) { imagegif($this->image,$filename); }
		  elseif($image_type == IMAGETYPE_PNG) { imagepng($this->image,$filename); }
		  if($permissions != null) { chmod($filename,$permissions); }
	   }

	   public function output($image_type = IMAGETYPE_JPEG) {
		  if($image_type == IMAGETYPE_JPEG) { imagejpeg($this->image); }
		  elseif($image_type == IMAGETYPE_GIF) { imagegif($this->image); }
		  elseif($image_type == IMAGETYPE_PNG) { imagepng($this->image); }
	   }

	   public function getWidth() {
		  return imagesx($this->image);
	   }

	   public function getHeight() {
		  return imagesy($this->image);
	   }

	   public function resizeToHeight($height) {
		  $ratio = $height / $this->getHeight();
		  $width = $this->getWidth() * $ratio;
		  $this->resize($width,$height);
	   }

	   public function resizeToWidth($width) {
		  $ratio = $width / $this->getWidth();
		  $height = $this->getheight() * $ratio;
		  $this->resize($width,$height);
	   }
		
		// assumes image has width and height values > 0
		public function findDimensionToChange($maxWidth, $maxHeight){
			$width = $this->getWidth();
			$height = $this->getHeight();
			if ($width > $maxWidth){
				if ($height > $maxHeight){
					// figure out ratios and use the most appropriate one
					$whratio = $width / $height;
					if ($width - $maxWidth > $height * $whratio - $maxHeight)
					{
						return "width";
					}
					else return "height";
				}
				else {
					// $width is the limiting factor
					return "width";
				}
			}
			else if ($height > $maxHeight) {
				// height is the limiting factor
				return "height";
			}
			return false;
		}


	   public function scale($scale) {
		  $width = $this->getWidth() * $scale/100;
		  $height = $this->getheight() * $scale/100;
		  $this->resize($width,$height);
	   }

	   public function resize($final_width,$final_height) {
		
		$image_resized = imagecreatetruecolor( $final_width, $final_height );
		$image = $this->image;  
	   
		if ( ($this->image_type == IMAGETYPE_GIF) || ($this->image_type == IMAGETYPE_PNG) ) {
		  
		  $trnprt_indx = imagecolortransparent($image);
	   
		  // If we have a specific transparent color
		  if ($trnprt_indx >= 0) {
	   
			// Get the original image's transparent color's RGB values
			$trnprt_color    = imagecolorsforindex($image, $trnprt_indx);
	   
			// Allocate the same color in the new image resource
			$trnprt_indx    = imagecolorallocate($image_resized, $trnprt_color['red'], $trnprt_color['green'], $trnprt_color['blue']);
	   
			// Completely fill the background of the new image with allocated color.
			imagefill($image_resized, 0, 0, $trnprt_indx);
	   
			// Set the background color for new image to transparent
			imagecolortransparent($image_resized, $trnprt_indx);
	   
		 
		  }
		  // Always make a transparent background color for PNGs that don't have one allocated already
		  elseif ($this->image_type == IMAGETYPE_PNG) {
	   
			// Turn off transparency blending (temporarily)
			imagealphablending($image_resized, false);
	   
			// Create a new transparent color for image
			$color = imagecolorallocatealpha($image_resized, 0, 0, 0, 127);
			
			// Completely fill the background of the new image with allocated color.
			imagefill($image_resized, 0, 0, $color);
	   
			// Restore transparency blending
			imagesavealpha($image_resized, true);
		  }
		}

		imagecopyresampled($image_resized, $image, 0, 0, 0, 0, $final_width, $final_height, $this->getWidth(), $this->getHeight());
		$this->image = $image_resized;
	   }
		
		// assumes final_height is less than original height (can't crop something to be larger than original)
		public function cropHeight($final_height) {
			$final_width = $this->getWidth();
			
			$image_resized = imagecreatetruecolor( $final_width, $final_height );
			$image = $this->image;  
		   
			if ( ($this->image_type == IMAGETYPE_GIF) || ($this->image_type == IMAGETYPE_PNG) ) {
			  
			  $trnprt_indx = imagecolortransparent($image);
		   
			  // If we have a specific transparent color
			  if ($trnprt_indx >= 0) {
		   
				// Get the original image's transparent color's RGB values
				$trnprt_color    = imagecolorsforindex($image, $trnprt_indx);
		   
				// Allocate the same color in the new image resource
				$trnprt_indx    = imagecolorallocate($image_resized, $trnprt_color['red'], $trnprt_color['green'], $trnprt_color['blue']);
		   
				// Completely fill the background of the new image with allocated color.
				imagefill($image_resized, 0, 0, $trnprt_indx);
		   
				// Set the background color for new image to transparent
				imagecolortransparent($image_resized, $trnprt_indx);
		   
			 
			  }
			  // Always make a transparent background color for PNGs that don't have one allocated already
			  elseif ($this->image_type == IMAGETYPE_PNG) {
		   
				// Turn off transparency blending (temporarily)
				imagealphablending($image_resized, false);
		   
				// Create a new transparent color for image
				$color = imagecolorallocatealpha($image_resized, 0, 0, 0, 127);
				
				// Completely fill the background of the new image with allocated color.
				imagefill($image_resized, 0, 0, $color);
		   
				// Restore transparency blending
				imagesavealpha($image_resized, true);
			  }
			}

			imagecopyresampled($image_resized, $image, 0, 0, 0, ($this->getHeight() - $final_height) / 2, $final_width, $final_height, $final_width, $final_height);
			$this->image = $image_resized;
			
			//$new_image = imagecreatetruecolor($width, $newHeight);
			//imagecopyresampled($new_image, $this->image, 0, 0, 0, ($this->getHeight() - $newHeight) / 2, $width, $newHeight, $width, $newHeight);
			//$this->image = $new_image;
		}
	
	
		// assumes final_height is less than original height (can't crop something to be larger than original)
		public function cropWidth($final_width) {
			$final_height = $this->getHeight();
			
			$image_resized = imagecreatetruecolor( $final_width, $final_height );
			$image = $this->image;  
		   
			if ( ($this->image_type == IMAGETYPE_GIF) || ($this->image_type == IMAGETYPE_PNG) ) {
			  
			  $trnprt_indx = imagecolortransparent($image);
		   
			  // If we have a specific transparent color
			  if ($trnprt_indx >= 0) {
		   
				// Get the original image's transparent color's RGB values
				$trnprt_color    = imagecolorsforindex($image, $trnprt_indx);
		   
				// Allocate the same color in the new image resource
				$trnprt_indx    = imagecolorallocate($image_resized, $trnprt_color['red'], $trnprt_color['green'], $trnprt_color['blue']);
		   
				// Completely fill the background of the new image with allocated color.
				imagefill($image_resized, 0, 0, $trnprt_indx);
		   
				// Set the background color for new image to transparent
				imagecolortransparent($image_resized, $trnprt_indx);
		   
			 
			  }
			  // Always make a transparent background color for PNGs that don't have one allocated already
			  elseif ($this->image_type == IMAGETYPE_PNG) {
		   
				// Turn off transparency blending (temporarily)
				imagealphablending($image_resized, false);
		   
				// Create a new transparent color for image
				$color = imagecolorallocatealpha($image_resized, 0, 0, 0, 127);
				
				// Completely fill the background of the new image with allocated color.
				imagefill($image_resized, 0, 0, $color);
		   
				// Restore transparency blending
				imagesavealpha($image_resized, true);
			  }
			}

			imagecopyresampled($image_resized, $image, 0, 0, ($this->getWidth() - $final_width) / 2, 0, $final_width, $final_height, $final_width, $final_height);
			$this->image = $image_resized;
			
			//$new_image = imagecreatetruecolor($width, $newHeight);
			//imagecopyresampled($new_image, $this->image, 0, 0, 0, ($this->getHeight() - $newHeight) / 2, $width, $newHeight, $width, $newHeight);
			//$this->image = $new_image;
		}
	}	
	
	// returns a valid imgName
	function setFileName($name, $type, $isImage)
	{	
		$table = "gallery_image";
		
		// are there any similarly named entities in the db?
		$query = logged_query("SELECT name FROM {$table} WHERE name like '{$name}%.{$type}'",0,array());
		if ($query===false){
			// use the basic name if there aren't
			return $name.'.'.$type;
		}
		else
		{	
			$val = array();
			$basicExists = false;
			// otherwise, give it a bumped name if other like it exist
			$pattern = "/^".$name."(_(\d{1,3}))?\.".$type."$/";
			foreach($query as $entry)
			{
				if (preg_match($pattern, $entry['name'], $matches)){
				echo 'dup';
					if (isset($matches[2])) 
					{
						$val[] = $matches[2];
					}
					else
					{
						$basicExists = true;
					}
				}
			}	
			// always use the basic name if it is available ( previously deleted)
			if (! $basicExists) return $name.'.'.$type;
			
			// basic exists but no numbers exist so return with 0 extension
			if (count($val) == 0) return $name.'_0.'.$type;
			
			// otherwise, return the next number since a number does exist
			return $name.'_'. (max($val) + 1) .'.'.$type;
		}
	}

	/* Generate Random Letters & Numbers String */
	function randString ($length = 20) {
		$string = "";
		$random = array('a','b','c','d','e','f','g','h','i','j','k','l','m','n','o','p','q','r','s','t','u','v','w','x','y','z','A','B','C','D','E','F','G','H','I','J','K','L','M','N','P','Q','R','S','T','U','V','W','X','Y','Z','1','2','3','4','5','6','7','8','9','0');
		for($i=1; $i<=$length; $i++) {
			mt_srand ((double)microtime()*1000000);
			$tmp = mt_rand(0,count($random)-1);
			$string.= $random[$tmp];
		}
		return $string;
	}
	
	$imageUrl = $_config['img_path'];
	$uploadUrl = $_config['upload_url'];
	$galleryId = trim($_POST['gallery_id']);
	$pageType = trim($_POST['page_type']);
	
	/* Validate Uploaded File */
	$errCode = $_FILES['upload_file']['error'];
	if($errCode !=0) {
		if ( $errCode == 1 ) $error = "Error({$errCode}): The uploaded file exceeds the max file size.";// as described in php_ini
		if ( $errCode == 2 ) $error = "Error({$errCode}): The uploaded file exceeds the max file size.";// as described in MAX_FILE_SIZE hidden input
		if ( $errCode == 3 ) $error = "Error({$errCode}): The uploaded file was only partially uploaded.";
		if ( $errCode == 4 ) $error = "Error({$errCode}): No file was uploaded. ";
		if ( $errCode == 5 ) $error = "Error({$errCode}): The uploaded file exceeds the max file size.";
		if ( $errCode == 6 ) $error = "Error({$errCode}): Missing a temporary folder.";
		if ( $errCode == 7 ) $error = "Error({$errCode}): Failed to write file to disk.";
		if ( $errCode == 8 ) $error = "Error({$errCode}): A PHP extension stopped the file upload.";
	} 

	// get file name/type
	$SafeFile = $_FILES['upload_file']['name'];
	$SafeFile = str_replace(" ", "_", $SafeFile);
	$SafeFile = str_replace("#", "Num", $SafeFile);
	$SafeFile = str_replace("$", "Dollar", $SafeFile);
	$SafeFile = str_replace("%", "Percent", $SafeFile);
	$SafeFile = str_replace("^", "", $SafeFile);
	$SafeFile = str_replace("&", "and", $SafeFile);
	$SafeFile = str_replace("*", "", $SafeFile);
	$SafeFile = str_replace("?", "", $SafeFile); 
	$SafeFile = str_replace("'", "", $SafeFile); 
	
	$prep_id = "g{$galleryId}_";
	$type = strtolower(end(explode(".", $SafeFile)));
	$name = substr($SafeFile, 0, -(strlen($type) + 1));
	$name = $prep_id.$name;

	if (!$error) {
		
		$isImage = false;
		if ( in_array($type, array('jpeg', 'jpg', 'gif', 'png') ) ) $isImage = true;
		if ($isImage){	
		/* Process & Save File */
		$p = setFileName($name, $type, $isImage);
	
		// move the source file (download sections of upload files.
		$src = $_config['upload_path'].'/galleries/original/'.$p;
		move_uploaded_file($_FILES['upload_file']['tmp_name'], $src);
		

			$maxWidth = 860; // this needs to be config item
			$maxHeight = 645; //     also
			
			$fullimage = new ImageResizer();
			$fullimage->load($src);
			$dimensionToChange = $fullimage->findDimensionToChange($maxWidth, $maxHeight);
			if ($dimensionToChange == 'width') $fullimage->resizeToWidth($maxWidth);
			else if ($dimensionToChange == 'height') $fullimage->resizeToHeight($maxHeight);
			// otherwise, push through resizing to preserve transparency in gifs and pngs
			else if ($fullimage->image_type == IMAGETYPE_GIF || $fullimage->image_type == IMAGETYPE_PNG) $fullimage->resizeToWidth($fullimage->getWidth());
			$fullimage->save($_config['upload_path'].'/galleries/fullsize/'.$p,$fullimage->image_type);
			
			$image = new ImageResizer();
			$image->load($src);
			$new_height = $image->getHeight();
			$new_width = $image->getWidth();
			if ($new_height > $new_width)
			{
				// push it through the resize function, even if resizing not necessary, to preserve transparency
				$new_width = $new_width > 80 ? 80 : $new_width;
				$image->resizeToWidth($new_width);
				if($image->getHeight() > 80) $image->cropHeight(80);
			} else {
				// push it through the resize function, even if resizing not necessary, to preserve transparency
				$new_height = $new_height > 80 ? 80 : $new_height;
				$image->resizeToHeight($new_height);
				if($image->getWidth() > 80) $image->cropWidth(80);
			}
			
			$image->save($_config['upload_path'].'/galleries/thumb/'.$p,$fullimage->image_type);
			//$thumb = $_config['site_path'].'images/thumb/'.$p;

			logged_query("INSERT INTO gallery_image SET gallery_id = :galleryId, name = ':p', posn='0', alt=':p', date=UTC_TIMESTAMP()",0,array(
				":galleryId" => $galleryId,
				":p" => $p
			));
			$result_id = $_config['db']->getLastInsertId();

			
		} else { $error = "Gallery only accepts image files."; }
			
	
	}
	if ( isset($error) )
	{
		my_log($error);
		$msgType = 'errorMsg';
	}
	else 
	{
		$error = "";
		$msgType = 'successMsg';
	}
	
	if (true) : ?>
	
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<script src="http://ajax.googleapis.com/ajax/libs/jquery/1.7/jquery.min.js"></script>
<script type="text/javascript">
$(document).ready(function(){

parent.$(parent.document).trigger('uploadResult', 
	['<?php echo $error; ?>',
	'<?php echo $msgType; ?>',
	"<?php echo $uploadUrl; ?>",
	"<?php echo $imageUrl; ?>",
	"<?php echo $result_id; ?>",
	"<?php echo $p; ?>",
	"<?php echo $type; ?>",
	"<?php echo $isImage; ?>"]);

});
</script>
</head>  
<body>
</body>
</html>
	
	<?php 
	endif;
	die();
}

//oversized uploads don't pass any Files or post data:
//if nothing else has happened return a trigger to write an error message for the uploader
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<script src="http://ajax.googleapis.com/ajax/libs/jquery/1.7/jquery.min.js"></script>
<script type="text/javascript">
$(document).ready(function(){

parent.$(parent.document).trigger('failedUpload');

});
</script>
</head>  
<body>
</body>
</html>