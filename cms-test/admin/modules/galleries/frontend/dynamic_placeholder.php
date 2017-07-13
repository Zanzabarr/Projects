<?php
include('../system/config.php');

$dim = array();
foreach($_GET as $dim => $dummy)
{
	$dimensions[] = $dim;
	if(count($dimensions) > 1) end;
}

$width = $_config['multi_uploader']['gallery']['banner']['max_width'];
$height = $_config['multi_uploader']['gallery']['banner']['max_height'];

if(isset($dimensions[0]) && is_numeric($dimensions[0]))
{
	// a width has been passed, set it
	$width = $dimensions[0];
	
	// if a height is passed, set it
	if(isset($dimensions[1]) && is_numeric($dimensions[1]))
	{
		$height = $dimensions[1];		
	}
	else
	{
		//fanciness ensues
		// if the height isn't defined, use the config values to get the aspect ratio and respect that aspect ratio
		// note that height still contains the default value but width has changed
		$ar = $_config['multi_uploader']['gallery']['banner']['max_width'] / $height;
		
		$height = $width / $ar;
		
	}
	
}

// create the image
$my_img = imagecreate( $width, $height );
$background = imagecolorallocate( $my_img, 250, 250, 250 );


// send the image to the browser
header( "Content-type: image/png" );
imagepng( $my_img );

// free memory
imagecolordeallocate( $background );
imagedestroy( $my_img );