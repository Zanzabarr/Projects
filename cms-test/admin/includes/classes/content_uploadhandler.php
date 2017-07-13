<?php 
// notes to use this as a template for other modifications to uploader handler:
//	set the folder_name based on the subclass name
//	set add_img/delete_img to handle database calls for this element
//  target id should be sent by the js post for inserts
//
class content_uploadhandler extends uploadhandler {
	protected $target_id = null;
	protected $folder_name = 'content'; //used throughout
	public function __construct($options)
	{		
		global $_config;
		
		//optional parms for addition to db
		$this->target_id = isset($options['target_id']) ? $options['target_id'] : null ;
		$this->target_id_field = isset($options['target_id_field']) ? $options['target_id_field'] : null ;
		$this->page_type = isset($options['page_type']) ? $options['page_type'] : null;
	
		$options['upload_dir'] = $_config['upload_path'] . $this->folder_name . "/";
		$options['upload_url'] = $_config['upload_url'] . $this->folder_name . "/";
		$options['script_url'] = $_config['admin_url'] . 'js/jquery.fileupload/upload_handler.php';
		$options['delete_type'] = 'POST';
		parent::__construct($options);
	}
	
	// returns the newly inserted id, will be output as part of json array as {"id":value}
	public function add_img($imgname)  
	{  
		global $_config;
		
//////////////////////////////////////////////////////////	
	//look for a session_id salted table match
	$result = $_config['db']->query("show tables");
	
	
	//retrieve table name from hash
	$cur_salt = getTinySalt();
	$table_name = '';
	if($this->page_type == hash('md5', 'dflt'.$cur_salt) ) 
	{
		$this->page_type = 'dflt';
		$source_table = 'pages';
	}
	else
	{
		$tables = array();
		$page_type ='';
		$source_table='';
		while($row = $result->fetch(PDO::FETCH_NUM)){
			$tables[] = $row[0];
		}
		foreach($tables as $table)
		{
			if($this->page_type == hash('md5', $table . $cur_salt))
			{
				$this->page_type = $table;
				$source_table = $table;
				break;
			}
		}
		if(!$source_table)
		{
			echo json_encode(array('error' => 'Save Failed: could not add to database1'));
			die();
		}
	}
	// retrieve field names from hashes
	$result = logged_query("SELECT * FROM `{$source_table}` LIMIT 1",0,array());
	$id_field = '';
	if(is_array($result)) : foreach($result[0] as $tmp_field => $dummy)
	{
		if(!$id_field && hash('md5', $tmp_field . $cur_salt) == $this->target_id_field)
		{
			$id_field = $tmp_field;
			break;
		}
		
	} endif;
	if(!$id_field)
	{
		echo json_encode(array('error' => 'Save Failed: could not add to database2'));
		die();
	}	
	// finally, get the unhashed id from the source_table and id_field
	$result = logged_query("
		SELECT `{$id_field}` 
		FROM `{$source_table}` 
		WHERE md5(CONCAT(`{$id_field}`,'{$cur_salt}'))=:target_id",0,array(
		":target_id" => $this->target_id
	));

	if(isset($result[0][$id_field])) $this->target_id = $result[0][$id_field];
	else
	{
		echo json_encode(array('error' => 'Save Failed: could not add to database3'));
		die();
	}

///////////////////////////////////////////////////////////////////////////////////////		
		
		if ($this->is_image($imgname)) $tablename = "pictures";
		else $tablename = "upload_file";

		logged_query("INSERT INTO `{$tablename}` (`page_id`,`page_type`, `filename`, `alt`,`status`) VALUES (:target_id, :page_type,:imgname,:alt,0)",0,array(
			":target_id" => $this->target_id,
			":page_type" => $this->page_type,
			":imgname" => $imgname,
			":alt" => $imgname
			)
		);  
		return $_config['db']->getLastInsertId();  
	}
	
	public function delete_img($imgname)
	{
		if ($this->is_image($imgname)) $tablename = "pictures";
		else $tablename = "upload_file";
		logged_query("DELETE FROM `{$tablename}` WHERE `filename` =:imgname",0,array(":imgname" => $imgname) );
	}
	
	public function is_image($imgname)
	{
		if(!count($this->options['image_versions']) ) return false;
		
		// get the first key in the array
		$key = key($this->options['image_versions']);
		if (! $key) return false;
		
		// see if there is an image file that matches that key in the array;
		$file_path = $this->get_upload_path($imgname, $key);
		
		return is_file($file_path);
		
		//return preg_match('/\.(gif|jpe?g|png)$/i',$imgname);
	}
	
	// overrides default functionality by adding file_name=$folder_name to the post/get string
	// used by /admin/js/jquery.fileupload/upload_handler.php to sort properly
	protected function set_file_delete_properties($file) {
        $file->delete_url = $this->options['script_url']
            .$this->get_query_separator($this->options['script_url'])
            .'file='.rawurlencode($file->name)
            .'&_method=DELETE'
			.'&file_name=' . $this->folder_name;
        if ($this->options['access_control_allow_credentials']) {
            $file->delete_with_credentials = true;
        }
    }


}