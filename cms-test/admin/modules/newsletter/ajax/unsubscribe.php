<?php
include("../../../includes/config.php");
include('../includes/functions.php');

if(isset($_POST['email'])) {
	//make a chimp
	$apikey = $_POST['apikey'];
	include('../classes/MailChimp.php');
	$chimp = new MailChimp($apikey);
	//remove subscriber
	logged_query("DELETE FROM newsletter_subs WHERE id = {$_POST['id']}",0,array());
	
	//get groups by list id
	$groups = logged_query("SELECT * FROM newsletter_subs_groups WHERE sub_id = {$_POST['id']}",0,array());
	
	if($groups===false) {
		$response = "No active lists";
	} else {
		logged_query("DELETE FROM newsletter_subs_groups WHERE sub_id = {$_POST['id']}",0,array());
		
		foreach($groups as $unlist) {
			$unsubd = $chimp->call('/lists/unsubscribe',array('apikey'=>$apikey,'id'=>$unlist['list_id'],'email'=>array('email'=>$_POST['email'])));
			$response = "success";
		}
	}
	echo $response;
}
?>