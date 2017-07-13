<?php
//echo print_r($_POST);
//echo print_r($_FILES);

// -----------------------------------------------save position-----------------------------------------
if (array_key_exists('option', $_POST) && isset($_POST['image_table_name']) && $_POST['option'] == "save_posn")
{	
	if (! isset($_POST['image_table_name']) ) die();
	$image_table_name = $_POST['image_table_name'];
	
	include("../../includes/config.php");
	
	ajax_check_login();
	
	$image_table_name 	= $_POST['image_table_name'];
	if(preg_match_all('/[^a-zA-Z_]/', $image_table_name, $out) ) die('Could not find images');
	
	// update all in corralIds
	if (isset($_POST['corralIds']) && count($_POST['corralIds']) > 0 )
	{
		$arCorIds = $_POST['corralIds'];
		
		if(! is_web_int($arCorIds[0]) ) die('Could not move image');
		$firstId = $arCorIds[0];
		//Where clause
		$whereClause = "WHERE id='{$firstId}' ";

		for ($index = 1;$index < count($arCorIds); $index++ )
		{ 
			$corId = $arCorIds[$index];
			if(! is_web_int($corId) ) die('Could not move image');
			
			$whereClause .= "OR id='{$corId}' ";
		}
	
		// do updates
		logged_query("UPDATE `{$image_table_name}` SET `posn`='0' {$whereClause}",0,array());
	}
	// update all in penIds
	if (isset($_POST['penIds']) && count($_POST['penIds']) > 0 )
	{
		$arPenIds = $_POST['penIds'];


		foreach ($arPenIds as $key =>$value )
		{ 
			if(! is_web_int($value) ) die('Could not move image');
			if(! is_web_int($key) ) die('Could not move image');
			$posn = $key+1;
			logged_query("UPDATE {$image_table_name} SET `posn` = :posn WHERE id = :value",0,array(":posn" => $posn, ":value" => $value));
		}
	}
	die();
} 

// -----------------------------------------------edit image--------------------------------------------
if (array_key_exists('option', $_POST) && isset($_POST['image_table_name']) && $_POST['option'] == "edit_image") 
{
	include("../../includes/config.php");

	ajax_check_login();
	
	$image_table_name 	= $_POST['image_table_name'];
	if(preg_match_all('/[^a-zA-Z_]/', $image_table_name, $out) ) die('Could not edit image');
	
	if ( ! isset($_POST['alt']) || ! isset($_POST['image_id'])) die('Missing Data');
	$desc = isset($_POST['desc']) ? htmlspecialchars($_POST['desc'], ENT_QUOTES)  : '';
	$html_desc = isset($_POST['html_desc']) ? htmlspecialchars($_POST['html_desc'], ENT_QUOTES) : '';
	$alt = htmlspecialchars($_POST['alt'], ENT_QUOTES);
	$url = isset($_POST['url']) ? htmlspecialchars($_POST['url'], ENT_QUOTES) : "";
	$image_id = $_POST['image_id'];
	
	$query = "UPDATE {$image_table_name} SET `desc`=:desc, `html_desc`=:html_desc, `alt`=:alt";
	if(isset($_POST['url'])) $query .= ",`url`=:url";
	$query .= "  WHERE `id`=:image_id";
	
	$binding = array(
		":desc" => $desc,
		":html_desc" => $html_desc,
		":alt" => $alt,
		":image_id" => $image_id
	);
	if(isset($_POST['url'])) $binding[":url"] = $url;
	
	$success = logged_query($query,0,$binding);
	
	if ($success === false) die('There was an error updating the image data');
	
	die( 'success');
}