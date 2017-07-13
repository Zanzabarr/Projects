<?php

if($arOutput['req_login']) 
{ ?>
	<h1 class='left'>File Sharing</h1>
	<h2>Please log in for full access to this site.</h1>

	<div style="clear:both; height:20px"></div>

	<div class="login_wrapper">
	<?php
		showPageLogin($arOutput['login_errors']);
	?>
    </div>

<?php
}
else 
{
?>
<h1 class='left'>File Sharing</h1>
<?php
	$has_folders = false;
	foreach($_SESSION['ftp_folders'] as $tmpFolder)
	{
		if($tmpFolder['status'])
		{
			$has_folders = true;
			break;
		}
	}
	if(!$has_folders)
	{
?>
<h2>No Access</h2>
<p>You don't have access to any folders in the File Sharing section. </p>
<?php
	}
	else
	{
?>
<a class='logoutBtn' href='<?php echo $_config['path']['members'];?>ftp/logout'>Log Me Out</a>
<a class='logoutBtn' href='admin/modules/members/frontend/frame/ftp.php' style="padding-right:1em;">Fullscreen</a>

<div style="clear:both; height:20px"></div>



<!--[if !IE]> -->
<div id="frameWrap">
<!-- <![endif]-->
<!--[if IE]>
<div id="frameWrap" class="IE">
<![endif]-->
<iframe id="ftp_frame" frameBorder="0" src="admin/modules/members/frontend/frame/ftp.php" ></iframe>
</div>
<?php
	}
}
