<?php /* THIS SITE'S MEMBER MODULE MODIFIED TO WORK IN TANGENT WITH FTP MODULE */
// initialize the page
$headerComponents = array();
$headerModule = 'members';
include('../../includes/headerClass.php');
include('includes/functions.php');

$pageInit = new headerClass($headerComponents,$headerModule);

$baseUrl = $_config['admin_url'];

$memberPreferences = getMembersOptions();

$hasFTP = $memberPreferences['ftp_front']; //if this site has the ftp module



$newMember = false;
$memberImageString = NULL;
$folders = get_valid_folders();



if ( array_key_exists('option', $_GET) && ($_GET['option'] == 'create') ) 
{
	$newMember = true;

    // set default membership expiry date for new members (end of current year)
    $currYear = date('Y');
    $membership_expiry = $currYear . '-12-31 23:59:59'; // end of current year
	
	$result = logged_query('
		SELECT MAX( CAST( SUBSTRING( `email` , 5 ) AS Unsigned ) ) AS num
		FROM `members`
		WHERE `id` >0
		AND `email` REGEXP "^new_[0-9]*$"',0,array()
	);
	$slug_num = is_array($result) && count($result) ? (int)$result[0]['num'] : 0;
	$tmp_slug = 'new_' . ++$slug_num;
	$result = logged_query("
		INSERT INTO `members` 
		SET email=:tmp_slug, `membership_expiry`=:membership_expiry
		",0,array(
			":tmp_slug" => $tmp_slug, 
			":membership_expiry" => $membership_expiry
		)
	);
	$member_id = $_config['db']->getLastInsertId();
	$list = logged_query("SELECT * FROM `members` WHERE `id` =:member_id",0,array(":member_id" => $member_id));
	$list = $list[0];
}

// if this is an edit of an existing member, get the member info
elseif (array_key_exists('memberid', $_GET) && is_numeric($_GET['memberid']) && ! array_key_exists('option', $_GET) ) {	
	$list = logged_query("SELECT * FROM members WHERE id =:id ORDER BY id DESC LIMIT 1;",0,array(":id" => $_GET['memberid']) ); 
	if ($list && count($list)) $list = $list[0];
	
	$memberImageString = $list['member_image'];
    $membership_expiry = $list['membership_expiry'];
    $unpaid_signup = $list['unpaid_signup'];
	$member_id = $_GET['memberid'];
	
	$valid_folders = get_valid_folders($_GET["memberid"]);
	
	$tmpfolders= $valid_folders;
	$list['ftp_folder'] = array();
	
	// currently, users permissions apply to all branches, 
		//		eventually individual branches will be selectable
		// since all folders have the same permission, 
		//		use this to find out the current permission
	$list['restriction'] = 'none';
	foreach($tmpfolders as $key => $value)
	{
		$list['ftp_folder'][$key] = $value['folder'];
		$list['restriction'] = $value['restriction'];
	
	}	
}
// if we aren't editing or creating either, we shouldn't be here
elseif (! array_key_exists('option', $_GET) ) 
{
	header( "Location: " . $_config['admin_url'] . "modules/members/members.php" );
	exit;
}	


#==================
# process member info
#==================
if(isset($_POST['submit-member'])) {

    $errorMsgType = 'errorMsg';
	$errorType = 'error';
	$errorHeading = 'Error';
	$errorMessage = 'all required fields must be complete, saved as Inactive';

	$list = array();

	foreach($_POST as $k=>$v)
	{
			if($k == 'ftp_folder') 
			{
				$permitted_folders = array();
				$justfolders = array();
				foreach($folders as $key => $folder_data)
					$justfolders[$key]=$folder_data['folder'];
				foreach($v as $tmp_folder => $dummy)
				{
					if($key = array_search($tmp_folder, $justfolders))
					{
						$permitted_folders[$key] = $tmp_folder;
					}
				}
				${$k} = $permitted_folders;
				$list[$k] = $permitted_folders;
			}
			else 
			{
				${$k} = trim(htmlspecialchars($v, ENT_QUOTES));
				$list[$k] = $v;
			}
			if (${$k} == '' && $k == 'email') {$message['inline'][$k] = array ('type' => $errorMsgType, 'msg' => 'Required field');}
			if ($k == 'restriction' && $restriction != 'none' && $restriction != 'write only' && $restriction != 'read only'  ) {$message['inline'][$k] = array ('type' => $errorMsgType, 'msg' => 'Invalid Value');}
	}
	if(! isset($_POST['ftp_folder'])) $list['ftp_folder'] = array();

	$memberImageString = $list['member_image_string'];

    // set checkbox data for unchecked boxes
    if(!isset($_POST['pw_change_request'])) $list['pw_change_request'] = $pw_change_request = 0;
    if(!isset($_POST['eBulletin'])) $list['eBulletin'] = $eBulletin = 0;
	if(!isset($_POST['ftp_access'])) $list['ftp_access'] = $ftp_access = 0;
    if(!isset($_POST['list_name'])) $list['list_name'] = $list_name = 0;
    if(!isset($_POST['list_address'])) $list['list_address'] = $list_address = 0;
    if(!isset($_POST['list_phone'])) $list['list_phone'] = $list_phone = 0;
    if(!isset($_POST['list_email'])) $list['list_email'] = $list_email = 0;
	if(! isset($_POST['ftp_folder'])) $list['ftp_folder'] = array();

    // set unpaid_signup value to '0' if administrator manually pays for a newly signed up yet unpaid member
    if(isset($_POST['payment_status']) && $_POST['payment_status'] == 1 && isset($_POST['unpaid_signup']) && $_POST['unpaid_signup'] == 1) {
        $list['unpaid_signup'] = $unpaid_signup = 0;
    }

    // set url
    $url = trim($email);
    $url = preg_replace('/\s+/', ' ', $url); // strip extra whitespace
    
    $urlArray = explode('@', $url);
    $url = $urlArray[0]; // strip all characters at and after '@' character

    $url = preg_replace('/[\s\W]/', '-', $url); // change any spaces or non-word chars to hyphens
    $url = preg_replace('/-+/', '-', $url); // get rid of consecutive hyphens
    $url = preg_replace('/^-/', '', $url); // get rid of leading hyphen
    $url = preg_replace('/-$/', '', $url); // get rid of trailing hyphen

    $members = getMembersData();
    $memberUrls = array();

    // get all member urls
    foreach ($members as $member)
    {
        $memberUrls[$member['url']] = $member;
    }

    $urlCount = 0;
    $tempUrl = $url;

    // check to see if url already exists for another member. If it does, increment counter
    while(array_key_exists($url, $memberUrls) && ($_GET['option'] == 'create' || $memberUrls[$url]['id'] != $member_id))
	{
        $urlCount++;
        $url = $tempUrl . '_' . $urlCount;
    }
    
    // set email(username)
    $email = trim($email);
    $emailLower = strtolower($email);

    reset($members);
    
    $memberEmails = array();

    // get all member emails
    foreach ($members as $member)
    {
        $memberEmails[strtolower($member['email'])] = $member;
    }

    // check to see if email already exists for another member.
    if(array_key_exists($emailLower, $memberEmails) && ($_GET['option'] == 'create' || $memberEmails[$emailLower]['id'] != $member_id))
	{
	    $errorMessage = 'That email already exists, saved as Inactive';
        $message['inline']['email'] = array ('type' => $errorMsgType, 'msg' => 'Must be unique');
    }

	// if an error was found, create the error banner
	if ($errorsExist = count(isset($message['inline']) ? $message['inline'] : array()))
	{
		$message['banner'] = array ('heading' => $errorHeading, 'message' => $errorMessage, 'type' => $errorType);
		$status = $list['status'] = 0; // saving as Inactive
	}
	else // set the success message
	{
		$message['banner'] = array ('heading' => 'Successfully Saved', 'type' => 'success');
	}

	// save even if errors exist: but save as draft
	if (array_key_exists('option', $_GET) && ($_GET['option'] == 'create'))
	{
        $password = hasher($password); // encrypt password

		$result = logged_query("INSERT INTO `members` (`url`, `first_name`, `last_name`,`bio`, `phone_number`, `mailing_address`, `city`, `province_state_region`, `country`, `postal_code`,  `email`, `password`, `status`, `ftp_access`, `level`, `payment_status`, `pw_change_request`, `membership_expiry`, `eBulletin`, `list_name`, `list_address`, `list_phone`, `list_email`, `creation_date`) 
VALUES (:url, :first_name, :last_name, :bio, :phone_number, :mailing_address, :city, :province_state_region, :country, :postal_code, :email, :password, :status, :ftp_access, :level, :payment_status, :pw_change_request, :membership_expiry, :eBulletin, :list_name, :list_address, :list_phone, :list_email, NOW());",0,array(
			':url'=> $url,
			':first_name'=> $first_name,
			':last_name'=> $last_name,
			':bio'=> $bio,
			':phone_number'=> $phone_number,
			':mailing_address'=> $mailing_address,
			':city'=> $city,
			':province_state_region'=> $province_state_region,
			':country'=> $country,
			':postal_code'=> $postal_code,
			':email'=> $email,
			':password' => $password,
			':status'=> $status,
			':ftp_access'=> $ftp_access,
			':level'=> $level,
			':payment_status'=> $payment_status,
			':pw_change_request'=> $pw_change_request,
			':membership_expiry'=> $membership_expiry,
			':eBulletin'=> $eBulletin,
			':list_name'=> $list_name,
			':list_address'=> $list_address,
			':list_phone'=> $list_phone,
			':list_email'=> $list_email		  
		)); 

		
			
		$saveError = $result === false;
		
		// save valid folders for user:
		if ($saveError || (isset( $list['ftp_folder']) && ! set_valid_folders($member_id, $list['ftp_folder'], $list['restriction']) ) ) $saveError = true;
			
		// banners: if there was an error, overwrite the previously set success message
		if ($saveError)	$message['banner'] = array ('heading' => 'Error Saving Member', 'message' => 'there was an error writing to the database', 'type' => 'error' );
		else
		{
			$member_id = $_config['db']->getLastInsertId();




		
			if(is_uploaded_file($_FILES['member_image']['tmp_name'])) {
				// upload member image given
				if(!(memberimageupload($_FILES, $member_id, $memberImageString))) {
					// image upload failed   
					if($saveError || $errorsExist) {
						$message['banner']['message'] .= '. Member image is wrong file type';
					}
					else {
						$message['banner'] = array ('heading' => 'Error Saving Member Image', 'message' => 'Wrong file type', 'type' => 'error' );
					}  
				}
			}

			// successfully created member: no longer a new page!
			$newMember = false;
		}
	}
	elseif (array_key_exists('option', $_GET) && ($_GET['option'] == 'edit'))
	{
		if($hasFTP) {

		}

		$result = logged_query("UPDATE `members` SET
        `url` = :url, 
		`first_name` = :first_name, 
		`last_name` = :last_name,
		`bio` = :bio,
		`phone_number` = :phone_number,
        `mailing_address` = :mailing_address,
        `city` = :city,
        `province_state_region` = :province_state_region,
        `country` = :country,
        `postal_code` = :postal_code, 
		`email` = :email,
		`status` = :status,
		`ftp_access` = :ftp_access,
        `level` = :level,
        `payment_status` = :payment_status,
        `pw_change_request` = :pw_change_request,
        `unpaid_signup` = :unpaid_signup,
        `eBulletin` = :eBulletin,
        `list_name` = :list_name,
        `list_address` = :list_address,
        `list_phone` = :list_phone,
        `list_email` = :list_email
         WHERE `id` = :member_id LIMIT 1;",0,array(
			':url'=> $url,
			':first_name'=> $first_name,
			':last_name'=> $last_name,
			':bio'=> $bio,
			':phone_number'=> $phone_number,
			':mailing_address'=> $mailing_address,
			':city'=> $city,
			':province_state_region'=> $province_state_region,
			':country'=> $country,
			':postal_code'=> $postal_code,
			':email'=> $email,
			':status'=> $status,
			':ftp_access'=> $ftp_access,
			':level'=> $level,
			':payment_status'=> $payment_status,
			':pw_change_request'=> $pw_change_request,
			':unpaid_signup'=> $unpaid_signup,
			':eBulletin'=> $eBulletin,
			':list_name'=> $list_name,
			':list_address'=> $list_address,
			':list_phone'=> $list_phone,
			':list_email'=> $list_email,
			':member_id' => $member_id
		 ));
		
		$saveError = false;
		// check result: # means number of rows changed (0 means no rows changed)
		// 				false means an error occured
		if($result === false) $saveError = true;
		
		// save valid folders for user:
		if ($saveError || (isset( $list['ftp_folder']) && isset($list['restriction']) && ! set_valid_folders($member_id, $list['ftp_folder'], $list['restriction']) ) ) $saveError = true;
			
		// banners
		// banners: if there was an error, overwrite the previously set success message
		if ($saveError)	$message['banner'] = array ('heading' => 'Error Saving Member', 'message' => 'there was an error writing to the database', 'type' => 'error' );
        
        if(isset($_FILES['member_image']['tmp_name']) && is_uploaded_file($_FILES['member_image']['tmp_name'])) {
            // upload member image given

            if(!(memberimageupload($_FILES, $member_id, $memberImageString))) {
                // image upload failed   
                if($saveError || $errorsExist) {
                    $message['banner']['message'] .= ' and member image is wrong file type';
                }
                else {
		            $message['banner'] = array ('heading' => 'Error Saving Member Image', 'message' => 'Wrong file type', 'type' => 'error' );
                }  
            }
        }
	}
}	

if (! isset($message)) $message=array();

// first name
$input_first_name = new inputField('First Name', 'first_name');
$input_first_name->toolTip('First Name (optional)');
$input_first_name->value(htmlspecialchars_decode(isset($list['first_name']) ? $list['first_name'] : '', ENT_QUOTES));
$input_first_name->counterMax(80);
$input_first_name->size('small');
$input_first_name->arErr($message);

// last name
$input_last_name = new inputField('Last Name', 'last_name');
$input_last_name->toolTip('Last Name (optional)');
$input_last_name->value(htmlspecialchars_decode(isset($list['last_name']) ? $list['last_name'] : '', ENT_QUOTES));
$input_last_name->counterMax(80);
$input_last_name->size('small');
$input_last_name->arErr($message);

// phone number
$input_phone_number = new inputField('Phone Number', 'phone_number');
$input_phone_number->toolTip('Member phone number (optional)');
$input_phone_number->value(htmlspecialchars_decode(isset($list['phone_number']) ? $list['phone_number'] : '', ENT_QUOTES));
$input_phone_number->counterMax(30);
$input_phone_number->size('small');
$input_phone_number->arErr($message);

// mailing address
$input_mailing_address = new inputField('Mailing Address', 'mailing_address');
$input_mailing_address->toolTip('Member mailing address (optional)');
$input_mailing_address->value(htmlspecialchars_decode(isset($list['mailing_address']) ? $list['mailing_address'] : '', ENT_QUOTES));
$input_mailing_address->counterMax(175);
$input_mailing_address->size('small');
$input_mailing_address->arErr($message);

// city
$input_city = new inputField('City', 'city');
$input_city->toolTip('Member city (optional)');
$input_city->value(htmlspecialchars_decode(isset($list['city']) ? $list['city'] : '', ENT_QUOTES));
$input_city->counterMax(50);
$input_city->size('small');
$input_city->arErr($message);

// province/state/region 
$input_province_state_region = new inputField('Province/State/Region', 'province_state_region');
$input_province_state_region->toolTip('Member province/State/Region (optional)');
$input_province_state_region->value(htmlspecialchars_decode(isset($list['province_state_region']) ? $list['province_state_region'] : '', ENT_QUOTES));
$input_province_state_region->counterMax(50);
$input_province_state_region->size('small');
$input_province_state_region->arErr($message);

// country
$input_country = new inputField('Country', 'country');
$input_country->toolTip('Member country (optional)');
$input_country->value(htmlspecialchars_decode(isset($list['country']) ? $list['country'] : '', ENT_QUOTES));
$input_country->counterMax(50);
$input_country->size('small');
$input_country->arErr($message);

// postal code
$input_postal_code = new inputField('Postal Code', 'postal_code');
$input_postal_code->toolTip('Member postal code (optional)');
$input_postal_code->value(htmlspecialchars_decode(isset($list['postal_code']) ? $list['postal_code'] : '', ENT_QUOTES));
$input_postal_code->counterMax(10);
$input_postal_code->size('small');
$input_postal_code->arErr($message);

// email
$input_email = new inputField('Email (Username)', 'email');
$input_email->toolTip('Email used as username for login, and must be unique (required)');
$input_email->value(htmlspecialchars_decode(isset($list['email']) ? $list['email'] : '', ENT_QUOTES));
$input_email->counterMax(75);
$input_email->size('small');
$input_email->arErr($message);

// password	
$input_password = new inputField('Password', 'password');
$input_password->type('password');
$input_password->toolTip('Member password');
$input_password->value("");
$input_password->counterMax(75);
$input_password->size('small');
$input_password->arErr($message);

// Confirm password	
$input_cfm_password = new inputField('Confirm Password', 'cfm_password');
$input_cfm_password->type('password');
$input_cfm_password->toolTip('Confirm Member password');
$input_cfm_password->value("");
$input_cfm_password->counterMax(75);
$input_cfm_password->size('small');
$input_cfm_password->arErr($message);

// member status
$input_status = new inputField( 'Member Status', 'status' );	
$input_status->toolTip('An Inactive member will not show in the frontend member roster, and will not be able to login.');
$input_status->type('select');
$input_status->selected(isset($list['status']) ? $list['status'] : '');
$input_status->option( 0, 'Inactive' );
$input_status->option( 1, 'New' );
$input_status->option( 2, 'Regular' );
$input_status->option( 3, 'Complimentary' );
$input_status->option( 4, 'Honorary' );
$input_status->option( 5, 'Sponsored' );
$input_status->arErr($message);

// member level
$input_level = new inputField( 'Member Level', 'level' );	
$input_level->toolTip('Member level to be used, if levels have meaning.');
$input_level->type('select');
$input_level->selected(isset($list['level']) ? $list['level'] : '');
$input_level->option( 0, 'Standard' );

// payment status
$input_payment_status = new inputField( 'Payment Status', 'payment_status' );	
$input_payment_status->toolTip('Member payment status. Unpaid members cannot login');
$input_payment_status->type('select');
$input_payment_status->selected(isset($list['payment_status']) ? $list['payment_status'] : '');
$input_payment_status->option( 0, 'Unpaid' );
$input_payment_status->option( 1, 'Paid' );

// password change request
$input_pw_change_request = new inputField( 'Request PW Change', 'pw_change_request' );	
$input_pw_change_request->toolTip('Flag member for password change, requested upon frontend signin by member.');
$input_pw_change_request->type('checkbox');
$input_pw_change_request->value(isset($list['pw_change_request']) ? $list['pw_change_request'] : '0');

// eBulletin (receive)
$input_eBulletin = new inputField( 'Receive eBulletin', 'eBulletin' );	
$input_eBulletin->toolTip('Flag member to receive the eBulletin.');
$input_eBulletin->type('checkbox');
$input_eBulletin->value(isset($list['eBulletin']) ? $list['eBulletin'] : '0');

// create FTP member?
$input_ftp_access = new inputField( 'FTP Access', 'ftp_access' );
$input_ftp_access->toolTip('Will this member have FTP access?');
$input_ftp_access->type('checkbox');
$input_ftp_access->value(isset($list['ftp_access']) ? $list['ftp_access'] : '0');

// list name (in member list)
$input_list_name = new inputField( 'List Name', 'list_name' );	
$input_list_name->toolTip('Flag member to display name in member list.');
$input_list_name->type('checkbox');
$input_list_name->value(isset($list['list_name']) ? $list['list_name'] : '0');

// list address (in member list)
$input_list_address = new inputField( 'List Address', 'list_address' );	
$input_list_address->toolTip('Flag member to display address in member list.');
$input_list_address->type('checkbox');
$input_list_address->value(isset($list['list_address']) ? $list['list_address'] : '0');

// list phone (in member list)
$input_list_phone = new inputField( 'List Phone Number', 'list_phone' );	
$input_list_phone->toolTip('Flag member to display phone number in member list.');
$input_list_phone->type('checkbox');
$input_list_phone->value(isset($list['list_phone']) ? $list['list_phone'] : '0');

// list email (in member list)
$input_list_email = new inputField( 'List Email', 'list_email' );	
$input_list_email->toolTip('Flag member to display email in member list.');
$input_list_email->type('checkbox');
$input_list_email->value(isset($list['list_email']) ? $list['list_email'] : '0');

// Permissions
$input_restriction = new inputField('Permissions', 'restriction');
$input_restriction->toolTip('Unrestricted: User can upload and download<br>Read Only: Member can only download<br>Write Only: User can only upload');
$input_restriction->type('select');
$input_restriction->selected(isset($list['restriction']) ? $list['restriction'] : 'none');
$input_restriction->option( 'none', 'Unrestricted' );
$input_restriction->option( 'read only', 'Read Only' );
$input_restriction->option( 'write only', 'Write Only' );
$input_restriction->arErr($message);
?>

<?php
$pageResources ="
<link rel=\"stylesheet\" type=\"text/css\" href=\"".$_config['admin_url']."modules/members/style.css\" />
<script type=\"text/javascript\" src=\"".$_config['admin_url']."modules/members/js/members_new.js\"></script>
";
$pageInit->createPageTop($pageResources);

 ?>
 <div id="member_new_page_container" class="page_container">
    <div id="h1">
        <?php if($newMember) {
            echo '<h1>Add Member</h1>';
        } else {
            echo '<h1>Edit Member</h1>';
        } ?>
    </div>
    <div id="info_container">
		<?php 
		// ----------------------------------------subnav--------------------------------
		$selectedMembers = 'tabSel';
		$selectedOpts = '';
		$selectedCats = '';
		$selectedHome = '';
		include("includes/subnav.php"); 
		echo '<hr />';
		//---------------------------------------Error Banner----------------------------- 
		// create a banner if $message['banner'] exists
            echo '<div id="new_members_banner">';
            createBanner($message); 
            echo '</div>';

		$parms = "?memberid={$member_id}&option=edit";
		?>
		<form action="members_new.php<?php echo $parms; ?>" method="post" enctype="multipart/form-data" name="addmember" id="addmember" class="form">
            <input type="hidden" name="member_id" id="member_id" value="<?php echo isset($member_id) ? $member_id : ''; ?>" /> 
			<!--  properties_wrap -->
			<h2 id="prop-toggle" class="tiptip toggle" title="Information about the member.">Member Properties</h2><br />
			<div id="prop-toggle-wrap">
			<?php
			
			if($memberPreferences['members_front'])
			{




                if(!$newMember && $memberImageString != NULL) {

                    $memberImageName = $member_id . '_s_' . $memberImageString . '.jpg'; 
                    $fileInputText = 'Click to select a different image'; 
                    $hasProfileImage = true;
                }
                else {
                    // default member image
                    $memberImageName = 'default_s.jpg'; 
                    $fileInputText = 'Click to select a new image'; 
                    $hasProfileImage = false;
                }
               
                echo '<div id="profile-image-container">';
                
                if($hasProfileImage) {
                    // display ability to delete image
                    echo '<a href="members_new.php" class="tipTop deletememberimage" title="Delete Image" rel="'.$member_id.'">';
                    echo '<img src="'.$_config['admin_url'].'modules/members/images/delete.png" alt="Delete">';
                    echo '</a>';
                }

                echo '<img id="profile-image" src="'.$_config['admin_url'].'modules/members/images/memberimages/'.$memberImageName.'" alt="member image" />'."\n";
                // display image upload box
                if($hasProfileImage) {
                    // display toggle wrap for file input box
                    echo '<div id="togglememberimage" class="toggle"><a class="addForm"><img src="'.$_config['admin_url'].'modules/members/images/img-icon16.jpg" alt="image icon" />Change Member Image</a></div>';
                    echo '<div id="togglememberimage-wrap">';
                }
                else {
                    echo "<label class=\"tipRight\" title=\"Member's profile image (optional).\">Member Image</label>";
                }
                
                echo '<div id="file-input-container">';
                
                if (using_ie()) {
                    echo '<input id="member_image_ie" name="member_image" type="file" />';
                } 
                else {
                    echo '<input id="member_image" name="member_image" type="file" />';
                    echo '<div class="fakefile"><input type="text" Value="'.$fileInputText.'" /></div>';
                }

                echo '</div><!-- END file-input-container -->';

                if($hasProfileImage) {
                    echo '</div><!-- END togglememberimage-wrap-->';
                }

                echo '</div><!-- END profile-image-container -->';  
			}



                
				// membership expiry date
			if($memberPreferences['renewal_req'])
			{



                // convert membership expiry date to timestamp
                if($membership_expiry instanceof DateTime) {
                    $membership_expiry_TS = $membership_expiry->getTimestamp();
                } else {
                    $membership_expiry_TS = strtotime($membership_expiry);
                }

                // determine membership expiry status
                $expired = false;
                $renewable = true;
                $expirySpanClass = '';
                $monthInSeconds = 60 * 60 * 24 * 30;
				$renewalWindow = $monthInSeconds * 10;
			
                if(time() >= $membership_expiry_TS) {
                    $expired = true;
                    $expirySpanClass = ' class="expired"';
                }
                elseif(time() >= ($membership_expiry_TS - $renewalWindow)) {
                    $renewable = true;
                    $expirySpanClass = ' class="renewable"';
                } 
                   
                echo '<div id="expiry_date_div"><label id="expiry_date_label" class="tiptip" title="Membership expiry date.">Expiry Date</label>'; 
                echo '<span'.$expirySpanClass.'>'.date('M j, Y', $membership_expiry_TS).'</span>';
                
                if($expired || $renewable) {
                    // membership within 1 month of expiry or expired, show renew option
                    echo '<input type="submit" class="blue button" id="submit-renewal" name="submit-renewal" value="Renew" />';
                
                    // make information available to JS
                    if($expired) echo '<input type="hidden" id="expiry_status" name="expiry_status" value="expired" />';
                    elseif($renewable) echo '<input type="hidden" id="expiry_status" name="expiry_status" value="renewable" />';
                }

                echo '</div>';
            }



                $input_email->createInputField();
                $input_status->createInputField();
                $input_level->createInputField();
				
				if($memberPreferences['pay_signup'])
				{



					$input_payment_status->createInputField();
				}
				else
				{
					echo '<input type="hidden" name="payment_status" value="1">';
				}
				






				$input_pw_change_request->createInputField();
				
				if($hasFTP) 
				{
					$input_ftp_access->createInputField(); ?>				
			<div id='ftp_section'>
<?php 			$input_restriction->createInputField();
				// Access folders
?>				<div class="input_wrap">
					<label class="tipRight" title="Set the folders this user can access">Access Folders</label>
					<div class="input_inner">
						<div id="module_table">
							<?php // build this in sets of three per row
							foreach($folders as $curIndex => $curName) :
								$checked = isset($list['ftp_folder']) && in_array($curName['folder'], $list['ftp_folder']) ? ' checked="checked"' : '';	
							?>	
							<div class="checkWrap">
								<input type="checkbox" class="no_counter" value="1"	name="ftp_folder[<?php echo $curName['folder']; ?>]"<?php echo $checked; ?>/>
								<?php echo $curName['folder']; ?>
							</div>
							<?php endforeach; ?>
						</div>
					</div><!-- END input_inner -->
						
				</div><!-- END input_wrap -->
				<div style="clear:both;"></div>
			</div>	
					
			<?php
				}
                if($newMember) {
                    $input_password->createInputField();
                    $input_cfm_password->createInputField();
                    
                    echo '<hr class="short" />';
				}
                else {
                    // reset password code
                    displayChangePasswordForm();
                }  
?>
			</div>
			<h2 id="info-toggle" class="tiptip toggle" title="Information about the member.">Member Info</h2><br />
			<div id="info-toggle-wrap" style="display:none;">
				<?php
				$input_first_name->createInputField();
				$input_last_name->createInputField();
				$input_phone_number->createInputField();
				$input_mailing_address->createInputField();
				$input_city->createInputField();
				$input_province_state_region->createInputField();
				$input_country->createInputField();
				$input_postal_code->createInputField();


				//$input_eBulletin->createInputField();

                
			?>
			</div><!-- end prop_wrap -->		   
    <?php
	/* Temporarily removed List Display Options section (not working properly)



            <hr />                
			
            <h2 id="list-display-toggle" class="tiptip toggle" title="Sets what member information to display in the frontend member list for this member.">Member List Display Options</h2><br />
			<div id="list-display-toggle-wrap">
                <?php
                echo '<label class="tiptip barefieldcheck" title="Flag member to display name in member list.">List Name</label>'; 
				$input_list_name->createBareInputField();
                echo '<label class="tiptip barefieldcheck" title="Flag member to display address in member list.">List Address</label>'; 
				$input_list_address->createBareInputField();
                echo '<label class="tiptip barefieldcheck" title="Flag member to display phone number in member list.">List Phone Number</label>'; 
				$input_list_phone->createBareInputField();
                echo '<label class="tiptip barefieldcheck" title="Flag member to display email in member list.">List Email</label>'; 
				$input_list_email->createBareInputField();
                ?>
            </div><!-- end list-display-toggle-wrap -->
            <hr />                
	*/
	?>
			<!-- bio area -->
		<?php if($memberPreferences['members_front']) : ?>




			

			<h2 id="bio-toggle" class="tiptip toggle" title="Tell us about yourself.">Member Bio</h2>
			<?php if (isset ($message['inline']) && array_key_exists('bio', $message['inline'])) :?>
				<span class="<?php echo $message['inline']['bio']['type'] ;?>"><?php echo $message['inline']['bio']['msg'] ;?> </span>
			<?php endif; ?>
			<br />
			<div id="bio-toggle-wrap">
			<?php
			// create tinymce
			$editable = array(
				'editable_class' => 'mceUploadable',
				'attributes' => array(
					'id' => 'bio',
					'name' => 'bio'
				),
				'secure_data' => array(
					'id-field' => 'id',				// req for save && upload
					'id-val' => $member_id,	// req for save && upload
					'upload-type' => 'members'			// req for upload
				)
			);
			$wrapper = getContentWrapper($editable);
			echo $wrapper['open'];
			echo isset($list['bio']) ? htmlspecialchars_decode($list['bio']) : '' ;
			echo $wrapper['close'];
			?>
			</div>
		<?php else: ?>
			<input type="hidden" name="bio" value="">
		<?php endif; ?>



			<!-- end content area -->
			
			<!-- page buttons -->
			<div class='clearFix' ></div>
			<input name="submit-member" type="hidden" />
			<input type="hidden" name="member_image_string" value="<?php echo isset($memberImageString) ? $memberImageString : ''; ?>" />
			<input type="hidden" name="membership_expiry" value="<?php echo isset($membership_expiry) ? $membership_expiry : ''; ?>" />
			<input type="hidden" name="unpaid_signup" value="<?php echo isset($unpaid_signup) ? $unpaid_signup : '0'; ?>" />
		</form>	
		<a id="submit-btn" class="blue button" href="#">Save</a>

		<a class="grey button" id="cancel-btn" href="members.php">Cancel</a>
		<div class='clearFix' ></div> 
		<!-- end page buttons -->

	</div>
</div>	
	

<?php 
include($_config['admin_includes'] . "footer.php"); 
?>

<?php 
/***********************FUNCTIONS**************************/
function displayChangePasswordForm() { 
    global $member_id;
    global $_config; 
    global $message?>

        <hr class="short" />
        <div id="togglebutton" class="toggle"><a name="memberPwd" id="togglePwdForm" class="addForm"><img src="<?php echo $_config['admin_url'] . 'modules/members/images/password.png'; ?>" />Change Member Password</a></div>
            <div id="togglebutton-wrap">
                    <?php
                    $input_new_pass = new inputField( 'New Password:', 'new_pass' );
                    $input_new_pass->type('password');
                    $input_new_pass->counterMax(75);
                    $input_new_pass->toolTip('New Member Password');
                    $input_new_pass->arErr($message);
                    $input_new_pass->createInputField();

                    $input_ver_pass = new inputField( 'Confirm Password:', 'cfm_new_pass' );
                    $input_ver_pass->type('password');
                    $input_ver_pass->counterMax(75);
                    $input_ver_pass->toolTip('Confirm New Member Password');
                    $input_ver_pass->arErr($message);
                    $input_ver_pass->createInputField();
                    ?>

                    <div class="clearFix"></div>
                    <input type="submit" class='blue button' id="submit-pass" name="submit-pass" value="Update Password"/>
                    <input type="hidden" id="member_id" name="member_id" value="<?php echo $member_id; ?>" />
                    <div class="clearFix"></div>
            </div><!-- END togglebutton-wrap -->
            <hr class="short" />
<?php } ?>
