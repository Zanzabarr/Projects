<?php
// ajax delete comment
session_start();
include("../../../../includes/config.php");

	if($_POST['action'] == 'approve' && isset( $_SESSION['uid']) ){
		if (isset($_POST['id']))
		{
			$id = $_POST['id'];
			$sqlError = 'success';
			$query = "UPDATE `blog_comments` SET `approve`=1 WHERE id = '{$id}'";
			$result = logged_query($query,0,array(":id" => $id));
			if($result === false) $sqlError = 'Error Approving Comment. ';
			echo $sqlErr;
		}
		else echo 'error';
		
		die();
	}
?>