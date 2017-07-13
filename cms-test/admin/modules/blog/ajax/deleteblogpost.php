<?php
include("../../../includes/config.php");

ajax_check_login();

if(array_key_exists('action', $_POST) && $_POST['action'] == "delete")
{
	if (isset($_POST['id']))
	{
		$id = $_POST['id'];
		$sqlError = false;
		$query = "DELETE FROM `blog_post` WHERE `id` = :id";
		$result = logged_query($query,0,array(":id" => $id));
		if($result === false) $sqlError = 'Error Deleting Post. ';
		
		$query = "DELETE FROM `blog_comments` WHERE `post_id` = :post_id";
		$result = logged_query($query,0,array(":post_id" => $id));
		if($result === false) $sqlError .= 'Error Deleting Comments.';
		
		echo $sqlError ? $sqlErr : 'success';
	}
	else echo 'error';
	die();
}	
?>