<?php
// FTP FUNCTIONS
function set_valid_folders($id, $folders, $restriction)
{
	// currently, user permissions apply to all folders, system is capable of handling individual assignments
	// apply the restriction to all branches
	
	if(! is_numeric($id)) return false;
	// wipe out all existing folder permissions
	logged_query("DELETE from `ftp_user_folders` WHERE `ftp_user_id` = :id",0,array(":id" => $id));

	// set new folder permissions
	foreach($folders as $fid => $folder)
	{
		$result = logged_query("INSERT INTO `ftp_user_folders` VALUES (:id, :fid, :restriction) ",0,array(":id" => $id, ":fid" => $fid, ":restriction" => $restriction));
		if($result === false) return false;
	}
	return true;
}

function get_valid_status_folders($id = false, $order_by='folder', $sort_order = 'ASC')
{
	return get_valid_folders($id, $order_by, $sort_order, true ); 
}

function buildFolderRow($id, $folder)
{	
	global $_config;
	
	if ($folder)
	{
		$notInput = "class='notInput'";
		$disabled = "disabled='disabled'";
		$folder_status = $folder['status'] ? 'Active' : 'Inactive';
	} else {
		$notInput = '';
		$disabled = "";
		$folder_status = '';
		$folder['folder'] = '';
		$folder['date_updated'] = '';
	}
	
	$menu  ="<span class='folder'><input type='text' {$notInput}  rel='{$folder['folder']}' {$disabled} value='{$folder['folder']}' /></span>"."\n";
	$menu .="<span class='ftp_mod'>{$folder['date_updated']}</span>\n";
	$menu .="<span class='ftp_status'>{$folder_status}</span>\n";
	$menu .="<span class='op_group'>
		<a class='submitName' rel='{$id}' href='#'>
			<img class='tipTop' height='20' style='margin-bottom:2px' title='Submit Folder Name' src='../../images/blue_add.png' alt='Submit' />
		</a>
	";

	$menu .="
		<a class='editFolder'  href='#'>
			<img class='tipTop' title='Edit' src=\"../../images/edit.png\" alt=\"Edit\">
		</a>
		<a class='deleteFolder' href='#' class=\"deletefolder\" rel=\"".$id."\">
			<img class='tipTop' title='Delete' src=\"../../images/delete.png\" alt=\"Delete\">
		</a>
	"."\n";

	
	$menu .="</span>"."\n";// end class op_group
	return $menu;
}

function change_folder_name($oldname, $newname)
{
	global $_config;
	
	// make sure this is a valid folder:
	$valid_folders = get_valid_folders();

	// find the id of the original folder
	//if(! $id = array_search($oldname, $valid_folders) ) return 'Folder Not Found';
	$id = false;
	foreach($valid_folders as $tmp_id => $tmp_folder_data)
	{	
		if($tmp_folder_data['folder'] == $oldname)
		{
			$id = $tmp_id;
			break;
		}
	}
	//reached the end without finding the oldname?
	if($id === false)return 'Folder Not Found';
	
	
	// make sure no duplicate names
	if(in_array($newname, $valid_folders)) return 'That folder name is already in use';
	
	// all is good, try to change name
	$path = $_config['secure_uploads'];
	
	// if successfull, update db
	if( @rename($path.$oldname, $path.$newname) ) {
		$result = logged_query("
			UPDATE `ftp_folders` SET `folder` = :newname WHERE `id` = :id;
		",0,array(":newname" => $newname, ":id" => $id));
		if($result === false) // if there was an error, reverse the rename to remain consistent
		{
			sleep(1);
			if(! @rename($path.$newname, $path.$oldname) ) return "Rename Error: Failed to undo Rename after Database Error";
			return "Database Error: Rename Failed";
		}
		else return "success";
	}
	else return 'Rename Failed';
}

function create_folder($newname)
{
	global $_config;
	
	// make sure no duplicate names
	$valid_folders = get_valid_folders();

	if(in_array($newname, $valid_folders)) return 'That folder name is already in use';
	
	if (build_secure_path($newname)) 
	{	
		//update db and we are done...
		logged_query("INSERT INTO `ftp_folders` (folder) VALUES (:newname) ",0,array(":newname" => $newname));
		return $_config['db']->getLastInsertId();
	} else return 'Could not create new folder';
}

function get_folder($id)
{
	$folder = logged_query("
		SELECT `folder` FROM `ftp_folders` WHERE `id`=:id
	",0,array(":id" => $id));
	return isset($folder[0]['folder']) ? $folder[0]['folder'] : false;
}

// if the passed directory path ( string ) doesn't exist, build it recursively and build the standard child directories 
function build_secure_path($folder)
{
	global $_config;
	
	$uploadPath = $_config['secure_uploads'] . $folder;
	
	if (!file_exists($uploadPath))
	{
		$old = umask(0);
		$success = mkdir($uploadPath, 0777, true);
		umask($old);
		
		return $success;
	}
}

function delete_folder($folder_id)
{
	global $_config;
	
	//get folder name by id
	$folder = get_folder($folder_id);
	
	if(! $folder) return false;
	
	$uploadPath = $_config['secure_uploads'] . $folder;
	rrmdir($uploadPath);
	
	return true;
}

# recursively remove a directory
 function rrmdir($dir) {
   if (is_dir($dir)) {
     $objects = scandir($dir);
     foreach ($objects as $object) {
       if ($object != "." && $object != "..") {
         if (filetype($dir."/".$object) == "dir") rrmdir($dir."/".$object); else unlink($dir."/".$object);
       }
     }
     reset($objects);
     rmdir($dir);
   }
 }

function ftp_logged_in_folder($folder)
{
	if (!isset($_SESSION['ftp_logged_in']) || ! is_pos_int($_SESSION['ftp_logged_in']) ) return false;
	
	$user_id = $_SESSION['ftp_logged_in'];
	
	$result = logged_query_assoc_array("
		SELECT * 
		FROM  ftp_folders, ftp_user_folders
		WHERE ftp_folders.folder = :folder'
		  AND ftp_folders.id = ftp_user_folders.folder_id
		  AND ftp_user_folders.ftp_user_id = :user_id
	",0,array(":folder" => $folder, ":user_id" => $user_id));

	return (bool) count($result);
	
}

function get_valid_folders($id = false, $order_by='folder', $sort_order = 'ASC', $byStatus = false)
{
	global $_config;
	$do_date_sort = false;
	
	// return empty result if sort is not a valid field(folder/status/date_updated,username)
	if(! in_array($order_by, array('folder','status','date_updated','username'))) return array();
	
	if($order_by == 'status') $sort_order = $sort_order == 'ASC' ? 'DESC' : 'ASC';
	if($order_by == 'date_updated'){
		// make the system order by folder
		$order_by = 'folder';
		$do_date_sort = true;
	}
	$statusClause = $byStatus ? 'AND status=1' : '';
	if ($id){
		$resource = logged_query("
			SELECT `folder`, `ftp_folders`.`id`, `status`,`restriction` FROM `ftp_user_folders`, `ftp_folders`
			WHERE `ftp_folders`.`id` = `ftp_user_folders`.`folder_id`
			AND `ftp_user_id` = :id
			{$statusClause}
			ORDER BY `{$order_by}` {$sort_order}
		",0,array(":id"=>$id));
	} else {
		$resource = logged_query("
			SELECT `folder`, `id`, `status` FROM `ftp_folders` ORDER BY `{$order_by}` {$sort_order}
		",0,array());
	}
	
	$result = array();
	if($resource) : foreach($resource as $row) {
		$result[$row['id']] = array( 
			'folder' => $row['folder'], 
			'date_updated' => get_folder_time($row['folder']),
			'status' => $row['status'],
			'mtime' => filemtime($_config['secure_uploads'] . $row['folder'])
		);
		if(isset($row['restriction'])) $result[$row['id']]['restriction'] = $row['restriction'];
		
	}
	endif;
	
	if($do_date_sort){
		// convert the date
		if ($sort_order == 'DESC')
			uasort($result,'cmpTime');
		else uasort($result, 'cmpRevTime');
	}
	
	return $result;
}

function cmpTime($a, $b)
{
	$a = $a['mtime'];
    $b = $b['mtime'];
    if ($a == $b) {
        return 0;
    }
    return ($a < $b) ? -1 : 1;
}function cmpRevTime($a, $b)
{
	$a = $a['mtime'];
    $b = $b['mtime'];
    if ($a == $b) {
        return 0;
    }
    return ($a > $b) ? -1 : 1;
}

// used to find the current folder's time to set up initial state for system
function get_folder_time($folder)
{
	global $_config;
	$path = $_config['secure_uploads'] . $folder;

	if (!file_exists($path)) return false;
	return date('o/M/j g:i A', filemtime($path));
}

function get_local_date($date)
{
	$offset = 0;
	if (isset($_SESSION['ftp_tz_offset']) )
	{
		// get the server's timezone offset in minutes
		$server_offset = date('Z') / 60;
		
		$offset = $_SESSION['ftp_tz_offset'] + $server_offset;
	}
	return date('o/M/j g:i A', strtotime($date) + $offset * 60  );
}
function get_local_day($date)
{
	$offset = 0;
	if (isset($_SESSION['ftp_tz_offset']) )
	{
		// get the server's timezone offset in minutes
		$server_offset = date('Z') / 60;
		
		$offset = $_SESSION['ftp_tz_offset'] + $server_offset;
	}
	return date('M j', strtotime($date) + $offset * 60  );
}


//  MEMBER FUNCTIONS
// get options for displaying members data from members_options table

function getMembersOptions()
{
	
	$options = logged_query("
		SELECT members_options.*, `title`, `desc`, `status`, `first_page_only`, `members_per_pg`, `date`,`environment`,`paypal_on`,`dev_paypal`,`live_paypal`,`dev_orderemail`, `live_orderemail`  
		FROM members_options, members_home, members_paypal as mp;
	",0,array());

	if (is_array($options) && count ($options)) 
	{
		$options[0]['live_paypal'] = decrypt($options[0]['live_paypal']);
		$options[0]['live_orderemail'] = decrypt($options[0]['live_orderemail']);
		return $options[0];
	}
	return $options;
}

function getPaypalData()
{
		$options = logged_query("
		SELECT * 
		FROM members_paypal;
	",0,array());

	if (is_array($options) && count ($options)) 
	{
		$options[0]['live_paypal'] = decrypt($options[0]['live_paypal']);
		$options[0]['live_orderemail'] = decrypt($options[0]['live_orderemail']);
		return $options[0];
	}
	return $options;
}

function getMembersData()
{
    global $_config;

	//create members array
	$members = array();
    $tmpmembers=logged_query("SELECT *, CONCAT(`last_name`, ', ', `first_name`) as `full_name`  FROM `members` WHERE `id` > 0 ORDER BY `full_name` ASC, `email` ASC",0,array());
	if($tmpmembers && count($tmpmembers)) : foreach ($tmpmembers as $member){
		// convert members data to useable format
        if($member['status'] == 0) $member['status_word'] = 'Inactive';
        elseif($member['status'] == 1) $member['status_word'] = 'New';
        elseif($member['status'] == 2) $member['status_word'] = 'Regular';
        elseif($member['status'] == 3) $member['status_word'] = 'Complimentary';
        elseif($member['status'] == 4) $member['status_word'] = 'Honorary';
        elseif($member['status'] == 5) $member['status_word'] = 'Sponsored';
		
        $member['expiry_date_formatted'] = date('M j, Y', strtotime($member['membership_expiry']));
        
        if($member['member_image'] == NULL) {
            // default image
            $member['image_path'] = $_config['admin_url'] . 'modules/members/images/memberimages/default_th35.jpg';  
        }
        else {
            $member['image_path'] = $_config['admin_url'] . 'modules/members/images/memberimages/' . $member['id'] . '_th35_' . $member['member_image'] . '.jpg'; 
        }
 
		// set default data
		
		// update the members array
		$members[$member['id']] = $member;
	} endif;

	return $members;
}

function buildMembersMenu($members, $hasEdit=true)
{
	$menu = "<div class ='members_grp' >";
	foreach($members as $row)
	{
		$menu .= "<div class ='members_row' >"."\n";
		$menu .=	"<div class ='menu_row'>"."\n";
		$menu .= 		buildMenuRow($row, $hasEdit)."\n";
		$menu .=	"</div>"."\n";
		$menu .= "</div>"."\n";// end of members row
	}	
	$menu .= "</div>";// end of members grp
	return $menu;
}

function buildMenuRow($member, $hasEdit=true)
{	
	$name_portion = $hasEdit && ( $member['last_name'] || $member['first_name'] ) ? $member['full_name'] : $member['email'];

	global $_config;
	$menu ="<span class='row_image'><img src=\"".$member['image_path']."\" alt=\"member img\" /></span>"."\n";
	$menu .="<span class='row_email'>".$name_portion."</span>"."\n";
	$menu .= "<span class='row_status'>".$member['status_word']."</span>"."\n";
	$menu .= "<span class='row_expiry'>".$member['expiry_date_formatted']."</span>"."\n";
		
	$menu .="<span class='op_group'>"."\n";
	if($hasEdit)
	{
			$menu .= "
		<a href='members_new.php?memberid=".$member['id']."'>
			<img class='tipTop' title='Edit' src=\"../../images/edit.png\" alt=\"Edit\">
		</a>"."\n";
	}
	$menu .="
		<a  href='members.php?delete=".$member['id']."' class=\"deletemember\" rel=\"".$member['id']."\">
			<img class='tipTop' title='Delete' src=\"../../images/delete.png\" alt=\"Delete\">
		</a>
	"."\n";
	
	$menu .="</span><div class='clearFix'></div>"."\n";// end class op_group
	return $menu;
}

function urlfriendly($mess)
{
	$x = ereg_replace("[^A-Za-z0-9_-]", "_", $mess);
	while(strpos($x, "__")){
		$clean = ereg_replace("__", "_", $x);
	}
	return $clean;

}

function errors()
{
	global $error;
	if(isset($error)){
		echo "<div class=\"error\">".$error."</div>";
	}
	if(isset($_GET['wrongfiletype'])){
		echo "<div class=\"error\">That file type is not supported</div>";
	}
	
	if(isset($_SESSION['SESS_MSG'])){
		echo "<div class=\"error\">".$_SESSION['SESS_MSG']."</div>";
		unset($_SESSION['SESS_MSG']);
	}
	
	if(isset($_SESSION['SESS_MSG_green'])){
		echo "<div class=\"success\">".$_SESSION['SESS_MSG_green']."</div>";
		unset($_SESSION['SESS_MSG_green']);
	}
}

function thumbmaker($source_image, $destination, $thumbSize)
{
    $info = getimagesize($source_image);
    $imgtype = image_type_to_mime_type($info[2]);

    #assuming the mime type is correct
    switch ($imgtype) {
        case 'image/jpeg':
            $source = imagecreatefromjpeg($source_image);
            break;
        case 'image/gif':
            $source = imagecreatefromgif($source_image);
            break;
        case 'image/png':
            $source = imagecreatefrompng($source_image);
            break;
        default:
            return false;
    }

    #Figure out the dimensions of the image and the dimensions of the desired thumbnail
    $width = imagesx($source);
    $height = imagesy($source);
	
    #Do some math to figure out which way we'll need to crop the image
    #to get it proportional to the new size, then crop or adjust as needed
	
	
	
///--------------------------------------------------------
//setting the crop size
//--------------------------------------------------------
if($width > $height) $biggestSide = $width;
else $biggestSide = $height;

//The crop size will be half that of the largest side
$cropPercent = .5;
$cropWidth   = $biggestSide*$cropPercent;
$cropHeight  = $biggestSide*$cropPercent;

//getting the top left coordinate
$c1 = array("x"=>($width-$cropWidth)/2, "y"=>($height-$cropHeight)/2);

//--------------------------------------------------------
// Creating the thumbnail
//--------------------------------------------------------

$thumb = imagecreatetruecolor($thumbSize, $thumbSize);
imagecopyresampled($thumb, $source, 0, 0, $c1['x'], $c1['y'], $thumbSize, $thumbSize, $cropWidth, $cropHeight);


	if (imagejpeg($thumb, $destination, 100)) {
        return true;
    }
    return false;
}

function resize($source_image, $destination, $tn_w)
{
    $info = getimagesize($source_image);
	
    $imgtype = image_type_to_mime_type($info[2]);


    #assuming the mime type is correct
    switch ($imgtype) {
        case 'image/jpeg':
            $source = imagecreatefromjpeg($source_image);
            break;
        case 'image/gif':
            $source = imagecreatefromgif($source_image);
            break;
        case 'image/png':
            $source = imagecreatefrompng($source_image);
            break;
        default:
            echo ('Invalid image type.' . $imgtype  . "  " . "  " . $source_image . "  " . $destination);
            return false;
    }

    #Figure out the dimensions of the image and the dimensions of the desired thumbnail
    $src_w = imagesx($source);
    $src_h = imagesy($source);
	
    #Do some math to figure out which way we'll need to crop the image
    #to get it proportional to the new size, then crop or adjust as needed
	
	$ratio = $src_h / $src_w;
	$new_w = $tn_w;
	$new_h = $tn_w * $ratio;
	
	$newpic = imagecreatetruecolor(round($new_w), round($new_h));
	imagecopyresampled($newpic, $source, 0, 0, 0, 0, $new_w, $new_h, $src_w, $src_h);
    $final = imagecreatetruecolor($new_w, $new_h);


    $backgroundColor = imagecolorallocate($final, 255, 255, 255);
    imagefill($final, 0, 0, $backgroundColor);
    imagecopy($final, $newpic, (($new_w - $new_w)/ 2), (($new_h - $new_h) / 2), 0, 0, $new_w, $new_h);

	if (imagejpeg($final, $destination, 100)) {
        return true;
    }
    return false;
}

function deletememberimg($x)
{
	global $_config;	
	
	logged_query("UPDATE `members` SET `member_image` = NULL WHERE `id` = :x LIMIT 1",0,array(":x" => $x));
	foreach (glob("{$_config['admin_url']}modules/members/images/memberimages/{$x}_*") as $filename) {
	unlink($filename);
	}		
}

function memberimageupload($files, $memberid, &$memberImageString)
{
//make sure this directory is writable!
$extlimit = "yes"; //Limit allowed extensions? (no for all extensions allowed)
//List of allowed extensions if extlimit = yes
$limitedext = array(".gif",".jpg",".png",".jpeg");
	
//the image -> variables
$file_type = $files['member_image']['type'];
$file_name = $files['member_image']['name'];
$file_size = $files['member_image']['size'];
$file_tmp = $files['member_image']['tmp_name'];

//check the file's extension
$ext = strrchr($file_name,'.');
$ext = strtolower($ext);

//uh-oh! the file extension is not allowed!
if (($extlimit == "yes") && (!in_array($ext,$limitedext))) {
$_SESSION['SESS_MSG'] = "Wrong file Type.";
return false;
}

//create a random file name
$rand_name = md5(time());
$rand_name= rand(0,999999999);

/////////////////////////////////
// CREATE THE THUMBNAIL //
////////////////////////////////

global $_config;

//if other images of item exist, delete them
if($file_name != NULL) {
	
	$oldimage = logged_query("SELECT `member_image` FROM `members` WHERE `id` =:memberid LIMIT 1",0,array(":memberid" => $memberid));

	$oimg = $oldimage[0];

	if($oimg != NULL) {
	
		foreach (glob("{$_config['admin_path']}modules/members/images/memberimages/{$memberid}_*") as $filename) {

			unlink($filename);
		}
	}
}
	$md5time = md5(time());

														
	//--------------destination---------------------//				
				
	$destTmp = $_config['admin_path'] . "modules/members/images/memberimages/xxx";
	$destFinalPre = $_config['admin_path'] . "modules/members/images/memberimages/" . $memberid;					
															
	if(!move_uploaded_file($file_tmp, $destTmp)) {
		return false;
	}
				
	if(!resize($destTmp, "{$destFinalPre}_l_{$md5time}{$ext}", 400)) return false;
	$image = imagecreatefromjpeg("{$destFinalPre}_l_{$md5time}{$ext}");
	imagejpeg($image, "{$destFinalPre}_l_{$md5time}.jpg");

	if(!resize($destTmp, "{$destFinalPre}_m_{$md5time}{$ext}", 250)) return false;
	$image = imagecreatefromjpeg("{$destFinalPre}_m_{$md5time}{$ext}");
	imagejpeg($image, "{$destFinalPre}_m_{$md5time}.jpg");
				
	if(!resize($destTmp, "{$destFinalPre}_s_{$md5time}{$ext}", 150)) return false;
	$image = imagecreatefromjpeg("{$destFinalPre}_s_{$md5time}{$ext}");
	imagejpeg($image, "{$destFinalPre}_s_{$md5time}.jpg");
       
	if(!thumbmaker($destTmp, "{$destFinalPre}_th_{$md5time}{$ext}", 100)) return false;
	$image = imagecreatefromjpeg("{$destFinalPre}_th_{$md5time}{$ext}");
	imagejpeg($image, "{$destFinalPre}_th_{$md5time}.jpg");
        
   	if(!thumbmaker($destTmp, "{$destFinalPre}_th50_{$md5time}{$ext}", 50)) return false;
	$image = imagecreatefromjpeg("{$destFinalPre}_th50_{$md5time}{$ext}");
	imagejpeg($image, "{$destFinalPre}_th50_{$md5time}.jpg");
   	
    if(!thumbmaker($destTmp, "{$destFinalPre}_th35_{$md5time}{$ext}", 35)) return false;
	$image = imagecreatefromjpeg("{$destFinalPre}_th35_{$md5time}{$ext}");
	imagejpeg($image, "{$destFinalPre}_th35_{$md5time}.jpg");

	logged_query("UPDATE `members` SET `member_image` = :md5time WHERE `id` = :memberid",0,array(":md5time" => $md5time, ":memberid" => $memberid));

	$memberImageString = $md5time;

	return true;
}

function redirect() {
global $_config;

	echo "<script>
	window.location = '{$_config['site_path']}';
	</script>";
}


function showMemberOptions($start_open) { 
	global $_config; 
?>
 
<div class="member_panel_container">

	<div class="member_panel">
    	
    
    	<a class="logout-button" href="<?php echo $_SERVER['REQUEST_URI'] . '?memberLogout=true'; ?>">Logout</a>
		&nbsp;&nbsp;&nbsp;
		<a class="options-button" data-start_open="<?php echo $start_open; ?>" href="">Options</a>
   		<input type="hidden" class="scriptSRC" value="<?php echo $_config['admin_url'] . 'modules/members/frontend/login/member_panel/member_options_frame/options.php'; ?>" /> 

    	<div class="iframe_area"></div>
	</div><!-- member_panel -->
    
    <div class="clear"></div>
</div><!-- member_panel_container -->
<?php
}


//---------------------------------------------------------------//
function showInitialLinks($options)
{ 
	global $_config; 
	$formActionUrlArr = explode('?', $_SERVER['REQUEST_URI']);
    $formActionUrl = $formActionUrlArr[0];
	$formData = json_encode(array('formActionUrl' => $formActionUrl), JSON_HEX_APOS);
	
	$signup_data = getMembersSignupMessage();
	?>
	<div class="member_panel_container"> 
		
    	<div class="member_panel">
		<?php if ($options['online_signup'] && $signup_data['status']) : ?>
			<a href="<?php echo $_config['path']['members']; ?>create-account#content">Create an Account</a>
			<span class='divider'></span>
		<?php endif; ?>	
			<a class="member-login" data-post='<?php echo $formData; ?>' href="<?php echo "{$_config['admin_url']}modules/members/frontend/login/member_panel/form_login.php"; ?>">My Account</a>
			<a href="#" class="member-login-cancel" style="display:none">Cancel</a>
		</div>
	</div>	
<?php
}

function showLoginForm($errors = array()) { 
    global $_config;
	$formActionUrlArr = explode('?', $_SERVER['REQUEST_URI']);
    $formActionUrl = $formActionUrlArr[0];

    ?>

    <div class="member_panel_container"> 
    	<div class="member_panel">
			<a href="<?php echo $_config['path']['members']; ?>create-account#content">Create an Account</a><span class='divider'></span><a href="<?php echo $_SERVER['REQUEST_URI']; ?>">Cancel</a>
        	
            
    		<?php 
			$formData = array(
				'formActionUrl' => $formActionUrl,
				'login_email' => isset($_POST['login_email']) ? $_POST['login_email'] : '',
				'errors' => $errors
			);
			$formLocation = "{$_config['admin_url']}modules/members/frontend/login/member_panel/form_login.php";
			ajax_form($formLocation, $formData); 
			
			
			?>
    	</div><!-- member_panel -->
        
        <div class="clear"></div>
    </div><!-- member_panel_container -->
<?php } 

function showPageLogin($errors)
{
	global $_config;
	$formActionUrlArr = explode('?', $_SERVER['REQUEST_URI']);
    $formActionUrl = $formActionUrlArr[0] . "#content";
	$formData = array(
		'formActionUrl' => $formActionUrl,
		'username' => isset($_POST['username']) ? $_POST['username'] : '',
		'login_errors' => $errors
	);
	$formLocation = "{$_config['admin_url']}modules/members/frontend/forms/ftp_form.php";
	
	ajax_form($formLocation, $formData); 
	
}

//---------------------------------------------------------------//
function validateLoginForm() {
    $errors = array();

    if(!isset($_POST['login_email']) || !isset($_POST['password']) || !isValidEmailAddress($_POST['login_email'])) {
        $errors['login'] = 'Invalid Email or Password';
    }    

    return $errors;
}

function cleanupTmpPasswords()
{
	return logged_query("UPDATE `members` SET `tmp_password`='', `tmp_password_date`='' WHERE `tmp_password`<>'' AND `tmp_password_date` < NOW() - interval 10 minute",0,array() );
}

function processLoginForm($u,$p)  
{
	global $_config; 
	$errors = array();
	$options=getMembersOptions();
	$result=logged_query("
	SELECT id,password,tmp_password, status, payment_status,confirmation_code,url,pw_change_request
	FROM `members` 
	WHERE email like :u 
	  AND id > 0
	LIMIT 1",0,array(":u" => $u));
	$memberData= isset($result[0]) ? $result[0] : false;
	
	// clear tmp password
	cleanupTmpPasswords();
	$pw_change_request = $memberData['pw_change_request'];
	if ($memberData && (hasher($p, $memberData['password']) ) ) $password_matched = true;
	elseif ($memberData && isset($memberData['tmp_password']) && $memberData['tmp_password'] && (hasher($p, $memberData['tmp_password']) ) ) 
	{
		$password_matched = true;
		$pw_change_request = 1;
	}
	else $password_matched = false;
	
	if($password_matched)
	{	
	  	// remove the tmp_password/date
		// set the pw_change_request to true
		logged_query("
		UPDATE `members` 
		SET `tmp_password`='', `tmp_password_date`='', `pw_change_request`=:pw_change_request
		WHERE `email`=:u;",0,array(":u" => $u, ":pw_change_request" => $pw_change_request)
		);
		
		// correct email (username) and password, determine if member is eligible to login based on memberData
	
		if( $memberData['confirmation_code']  ) 
		{
            // membership awaiting payment
            $errors['login'] = 'Your membership is awaiting confirmation.<br><a href="'.$_config['path']['members'].'confirm/user/'.$memberData['url'].'"><span style="font-size:16px; font-weight:bold">Confirm Now.</span></a>';
        }
        elseif($options['pay_signup'] && $memberData['payment_status'] == 0 ) 
		{
            // membership awaiting payment
            $errors['login'] = 'We do not have a record of payment.  You will not be able to access the site until payment is received.<br /><br /><div><a href="'.$_config['path']['members'].'payment/email/'.$u.'"><span style="font-size:16px; font-weight:bold">Pay My Membership Fee Now.</span></a></div>';
        }
        /*elseif(strtotime($memberData['membership_expiry']) < time()) {
            // membership expired
            $errors['login'] = 'Membership expired.';
        }*/
        elseif($memberData['status'] == 0) 
		{
			// member status set to inactive
            $errors['login'] = 'Your membership is currently inactive. Please contact us to re-activate your membership';
		}
	} 
	else 
	{ 
		$user_phrase = $memberData && $memberData['url'] ? "/user/{$memberData['url']}" : '';
		$errors['login'] = "Invalid Email/Password";//<br><a href='{$_config['path']['members']}reset{$user_phrase}'>Forgot Password?</a>
    } 
	
	if(count($errors))
	{
		unset($_SESSION['ftp_tz_offset']);
		unset($_SESSION['loggedInAsMember']);
		unset($_SESSION['ftp']); // set in folder manager
		unset($_SESSION['ftp_folders']);
	}
	else
	{
		$_SESSION['loggedInAsMember'] = $memberData['id'];
		$tz_offset = $_POST['tz_offset'];
		$tz_offset = is_numeric($_POST['tz_offset']) ? $tz_offset : false;
		$_SESSION['ftp_folders'] = get_valid_folders($memberData['id']);
		$_SESSION['ftp_tz_offset'] = $tz_offset;
	}
	
	return $errors;
}

//---------------------------------------------------------------//
function isValidEmailAddress($email) {
    if (preg_match('/^[a-zA-Z0-9._-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,4}$/', $email)) return true;
    else return false;
}

function userConfirmed($email)
{
	return logged_query("UPDATE `members` SET `confirmation_code`='' WHERE `email`=:email", 0, array(':email' => $email) );
}

function startNewMember($options,$member_data)
{
// $member_data: id 				of the memeber
//	   optional: payment_status		if set, set this status
//		 future: ftp details 		create folder? give permissions for folders?
//				 membership_expiry	when should this expire...should it?

	// don't know what all I'll need to do here:
	// set finish datetime depending on the members module preferences
	$post_data['membership_expiry'] = "NOW()+INTERVAL 1 YEAR";
	
	// set users ftp_access
	// by default, they have no access: must be set in Members Module in CMS (will need to change this)
	
	// set users status
	//	by default, they are set to level 2 (Active/Basic/Regular)
	$post_data['status'] = 2;
	
	// set users payment status
	// if payments are enabled, and $signup_data includes payment_status, set the status
	if($options['pay_signup'] && isset($member_data['payment_status']))
	{
		$post_data['payment_status'] = $member_data['payment_status'];
	}

	$where_clause = 'WHERE id=:id';
	$where_bindings = array(':id' => $member_data['id']);
	return logged_array_update('members', $post_data, $where_clause, $where_bindings);
	
}


// get member by id
function getMemberByID($member_id)
{
	$member = logged_query("SELECT * FROM `members` WHERE `status` >= 1 AND `id`>=1 AND `id` =:member_id LIMIT 1",0,array(":member_id" => $member_id));

	if ($member === false || count($member) == 0 ) $member = false ;
	else $member = $member[0];

	return $member;
}

function getMember($member_url)
{
	$member = logged_query("SELECT * FROM `members` WHERE `status` >= 1 AND `id`>=1 AND `url` =:member_url LIMIT 1",0,array(":member_url" => $member_url));

	if ($member === false || count($member) == 0 ) $member = false ;
	else $member = $member[0];

	return $member;
}

function getMemberByEmail($member_email)
{
	$member = logged_query("SELECT * FROM `members` WHERE `status` >= 1 AND `id`>=1 AND `email` =:member_email LIMIT 1",0,array(":member_email" => $member_email));

	if ($member === false || count($member) == 0 ) $member = false ;
	else $member = $member[0];

	return $member;
}

function getMembersSignupMessage()
{
	$data = logged_query("SELECT * FROM `members_signup` WHERE `id`>=1 LIMIT 1",0,array());

	if ($data === false || count($data) == 0 ) $data = false ;
	else $data = $data[0];

	return $data;
}


function unique_email_address($email_address) {
	$email_address = strtolower($email_address);
    $member = logged_query("SELECT * FROM `members` WHERE `id` > 0 AND LOWER(`email`) = :email_address",0,array(':email_address' => $email_address));
 
	// if this is a non-member eBulletin subscriber, delete the subscription and ok them for application.
	return !(is_array($member) && count($member));
}   
