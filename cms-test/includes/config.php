<?php
#######################
##        NEW        ##
#######################
// to prevent session bleed between our sites, we need to set the session cookie params and dictate where they start
// on live sites, they start at the web-root
if($_SERVER['HTTP_HOST']=="localhost") session_set_cookie_params(0, "/cms-test"); // subfolder
else session_set_cookie_params(0, "/");


####################################
##        LOCAL SETTINGS          ##
####################################
$_config['test_mobile'] = false;
$_config['is_tablet'] = false; // will only trigger if test_mobile OR REAL TABLET
$_config['is_phone'] = false; // will only trigger if test_mobile OR REAL PHONE
$_config['debug'] = true;								// set to true if debugging is turned on (not for live sites)
$_config['troubleshootdb'] = false;					// set to true while testing modules using mysql_ functions instead of pdo
															// throws exceptions specific to troubleshooting this transition


####################################
##      DATABASE SETTINGS         ##
$_config['sql']['host'] = 'localhost';
$_config['sql']['user'] = 'wordpress';
$_config['sql']['pass'] = 'wordpress';
$_config['sql']['db'] = 'tempcms'; //database name

####################################
##        GLOBAL SETTINGS         ##
####################################
$_config['company_name'] = "Test Cms";					// company name used in various modules
$_config['site'] = "cms-test";								// name of the site

// On Robson: site_path must NOT include the server portion, eg: "http://somedomain.com/";
$_config['site_path'] = "http://localhost/cms-test/";
$_config['rootpath'] = "/var/www/html/cms-test/";			// filepath to where site resides
$_config['admin_folder'] = 'admin/';						// name of the folder that CMS exists in
$_config['menu_type'] = 'top-side';							// currently supports: top, top-side
$_config['home_slug'] = 'home';							// 	the name portion of the home.php file stored in
														//	/includes/custom_pages

// development subroot
//$_config['subroot'] = "/var/";	// edit for local dev, comment or remove on staging or live servers
// dreamhost subroot
$_config['subroot'] = "/var/www/html/";
// robson subroot
//$_config['subroot'] = "/var/www/vhosts/domain.ca/";  // this is the subroot on robson - EDIT WITH PROPER DOMAIN DIRECTORY

$_config['forms_email'] ='rosler19@hotmail.com';
$_config['forms_phone'] ="";

$_config['site_emails'] = array(								// this is an array of valid 'from' emails. They must be addresses set up on our server.
	array('name' => "Ryan Osler", 'email' => 'Rosler19@hotmail.com'),	//	(Spam flags if from address isn't linked to server)
	array('name' => "Admin", 'email' => 'Rosler19@hotmail.com')
);
$_config['multi_user'] = true;							// does this site allow more than one admin user?

####################################
##    internal use: don't alter   ##
####################################
$_config['admin_url'] = $_config['site_path'].$_config['admin_folder'];
$_config['admin_path'] = $_config['rootpath'].$_config['admin_folder'];
$_config['admin_includes'] = $_config['admin_path'] .'includes/';
$_config['admin_modules'] = $_config['admin_path'] .'modules/';
$_config['components'] = $_config['admin_path'] .'components/';
$_config['img_path'] = $_config['admin_url']."images/";
$_config['file'] = $_config['admin_includes']."string.dat";

####################################
##         Special Files          ##
####################################
$_config['site_down_file'] = $_config['rootpath'] . 'includes/menuless_pages/site_down.php'; // We need a boiler plate for this, currently doesn't exist.
								// Good place for custom html error screen with no db calls for when we take down the site or if critical errors (db not found) occur
								// If no file exists, plain text error message appears
####################################
##   tinyMCE Secondary Content    ##
####################################
// in rare cases, we set up a special page that has two or more tinyMCE fields.
// the first field is the one used to access the page in the pages section of the cms, under special pages
// for the backend tinymce to be able to point the subsequent tinyMCE fields at the right page, we need to provide overrides
//   (otherwise, tinyMCE will be pointed at the name of the menuless page)
// eg: home is a special page in custom pages.
//     It has a tinyMCE area, then some php generated code then another tinyMCE content area
//     The first tinyMCE area uses the same name as the special page: home
//	   The second area uses: home2
//		If we did nothing else, in the backend, the jump button would point to /home2 instead of home
// 		So, here, we define that override
// These values will be used in "Pages" to populate the "Special Pages" dropdown so users can select and edit these fields appropriately
//		these "Pages" have a content section but no Meta Tags section since the Meta Tags are set in the main page
//		the key should be constructed with '_' for spaces. The key is used as the dropdown value and converted into a beautified version
//			by replaceing "_" with ' ' and Capitalizing: Choose key names accordingly
$_config['tinymce_secondary_content'] = array();
 $_config['tinymce_secondary_content']['home_part_2'] = 'home#home2';
####################################
##        UPLOAD SETTINGS         ##
####################################
$_config['upload_base'] = 'uploads/';
$_config['upload_url'] = $_config['site_path'] . $_config['upload_base'];
$_config['upload_path'] = $_config['rootpath'] . $_config['upload_base'];
$_config['upload_max_file_size_Mb'] = 10; /* 		measured in Mb

  This value sets the limit for all uploads using the ajax file uploader.


*/
$_config['upload_max_file_size'] = $_config['upload_max_file_size_Mb'] * 1024 * 1024;

// configure the size of your uploaded images (currently doesn't apply to events/members/eCom)
$_config['multi_uploader']['content'] = array (
	'fullsize' 	=> array(				// 	fullsize should be no larger than the width of your
		'max_width' 	=> 990,				//		content area plus padding/margin
		'max_height'	=> 660
	),
	'thumb'	=> array (
		'max_width' 	=> 80,			// this square thumb is used in the backend and gallery frontend page
		'max_height'	=> 80,
		'center'		=> true			// resizes image and crops excess (must have height and width)
	),
	'mini'		=> array(				// only accepts width, image is proportionately resized to this width
		'max_width'		=> 300
	)
);

//****************************//
//***** IMPORTANT NOTE *******//
// the gallery uploader must be set in /admin/modules/gallery/system/config.php

/*
$_config['multi_uploader']['gallery'] = array(
	'fullsize' => array (
		'max_width' 	=> 990,
		'max_height' 	=> 660
	),
	'thumb'	=> array (
		'max_width' 	=> 80,			// this square thumb is used in the backend and gallery frontend page
		'max_height'	=> 80,
		'center'		=> true			// resizes image and crops excess (must have height and width)
	),
	'banner' => array	(				// this rectangular image is used in the frontend as the main image
		'max_width'		=> 690,			// setting a max width and height, if the image exceeds either, it gets scaled
		'max_height'	=> 345			// to fit both
	),
	'slider' => array (
		'max_height'	=> 75,
		'max_width'		=> 200,
		'center'		=> true
	)
);
*/

$_config['multi_uploader']['banners'] = array(
	'rotating' => array (
		'max_width' 	=> 1024,
		'max_height' 	=> 420,
		'center'		=> true
	),
	'thumb'	=> array (
		'max_width' 	=> 80,			// this square thumb is used in the backend and gallery frontend page
		'max_height'	=> 80,
		'center'		=> true			// resizes image and crops excess (must have height and width)
	),
	'banner' => array	(				// this rectangular image is used in the frontend as an inside page banner
		'max_width'		=> 1024,			// setting a max width and height, if the image exceeds either, it gets cropped
		'max_height'	=> 250,
		'center'		=> true
	)
);

$_config['multi_uploader']['ecom'] = array (
	'fullsize' 	=> array(				// 	fullsize should be no larger than the width of your
		'max_width' 	=> 990,				//		content area plus padding/margin
		'max_height'	=> 660
	),
	'thumb'	=> array (
		'max_width' 	=> 80,			// this square thumb is used in the backend and gallery frontend page
		'max_height'	=> 80,
		'center'		=> true			// resizes image and crops excess (must have height and width)
	),
	'mini'		=> array(				// only accepts width, image is proportionately resized to this width
		'max_width'		=> 300
	)
);

$_config['multi_uploader']['blog_post'] = array (
	// fullsize should be no larger than the width of your content area plus padding/margin
	'fullsize' 	=> array( 
		'max_width' 	=> 800,
		'max_height'	=> 600
	),
	// this square thumb is used in the backend and gallery frontend page
	'thumb'	=> array (
		'max_width' 	=> 80,			
		'max_height'	=> 80,
		'center'		=> true // resizes image and crops excess (must have height and width)
	),
	// only accepts width, image is proportionately resized to this width
	'mini'		=> array(
		'max_width'		=> 200,
		'max_height'	=> 200,
		'center'		=> true
	)
);
global $_config;
//phpinfo();

// now initialize this data
include($_config['admin_includes'] . 'initialize.php' );
