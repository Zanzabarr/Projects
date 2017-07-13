<?php
//**** Blog Customization ****//
$slugName 	=	'blog';		  // slug name
$prettyName = 	'blog';		  // display name
//****************************//

// set the custom name: slugName is the uri slug associated with the module named 'blog'
$_config['customNames'][$slugName] = 'blog';
// set the path to the frontend page
$_config['path']['blog'] = $_config['site_path'] .$slugName . '/';


// mod_special is an array of key value pairs used to link a page (in edit_page.php) to a module slug
		// Key is the name of the special page (as it appears as a select option) 
	// value is the slug name; 
$_config['mod_special'][$prettyName] = $slugName; 
											
?>