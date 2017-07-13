<?php
include("../../../includes/config.php");

ajax_check_login();

if(array_key_exists('action', $_POST) && $_POST['action'] == "delete")
{
	if (isset($_POST['id']))
	{
		$id = $_POST['id'];
		if(!is_web_int($id)) die('error');

        foreach (glob("{$_config['admin_path']}modules/members/images/memberimages/{$id}_*") as $filename) {

            unlink($filename);
        }

		$query = "UPDATE `members` SET member_image = NULL WHERE id = :id";
		$result = logged_query($query,0,array(":id" => $id));
		
		
		echo $result === false ? 'Error Deleting Image' : 'success';
	}
	else echo 'error';
	
	die();
}	
?>
