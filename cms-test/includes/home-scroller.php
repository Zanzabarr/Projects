<?php 
$gallery_id 	= 13;
if(!is_pos_int($gallery_id))$error = 'We could not find the gallery you were looking for. Please try again later.';
//$num_thumbs	   	= isset($arSpecial[2]) || !is_pos_int($arSpecial[2],true) ? $arSpecial[2] : 5;


$galleryInfo = logged_query("SELECT * FROM gallery where id =:gallery_id",0,array(":gallery_id" => $gallery_id));
if ( count($galleryInfo) >0 )
{
	$galleryInfo = $galleryInfo[0];
}

if(isset($galleryInfo['status']) && $galleryInfo['status'])
{
	$orderBy = $galleryInfo['sort_type'];
	if ($orderBy == 0) $orderClause = ' ORDER BY `date` desc ';
	elseif ($orderBy == 1) $orderClause = ' ORDER BY `date` asc ';
	elseif ($orderBy == 2) $orderClause = ' ORDER BY `posn`';
	else $orderClause = ' ORDER BY `alt`';

	$galleryImgs = logged_query("SELECT * FROM gallery_image WHERE gallery_id=:id AND `posn` > 0 {$orderClause}",0,array(":id" => $galleryInfo['id']));

	if(!empty($galleryImgs)) {
?>
<div class="jsGallery miniGallery">
<?php
if(count($galleryImgs)>1) {
	$galleryoptions = array(
		'galleryId' 		=> $gallery_id , 
		'leftArrow' => '<img src="admin/images/arrow-left-on.png">', 
		'rightArrow' => '<img src="admin/images/arrow-right-on.png">',
		'leftThumbArrow' => '<img src="images/left-scroll-arrow.png">', 
		'rightThumbArrow' => '<img src="images/right-scroll-arrow.png">',
		'displaySizeFolder' => 'banner',
		'placeHolder'		=> 'admin/images/resp_gallery_placeholder.gif',
		'cycleSpeed'		=> 0
	);
} else {
	$galleryoptions = array(
		'galleryId' => $gallery_id, 
		'noScrollArrows' => true,
		'displaySizeFolder' => 'banner',
		'placeHolder'		=> 'admin/images/resp_gallery_placeholder.gif',
		'cycleSpeed'		=> 0
	);
}

$gallery = new displayGallery( 
	$galleryImgs, 
	'uploads/gallery/', 
	$galleryoptions
);
	//$gallery->buildGallery();
	//if($_config['gallery_img_text_desc'])$gallery->buildCaption();
	$gallery->buildNav();
	if($_config['gallery_img_html_desc']) $gallery->buildHTMLCaption();
	//$gallery->buildStackedNav();
?>
</div>

<?php
	} else {
		echo "We're sorry, this Gallery has no images at this time.";
	}
}
else echo "We're sorry, this Gallery is currently unavailble. Please try again later.";


 /*
<div class="leftScrollArrow"><img src="images/left-scroll-arrow.png" alt="scroll" /></div>
<div class="homeScroller">

<ul class="homeScroll">

</ul>

</div><!--scroll box-->
<div class="rightScrollArrow"><img src="images/right-scroll-arrow.png" alt="scroll" /></div>

<script>
$(document).ready( function() {
	$('.rightScrollArrow').on('click', function() {
		
	});
});
</script>
*/?>
