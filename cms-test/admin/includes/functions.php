<?php
/*
	The following two functions, used in conjunction, allow us to encrypt database data stored in tinyMCE inline editable tags.

	With random salts generated on each page load, hackers can't alter the listed table to something else.

	In rare cases, alternate salts will be required
		(opening a pop that has a tinyMCE editable field may require it's own salt so the underlying page's salts remain undisturbed)
		In this case, a parm naming the alternate field can be passed, but for full page refreshes, the dflt should be used

setTinySalt
 used in tiny_mce frontend to encrypt database data stored in the
 must be called on each frontend page load (in index.php)
*/
function createTinySalt($field = 'dflt')
{
	if(!isset($_SESSION['tiny_salt'][$field])) $_SESSION['tiny_salt'][$field] = substr(session_id(),0,10);
	return $_SESSION['tiny_salt'][$field];
}
// used in tinyMCE ajax functions to make sure the salt matches the currently loaded page
function getTinySalt($field = 'dflt')
{
	return $_SESSION['tiny_salt'][$field];
}

/*	function to create an ajax form loading div
 *	@param string formLocation:				valid url to desired form or portion of a form
 *										eg)	'members/frontend/ftp_form.php'  will return the full contents of the file:ftp_form.php
 *											'members/frontend/ftp_form.php #ftp_login' will return only the portion of the file contained in(inclusively) the
 *												markup with id="ftp_login". Useful for putting several forms in one location
 *	@param array formData:					array of values used on the form. This can be error messages/default values/original values: anything passed from the
 *												php source to the ajax form
 *
 *	@required: form at formLocation			Receiving form will get $formData in the $_POST array
 *		 NOTE: 								variables passed to the form go through ajax
 *												- (bool) is arrives as a string: 'true' / 'false': Bools should be sent as (int)
 *											file must be in a navigable directory
 *												- eg) /external
 *											   	- not in /includes
 *	@required: /js/functions.js				one of these two areas
 *		or	   /includes/inc-bottom.php		(preferably this one, faster loading) needs to contain the following js:
 *				$('.ajaxForm').each(function(){
 *					$(this).load($(this).attr('data-location'),$.parseJSON($(this).attr('data-post')) )
 *				});
 */
function ajax_form($formLocation, $formData = array())
{
	global $_config;
	$file = $formLocation;
	$file_headers = @get_headers($file);
	if($file_headers[0] == 'HTTP/1.1 404 Not Found') {
		$exists = false;
	}
	else {
		$exists = true;
	}
	if($exists) :
?>
	<div class="ajaxForm" data-location='<?php echo $formLocation; ?>' data-post='<?php echo json_encode($formData, JSON_HEX_APOS); ?>' ></div>
<?php
	endif;
}

function rand_string( $length = 10 ) {
	$chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
	$str = '';
	$size = strlen( $chars );
	for( $i = 0; $i < $length; $i++ ) {
		$str .= $chars[ rand( 0, $size - 1 ) ];
	}

	return $str;
}

/**
 * Function to create and display error and success messages
 * @access public
 * @param string session name
 * @param mixed	value
 * @return mixed value on successful receipt
 *				 true on successful set
 *				 false on failed set or receipt
 */
function flash( $name = '', $value = '' )
{
    //We can only do something if the name isn't empty
    if( !empty( $name ) )
    {
        //No message, create it
        if( !empty( $value ) && empty( $_SESSION[$name] ) )
        {
            if( !empty( $_SESSION[$name] ) )
            {
                unset( $_SESSION[$name] );
            }
            $_SESSION[$name] = $value;
			return true;
        }
        //Message exists, return it
        elseif( !empty( $_SESSION[$name] ) && empty( $value ) )
        {
			$value = $_SESSION[$name];
            unset($_SESSION[$name]);
            return $value;
        }
    }
	return false;

}
function check_input($value)
{
	if($_config['troubleshootdb'])
		throw new Exception('Function Not Updated: switching to pdo. This function is not used in core programming but may still be used in modules. Requires updating to pdo if still in use. If not, remove.');

	// Stripslashes
	if (get_magic_quotes_gpc())
	  {
	  $value = stripslashes($value);
	  }
	// Quote if not a number
	if (!is_numeric($value))
	  {
	  $value = "'" . mysql_real_escape_string($value) . "'";
	  }
	return $value;
}

function extract_multi_index($strList)
{
	$result = array();
	$strList = trim($strList, ',');
	$arList = explode(',', $strList);
	foreach ($arList as $tmpIndex)
	{
	//var_dump($tmpIndex);
		if (! (is_numeric($tmpIndex) && $tmpIndex == round($tmpIndex)) ) return $tmpIndex;
		$result[] = $tmpIndex;
	}
	return ($result);
}

function pack_multi_index ($arList)
{
	$result = ',';
	foreach ($arList as $k => $v)
	{
		if (! (is_numeric($k) && $k == round($k)) ) return false;
		$result .= $k . ',';
	}

	return $result;
}
/* checkbox group builder
** 	$check_options:array of arrays of id:title and optional class (string: css class for the input group)
**		$check_options = array(
**			array( 'id' => 0, 'title' => 'Engine Heater', class => ''),
**			array( 'id' => 1, 'title' => 'Engine & Bunk Heater', class => 'hideme'),
**			array( 'id' => 2, 'title' => 'Bunk Heater')
**		);
**	$checked_items: array of checked ids (either from form submission or saved data)
**
**	$check_group_name: name of the group for form submissions
**		$check_group_name = 'aux';
**	$check_group_label: name that goes in the groups label
**		$check_group_label = 'Auxiliary Equipment';
**	$check_group_table_id: id assigned to the table for css purposes;
**	 	$check_group_table_id = 'aux_table';
**	$check_group_title: hover text
**		$check_group_title = 'Choose all applicable items.';
**	$check_group_columns: number of columns this one will show;
**		$check_group_columns = 3;
**  $check_group_class: additional class name for the entire table of checkboxes
**
*/
function display_checkbox_group($check_options,$check_group_name, $check_group_label,$checked_items=array(),$check_group_table_id='',$check_group_title='',$check_group_columns=2, $radio = false, $check_group_class = '')
{
	$type = $radio ? 'radio' : 'checkbox'
?>
	<div class="input_wrap">
		<label class="tipRight" title="<?php echo $check_group_title; ?>"><?php echo $check_group_label; ?></label>
		<div class="input_inner">
			<table id="<?php echo $check_group_table_id; ?>" class='check_group <?php echo $check_group_class; ?>'>
				<tbody>
		<?php for ($trCount = 0; $trCount < ceil(count($check_options) / $check_group_columns); $trCount++) : ?>
					<tr>
			<?php
			for ($tdCount = 0; $tdCount < $check_group_columns; $tdCount++) :
				$curIndex 	= $trCount * $check_group_columns + $tdCount;
				if($curIndex < count($check_options)) :
					// build the data spot
					$curId 	 = $check_options[$curIndex]['id'];
					$curName = $check_options[$curIndex]['title'];
					$curClass= isset($check_options[$curIndex]['class']) ? $check_options[$curIndex]['class'] : '';

					if ($radio) $inputName = $check_group_name;
					else $inputName = "{$check_group_name}[{$curId}]"
			?>
						<td>
							<input type="<?php echo $type; ?>" class="no_counter <?php echo $curClass; ?>" value="<?php echo $curId; ?>" id="col_<?php echo $curId; ?>" name="<?php echo $inputName?>" <?php echo in_array( $curId, $checked_items ) ? ' checked="checked"' : ''; ?> >
							<span><?php echo $curName; ?></span>
						</td>
				<?php else : // output the blank table data ?>
						<td>&nbsp;</td>
				<?php
				endif;
			endfor;   ?>
					</tr>
		<?php endfor; ?>
				</tbody>
			</table><!-- END module_table -->
		</div><!-- END input_inner -->

	</div><!-- END input_wrap -->
	<div class="clearFix"></div>
<?php
}


/* checkbox group builder
** 	$check_options:array of arrays of id:title and optional class (string: css class for the input group)
**		$check_options = array(
**			array( 'id' => 0, 'title' => 'Engine Heater', class => ''),
**			array( 'id' => 1, 'title' => 'Engine & Bunk Heater', class => 'hideme'),
**			array( 'id' => 2, 'title' => 'Bunk Heater')
**		);
**	$checked_items: array of checked ids (either from form submission or saved data)
**
**	$check_group_name: name of the group for form submissions
**		$check_group_name = 'aux';
**	$check_group_label: name that goes in the groups label
**		$check_group_label = 'Auxiliary Equipment';
**	$check_group_table_id: id assigned to the table for css purposes;
**	 	$check_group_table_id = 'aux_table';
**	$check_group_title: hover text
**		$check_group_title = 'Choose all applicable items.';
**	$check_group_columns: number of columns this one will show;
**		$check_group_columns = 3;
**  $check_group_class: additional class name for the entire table of checkboxes
**
*/
function display_checkbox_list($check_options,$check_group_name, $check_group_label,$checked_items=array(),$check_group_table_id='',$check_group_title='',$check_group_columns=2, $radio = false, $check_group_class = '')
{
	$type = $radio ? 'radio' : 'checkbox';
	$column_class = 'col_' . $check_group_columns;
?>
	<div class="input_wrap">
		<label class="tipRight" title="<?php echo $check_group_title; ?>"><?php echo $check_group_label; ?></label>
		<div class="input_inner">
			<div class='all_all tipTop' title='Select All'></div>
			<div class='all_none tipBottom' title='Select None'></div>
			<ul id="<?php echo $check_group_table_id; ?>" class='check_group <?php echo $column_class . ' ' .$check_group_class; ?>'>

		<?php foreach ($check_options as $tmp_opt) :
					// build the data spot
					$curId 	 = $tmp_opt['id'];
					$curName = $tmp_opt['title'];
					$curClass= isset($tmp_opt['class']) ? $tmp_opt['class'] : '';

					if ($radio) $inputName = $check_group_name;
					else $inputName = "{$check_group_name}[{$curId}]"
			?>
						<li>
							<input type="<?php echo $type; ?>" class="no_counter <?php echo $curClass; ?>" value="<?php echo $curId; ?>" id="col_<?php echo $curId; ?>" name="<?php echo $inputName?>" <?php echo is_array($checked_items) && in_array( $curId, $checked_items ) ? ' checked="checked"' : ''; ?> >
							<span><?php echo $curName; ?></span>
						</li>
		<?php
			endforeach;   ?>


			</ul><!-- END module_table -->
		</div><!-- END input_inner -->

	</div><!-- END input_wrap -->
	<div class="clearFix"></div>
<?php
}

/* hasher, function used for password validation.
// usage:
//		hash a password for db storage:
//			$encryptedPass = hasher($inputPass);
//		validate input against db stored password
//			$isValidPass = hasher($inputPass, $dbStoredPass);
//
// if only info is passed,
//		returns a "blowfish" hash with a randomized salt added to the end of the hash
// if info and encdata(previously generated hash with random salt) is passed,
// 		returns true if the hash (minus the salt) of $info matches $encdata(minus the salt);
*/
function hasher($info, $encdata = false)
{
	$strength = "08";
	//if encrypted data is passed, check it against input ($info)
	if ($encdata) {
		//echo substr($encdata, 0, 60) . "<br />" . crypt($info, "$2a$".$strength."$".substr($encdata, 60));
		if (substr($encdata, 0, 60) == crypt($info, "$2a$".$strength."$".substr($encdata, 60))) {
			return true;
		}
		else {
			return false;
		}
	}
	else {
		//make a salt and hash it with input, and add salt to end
		$salt = "";
		for ($i = 0; $i < 22; $i++) {
			$salt .= substr("./ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789", mt_rand(0, 63), 1);
		}
		//return 82 char string (60 char hash & 22 char salt)
		return crypt($info, "$2a$".$strength."$".$salt).$salt;
	}
}


// return a valid url
function goodUrl($url)
{
	// create regular expression to change to safe url
	$url = preg_replace('/[^A-Za-z0-9_-]/g', '_', $url);
	$url = preg_replace('/__/g', '_', $url);
	$url = preg_replace('/__/g', '_', $url);
	$url = preg_replace('/__/g', '_', $url);
	$url = preg_replace('/^_/', '_', $url);
	$url = preg_replace('/_$/', '_', $url);
	return $url;
}

// create an array of reserved page names:
// can't be claimed system names like:	'images'...
// or customNames like: 				'blog/news', 'cart'...
// or config defined names like:		'admin_folder : admin'
function reservedSlugs()
{
	global $_config;
	$folder = trim($_config['admin_folder'], '/');
	$usedSlugs = array('images','css','external','fonts','includes','js','uploads',$folder);

	return $usedSlugs;
}

// newSlug is the slug that is being validated
// arSlug is an array of slugs that the desired url could clash with
// arSlugsAr is an optional array of arrays of possible conflict slugs
// isBaseLevel is true if this slug is being put on the base level (pages) as opposed to under another level(blog)
function validateUrl(&$newSlug, $arSlug = array() )
{
	$tmpSlug = $newSlug;
	$suffix = '-';
	$suffixNum = 1;
	while (in_array($tmpSlug, $arSlug) )
	{
		$tmpSlug = $newSlug . $suffix . $suffixNum++;
	}
	return $newSlug == $tmpSlug;
}

// if the passed directory path ( string ) doesn't exist, build it recursively and build the standard child directories
function buildUploadPathIfEmpty($uploadPath)
{
	global$_config;
	if($_config['troubleshootdb'])
		throw new Exception("This function should no longer be used: If this error occurs, inspect");

	if (!file_exists($uploadPath))
	{
		$old = umask(0);
		if(@mkdir($uploadPath, 0777, true) )
		{
			@mkdir($uploadPath . "thumb");
			@mkdir($uploadPath . "original");
			@mkdir($uploadPath . "fullsize");
		}
		umask($old);
	}
}

/* true if the db has been installed */
function is_installed()
{

	$query = logged_query_assoc_array("SELECT * FROM `auth_users`",null,0,array());

	if (! $query || ! count($query) ) return false;
	return true;
}


// Creates Slug for dynamic page loading
function remove_accent($str)
{
	$a = array('À','Á','Â','Ã','Ä','Å','Æ','Ç','È','É','Ê','Ë','Ì','Í','Î','Ï','Ð','Ñ','Ò','Ó','Ô','Õ','Ö','Ø','Ù','Ú','Û','Ü','Ý','ß','à','á','â','ã','ä','å','æ','ç','è','é','ê','ë','ì','í','î','ï','ñ','ò','ó','ô','õ','ö','ø','ù','ú','û','ü','ý','ÿ','Ā','ā','Ă','ă','Ą','ą','Ć','ć','Ĉ','ĉ','Ċ','ċ','Č','č','Ď','ď','Đ','đ','Ē','ē','Ĕ','ĕ','Ė','ė','Ę','ę','Ě','ě','Ĝ','ĝ','Ğ','ğ','Ġ','ġ','Ģ','ģ','Ĥ','ĥ','Ħ','ħ','Ĩ','ĩ','Ī','ī','Ĭ','ĭ','Į','į','İ','ı','Ĳ','ĳ','Ĵ','ĵ','Ķ','ķ','Ĺ','ĺ','Ļ','ļ','Ľ','ľ','Ŀ','ŀ','Ł','ł','Ń','ń','Ņ','ņ','Ň','ň','ŉ','Ō','ō','Ŏ','ŏ','Ő','ő','Œ','œ','Ŕ','ŕ','Ŗ','ŗ','Ř','ř','Ś','ś','Ŝ','ŝ','Ş','ş','Š','š','Ţ','ţ','Ť','ť','Ŧ','ŧ','Ũ','ũ','Ū','ū','Ŭ','ŭ','Ů','ů','Ű','ű','Ų','ų','Ŵ','ŵ','Ŷ','ŷ','Ÿ','Ź','ź','Ż','ż','Ž','ž','ſ','ƒ','Ơ','ơ','Ư','ư','Ǎ','ǎ','Ǐ','ǐ','Ǒ','ǒ','Ǔ','ǔ','Ǖ','ǖ','Ǘ','ǘ','Ǚ','ǚ','Ǜ','ǜ','Ǻ','ǻ','Ǽ','ǽ','Ǿ','ǿ');
	$b = array('A','A','A','A','A','A','AE','C','E','E','E','E','I','I','I','I','D','N','O','O','O','O','O','O','U','U','U','U','Y','s','a','a','a','a','a','a','ae','c','e','e','e','e','i','i','i','i','n','o','o','o','o','o','o','u','u','u','u','y','y','A','a','A','a','A','a','C','c','C','c','C','c','C','c','D','d','D','d','E','e','E','e','E','e','E','e','E','e','G','g','G','g','G','g','G','g','H','h','H','h','I','i','I','i','I','i','I','i','I','i','IJ','ij','J','j','K','k','L','l','L','l','L','l','L','l','l','l','N','n','N','n','N','n','n','O','o','O','o','O','o','OE','oe','R','r','R','r','R','r','S','s','S','s','S','s','S','s','T','t','T','t','T','t','U','u','U','u','U','u','U','u','U','u','U','u','W','w','Y','y','Y','Z','z','Z','z','Z','z','s','f','O','o','U','u','A','a','I','i','O','o','U','u','U','u','U','u','U','u','U','u','A','a','AE','ae','O','o');
	return str_replace($a, $b, $str);
}

function post_slug($str)
{
	return strtolower(preg_replace(array('/[^a-zA-Z0-9 -]/', '/[ -]+/', '/^-|-$/'), array('', '-', ''), remove_accent($str)));
}


// returns all the special pages as key value pairs
// key: 	capitalized name of special page
// value: 	uri-friendly slug
function get_all_special_pages()
{
	global $_config;
	if(isset($_config['mod_special']) )foreach ($_config['mod_special'] as $spec_name => $spec_slug) $spec_pages[ucwords($spec_name)] = $spec_slug;
	foreach ($_SESSION['menulessPages'] as $spec_name => $spec_slug) $spec_pages[ucwords($spec_name)] = $spec_name;
	foreach ($_SESSION['customPages'] as $spec_name => $spec_slug) $spec_pages[ucwords($spec_name)] = $spec_name;
	return $spec_pages;
}

// checks to see if user is logged in, if not: redirect to login
// if logged in, return user data
function check_login()
{
	global $_config;
	$location = $_config['admin_url'] . 'login.php';

	// get the list of users
	$users = logged_query_assoc_array('SELECT * FROM auth_users', 'user_id', 0, array());

	if(!isset($_SESSION['uid']) || (isset($_SESSION['uid']) && !array_key_exists($_SESSION['uid'], $users)) ) {
		header("Location: " . $location);
		exit;
	}
	else return $users[$_SESSION['uid']];
}


// checks to see if user is logged in as admin
// and return true if that is the case. Return false otherwise
function logged_in_as_admin()
{
    global $_config;

	if(!isset($_SESSION['uid']))
	{
		unset($_SESSION['isAdmin']);
		return false;
	}
	if(isset($_SESSION['isAdmin'])) return $_SESSION['isAdmin'];


    // get the list of users
    $users = logged_query_assoc_array('SELECT * FROM auth_users WHERE `admin` = "yes"', 'user_id',0,array());

    if(!array_key_exists($_SESSION['uid'], $users) ) {
        $_SESSION['isAdmin'] = false;
		return false;
    }
	$_SESSION['isAdmin'] = true;
	return true;
}

// checks to see if the user is logged in as the passed value
// the value should match the relevant module name
function logged_in_as_ftp()
{
	global $_config;

    if(array_key_exists('ftp_logged_in' , $_SESSION)) {
        return true;
    }
    else return false;
}

// checks to see if the user is logged in as a member
// and return true if that is the case. Return false otherwise
function logged_in_as_member()
{
    global $_config;

    if(array_key_exists('loggedInAsMember', $_SESSION)) {
        return true;
    }
    else return false;
}

function ajax_check_login()
{
	global $_config;

	// get the list of users
	$users = logged_query_assoc_array('SELECT user_id FROM auth_users', 'user_id',0,array());

	if(!isset($_SESSION['uid'] ) || !array_key_exists($_SESSION['uid'], $users) ) {
		die('Not Logged In');
	}
	else return $_SESSION['uid'];
}

function using_ie()
{
    $u_agent = $_SERVER['HTTP_USER_AGENT'];
    $ub = False;
    if(preg_match('/MSIE/i',$u_agent))
    {
        $ub = True;
    }

    return $ub;
}

/*	isModule 						find out if specified module is loaded
**	@parm	$module:	(string)	string is the name of one of the modules found in the modules directory
**	@return	bool					true if this module exists
*/
function hasModule($module)
{
	global $_config;
	return file_exists( $_config['admin_modules'] . $module);
}

/*	getModuleData 	get data about the module
**	@parm	$module:	(string)	string is a filename of one of the modules found in the modules directory
**	@return	(array)					array of data about the module
**				valid	(bool)		true if the current user is allowed to use this module
**				data	(string)	the data used for <li> definition for this module in the sidebar
*/
function getModuleData($module)
{
	global $_config;
	$loc = $_config['admin_modules'] . $module . '/system/validate.php';
	if (file_exists($loc) )
	{
		$modSide = include($loc);
		$valid = ($modSide == false) ? false : true;
		$data = array('valid' => $valid, 'data' => $modSide);
		return $data;
	}
	else return array('valid' => false, 'data' => array());
}

// create the error/msg banner
function createBanner ( $message )
{
		?>
		<div class="msg-wrap">
			<div class="close-message"></div>
			<div id="bannerType" class="<?php echo isset($message['banner']['type']) ? $message['banner']['type'] : '' ; ?>">
				<h2><?php echo isset($message['banner']['heading']) ? $message['banner']['heading'] : ''; ?></h2>
				<span><?php echo isset($message['banner']['message']) ? $message['banner']['message'] : ''; ?></span>
			</div>
		</div>
		<?php
}

//*********************** AJAX FUNCTIONS ***********************************//

// used in ajax calls to create a viable filename
// assumes image stored in a table with image called : 'name'
// returns a valid imgName
function setFileName($table, $name, $type)
{
	if($_config['troubleshootdb'])
		throw new Exception('Function Not Updated: switching to pdo. This function is not used in core programming but may still be used in modules. Requires updating to pdo if still in use. If not, remove.');

	// are there any similarly named entities in the db?
	$query = mysql_query("SELECT name FROM {$table} WHERE name like '{$name}%.{$type}'");
	if (mysql_num_rows($query) == 0){
		// use the basic name if there aren't
		return $name.'.'.$type;
	}
	else
	{
		$val = array();
		$basicExists = false;
		// otherwise, give it a bumped name if other like it exist
		$pattern = "/^".$name."(_(\d{1,3}))?\.".$type."$/";
		while ($entry = mysql_fetch_assoc($query))
		{
			if (preg_match($pattern, $entry['name'], $matches)){
			echo 'dup';
				if (isset($matches[2]))
				{
					$val[] = $matches[2];
				}
				else
				{
					$basicExists = true;
				}
			}
		}
		// always use the basic name if it is available ( previously deleted)
		if (! $basicExists) return $name.'.'.$type;

		// basic exists but no numbers exist so return with 0 extension
		if (count($val) == 0) return $name.'_0.'.$type;

		// otherwise, return the next number since a number does exist
		return $name.'_'. (max($val) + 1) .'.'.$type;
	}
}

// checks $_FILES for error code and returns an error string
function getFileUploadError()
{
	if($_config['troubleshootdb'])
		throw new Exception('Possible Unused Function: this function is no longer used in core code. If you have loaded a module using it: test function and remove this Exception.');


/* Validate Uploaded File */
	$errCode = $_FILES['upload_file']['error'];
	$error = "";
	if($errCode !=0) {
		if ( $errCode == 1 ) $error = "Error({$errCode}): The uploaded file exceeds the max file size.";// as described in php_ini
		elseif ( $errCode == 2 ) $error = "Error({$errCode}): The uploaded file exceeds the max file size.";// as described in MAX_FILE_SIZE hidden input
		elseif ( $errCode == 3 ) $error = "Error({$errCode}): The uploaded file was only partially uploaded.";
		elseif ( $errCode == 4 ) $error = "Error({$errCode}): No file was uploaded. ";
		elseif ( $errCode == 5 ) $error = "Error({$errCode}): The uploaded file exceeds the max file size.";
		elseif ( $errCode == 6 ) $error = "Error({$errCode}): Missing a temporary folder.";
		elseif ( $errCode == 7 ) $error = "Error({$errCode}): Failed to write file to disk.";
		elseif ( $errCode == 8 ) $error = "Error({$errCode}): A PHP extension stopped the file upload.";
	}
	return $error;
}

// gallery id is the unique id for this item (page_id/gallery_id/item_id)
// preposition is the identifier that precedes the image name
function getSafeFileName($galleryId, $preposition = '')
{
	if($_config['troubleshootdb'])
		throw new Exception('Possible Unused Function: this function is no longer used in core code. If you have loaded a module using it: test function and remove this Exception.');


	// get file name/type
	$SafeFile = $_FILES['upload_file']['name'];
	$SafeFile = str_replace(" ", "_", $SafeFile);
	$SafeFile = str_replace("#", "Num", $SafeFile);
	$SafeFile = str_replace("$", "Dollar", $SafeFile);
	$SafeFile = str_replace("%", "Percent", $SafeFile);
	$SafeFile = str_replace("^", "", $SafeFile);
	$SafeFile = str_replace("&", "and", $SafeFile);
	$SafeFile = str_replace("*", "", $SafeFile);
	$SafeFile = str_replace("?", "", $SafeFile);
	$SafeFile = str_replace("'", "", $SafeFile);

	$prep_id = $preposition ? "{$preposition}{$galleryId}_" : '';
	$result['type'] = strtolower(end(explode(".", $SafeFile)));
	$name = substr($SafeFile, 0, -(strlen($result['type']) + 1));
	$result['name'] = $prep_id.$name;

	return $result;
}

function __autoload($name){
	global $_config;

	$name = strtolower($name);

	$path = $_config['admin_includes'] . 'classes/' . $name . '.php';
	if (file_exists($path) )
	{
		include_once($path);
		return;
	}
	else
	{
		foreach($_SESSION['activeModules'] as $tmpmod)
		{
			$path = $_config['admin_modules'] . $tmpmod . "/classes/" . $name . ".php";
			if (file_exists($path) )
			{
				include_once($path);
				return;
			}
		}
	}
	my_log("Failed to launch Class: {$name}");
}

// returns true if $num is an integer value (even if passed as a string) > 0
function is_pos_int($num, $includeZero = false)
{
	$compareVal = $includeZero ? 0 : 1;
	return (is_numeric($num) && $num >= $compareVal && $num == round($num));
}

// returns true if $num is an integer value (even passed as a string)
function is_web_int($num)
{
	return (is_numeric($num) && $num == round($num));
}
// returns true if the number has a decimal component
function is_decimal( $val )
{
    return is_numeric( $val ) && floor( $val ) != $val;
}
// if there is at least one string key, $array will be regarded as associative array
function is_assoc($array)
{
	if (! is_array($array)) return false;
	return count(array_filter(array_keys($array), 'is_string')) == count($array);
}

// confirms user is logged in
function logged_in()
{
	global $_config;

	$db = $_config['db'];
	// get the list of users
	$result = $db->select('auth_users');

	if ($result === false || ! count($result)) return false;
	foreach ($result as $user)
		$users[$user['user_id']] = $user;


	if(!isset($_SESSION['uid']) || (isset($_SESSION['uid']) && !array_key_exists($_SESSION['uid'], $users)) ) {
		return false;
	}
	else return true;
}

//-------------------------------------------------------------//
function validateText (&$arErrors)
{
	if($_config['troubleshootdb'])
		throw new Exception('Possible Unused Function: this function is no longer used in core code. If you have loaded a module using it: test function and remove this Exception.');

	/* This function will validate the value
	  of each key in the $argPost array parameter.
	  If the value is blank a '*' entry is made
	  in the return array.
	*/

	$asterisk = "<span style=\"color:#821518; font-size:1.3em\"> *</span>";
	$_REQUEST['processed'] = "yes";

	foreach($_POST as $key => $value)
	{
	   if (trim($value) == "")
	   {
	       $arErrors[$key] = $asterisk;
		   $_REQUEST['error'] = "yes";
	   }// end if
	   else
	   {
	       $arErrors[$key] = "";
	   }
	}// end for

}// end function validateText
//-------------------------------------------------------------//


// takes a string with - and _ and replaces with spaces and capitalizes words
function beautify($string)
{
	return ucwords(str_replace(array('-', '_'), ' ', $string));
}

function in_arrayi($needle, $haystack)
{
    for($h = 0 ; $h < count($haystack) ; $h++)
    {
        $haystack[$h] = strtolower($haystack[$h]);
    }
    return in_array(strtolower($needle),$haystack);
}

function check_email_address($email) {

	return (filter_var($email,FILTER_VALIDATE_EMAIL));

	/*   left this in, just in case: replaced by filter function above

    // First, we check that there's one @ symbol, and that the lengths are right
    if (!preg_match("/^[^@]{1,64}@[^@]{1,255}$/", $email)) {
        // Email invalid because wrong number of characters in one section, or wrong number of @ symbols.
        return false;
    }
    // Split it into sections to make life easier
    $email_array = explode("@", $email);
    $local_array = explode(".", $email_array[0]);
    for ($i = 0; $i < sizeof($local_array); $i++) {
         if (!preg_match("@^(([A-Za-z0-9!#$%&'*+/=?^_`{|}~-][A-Za-z0-9!#$%&'*+/=?^_`{|}~\.-]{0,63})|(\"[^(\\|\")]{0,62}\"))$@", $local_array[$i])) {
            return false;
        }
    }
    if (!preg_match("/^\[?[0-9\.]+\]?$/", $email_array[1])) { // Check if domain is IP. If not, it should be valid domain name
        $domain_array = explode(".", $email_array[1]);
        if (sizeof($domain_array) < 2) {
                return false; // Not enough parts to domain
        }
        for ($i = 0; $i < sizeof($domain_array); $i++) {
            if (!preg_match("/^(([A-Za-z0-9][A-Za-z0-9-]{0,61}[A-Za-z0-9])|([A-Za-z0-9]+))$/", $domain_array[$i])) {
                return false;
            }
        }
    }
    return true;
	*/
}
//-------------------------------------------------------------//
// Get URI should be replaced by the uri class


/*	Get Uri
**
**	Get the uri value by index
**
**	@parm	(int)	index (starting at 0) of the desired uri element
**
**	@return	(mixed)
**				(bool)	false if no value at this position
**				(str)	value found at this posn
*/
function get_uri($index)
{
	global $_config;
	if($_config['troubleshootdb'])
		throw new Exception('Deprecated: switch to Uri class function: uri::get($index)');

	global $uri;
	return get_safe_index($uri, $index);
}

/*	Get Get Safe Index
**
**	Get the value of an array at its index
**
**	@parm	(int)	index (starting at 0) of the desired uri element
**
**	@return	(mixed)
**				(bool)	false if no value at this position
**				(mixed)	value found at this posn
*/
function get_safe_index($array, $index)
{
	return (isset($array[$index]) && $array[$index] == '' || ! isset($array[$index])) ? false : $array[$index];
}

/*	Get Page Files
**
**	Get pages from the selected directory
**    only returns files ending in php/html/htm
**
**  Usage: Used to identify all the web readable files in a directory. Used (in /index.php) to identify custom pages
**           to load them.
**
**	@return	(array)
**			key:	(str)	name of the page
**			value:	(str)	type of the page (should be php but could be html/htm)
*/
function get_page_files($dir = false)
{
	$specialPages = array();
	if ($dir && is_dir($dir)) {
		if ($dh = opendir($dir)) {
			while (($file = readdir($dh)) !== false) {

				if ( filetype($dir . $file) == 'file' )
				{
					$tmptype = explode(".", $file);
					$type = strtolower(end($tmptype));
					if ($type == 'htm' || $type == 'html' || $type == 'php')
					{
						$name = substr($file, 0, -(strlen($type) + 1));
						$specialPages[$name] = $type;

					}
				}
			}
			closedir($dh);
		}
	}
	return $specialPages;
}

// returns an array of files in $directory
function getDirectoryList ($directory)
{
	// create an array to hold directory list
	$results = array();

	// create a handler for the directory
	$handler = opendir($directory);

	// open directory and walk through the filenames
	while ($file = readdir($handler)) {
		// if file isn't this directory or its parent, add it to the results
		if ($file != "." && $file != "..") {
			$results[] = $file;
		}
    }

	// tidy up: close the handler
	closedir($handler);

	// done!
	return $results;
 }

/*	Display Array Clean
**
**	display nested arrays cleanly in browser
**
**	@parm	$arrayname	(ar)	the array to be displayed
**			$tab		(str)	how the spacing is to be represented
**			$indent		(int)	indent level(for recursive calls)
**
**	@return	(str)	formatted string for easy reading of array in browser
*/
function display_array($arrayname,$tab="&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp;",$indent=0)
{
 if( ! is_array($arrayname) ) return false;
 $curtab ="";
 $returnvalues = "";
 while(list($key, $value) = each($arrayname)) {
  for($i=0; $i<$indent; $i++) {
   $curtab .= $tab;
   }
  if (is_array($value)) {
   $returnvalues .= "$curtab$key : Array: <br />$curtab{<br />\n";
   $returnvalues .= display_array($value,$tab,$indent+1)."$curtab}<br />\n";
   }
  else $returnvalues .= "$curtab$key => $value<br />\n";
  $curtab = NULL;
  }
 return $returnvalues;
}

// get the loaded modules and run their config files
function getModules()
{
	global $_config;
	$dir = $_config['admin_modules'];
	$activeModules = array();
	if (is_dir($dir)) {
		if ($dh = opendir($dir)) {
			while (($file = readdir($dh)) !== false) {
				if ( $file != '.' && $file != '..' && filetype($dir . $file) == 'dir')
				{
					$activeModules[] = $file;
					include($_config['admin_modules'] . $file . '/system/config.php');
				}
			}
			closedir($dh);
		}
	}
	return $activeModules;
}

// get query and stash errors in error log
function logged_query($string, $debug=0, $bind='')
{
	global $_config;
	if 	(
		$bind == '' &&
		isset($_config['troubleshootdb']) &&
		$_config['troubleshootdb']
	)
	{
		throw new Exception('logged_query and logged_query_assoc now take new parameters');
		my_log('logged_query and logged_query_assoc now take new parameters: see functions in /admin/includes/functions.php');
	}

	$db = $_config['db'];
    if ($debug == 1)
        print $string;

    if ($debug == 2)
		my_log($string,'debug');

//    $result = mysql_query($string);
// no error trapping: beware the hackers
	$result = $db->run($string, $bind);
/*
    if ($result == false)
    {
        my_log("SQL error number: ". mysql_errno() . "\nSQL error: ".mysql_error()."\n\nOriginal query: {$string}");
        if ($debug) die("SQL error: ".mysql_error()."\b<br>\n<br>Original query: $string \n<br>\n<br>");
    }
*/
    return $result;
}

// return all arrays from sql call, if index is set: use this db element name as the index of the assoc array
//  eg: $index = 'user_id': the sql table has a return value: user_id, use that value as the assoc array's  index.
function logged_query_assoc_array($sql,$index = null, $debug=0, $bind='')
{

    // this function require presence of good_query() function
    $result = logged_query($sql, $debug, $bind);

	$lsts = array();
	if (is_array($result))
	{
		foreach ($result as $row )
		{
			if ( is_null($index) ) $lsts[] = $row;
			else $lsts[ $row[$index] ] = $row;
		}
	}
	return $lsts;
}

// perform an array insert or delete/insert on a specified where clause
// normally, this is for a table that doesn't auto-incrmementt
function logged_array_replace($table, $post_data, $enc_fields = array())
{
	if($_config['troubleshootdb'])
		throw new Exception('Function Not Updated: switching to pdo. This function is not used in core programming but may still be used in modules. Requires updating to pdo if still in use. If not, remove.');

	if(! is_assoc($post_data)) $return = array('error' => "logged_array_replace: post_data must be assoc array");
	if(! is_string($table)) $return = array('error' => "Table must be a table name");
	if(! count($post_data)) $return = array('error' => "logged_array_replace: post_data cannot be empty");

	if ( ! isset($return) )
	{
		$table = mysql_real_escape_string($table);
		$query = "
			REPLACE  `{$table}` SET";

		foreach ($post_data as $key => $value){
			$key = (string) $key;
			$value = (string) trim($value);
			if(in_array($key,$enc_fields)) {	//encrypt where needed
				$value = encrypt($value);
			}
			$query .= "`" . mysql_real_escape_string($key) . "` =";

			if (substr($value,0,15) == 'UTC_TIMESTAMP()' || substr($value,0,5)=='NOW()' || substr($value,0,9)=='CURDATE()' || substr($value,0,9)=='CURTIME()')
				$query .= mysql_real_escape_string($value) . ",";
			else
				$query .= "'" . mysql_real_escape_string($value) . "',";
		}
		$query = rtrim($query, ',');



		$return['result'] = logged_query($query);
		$return['error'] = mysql_error();
	}
	if ($return['error']) my_log($return['error']);

	return $return;

}

function logged_array_insert($table, $post_data, $enc_fields = array())
{
	global $_config;

	if(! is_assoc($post_data)) $return = array('error' => "logged_array_insert: post_data must be assoc array");
	if(! is_string($table)) $return = array('error' => "Table must be a table name");
	if(! count($post_data)) $return = array('error' => "logged_array_insert: post_data cannot be empty");

	if ( ! isset($return) )
	{
		$bindings = array();
		$query = "
			INSERT INTO `{$table}` (";

		foreach ($post_data as $key => $value){
			$key = (string) $key;
			$query .= "`" .$key . "`,";
		}
		$query = rtrim($query, ',');
		$query .= "
			)
			VALUES (";
		foreach ($post_data as $key => $value){
			$value = (string) trim($value);
			if(in_array($key,$enc_fields)) {	//encrypt where needed
				$value = encrypt($value);
			}

			if (substr($value,0,15) == 'UTC_TIMESTAMP()' || substr($value,0,5)=='NOW()' || substr($value,0,9)=='CURDATE()' || substr($value,0,9)=='CURTIME()')
			{
				$query .= $value . ",";
			}
			else
			{
				$query .= ":".$key . ",";
				$bindings[$key] = $value;
			}
		}
		$query = rtrim($query, ',');
		$query .= "
			);
		";
		$return['result'] = logged_query($query,0,$bindings);

		if($return['result'] !== false)
		{
			$return['insert_id'] = $_config['db']->getLastInsertId();
			$return['error'] = false;
		}
		else
		{
			$return['insert_id'] = false;
			$return['error'] = 'Error Adding Record';
		}
	}

	return $return;

}

// if the where clause contains POST data, use bindings in the clause and include the array of bindings in $where_bindings
// 		eg $where_clause = 'WHERE id=:user_id';$where_bindings = array(':user_id' => $_POST['user_id'])
function logged_array_update($table, $post_data, $where_clause, $where_bindings = false, $enc_fields = array())
{
	global $_config;
	if 	(
		!is_array($where_bindings) &&
		isset($_config['troubleshootdb']) &&
		$_config['troubleshootdb']
	)
	{
		throw new Exception('logged_array_update now takes new parameters');
		my_log('logged_query and logged_query_assoc now take new parameters: see functions in /admin/includes/functions.php');
	}

	if(! is_assoc($post_data)) $return = array('error' => "logged_array_insert: post_data must be assoc array");
	if(! is_string($table)) $return = array('error' => "Table must be a table name");
	if(! is_string($where_clause)) $return = array('error' => "where_string must be a valid where clause for the update");
	if(! count($post_data)) $return = array('error' => "logged_array_insert: post_data cannot be empty");

	if ( ! isset($return) )
	{
		$bindings = $where_bindings;
		$table = $table;
		$query = "
			UPDATE `{$table}` SET";

		foreach ($post_data as $key => $value){
			$key = (string) $key;
			$value = (string) trim($value);
			if(in_array($key,$enc_fields)) {	//encrypt where needed
				$value = encrypt($value);
			}
			$query .= "`" . $key . "` = ";

			if (substr($value,0,15) == 'UTC_TIMESTAMP()' || substr($value,0,5)=='NOW()' || substr($value,0,9)=='CURDATE()' || substr($value,0,9)=='CURTIME()')
				$query .= $value . ",";
			else
			{
				$query .= ":".$key . ",";
				$bindings[$key] = $value;
			}
		}
		$query = rtrim($query, ',');

		$query .= ' ' . $where_clause;

		$return['result'] = logged_query($query,0,$bindings);

		if($return['result'] !== false)
		{
			$return['error'] = false;
		}
		else
		{
			$return['error'] = 'Error Updating Record';
		}
	}

	return $return;

}



/*	Display Content
**
**	Show tinyMCE content
** 		creates encoded data wrapper for frontend editing
**		displays htmlentities properly
**		performs necessary substitutions
**
**	@parm	$content	(str)					the string to be displayed
**  @parm 	$editable (array)					an array of parameters used to create a tinyMCE wrapper
** 	@key	wrapper_type (string) [optional] 	html tag used to wrap the content. dflt: 'div' eg) 'article'
**	@key	editable_class (string)				specifies a tinymce class.
**													Standard classes defined in /admin/js/tiny_mce_settings.js
**													mceEditor: fills textarea, must be part of a form
**													mceUploadable: above ^, and handles uploads
**													inlineStandard: saves inline changes to database
**													inlineUploadable: saves to db and handles uploads
**													custom classes' js needs to be defined in /admin/js/tiny_mce_settings.js
**  @key	additonal_classes (string)			classes other than the editable class
**  @key 	attributes (array)	[optional] 		additional attributes (random id will be created if not set)
**	@key	data (array) [optional]				data attributes for tinyMCE functions: raw values get hashed for security
**
** 	  	required sub-arrays for  save function (inlineStandard && inlineUploadable)
** 		and required sub-arrays for uploadable function (mceUploadable && inlineUploadable)
**	@key-2	table (string) [save]				name of db table to be updated eg) 'pages'
**	@key-2	id_field (string) [save|upload]		name of db field used in WHERE clause of update eg) 'id'
**	@key-2	id_val (string|bool) [save|upload]	value used in WHERE clause of update eg) 3
**	@key-2	field (string) [save]				name of db field to be updated eg) 'content'
**	@key-2	upload_type (string) [upload]		page_type field in table. 'dflt' for regular pages eg) 'dflt'
**
**	@return	(echo)		echoes out the contents of the content after performing any wrapping and required substitutions
*/
function display_content($content, $editable = array())
{
	$wrapper= array('open' => '', 'close' => '');

	$count=0; //are there any substitutions?
	//$content = preg_replace("/{{(.*)}}/e","substitute_tags('$1');", $content,-1, $count); //-- DEPRECATED MODIFIER
	$content = preg_replace_callback('/{{(.*)}}/', create_function ('$taggedpg', 'return substitute_tags($taggedpg[1]);'), $content, -1, $count);

	// need to add conditionals here
	//if(!$count && logged_in_as_admin() ) $wrapper = getContentWrapper($editable);
	if(logged_in_as_admin() ) $wrapper = getContentWrapper($editable);

	// remove the wraps (if they were set) if inline editable declared as false
	if(isset($_SESSION['inline_editable']) && $_SESSION['inline_editable'] === false) $wrapper= array('open' => '', 'close' => '');

	echo $wrapper['open'];
	echo htmlspecialchars_decode($content);
	echo $wrapper['close'];
}
/* 	getContentWrapper
** 	get the tinyMce class and datafields
**  @parm 	$editable (array)					an array of parameters used to create a tinyMCE wrapper
** 	@key	wrapper_type (string) [optional] 	html tag used to wrap the content. dflt: 'div' eg) 'article'
**	@key	editable_class (string)				specifies a tinymce class.
**													Standard classes defined in /admin/js/tiny_mce_settings.js
**													mceEditor: fills textarea, must be part of a form
**													mceUploadable: above ^, and handles uploads
**													inlineStandard: saves inline changes to database
**													inlineUploadable: saves to db and handles uploads
**													custom classes' js needs to be defined in /admin/js/tiny_mce_settings.js
**  @key	additonal_classes (string)			classes other than the editable class
**  @key 	attributes (array)	[optional] 		additional attributes (random id will be created if not set)
**	@key	secure_data (array) [optional]		data attributes for tinyMCE functions: raw values get hashed for security
**
** 	  	required sub-arrays for  save function (inlineStandard && inlineUploadable)
** 		and required sub-arrays for uploadable function (mceUploadable && inlineUploadable)
**	@key-2	table (string) [save]				name of db table to be updated eg) 'pages'
**	@key-2	id_field (string) [save|upload]		name of db field used in WHERE clause of update eg) 'id'
**	@key-2	id_val (string|bool) [save|upload]	value used in WHERE clause of update eg) 3
**	@key-2	field (string) [save]				name of db field to be updated eg) 'content'
**	@key-2	upload_type (string) [upload]		page_type field in table. 'dflt' for regular pages eg) 'dflt'
*/
function getContentWrapper($editable)
{
	$wrapper= array('open' => '', 'close' => '');
	if(!count($editable)) return $wrapper;

	if(!isset($editable['editable_class']) || !is_string($editable['editable_class']) || !$editable['editable_class'] )
		throw new Exception('editable parm is missing key: editable_class');

	if(	($editable['editable_class'] == 'inlineStandard' ||
		$editable['editable_class'] == 'inlineUploadable') && (
			!isset($editable['secure_data']['table']) ||
			!isset($editable['secure_data']['id-field']) ||
			!isset($editable['secure_data']['id-val']) ||
			!isset($editable['secure_data']['field'])
		)
	) throw new Exception("editable_class ({$editable['editable_class']}) is missing a required parameter");

	if(	($editable['editable_class'] == 'inlineUploadable' || $editable['editable_class'] == 'mceUploadable' ) && (
			!isset($editable['secure_data']['upload-type']) ||
			!isset($editable['secure_data']['id-field']) ||
			!isset($editable['secure_data']['id-val'])
		)
	) throw new Exception("editable_class ({$editable['editable_class']}) is missing a required parameter");

	// if not one of the above classes, this is a custom class and we will just hope for the best


	if(! isset($editable['secure_data'])) $editable['secure_data'] = array();
	if(! isset($editable['additonal_classes'])) $editable['additonal_classes'] = '';


	if(!isset($editable['attributes']['id'])) $id = "tiny_id_".substr(md5( time( ) ),0,13 );
	else
	{
		$id = $editable['attributes']['id'];
		unset($editable['attributes']['id']);
	}
	if(!isset($editable['attributes']) ) $editable['attributes'] = array();

	if(isset($editable['wrapper_type'])) $wrapper_type = $editable['wrapper_type'];
	elseif ($editable['editable_class'] == 'inlineStandard' || $editable['editable_class'] == 'inlineUploadable') $wrapperType = 'div';
	else $wrapperType = 'textarea';

	// construct the wrapper
	$open = "<{$wrapperType} id=\"{$id}\" class=\"{$editable['editable_class']} {$editable['additonal_classes']}\"";
	// add extra attributes
	foreach($editable['attributes'] as $att => $val)
	{
		$open .= " {$att}=\"{$val}\"";
	}
	// add secure_data
	$cur_salt = getTinySalt();
	foreach($editable['secure_data'] as $key => $val)
	{
		$hashed = hash('md5', $val . $cur_salt);
		$open .= " data-{$key}=\"{$hashed}\"";
	}
	$open .= ">";
	$wrapper['open'] = $open;
	$wrapper['close'] = "</{$wrapperType}>";
	global $hasInlineEditable;

	$hasInlineEditable = true;
	return $wrapper;
}

/*	Substitute Tags
**
**	replaces tags
**  	with either :
**			the contents of a php/html/htm file located in /includes/tagged_pages
**		or:
**			the contents of a php/html/htm file located at the path provided in the module's config data at
**				$_config['tagged_modules']['TAG_NAME_HERE'] = 'PATH/TO/TAGGED/ELEMENT';
**				NOTE: developers must take care to avoid duplications of naming in modules.
**					  Module tagged names must start with the module name to avoid duplication
**	@parm	$special	(str)	a tag formatted as: {{tag-name/var-1/var-2/...}}
**									tag-name: 		must have a corresponding php/html/htm file in /includes/tagged_pages
**									/var-1/var-2:	'/' separated list of parameters (optional)
**
**	@passes	$arSpecial	(array)		translates the above parm list into an array for use by the receiving php file
**	@return	(str)		the contents of the included file
*/
function substitute_tags($special)
{
	global $_config;
	$arSpecial = array(); // this is the tag information broken down as uri data
							// the first position is the filename
							// remaining optional positions are parameters to be use by the file

	$arSpecial = explode('/', $special);

	// since we use / as a divider, internal '/' have to be substituted. We use **
	foreach($arSpecial as $k => $v)
	{
		$arSpecial[$k] = str_replace('**', '/',$v);
	}

	// get the tagged pages if they aren't already in session data
	if (! isset($_SESSION['tagged_pages']) ) $_SESSION['tagged_pages'] = get_page_files( $_config['rootpath'] . 'includes/tagged_pages/' );

	// check tagged_pages array for the passed tag
	if ( array_key_exists( $filename = get_safe_index( $arSpecial, 0 ) , $_SESSION['tagged_pages']) )
	{
		$extension = $_SESSION['tagged_pages'][$filename];

		// NOTE: of course, the $arSpecial will be available to the receiving file to use any passed variables
		// shunt include output to Output Buffer
		ob_start();
		?>
<div class="tinyNoEditTagged" data-id="<?php echo "{$special}"; ?>">
<?php
		if($_config['debug']) $success = include($_config['rootpath'] . 'includes/tagged_pages/' . $filename . '.' . $extension );
		else $success = @include($_config['rootpath'] . 'includes/tagged_pages/' . $filename . '.' . $extension );

		if(!$success) my_log('error including tagged page: '. $filename);
		// return the output
?>
</div>
<!-- tagged_end -->

<?php
		return ob_get_clean();
	}
	elseif ( isset($_config['tagged_modules']) &&  array_key_exists( $filename = get_safe_index( $arSpecial, 0 ) , $_config['tagged_modules']) )
	{
		$path = $_config['tagged_modules'][$filename];

		// NOTE: of course, the $arSpecial will be available to the receiving file to use any passed variables
		// shunt include output to Output Buffer
		ob_start();
		?><div class="tinyNoEditTagged" data-id="<?php echo "{$special}"; ?>"><?php
		if($_config['debug']) $success = include($path);
		else $success = @include($path);
		// log error if found
		if(!$success) my_log('error including tagged module: '. $filename);
		// return the output
		?></div><!-- tagged_end --><?php
		return ob_get_clean();
	}
}

// *********************************** multiImage displays ****************************************//

// builds a thumb slider
// takes a class name to wrap the group and an array of images
function buildMultiImage($wrapName, $arImage)
{
	if($_config['troubleshootdb'])
		throw new Exception('Possible Unused Function: this function is no longer used in core code. If you have loaded a module using it: test function and remove this Exception.');

	global $_config;
	?>

	<div class="jsGallery multiImage">
<?php
	$galleryOptions = array(
		'noCaption'			=>	true
	);
	$gallery = new displaygallery($arImage, $_config['upload_url']."ecom/",$galleryOptions);

	//$gallery->buildCaption();
	$gallery->buildMiniGallery();
	//$gallery->buildMiniNav();
?>
	</div>
<?php
}

// takes a single Image for the thumb slider
function buildSingleImage($wrapName, $image, $filetype = 'thumb', $allowDesc = true)
{
	global $_config;
	if($_config['troubleshootdb'])
		throw new Exception('Deprecated: use displayGallery class.');

	if(!isset($image['alt'])) $image['alt'] = $image['name'];
	?>
	<div class="<?php echo $wrapName; ?>">
		<a class="gallery_group" href="<?php echo $_config['upload_url']; ?>ecom/fullsize/<?php echo $image['name']; ?>" title="<?php echo $allowDesc ? $image['desc'] : '';?>" rel="item_<?php echo $image['item_id']; ?>">
			<div class="singleImage" >
				<img src="<?php echo $_config['upload_url']; ?>ecom/<?php echo $filetype; ?>/<?php echo $image['name']; ?>" alt="<?php echo $image['alt']; ?>"  />
			</div>
		</a>
	</div>

<?php
}

function buildMiniGallery($wrapName, $arImage)
{
	if($_config['troubleshootdb'])
		throw new Exception('Deprecated: use displayGallery class.');

	global $_config;
	?>

	<div class="jsGallery displayItem">

<?php

	$galleryOptions = array(
		'displaySizeFolder' =>	'fullsize',
		'noCaption'			=>	true
	);
	$gallery = new displaygallery($arImage, $_config['upload_url']."ecom/",$galleryOptions);

	//$gallery->buildCaption();
	$gallery->buildMiniGallery();
	$gallery->buildMiniNav();

?>
	</div>
<?php
}
// only for use with the testimonials module
function get_random_testimonial()
{
	$result = logged_query_assoc_array("
		SELECT * FROM `testimonials` WHERE id > 0 AND `status` > 0 ORDER BY id;", null,0,array());
	$test_count = count($result);
	if ($result && $test_count > 0 ) return $result[rand(0, $test_count - 1)];
	else return false;
}

// install all core db tables
function install_core_db($_config, $pass)
{
	// PAGES TABLES
	$query ="
CREATE TABLE IF NOT EXISTS `pages` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `parent_id` int(11) NOT NULL,
  `has_menu` tinyint(1) DEFAULT '0',
  `menu_order` smallint(3) DEFAULT '0',
  `slug` varchar(50) NOT NULL,
  `page_title` varchar(100) NOT NULL,
  `no_follow` int(1) DEFAULT '0',
  `content` text NOT NULL,
  `seo_keywords` text NOT NULL,
  `seo_description` varchar(250) NOT NULL,
  `seo_title` varchar(100) NOT NULL,
  `status` tinyint(1) DEFAULT '0',
  `visibility` tinyint(1) DEFAULT '0',
  `date` varchar(30) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;
";
	$result = logged_query($query,0,array());
	if($result === false) die('Error Creating Core Table');

	// add blank page if not already present
	$query ="SELECT `id` FROM `pages`";
	$result = logged_query($query,0,array());
	if($result === false) die('Error Adding Core Data');
	if(!is_array($result) || !count($result))
	{
		$pages_query = "INSERT INTO `pages` (`id`, `parent_id`, `has_menu`, `menu_order`, `slug`, `page_title`, `no_follow`, `content`, `seo_keywords`, `seo_description`, `seo_title`, `status`, `visibility`, `date`) VALUES
(1, 0, 1, 0, 'home', 'Home', 0, '&lt;h1&gt;Welcome&lt;/h1&gt;\r\n&lt;p&gt;Sed ut perspiciatis unde omnis iste natus error sit voluptatem accusantium doloremque laudantium, totam rem aperiam, eaque ipsa quae ab illo inventore veritatis et quasi architecto beatae vitae dicta sunt explicabo. Nemo enim ipsam voluptatem quia voluptas sit aspernatur aut odit aut fugit, sed quia consequuntur&lt;/p&gt;\r\n&lt;h2&gt;Magni dolores eos qui ratione voluptatem sequi nesciunt&lt;/h2&gt;\r\n&lt;p&gt;Neque porro quisquam est, qui dolorem ipsum quia dolor sit amet, consectetur, adipisci velit, sed quia non numquam eius modi tempora incidunt ut labore et dolore magnam aliquam quaerat voluptatem. Ut enim ad minima veniam, quis nostrum exercitationem ullam corporis suscipit laboriosam, nisi ut aliquid ex ea commodi consequatur? Quis autem vel eum iure reprehenderit qui in ea voluptate velit esse quam nihil molestiae consequatur, vel illum qui dolorem eum fugiat quo voluptas nulla pariatur&lt;/p&gt;', 'seo keywords here', 'seo description here', '{$_config['company_name']}', 1, 0, UTC_TIMESTAMP()),

(2, 0, 1, 1, 'services', 'Services', 0, '&lt;h1&gt;Services&lt;/h1&gt;\r\n&lt;p&gt;Sed ut perspiciatis unde omnis iste natus error sit voluptatem accusantium doloremque laudantium, totam rem aperiam, eaque ipsa quae ab illo inventore veritatis et quasi architecto beatae vitae dicta sunt explicabo. Nemo enim ipsam voluptatem quia voluptas sit aspernatur aut odit aut fugit, sed quia consequuntur&lt;/p&gt;\r\n&lt;h2&gt;Magni dolores eos qui ratione voluptatem sequi nesciunt&lt;/h2&gt;\r\n&lt;p&gt;Neque porro quisquam est, qui dolorem ipsum quia dolor sit amet, consectetur, adipisci velit, sed quia non numquam eius modi tempora incidunt ut labore et dolore magnam aliquam quaerat voluptatem. Ut enim ad minima veniam, quis nostrum exercitationem ullam corporis suscipit laboriosam, nisi ut aliquid ex ea commodi consequatur? Quis autem vel eum iure reprehenderit qui in ea voluptate velit esse quam nihil molestiae consequatur, vel illum qui dolorem eum fugiat quo voluptas nulla pariatur&lt;/p&gt;', 'Services', 'Services', 'Services', 1, 0, UTC_TIMESTAMP()),

(3, 2, 1, 0, 'first-service', 'First Service', 0, '&lt;h1&gt;First Service&lt;/h1&gt;\r\n&lt;p&gt;Sed ut perspiciatis unde omnis iste natus error sit voluptatem accusantium doloremque laudantium, totam rem aperiam, eaque ipsa quae ab illo inventore veritatis et quasi architecto beatae vitae dicta sunt explicabo. Nemo enim ipsam voluptatem quia voluptas sit aspernatur aut odit aut fugit, sed quia consequuntur&lt;/p&gt;\r\n&lt;h2&gt;Magni dolores eos qui ratione voluptatem sequi nesciunt&lt;/h2&gt;\r\n&lt;p&gt;Neque porro quisquam est, qui dolorem ipsum quia dolor sit amet, consectetur, adipisci velit, sed quia non numquam eius modi tempora incidunt ut labore et dolore magnam aliquam quaerat voluptatem. Ut enim ad minima veniam, quis nostrum exercitationem ullam corporis suscipit laboriosam, nisi ut aliquid ex ea commodi consequatur? Quis autem vel eum iure reprehenderit qui in ea voluptate velit esse quam nihil molestiae consequatur, vel illum qui dolorem eum fugiat quo voluptas nulla pariatur&lt;/p&gt;', 'First Service', 'First Service', 'First Service', 1, 0, UTC_TIMESTAMP()),

(4, 2, 1, 1, 'second-service', 'Second Service', 0, '&lt;h1&gt;Second Service&lt;/h1&gt;\r\n&lt;p&gt;Sed ut perspiciatis unde omnis iste natus error sit voluptatem accusantium doloremque laudantium, totam rem aperiam, eaque ipsa quae ab illo inventore veritatis et quasi architecto beatae vitae dicta sunt explicabo. Nemo enim ipsam voluptatem quia voluptas sit aspernatur aut odit aut fugit, sed quia consequuntur&lt;/p&gt;\r\n&lt;h2&gt;Magni dolores eos qui ratione voluptatem sequi nesciunt&lt;/h2&gt;\r\n&lt;p&gt;Neque porro quisquam est, qui dolorem ipsum quia dolor sit amet, consectetur, adipisci velit, sed quia non numquam eius modi tempora incidunt ut labore et dolore magnam aliquam quaerat voluptatem. Ut enim ad minima veniam, quis nostrum exercitationem ullam corporis suscipit laboriosam, nisi ut aliquid ex ea commodi consequatur? Quis autem vel eum iure reprehenderit qui in ea voluptate velit esse quam nihil molestiae consequatur, vel illum qui dolorem eum fugiat quo voluptas nulla pariatur&lt;/p&gt;', 'Second Service', 'Second Service', 'Second Service', 1, 0, UTC_TIMESTAMP()),

(5, 2, 1, 2, 'third-service', 'Third Service', 0, '&lt;h1&gt;Third Service&lt;/h1&gt;\r\n&lt;p&gt;Sed ut perspiciatis unde omnis iste natus error sit voluptatem accusantium doloremque laudantium, totam rem aperiam, eaque ipsa quae ab illo inventore veritatis et quasi architecto beatae vitae dicta sunt explicabo. Nemo enim ipsam voluptatem quia voluptas sit aspernatur aut odit aut fugit, sed quia consequuntur&lt;/p&gt;\r\n&lt;h2&gt;Magni dolores eos qui ratione voluptatem sequi nesciunt&lt;/h2&gt;\r\n&lt;p&gt;Neque porro quisquam est, qui dolorem ipsum quia dolor sit amet, consectetur, adipisci velit, sed quia non numquam eius modi tempora incidunt ut labore et dolore magnam aliquam quaerat voluptatem. Ut enim ad minima veniam, quis nostrum exercitationem ullam corporis suscipit laboriosam, nisi ut aliquid ex ea commodi consequatur? Quis autem vel eum iure reprehenderit qui in ea voluptate velit esse quam nihil molestiae consequatur, vel illum qui dolorem eum fugiat quo voluptas nulla pariatur&lt;/p&gt;', 'Third Service', 'Third Service', 'Third Service', 1, 0, UTC_TIMESTAMP()),

(6, 0, 1, 2, 'contact', 'Contact', 0, '&lt;h1&gt;Contact Us&lt;/h1&gt;', 'Contact', 'Contact', 'Contact', 1, 0, UTC_TIMESTAMP()),

(7, 0, 0, 0, 'thank-you', 'Thank You', 0, '&lt;h1&gt;Thank You&lt;/h1&gt;\r\n&lt;p&gt;Thank you for your interest. Someone will be in contact with you shortly.&lt;/p&gt;\r\n&lt;p&gt;{{goback}}&lt;/p&gt;', 'Thank You', 'Thank You', 'Thank You', 1, 0, UTC_TIMESTAMP()),

(8, 0, 0, 0, 'site-map', 'Site Map', 0, '&lt;p&gt;site map is generated from a custom page, this text&amp;nbsp;will not show&lt;/p&gt;', 'Site Map', 'Site Map', 'Site Map', 1, 0, UTC_TIMESTAMP()),

(9, 0, 0, 0, 'privacy', 'Privacy', 1, '&lt;h1&gt;Privacy&lt;/h1&gt;', 'Privacy', 'Privacy', 'Privacy', 1, 0, UTC_TIMESTAMP())";

		$result = logged_query( "{$pages_query}",0,array());

		if($result === false) die('Error Adding Core Data');
		if($result) $moduleAdded['Pages'] = true;
	}

	$query ="
CREATE TABLE IF NOT EXISTS `page_revisions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `page_id` int(11) NOT NULL,
  `slug` varchar(50) NOT NULL,
  `page_title` varchar(100) NOT NULL,
  `no_follow` int(1) DEFAULT '0',
  `content` text NOT NULL,
  `seo_keywords` text NOT NULL,
  `seo_description` varchar(250) NOT NULL,
  `seo_title` varchar(100) NOT NULL,
  `status` tinyint(1) DEFAULT '0',
  `visibility` tinyint(1) DEFAULT '0',
  `date` varchar(30) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;";
	$result = logged_query($query,0,array());
	if($result === false) die('Error Creating Core Table');

	$query ="
CREATE TABLE IF NOT EXISTS `paypal_ipn` (
  `txn_id` varchar(250) COLLATE utf8_unicode_ci NOT NULL,
  `date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`txn_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
";
	$result = logged_query($query,0,array());
	if($result === false) die('Error Creating Core Table: paypal_ipn');


	$query ="
CREATE TABLE IF NOT EXISTS `pictures` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `page_id` bigint(20) NOT NULL,
  `page_type` varchar(25) DEFAULT 'dflt',
  `filename` varchar(250) NOT NULL,
  `alt` varchar(250) NOT NULL,
  `status` int(1) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;
";
	$result = logged_query($query,0,array());
	if($result === false) die('Error Creating Core Table');

	$query ="
CREATE TABLE IF NOT EXISTS `upload_file` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `page_id` bigint(20) NOT NULL,
  `page_type` varchar(25) DEFAULT 'dflt',
  `filename` varchar(250) NOT NULL,
  `alt` varchar(250) NOT NULL,
  `status` int(1) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;
";
	$result = logged_query($query,0,array());
	if($result === false) die('Error Creating Core Table');



	// USER TABLES
	$userTable ="
CREATE TABLE IF NOT EXISTS `auth_users_permit` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `module` varchar(256) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_module` (`user_id`,`module`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1
	";
	$result = logged_query($userTable,0,array());
	if($result === false) die('Error Creating Core Table');

	$userTable ="
CREATE TABLE IF NOT EXISTS `auth_users` (
  `user_id` int(11) NOT NULL AUTO_INCREMENT,
  `first_name` varchar(50) DEFAULT NULL,
  `last_name` varchar(50) DEFAULT NULL,
  `company_name` varchar(150) NOT NULL,
  `office_address` varchar(150) NOT NULL,
  `office_city` varchar(150) NOT NULL,
  `office_postal` varchar(10) NOT NULL,
  `office_number` varchar(15) NOT NULL,
  `fax_number` varchar(15) NOT NULL,
  `cell_number` varchar(15) NOT NULL,
  `email` varchar(150) DEFAULT NULL,
  `timezone` varchar(150) DEFAULT 'America/Los_Angeles',
  `username` varchar(25) DEFAULT NULL,
  `password` varchar(100) DEFAULT NULL,
  `tmp_password` varchar(100) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `tmp_password_date` datetime NOT NULL,
  `admin` varchar(3) NOT NULL,
  PRIMARY KEY (`user_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=0
	";
	$result = logged_query($userTable,0,array());
	if($result === false) die('Error Creating Core Table');

	// add core user if not already present
	$query ="SELECT `user_id` FROM `auth_users` WHERE `user_id`=0";
	$result = logged_query($query,0,array());
	if ($result === false) die('Error Creating Core Table');
	if(!isset($result[0]))
	{
		$query ="INSERT INTO `auth_users` (`user_id`, `first_name`, `last_name`, `email`, `username`, `password`, `admin`)
		VALUES (0,:first_name,:last_name,:email,:username,:pass,'yes');";
		$result = logged_query($query,0,array(
			":first_name" =>$_POST['first_name'],
			":last_name" => $_POST['last_name'],
			":email" =>		$_POST['email'],
			":username" =>	$_POST['username'],
			":pass" =>		$pass
		));

		if ($result === false) die('Error Creating Core Table');
		if($result) $moduleAdded['Users'] = true;
	}
	return $moduleAdded;
}

/* 	ENCRYPTION/DECRYPTION  */
function encrypt($string)
{
	$key = get_key();
    return rtrim(
        base64_encode(
            mcrypt_encrypt(
                MCRYPT_RIJNDAEL_256,
                $key, $string,
                MCRYPT_MODE_ECB,
                mcrypt_create_iv(
                    mcrypt_get_iv_size(
                        MCRYPT_RIJNDAEL_256,
                        MCRYPT_MODE_ECB
                    ),
					MCRYPT_RAND
				)
            )
        ), "\0"
    );
}
function decrypt($encrypted)
{
	$key = get_key();
    return rtrim(
        mcrypt_decrypt(
            MCRYPT_RIJNDAEL_256,
            $key,
            base64_decode($encrypted),
            MCRYPT_MODE_ECB,
            mcrypt_create_iv(
                mcrypt_get_iv_size(
                    MCRYPT_RIJNDAEL_256,
                    MCRYPT_MODE_ECB
                ),
                MCRYPT_RAND
            )
        ), "\0"
    );
}
// get sitewide encryption key
function get_key()
{
	global $_config;
	$handle = fopen($_config['file'], 'r');
	$key = fgets($handle);
	fclose($handle);
	return $key;
}

function cleanup_admin_tmp_password()
{
	logged_query("UPDATE `auth_users` SET `tmp_password`='', `tmp_password_date`='' WHERE `tmp_password`<>'' AND `tmp_password_date` < NOW() - interval 10 minute",0,array() );
}
