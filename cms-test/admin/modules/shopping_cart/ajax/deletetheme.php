<?php
	session_start();
	require_once('../inc/querys.php');
	require_once('../inc/functions.php');
	include("../../../includes/config.php");  // Load Configuration file
	
	check_login();
	
if($_POST['action'] == 'delete'){
	deletetheme($_POST['id']);
}	

