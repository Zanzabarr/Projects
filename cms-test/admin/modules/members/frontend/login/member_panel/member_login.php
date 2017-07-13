<?php
//include_once('../../config.php');
include_once($_config['admin_modules'].'members/includes/functions.php');
$hasFTP = isset($_config['customNames']['ftp']) ? true : false; //if this site has the ftp module
	
	$options = getMembersOptions();
	
    include_once('member_panel.js');
    
    if(array_key_exists('memberLogout', $_GET)) {
        unset($_SESSION['member']);
		unset($_SESSION['loggedInAsMember']);
		unset($_SESSION['ftp_logged_in']);
		unset($_SESSION['ftp_tz_offset']);
		unset($_SESSION['ftp']);
		unset($_SESSION['ftp_folders']);
		$ftp_logged_in = false;
		// redirect to home
		?>
		<script type="text/javascript">
		window.location = "<?php echo $_config['site_path']; ?>";
		</script>
		<?php
    }

    if(logged_in_as_member()) {
		$member_data = getMemberByID($_SESSION['loggedInAsMember']);
		$start_open = $member_data['pw_change_request'] ? '1' : '';
        showMemberOptions($start_open);
    }
    elseif(!array_key_exists('submit-login', $_POST)) {
        // show login form
       if ( array_key_exists('member-login', $_POST) )  showLoginForm();
	else showInitialLinks($options);
    }
    else {
        $errors = validateLoginForm();
        if(count($errors) != 0) {
            showLoginForm($errors);
        }
        else {
			
            $errors = processLoginForm($_POST['login_email'], $_POST['password'] );
			if(count($errors)) showLoginForm($errors);
			else redirect();
        }
    }