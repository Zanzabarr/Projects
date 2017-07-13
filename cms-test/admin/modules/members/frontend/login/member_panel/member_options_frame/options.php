<?php
include_once('../../../../../../includes/config.php');
include_once('../../../../../../includes/functions.php');
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>

<title>Member Preferences</title>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" /> 

<link rel="stylesheet" type="text/css" href="<?php echo $_config['admin_url'] . 'modules/members/frontend/login/member_panel/member_options_frame/css/styles.css'; ?>">
<script type="text/javascript" src="<?php echo $_config['admin_url'] . 'modules/members/frontend/login/member_panel/member_options_frame/js/jquery-1.7.2.min.js'; ?>"></script>
<link rel='stylesheet' type='text/css' href='<?php echo $_config['admin_url'];?>js/jquery.fancybox-1.3.4/fancybox/jquery.fancybox-1.3.4.css' />
<script type="text/javascript" src="<?php echo $_config['admin_url'];?>js/jquery.fancybox-1.3.4/fancybox/jquery.fancybox-1.3.4.pack.js"></script>
<script type="text/javascript" src="<?php echo $_config['admin_url'] . 'modules/members/frontend/login/member_panel/member_options_frame/js/inc.js'; ?>"></script>
<script type="text/javascript" src="<?php echo $_config['site_path'] . 'js/functions.js'; ?>"></script>
<?php if(!logged_in_as_member()) { 
// reload parent page, in turn getting rid of member panel frame,
// as member is no longer logged in ?>

<script type="text/javascript">
    parent.changeURL(parent.document.location);
</script>
<?php } ?>

</head>

<body>
<?php
if(logged_in_as_member()) {

	$member_data = getMemberByID($_SESSION['loggedInAsMember']);
    displayMenuBar($member_data);
	
	
	echo '<div id="options-content">';
	
    if(array_key_exists('contact-info', $_GET)) {
        // member contact info page
        if(!array_key_exists('edit', $_GET)) {
            displayContactInfo($member_data);
        }
        else {            
            // contact info edit page selected
            if(!array_key_exists('submit-contact-info', $_POST)) {
                displayContactInfoForm($member_data);
            }
            else {
                // contact info form submitted
                $errors = validateContactInfoForm($member_data);
            
                if(count($errors) > 0) {
                    displayContactInfoForm($member_data,$errors);
                }
                else {
                    processContactInfoForm($member_data);
                }
            }
        }
    }
    elseif(array_key_exists('membership-details', $_GET)) {
        // membership details page
        displayMembershipDetails($member_data);
    }
    elseif(array_key_exists('change-password', $_GET)) {
        // change password page
        if(!array_key_exists('submit-password-change', $_POST)) {
            displayPasswordChangeForm($member_data);
        }
        else {
            // password change submitted
            $errors = validatePasswordChangeForm($member_data);

            if(count($errors) > 0) {
                displayPasswordChangeForm($member_data,$errors);
            }
            else {
                processPasswordChangeForm($member_data);
            }
        }
    }
	elseif($member_data['pw_change_request'] == 1) {
		// member flagged for password change. Show password change form.
		displayPasswordChangeForm($member_data);
	}
    else {
        // default page to display
        displayMembershipDetails($member_data);
    }
}
else {
    // not logged in (session timeout occurred)
    echo '<p>Login Timed Out. Please refresh the page and log back in to continue.</p>';
}

echo '</div>';
?>
<script type="text/javascript">
	$('.ajaxForm').each(function(){
		$(this).load($(this).attr('data-location'),$.parseJSON($(this).attr('data-post')) )
	});
</script>
</body>
</html>


<?php /***********************/
      /**   PHP FUNCTIONS   **/
      /***********************/


//---------------------------------------------------------------//
function displayMenuBar($member_data) { 
    global $_config; 
	
	$default_selected = 'contact-info';
	$selected = '';
	if(array_key_exists('contact-info', $_GET)) $selected = 'contact-info';
	elseif(array_key_exists('membership-details', $_GET)) $selected = 'membership-details';
	elseif( array_key_exists('change-password', $_GET) || $member_data['pw_change_request'] == 1 ) $selected = 'change-password';
	else $selected = $default_selected;
	?>

    <!-- <div id="close-button-container">
        <a id="close-button"><img src="< ?php echo $_config['site_path'] . 'includes/members/member_panel/member_options_frame/images/close.png'; ?>" /></a>
    </div> -->
	
    <div id="member-menu-bar">
       <!-- not using contact info for this site -->
		<a href="<?php echo $_SERVER['PHP_SELF'] . '?contact-info='; ?>" <?php echo $selected == 'contact-info' ? 'class="selected"' : ''; ?>>Contact&nbsp;Info</a>
		<a href="<?php echo $_SERVER['PHP_SELF'] . '?membership-details='; ?>" <?php echo $selected == 'membership-details' ? 'class="selected"' : ''; ?>>Membership&nbsp;Details</a>
        <a href="<?php echo $_SERVER['PHP_SELF'] . '?change-password='; ?>" <?php echo $selected == 'change-password' ? 'class="selected"' : ''; ?>>Change&nbsp;Password</a>
    </div><!-- member-menu-bar -->
<?php } 


//---------------------------------------------------------------//
function displayContactInfo($member_data) { 

?>
    <p><a id="edit-button" href="<?php echo $_SERVER['PHP_SELF'] . '?contact-info=&edit='; ?>">Edit Contact Information</a></p>

    <p><strong>First Name:</strong> <?php echo $member_data['first_name']; ?></p>
    <p><strong>Last Name:</strong> <?php echo $member_data['last_name']; ?></p>
    <p><strong>Phone Number:</strong> <?php echo $member_data['phone_number']; ?></p>
    <p><strong>Mailing Address:</strong> <?php echo $member_data['mailing_address']; ?></p>
    <p><strong>City:</strong> <?php echo $member_data['city']; ?></p>
    <p><strong>Province/State/Region:</strong> <?php echo $member_data['province_state_region']; ?></p>
    <p><strong>Country:</strong> <?php echo $member_data['country']; ?></p>
    <p><strong>Postal Code:</strong> <?php echo $member_data['postal_code']; ?></p>
    
    <!--<p><strong>eBulletin:</strong> <?php //echo ($member_data['eBulletin'] == 1) ? 'Yes' : 'No'; ?></p>-->
    <p><strong>List Name:</strong> <?php echo ($member_data['list_name'] == 1) ? 'Yes' : 'No'; ?></p>
    <p><strong>List Address:</strong> <?php echo ($member_data['list_address'] == 1) ? 'Yes' : 'No'; ?></p>
    <p><strong>List Phone:</strong> <?php echo ($member_data['list_phone'] == 1) ? 'Yes' : 'No'; ?></p>
    <p><strong>List Email:</strong> <?php echo ($member_data['list_email'] == 1) ? 'Yes' : 'No'; ?></p>
<?php } 


//---------------------------------------------------------------//
function displayContactInfoForm($member_data, $errors = array()) { 
    global $_config;
	foreach($errors as $error) {
        echo '<p class="error">'.$error.'</p>';
    }
	$member_data['referrer'] = $_SERVER['PHP_SELF'];
	$formLocation = "{$_config['admin_url']}modules/members/frontend/login/member_panel/member_options_frame/form-contact.php";
	ajax_form($formLocation, $member_data); 
} 


//---------------------------------------------------------------//
function validateContactInfoForm() {
    $errors = array();

    // not currenty required

    return $errors;
} 


//---------------------------------------------------------------//
function processContactInfoForm($member_data) { 
    foreach($_POST as $k => $v) {
        if($k == 'bio') ${$k} = trim(htmlspecialchars($v, ENT_QUOTES));
        else ${$k} = trim($v);
        $list[$k] = $v;
    }

    // set checkbox data for unchecked boxes
    if(!isset($_POST['pw_change_request'])) $list['pw_change_request'] = $pw_change_request = 0;
    if(!isset($_POST['eBulletin'])) $list['eBulletin'] = $eBulletin = 0;
    if(!isset($_POST['list_name'])) $list['list_name'] = $list_name = 0;
    if(!isset($_POST['list_address'])) $list['list_address'] = $list_address = 0;
    if(!isset($_POST['list_phone'])) $list['list_phone'] = $list_phone = 0;
    if(!isset($_POST['list_email'])) $list['list_email'] = $list_email = 0;

    $result = logged_query("
		UPDATE `members` SET
		`first_name` = :first_name, 
		`last_name` = :last_name,
		`phone_number` = :phone_number,
		`mailing_address` = :mailing_address,
		`city` = :city,
		`province_state_region` = :province_state_region,
		`country` = :country,
		`postal_code` = :postal_code, 
		`eBulletin` = :eBulletin,
		`list_name` = :list_name,
		`list_address` = :list_address,
		`list_phone` = :list_phone,
		`list_email` = :list_email
		WHERE `id` = :member_id LIMIT 1;",0,array(
		':first_name' => $first_name,
		':last_name' => $last_name,
		':phone_number' => $phone_number,
		':mailing_address' => $mailing_address,
		':city' => $city,
		':province_state_region' => $province_state_region,
		':country' => $country,
		':postal_code' => $postal_code,
		':eBulletin' => $eBulletin,
		':list_name' => $list_name,
		':list_address' => $list_address,
		':list_phone' => $list_phone,
		':list_email' => $list_email,
		':member_id' => $member_data['id']
	));

    if($result === false) {
        echo '<p class="error">There was an error saving to the database.</p>';
    }
    else {
        // display updated information, and update Session to new data
		
        displayContactInfo($list);    
    }
} 


//---------------------------------------------------------------//
function displayMembershipDetails($member_data) { 
	global $_config;

    $memberStatusArr = array('Inactive', 'New', 'Regular', 'Complimentary', 'Honorary', 'Sponsored');
    $memberStatus = $memberStatusArr[$member_data['status']];

    if($member_data['level'] == 0) {
        $memberLevel = 'Standard';
    }
    else {
        $memberLevel = 'Premium';
    }

    if($member_data['payment_status'] == 0) {
        $paymentStatus = 'Unpaid';
    }
    else {
        $paymentStatus = 'Paid';
    }  
    
    // convert membership expiry date to timestamp
    $membership_expiry = $member_data['membership_expiry'];

    if($membership_expiry instanceof DateTime) {
        $membership_expiry_TS = $membership_expiry->getTimestamp();
    } else {
        $membership_expiry_TS = strtotime($membership_expiry);
    }

    // determine membership expiry status
    $expired = false;
    $renewable = false;
    $expirySpanClass = '';
    $monthInSeconds = 60 * 60 * 24 * 30;
	$renewalWindow = $monthInSeconds * 10; // 300 days

    if(time() >= $membership_expiry_TS) {
       $expired = true;
       $expirySpanClass = ' class="expired"';
    }
    elseif(time() >= ($membership_expiry_TS - $renewalWindow)) {
       $renewable = true;
       $expirySpanClass = ' class="renewable"';
    }
    ?>

    <p><strong>Email Address:</strong> <?php echo $member_data['email']; ?></p>
    <p><strong>Membership Status:</strong> <?php echo $memberStatus; ?></p>
    <p><strong>Membership Level:</strong> <?php echo $memberLevel; ?></p>
    <p><strong>Payment Status:</strong> <?php echo $paymentStatus; ?></p>
    <!--<p><strong>Seeds Submitted:</strong> <?php //echo $seedsSubmitted; ?></p>-->
    <!--<p><strong>Membership Expiry:</strong> <span<?php //echo $expirySpanClass; ?>><?php //echo date('M j, Y', $membership_expiry_TS); ?></span>
    </p>
        <?php //if($expired || $renewable) { ?>
		    <form id="member_panel_renew_form" method="POST" action="<?php //echo $_config['site_path'].'membership-renewal'; ?>" target="_parent">
            	<input type="submit" name="submit-renewal" value="Renew Membership" />
                <input type="hidden" name="renew_email" value="<?php //echo $member_data['email']; ?>" />
            </form>-->
    	<?php //} ?>
<?php } 


//---------------------------------------------------------------//
function displayPasswordChangeForm($member_data,$errors = array()) { 
	global $_config;
	if(isset($member_data['pw_change_request']) && $member_data['pw_change_request'] == 1) {
		echo '<p>Please change your password from the current temporary password.</p>';
	}

    foreach($errors as $error) {
        echo '<p class="error">'.$error.'</p>';
    }
	
	// load the form via js
	$formLocation = "{$_config['admin_url']}modules/members/frontend/login/member_panel/member_options_frame/form-change-password.php";
	ajax_form($formLocation, array('referrer' => $_SERVER['PHP_SELF'])); 
			
} 


//---------------------------------------------------------------//
function validatePasswordChangeForm() { 
    $errors = array();

    if($_POST['password'] == '' || $_POST['cfm_password'] == '') {
        $errors['password'] = 'Password cannot be blank.';
    }
    elseif($_POST['password'] != $_POST['cfm_password']) {
        $errors['password'] = 'Password and Confirm Password must be equal.';
    }

    return $errors;
} 


//---------------------------------------------------------------//
function processPasswordChangeForm($member_data) { 
        $pw = hasher($_POST['password']);

        $query = "UPDATE `members` SET password = :pw, pw_change_request = 0 WHERE id = :member_id;";
        $result = logged_query($query,0,array(':pw' => $pw, ':member_id' => $member_data['id']));

        if($result === false) echo '<p>Database Error, failed to change password.</p>';
        else echo '<p>Password Changed Successfuly</p>';
} 

// get member by id
function getMemberByID($member_id)
{
	$member = logged_query("SELECT * FROM `members` WHERE `status` >= 1 AND `id`>=1 AND `id` =:member_id LIMIT 1",0,array(":member_id" => $member_id));

	if ($member === false || count($member) == 0 ) $member = false ;
	else $member = $member[0];

	return $member;
}

?>
