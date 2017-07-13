<?php
// get the rotating banner data
$result = logged_query("SELECT * FROM `banners` where posn > 0 ORDER BY posn ASC",0,array() );
if( is_array($result) && count($result) ) $banners = $result;
else $banners = false;
?>
<div id="rotating-banner"  class="wrap">
        	<div id="slidedeck_frame" class="skin-slidedeck">
    			<dl class="slidedeck">
				
<?php 
if (!$banners)		//do default banner
{
?>
        			<dd id="banner-image-7" class="showbanner active" <?php if ( $isMobile) echo 'style="margin-left:0;"'; ?>>
					    <a href="small-business-packages" title="Small Business Help"><img src="images/banners/banner-7.jpg" alt="Websites By The Month"></a>
                        <p>&nbsp;</p>
                    </dd>
<?php 
}
else				// show backend banners: if this is mobile, only show one.
{
	$path = $_config['upload_url'] . 'banners/rotating/' . $banners[0]['name'];
	$alt = $banners[0]['alt'];
	$link = $banners[0]['link'];
	$hover_text = $banners[0]['desc'];
	$html = $banners[0]['html_desc'];
	$html = "&nbsp;";
	// this site doesn't use the $html
?>
					<dd id="banner-image-1" class="showbanner active" <?php if ($isMobile) echo 'style="margin-left:0;"'; ?>>
						<a href="<?php echo $link;?>" title="<?php echo $hover_text;?>"><img src="<?php echo $path;?>" alt="<?php echo $alt; ?>"></a>
						<p><?php echo $html;?></p>
					</dd>	
<?php
	if(!$isMobile)   // do extra images
	{
	
		unset($banners[0]);
		$number = 2;
		foreach($banners as $banner)
		{
			$path = $_config['upload_url'] . 'banners/rotating/' . $banner['name'];
			$id = "banner-image-" . $number++;
			$alt = $banner['alt'];
			$link = $banner['link'];
			$html = $banner['html_desc'];
			$html = "&nbsp;";
			$hover_text = $banner['desc'];
?>
					<dd id="<?php echo $id; ?>">
						<a href="<?php echo $link;?>" title="<?php echo $hover_text;?>"><img src="<?php echo $path;?>" alt="<?php echo $alt; ?>"></a>
						<p><?php echo $html;?></p>
					</dd>		
					
<?php
		}
		
	}
}
/* 	                   
    			    <dd id="banner-image-1">
                        <a href="website-features" title="Websites By The Month"><img src="images/banners/banner-1.jpg" alt="Websites By The Month"></a>
                        <p>&nbsp;</p>
    			    <dd id="banner-image-2">
                        <a href="search-engine-optimization" title="SEO"><img src="images/banners/banner-2.jpg" alt="Websites By The Month"></a>
                        <p>&nbsp;</p>
    			    <dd id="banner-image-3">
                        <a href="responsive-web-designs"><img src="images/banners/banner-3.jpg" alt="Websites By The Month"></a>
                        <p>&nbsp;</p>
        		    <dd id="banner-image-4">
                        <a href="small-business-packages" title="Small Business Packages"><img src="images/banners/banner-4.jpg" alt="Websites By The Month"></a>
                        <p>&nbsp;</p>
                    </dd>
 
 */ 
?>       			
		    	</dl>
			
			<div style="clear:both"></div>

		<!--	<div id="banner-buttons">
				<div id="banner-button-box-1" class="banner-button-box showbutton active">01</div>
				< ?php if (! $isMobile) { ?>
				<div id="banner-button-box-2" class="banner-button-box">02</div>
				<div id="banner-button-box-3" class="banner-button-box">03</div>
				<div id="banner-button-box-4" class="banner-button-box">04</div>
				<div id="banner-button-box-5" class="banner-button-box">05</div>
				<div id="banner-button-box-6" class="banner-button-box">06</div>
				<div id="banner-button-box-7" class="banner-button-box">07</div>
		 		< ?php } ?>
			</div>< !-- banner-buttons 
			
			<div style="clear:both"></div>	-->	
		</div> <!-- slidedeck_frame -->
</div> <!-- rotating-banner -->
