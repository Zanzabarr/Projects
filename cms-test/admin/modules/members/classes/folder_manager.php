<?php 
// TODO: module initialization needs to create the folder path at creation


/*	Control access to folders within the system
**	defns: 
** 		base folder:		the top level folder in this group: only contains folders, no files
**		branch: 			second level folders (all are available to the admin and to users with the right id)
** 		legal branch:		branches that are legal for this user(all branches are legal for admin)
** 		current folder:		path (starting with branch) to the current folder (if blank, represents the base folder)
**							eg) "branch1/parta/subpartd", "branch2", ""
**		current children:	subfolder found in the current folder

** functions
** 			sort_alpha()	sets sort as alphabetical
**			sort_date()
**			sort_asc()
**			sort_desc()

**			get_sub_folders($path) returns all sub folders from $path sort_by: alpha, date, sort_order asc, desc
*/
class folder_manager {
	// construtor required
	protected $base_path = "";	// must be set at construction: absolute file path to the top folder (normally should be secure path)
	
	// information about the current path
	protected $branch 			= '';		// name of the current branch as passed from the current path: '' if no branch selected (on base level)
	protected $branch_valid		= false;	// is the current branch valid for this user: true or false: if no branch selected, it is false
	protected $branch_restrictions = 'none'; // valid data: none read only write only 
	
	protected $subfolder		= '';		// path to the current subfolder: '' if no subfolder, can be any depth, folders separated by '/' 
											//  NOTE: does not start or end with '/'

	protected $valid_branches 	= array();	// key/value pair: branch_name/restriction (restriction values: none/read only/write only
											// a branch may be valid but have read or write restrictions
											
	// constructor options
	protected $required_options = array(); // normally, options aren't required, but if there are any, add them here
	
	protected $user_id 			= "1";	// 1 is the default for admin/main user, value must be a positive integer
	protected $sort_by			= 'name';		// how files are sorted, either by 'name' or 'date_updated'
	protected $sort_order		= 'asc';		// order files are sorted in, either 'asc' or 'desc'
	
	protected $error_messages = array(
		1 => "Initialization Error",
		2 => "Unrecognized Data Error"
	);
	protected $logpath 			= "log.txt";
	
	public function __construct($base_path, $path = '', $options=array())
	{
		try {
			// if logpath option exists, set it first
			if(isset($options['logpath']))
			{
				$this->logpath = $options['logpath'];
				unset($options['logpath']);
			}
			// validate and set base path;
			$this->set_base_path($base_path);

			// validate and set the options
			$this->set_options( $options );
			
			// set the valid branches for current user
			$this->set_valid_branches();
			
			// validate and set the path data
			$this->set_path($path);
			unset($_SESSION['ftp']);
			// use this to set the session for read only and denied file access in secure_uploads
			$_SESSION['ftp']['basepath'] = $this->base_path;
			$_SESSION['ftp']['readable_branches'] = $this->valid_branches;

		} catch (Exception $e) { $this->throw_message($e); }
	}
	
	public function get_all_branches()
	{
		try {
		
		echo "TODO: get branch info from the base folder (don't rely on db)";

		} catch (Exception $e) { $this->throw_message($e); }
	}
	
	// get all the valid branches for the passed id
	// returns key/value pair: name/restriction (restriction values: none/read only/write only
	public function get_valid_branches($id)
	{
		try{ 
			
			$this->_get_valid_branches($id); 
		
		} catch (Exception $e) { $this->throw_message($e); }
	}
	
	// same as get_sub_folders but apply the sort options defined at initialization
	public function get_sorted_sub_folders($path="")
	{
		try{

			return $this->_get_sorted_sub_folders($path);
	
		} catch (Exception $e) { $this->throw_message($e); }
	}
	
	// starting at path, get all folders in sorted order
	public function get_sorted_tree($path = '')
	{
		try{
			
			return $this->_get_sorted_tree($path);
	
		} catch (Exception $e) { $this->throw_message($e); }
	}
	
	public function get_sub_folders($path = "")
	{
		try{
		
			return $this->_get_sub_folders($path);
		
		} catch (Exception $e) { $this->throw_message($e); }
	}
	
	public function is_cur_branch_valid()
	{
		return $this->branch_valid;
	}
	
	public function get_cur_branch()
	{
		return $this->branch;
	}
	
	public function get_cur_branch_restrictions()
	{
		return $this->branch_restrictions;
	}
	
	public function move_file($from, $to)
	{
		try {
			return $this->_move_file($from, $to);
			
		} catch (Exception $e) { $this->throw_message($e); }
	}	
	
	public function rename_file($from, $to, $path)
	{
		try {
			return $this->_rename_file($from, $to, $path);
			
		} catch (Exception $e) { $this->throw_message($e); }
	}
	
	public function create_folder($name, $path, $merge = false)
	{
		try {
			return $this->_create_folder($name, $path);
			
		} catch (Exception $e) { $this->throw_message($e); }
	}
	
	public function delete_folder($path)
	{
		try {
			return $this->_delete_folder($path);
			
		} catch (Exception $e) { $this->throw_message($e); }
	}
	
	
	
	public function rename_folder($frompath, $to)
	{
		try {
			return $this->_rename_folder($frompath, $to);
			
		} catch (Exception $e) { $this->throw_message($e); }
	}
	
	// --------------------------------------------- PRIVATE FUNCTIONS ---------------------------------------//	
	
	// ERROR HANLING NOTES
	// 	all public methods need to try-catch with this function in the catch.
	// 	It catches all Exceptions and logs the msg. 
	//	If the Exception has an error code assigned, it throws the error message assigned to that code for outputting
	//									  otherwise, it outputs the original message
	//		
	private function throw_message($e)
	{
		$code = $e->getCode();
		
		if($code)$msg = $this->error_messages[$code];
		else $msg = $e->getMessage();
		
		// log the detailed error description
		$this->logging($e);
		
		// throw the simple error message
		throw new Exception ($msg);
	}
	
	// get all valid immediate children of folder at $path 
	// return array ('name' => "file_name", 'date_updated' => 'last updated')
	private function _get_sub_folders($path = "")
	{
		// get path data
		//  kicks us out if path goes nowhere 
		$path_data = $this->get_path_data($path);
		
		// if this isn't the base level(no branch selected) and branch is invalid, kick us out
		if( $path_data['branch'] && ! $path_data['branch_valid'] ) throw new Exception('Invalid Branch');		
		// get all folders
		$results = array_filter(glob($this->base_path . $path. "/*"), 'is_dir');

		// filter results and add data
		$return = array();
		foreach ( $results as $result)
		{
			$res = explode('/',$result);
			$res = end($res);
			// if this is the base level, we are returning branches: only return branches legal for this user
			// also, don't return the . and .. values
			if ( ( $path_data['branch'] || array_key_exists($res, $this->valid_branches) ) && $res != '.' && $res != '..') 
			{
				$mtime = filemtime($this->base_path . $path .'/'.$res);
				$return[$res] = array(
					'name' 			=> $res,
					'date_updated' 	=> $mtime,
					'local_date' 	=> $this->get_local_date($mtime),
					'local_day'		=> $this->get_local_day($mtime),
				);
				if ($path_data['branch']) $return[$res]['constraint'] = $path_data['branch_restrictions'];
				else $return[$res]['constraint'] = $this->valid_branches[$res];
			}
		}
		return $return;
	}
	
	private function _get_sorted_sub_folders($path="")
	{
		$folders = $this->_get_sub_folders($path);
		// default is desc by name
		if ($this->sort_by != 'name' || $this->sort_order != 'asc')
		{
			// reverse the sort sign if descending
			$sign = $this->sort_order == 'desc' ? -1 : 1;
			if ($this->sort_by != 'name')
			{
				uasort($folders, function($a, $b) use ($sign) {
					return ($a['date_updated']  - $b['date_updated']) * $sign ;
				});	
			} else {
				uasort($folders, function($a, $b) use ($sign) {
					return strcmp($a['name'] , $b['name']) * $sign ;
				});	
			}	
		}			
		return($folders);
	}
	
	private function _get_sorted_tree($path)
	{

		$result = array();
		$subfolder = $this->_get_sorted_sub_folders($path);
		
		foreach ( $subfolder as $key => $folder )
		{
			$cur_path = trim( $path . '/' . $folder['name'], '/' );
			$result[$key] = $folder;
			$result[$key]['path'] = $cur_path;
			$result[$key]['subfolder'] = $this->_get_sorted_tree($cur_path);
		}
		
		return $result;
	}

	private function set_base_path($path)
	{
		// make sure $path ends in a /
		$path = rtrim(rtrim($path), '/') . "/";
		
		if (!file_exists($path)) throw new Exception("error creating base path: {$path}", 1);
		
		$this->base_path = $path;
	}

	private function set_options( $options )
	{
		$errors = array();
		foreach ($this->required_options as $req)
		{
			if ( !array_key_exists($req, $options) ) $errors[] = $req;
		}
		$err_count = count($errors);

		if ($err_count) 
		{
			$msg = "The following required option(s) not set during construction:";
			for($i=0; $i < $err_count; $i++)
			{
				$msg .= $errors[$i];
				if($i != $err_count - 1) $msg .= ", ";
			}
			throw new Exception ($msg, 1);
		}
	
		foreach($options as $opt => $val)
		{
			if($opt == 'user_id' )
			{
				if(! is_pos_int($val, true) ) throw new Exception ('invalid user_id', 1);
			}
			elseif($opt == 'sort_by' ) 
			{
				if($val != 'name' && $val != 'date_updated') throw new Exception ('invalid sort_by: name or date expected', 1);
			}
			elseif($opt == 'sort_order' ) 
			{
				if($val != 'asc' && $val != 'desc') throw new Exception ('invalid sort_order: asc or desc expected', 1);
			}
			elseif($opt == 'logpath') { /* TODO: validate logpath */ }
			else throw new Exception ("invalid option: '{$opt}' with value: '{$val}'", 1);
			$this->$opt = $val;
		}
	}
	
	// set the path related local variables 
	private function set_path ($path)
	{
			// get path data
			$path_data = $this->get_path_data($path);
			
			$this->branch = $path_data['branch'];
			$this->branch_valid = $path_data['branch_valid'];
			$this->branch_restrictions = $path_data['branch_restrictions'];
			$this->subfolder = $path_data['subfolder'];
	}
	
	private function _move_file($from, $to)
	{
		$from_data = $this->get_path_data($from, false);
		$to_data = $this->get_path_data($to);
		
		// we now have a from and to:
		if($from_data['branch_restrictions'] == 'read only') throw new Exception ("Move failed: {$from_data['branch']} is Read Only");
		if($to_data['branch_restrictions'] == 'read only') throw new Exception ("Move failed: {$to_data['branch']} is Read Only");
		
		// still here? put together the paths
		
		$frompath = $this->base_path . $from_data['branch'] . '/';
		if($from_data['subfolder']) $frompath .= $from_data['subfolder'] . '/';
		$frompath .= $from_data['filename'];
		
		$topath = $this->base_path . $to_data['branch'] .'/';
		if($to_data['subfolder']) $topath .= $to_data['subfolder'] . '/';
		$safe_name = $this->get_file_name( $from_data['filename'], $topath );
		$topath = $topath .$safe_name;
		
		if (copy($frompath, $topath)) {
		  unlink($frompath);
		} else throw new Exception ("Could not rename {$from} to {$to}" );
		
		return array('from_data' => $frompath,'to_data' => $topath);
	}
	
	private function _rename_file($from, $to, $path)
	{
		// make sure of trailing /
		$path = trim($path, '/') . '/';
		$from_data = $this->get_path_data($path.$from, false);
		
		
		// we now have a from and to:
		if($from_data['branch_restrictions'] == 'read only') throw new Exception ("Move failed: Branch {$from_data['branch']} is Read Only");
		
		// still here? put together the paths
		
		$safepath = $this->base_path . $from_data['branch'] . '/';
		if($from_data['subfolder']) $safepath .= $from_data['subfolder'] . '/';
		
		$frompath = $safepath.$from_data['filename'];
		
		$safe_name = $this->get_file_name( $to, $safepath );
		$topath = $safepath .$safe_name;

		if (! rename($frompath, $topath) ) throw new Exception ("Could not rename {$from} to {$to}" );
		
		return array('newName' => $safe_name);
	}

	public function _create_folder($name, $path)
	{
		$path = trim($path, '/') . '/';
		$path_data = $this->get_path_data($path);
		
		if( strlen($name) > 200) return array('message'=>'Name too long', 'safename' => $name);
		
		$safename = $this->safe_folder_name($name);
		if ($safename != $name ) return array('message'=>'Invalid Name Entered, please try again', 'safename' => $safename);
	
		$safepath = $this->base_path . $path_data['branch'] . '/';
		if($path_data['subfolder']) $safepath .= $path_data['subfolder'] . '/';
		
		$newpath = $safepath.$safename;
	
		if(is_dir($newpath)) return array('merge' => array('path' => $path, 'name' => $safename));
		
		// still here?
		if (! mkdir($newpath) ) throw new Exception ("Error creating folder:{$name}\n please try another name");
		
		
		return array('newName' => $safename, 'path' => $path, 'newpath' => $path .  $safename);
	}
	
	private function _delete_folder($path)
	{
		$path = trim($path, '/') . '/';
		$path_data = $this->get_path_data($path);
		
		if(!$path_data['branch']) throw new Exception("Error: cannont delete Root");
		if(!$path_data['subfolder']) throw new Exception ("Error: cannot delete Folder Root from here; Log in to Admin");
		
		$fullpath = $this->base_path.$path_data['branch']."/".$path_data['subfolder'];

		$this->rrmdir($fullpath);
		return array('success' => 'success');
	}
	
	
	
	//TODO 
	private function _rename_folder($frompath, $to)
	{
		$frompath = trim($frompath, '/') . '/';
		$from_data = $this->get_path_data($frompath);
		
		return $from_data;
	}
	
	// returns path related data from path
	// by default, treats as folder path, 
	// set isFolder false to get file path data
	private function get_path_data($path, $isFolder = true)
	{
		// make it ignore leading/trailing separators
		$path = trim(trim($path, '/'));
			
		// ensure the folder exists
		$fullpath = $this->base_path . $path;

		// make sure this is a safe path
		$fullpath = $this->truepath($fullpath);
		
		if ( !$fullpath ) throw new Exception ("No Such Folder: {$path} ");
		
		// make sure this path is part of the valid structure
		if ( strpos($fullpath, rtrim($this->base_path, '/') ) !== 0 ) throw new Exception ("No Such Folder: {$path} ");
		
		// is this a file path or folder path?
		if ($isFolder)
		{
			if(!is_dir($fullpath)) throw new Exception ("No Such Folder: {$path} ");
		} else { // a file
			if(!is_file($fullpath)) throw new Exception ("No Such File: {$path} ");
		}
		// separate it on /
		$arPath = explode('/', $path);

		// set the current branch
		$result['branch'] = isset($arPath[0]) ? $arPath[0] : '';
		
		// set true or false for: branch is valid
		if($result['branch_valid'] = array_key_exists($result['branch'], $this->valid_branches))
		{
			$result['branch_restrictions'] = $this->valid_branches[$result['branch']];
		} else $result['branch_restrictions'] = 'none';
		
		
		if (!$isFolder) 
		{
			$result['filename'] = array_pop($arPath);
		}
		
		$result['subfolder'] = '';
		$sfcount = count($arPath);
		for ($i=1; $i< $sfcount; $i++)
		{
			$result['subfolder'] .= $arPath[$i];
			if($i < $sfcount - 1 ) $result['subfolder'] .= '/';
		}
		return $result;
	}
	

	// takes a path and returns false if the first part of the path isn't valid for this user
	// returns the branch name if valid
	private function set_valid_branches()
	{
			$this->valid_branches = $this->_get_valid_branches($this->user_id);
	}
	
	// get all the valid branches for the passed id
	// returns key/value pair: name/restriction (restriction values: none/read only/write only
	private function _get_valid_branches($id)
	{
		// make sure $id is valid type
		if( ! $this->is_pos_int($id, true)) throw new Exception("Invalid Id: {$id}", 2);
		
		$result = logged_query("
			SELECT `folder`, `restriction`
			FROM `ftp_folders` AS f, `ftp_user_folders` AS uf
			WHERE f.id = uf.folder_id
			  AND uf.ftp_user_id = :id
			  AND f.id > 0
			  AND f.status > 0
		",0,array(":id" => $id));
		
		$return = array();
		if($result && count($result)): foreach($result as $row){
			$return[$row['folder']] = $row['restriction'];
		} endif;
		return $return;
	}
	
	private function logging($string, $type='Error')
	{	
		error_log(date("Y-m-d H:i:s")."\t".$type."\t".$_SERVER['SCRIPT_FILENAME']."\n".$string."\n", 3, $this->logpath);
	}
	
	private function is_pos_int($num, $includeZero = false)
	{
		$compareVal = $includeZero ? 0 : 1;
		return (is_numeric($num) && $num >= $compareVal && $num == round($num));
	}	

	private function get_local_date($date)
	{
		$offset = 0;
		if (isset($_SESSION['ftp_tz_offset']) )
		{
			// get the server's timezone offset in minutes
			$server_offset = date('Z') / 60;
			
			$offset = $_SESSION['ftp_tz_offset'] + $server_offset;
		}
		return date('o/M/j g:i A', $date + $offset * 60  );
	}
	
	private function get_local_day($date)
	{
		$offset = 0;
		if (isset($_SESSION['ftp_tz_offset']) )
		{
			// get the server's timezone offset in minutes
			$server_offset = date('Z') / 60;
			
			$offset = $_SESSION['ftp_tz_offset'] + $server_offset;
		}
		return date('M j', $date + $offset * 60  );
	}
	
	private function truepath($path){
    // whether $path is unix or not
    $unipath=strlen($path)==0 || $path{0}!='/';
    // attempts to detect if path is relative in which case, add cwd
    if(strpos($path,':')===false && $unipath)
        $path=getcwd().DIRECTORY_SEPARATOR.$path;
    // resolve path parts (single dot, double dot and double delimiters)
    $path = str_replace(array('/', '\\'), DIRECTORY_SEPARATOR, $path);
    $parts = array_filter(explode(DIRECTORY_SEPARATOR, $path), 'strlen');
    $absolutes = array();
    foreach ($parts as $part) {
        if ('.'  == $part) continue;
        if ('..' == $part) {
            array_pop($absolutes);
        } else {
            $absolutes[] = $part;
        }
    }
    $path=implode('/', $absolutes);
    // resolve any symlinks
    if(file_exists($path) && linkinfo($path)>0)$path=readlink($path);
    // put initial separator that could have been lost
    $path=!$unipath ? '/'.$path : $path;
    return $path;
}
	
	
	protected function safe_folder_name($name) {	
		// remove unsafe characters
		$name = preg_replace("/[^\w\s\d\.\-_~,;:\[\]\(\]\)]/", '', $name);
		$name = preg_replace('/\.{2,}/','.',$name);
		$name = trim($name, '.');
		return $name;
	}
	
	protected function upcount_name_callback($matches) {
        $index = isset($matches[1]) ? intval($matches[1]) + 1 : 1;
        $ext = isset($matches[2]) ? $matches[2] : '';
        return ' ('.$index.')'.$ext;
    }

    protected function upcount_name($name) {
        return preg_replace_callback(
            '/(?:(?: \(([\d]+)\))?(\.[^.]+))?$/',
            array($this, 'upcount_name_callback'),
            $name,
            1
        );
    }

    protected function get_unique_filename($name,$path) {
        while( is_file($path . $name) ) {
            $name = $this->upcount_name($name);
        }
        return $name;
    }

    protected function trim_file_name($name) {
		// remove unsafe characters
		$name = $this->safe_folder_name($name);
		
        // Remove path information and dots around the filename, to prevent uploading
        // into different directories or replacing hidden system files.
        // Also remove control characters and spaces (\x00..\x20) around the filename:
        $name = trim(basename(stripslashes($name)), ".\x00..\x20");
        // Use a timestamp for empty filenames:
        if (!$name) {
            $name = str_replace('.', '-', microtime(true));
        }
        return $name;
    }
	
	// recursivel remove directory/files and all sub-directories/files
	protected function rrmdir($dir)
	{
		if (is_dir($dir)) 
		{
			$objects = scandir($dir);
			foreach ($objects as $object) 
			{
				if ($object != "." && $object != "..") 
				{
					if (filetype($dir."/".$object) == "dir") rrmdir($dir."/".$object); 
					else unlink($dir."/".$object);
				}
			}
			reset($objects);
			rmdir($dir);
		}
	}
	
	//$path has a trailing /
	//assumes a fully validated path
    protected function get_file_name($name,$path) {
        return $this->get_unique_filename(
            $this->trim_file_name($name),
			$path
        );
    }
}