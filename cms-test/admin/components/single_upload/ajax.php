<?php
//echo print_r($_POST);
//echo print_r($_FILES);


// -----------------------------------------------edit image--------------------------------------------
if (array_key_exists('option', $_POST) && isset($_POST['image_table_name']) && $_POST['option'] == "edit_image") 
{
	include("../../includes/config.php");

	ajax_check_login();
	
	if (! isset($_POST['alt']) || 
		! isset($_POST['image_id']) || 
		! isset($_POST['image_table_name']) ||
		! isset($_POST['image_table_name']) ||
		! isset($_POST['target_alt'])
	) die('Missing Data');
	
	$image_table_name 	= $_POST['image_table_name'];
	$target_alt = $_POST['target_alt'];
	$target_id_name = $_POST['target_id_name'];
	if(	preg_match_all('/[^a-zA-Z_]/', $image_table_name, $out) || 
		preg_match_all('/[^a-zA-Z_]/', $target_alt, $out) || 
		preg_match_all('/[^a-zA-Z_]/', $target_id_name, $out) 
	) die('Could not edit image');
	
	
	$alt = htmlspecialchars($_POST['alt'], ENT_QUOTES);
	$image_id = $_POST['image_id'];
	$success = logged_query("UPDATE {$image_table_name} SET `{$target_alt}`=:alt WHERE `{$target_id_name}`=:image_id",0,array(
		":alt" => $alt,
		":image_id" => $image_id
	));
	if ($success === false) die('Image info not altered');
	die( 'success');
}