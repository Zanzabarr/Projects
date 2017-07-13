<?php
include("../../../includes/config.php");  // Load Configuration file

ajax_check_login();

if(array_key_exists('action', $_POST)) {
    if (isset($_POST['id'])) {

        if($_POST['action'] == 'off') $st= '1';
		else $st='0';
        
		logged_query("UPDATE `testimonials` SET `status` = '{$st}' WHERE `id` =:id",0,array(":id" => $_POST['id']) );
    }
	die();
}
