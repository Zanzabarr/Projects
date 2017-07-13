<?php 
include('includes/headerClass.php');


// ************************* ADMINISTRATION: DELETE USER ***************************** //	
// if this is a delete of a secondary user, do that first so all subsequent data is legitimate
// only admin can delete users and nobody can delete the super-admin (id 1)
if( isset($_GET['delete']) && logged_in_as_admin() && $_config['multi_user'] && is_numeric($_GET['delete']) && $_GET['delete'] > 1)
{
	//lets make sure the user exists and get the username for success message
	$userResult = logged_query_assoc_array(
		"SELECT `username` FROM `auth_users` WHERE `user_id` = :deleteid",
		null,1, array(":deleteid" => $_GET['delete'])
	);

	if( !$userResult)
	{
		// oops, error message
		$message['banner'] = array (
			'heading' => 'Invalid User', 
			'message' => 'That User cannot be removed.', 
			'type' => 'error'
		);		
		
	} else {
		//ok, that's it: do the delete
		$success = logged_query("DELETE FROM `auth_users` WHERE `user_id` = :deleteid", 0, array(':deleteid' => $_GET['delete']));
		
		if($success) 
		{
			$success = logged_query("DELETE FROM `auth_users_permit` WHERE `user_id` = :deleteid", 0, array(':deleteid' => $_GET['delete']));
		}		
		
		if ($success)
		{
			// set the success message	
			$message['banner'] = array (
				'heading' => 'User Removed', 
				'message' => "Successfully removed user:{$userResult[0]['username']}", 
				'type' => 'success'
			);
		} else {
			// set the success message	
			$message['banner'] = array (
				'heading' => 'Error Removing User', 
				'message' => "Unable to update record for{$userResult[0]['username']}, click 'Dashboard' to clear data then try again.", 
				'type' => 'error'
			);
		}
	}
	// in either case set the trigger to open the admin section
	$message['openTag'][] = '#admin-toggle-wrap';
}
	
	
// get all users
$users=logged_query_assoc_array("SELECT * FROM auth_users ORDER BY `username`", 'user_id',0,array());
// get the current user's data
$info=$users[$_SESSION['uid']];
// remove the current user from the users list
unset($users[$_SESSION['uid']]);

// create an array of all usernames
$usernames = array();
foreach($users as $user) $usernames[] = $user['username']; 

// create valid modules array
//		key is module name, value is display friendly vesion of that name
//			custom display name can be set in /includes/config.php
$tmpModules = $_SESSION['activeModules'];
$permitModules[] = array('module_name' =>'pages', 'module_pretty' => 'Pages'); 
foreach ($tmpModules as $module)
{
	if ( isset($_config['beautified']) && array_key_exists($module, $_config['beautified']))
	{
		$beauty = $_config['beautified'][$module]; // false if disallowed
	}
	else $beauty = beautify($module);
	
	if($beauty) $permitModules[] = array( 'module_name' => $module, 'module_pretty' => $beauty);
}

// ************************* PERMISSIONS ***************************** //
// do permission inserts before building page resources so altered admin can be changed for js user array
if( isset($_POST['permissions']) && logged_in_as_admin() && $_config['multi_user']  )  
{ 
	$user_id = $_POST['permit_user'];
	// ensure we are trying to update a legitimate user 
	//  (superadmin always has full permissions and subadmin can't update self)
	//   	superadmin's id = 1 and $users doesn't contain active user's data
	if ( is_numeric($user_id) && $user_id > 1 && array_key_exists($user_id, $users) )
	{
		// update users admin status
		$permit_add_user = isset($_POST['permit_add_user']) ? 'yes' : '';
		$update = "
			UPDATE auth_users 
			SET admin = '{$permit_add_user}'
			WHERE user_id=:user_id";
		logged_query($update,0,array(':user_id' => $user_id)) ;
		
		// update admin status in $users array
		$users[$user_id]['admin'] = $permit_add_user;

		// first, wipe out all permissions for this user
		logged_query("DELETE FROM `auth_users_permit` WHERE `user_id` = :user_id",0, array(':user_id' =>  $user_id));
		
		//  take in all permissions and insert into permission array
		// 	note: must be a real module!		
		if(isset($_POST['permit'])) : foreach($_POST['permit'] as $validate_module => $value) :
			// make sure this is a valid array
			foreach($permitModules as $arModule)
			{
				$real_module = $arModule['module_name'];
				if($validate_module == $real_module)
				{
					// is validated: do insert
					$insert = "INSERT INTO `auth_users_permit` (user_id, module) 
VALUES (:user_id, :real_module)";
					logged_query($insert,0,array(':user_id' => $user_id, ':real_module' => $real_module));	
					
					break;
				}
			}	
		endforeach; endif;
		
		// success message
		$username = ucwords($users[$user_id]['username']);
		$message['banner'] = array (
			'heading' => 'Updated Permissions', 
			'message' => "For '{$username}'", 
			'type' => 'success'
		);
	} else {
		// no such user error message
		$message['banner'] = array (
			'heading' => 'Update Failed', 
			'message' => 'The requested user could not be found.', 
			'type' => 'error'
		);
		
	}
	// keep the permissions section open
	$message['openTag'][] = '#permit-toggle-wrap';
}	
	
	// ************************* ADMINISTRATION: ADD/EDIT USER ***************************** //
	// do administration insert/update before building page resources so new admin can be added to js user array
	if( isset($_POST['admin']) && logged_in_as_admin() && $_config['multi_user'] &&  is_numeric($_POST['admin_user'])
	&& ($_POST['admin'] == 'new' || $_POST['admin'] == 'edit' ) 
	)
	{ 
		if ($_POST['admin'] == 'new') 		$action = 'new';
		elseif ($_POST['admin'] == 'edit')	$action = 'edit';

		$new_user['admin_user'] 	= $user_id 		= trim(htmlspecialchars($_POST['admin_user'],ENT_QUOTES));
		$new_user['admin_email'] 	= $email 		= trim(htmlspecialchars($_POST['admin_email'],ENT_QUOTES));
		$new_user['admin_timezone'] = $timezone 	= trim(htmlspecialchars($_POST['admin_timezone'],ENT_QUOTES));
		$new_user['admin_pass'] 	= $pass 		= trim(htmlspecialchars($_POST['admin_pass'],ENT_QUOTES));
		$new_user['admin_ver_pass'] = $ver_pass 	= trim(htmlspecialchars($_POST['admin_ver_pass'],ENT_QUOTES));
		$new_user['admin_username'] = $username		= trim(htmlspecialchars($_POST['admin_username'],ENT_QUOTES));
		$new_user['admin_fname'] 	= $first_name		= trim(htmlspecialchars($_POST['admin_fname'],ENT_QUOTES));
		$new_user['admin_lname'] 	= $last_name		= trim(htmlspecialchars($_POST['admin_lname'],ENT_QUOTES));
		// echo display_array($new_user);
		// validate data
		foreach($new_user as $key => $data)
		{	
			// validate email address
			if( $key == 'admin_email' && $data && !check_email_address($data) )
			{
				$message['inline'][$key] = array ('type' => 'errorMsg', 'msg' => 'Valid address required');
				$message['banner'] = array (
					'heading' => 'Invalid Email', 
					'message' => 'Please supply a valid email address.', 
					'type' => 'error'
				);				
			}
			// validate password
			if( $key == 'admin_ver_pass' && $data != $new_user['admin_pass'] )
			{
				$message['inline'][$key] = array ('type' => 'errorMsg', 'msg' => 'Must match password above');
				$message['banner'] = array (
					'heading' => 'Password Mismatch', 
					'message' => '"Password" and "Verify Password" must match exactly.', 
					'type' => 'error'
				);
			}
			
			if ( $data == '')
			{
				$message['inline'][$key] = array ('type' => 'errorMsg', 'msg' => 'Required field');
				$message['banner'] = array (
					'heading' => 'Required Fields', 
					'message' => 'All fields are required.', 
					'type' => 'error'
				);
			}	
			// username must be unique
			// note: user array doesn't have self or superadmin in it.
			if( $key == 'admin_username' && 
				// check matches case insensitively
				(strtolower($data) == strtolower($info['username']) || in_arrayi($data, $usernames) ) 
			)
			{
				// not an error if this is an edit and username is the selected  user (obviously)
				if($user_id == 0 || strtolower($data) != strtolower($users[$user_id]['username']) ){
					$message['inline'][$key] = array ('type' => 'errorMsg', 'msg' => 'Username already exists');
					$message['banner'] = array (
						'heading' => 'Username Error', 
						'message' => 'Choose a different username, this one already exists.', 
						'type' => 'error'
					);
				}
			}
		}
		// completed validation, if errors exist, $message is set;
		if (!isset($message))
		{
			// no errors, save data.
			// first case: new user
			if($action == 'new')
			{
				$pass = hasher($pass);
				// insert into db
				$insert = "INSERT INTO `auth_users` (email, timezone, password, username, first_name, last_name) 
VALUES (:email, :timezone, :pass, :username, :first_name, :last_name)";
				logged_query($insert,0,
					array(':email' => $email, 
						':timezone' => $timezone, 
						':pass' => $pass, 
						':username' => $username,
						':first_name' => $first_name,
						':last_name' => $last_name
						
					)
				);	
				//$new_user['admin_user'] = mysql_insert_id();
				$new_user['admin_user'] = $_config['db']->lastInsertId();

				// set the success message
				$message['banner'] = array ('heading' => 'Successfully Saved', 'message' => "New Admin added: {$username}", 'type' => 'success');
			}
			elseif ($action == 'edit')
			{													
				//$password = md5($pass);
				$password = hasher($pass);
				$update = "
						UPDATE auth_users 
						SET email 	 	=:email,
							timezone 	=:timezone,
							password 	=:password,
							username 	= :username,
							first_name 	= :first_name,
							last_name 	= :last_name
						WHERE user_id=:user_id";
				logged_query($update,0,array(
					":email" 		=> $email,
					":timezone" 	=> $timezone,
					":password" 	=> $password,
					":username" 	=> $username,
					":first_name" 	=> $first_name,
					":last_name"	=> $last_name,
					":user_id" 		=> $user_id
				)) ;	
				
				// set the success message
				$message['banner'] = array ('heading' => 'Successfully Saved', 'message' => "Updated Admin Info: {$username}", 'type' => 'success');
				
			}
			
			// SAVED: DO CLEANUP
			
			// update $users to include the $new_user or alter the updated user
			$users[$new_user['admin_user']] = array(
				'user_id'	=> $new_user['admin_user'],
				'email' 	=> $new_user['admin_email'], 
				'timezone' 	=> $new_user['admin_timezone'], 
				'password' 	=> $new_user['admin_pass'],
				'username'	=> $new_user['admin_username'],
				'first_name'=> $new_user['admin_fname'],
				'last_name'	=> $new_user['admin_lname']
			);
			
			// unset the post data
			unset($_POST['admin_user']);
			unset($_POST['admin_email']);
			unset($_POST['admin_timezone']);
			unset($_POST['admin_pass']);
			unset($_POST['admin_ver_pass']);
			unset($_POST['admin_username']);
			unset($_POST['admin_fname']);
			unset($_POST['admin_lname']);
			// set id so js can zoom to next section: set permissions
			$message['openTag'][] = '#permit-toggle-wrap';
			
			// set the Permissions Section's user to this one
			$opening_permission_user = $new_user['admin_user'];
		}
		// errors exist, nothing saved. Post data will be used to set the field values and $message is set to fill error messages
	}
	
	$pageResources = "<script type=\"text/javascript\" src=\"".$baseUrl."js/dashboard.js\"></script>";
	// ************************* BUILD JS USERS ARRAY ***************************** //
	if ( $_config['multi_user'] ) {
		$pageResources .= "<script type='text/javascript'>";
		$pageResources .= "var arUsers= new Array();";
		// build up array of users
		

		foreach ($users as $userId => $user) : if ($userId > 1) :
			// build up array of user data
			$pageResources .="tmpArray = new Array();";
			$pageResources .="tmpArray['user_id'] = '{$user['user_id']}';";
			$pageResources .="tmpArray['username'] = '{$user['username']}';";
			$pageResources .="tmpArray['first_name'] = '{$user['first_name']}';";
			$pageResources .="tmpArray['last_name'] = '{$user['last_name']}';";
			$pageResources .="tmpArray['email'] = '{$user['email']}';";
			$pageResources .="tmpArray['timezone'] = '{$user['timezone']}';";
			$pageResources .="arUsers.push(tmpArray);";
		endif; endforeach;	
		$pageResources .= "</script>";
	}
	
	// ***************** BUILD JS MODULE PERMISSION ARRAY ************************* //
	if ( $_config['multi_user'] ) {
		// create user permissions array
		$userPermissions = logged_query_assoc_array("
			SELECT * FROM `auth_users_permit` WHERE `user_id` != :uid
		",null,0,array(':uid' => $info['user_id']));
	
		// create multi-dim array of permitted modules by user_id
		$arModPermit = array();
		foreach($userPermissions as $userPermission)
		{
			$arModPermit[$userPermission['user_id']][] = $userPermission['module'];
		}
		
		$pageResources .= "<script type='text/javascript'>";
		$pageResources .= "var arModPermit= new Array();";
		
		// push all true add_user states into the array
		foreach ($users as $userId => $user) : if ($userId > 1 && isset($user['admin']) && $user['admin'] == 'yes') :
			// build up array of user data
			$pageResources .="tmpArray = new Array();";
			$pageResources .="tmpArray['user_id'] = '{$user['user_id']}';";
			$pageResources .="tmpArray['module'] = 'add_user';";
			$pageResources .="arModPermit.push(tmpArray);";
		endif; endforeach;	

		
		// push all module states into the array
		foreach($userPermissions as $userPermission) 
		{
			$pageResources .= "var tmpArray = new Array();";
			$pageResources .= "tmpArray['user_id'] = '{$userPermission['user_id']}';";
			$pageResources .= "tmpArray['module'] = '{$userPermission['module']}';";			
			$pageResources .= "arModPermit.push(tmpArray);";
		}
		$pageResources .= "</script>";
	}
	$pageInit = new headerClass();
	
	$pageInit->createPageTop($pageResources);
	
	// do General info update
	if (isset($_GET['info']) && $_GET['info'] == "update") {
		$info['first_name'] 	= $first_name 		= trim($_POST['first_name']);
		$info['last_name'] 		= $last_name 		= trim($_POST['last_name'] );
		$info['company_name'] 	= $company_name 	= trim($_POST['company_name']);
		$info['office_address'] = $office_address 	= trim($_POST['office_address']);
		$info['office_city'] 	= $office_city 		= trim($_POST['office_city']);
		$info['office_postal'] 	= $office_postal 	= trim($_POST['office_postal']);
		$info['office_number'] 	= $office_number 	= trim($_POST['office_number']);
		$info['fax_number'] 	= $fax_number 		= trim($_POST['fax_number']);
		$info['cell_number'] 	= $cell_number 		= trim($_POST['cell_number']);
		$info['email'] 			= $email 			= trim($_POST['email']);
		$info['timezone'] 		= $timezone 		= trim($_POST['timezone']);
		
		if ($first_name != "" && $last_name != ""  && $email != "" && $good_email = check_email_address($email)) {
			$update = "UPDATE auth_users 
	SET first_name=:first_name, 
		last_name=:last_name,
		company_name=:company_name, 
		office_address=:office_address, 
		office_city=:office_city, 
		office_postal=:office_postal, 
		office_number=:office_number, 
		fax_number=:fax_number, 
		cell_number=:cell_number, 
		email=:email,
		timezone=:timezone
	WHERE user_id=:sess_uid";

			$success = logged_query($update,0,array(
				":first_name" => $first_name,
				":last_name" => $last_name,
				":company_name" => $company_name,
				":office_address" => $office_address,
				":office_city" => $office_city,
				":office_postal" => $office_postal,
				":office_number" => $office_number,
				":fax_number" => $fax_number,
				":cell_number" => $cell_number,
				":email" => $email,
				":timezone" => $timezone,
				":sess_uid" => $_SESSION['uid']
			)); 
			if( ! $success ) {
				$message['banner'] = array ('heading' => 'Error Updating!', 'message' => 'Unable to update user information, click "Dashboard" to clear data, then try again.', 'type' => 'error');
			}
			else {
				$message['banner'] = array ('heading' => 'Success!', 'message' => 'Your information has been updated!', 'type' => 'success');
			}
		} else {
		
			if (!$good_email)
			{
				$message['inline']['email'] = array ('type' => 'errorMsg', 'msg' => 'Please supply a valid email address');
				$message['banner'] = array (
					'heading' => 'Invalid Email', 
					'message' => 'Please supply a valid email address', 
					'type' => 'error'
				);
			}
			$req_fld = false;
			if ($first_name == '') 
			{
				$message['inline']['first_name'] = array ('type' => 'errorMsg', 'msg' => 'Required field');
				$req_fld = true;
			}
			if ($last_name == '') 
			{	
				$message['inline']['last_name'] = array ('type' => 'errorMsg', 'msg' => 'Required field');
				$req_fld = true;
			}
			if ($email == '') 
			{
				$message['inline']['email'] = array ('type' => 'errorMsg', 'msg' => 'Required field');
				$req_fld = true;
			}
			if ($req_fld)
			{
				$message['banner'] = array (
					'heading' => 'Oops!', 
					'message' => 'You can not leave any required fields blank!', 
					'type' => 'error'
				);
			}	
		}
	}

	// attempting to update password
	if (isset($_GET['password']) && $_GET['password'] == "update") {
		$username = trim($_POST['username']);
		
		// validate
		if ( $username == '' ) 
			$message['inline']['username'] = array ('type' => 'errorMsg', 'msg' => 'Required field');
		// username must be unique
		// note: user array doesn't have self or superadmin in it.
		if(	strtolower($username) != strtolower($info['username']) || in_arrayi($username, $usernames) )
			$message['inline']['username'] = array ('type' => 'errorMsg', 'msg' => 'Username exists, choose another');
		if ( $_POST['new_pass'] == "") 
			$message['inline']['new_pass'] = array ('type' => 'errorMsg', 'msg' => 'Required field');
		if ( $_POST['new_pass'] != $_POST['ver_pass'] ) 
			$message['inline']['ver_pass'] = array ('type' => 'errorMsg', 'msg' => "Verification and New Passwords don't match");
		
		// see if any errors were recorded
		if ( isset($message['inline']) && count($message['inline']) ) 
		{
			//error occured
			$message['banner'] = array ('heading' => 'Could not update password', 'message' => 'correct errors on the page.', 'type' => 'error');
		}
		else
		{
			// success: save
			//$md5pass = md5($_POST['ver_pass']);
			$hashPass = hasher(trim($_POST['ver_pass']));
			$updatePass = "UPDATE auth_users 
	SET password=:hashPass, 
		username=:username
	WHERE user_id =:sess_uid";
			$success = logged_query($updatePass,0,array(
				":hashPass" => $hashPass,
				":username" => $username,
				":sess_uid" => $_SESSION['uid']
			));
			if ( $success ) 
			{
				$message['banner'] = array ('heading' => 'Updated Password', 'message' => '', 'type' => 'success');
			} else {
				$message['banner'] = array ('heading' => 'Error Updating Password', 'message' => 'Password not changed. Please click "Dashboard" to refresh data, then try again.', 'type' => 'error');
			}
			
			$_POST['password'] = "";
		}
	}

	// timezone array
	$arTZ = array(
		'Pacific/Midway' => '(GMT-11:00) Midway Island, Samoa',
		'America/Adak' => '(GMT-10:00) Hawaii-Aleutian',
		'Etc/GMT+10' => '(GMT-10:00) Hawaii',
		'Pacific/Marquesas' => '(GMT-09:30) Marquesas Islands',
		'Pacific/Gambier' => '(GMT-09:00) Gambier Islands',
		'America/Anchorage' => '(GMT-09:00) Alaska',
		'America/Ensenada' => '(GMT-08:00) Tijuana, Baja California',
		'Etc/GMT+8' => '(GMT-08:00) Pitcairn Islands',
		'America/Los_Angeles' => '(GMT-08:00) Pacific Time (US & Canada)',
		'America/Denver' => '(GMT-07:00) Mountain Time (US & Canada)',
		'America/Chihuahua' => '(GMT-07:00) Chihuahua, La Paz, Mazatlan',
		'America/Dawson_Creek' => '(GMT-07:00) Arizona',
		'America/Belize' => '(GMT-06:00) Saskatchewan, Central America',
		'America/Cancun' => '(GMT-06:00) Guadalajara, Mexico City, Monterrey',
		'Chile/EasterIsland' => '(GMT-06:00) Easter Island',
		'America/Chicago' => '(GMT-06:00) Central Time (US & Canada)',
		'America/New_York' => '(GMT-05:00) Eastern Time (US & Canada)',
		'America/Havana' => '(GMT-05:00) Cuba',
		'America/Bogota' => '(GMT-05:00) Bogota, Lima, Quito, Rio Branco',
		'America/Caracas' => '(GMT-04:30) Caracas',
		'America/Santiago' => '(GMT-04:00) Santiago',
		'America/La_Paz' => '(GMT-04:00) La Paz',
		'Atlantic/Stanley' => '(GMT-04:00) Faukland Islands',
		'America/Campo_Grande' => '(GMT-04:00) Brazil',
		'America/Goose_Bay' => '(GMT-04:00) Atlantic Time (Goose Bay)',
		'America/Glace_Bay' => '(GMT-04:00) Atlantic Time (Canada)',
		'America/St_Johns' => '(GMT-03:30) Newfoundland',
		'America/Araguaina' => '(GMT-03:00) UTC-3',
		'America/Montevideo' => '(GMT-03:00) Montevideo',
		'America/Miquelon' => '(GMT-03:00) Miquelon, St. Pierre',
		'America/Godthab' => '(GMT-03:00) Greenland',
		'America/Argentina/Buenos_Aires' => '(GMT-03:00) Buenos Aires',
		'America/Sao_Paulo' => '(GMT-03:00) Brasilia',
		'America/Noronha' => '(GMT-02:00) Mid-Atlantic',
		'Atlantic/Cape_Verde' => '(GMT-01:00) Cape Verde Is.',
		'Atlantic/Azores' => '(GMT-01:00) Azores',
		'Europe/Belfast' => '(GMT) Greenwich Mean Time : Belfast',
		'Europe/Dublin' => '(GMT) Greenwich Mean Time : Dublin',
		'Europe/Lisbon' => '(GMT) Greenwich Mean Time : Lisbon',
		'Europe/London' => '(GMT) Greenwich Mean Time : London',
		'Africa/Abidjan' => '(GMT) Monrovia, Reykjavik',
		'Europe/Amsterdam' => '(GMT+01:00) Amsterdam, Berlin, Bern, Rome, Stockholm, Vienna',
		'Europe/Belgrade' => '(GMT+01:00) Belgrade, Bratislava, Budapest, Ljubljana, Prague',
		'Europe/Brussels' => '(GMT+01:00) Brussels, Copenhagen, Madrid, Paris',
		'Africa/Algiers' => '(GMT+01:00) West Central Africa',
		'Africa/Windhoek' => '(GMT+01:00) Windhoek',
		'Asia/Beirut' => '(GMT+02:00) Beirut',
		'Africa/Cairo' => '(GMT+02:00) Cairo',
		'Asia/Gaza' => '(GMT+02:00) Gaza',
		'Africa/Blantyre' => '(GMT+02:00) Harare, Pretoria',
		'Asia/Jerusalem' => '(GMT+02:00) Jerusalem',
		'Europe/Minsk' => '(GMT+02:00) Minsk',
		'Asia/Damascus' => '(GMT+02:00) Syria',
		'Europe/Moscow' => '(GMT+03:00) Moscow, St. Petersburg, Volgograd',
		'Africa/Addis_Ababa' => '(GMT+03:00) Nairobi',
		'Asia/Tehran' => '(GMT+03:30) Tehran',
		'Asia/Dubai' => '(GMT+04:00) Abu Dhabi, Muscat',
		'Asia/Yerevan' => '(GMT+04:00) Yerevan',
		'Asia/Kabul' => '(GMT+04:30) Kabul',
		'Asia/Yekaterinburg' => '(GMT+05:00) Ekaterinburg',
		'Asia/Tashkent' => '(GMT+05:00) Tashkent',
		'Asia/Kolkata' => '(GMT+05:30) Chennai, Kolkata, Mumbai, New Delhi',
		'Asia/Katmandu' => '(GMT+05:45) Kathmandu',
		'Asia/Dhaka' => '(GMT+06:00) Astana, Dhaka',
		'Asia/Novosibirsk' => '(GMT+06:00) Novosibirsk',
		'Asia/Rangoon' => '(GMT+06:30) Yangon (Rangoon)',
		'Asia/Bangkok' => '(GMT+07:00) Bangkok, Hanoi, Jakarta',
		'Asia/Krasnoyarsk' => '(GMT+07:00) Krasnoyarsk',
		'Asia/Hong_Kong' => '(GMT+08:00) Beijing, Chongqing, Hong Kong, Urumqi',
		'Asia/Irkutsk' => '(GMT+08:00) Irkutsk, Ulaan Bataar',
		'Australia/Perth' => '(GMT+08:00) Perth',
		'Australia/Eucla' => '(GMT+08:45) Eucla',
		'Asia/Tokyo' => '(GMT+09:00) Osaka, Sapporo, Tokyo',
		'Asia/Seoul' => '(GMT+09:00) Seoul',
		'Asia/Yakutsk' => '(GMT+09:00) Yakutsk',
		'Australia/Adelaide' => '(GMT+09:30) Adelaide',
		'Australia/Darwin' => '(GMT+09:30) Darwin',
		'Australia/Brisbane' => '(GMT+10:00) Brisbane',
		'Australia/Hobart' => '(GMT+10:00) Hobart',
		'Asia/Vladivostok' => '(GMT+10:00) Vladivostok',
		'Australia/Lord_Howe' => '(GMT+10:30) Lord Howe Island',
		'Etc/GMT-11' => '(GMT+11:00) Solomon Is., New Caledonia',
		'Asia/Magadan' => '(GMT+11:00) Magadan',
		'Pacific/Norfolk' => '(GMT+11:30) Norfolk Island',
		'Asia/Anadyr' => '(GMT+12:00) Anadyr, Kamchatka',
		'Pacific/Auckland' => '(GMT+12:00) Auckland, Wellington',
		'Etc/GMT-12' => '(GMT+12:00) Fiji, Kamchatka, Marshall Is.',
		'Pacific/Chatham' => '(GMT+12:45) Chatham Islands',
		'Pacific/Tongatapu' => '(GMT+13:00) Nuku Alofa',
		'Pacific/Kiritimati' => '(GMT+14:00) Kiritimati'      
	);
	
	// ***************       Inputs       **************** //	
	$message = isset($message) ? $message :false;
	
	// *************** General Info Inputs **************** //
	$input_tz = new inputField( 'Time Zone', 'timezone' );	
	$input_tz->type('select');
	$input_tz->selected($info['timezone']);
	foreach ($arTZ as $value => $heading )	$input_tz->option($value, $heading);

	if (isset($info['first_name'])) $val = $info['first_name'];
	elseif (isset($_POST['first_name'])) $val = $_POST['first_name'];
	else $val = '';
	$input_first_name = new inputField( 'First Name:', 'first_name' );	
	$input_first_name->value( $val );
	$input_first_name->arErr( $message );
	$input_first_name->counterMax(50);
	
	if (isset($info['last_name'])) $val = $info['last_name'];
	elseif (isset($_POST['last_name'])) $val = $_POST['last_name'];
	else $val = '';
	$input_last_name = new inputField( 'Last Name:', 'last_name' );	
	$input_last_name->value( $val );
	$input_last_name->arErr( $message );
	$input_last_name->counterMax(50);

	$val =  isset($info['company_name']) ? $info['company_name'] : $_POST['company_name'] ;
	$input_company_name = new inputField( 'Company Name:', 'company_name' );	
	$input_company_name->value( $val );
	$input_company_name->counterMax(150);
										
	$val =  isset($info['office_address']) ? $info['office_address'] : $_POST['office_address'] ;
	$input_office_address = new inputField( 'Office Address:', 'office_address' );	
	$input_office_address->value( $val );
	$input_office_address->counterMax(150);

	$val =  isset($info['office_city']) ? $info['office_city'] : $_POST['office_city'] ;
	$input_office_city = new inputField( 'Office City:', 'office_city' );	
	$input_office_city->value( $val );
	$input_office_city->counterMax(150);
					
	$val =  isset($info['office_postal']) ? $info['office_postal'] : $_POST['office_postal'] ;
	$input_office_postal = new inputField( 'Office Postal Code:', 'office_postal' );	
	$input_office_postal->value( $val );
	$input_office_postal->counterMax(10);
					
	$val =  isset($info['office_number']) ? $info['office_number'] : $_POST['office_number'] ;
	$input_office_number = new inputField( 'Office Number:', 'office_number' );	
	$input_office_number->value( $val );
	$input_office_number->counterMax(15);
		
	$val =  isset($info['fax_number']) ? $info['fax_number'] : $_POST['fax_number'] ;
	$input_fax_number = new inputField( 'Fax Number:', 'fax_number' );	
	$input_fax_number->value( $val );
	$input_fax_number->counterMax(25);
		
	$val =  isset($info['cell_number']) ? $info['cell_number'] : $_POST['cell_number'] ;
	$input_cell_number = new inputField( 'Cell Phone:', 'cell_number' );	
	$input_cell_number->value( $val );
	$input_cell_number->counterMax(15);
		
	$val =  isset($info['email']) ? $info['email'] : $_POST['email'] ;	
	$input_email = new inputField( 'Email:', 'email' );	
	$input_email->value( $val );
	$input_email->counterMax(150);
	$input_email->arErr($message);
	
	// *************** Admin Inputs **************** //
	$val = isset($_POST['admin_timezone']) ? $_POST['admin_timezone'] : $info['timezone'];
	$admin_tz = new inputField( 'Time Zone', 'admin_timezone' );	
	$admin_tz->type('select');
	$admin_tz->selected($val);
	foreach ($arTZ as $value => $heading )	$admin_tz->option($value, $heading);
		
	$val =  isset($_POST['admin_email']) ?  $_POST['admin_email'] : '';	
	$admin_email = new inputField( 'Email:', 'admin_email' );	
	$admin_email->value( $val );
	$admin_email->counterMax(150);
	$admin_email->arErr($message);
	
	$val =  isset($_POST['admin_username']) ?  $_POST['admin_username'] : '';	
	$admin_username = new inputField( 'Username:', 'admin_username');	
	$admin_username->value($val);
	$admin_username->counterMax(25);
	$admin_username->arErr($message);
	
	$val =  isset($_POST['admin_lname']) ?  $_POST['admin_lname'] : '';	
	$admin_lname = new inputField( 'Last Name:', 'admin_lname');	
	$admin_lname->value($val);
	$admin_lname->counterMax(50);
	$admin_lname->arErr($message);
	
	$val =  isset($_POST['admin_fname']) ?  $_POST['admin_fname'] : '';	
	$admin_fname = new inputField( 'First Name:', 'admin_fname');	
	$admin_fname->value($val);
	$admin_fname->counterMax(50);
	$admin_fname->arErr($message);
	
	

	$admin_pass = new inputField( 'Password:', 'admin_pass' );	
	$admin_pass->type('password');
	$admin_pass->counterMax(75);
	$admin_pass->arErr($message);

	$admin_ver_pass = new inputField( 'Verfiy Password:', 'admin_ver_pass' );	
	$admin_ver_pass->type('password');
	$admin_ver_pass->counterMax(75);
	$admin_ver_pass->arErr($message);	
	
	// ************** Permission Info Inputs *************** //
	
	$val = isset($_POST['permit_add_user']) ?  $_POST['permit_add_user'] : '';	
	$permit_add_user = new inputField('Add users', 'permit_add_user');
	$permit_add_user->type('checkbox');
	$permit_add_user->value( $val );
	$permit_add_user->arErr($message);
	
	// *************** Password Info Inputs **************** //
	$input_username = new inputField( 'Username:', 'username');	
	$input_username->value($info['username']);
	$input_username->counterMax(25);
	$input_username->arErr($message);

	$input_new_pass = new inputField( 'New Password:', 'new_pass' );	
	$input_new_pass->type('password');
	$input_new_pass->counterMax(75);
	$input_new_pass->arErr($message);

	$input_ver_pass = new inputField( 'Verfiy New Password:', 'ver_pass' );	
	$input_ver_pass->type('password');
	$input_ver_pass->counterMax(75);
	$input_ver_pass->arErr($message);
	
	// add flash data to banner
	// modulesAdded only occurs on landing here, no risk of other messages
	$modulesAdded = flash('module_added');
	if(is_array($modulesAdded) and count($modulesAdded))
	{
		$tmp_heading = "Added Modules:";
		$msg = "";
		foreach($modulesAdded as $tmpMod => $dummy)
		{
			$msg .= $tmpMod . ", ";
		}
		$msg = trim($msg, ", ");
		
		$message['banner'] = array (
				'heading' => $tmp_heading, 
				'message' => $msg, 
				'type' => 'success'
			);
	}
	
	?>
    <div class="page_container">
    	<?php echo isset($error) ? $error : ''; ?>
		
		
		
    	<div id=h1><h1>Update Administrative Info</h1></div>
        <div id="info_container">
			
			<?php //--Error Banner-
			// create a banner 
			createBanner($message); 
			
			// create list of tags to open
			if(isset($message['openTag'])) : foreach($message['openTag'] as $tag) :
			?>
			<input class="tagsToOpen" type="hidden" value="<?php echo $tag; ?>" /> 
			<?php
			endforeach; endif;
			
			// general info is only for the super-admin
			if( $info['user_id'] == 1 ) :
			?>
			<!-- General Section -->
			<h2 id="gen-toggle" class="tiptip toggle" title="Update your personal info.">General Info</h2><br />
			<div id="gen-toggle-wrap">
            
				<form id="form_general" class='form'  method="post" action="?info=update">
				<?php
					$input_first_name->createInputField();
					$input_last_name->createInputField();
					$input_company_name->createInputField();
					$input_office_address->createInputField();
					$input_office_city->createInputField();
					$input_office_postal->createInputField();
					$input_office_number->createInputField();
					$input_fax_number->createInputField();		
					$input_cell_number->createInputField();
					$input_email->createInputField();				
					$input_tz->createInputField(); 
				?>
					<div class="clearFix"></div>
					<input id="general_update" type="submit" class='blue button' name="submit" value="Update" />
					<div class="clearFix"></div>
					<hr />
				</form>
			</div>
			<!-- END General Section -->
			<?php endif; ?>
			<!-- Admin Section -->
			<?php 
			// need to be admin & multi-user 
			if ($info['admin'] == 'yes' && $_config['multi_user'] ) :
			?>
			<h2 id="admin-toggle" class="tiptip toggle" title="Create additional Administrators.">Administration</h2><br />
			<div id="admin-toggle-wrap">
				
				<!-- END form controls -->
				<!-- new user form -->
				<form id="form_admin" method="post" class='form' action="?">
					<!-- form controls -->
					<div class="input_wrap">
						<label>Users</label>
						<div class="input_inner">
							<div class="admin_buttons">
								<a id="userFormToggle" class="blue button" href="#">Create</a>
								<a id="deleteUserBtn" class="red button" href="#">Delete</a>
							</div>
							<select id="admin_user" class="no_counter" name="admin_user">
								<option <?php echo isset($_POST['user_id']) && $_POST['user_id'] == 0  ? 'selected="selected"' : '';?> value="0">New Admin</option>
					<?php foreach ($users as $userId => $user) : if ($userId > 1) : ?>
								<option 
									value="<?php echo $userId; ?>"
									<?php echo isset($_POST['admin_user']) && $_POST['admin_user'] == $user['user_id']  ? 'selected="selected"' : '';?>
								>
									<?php echo $user['username']; ?>
								</option>
					<?php endif; endforeach; ?>
							</select>
						</div>
					</div>
					<div class="clearFix"></div>
					
					<div id="userForm">
						<input type="hidden" name="admin" id="admin_form"  
							value="<?php echo isset($_POST['admin']) ? $_POST['admin'] : 'new'; ?>" 
						/>
						<?php
						
						$admin_fname->createInputField();
						$admin_lname->createInputField();
						$admin_username->createInputField();
						$admin_pass->createInputField(); 
						$admin_ver_pass->createInputField();
						$admin_email->createInputField();				
						$admin_tz->createInputField(); 
						?>
						<div class="clearFix"></div>
						<input id="user_btn" type="submit" class='blue button' name="submit" value="Update" />
						<div class="clearFix"></div>
					</div>
				</form>
				<!-- new user form -->
				<hr />
			</div>	
			<?php endif; ?>
			<!-- END Admin Section-->
			
			<!-- Permissions Section -->
			<?php 
			// need to be admin & multi-user 
			if ($info['admin'] == 'yes' && $_config['multi_user'] ) :
				if( ! count($users) ) :
			?>
			<h2 class="tiptip" 
				title="Permissions are only assigned to additional Administrators. You can create some in 'Adminstration' above." 
				style="color:grey;width:90px;"
			>Permissions</h2>
			<?php	
				else :
			?>
			<h2 id="permit-toggle" class="tiptip toggle" title="Assign permissions to Additional Administrators. By default, new Administrators have no Permissions set.">Permissions</h2><br />
			<div id="permit-toggle-wrap">
				<!-- new user form -->
				<form id="form_permissions" method="post" class='form' action="?">
					<input type="hidden" name="permissions">
					
					<!-- form controls -->
					<div class="input_wrap">
						<label>Users</label>
						<div class="input_inner">

							<select id="permit_user" class="no_counter" name="permit_user">
					<?php 
					// determine which user has been selected
					if ( isset($opening_permission_user) ) 	$selected_user = $opening_permission_user;
					elseif(isset($_POST['permit_user'])) 	$selected_user = $_POST['permit_user'];
					else $selected_user = false;
					
					foreach ($users as $userId => $user) : if ($userId > 1) : ?>
								<option value="<?php echo $userId; ?>" 
									<?php echo $selected_user === $user['user_id']  ? 'selected="selected"' : '';?>
								>
									<?php echo $user['username']; ?>
								</option>
					<?php endif; endforeach; ?>
							</select>
						</div>
					</div>
					<div class="clearFix"></div>
					<!-- permissions fun CURRENT-->
					<?php 
					$permit_add_user->createInputField();
					// Build Modules Box
					?>
					<div class="input_wrap">
						<label>Access Modules</label>
						<div class="input_inner">
							<table id="module_table">
								<tbody>
					<?php // build this in sets of three per row
					for ($trCount = 0; $trCount < ceil(count($permitModules) / 3); $trCount++) :			
					?>	
									<tr>
						<?php 
						for ($tdCount = 0; $tdCount < 3; $tdCount++) :
							$curIndex 	= $trCount * 3 + $tdCount;
							if($curIndex < count($permitModules)) :
								// build the data spot
								$curName 	= $permitModules[$curIndex]['module_name'];
								$curPretty	= $permitModules[$curIndex]['module_pretty'];
						?>
										<td>
											<input type="checkbox" class="no_counter" value="1"
												id="permit_<?php echo $curName; ?>";
												name="permit[<?php echo $curName; ?>]"  
						<?php // need more here to be able to switch between users 
							if (isset( $_POST['permit'][$curName]) ) 
												echo ' checked="checked"';
						?>
											/>
											<?php echo $curPretty; ?>
										</td>
						<?php
							else :		// output the blank table data
						?>
										<td>&nbsp;</td>
						<?php
							endif;
						endfor;
						
						?>
								</tr>
					<?php
					endfor;	
					?>
								</tbody>
							</table><!-- END module_table -->
						</div><!-- END input_inner -->
						
					</div><!-- END input_wrap -->
					<!-- END permissions fun -->
					<div class="clearFix"></div>
					<input id="permission_btn" type="submit" class='blue button' name="submit" value="Update" />
					<a id="permitCheck" class="green button" href="#">Check All</a>
					<a id="permitUnCheck" class="green button" href="#">Clear All</a>
					<div class="clearFix"></div>
				</form>
				<hr />
			</div>	
			<?php endif; endif;?>
			<!-- END Permissions Section-->
			

			
			<!-- Admin Password Section -->
			<h2 id="pass-toggle" class="tiptip toggle" title="Update your password."><!-- <img src="images/password.png" /> -->Change Admin Password</h2><br />
			<div id="pass-toggle-wrap">
				<form id="form_password" method="post" action="?password=update">
				<?php
					$input_username->createInputField();
					$input_new_pass->createInputField();
					$input_ver_pass->createInputField();					
				?>
					<div class="clearFix"></div>
					<input id="password_btn" type="submit" class='blue button' name="submit" value="Update"/>
					<div class="clearFix"></div>
				</form>
			</div>
			<!-- END Admin Password Section -->
		</div>
	</div><!-- end page container -->

<?php include("includes/footer.php"); ?>
