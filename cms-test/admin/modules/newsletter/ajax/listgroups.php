<?php
include("../../../includes/config.php");
include('../includes/functions.php');

if(isset($_POST['id'])) {
	//make a chimp
	$apikey = $_POST['apikey'];
	include('../classes/MailChimp.php');
	$chimp = new MailChimp($apikey);
	
	//get groups by list id
	$groups = $chimp->call('lists/interest-groupings',array('apikey'=>$apikey,'id'=>$_POST['id']));
	if(isset($groups['status']) && $groups['status']=="error") {
		$groups = false;
		$response = "No active groups";
	} else {
		foreach ($groups as $grouping){
			$str = "";
			foreach($grouping['groups'] as $g) {
				$check_options[] = array( 
					'id' 	=> 	$g['name'],
					'title' =>	$g['name'],
					'class' => 	$str
				);
			}
		}
		// build the Groups table
		$checked_items = array();
		$check_group_name 		= 'groups';
		$check_group_label 		= 'Groups';
		$check_group_table_id	= 'group_table';
		$check_group_columns 	= 3;
		$check_group_title 		= 'Select the Groups to subscribe this email address to.';
		$as_radio				= false;
		$check_group_class = '';
				
		//display it as a list
		$response = display_checkbox_list(
			$check_options,
			$check_group_name,
			$check_group_label,
			$checked_items,
			$check_group_table_id,
			$check_group_title,
			$check_group_columns,
			$as_radio,
			$check_group_class
		);
	}
	
	//return html
	echo $response;
}
?>