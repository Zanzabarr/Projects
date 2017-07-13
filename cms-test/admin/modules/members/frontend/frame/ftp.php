<?php
include_once("../../../../includes/config.php");
include_once("../../includes/functions.php"); 

$active = array('switch'=>'folder','dir'=>'ASC');
if(isset($_POST['changed']))
{
	$last_active = isset($_SESSION['folder_sort']) ? $_SESSION['folder_sort'] : array('switch'=>'folder','dir'=>'ASC');
	
	// if this is already active, arrow direction changes
	if($_POST['changed'] == $last_active['switch'])
	{
		$tmpDir = isset($_POST[$_POST['changed']]) ? 'ASC' :'DESC';
		$active = array(
			'switch' => $_POST['changed'],
			'dir' => $tmpDir
		);
	}
	else // this is now the highlighted element and the arrow is down by default
	{
		$active = array('switch'=> $_POST['changed'], 'dir'=>'ASC');
	}
	$_SESSION['folder_sort'] = $active;
}

$ftp_logged_in = isset($_SESSION['loggedInAsMember']) ? $_SESSION['loggedInAsMember'] : false;
?>
<!DOCTYPE HTML>
<!--[if IE 8 ]>    <html class="ie8 ielt9 isIE" lang="en"> <![endif]-->
<!--[if IE 9 ]>    <html class="ie9 isIE" lang="en"> <![endif]-->
<!--[if (gt IE 9) ]>    <html class="isIE" lang="en"> <![endif]-->
<!--[if !(IE)]><!--> <html class="" lang="en"> <!--<![endif]-->
<html lang="en">
<head>


<!-- Force latest IE rendering engine or ChromeFrame if installed -->
<!--[if IE]><meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1"><![endif]-->
<meta charset="utf-8">
<title>File Sharing</title>

<meta name="viewport" content="width=device-width">
<link rel='stylesheet' href='<?php echo $_config['admin_url']?>js/jquery.fancybox-1.3.4/fancybox/jquery.fancybox-1.3.4.css' type='text/css' media='screen' />


<script src="http://code.jquery.com/jquery-1.9.1.min.js"></script>
<script src="http://code.jquery.com/jquery-migrate-1.1.1.min.js"></script>
<script src="<?php echo $_config['admin_url']?>modules/members/frontend/frame/js/jquery-ui-1.10.2.custom/js/jquery-ui-1.10.2.custom.min.js"></script>


<!-- Shim to make HTML5 elements usable in older Internet Explorer versions -->
<!--[if lt IE 9]><script src="http://html5shim.googlecode.com/svn/trunk/html5.js"></script><![endif]-->

<!--[if lt IE 9]>
<script type="text/javascript" src="js/ie9.js"></script>
<![endif]-->



<link rel="stylesheet" href="css/uploader_frame.css" />

</head>
<body>

<?php 



if ($ftp_logged_in == false ) die('Please Log In');

$folders = array();
$errormsg = '';


?>
<input id="root" type="hidden" value="<?php echo $_config['site_path'];?>">
<input id="framePath" type="hidden" value="<?php echo $_config['admin_url'] . "modules/members/frontend/frame/";?>">
<input id="securePath" type="hidden" value="<?php echo $_config['secure_uploads'];?>">

<div id="main" style="position:relative;">
	<p id="msg-box"><?php echo $errormsg; ?></p>
	<div id="sort-wrap">
		<form id='sort-form' action="<?php echo $_SERVER['REQUEST_URI']; ?>" method="post" enctype="application/x-www-form-urlencoded">
			
			<table border="0">
				<thead>
					<tr>
						<th id="head_name">
							<div>Folder Name
								<div class="upDown <?php echo $active['switch'] == 'folder' ? '' : 'inactive'; ?>">
									<input type="checkbox" name="folder" <?php echo $active['switch'] == 'folder' && $active['dir'] =='DESC' ? '' : 'checked'; ?> ><label></label>
								</div>
							</div>	
						</th>
						<th id="head_modified">
							<div>Modified
								<div class="upDown <?php echo $active['switch'] == 'date_updated' ? '' : 'inactive'; ?>">
									<input type="checkbox" name="date_updated" <?php echo $active['switch'] == 'date_updated' && $active['dir'] =='DESC' ? '' : 'checked'; ?> ><label></label>
								</div>
							</div>
						</th>
					</tr>
				</thead>
				<tbody id="sorted-folders"></tbody>
			</table>

		</form>				
	</div>	
	<div class="clear"></div>
<form id="fileArea" class="fileupload" enctype="multipart/form-data" method="POST" action="upload_handler.php" style="display: block;">
	<div class="fileinputs">
				<input type="file" name="files[]" multiple class="file">
				<div class="fakefile">Upload File(s)</div>
	</div>
	<div id="drop">	</div>
</form>
<div class="clear"></div>
</div>


<script type='text/javascript' src='<?php echo $_config['admin_url']?>js/jquery.fancybox-1.3.4/fancybox/jquery.fancybox-1.3.4.pack.js'></script>



			
<script src="<?php echo $_config['admin_url']?>js/jquery.fileupload/jquery.ui.widget.js"></script>
<script src="<?php echo $_config['admin_url']?>js/jquery.fileupload/jquery.iframe-transport.js"></script>
<script src="<?php echo $_config['admin_url']?>js/jquery.fileupload/jquery.fileupload.js"></script>
<!-- The main application script -->
<script src="js/main.js"></script>
<!-- The XDomainRequest Transport is included for cross-domain file deletion for IE8+ -->
<!--[if gte IE 8]><script src="js/cors/jquery.xdr-transport.js"></script><![endif]-->
</body> 
</html>
