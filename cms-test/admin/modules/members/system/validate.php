<?php
/* this page is used as an include only, it returns module data for the sidebar and other functions
** all modules must have a validate page

** passed from caller
** @parm 	$curUser:	array()		all info from the current user's auth_users table entry
** @return	$return:	
**		mixed 
**			bool					false if not valid
**			array(					array of data if valid
**				'mainUrl'	string		url of module's mainpage 
**				'sideBarID'	string		id used in admin/css/styles.css: of course, a corresponding entry must exist in the .sidebar section of the css
**																					a corresponding 'sideBarId'_active also must exist eg
**													eg: .sidebar blogs {...}		normal appearance
**														.sidebar blogs_active {...}	hover rules
**				'pageName'	string		name of the main page		
*/
// validate the user
// currently, not much to it. Haven't developed different user settings for different accounts...eventually will and we'll have a real validation test
//if ($_SESSION['user']['admin'] != 'yes') return false;


// give all required data to build the sidebar element
// NOTE: main styles.css will require .sidebar rules for this module
$return = array();
$return['mainUrl'] 		= $_config['admin_url'] . 'modules/members/members.php';
$return['sideBarId']	= 'members';

return $return;

?>
