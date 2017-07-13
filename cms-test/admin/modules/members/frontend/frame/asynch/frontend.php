<?php

include("../../../../../includes/config.php");
include_once($_config['admin_modules']."members/includes/functions.php");

// this may be deprecated
if(array_key_exists('action', $_POST) && $_POST['action'] == "set_modified" && array_key_exists('folder',$_POST))
{
	$logged_in = ftp_logged_in_folder($_POST['folder']);
	
	if(! $logged_in) 
	{
		my_log('ftp folder last modified update failed: not logged in');
		die();
	}
	
	$new_date = ftp_update_modified($_POST['folder']);
	echo json_encode(array('success' => $new_date));
	die();
	
}


if(array_key_exists('action', $_GET) && $_GET['action'] == "get_tree" )
{
	if (!isset($_SESSION['loggedInAsMember']) || ! is_pos_int($_SESSION['loggedInAsMember']) )
	{
		my_log('Ftp folders not loaded: user not logged in');
		echo json_encode(array('error' => 'Not Logged In') );
		die();
	}
	
	$sort_by = isset($_GET['sort_by']) ? $_GET['sort_by'] : 'name';
	$sort_order = isset($_GET['sort_order']) ? $_GET['sort_order'] : 'desc';
	$user_id = $_SESSION['loggedInAsMember'];
	$logpath = $_config['admin_path']."logs/log.txt";
	
	try {

		$fm = new Folder_Manager($_config['secure_uploads'], '', 
			array('sort_by' => $sort_by, 
				  'sort_order' => $sort_order, 
				  'logpath' => $logpath, 
				  'user_id' => $user_id
				  )
		);
		$user_folders = $fm->get_sorted_tree();
		
	} catch (Exception $e) { 

		echo json_encode( array('error' => nl2br( $e->getMessage() ) ) );
		die();
	}
	
	echo json_encode(array('folders' => $user_folders));
	die();
	
}

if(array_key_exists('action', $_POST) && $_POST['action'] == "move_file" )
{
	if (!isset($_SESSION['loggedInAsMember']) || ! is_pos_int($_SESSION['loggedInAsMember']) ) 
	{
		my_log('Ftp folders not loaded: user not logged in');
		echo json_encode(array('error' => 'Not Logged In') );
		die();
	}

	$user_id = $_SESSION['loggedInAsMember'];
	$logpath = $_config['admin_path']."logs/log.txt";
	
	try {

		$fm = new Folder_Manager($_config['secure_uploads'], '', 
			array('logpath' => $logpath, 
				  'user_id' => $user_id
				  )
		);
		$result = $fm->move_file($_POST['fromPath'], $_POST['toPath']);
		
	} catch (Exception $e) { 

		echo json_encode( array('error' => nl2br( $e->getMessage() ) ) );
		die();
	}
	
	echo json_encode($result);
	die();
	
}

if(array_key_exists('action', $_POST) && $_POST['action'] == "rename_file" )
{
	if (!isset($_SESSION['loggedInAsMember']) || ! is_pos_int($_SESSION['loggedInAsMember']) ) 
	{
		my_log('Ftp folders not loaded: user not logged in');
		echo json_encode(array('message' => 'Not Logged In') );
		die();
	}

	$user_id = $_SESSION['loggedInAsMember'];
	$logpath = $_config['admin_path']."logs/log.txt";
	
	try {
		$fm = new Folder_Manager($_config['secure_uploads'], '', 
			array('logpath' => $logpath, 
				  'user_id' => $user_id
				  )
		);
		$result = $fm->rename_file($_POST['from'], $_POST['to'], $_POST['path']);
		
	} catch (Exception $e) { 
		
		echo json_encode( array('message' => nl2br( $e->getMessage() ) ) );
		die();
	}
	
	echo json_encode($result);
	die();
	
}

if(array_key_exists('action', $_POST) && $_POST['action'] == "rename_folder" )
{
	if (!isset($_SESSION['loggedInAsMember']) || ! is_pos_int($_SESSION['loggedInAsMember']) ) 
	{
		my_log('Ftp folders not loaded: user not logged in');
		echo json_encode(array('message' => 'Not Logged In') );
		die();
	}

	$user_id = $_SESSION['loggedInAsMember'];
	$logpath = $_config['admin_path']."logs/log.txt";
	
	try {
		$fm = new Folder_Manager($_config['secure_uploads'], '', 
			array('logpath' => $logpath, 
				  'user_id' => $user_id
				  )
		);
		$result = $fm->rename_folder($_POST['frompath'], $_POST['to']);
		
	} catch (Exception $e) { 
		
		echo json_encode( array('message' => nl2br( $e->getMessage() ) ) );
		die();
	}
	
	// tmp
	//$result = $_POST;
	
	echo json_encode($result);
	die();
	
}

if(array_key_exists('action', $_POST) && $_POST['action'] == "create_folder" )
{
	if (!isset($_SESSION['loggedInAsMember']) || ! is_pos_int($_SESSION['loggedInAsMember']) ) 
	{
		my_log('Ftp folders not loaded: user not logged in');
		echo json_encode(array('message' => 'Not Logged In') );
		die();
	}

	$user_id = $_SESSION['loggedInAsMember'];
	$logpath = $_config['admin_path']."logs/log.txt";
	
	try {
		$fm = new Folder_Manager($_config['secure_uploads'], '', 
			array('logpath' => $logpath, 
				  'user_id' => $user_id
				  )
		);
		$result = $fm->create_folder( $_POST['name'], $_POST['path']);
		
	} catch (Exception $e) { 
		
		echo json_encode( array('message' => nl2br( $e->getMessage() ) ) );
		die();
	}
	
	// tmp
	//$result = $_POST;
	
	echo json_encode($result);
	die();
	
}

if(array_key_exists('action', $_POST) && $_POST['action'] == "delete_folder" )
{
	if (!isset($_SESSION['loggedInAsMember']) || ! is_pos_int($_SESSION['loggedInAsMember']) ) 
	{
		my_log('Ftp folders not loaded: user not logged in');
		echo json_encode(array('error' => 'Not Logged In') );
		die();
	}

	$user_id = $_SESSION['loggedInAsMember'];
	$logpath = $_config['admin_path']."logs/log.txt";
	
	try {
		$fm = new Folder_Manager($_config['secure_uploads'], '', 
			array('logpath' => $logpath, 
				  'user_id' => $user_id
				  )
		);
		$result = $fm->delete_folder($_POST['path']);
		
	} catch (Exception $e) { 
		
		echo json_encode( array('error' => nl2br( $e->getMessage() ) ) );
		die();
	}
	
	// tmp
	//$result = $_POST;
	
	echo json_encode($result);
	die();
	
}