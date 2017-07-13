<?php
include("../../../includes/config.php");

ajax_check_login();

if(array_key_exists('action', $_POST) && $_POST['action'] == "delete")
{
	if (isset($_POST['id']))
	{
		$id = $_POST['id'];

		$query = "UPDATE `testimonials` SET id = :neg_id, status = 0 WHERE id =:id";
		$success = logged_query($query,0,array(":neg_id" => -$_POST['id'], ":id" => $_POST['id']));
		if($success) die('success');
	}
	die('error');
}	
?>
