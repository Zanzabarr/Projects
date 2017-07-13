<?php
//echo print_r($_POST);
//echo print_r($_FILES);

include("../../../includes/config.php");

if(array_key_exists('action', $_POST) && $_POST['action'] == "delete_letter") {
	if (isset($_POST['id']))
	{
		$id = $_POST['id'];
		
		// completely remove the product data
		$result = logged_query("DELETE FROM `newsletter` WHERE `id`={$id}",0,array());
		if ($result===false) 
		{
			echo "Error Removing Newsletter.";
			die();
		}
		die('success');
	}
	else echo 'Could not find Newsletter to delete.';
	die();
}
?>