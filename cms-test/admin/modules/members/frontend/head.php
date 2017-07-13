<?php
include_once($_config['admin_modules'].'members/includes/functions.php');

// figure out ftp head details
if (uri::get(1) == 'ftp' ) 
{
	// sort data and perform redirects as needed
	$seoData = 'File Sharing';//$arOutput['seoData'];
	// set seo data for use in the main head
	$seot = 'File Sharing';//$seoData['seo_title'];
	$seod = 'File Sharing';//$seoData['seo_description'];
	$seok = 'File Sharing';//$seoData['seo_keywords'];
	$info['no_follow'] = true;

	$options = getMembersOptions();

	if(!$options['ftp_front'])
	{
		if($options['members_front'])	
			header('Location: ' .$_config['path']['members'] );
		else	header('Location: ' . $_config['site_path'] . 'page-not-found');

	}

	if(uri::get('ftp') === 'logout')
	{
			unset($_SESSION['member']);
			unset($_SESSION['loggedInAsMember']);
			unset($_SESSION['ftp_logged_in']);
			unset($_SESSION['ftp_tz_offset']);
			unset($_SESSION['ftp']);
			unset($_SESSION['ftp_folders']);
	}

	if(isset($_POST['username']) && isset($_POST['password']) && isset($_POST['ftp_member_login_full'] ) )
		$login_errors = processLoginForm($_POST['username'], $_POST['password']);
	else $login_errors = array();
	
	$arOutput['login_errors'] = $login_errors;
	$arOutput['req_login'] = !logged_in_as_member();

	$moduleLink = "
	<link rel='stylesheet' href='admin/modules/members/frontend/ftp_front_styles.css' />
	<script>
		$(document).bind('drop dragover', function (e) {
			e.preventDefault();
		});	
	</script>
	";
}
else // do members head
{
	// get options array
	$options = getMembersOptions();
	// has member search been set by flash?
	$flash_srch = flash('member_search_key');
	// we use this a lot, find out what the uri at position 1 is
	$uri1 = uri::get(1);

	// if we are going to need this for tests later: get the data now
	if( ($uri1 == 'create-account' || $uri1 == 'confirm' || $uri1 == 'reset') && $options['online_signup']) 
	{
		$signup_data = getMembersSignupMessage();
	}


	if(isset($_POST['username']) && isset($_POST['password']) && isset($_POST['ftp_member_login_full'] ) )
		$login_errors = processLoginForm($_POST['username'], $_POST['password']);
	else $login_errors = array();

	$req_login = $options['member_req_login'] && !logged_in_as_member();


	if ( $options['members_front'] && $uri1 === false || $uri1 == 'page') {
		$mem_srch_key = false;
		
		if ( array_key_exists('member_search_key', $_POST) && trim($_POST['member_search_key']) != '') $mem_srch_key = trim($_POST['member_search_key']);
		elseif ($flash_srch && uri::get('page') ) $mem_srch_key = trim($flash_srch); // page data isn't passed on first access, prevents navigating to non-member page 
																					//		and coming back with flash data still stored
		if ($mem_srch_key)
		{
			$arOutput = doMembersSearch($mem_srch_key, $options);
		}
		else {
			$arOutput = doMembersHome($options);
		}
	}
	elseif ($uri1 == 'reset' && $options['online_signup'] && $signup_data['status'])
	{
		$arOutput = doMemberReset($options,$signup_data);
	}
	elseif ($uri1 == 'create-account' && $options['online_signup'] && $signup_data['status']) 
	{
		$arOutput = doMemberCreate($options,$signup_data);
	}
	elseif ($uri1 == 'confirm' && $options['online_signup'] && $signup_data['status']) 
	{
		$arOutput = doSignupConfirm($options,$signup_data);
	}
	elseif ($uri1 == 'payment' && $options['pay_signup'] && (!$options['environment'] == 'dev' || $_config['debug'])) 
	{
		$arOutput = doPayment($options);
	}
	elseif ($uri1 !== false && $uri1 != 'create-account' && $uri1 != 'confirm' && $uri1 != 'payment' && $uri1 != 'page' && uri::get(2) === false && $options['members_front']) 
	{
		$arOutput = doMemberPage($uri1, $options);
	}
	//elseif ($options['members_front']) 
	//{
	//	$arOutput = doMembersHome($options);
	//}
	//elseif ($options['ftp_front']) header('Location: ' . $_config['path']['members'] . 'ftp' );
	else	header('Location: ' . $_config['site_path'] . 'page-not-found');

	$arOutput['login_errors'] = $login_errors;
	$arOutput['req_login'] = $req_login;
	// set seo data for use in the main head
	$seot = "Members Area";
	$seod = "Members Area";
	$seok = "Members Area";

	?>
	<!-- members module includes -->
	<?php 
	$moduleLink = '<link rel="stylesheet" type="text/css" href="'.$_config['admin_url'].'modules/'.$module.'/frontend/style.css" />
	<link rel="stylesheet" href="admin/modules/members/frontend/ftp_front_styles.css" />'; 

}


//---------------------------------------------functions-----------------------------------------------------//
 // get main data about the members: from members_home and members_options tables
function doMemberPage($member_url, $options)
{
//	global $_config;
	
	// validate the member page
	$result = getMember($member_url);
	if ( $result === false ) //member doesn't exist, show member homepage instead
	{
		return doMembersHome($options);
	}
	
	// build the member page
	$singleMember = buildMemberPage($result);
	
	return array('memberPag' => false , 'singleMember' => $singleMember, 'memberData' => $result);
}

function doMembersSearch($memberSearchKey = "", $options)
{
	global $_config;
	// set temporary data to carry the search
	flash('member_search_key', $memberSearchKey);

    $memberSearchKey = preg_replace('/\s\s+/', ' ', $memberSearchKey);
	$whereClause = "WHERE `status` >= 1 AND `id` > 0 AND (`first_name` LIKE :memberSearchKey OR `last_name` LIKE :memberSearchKey) ORDER BY `last_name` asc";
	$whereBindings = array(':memberSearchKey' => "%{$memberSearchKey}%");
	
	// count the success
	$result = logged_query("SELECT * FROM `members` {$whereClause}",0,$whereBindings);
	if(is_array($result)) $matchCount = count($result);
	else $matchCount = 0;
		
	$whereClause = "WHERE `status` >= 1 AND `id` > 0 AND (`first_name` LIKE :memberSearchKey OR `last_name` LIKE :memberSearchKey) ORDER BY `last_name` asc";
	$memberPag = new paginationclass('members',$whereClause,3,$_config['site_path'].'members',$options['members_per_pg'],'buildMembersPage',array($_POST), $whereBindings );
	
//var_dump($memberPag->result);	
	$searchMessage = $matchCount ? 
		"Displaying " . count($memberPag->result)  . " of {$matchCount} matches for '" . htmlspecialchars($memberSearchKey) . "'" : 
		"<p>No matches for '" . htmlspecialchars($memberSearchKey) . "'</p>";

	return array('memberPag' => $memberPag, 'searchMessage' => $searchMessage);
}

function doMembersHome($options)
{
	global $_config;
	
	$whereClause = "WHERE `status` >= 1 AND `id` >= 0 ORDER BY `last_name` asc";
	$memberPag = new paginationclass('members',$whereClause,3,$_config['site_path'].'members',$options['members_per_pg'],'buildMembersPage',array($_POST),array());

	// display members homepage description?
	$desc = false;
	$title = $options['title'];
	if ($options['status'] && 
		(	
			!$options['first_page_only'] 
			|| ( ! isset($_POST['part']) || $_POST['part'] == 1 )
		)
	)
	{
		$desc = htmlspecialchars_decode($options['desc']);
	}
	
	$return = array('memberPag' => $memberPag , 'title' => $title, 'desc' => $desc);
	
	if($options['member_req_login'] && !logged_in_as_member()) $return['req_login'] = true;
	
	return $return;
}


function doMemberCreate($options,$signup_data)
{
	global $_config;
	
	$table 		= 'members';			// table form data is inserted into
	$list 		= array();				// form data
	$errors 	= array();				// form errors
	$error_msg 	= false;
	$form_errors = array();
	$success_msg = false;				// obviously, not success message yet
	if(isset($_POST['submit'])){
		
		//validation keys
		$required_input 	= array('first_name', 'last_name', 'mailing_address', 'city', 'country', 'phone_number', 'email','password', 're_pass');
		$multi_check_input 	= array();
		$integer_input 		= array();
		$decimal_input 		= array();
		$email_input		= array('email');

	
		$insertData = $_POST;
		unset($insertData['submit']);
		$objForm = new Form($insertData);
		$objForm->set_required_input($required_input);
		//$objForm->set_multi_check_input($multi_check_input);
		//$objForm->set_integer_input($integer_input);
		//$objForm->set_decimal_input($decimal_input);
		//$objForm->set_binary_input($binary_input);
		$objForm->set_email_input($email_input);
		$objForm->set_password_pair('password', 're_pass', 'alerts');
		$list = $objForm->validate();
		$errors = $objForm->get_errors();
	
		//additional tests
		if (! isset($errors['email']) && ! unique_email_address($list['email']) )
			$errors['email'][] = 'This email is already in use, please select another';
	
		if ( count($errors) )
		{
			foreach($errors as $key => $arErr)
			{
				$error_message = '';
				foreach($arErr as $msg)
				{
					$error_message .= $msg . '<br>';
				}
				$form_errors[$key] = $error_message;
			}
		}
		else // insert
		{
		//***********************************************************************************  DO THIS FIRST !!!!!!  ********************************	
			//TODO	need to make sure member is active (needs expiry date)
			// set password

			$encryptedPassword = hasher($list['password']); // encrypt password

			// set url
			$url = $list['email'];
			$url = preg_replace('/\s+/', ' ', $url); // strip extra whitespace

			$urlArray = explode('@', $url);
			$url = $urlArray[0]; // strip all characters at and after '@' character

			$url = preg_replace('/[\s\W]/', '-', $url); // change any spaces or non-word chars to hyphens
			$url = preg_replace('/-+/', '-', $url); // get rid of consecutive hyphens
			$url = preg_replace('/^-/', '', $url); // get rid of leading hyphen
			$url = preg_replace('/-$/', '', $url); // get rid of trailing hyphen

			$members = logged_query("SELECT * FROM `members` WHERE `id` > 0 ORDER BY `email` ASC",0,array());
			$memberUrls = array();

			// get all member urls
			foreach ($members as $member)
			{
				$memberUrls[$member['url']] = $member;
			}

			$urlCount = 0;
			$tempUrl = $url;

			// check to see if url already exists for another member. If it does, increment counter
			while(array_key_exists($url, $memberUrls))
			{
				$urlCount++;
				$url = $tempUrl . '_' . $urlCount;
			}

			// set password change request
			$pw_change_request = 1;

			// grab the main data for the insert
			$post_data = $list;
			// remove extraneous data
			unset($post_data['re_pass'],$post_data['alerts']);
			// add extra fields
			$post_data['url'] = $url;
			$post_data['password'] = $encryptedPassword;
			
			// set the member as a new member
			$post_data['status'] = 1; // new signup
			
			// insert into table
			$result = logged_array_insert($table, $post_data);

			$saveError = (bool) $result['error'];
		
			if(! $saveError) 
			{	
				// set the success message
				$success_msg = $signup_data['success'];
				
				// email user with confirmation (with or without request for payment)
				if($signup_data['email_notification'])
				{
					// write the notification email
					$from = array('name' => $signup_data['notify_from_name'], 'email' => $signup_data['notify_from']);
					$to = array('name' => '', 'email' => $signup_data['notify_to']); 
					$mailer = new email($from);
					$message = nl2br($signup_data['notify_body']);
		
					// add signup info here
					$member_info = $list;
					unset( $member_info['re_pass'],$member_info['alerts'],$member_info['password']);
					$message .= "<br><table><tr><th colspan='2' style='text-align:left;'>Applicant Information</th></tr>";
					foreach($member_info as $key => $value)
					{
						$key = strtoupper(str_replace('_', ' ', $key));
						$value = htmlspecialchars($value);
						if($value) $message .= "<tr><td>{$key} </td><td> {$value}</td>";
					}
					$message .= "</table>";
					if($signup_data['email_notification'] == 2)
					{
						// append a login link to the bottom of the email
						$message .= "<br><br><p>To Accept or Deny this Membership Request: <a href='{$_config['site_path']}admin'>Login to {$_config['site_path']}admin</a> and go to 'New Signups' in the Members Section</p>";
					}
					else $message .= "<br><br><p>To edit this new member: <a href='{$_config['site_path']}admin'>Login to {$_config['site_path']}admin</a> and go to 'New Signups' in the Members Section</p>";
					$result = $mailer->send($to, $signup_data['notify_title'], $message);
				}
				
				// send the user's confirmation email, as long as notification level isn't 'Approve'
				//   in the case of 'Approve', the site admin will send the confirmation
				if( $signup_data['email_notification'] != 2)
				{
					$result = cleanupConfirmationCodes($options['confirmation_period']);
					if($result === 'bad input') my_log("Members Function: cleanupConfirmationCodes() is receiving bad input. Cleanup did not occur");
					
					// set confirmation code
					$conf_code = rand_string();
					$result = logged_query("UPDATE `members` SET `confirmation_code`=:conf_code WHERE `url`=:post_data_url",0,array(
						":conf_code" =>$conf_code, 
						":post_data_url" => $post_data['url'])
					);
										
					// prepare email
					$from = array('name' => '', 'email' => $options['confirm_from']);
					$to = array('name' => '', 'email' => $post_data['email']); 
					$mailer = new email($from);
					$message = nl2br($options['confirm_body']);
					$message .= "<br><br><p>To finish signing up, go to <a href='{$_config['path']['members']}confirm/user/{$post_data['url']}/code/{$conf_code}'>{$_config['company_name']} Signup Confirmation</a> and enter this Confirmation Code: <span>{$conf_code}</span></p>";
					$result = $mailer->send($to, $options['confirm_title'], $message);
				}
				// sometimes signup page has referrer
				
				
				
/*				// redirect to success page	
				if( $prodID = uri::get('product-enquiry') ) $newTarget = "buyers-conditions/product-enquiry/{$prodID}#enquire";
				elseif(uri::exists('new-listing')) $newTarget = "sellers-conditions/new-listing#new_form";
				else $newTarget = "signup-success#success";

				?>
				<script type="text/javascript">
					window.location.replace("<?php echo $newTarget;?>");
				</script>	
			
<?php 	*/	} else 	{
				$error_msg = "<p style='color:red;'>There was an error saving your information: please try again.<br>If the problem persists, please <a href='mailto:{$_config['forms_email']}'>let us know</a>!</p> ";
			}
		}
	} 
	else //pass the referring page's data with formData 
	{
	//	var_dump($_SERVER["HTTP_REFERER"]);
	}
	
	
	$desc = false;
	if ($signup_data['status'] )
	{
		$desc = $signup_data['desc'];
	}

	return array('createMember'=> true, 'formData' => array(), 'memberPag' => false, 'form_errors' => $form_errors, 'list' => $list, 'error_msg' => $error_msg, 'signup_title' => $signup_data['title'], 'signup_desc' => $desc, 'success_msg' => $success_msg);
}


function doSignupConfirm($options,$signup_data)
{
	global $_config;
	
	$table 		= 'members';			// table form data is updated in
	$list 		= array();				// form data
	$errors 	= array();				// form errors
	$form_errors = array();
	$success_msg = false;				// obviously, not success message yet


	if( !isset($_POST['submit'])){ // not posted, get initial info from link, if available
		$tmpUser = uri::get('user');
		$tmpCode = uri::get('code');
		$list['username'] = '';
		if($tmpUser)
		{
			$result = logged_query("SELECT `email` FROM `members` WHERE `url`=:url",0,array(":url" => $tmpUser));
			if(is_array($result) && count($result)) $list['username'] = $result[0]['email'];
		}
		$list['confirmation_code'] = $tmpCode ? $tmpCode : '';
		$list['password'] = '';
	}
	else  // posted data, use that to populate the list instead!
	{
		$list = $_POST;
		unset($list['submit']);
		
		if(trim($list['username']) == '' || trim($list['password']) == '') 
		{
			$form_errors[] = "Please provide a valid Email Address and Password";
		}
		else // we have all our data, lets check it out
		{
			cleanupConfirmationCodes($options['confirmation_period']);
			$query=logged_query("SELECT `id`, `password`, `confirmation_code`,`url` FROM members WHERE `email` = :username LIMIT 1", 0, array(
				':username' => $list['username']
				)
			);

			if (isset($query[0])) $user=$query[0];
			else	$user = false;
			
			$passwordMatches = $user ? hasher($list['password'], $user['password']) : false;
			// cases:
			// 1) user exists but confirmation is not required
			//	  This account has already been confirmed
			if ($user && !$user['confirmation_code'])
			{
				if(!logged_in_as_member())
					$form_errors[] = "This account is already active<br><a href='{$_config['path']['members']}#content'>Sign In Now</a>";
				else $form_errors[] = "This account is already active and you are currently logged in";
			}
			// 2) user exists and confirmation is blank or wrong
			//	  Incorrect confirmation code: try again or 'click here' to have code emailed to you again
			elseif($user && $user['confirmation_code'] != $list['confirmation_code'] || !$passwordMatches)
			{
				$sendNewConfirm = "<a href='{$_config['path']['members']}reset/user/{$user['url']}'>Click Here</a>";
					$form_errors[] = "Error: try again<br>or<br>{$sendNewConfirm} and we will email you a new confirmation code and temporary password"; 
			}
			// 3) user exists and confirmation is required and confirmation matches
			//	  Success!!
			elseif ($user && $passwordMatches) 
			{ 
					// clear the confirmation code
					userConfirmed($list['username']);
				
					// if the user needs to pay send them to the payment page with a flash message or uri code to trigger confirmation success message
					if(false)
					{}
					else // finished here: do the rest of the final steps
					{
						//finished:
						$success_msg =$signup_data['confirmed'];
						// $member_data is information needed to activate the member
						// currently, only the id is required here. Probably won't need more.
						//   this function is also used in the back end. There, more data may be passed, such as ftp details, membership duration...
						$member_data = array('id' => $user['id']);
						startNewMember($options, $member_data);
					}
				
			}	
			// 4) all other cases
			//	  Failure!! May need to sign up again note
			else
			{
				$timespan = $options["confirmation_period"] == 1 ? $options["confirmation_period"] . ' day' : $options["confirmation_period"] .' days'; 
				$form_errors[] = "Invalid Email, Password or Confirmation Code<br>NOTE: Codes expire {$timespan} after delivery, you may need to <a href='{$_config['path']['members']}create-account#content'>apply again</a>";
			}
		}
	}
	$confirm_title = "Signup Confirmation";
	$confirm_desc = "<h2>Confirm Your Application</h2><p>Complete the form using information from your Confirmation Email.";

	
	return array('signupConfirm'=> true, 'memberPag' => false, 'form_errors' => $form_errors, 'list' => $list, 'confirm_title' => $confirm_title, 'confirm_desc' => $confirm_desc, 'success_msg' => $success_msg);
}

//user has requested a password/confirm reset
function doMemberReset($options,$signup_data)
{
	global $_config;
	// has only gotten this far because online signup is enabled and active
	
	// must have a member's url passed by user/url
	$msg = '';
	$success = false;
	$user_url = uri::get('user');
	
	// clean up the tmp passwords
	cleanupTmpPasswords();

	// if neither the url nor the Post are set: just show the form.
	if(!$user_url && (!isset($_POST['username']) || !trim($_POST['username'])))
		return array('requestReset'=> true, 'memberPag' => false, 'msg' => $msg, 'success' => $success);
	
	// if an email address has been posted, use it instead of the url
	if(isset($_POST['username']))
	{
		$user = getMemberByEmail($_POST['username']);
	}
	else
	{
		$user = getMember($user_url);
	}
	
	if(is_array($user))
	{
		// whoa Nelly! Don't do it if we already have a tmp_password (they only last ten minutes)
		if( $user['tmp_password'])
		{
			$msg = "<p>A reset email has recently been sent to this address. Be sure to check your spam folder. You can request another reset in a few minutes.</p>";
		}
		// must be valid url
		else
		{
			 
			
			// create tmp_password && (if confirm code presesnt) confirm_code
			$newPassword = rand_string();
			$newConfirm = $user['confirmation_code'] ? rand_string() : '';
			// encrypt password
			$encryptedPassword = hasher($newPassword); 
			
			// update member record in db
			$result = logged_query("UPDATE `members` SET `confirmation_code`=:newConfirm, `tmp_password`=:encryptedPassword, `tmp_password_date`=NOW() WHERE `url`=:url ",0,array(
				":newConfirm" => $newConfirm,
				":encryptedPassword" => $encryptedPassword,
				":url" => $user['url']
			));
			if(!$result) 
			{	
				$msg = "<p>There was an error setting your new Password and/or Confirmation Code. Try again later</p>";
			}
			// email details to user
			else
			{
				$user_fullname = $user['first_name'] . ' ' . $user['last_name'];
			  	$from = array('name' => $signup_data['signup_from_name'], 'email' => $signup_data['signup_from']);

			//	$to = array('name' => $user_fullname, 'email' => $user['email']); 
				$to = array('name' => $user_fullname, 'email' => $user['email']); 
				$mailer = new email($from);
				if($user['confirmation_code'])
				{	
					
					$message = "<p>Hello {$user_fullname},</p><p>We have received a request to reset your Confirmation Code and provide you with a Temporary Password so you can complete the Signup Process to become a member.</p><p>To Confirm your Membership, provide the following information at <a href='{$_config['path']['members']}confirm/user/{$user['url']}/code/{$newConfirm}'>{$_config['company_name']} Signup Confirmation</a></p><table><tr><td>Confirmation Code</td><td>{$newConfirm}</td></tr><tr><td>Temporary Password</td><td>{$newPassword}</td></tr></table><br><p><em>Note:</em>For security purposes, the temporary password will expire in a few minutes. Be sure to sign in and change your password after confirming your account</p>";
					
					
					$msg = "<p>New Confirmation Code and Temporary Password have been sent to the Email Address provided so you can complete the Signup Process.</p><p>To Confirm your Membership, follow the link provided in the email and enter your new information in the spaces provided.<p><em>Note:</em>For security purposes, the temporary password will expire in a few minutes. Be sure to sign in and change your password after confirming your account</p>";
				}	
				else // user has requested a temporary password
				{
					$message = "<p>Hello {$user_fullname},</p><p>We have received a request to provide you with a Temporary Password so you can log in to your Account.<br>The Temporary Password provided below will become invalid in a few minutes or after your next Login, so be sure to change your Password. You can Log In at <a href='{$_config['path']['members']}'>{$_config['company_name']} Login</a></p><table><tr><td>Temporary Password</td><td>{$newPassword}</td></tr></table><br><p><em>Note:</em>If you did not make this request, you don't need to do anything. Your password will not be changed and the Temporary Password will expire in a few minutes.</p>";
					
					$msg = "<p>A Temporary Password has been sent to the Email Address provided.</p><p><em>Note:</em>For security purposes, the temporary password will expire in a few minutes. Be sure to sign in and change your password once the email arrives. Don't forget to check your spam folder if it doesn't arrive immediately</p>";
				}
					$result = $mailer->send($to, $_config['company_name'] . " Password Reset", $message);
					
					if(!$result) $msg = "There was an email delivery error, we were unable to send the request to the email address provided. Please try again or contact <a href='mailto:{$signup_data['signup_from']}'>{$signup_data['signup_from_name']}</a> for assistance.";
					
					else $success = true;
			}
			
			
		}
		
	}	
	// if failed at any point, write error message
	$msg = $msg ? $msg : "<p>No valid user selected please try again</p>";
	
	return array('requestReset'=> true, 'memberPag' => false, 'msg' => $msg, 'success' => $success);
}

// work in progress
function doPayment($options)
{
	$form_errors = array();
	$list = array();
	$success_msg = false;
	
	
	$paypal_button = buildPaymentButton($options);
	

	return array('paymentPage'=>true, 'paypal_button' => $paypal_button, 'memberPag' =>false, 'form_errors' => $form_errors, 'list' => $list, 'success_msg' => $success_msg);
}

// work in progress
function buildPaymentButton($options)
{
	global $_config;
	$notEnabledMsg = "<p>We are not currently accepting payments, please try again later</p>";
	
    if(!$options['paypal_on']) return $notEnabledMsg;
	if($options['environment'] == 'dev')
	{
		if(!check_email_address($options['dev_paypal']) || !check_email_address($options['dev_orderemail'])) return $notEnabledMsg; 
		
		$paypalEmail = $options['dev_paypal'];
		$form_action = "https://www.sandbox.paypal.com/cgi-bin/webscr";
	}
	else 
	{
		if(!check_email_address($options['live_paypal']) || !check_email_address($options['live_orderemail'])) return $notEnabledMsg; 
		
		$paypalEmail = $options['live_paypal'];
		$form_action = "https://www.paypal.com/cgi-bin/webscr";
	}
	
//	$paypalEmail = 'golfbeyondtheswing@gmail.com';
    $itemName = 'Membership Payment';
    $memberEmail = 'rosler19@hotmail.com';
	
	
	//isset($_GET['email']) ? urldecode(mysql_real_escape_string($_GET['email'])) : '';
    $membershipCost = 19.95;
    $notifyURL = $_config['admin_url'] . 'modules/members/paypal/listener.php';
    $successURL = $_config['path']['members']."payment";
    $cancelURL = $_config['path']['members'] . "payment";
	
	ob_start();
		

		
	if($options['environment'] == 'dev') echo "<h2><em>WARNING:</em> Development Mode, purchases will not go through. For testing purposes only.</h2>";	

	?>
		
		<form id="payment-form" action="<?php echo $form_action; ?>" method="post" name="paypal">
		<input type="hidden" name="cmd" value="_xclick">
		<input type="hidden" name="business" value="<?php echo $paypalEmail; ?>">
		<input type="hidden" name="lc" value="CA">
		<input type="hidden" name="item_name" value="<?php echo $itemName; ?>">
		<input type="hidden" name="custom" value="<?php echo $memberEmail; ?>">
		<input type="hidden" name="amount" value="<?php echo $membershipCost; ?>">
		<input type="hidden" name="notify_url" value="<?php echo $notifyURL; ?>">
		<input type="hidden" name="currency_code" value="CAD">
		<input type="hidden" name="button_subtype" value="services">
		<input type="hidden" name="no_note" value="1">
		<input type="hidden" name="no_shipping" value="1">
		<input type="hidden" name="rm" value="1">
		<input type="hidden" name="return" value="<?php echo $successURL; ?>">
		<input type="hidden" name="cancel_return" value="<?php echo $cancelURL; ?>">
		<input type="hidden" name="tax_rate" value="0.000">
		<input type="hidden" name="shipping" value="0.00">
		<input type="hidden" name="bn" value="PP-BuyNowBF:btn_paynowCC_LG.gif:NonHosted">
		<input type="image" src="https://www.paypal.com/en_US/i/btn/btn_paynowCC_LG.gif" border="0" name="submit" alt="PayPal - The safer, easier way to pay online!">
		<img alt="" border="0" src="https://www.paypal.com/en_US/i/scr/pixel.gif" width="1" height="1">
	</form>
	<?php

	$button = ob_get_contents();
	ob_end_clean();	
	return $button;
}


function buildMemberPage( $result )
{
	global $_config;
    
    if($result['member_image'] != NULL) {
        $memberImage = $result['id'] . "_m_" . $result['member_image'] . ".jpg";
    }
    else {
        $memberImage = 'default_m.jpg';
    }

    $memberImageUrl = $_config['site_path'] . "/admin/modules/members/images/memberimages/" . $memberImage;

	$return ="
		<a class='members_return' href='{$_config['path']['members']}members'>Return to Members</a>
		<div class='membercontent'>
            <p id=\"member-image\"><img src=\"".$memberImageUrl."\" alt=\"Member Image\" /></p>
            <h1>".$result['first_name']."&nbsp; ".$result['last_name']."</h1>
            <p><strong>Phone Number:</strong> ".$result['phone_number']."</p>
            <p><strong>Email: </strong><a href=\"mailto:".$result['email']."\">".$result['email']."</a></p>";	
	if(isset($result['bio']) && $result['bio']) 
	{
	$return .= "
            <h2 id=\"bio-header\">Biography</h2>
			<p>".htmlspecialchars_decode($result['bio'])."</p>";
	}
	$return .="
            <div style='clear:both'></div>
		</div>
	";	
	return $return;
}



function cleanupConfirmationCodes($days_old = 3)
{
	if (!is_web_int($days_old) || $days_old < 1 || $days_old > 30) return 'bad input';
	$days_old = (int) $days_old;

	return logged_query("DELETE FROM `members` WHERE `confirmation_code`<>'' AND `creation_date` < NOW() - interval :days_old day",0,array(":days_old" =>  $days_old));
}


function buildMembersPage( $result, $source )
{	
	global $_config; 
?>
		<a class="memberTitle" href="<?php echo $_config['path']['members'].$result['url']; ?>"><?php echo $result['last_name'] . ', ' . $result['first_name']; ?></a>	
<?php
}
