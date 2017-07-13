<?php
####################################
##      DESCRIPTION SETTINGS      ##
####################################
$_config['gallery_img_html_desc'] = true;		// enable html image descriptions under the fullsize image
$_config['gallery_img_text_desc'] = true;		// enable simple text image descriptions (appear as banner under the image when

####################################
##      Gallery Dimensions	      ##
####################################
$_config['multi_uploader']['gallery'] = array(
	'fullsize' => array (				// used as the blown up image when the gallery is clicked
		'max_width' 	=> 990,			// if the image exceeds either value, it is resized to fit both
		'max_height' 	=> 660
	),
	'thumb'	=> array (
		'max_width' 	=> 80,			// this square thumb is used in the backend and gallery frontend page
		'max_height'	=> 80,			// used in SQUARE and STACKED Thumbs
		'center'		=> true			// resizes image and crops excess (must have height and width)
	),
	'banner' => array	(				// this rectangular image is used in the frontend as the main image
		'max_width'		=> 690,			// setting a max width and height, if the image exceeds either, it gets scaled
		'max_height'	=> 345			// to fit both
	),
	'slider' => array (					// used by the Slider
		'max_height'	=> 75			// by setting a max_height, we allow the width to conform to the height
	)									// while still allowing smaller images to be centered in the display area
);												

####################################
##      Gallery Defaults	      ##
####################################
// this is the full list of configurable items for each gallery
// the values set here are used as the default gallery when using tagged pages {{gallery/galleryID#}}
// All these options can be overridden in the tagged page call, find instructions at the bottom of this file
$_config['gallery_display_options'] = array(
	/////////////////////
	// GENERAL OPTIONS //
	/////////////////////
	// names for the different folders storing the different image types
	'fullsizeFolder'	=> 'fullsize',	// fullscreen width images (blown up version)
	'displaySizeFolder' => 'banner',	// version displayed in the gallery
	'thumbFolder' 		=> 'thumb',		// square thumbs
	'sliderFolder'		=> 'slider',	// slider thumbs
	
	// image paths for the different arrows
	'leftArrow'			=> '<img src="admin/modules/galleries/frontend/images/left-shadowed.png" alt="Left">',
	'rightArrow'		=> '<img src="admin/modules/galleries/frontend/images/right-shadowed.png" alt="Right">',
	'rightThumbArrow'	=> '<img src="admin/modules/galleries/frontend/images/right-shadowed-sm.png" alt="Right">',
	'leftThumbArrow'	=> '<img src="admin/modules/galleries/frontend/images/left-shadowed-sm.png" alt="Left">',		
	
	// the gallery Id gets attached to this class. used to keep multiple galleries unique. 
	'uniqueClass'		=> "GalleryID_",
	
	//////////////////////////
	// MAIN GALLERY OPTIONS //
	//////////////////////////
	// location of the placeholder image
	//   by default, uses the dynamically generated placeholder
	//   to customize display, it can be passed to get values: width and height
	//   if only width is passed, original aspect ratio is used (recommended)
	'placeHolder'		=> 'admin/modules/galleries/frontend/dynamic_placeholder.php',
	
	// speed in miliseconds that image cycles: 0 = no cycle
	'cycleSpeed'		=> 0,
	
	// by default, 	exploded image has caption if included in imgArray
	'noExplodedCaption'	=> false,
	
	// main display has navigation arrows
	'noScrollArrows'	=> false,
	
	// main display can be clicked to show fullsize image
	'noExplodedImage'	=> false,
	
	/////////////////////	NOTE: these are the default settings assuming the slider is used as thumbs for a gallery
	// SLIDER OPTIONS  //	  	  stand-alone settings in STAND ALONE SLIDER DEFAULTS below
	/////////////////////         
	// if true, the active slide gets a special border as defined in displaygallery.css 
	// with selector: .sliderActiveBorder .activeLI img
	'sliderActiveBorder'=> true,
	// if true, use the url data associated with the image to create a link for that image
	// NOTE: this url will NOT work if the slider is linked to a gallery
	'sliderUrl'			=> false,
	// if true, the slider images have a border (as defined in displaygallery.css)
	'sliderImageBorder'	=> true,			
	
	//////////////////////////
	// SQUARE THUMB OPTIONS //
	//////////////////////////
	// the number of thumbs in nav bar, square thumbs only
	'numThumbs'			=> 4,

	/////////////////////
	// DEFUNCT OPTIONS // (require further dev)
	/////////////////////
	// if true, display the caption as part of the image
	// NOTE!!: currently, there are issues with this function. Only set to true if you are prepared to troubleshoot
	//         if a client needs this functionality, it will be properly developed at that time.
	//		   additional caveat, each site will need its own custom css for this functionality to properly work.
	'insetCaption'		=> false
);

////////////////////////////////////////
// DEFAULT GALLERY STRUCTURE AND ORDER//
////////////////////////////////////////
//   These are the gallery items that will be displayed in tagged pages: {{gallery/galleryID#}}
// 	 Note that order matters. This array sets what items will be displayed in what order.
$_config['default_gallery_structure'] = array( 
	'gallery',					// the actual gallery
	'caption',					// text captions, must have been enabled in Description Settings above
//	'squareThumbs',				// Our original thumbed navigator (can be used on its own to simply scroll images)
//	'stackedThumbs',			// Another navigator using thumbs, shows all images in gallery in thumb form. All images are shown
	'slider',					// new thumbed navigator, can also be used on its own. Allows variable width thumbs
	'HTMLcaption',				// HTML captions, must have been enabled in Description Settings above
);

//////////////////////////////////
// STAND ALONE SLIDER DEFAULTS  //
//////////////////////////////////
// The above default are for a gallery and it's thumbs
// This set of values defines the basic behaviour of a slider that isn't used as an index for a gallery
// These values will override the above defaults if the tag includes "slider". eg: {{gallery/galleryID#/slider/more/data}}
$_config['gallery_solo_slider_display_options'] = array(
	// if true, the active slide gets a special border as defined in displaygallery.css 
	// with selector: .sliderActiveBorder .activeLI img
	'sliderActiveBorder'=> false,
	// if true, use the url data associated with the image to create a link for that image
	// NOTE: this url will NOT work if the slider is linked to a gallery
	'sliderUrl'			=> true,
	// if true, the slider images have a border (as defined in displaygallery.css)
	'sliderImageBorder'	=> false
);

####################################
##     MODULE TAGGED ELEMENTS     ##
####################################
// array of include paths to 'tagged' elements: 
// if the admin_module config is not set, this call doesn't require this info, set as blank to prevent errors.
$_config['tagged_modules']['gallery'] = isset($_config['admin_modules']) ? $_config['admin_modules'] . "galleries/frontend/tagged_modules/gallery.php" : "";											
####################################
##         USAGE NOTES            ##
####################################
/*
*	Both the gallery structure and all options can be set in the tagged call.
*   gallery structure items are simply added in order
*	options are passed as name value pairs 
*      Note that, since '/' is used as a divider, all '/' in the values must be substituted with '**' (system swaps the values)
*
*   Example, to change the normal values for a single gallery with id 19 to 
*		display only the gallery and the stackedThumbs navigator
*		and change the arrows to a different image,
*       add the following tag to the tinyMCE field you want the gallery to appear in
{{gallery/19/gallery/stackedThumbs/leftArrow/<img src="admin**images**left.png" alt="left">/rightArrow/<img src="admin**images**right.png" alt="right">}}
*/