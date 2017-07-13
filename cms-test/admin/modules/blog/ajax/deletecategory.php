<?php
include("../../../includes/config.php");

ajax_check_login();

if(array_key_exists('action', $_POST) && $_POST['action'] == "delete")
{
	if (isset($_POST['id']))
	{
		$id = $_POST['id'];

		$query = "DELETE FROM `blog_cat` WHERE id = '{$id}'";
		$result = logged_query($query,0,array(":id" => $id));
		
		if($result !== false) die('success');
	}
	else echo 'error';
	die();
}