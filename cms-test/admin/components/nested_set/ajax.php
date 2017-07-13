<?php
//echo print_r($_POST);
//echo print_r($_FILES);

// -----------------------------------------------save position-----------------------------------------
if (array_key_exists('option', $_POST) && $_POST['option'] == "reset_parent")
{	
	include("../../includes/config.php");
	
	ajax_check_login();
	

	$callingClass =  $_POST['callingClass'];	
	$parentId = isset($_POST['parentId']) && is_pos_int($_POST['parentId'],true) ? $_POST['parentId'] : false;
	$childId = isset($_POST['categoryId']) && is_pos_int($_POST['categoryId'],true) ? $_POST['categoryId'] : false;
	
	// Instantiate the required class: must be an extension of nested_set with update/insert/get functions 
	$nestedSet = new $callingClass();
	
	// is this supposed to be going up a level?
	if ( isset($_POST['prev_cat']) && $_POST['prev_cat'] )
	{
		// determine parentId from passed id
		$parentId = $nestedSet->get_parent($parentId);

	}

	// fill the result arrays
	ob_start();
	 $nestedSet->build_path($parentId, $childId );
	$obPath = ob_get_clean();

	ob_start();
	 $nestedSet->build_child_list($childId, $parentId);
	$obList = ob_get_clean();
	
	$preJson = array('path' => $obPath, 'list' => $obList); 
	echo json_encode($preJson );
	die();
} 

