<?php
include("../../../includes/config.php");

ajax_check_login();

if(array_key_exists('action', $_POST) && $_POST['action'] == "delete")
{
	if (isset($_POST['id']))
	{
		$id = $_POST['id'];

		$query = "UPDATE `events` SET id =:neg_id, status = 0 WHERE id = :id";
		$result = logged_query($query,0,array(":neg_id" => -$id, ":id" => $id));
		
		if($result !== false) die('success');
	}
	else echo 'error';
	
	die();
}	

