<?php
require('../../../../includes/config.php');

if(! isset($_SESSION['loggedInAsMember']) ) 
{
	echo json_encode( array('error' => 'Log in to FTP' ) );
	die();
}

// get options as posted by the uploader
$options = isset($_REQUEST) ? $_REQUEST : null;
$uh_options = $options;

// the subclass is used to handle db updates for the folder defined in config
if (isset($_config['multi_uploader']['ftp'])) {
	$uh_options['image_versions'] = $_config['multi_uploader']['ftp'];
}	

// validate:
// make sure this is a valid user for this path
try {
$fm = new Folder_Manager($_config['secure_uploads'], $uh_options['folder'], 
		array(
			'logpath' => $_config['admin_path']."logs/log.txt",
			'user_id' => $_SESSION['loggedInAsMember']
		)
	);
// TODO: read only/write only:  $valid_path = $fm->get_cur_branch_restrictions();

} catch (Exception $e) { 
	my_log($e);
	echo json_encode( array('error' => nl2br( $e->getMessage() ) ) );
	die();
}

$upload_handler = new ftp_uploadhandler($uh_options);
// firing the constructor sorts and runs the delete/get/post functions


/**
 * This function creates recursive directories if it doesn't already exist
 *
 * @param String  The path that should be created
 * 
 * @return  void
 */
function create_dirs($path)
{
  if (!is_dir($path))
  {
    $directory_path = "";
    $directories = explode("/",$path);
    array_pop($directories);
   
    foreach($directories as $directory)
    {
      $directory_path .= $directory."/";
      if (!is_dir($directory_path))
      {
        mkdir($directory_path);
        chmod($directory_path, 0777);
      }
    }
  }
}



