<?php 
$finder = $pages->get_page_by_slug('storefinder');
$content = $finder['content'];
include $_config['admin_path']."modules/store_locator/frontend/store_locator.php"; ?>
<style>
#map_canvas {
    display: block;
    height: 450px;
    width: 870px;
    border: 1px solid #000;
}
#map_canvas img {  
    max-width: none;   
}
</style>
<div id="content_area" class="wrap">
	<div id="content">
		<div id="left_col">
	    	<?php     
			echo display_content($content);
			?>
			<div style='clear:both;height:1em;'></div>
			<div id='map_canvas'></div>
			<div style="clear:both;"></div>
      	</div>      <!-- left_col -->
	</div><!-- end content -->
	<div style="clear:both;"></div>
</div><!-- end content_area -->