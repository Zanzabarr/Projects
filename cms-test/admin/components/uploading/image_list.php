<?php
//echo print_r($_POST);
//echo print_r($_FILES);
if (true)
{
	include("../../includes/config.php");

	ajax_check_login();
	if(!isset($_GET['images_upload_base']) || !isset($_GET['page_id']) || !isset($_GET['page_id']) || !isset($_GET['page_type']) )
		die();

	$folder = $_GET['folder'];
	$upload_base = $_GET['images_upload_base'];

	$result = logged_query("SELECT * FROM `pictures` WHERE `page_id`=:page_id AND `page_type`=:page_type",0,array(
		":page_id" => $_GET['page_id'],
		":page_type" => $_GET['page_type']
		)
	);
	
	if(is_array($result) && count($result))
	{
		echo '[';
		$list='';
		foreach($result as $image)
		{
			$link = "{$upload_base}{$folder}/fullsize/{$image['filename']}";
			$link = htmlspecialchars($link);
			$filename = $image['alt'] ? htmlspecialchars($image['alt']) : htmlspecialchars($image['filename']);
			$list .= "{title: \"{$filename}\", value: \"{$link}\"},";
		}
		$list = trim($list,',');
		echo $list;
		echo ']';

	}
	
}