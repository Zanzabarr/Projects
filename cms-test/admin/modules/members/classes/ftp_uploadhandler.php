<?php 
// notes to use this as a template for other modifications to uploader handler:
//	set the folder_name based on the subclass name
//	set add_img/delete_img to handle database calls for this element
//  target id should be sent by the js post for inserts
//
class ftp_uploadhandler extends uploadhandler {
	protected $target_id = null;
	protected $folder_name = 'ftp'; //used throughout
	protected $folder = '';
	public function __construct($options)
	{		
		global $_config;

		$this->target_id = isset($options['target_id']) ? mysql_real_escape_string($options['target_id']) : null ;
		$this->folder = isset($options['folder']) ? $options['folder'] : '';
		
		$options['upload_dir'] = $_config['secure_uploads'] . $this->folder .'/';
		$options['upload_url'] = $_config['secure_uploads'] . $this->folder .'/';
		$options['script_url'] = $_config['admin_url'] . 'modules/members/frontend/frame/upload_handler.php';
		$options['delete_type'] = 'POST';

		parent::__construct($options);
	}
	
	// returns the newly inserted id, will be output as part of json array as {"id":value}
	public function add_img($imgname){}
	
	public function delete_img($imgname){}
	
	// overrides default functionality by adding file_name=$folder_name to the post/get string
	// used by /admin/js/jquery.fileupload/upload_handler.php to sort properly
	protected function set_file_delete_properties($file) {
        $file->delete_url = $this->options['script_url']
            .$this->get_query_separator($this->options['script_url'])
            .'file='.rawurlencode($this->folder . '/' . $file->name)
            .'&_method=DELETE'
			.'&folder=' . $this->folder
			.'&file_name=' . $this->folder_name;
        if ($this->options['access_control_allow_credentials']) {
            $file->delete_with_credentials = true;
        }
    }


}