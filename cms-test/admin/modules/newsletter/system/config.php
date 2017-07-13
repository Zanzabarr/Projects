<?php
// set the optional Recipient groups
// if true, group is available on the send newsletter page
$_config['newsletter']['recipient_type'] = array(
	'newsletter' 		=> true,			// if true, check non-member signups on the 'newsletter' checkbox
	'prayer_partners'	=> true,			// send to prayer partners
	'admin' 			=> true,			// send to admin
	'active_members' 	=> false,			// send to active memebers
	'expired_members'	=> false,			// send to expired members
	'additional'		=> true,			// allow write-ins
	'members_newsletter'=> true				// if true, check members for 'eBulletin' on the 'newsletter' checkbox
);


$_config['newsletter']['email'] = "{$_config['forms_email']}";
$_config['newsletter']['name']	= "{$_config['company_name']} Newsletter";

//**** news_items Customization ****//
$slugName   =   'newsletter';       // slug name
$prettyName =   'newsletter';       // display name
//****************************//

// set the custom name: slugName is the uri slug associated with the module named 'news_items'
$_config['customNames'][$slugName] = 'newsletter';
// set the path to the frontend page
$_config['path']['newsletter'] = $_config['site_path'] .$slugName . '/';


// mod_special is an array of key value pairs used to link a page (in edit_page.php) to a module slug
        // Key is the name of the special page (as it appears as a select option) 
    // value is the slug name; 
$_config['mod_special'][$prettyName] = $slugName;

?>
