<?php
class ecom_model {
	// required parameters
	private $images;	/* array of imagenames in this format:
						** array(
						**	'name'	=> 'example_image.jpg',		[Required]name of imagefile	
						**	'alt'	=> 'Alt Text',				[Optional]Alt text for images(defaults to imagename if not provided
						**	'desc'	=> 'Image Caption'			[Optional]Caption used if provided
						**	'href'	=> 'path to alternate page' [Optional]This should only be used if the thumb bar is being used
						**											independant of the gallery. If set, navigates to alternate
						**											pages instead of triggering the gallery pager
						**
						)
						*/
	private $imagePath;	// path to where those images are stored
	
	// optional parameters: values passed in options array as third parameter
	private	$fullsizeFolder		= 'fullsize';	// name of the folder storing fullsize images
	private	$displaySizeFolder 	= 'mini';		// name of the folder storing images for main display
	private	$thumbFolder 		= 'thumb';		// name of the folder storing thumbs for the navigator
	private $noCaption			= false;		// by default, 	exploded image has caption if included in imgArray
	private $noScrollArrows		= false;		//				main display has navigation arrows
	private $noExplodedImage	= false;		//				main display can be clicked to show fullsize image
	private $numThumbs			= 5;			// the number of thumbs in nav bar
	private $leftArrow			= "&larr;";		// the values to use as arrows (can replace with an image tag)
	private $rightArrow			= "&rarr;";		//		in the main display area	
	private $rightThumbArrow	= "&raquo;";	// the values to use as arrows (can replace with an image tag)
	private $leftThumbArrow		= "&laquo;";	//		in the thumb gallery display area
	
	public function __construct($images, $imagePath, $arOptions = array())
    {
		$this->images 				= $images;
		$this->imagePath 			= $imagePath;
		foreach($arOptions as $option => $value)
		{
			if( isset($this->{$option}) ) $this->{$option} = $value;
			else my_log("Error constructing miniGallery. Invalid Constructor Option:{$option}\n");
		}
	}
	

}	