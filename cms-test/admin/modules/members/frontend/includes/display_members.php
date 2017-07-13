<?php //session_start();

/* variables passed along from index.php (this is an include from index)
**	$_config	: contains all config data from admin/includes/config.php
**	$uri		: array of uri components: $uri[0] is the base (members in this case)
**	$module = 'members';
**	$pages		: pages object defined in admin/includes/functions.php - used to build menus and keep track of page info
*/

/* variables passed along from modules/members/frontend/head.php (via index.php)
**	$options		: (global) members page options (data includes members home page data)
** 	$arOutput		: display data
*/

//----------------------------begin html----------------------------------------------

// this section has been entered from /index.php and this is the beginning of:

// TODO if not logged in as member or admin, redirect to login screen


// make the module path available to js
?>
<input type="hidden" id="module_path" value="<?php echo $_config['admin_url'] . 'modules/members/'; ?>" />

<div id='members-body'>
	
<?php
$memberTitle = isset($arOutput['title'])?$arOutput['title']:'';
if($memberTitle) echo "<h1>{$memberTitle}</h1>";

if(isset($arOutput['nonmember_desc']) ) echo $arOutput['nonmember_desc'];

$req_login = isset($arOutput['req_login']) && $arOutput['req_login'];
if($req_login && !isset($arOutput['createMember']) && !isset($arOutput['signupConfirm']) && !isset($arOutput['paymentPage'])  ) 
{ 
?>
	<h2>Please log in for full access to this site.</h1>

	<div style="clear:both; height:20px"></div>

	<div class="login_wrapper">
	<?php
		showPageLogin($arOutput['login_errors']);
	?>
    </div>
<?php
}

	
// Show the Member's homepage description if it is enabled; But only if this is the first page of the member's roster
if (!$req_login && isset($arOutput['desc']) && $arOutput['desc'])
{
	echo $arOutput['desc'];
}

if ( isset($arOutput['memberPag']) && 
		$arOutput['memberPag'] != false && 
		count($arOutput['memberPag']->result) <= $options['members_per_pg']
	&& !$req_login && !isset($arOutput['singleMember']) && !isset($arOutput['createMember']) ) {
    // display name search form ?>
    <hr />
    <form method="post" action="<?php echo$_config['site_path']. $module; ?>#search">
        <h3 id="search">Search By Name</h3>
        <input type="text" value="" id="member_search_key" name="member_search_key" />
        <input type="submit" id="submitSearch" name="submitSearch" value="Search" />
    </form>
    <hr />
<?php 
	if(isset($arOutput['searchMessage'])) echo $arOutput['searchMessage']; 
} 
	
// display the appropriate page portions:
// display a single member if that case
if (!$req_login && isset($arOutput['singleMember'])) echo $arOutput['singleMember'];
// or display all members in pagination
if (!$req_login && $arOutput['memberPag'] != false) 
{
    echo '<div id="membersList">';
	$arOutput['memberPag']->showResults();
    echo '</div><!-- END membersList -->';
	echo $arOutput['memberPag']->uriPaginate();
}
if (isset($arOutput['createMember']))
{
//var_dump($arOutput);
	// display the page topper
	echo "<h1>{$arOutput['signup_title']}</h1>";
	
	// two cases, successfully signed up: not yet signed up
	if($arOutput['success_msg'])
	{
		display_content($arOutput['success_msg']);
	}
	else	//show the login form
	{
	if($arOutput['signup_desc']) 
		display_content($arOutput['signup_desc']);
	
		// load the form data and display the ajax form
		$formData = array(
			'referrer' => $_SERVER['REQUEST_URI'],
			'error_msg' => $arOutput['error_msg'],
			'form_errors' => $arOutput['form_errors'],
			'list' => $arOutput['list']
		);
		$formLocation = $_config['admin_url'] . "modules/members/frontend/forms/signup_form.php";
		ajax_form($formLocation, $formData );
	}
}

if (isset($arOutput['signupConfirm']))
{
	// display the page topper
	echo "<h1>{$arOutput['confirm_title']}</h1>";
	
	// two cases, successfully signed up: not yet signed up
	if($arOutput['success_msg'])
	{
		display_content($arOutput['success_msg']);
	}
	else	//show the login form
	{
	if($arOutput['confirm_desc']) 
		display_content($arOutput['confirm_desc']);
	
		// load the form data and display the ajax form
		$formData = array(
			'formActionUrl' => $_SERVER['REQUEST_URI'],
			'form_errors' => $arOutput['form_errors'],
			'list' => $arOutput['list']
		);
		$formLocation = $_config['admin_url'] . "modules/members/frontend/forms/confirm_form.php";
		?>
		
	<div style="clear:both; height:20px"></div>

	<div class="login_wrapper">
		<?php
		ajax_form($formLocation, $formData );
		?>
	</div>
	<?php
	}
}
if (isset($arOutput['paymentPage']))
{

echo $arOutput['paypal_button'];
}

if (isset($arOutput['requestReset']))
{
	echo "<h1>Reset Member Password/Confirmation Code</h1>";
	
	if ($arOutput['success']) echo $arOutput['msg'];
	else// if success wasn't set: put in a button
	{
			// load the form data and display the ajax form
		$formData = array(
			'formActionUrl' => $_config['path']['members'] . "reset",
			'username' => '', //$arOutput['username'],
			'msg' => $arOutput['msg']
		);
		$formLocation = $_config['admin_url'] . "modules/members/frontend/forms/reset_form.php";
		?>	
	<p>If you've forgotten your password or your Confirmation Code, you can request an email to be sent to your account's email address.</p>	
	<div style="clear:both; height:20px"></div>

	<div class="login_wrapper">
		<?php
		ajax_form($formLocation, $formData );
		?>
	</div>
	<?php
		
	}
}

?>
</div><!-- end members body -->