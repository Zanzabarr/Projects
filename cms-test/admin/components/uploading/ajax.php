<?php
//echo print_r($_POST);
//echo print_r($_FILES);
if (array_key_exists('option', $_POST) && $_POST['option'] == "update_alt")
{
	include("../../includes/config.php");

	ajax_check_login();
	if(!isset($_POST['file_id']) || ! is_web_int($_POST['file_id'])) die('File not found');
	if(!isset($_POST['type']) || ($_POST['type'] != 'image' && $_POST['type'] != 'file')) die('File not found');
	if(!isset($_POST['alt']) || !trim($_POST['alt'])) die('Name cannot be blank');
	
	$isImage = $_POST['type'] == 'image';
	
	if ($_POST['type'] == 'image') $table = 'pictures';
	else $table = 'upload_file';
	
	$result = logged_query("UPDATE `{$table}` SET `alt`=:alt WHERE `id`=:id",0,array(":alt"=>$_POST['alt'], "id"=>$_POST['file_id']) );
	
	if ($result) return 'success';
}