<?php
include("../../../includes/config.php");
ajax_check_login();

if(empty($_POST['filename']) || empty($_POST['content'])){
	exit;
}

$content = build_csv();

// Sanitizing the filename:
$filename = preg_replace('/[^a-z0-9\-\_\.]/i','',$_POST['filename']);

// generate content:

// Outputting headers:
header("Cache-Control: ");
header("Content-type: text/csv");
header('Content-Disposition: attachment; filename="'.$filename.'"');

echo $content;

function build_csv()
{
	$tmpmembers=logged_query("SELECT *, CONCAT(`last_name`, ', ', `first_name`) as `full_name`  FROM `members` WHERE `id` > 0 ORDER BY `full_name` ASC, `email` ASC",0,array());
	$result = "last name, first name, address, city, state, country, postal_code\n";
	foreach($tmpmembers as $member)
	{
		$last_name = clean($member['last_name']);
		$result .= '"' .clean($member['last_name']) .'",';
		$result .= '"' .clean($member['first_name']) .'",';
		$result .= '"' .clean($member['mailing_address']) .'",';
		$result .= '"' .clean($member['city']) .'",';
		$result .= '"' .clean($member['province_state_region']) .'",';
		$result .= '"' .clean($member['country']) .'",';
		$result .= '"' .clean($member['postal_code']) .'"'."\n";
	}
	return $result;
}

function clean($word)
{
	return str_replace("\"", "'", $word);
}