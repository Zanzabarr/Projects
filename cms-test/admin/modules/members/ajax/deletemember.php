<?php
include("../../../includes/config.php");

ajax_check_login();

if(array_key_exists('action', $_POST) && $_POST['action'] == "delete")
{
	if (isset($_POST['id']) && is_web_int($_POST['id']) )
	{
		$id = $_POST['id'];

		$query = "UPDATE `members` SET id = :neg_id, status = 0 WHERE id = :id";
		$result = logged_query($query,0,array(":neg_id" => -$id, ":id" => $id));
		if ($result === false) die('error');
		
		// remove all instances of the folder from ftp_users_folders
		logged_query("DELETE from `ftp_user_folders` WHERE `ftp_user_id` = :id",0,array(":id" => $id));
		if ($result !== false) die('success');
	}
	die('error');
}	

