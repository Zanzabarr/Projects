<?php
include("../../../includes/config.php");

ajax_check_login();

// ajax approve
if (array_key_exists('action', $_POST) && $_POST['action'] == "approve") {
	if (isset($_POST['id']))
	{
		$id = $_POST['id'];
		$sqlError = 'success';
		$query = "UPDATE `blog_comments` SET `approve`=1 WHERE id = '{$id}'";
		$result = logged_query($query,0,array(":id" => $id));
		if($result === false) $sqlError = 'Error Approving Comment. ';
		echo $sqlError;
	}
	else echo 'error';
	
	die();
}
// ajax delete
elseif (array_key_exists('action', $_POST) && $_POST['action'] == "delete") {

	if (isset($_POST['id']))
	{
		$id = $_POST['id'];
		$sqlError = 'success';
		$query = "DELETE FROM `blog_comments` WHERE id = '{$id}'";
		$result = logged_query($query,0,array(":id" => $id));
		if($result === false) $sqlError = 'Error Deleting Comment. ';
		echo $sqlError;
	}
	else echo 'error';
	
	die();
}