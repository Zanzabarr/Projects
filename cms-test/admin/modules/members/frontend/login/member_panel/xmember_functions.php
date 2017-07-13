<?php
function redirect() {
global $_config;

	echo "<script>
	window.location = '{$_config['site_path']}';
	</script>";
}
function showMemberOptions() { 
global $_config; ?>
 
<div class="member_panel_container">

	<div class="member_panel">
    	
    
    	<a class="logout-button" href="<?php echo $_SERVER['REQUEST_URI'] . '?memberLogout=true'; ?>">Logout</a>
		&nbsp;&nbsp;&nbsp;
		<a class="options-button" href="">Options</a>
   		<input type="hidden" class="scriptSRC" value="<?php echo $_config['admin_url'] . 'modules/members/frontend/login/member_panel/member_options_frame/options.php'; ?>" /> 

    	<div class="iframe_area"></div>
	</div><!-- member_panel -->
    
    <div class="clear"></div>
</div><!-- member_panel_container -->

<?php
	// if member flagged for password change, bring up member options window automatically
	//if($_SESSION['member']['pw_change_request'] == 1) { ?>
		<!--<script type="text/javascript">
			createMemberOptionsFrame();
		</script>-->
	<?php //}

}


//---------------------------------------------------------------//
function showInitialLinks()
{ ?>
	<div class="member_panel_container"> 
    	<div class="member_panel">
			<a href="members/create-account#content">Create an Account</a><span class='divider'></span><a class="member-login" href="<?php echo rtrim(str_replace('member-login','', $_SERVER['REQUEST_URI']), '/') . '/member-login'; ?>">My Account</a>
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
			<a href="members/create-account#content">Create an Account</a><span class='divider'></span><a href="<?php echo rtrim(str_replace('member-login','', $_SERVER['REQUEST_URI']), '/') ?>">Cancel</a>
        	
            
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


//---------------------------------------------------------------//
function validateLoginForm() {
    $errors = array();

    if(!isset($_POST['login_email']) || !isset($_POST['password']) || !isValidEmailAddress($_POST['login_email'])) {
        $errors['login'] = 'Invalid Email or Password';
    }    

    return $errors;
}


//---------------------------------------------------------------//
function processLoffginForm($u = false, $p = false) {
	$login_error = authorizeMember($u, $p);
	if($login_error)
	{

	}

    $email_address = trim($_POST['login_email']);
//    $hasFTP = isset($_config['customNames']['ftp']) ? true : false; //if this site has the ftp module
    $members=logged_query("SELECT * FROM `members` WHERE `id` > 0 AND `email` like :email_address ORDER BY `email` ASC LIMIT 1;",0,array(':email_address' => $email_address));
    $errors = array();
    if(count($members) == 0 || !hasher($_POST['password'], $members[0]['password'])) {
        // no member with that email exists, or password is incorrect
        $errors['login'] = 'Invalid Email or Password.';
    }
    else {
        // correct email (username) and password, determine if member is eligible to login based on memberData
        $memberData = $members[0];

        if($memberData['payment_status'] == 0) {
            // membership awaiting payment
            $errors['login'] = 'We do not have a record of payment.  You will not be able to access the site until payment is received.<br /><br /><div><a href="membership-payment?email='.$email_address.'"><span style="font-size:16px; font-weight:bold">Pay My Membership Fee Now.</span></a></div>';
        }
        /*elseif(strtotime($memberData['membership_expiry']) < time()) {
            // membership expired
            $errors['login'] = 'Membership expired.';
        }*/
        elseif($memberData['status'] == 0) {
            // member status set to inactive
            $errors['login'] = 'Your membership is currently inactive. Please contact us to re-activate your membership';
        }

        else {
            // cleared for login, set session variables
            $_SESSION['member'] = $memberData;
            $_SESSION['loggedInAsMember'] = true; 
			$ftpdata = logged_query("SELECT * FROM `ftp_user` WHERE `id` = :member_id LIMIT 1; ",0,array(":member_id" => $_SESSION['member']['id'] ));
			foreach($ftpdata as $ftpd ) {
				if($ftpd['status']>0) $_SESSION['ftp_logged_in']=$ftpd['id'];
			}
            // don't want to reveal passwords in data stream
            unset( $_SESSION['member']['password']);
            //showMemberOptions();
			//redirect to home for full refresh to get member-only content
				redirect();
        }
    }

    if(count($errors) > 0) {
        showLoginForm($errors);
    }
}

function processLoginForm($u,$p) 
{
	$errors = array();
	$result=logged_query("
	SELECT id,password,ftp_access as status 
	FROM `members` 
	WHERE email like :u 
	  AND id > 0
	LIMIT 1",0,array(":u" => $u));
	$memberData= isset($result[0]) ? $result[0] : false;
	if ($memberData && hasher($p, $memberData['password']) ) 
	{	
	    // correct email (username) and password, determine if member is eligible to login based on memberData

        if(isset($memberData['payment_status']) && $memberData['payment_status'] == 0) 
		{
            // membership awaiting payment
            $errors['login'] = 'We do not have a record of payment.  You will not be able to access the site until payment is received.<br /><br /><div><a href="membership-payment?email='.$email_address.'"><span style="font-size:16px; font-weight:bold">Pay My Membership Fee Now.</span></a></div>';
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
		$errors['login'] = 'Invalid Email or Password.';
    } 
	
	if(count($errors))
	{
		unset($_SESSION['ftp_tz_offset']);
		unset($_SESSION['loggedInAsMember']);
		unset($_SESSION['ftp']);
		unset($_SESSION['ftp_folders']);
		$ftp_logged_in = false;
	}
	else
	{
		$_SESSION['loggedInAsMember'] = $memberData['id'];
		$_SESSION['member'] = $memberData;
		$tz_offset = $_POST['tz_offset'];
		$tz_offset = is_numeric($_POST['tz_offset']) ? $tz_offset : false;
		$_SESSION['ftp_folders'] = get_valid_folders($ftp_logged_in);
		$_SESSION['ftp_tz_offset'] = $tz_offset;
	}
	
	return $errors;
} 


//---------------------------------------------------------------//
function isValidEmailAddress($email) {
    if (preg_match('/^[a-zA-Z0-9._-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,4}$/', $email)) return true;
    else return false;
}