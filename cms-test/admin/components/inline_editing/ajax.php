<?php
if (array_key_exists('option', $_POST) && $_POST['option'] == "front_edit")
{
	include("../../includes/config.php");

	if(	!isset($_POST['table_name']) || !$_POST['table_name'] || 
		!isset($_POST['id_field']) || !$_POST['id_field'] || 
		!isset($_POST['id_val']) || !$_POST['id_val'] || 
		!isset($_POST['field']) || !$_POST['field']  
	)
	{
		echo json_encode(array('error' => 'Save Failed: insufficient data'));
		die();
	}
	ajax_check_login();
	
	//look for a session_id salted table match
	$result = $_config['db']->query("show tables");
	$tables = array();
	while($row = $result->fetch(PDO::FETCH_NUM)){
		$tables[] = $row[0];
	}
	
	//retrieve table name from hash
	$cur_salt = getTinySalt();
	$table_name = '';
	foreach($tables as $table)
	{	$hashed = hash('md5', $table . $cur_salt);

		if($_POST['table_name'] == hash('md5', $table . $cur_salt))
		{
			$table_name = $table;
			break;
		}
	}
	if(!$table_name)
	{
		echo json_encode(array('error' => 'Save Failed: invalid data'));
		die();
	}
	
	// retrieve field names from hashes
	$result = logged_query("SELECT * FROM `{$table}` LIMIT 1",0,array());
	$id_field = '';
	$field = '';
	if(is_array($result)) : foreach($result[0] as $tmp_field => $dummy)
	{
		if(!$id_field && hash('md5', $tmp_field . $cur_salt) == $_POST['id_field'])
		{
			$id_field = $tmp_field;
			if($field) break;
		}
		elseif(!$field && hash('md5', $tmp_field . $cur_salt) == $_POST['field'])
		{
			$field = $tmp_field;
			if($id_field) break;
		}
		
	} endif;
	
//	var_dump($id_field);
//	var_dump($field);
// die();

	if($table && $id_field && $field)
	{
		// replace the tagged content with its original tag
		$pattern = '#<div class="tinyNoEditTagged" data-id="(.*?)".*?<!-- tagged_end -->#s';
		$replacement = '{{$1}}';	
		$tiny_contents = preg_replace($pattern, $replacement, $_POST["tiny_contents"]);
		
		$result = logged_query("
			UPDATE `{$table}` 
			SET `{$field}`=:tiny_contents 
			WHERE md5(CONCAT(`{$id_field}`,'{$cur_salt}'))=:id_val",
			0,array(":tiny_contents" => $tiny_contents, ":id_val" => $_POST['id_val'] )
		);
		if($result === false)
		{
			echo json_encode(array('error' => 'Save Failed: field not found'));
			die();
		}
	}
	else
	{
		echo json_encode(array('error' => 'Save Failed: invalid data'));
		die();
	}

	die();

}
