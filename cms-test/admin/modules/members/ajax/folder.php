<?php
include("../../../includes/config.php");
include_once($_config['admin_modules']."members/includes/functions.php");
ajax_check_login();

if(array_key_exists('action', $_POST) && $_POST['action'] == "delete")
{
	$id = isset($_POST['id']) && is_pos_int($_POST['id']) ? $_POST['id'] : false;
	if ( ! $id ) die("Folder does not exist");
	
	// remove folder from file structure
	if (! delete_folder($id)) die("Folder does not exist");
	
	// remove all instances of the folder from ftp_users_folders
	logged_query("DELETE from `ftp_user_folders` WHERE `folder_id` = :id",0,array(":id" => $id));
	
	// remove the folder from ftp folder table
	logged_query("DELETE from `ftp_folders` WHERE `id` = :id",0,array(":id" => $id));
	
	die('success');
	
}

if(array_key_exists('action', $_POST) && $_POST['action'] == "name_folder")
{
	// id must be an integer with 0 meaning new folder
	$id = $_POST['id'];
	$newname = trim($_POST['newname']);
	
	if(! $newname) die("No name provided");
	
	if ( ! is_pos_int($id, true) ) die('error');
	
	// validate folder name
	if ( ! preg_match("/^[a-zA-Z0-9 _]+$/", $newname) == 1) die("Branch Name may only contain: letters, numbers or underscore");

	// folder name can only 
	// if id is given, update name
	if($id)
	{
		//get oldname
		$oldname = get_folder($id);
		
		if(! $oldname) die("Original Folder not found");
		
		if($oldname == $newname) die('nochange');
		
		$success = change_folder_name($oldname, $newname);
	} else { 
		// otherwise, create new folder
		$success = create_folder($newname);
		
	}
	echo $success;
	die();
}

if(array_key_exists('action', $_POST) && $_POST['action'] == "status")
{
	$id = isset($_POST['id']) && is_pos_int($_POST['id']) ? $_POST['id'] : false;
	if ( ! $id ) die("User not found");
	
	$new_status = isset($_POST['new_status']) && ($_POST['new_status'] === '0' || $_POST['new_status'] === '1') ? $_POST['new_status'] : false;
	if ($new_status === false) die("Invalid Status");
	
	// attempt to update the users status
	
	// remove the folder from ftp folder table
	$result = logged_query("UPDATE `ftp_folders` SET `status`=:new_status WHERE `id` = :id",0,array(":new_status" => $new_status, ":id" => $id));
	if($result )
		die('success');
	else
		die('User not found');
}

