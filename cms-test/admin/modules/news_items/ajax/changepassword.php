<?php
include("../../../includes/config.php");

ajax_check_login();

if(array_key_exists('submit-pass', $_POST))
{
    if (isset($_POST['id']) && isset($_POST['password']))
    {
        $id = trim($_POST['id']);
        $pw = md5(trim($_POST['password']));

        $query = "UPDATE `news_items` SET password = '{$pw}' WHERE id = '{$id}';";
        $result = logged_query($query);
        
        echo $result===false ? "There was an error." : 'success';
    }
    else echo 'error';

    die();
}
?>
