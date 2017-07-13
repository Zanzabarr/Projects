<?php
// ajax delete comment
session_start();
include("../../../../includes/config.php");

	if($_POST['action'] == 'delete' && isset( $_SESSION['uid']) ){
		if (isset($_POST['id']))
		{
			$id = $_POST['id'];
			$sqlError = 'success';
			$query = "DELETE FROM `blog_comments` WHERE id = '{$id}'";
			$result = logged_query($query,0,array(":id" => $id));
			if($result === false) $sqlError = 'Error Deleting Comment. ';
			echo $sqlErr;
		}
		else echo 'error';
		
		die();
	}
?>