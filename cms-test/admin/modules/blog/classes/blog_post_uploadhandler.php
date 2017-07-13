<?php 
// notes to use this as a template for other modifications to uploader handler:
//	set the folder_name based on the subclass name
//	set add_img/delete_img to handle database calls for this element
//  target id should be sent by the js post for inserts
//
class blog_post_uploadhandler extends uploadhandler {
	protected $target_id = null;
	protected $folder_name = 'blog_post'; //used throughout
	public function __construct($options)
	{		
		global $_config;
		$this->target_id = isset($options['target_id']) ? $options['target_id'] : null ;
		$this->alt = isset($options['image_alt']) ? $options['image_alt'] : 'Blog Image';
		$options['upload_dir'] = $_config['upload_path'] . $this->folder_name . "/";
		$options['upload_url'] = $_config['upload_url'] . $this->folder_name . "/";
		$options['script_url'] = $_config['admin_url'] . 'js/jquery.fileupload/upload_handler.php';
		$options['delete_type'] = 'POST';
		parent::__construct($options);
	}
	
	// returns the newly inserted id, will be output as part of json array as {"id":value}
	public function add_img($imgname)  
	{  
		//first remove the original file, grab it from db instead of passed name to prevent hackers forcing bad deletes
		$result = logged_query("SELECT `image_name` FROM `blog_post` WHERE `id`=:target_id",0,array(":target_id" => $this->target_id));
	
		// delete files if a filename exists and it isn't the default image
		if(isset($result[0]['image_name']) && $result[0]['image_name'] && $result[0]['image_name'] != 'blog_post_dflt.jpg' )
		{
			$delete_img = $result[0]['image_name'];
			$dir_path = $this->options['upload_dir'];
			$file_path = $dir_path . $delete_img;
			$success = is_file($file_path) && unlink($file_path);
			if ($success) {
				foreach($this->options['image_versions'] as $version => $options) {
					if (!empty($version)) {
						$file_path = $dir_path . $version . '/' . $delete_img;
						if (is_file($file_path)) {
							unlink($file_path);
						}
					}
				}
			}
		}
	
		logged_query("UPDATE `blog_post` SET `image_name`=:image_name,`image_alt`= 'Blog Image' WHERE `id`=:target_id",0,array(
			":target_id" => $this->target_id,
			":image_name" => $imgname
		));
		return $this->target_id;			
	}
	
	
	public function delete_img($imgname)
	{
		$imgname = trim($imgname);
		logged_query("UPDATE `blog_post` SET `image_name` = 'blog_post_dflt.jpg', `image_alt` = 'Blog Image' WHERE `id` =:target_id",0,array(":target_id" => $this->target_id) );
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