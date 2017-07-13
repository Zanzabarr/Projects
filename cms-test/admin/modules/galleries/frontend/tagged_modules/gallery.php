<?php 
$gallery_id 	= isset($arSpecial[1]) ? $arSpecial[1] : 6;
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
	<!-- new responsive -->
<div class="jsGallery miniGallery">
<?php

$validGalleryTypes = array(
	'gallery'		=> 'buildGallery',
	'squareThumbs' 	=> 'buildNav',
	'stackedThumbs' => 'buildStackedNav',
	'slider' 		=> 'buildSlider',
	'caption' 		=> 'buildCaption',
	'HTMLcaption' 	=> 'buildHTMLCaption'
);

$galleryTypes = array();
$gallery_display_options = $_config['gallery_display_options'];

// if the keyword "slider" has been passed, set the defaults for stand alone sliders
if(in_array('slider',$arSpecial))
{
	$gallery_display_options = array_merge($gallery_display_options, $_config['gallery_solo_slider_display_options']);	
}

$skipNext = false;
// parse the passed $arSpecial Array
foreach($arSpecial as $k => $v)
{
	if($k <= 1) continue;
	if($skipNext) // if this is the value part of a key/value pair, skip
	{
		$skipNext = false;
		continue;
	}
	
	// if this is a valid option name, get the options value which follows in the array
	// gallery_display_options is passed from /admin/modules/galleries/system/config/php
	if(array_key_exists($v, $gallery_display_options) && isset($arSpecial[$k + 1]))
	{
		$gallery_display_options[$v] = $arSpecial[$k + 1];
		$skipNext = true;
	}
	// if this is a valid gallery type, add it to the gallery type array to be displayed (in order found)
	elseif(array_key_exists($v, $validGalleryTypes))
	{
		$galleryTypes[] = $v;
	}
}

if(count($galleryImgs)<=1) {

	$gallery_display_options['noScrollArrows'] = true;
	$gallery_display_options['cycleSpeed'] = 0;
	
}
$gallery_display_options['galleryId'] = $gallery_id;

$gallery = new displayGallery( 
	$galleryImgs, 
	'uploads/gallery/', 
	$gallery_display_options
);

// if the tagged page didn't set any gallery structure, use the default defined in /admin/modules/galleries/system/config/php
if(!count($galleryTypes))
{
	$galleryTypes = $_config['default_gallery_structure'];
}

// set the structure of the gallery
foreach($galleryTypes as  $gtype)
{
	$fnc = $validGalleryTypes[$gtype];
	$gallery->$fnc();
}

?>
</div>

<?php
	} else {
		echo "We're sorry, this Gallery has no images at this time.";
	}
}
else echo "We're sorry, this Gallery is currently unavailble. Please try again later.";


