<div id="banner-buttons">
		
		<?php
// get the rotating banner data
$result = logged_query("SELECT * FROM `banners` where posn > 0 ORDER BY posn ASC",0,array() );
if( is_array($result) && count($result) ) $banners = $result;
else $banners = false;
?>

<div id="banner-button-box-prev"><img src="<?php echo $_config['site_path'].'images/banner-button-previous.png'; ?>" alt="previous" /></div>

<?php
                $j = 1;
                foreach($banners as $banner) { 
				    if(!$isMobile || $j <= 1) {
						if(!$isMobile) {
                ?>	
						<div id="banner-button-box-<?php echo $j; ?>" class="banner-button-box <?php echo ($j == 1) ? 'showbutton active' : ''; ?>">&nbsp;</div>
                <?php
						}
                    }
                    $j++; 
                } ?>
	

<div id="banner-button-box-next"><img src="<?php echo $_config['site_path'].'images/banner-button-next.png'; ?>" alt="next" /></div>
		
		
		</div><!--banner counter-->
		
		
		<div style="clear:both;"></div>

