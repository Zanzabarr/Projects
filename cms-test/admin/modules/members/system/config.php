<?php
//**** Members Customization ****//
$slugName   =   'members';       // slug name
$prettyName =   'Members';       // display name
//****************************//

//****  Upload Values  ***********************//
$_config['secure_uploads'] = "{$_config['subroot']}secure_uploads_{$_config['site']}/ftp/";    	//secure upload directory (sits above web root)
//NOTE: this does weird stuff in our drafts directory
// This prevents weird permission overwrites. (two copies of secure_uploads existed)

$_config['multi_uploader']['ftp'] = array();

// set the custom name: slugName is the uri slug associated with the module named 'members'
$_config['customNames'][$slugName] = 'members';
// set the path to the frontend page
$_config['path']['members'] = $_config['site_path'] .$slugName . '/';


// mod_special is an array of key value pairs used to link a page (in edit_page.php) to a module slug
        // Key is the name of the special page (as it appears as a select option)
    // value is the slug name;
$_config['mod_special'][$prettyName] = $slugName;
$_config['mod_special'][$prettyName . ' FTP'] = $slugName . '/ftp';
