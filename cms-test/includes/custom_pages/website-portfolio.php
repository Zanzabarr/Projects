<?php 

$portfolio_page = $pages->get_page_by_slug(uri::get(0));
$content = $portfolio_page['content'];
$hasSidebar = false;

?>
<!-- ************* START OF CONTENT AREA ********************* -->

<div id="content_area" class="wrap">


	<div id="content" style="float:left;  margin-left:3%; margin-right:3%; width:94%">
		<?php display_content($content); 
				
		?>
    </div><!-- content -->

	<div style="clear:both"></div>
</div>	<!-- content_area -->


<!-- ************* END OF CONTENT AREA ********************* -->