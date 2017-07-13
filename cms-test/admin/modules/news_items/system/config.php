<?php
//**** news_items Customization ****//
$slugName   =   'news_items';       // slug name
$prettyName =   'news_items';       // display name
//****************************//

// set the custom name: slugName is the uri slug associated with the module named 'news_items'
$_config['customNames'][$slugName] = 'news_items';
// set the path to the frontend page
$_config['path']['news_items'] = $_config['site_path'] .$slugName . '/';


// mod_special is an array of key value pairs used to link a page (in edit_page.php) to a module slug
        // Key is the name of the special page (as it appears as a select option) 
    // value is the slug name; 
$_config['mod_special'][$prettyName] = $slugName;

?>
