<?php
include("../../../includes/config.php");

ajax_check_login();

if(array_key_exists('submit-pass', $_POST))
{
    if (isset($_POST['id']) && isset($_POST['password']))
    {
        $id = $_POST['id'];
        $pw = hasher($_POST['password']);

        $query = "UPDATE `members` SET password =:pw WHERE id =:id;";
        $result = logged_query($query,0,array(":pw" => $pw, ":id" => $id));
        
        if ($result === false ) echo 'error';
        else echo 'success';
    }
    else echo 'error';

    die();
}

