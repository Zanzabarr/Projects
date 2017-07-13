<?php
include('config.php');

if(isset($_GET[0]) && is_numeric($_GET[0]))
{
	$width = int $_GET[0];
	
	if(isset($_GET[1]) && is_numeric($_GET[1]))
	{
		$height = int $_GET[1];
	}
	else
	{
		//use the aspect ratio as determined in the config 
		
	}
	
}

$my_img = imagecreate( 200, 200 );
$background = imagecolorallocate( $my_img, 250, 250, 250 );



header( "Content-type: image/png" );
imagepng( $my_img );

imagecolordeallocate( $background );
imagedestroy( $my_img );
