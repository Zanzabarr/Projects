<?php
class ajaxUpload {
	
	public $imageTable;								// db table that this image is being uploaded to
													// if false, image uploads are disabled
	public $fileTable;								// defaults to false,
													// if false, file uploads are disabled
													// else, this is the table that uploaded files go to
	public $namefield;								// this is the field name in the above tables that the item will be stored
	public $galleryId;								// id of the gallery to be updated
	public $name;									// filename without filetype extension
	public $type;									// filetype
	public $filename;								// $name and $type combined
	public $error 		 		= false;			// upload error string
	public	$msgType 	 		= "errorMsg";		// ajax returns either errorMsg or successMsg
	public $uploadUrl 	 		= '';				// the url this is being uploaded to
	public $imageUrl 	 		= '';
	public $uploadResultFnJS	= 'uploadResult';	// this is the name of the function that receives the js results in an ajax call 
	public $resultId 	 		= ''; 
	public $p 			 		= '';
	public $isImage 	 		= '';
	public $uploadPath;
	public $src			 		= false;
	public $image_size	 		= false;			// an array of image dimensions taken from $_config
	public $isAjax;
	
	public function __construct($imageTable, $fileTable = false, $namefield,  $imageUrl, $uploadUrl, $uploadPath, $preposition = '', $galleryId = '', $isAjax = true)
    {
		// if the uploadPath doesn't exist...build it
		//  buildUploadPathIfEmpty($uploadPath);

		if( $this->error = $this->getFileUploadError() ) 
		{
			$this->sendResponse();
			return;
		}	
		$this->imageTable	= $imageTable;
		$this->fileTable 	= $fileTable;
		$this->namefield 	= $namefield;
		$this->preposition	= $preposition;
		$this->galleryId	= $galleryId;
		$this->isAjax		= $isAjax;
		
		$this->imageUrl 	= $imageUrl;
		$this->uploadUrl 	= $uploadUrl;
		$this->uploadPath	= $uploadPath;

		$safeFile 			= $this->getSafeFileName($galleryId, $preposition );
		$this->name			= $safeFile['name'];
		$this->type			= $safeFile['type'];
		$this->isImage 		= in_array($this->type, array('jpeg', 'jpg', 'gif', 'png') );
		
		// get the image_size config
		global $_config;
		if (isset($_config['image_size']) ) $this->image_size = $_config['image_size'];
		
		if( ! $this->setFileName() ) return; // error has been set

		// upload the original, try as image (and if fail) try as file...if either is successful, set src
		//   if critical error in either one, sends an error response and ends ajax session
		$this->src = $this->uploadOriginalImage();
		if( ! $this->src ) $this->src = $this->uploadFile();
		
		
	}
	
	// used in ajax calls to create a viable filename
	// assumes image stored in a table with image called : 'name'	
	// returns a valid imgName
	public function setFileName()
	{		

		if ($this->isImage && $this->imageTable) $table = $this->imageTable;
		elseif (! $this->isImage && $this->fileTable) $table = $this->fileTable;
		else 
		{ 
			// this is an unsupported upload type
			$this->errorResponse("Error: can't upload '{$this->type}' files");
			return false;
		}
		
		// are there any similarly named entities in the db?
		$query = logged_query("SELECT {$this->namefield} FROM {$table} WHERE {$this->namefield} like '{$this->name}%.{$this->type}'",0,array());
		if ($query === false) echo "Error accessing the database";
		if (empty($query)){
			// use the basic name if there aren't
			$this->filename = $this->name.'.'.$this->type;
		}
		else
		{	
			$val = array();
			$basicExists = false;
			// otherwise, give it a bumped name if other like it exist
			$pattern = "/^".$this->name."(_(\d{1,3}))?\.".$this->type."$/";
			while ($entry = logged_query($query[0][$this->namefield],0,array()))
			{
				if (preg_match($pattern, $entry[$this->namefield], $matches)){
				//echo 'dup';
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
			if (! $basicExists) $this->filename = $this->name.'.'.$this->type;
			
			// basic exists but no numbers exist so return with 0 extension
			elseif (count($val) == 0) $this->filename = $this->name.'_0.'.$this->type;
			
			// otherwise, return the next number since a number does exist
			else $this->filename = $this->name.'_'. (max($val) + 1) .'.'.$this->type;
		}
		return true;
	}

	// checks $_FILES for error code and returns an error string
	public function getFileUploadError()
	{
	/* Validate Uploaded File */
		$errCode = $_FILES['upload_file']['error'];
		$error = false;
		if($errCode !=0) {
			if ( $errCode == 1 ) $error = "Error({$errCode}): The uploaded file exceeds the max file size.";// as described in php_ini
			elseif ( $errCode == 2 ) $error = "Error({$errCode}): The uploaded file exceeds the max file size.";// as described in MAX_FILE_SIZE hidden input
			elseif ( $errCode == 3 ) $error = "Error({$errCode}): The uploaded file was only partially uploaded.";
			elseif ( $errCode == 4 ) $error = "Error({$errCode}): No file was uploaded. ";
			elseif ( $errCode == 5 ) $error = "Error({$errCode}): The uploaded file exceeds the max file size.";
			elseif ( $errCode == 6 ) $error = "Error({$errCode}): Missing a temporary folder.";
			elseif ( $errCode == 7 ) $error = "Error({$errCode}): Failed to write file to disk.";
			elseif ( $errCode == 8 ) $error = "Error({$errCode}): A PHP extension stopped the file upload.";
		} 
		return $error;
	}

	// gallery id is the unique id for this item (page_id/gallery_id/item_id)
	// preposition is the identifier that precedes the image name
	public function getSafeFileName($galleryId, $preposition = '')
	{
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

		// prep_id is being deprecated. Once it is confimed safe to remove, code will be altered throughout.
		$prep_id = $preposition ? "{$preposition}{$galleryId}_" : '';
		$filevar = explode(".",$SafeFile);
		$result['type'] = strtolower(end($filevar));
		$name = substr($SafeFile, 0, -(strlen($result['type']) + 1));
		
		// system still accepts prepositions. However, we are disabling them for the time being.
		// they will eventually be removed completely but are currently only deprecated
	//	$result['name'] = $prep_id.$name;
		$result['name'] = $name;
		
		return $result;
	}

	// returns false if not an image, no imageTable set or upload fails
	public function uploadOriginalImage()
	{
		if( ! $this->isImage || ! $this->imageTable ) return false;
	
		// build the upload path if it doesn't already exist
		$this->buildUploadPath('original');
		// also need to add the .htaccess file!
		
		$src = $this->uploadPath . 'original/' . $this->filename;
		
		// in the event of a critical error, send failed uploaded response
		if (! @move_uploaded_file($_FILES['upload_file']['tmp_name'], $src) ) 
		{
			$this->errorResponse(
				"Error: Failed to upload original image",
				"move_uploaded_file failed in class/ajaxUpload.php: uploadOriginalImage"
			);
			return false; // ajax call died but non-ajax needs a false 

		}
		return $src;
	}
	
	// returns false if is an image, or no fileTable set or upload fails
	public function uploadfile()
	{
		if( $this->isImage || ! $this->fileTable ) return false;
		
		//if( $this->type == 'pdf') $subfolder = 'pdf/';
		//else $subfolder = 'file/';
	
		$subfolder = 'file_download';
		// build the upload path if it doesn't already exist
		$this->buildUploadPath($subfolder);
		
		$src = $this->uploadPath . $subfolder .'/'. $this->filename;
		
		// in the event of a critical error, send failed uploaded response
		if (! @move_uploaded_file($_FILES['upload_file']['tmp_name'], $src) ) 
		{
			$this->errorResponse(
				"Error: Failed to upload file",
				"move_uploaded_file failed in class/ajaxUpload.php: uploadFile"
			);
			return false; // ajax call died but non-ajax needs a false 

		}
		// create a copy in the view_file folder
		$subfolder = 'file_view';
		$this->buildUploadPath($subfolder);
		$dest = $this->uploadPath . $subfolder .'/'. $this->filename;
		
				// in the event of a critical error, send failed uploaded response
		if (! @copy($src, $dest) ) 
		{
			$this->errorResponse(
				"Error: Failed to upload file",
				"copy failed in class/ajaxUpload.php: uploadFile"
			);
			return false; // ajax call died but non-ajax needs a false 

		}

		return $src;
	}
	
	// if the passed subfolder + uploadpath doesn't exist, build it recursively
	private function buildUploadPath($subfolder)
	{	
		$fullpath = $this->uploadPath . $subfolder;
		if (!file_exists($fullpath))
		{
			$old = umask(0);
			@mkdir($fullpath, 0777, true);
			umask($old);
			
			if ($subfolder == 'original' || $subfolder == 'file_download')
			{
				// if this is original folder, need to add the htaccess file
				$fp = fopen($fullpath . '/.htaccess', 'w');
				fwrite($fp, 'ForceType application/octet-stream
Header set Content-Disposition "attachment"');
				fclose($fp);
			}
		}
	}
	
	
	// maintains ratio while putting max width and height constraints on the image
	// if passed values, uses those,
	//	else if config values are set, uses those
	//  else uses default 1200wide by 900high  max
	//  optional: directoryName sets where the files will go.
	public function fullImage($maxWidth = false, $maxHeight = false, $directoryName = 'fullsize')
	{
		// fail if an error has been set during construction
		if($this->error) return false;
		
		if( ! $maxWidth)  $maxWidth  = ($this->image_size !== false) ? $this->image_size['fullsize']['width']  : 1200;
		if( ! $maxHeight) $maxHeight = ($this->image_size !== false) ? $this->image_size['fullsize']['height'] : 900;

		// build the upload path if it doesn't already exist
		$this->buildUploadPath($directoryName);

		$fullimage = new ImageResizer();
		$fullimage->load($this->src);
		$dimensionToChange = $fullimage->findDimensionToChange($maxWidth, $maxHeight);
		if ($dimensionToChange == 'width') $fullimage->resizeToWidth($maxWidth);
		else if ($dimensionToChange == 'height') $fullimage->resizeToHeight($maxHeight);
		// otherwise, push through resizing to preserve transparency in gifs and pngs
		else if ($fullimage->image_type == IMAGETYPE_GIF || $fullimage->image_type == IMAGETYPE_PNG) $fullimage->resizeToWidth($fullimage->getWidth());
		$fullimage->save($this->uploadPath . $directoryName . '/'. $this->filename, $fullimage->image_type);	

		return true;
	}
	// creates mini images (width constrained but not height)
	// if passed values, uses those,
	//	else if config values are set, uses those
	//  else uses default 80wide max
	//  optional: directoryName sets where the files will go.
	public function thumb($maxWidth = false, $directoryName = 'mini') 
	{
		// fail if an error has been set during construction
		if($this->error) return false;
		
		if( ! $maxWidth)  $maxWidth  = ($this->image_size !== false) ? $this->image_size['mini']['width']  : 80;
		
		
		// build the upload path if it doesn't already exist
		$this->buildUploadPath($directoryName);
			
		$image = new ImageResizer();
		$image->load($this->src);
		// push it through the resize function, even if resizing not necessary, to preserve transparency
		$new_width = $image->getWidth() > $maxWidth ? $maxWidth : $image->getWidth();
		$image->resizeToWidth($new_width);
		//if($image->getHeight() > $maxWidth) $image->cropHeight($maxWidth);
		$image->save($this->uploadPath . $directoryName. '/'. $this->filename, $image->image_type);

	}
	
	public function sqrThumb($maxWidth = false, $directoryName = 'thumb')
	{
		// fail if an error has been set during construction
		if($this->error) return false;
		
		if( ! $maxWidth)  $maxWidth  = ($this->image_size !== false) ? $this->image_size['thumb']['width']  : 80;
		
		// build the upload path if it doesn't already exist
		$this->buildUploadPath($directoryName);
		
		$image = new ImageResizer();
		$image->load($this->src);
		$new_height = $image->getHeight();
		$new_width = $image->getWidth();
		if ($new_height > $new_width)
		{
			// push it through the resize function, even if resizing not necessary, to preserve transparency
			$new_width = $new_width > $maxWidth ? $maxWidth : $new_width;
			$image->resizeToWidth($new_width);
			if($image->getHeight() > $maxWidth) $image->cropHeight($maxWidth);
		} else {
			// push it through the resize function, even if resizing not necessary, to preserve transparency
			$new_height = $new_height > $maxWidth ? $maxWidth : $new_height;
			$image->resizeToHeight($new_height);
			if($image->getWidth() > $maxWidth) $image->cropWidth($maxWidth);
		}
		$image->save($this->uploadPath . $directoryName . '/'. $this->filename, $image->image_type);
	}
	
	// constrain by the greater of width or height ratios and crop the other
	// always makes an image exactly to these dimensions
	public function exactSize($newWidth, $newHeight, $directoryName)
	{
		// fail if an error has been set during construction
		if($this->error) return false;
		
		// build the upload path if it doesn't already exist
		$this->buildUploadPath($directoryName);
		
		$image = new ImageResizer();
		$image->load($this->src);
		$dimensionToChange = $image->findDimensionToChange($newWidth, $newHeight);
		if ($dimensionToChange == 'height') 
		{
			$image->resizeToWidth($newWidth);
			if($image->getHeight() > $newHeight) $image->cropHeight($newHeight);
		}
		else if ($dimensionToChange == 'width') 
		{
			$image->resizeToHeight($newHeight);
			if($image->getWidth() > $newWidth) $image->cropWidth($newWidth);
		}
		$image->save($this->uploadPath . $directoryName . '/'. $this->filename, $image->image_type);
	}
	
	public function errorResponse($errorString, $extraLog = false)
	{
		if ($extraLog ) my_log($extraLog);
		$this->error = $errorString;
		$this->sendResponse();
	}
	
	public function successResponse($id)
	{//die($id);
		$this->msgType 	= "successMsg";	
		$this->resultId	= $id;
		$this->sendResponse();
	}
	
	public function sendResponse()
	{
		
		if($this->error) 
		{
			my_log($this->error);
			$this->msgType 	= "errorMsg";
		}
		// if this isn't an ajax call return now that any errors have been handled
		if(! $this->isAjax) return;
	?>
		
<!DOCTYPE HTML >
<html>
<head>
<script src="//ajax.googleapis.com/ajax/libs/jquery/1.7/jquery.min.js"></script>
<script type="text/javascript">
$(document).ready(function(){

parent.$(parent.document).trigger('<?php echo $this->uploadResultFnJS; ?>', 
	['<?php echo $this->error; ?>',
	'<?php echo $this->msgType; ?>',
	"<?php echo $this->uploadUrl; ?>",
	"<?php echo $this->imageUrl; ?>",
	"<?php echo $this->resultId; ?>",
	"<?php echo $this->filename; ?>",
	"<?php echo $this->type; ?>",
	"<?php echo $this->isImage; ?>"]);

});
</script>
</head>  
<body>
</body>
</html>
	
	<?php 
	die();
	}
}