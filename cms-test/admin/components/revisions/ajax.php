<?php
include("../../includes/config.php");

ajax_check_login();

// ajax delete
if (array_key_exists('version', $_POST) && $_POST['version'] == "removev") {
	
	// validate table name
	$table = $_POST['tname'];
	// must be alpha or _
	// must have _rev in the name
	if(preg_match_all('/[^a-zA-Z_]/', $table) || ! preg_match('/.*_rev.*/i', $table)) die('Could not find revision to delete');
		
	$query = "DELETE FROM `{$table}` WHERE id = :version_id"; 
	$success = logged_query($query,0,array(":version_id" => $_POST['version_id']));
	if(!$success) echo 'Could not find revision to delete';
	else echo 'success';
	die();
}
