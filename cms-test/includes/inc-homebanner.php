<?php /* HOME ROTATING BANNER
1) there is a condition in banner_functions.js that only load the fade functionality if there is more than one banner.
2) if $is_phone only one banner is loaded, and the dl/dd list is left out. Tablet and Desktop load all banners.
3) for each banner output consist of banner img + html layer on top.
4) if no banner exist, a default text will show.
5) rotating-banner-click-dots can be placed anywhere.
6) banner aspectRatio is now calculated automatically by php and moved here from js/banner_functions.js together with slidetime.
7) an additional outer wrap has been added with the sole purpose of hide overflow if the image has to pop out of the frame to create a zoom in effect in mobile.
8) CSS can be found in css/homebanner.css
9) inc_head has a condition where js/banner_functions.js only is loaded if $not_phone
*/ 

// get the rotating banner data
$result = logged_query("SELECT * FROM `banners` where posn > 0 ORDER BY posn ASC",0,array() );
if( is_array($result) && count($result) ) { 
	$banners = $result; 
	$hasBanner = true; 
	// set jQ $aspectRatio
	$path = $_config['upload_path'] . 'banners/rotating/' . $banners[0]['name'];
	$path = str_replace(' ', '%20', $path);
	list($width, $height, $type, $attr) = getimagesize($path);
	$aspectRatio = $height/$width;
}
else { 
	$banners[0]="gives working foreach() even if no banner"; 
	$hasBanner = false; 
}

/* ACTIVATE IF YOU NEED TO TEST 
if($is_phone) 	echo "is_tablet ";
if($not_phone) 	echo "not_tablet ";
if($is_phone) 	echo "is_phone ";
if($not_phone) 	echo "not_phone ";
*/
?>

<script type="text/javascript">
/* vars used by js/banner_functions.js */
var aspectRatio = <?php echo $aspectRatio; ?>; // height/width
var $autoslideTime = 6000; //milliseconds
</script>

<div class="for-hidden-overflow">
<div id="rotating-banner">

<?php if($not_phone) : ?>
	<div id="slidedeck_frame" class="skin-slidedeck">
		<dl class="slidedeck">
<?php endif;

$isMobileStyle = ""; // ($isMobile) ? 'style="margin-left:0;"':''; // huh? might be leftovers not needed anymore.

// rotating banner click dots. result can be placed anywhere
$bannerclickdots = "";
if($not_phone AND $hasBanner) {
	for($b=1; $b<=count($result); $b++) {
		$active = ($b==1) ? 'click-dots-active' : '';
		$bannerclickdots .= "<span id='click-dots-{$b}' class='click-dots {$active}'></span>";
	}
	$bannerclickdots = "<div class='rotating-banner-click-dots'>{$bannerclickdots}</div>"; // to place anywhere
}

// rotating banner
$number=1; 
foreach($banners as $banner)
{
	if($is_phone AND $number>1) break; // only 1 image in mobile
	else
	{
		if($hasBanner)
		{
			$path = $_config['upload_url'] . 'banners/rotating/' . $banner['name'];
			$alt = $banner['alt'];
			$hover_text = $banner['desc'];
			$link = $banner['link'];
			$link1 = ($link!="") ? "<a href='{$link}' title='{$hover_text}'>" : "";
			$link2 = ($link!="") ? "</a>" : "";
			$html = htmlspecialchars_decode($banner['html_desc']);
			$vw = "<script>document.write(document.documentElement.clientWidth);</script>"; // viewport width - add to $html if you need to find out
			$html = ($html!="") ? "<div class='rotating-banner-html'>{$html}</div>" : "";
			//$html = ""; // kill html
			
			$bannercontent = "{$link1}<img class='rotating-banner-img' src='{$path}' alt='{$alt}'>{$html}{$link2}";
		}
		else $bannercontent = "No Banner Available";
		
		// output banner + html. in mobile no dl/dd container. JS slide() disabled as well
		$first = ($number>1) ? "" : "class='showbanner active'";
		if($not_phone) echo "<dd id='banner-image-".$number."' {$first} {$isMobileStyle}>";
		echo $bannercontent;
		if($not_phone) echo "</dd>";
		
		$number++;
			
	}
}

if($not_phone) : ?>       			
		</dl>
<?php endif; ?>
		<div style="clear:both"></div>
	</div> <!-- slidedeck_frame -->

	<?php echo $bannerclickdots; /* DEFAULT position for clickable dots. Can be moved elsewhere. Change CSS in homebanner.css */ ?>

</div> <!-- rotating-banner -->
</div> <!-- for-hidden-overflow -->
<div style="clear:both"></div>