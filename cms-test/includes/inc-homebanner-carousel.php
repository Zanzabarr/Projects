<?php // get banner images
    $bannerImages = logged_query_assoc_array("SELECT * FROM banners WHERE posn !='0' ORDER BY posn",null,0,array());
?>
	<!-- new responsive -->
<div id="carousel_container">
<?php
if(count($bannerImages)>1) {
	$banneroptions = array(
		'leftArrow' => '<img src="admin/images/arrow-left-on.png">', 
		'rightArrow' => '<img src="admin/images/arrow-right-on.png">',
		'leftThumbArrow' => '<img src="admin/images/gray-previous.png">', 
		'rightThumbArrow' => '<img src="admin/images/gray-next.png">',
		'displaySizeFolder' => 'banner',
		'placeHolder'		=> 'admin/images/resp_banner_placeholder.gif',
		'cycleSpeed'		=> 4000
	);
} else {
	$banneroptions = array(
		'noScrollArrows' => true,
		'displaySizeFolder' => 'banner',
		'placeHolder'		=> 'admin/images/resp_banner_placeholder.gif',
		'cycleSpeed'		=> 0
	);
}

$banner = new displayBanner( 
	$bannerImages, 
	'uploads/banners/', 
	$banneroptions
);
	$banner->fullSlideBanner();
	//if($_config['gallery_img_text_desc'])$banner->buildCaption();
?>
	<div class="clear"></div>
</div><!-- end carousel_container -->