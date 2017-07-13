<?php
include("../../includes/config.php");

ajax_check_login();

// get options as posted by the uploader
$options = isset($_REQUEST) ? $_REQUEST : null;

$options['max_file_size'] = $_config['upload_max_file_size_Mb']*1024*1024;

// the subclass is used to handle db updates for the folder defined in config
$subclass = false; 
if (isset($_REQUEST['file_name']) ) {
	if (isset($_config['multi_uploader'][$_REQUEST['file_name']]))
		$options['image_versions'] = $_config['multi_uploader'][$_REQUEST['file_name']];
	$subclass = $_REQUEST['file_name'];
}	
$classname = $subclass ? $subclass . '_uploadhandler' : 'uploadhandler';

// make sure the subclass really exists
//if(!file_exists($_config['admin_includes'] . 'classes/'.$classname.'.php'))
//	$classname = 'uploadhandler';
	
$upload_handler = new $classname($options);


