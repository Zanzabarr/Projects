<?php
include("../../../includes/config.php");

ajax_check_login();

if(array_key_exists('action', $_POST) && $_POST['action'] == "delete")
{
	if (isset($_POST['id']))
	{
		$id = trim($_POST['id']);

		$query = "UPDATE `news_items` SET id = '-{$id}', status = 0 WHERE id = '{$id}'";
		$result = logged_query($query,0,array());
		
		echo $result===false ? "There was an error." : 'success';
	}
	else echo 'error';
	
	die();
}	
?>
