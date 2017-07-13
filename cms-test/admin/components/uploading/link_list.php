<?php
//echo print_r($_POST);
//echo print_r($_FILES);
if (true)
{
	include("../../includes/config.php");

	ajax_check_login();
	if(!isset($_GET['images_upload_base']) || !isset($_GET['folder']) || !isset($_GET['page_id']) || !isset($_GET['page_type']) )
		die();

	$folder = $_GET['folder'];
	$upload_base = $_GET['images_upload_base'];

	$files = logged_query("SELECT * FROM `upload_file` WHERE `page_id`=:page_id AND `page_type`=:page_type",0,array(
		":page_id" => $_GET['page_id'],
		":page_type" => $_GET['page_type']
		)
	);
	
	$images = logged_query("SELECT * FROM `pictures` WHERE `page_id`=:page_id AND `page_type`=:page_type",0,array(
		":page_id" => $_GET['page_id'],
		":page_type" => $_GET['page_type']
		)
	);
	
	if( (is_array($files) && count($files)) || (is_array($images) && count($images)))
	{
		echo '[';
		$list='';
		if(count($files)) 
		{
			$list .= "{title: '    FILES', value: ' '},";
			foreach($files as $file)
			{
				$link = "{$upload_base}{$folder}/fullsize/{$file['filename']}";
				$link = htmlspecialchars($link);
				$filename = $file['alt'] ? htmlspecialchars($file['alt']) : htmlspecialchars($file['filename']);
				$list .= "{title: \"{$filename}\", value: \"{$link}\"},";
			}
		}
		
		if(count($images)) 
		{
			$list .= "{title: '    IMAGES', value: ' '},";
			foreach($images as $file)
			{
				$link = "{$upload_base}{$folder}/fullsize/{$file['filename']}";
				$link = htmlspecialchars($link);
				$filename = $file['alt'] ? htmlspecialchars($file['alt']) : htmlspecialchars($file['filename']);
				$list .= "{title: \"{$filename}\", value: \"{$link}\"},";
			}
		}
		$list = trim($list,',');
		echo $list;
		echo ']';

	}
	
}