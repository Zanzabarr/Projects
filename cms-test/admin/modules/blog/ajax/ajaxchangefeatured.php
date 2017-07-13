<?php
include("../../../includes/config.php");

ajax_check_login();

if (array_key_exists('action', $_POST) && $_POST['action'] == "change_featured") 
{
	
	if (isset($_POST['id']) && is_pos_int($_POST['id']) && isset($_POST['change_to']) && ($_POST['change_to'] == '0' || $_POST['change_to'] == '1' ) )
	{
		$id = $_POST['id'];
		$change_to = $_POST['change_to'];
		
		// 'update' the featured value
		$result = logged_query("UPDATE `blog_post` SET `featured`={$change_to} WHERE id = {$id}",0,array());
		if ($result===false) 
		{
			echo 'Could not find Product to Change.';
			die();
		}
		echo 'success';
	}
	else echo 'Could not find Product to Change.';
	die();
}