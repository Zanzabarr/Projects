<?php

$base_url = $_config['site_path'];
global $base_url;

// set seo data for use in the main head
$seot = 'Testimonials';
$seod = 'Testimonials';
$seok = 'Testimonials';


$homePageData = logged_query_assoc_array("
SELECT `title`, `desc`, `status` 
FROM testimonials_home;",null,0,array());

if(count($homePageData) > 0) {
	$homePageData = $homePageData[0];
}
else {
	$homePageData = array();
}

	
?>
<!-- testimonials module includes -->
<?php $moduleLink = '<link rel="stylesheet" type="text/css" href="' . $_config['admin_url'] . 'modules/' . $module . '/frontend/style.css" /> <script type="text/javascript" src="'.$_config['admin_url'].'modules/'.$module.'/frontend/inc.js"></script>'; ?>
