<?php 
// notes to use this as a template for other modifications to uploader handler:
//	set the folder_name based on the subclass name
//	set add_img/delete_img to handle database calls for this element
//  target id should be sent by the js post for inserts
//
class gallery_uploadhandler extends uploadhandler {
	protected $target_id = null;
	protected $folder_name = 'gallery'; //used throughout
	public function __construct($options)
	{		
		global $_config;
		$this->target_id = isset($options['target_id']) ? $options['target_id'] : null ;
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
		
		if($this->is_image($imgname))
		{
			
			logged_query("INSERT INTO `gallery_image` (`gallery_id`,`posn`,`name`,`alt`,`date`) VALUES (:target_id,0,:imgname,:imgname, NOW())",0,array(
				":target_id" => $this->target_id,
				":imgname" => $imgname
			));

			return $_config['db']->getLastInsertId();			
		}
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
	
	public function delete_img($imgname)
	{
		$imgname = trim($imgname);
		logged_query("DELETE FROM `gallery_image` WHERE `name` =:imgname",0,array(":imgname" => $imgname) );
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