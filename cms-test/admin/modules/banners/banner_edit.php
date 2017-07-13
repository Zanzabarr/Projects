<?php 
// initialize the page
$headerComponents = array();
$headerModule = 'banners';
include('../../includes/headerClass.php');
include('functions.php');

$pageInit = new headerClass($headerComponents,$headerModule);

$bannerUploadPath 	= $_config['upload_path'] . "banners/";
$bannerUploadUrl	= $_config['upload_url'] . "banners/";
$bannerTableName	= 'banners';
$banner = new banners($bannerUploadPath, $bannerUploadUrl, $bannerTableName);


// access user data from headerClass
global $curUser;

// set the db variables
$page_type			= 'banners';		// name of uploadPageType for uploads component, also used to differentiate kinds of delete on parent page

$message			= array();			// will hold error/success message info

// ----------------------------------------------------------build banner info
// get data

$corralImgs = logged_query_assoc_array("SELECT * FROM banners WHERE posn ='0'", 'id',0,array());
$posnImgs = logged_query_assoc_array("SELECT * FROM banners WHERE posn !='0' ORDER BY posn", 'id',0,array());
?>
    
 <?php 
// set the header variables and create the header
$pageInit->createPageTop( $banner->headData() . "<script type=\"text/javascript\" src=\"".$_config['admin_url']."modules/banners/js/banner_edit.js\"></script>");
 ?>
 <div class="page_container">
	<div id="h1"><h1>Banner</h1></div>
    <div id="info_container">
		<?php

		// create a banner if $message['banner'] exists
		createBanner($message); 
		
	    $banner->buildBannerArea();
		echo "<hr />";
		
		//                             Banner Properties                    -->
		?>

	</div>	<!-- end info container -->
</div><!-- end page container -->
<?php 

include($_config['admin_includes'] . "footer.php"); ?>
