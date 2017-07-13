<?php
include("../../../includes/config.php");

ajax_check_login();

if(array_key_exists('submit-renewal', $_POST))
{
    if (isset($_POST['id']) && isset($_POST['expiry_status']))
    {
        $id = $_POST['id'];
        $expiry_status = $_POST['expiry_status'];

        $currYear = date('Y');

        if($expiry_status == 'expired') {
            $expiryDate = $currYear . '-12-31 23:59:59';    
        }
        elseif($expiry_status == 'renewable') {
            $expiryDate = ($currYear + 1) . '-12-31 23:59:59';    
        } else die("Error Renewing Membership");

        $query = "UPDATE `members` SET membership_expiry = :expiryDate WHERE id = :id;";
        $result = logged_query($query,0,array(":expiryDate" => $expiryDate, ":id" => $id));
       
        if($result === false) echo "Error Renewing Membership";
        else {
           echo date('M j, Y', strtotime($expiryDate));
        }
    }

    die();
}

