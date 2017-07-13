<div class="wrap">
	<div id="call-to-action">
		<a href="contact"><div class="enquiry-button"> </div></a>     
		
        <div style="text-align:left; padding-left:20px; padding-bottom:1em;margin-top:-7px;">
    	<span style="font-weight:700">Lorum Ipsum dolor sit amet, consectetur adipiscing elit.</span><br />
	Lorum Ipsum dolor sit amet, consectetur adipiscing elit..</div>

		<div style="clear:both;"></div>
    </div>
<div style="clear:both; height: 1.4em;"></div>
		
</div> <!-- wrap -->


<div id="bottom">

<!-- ************* START OF FOOTER ********************* -->
	<div id="footer" class="wrap">
		<div id="footer-col1">	
            	<h3>WEBSITE LINKS</h3>
                <p><?php if ($pages->make_link(3, 'LINK 1') !== false) echo '<br />'; ?></p>
                <p><?php if ($pages->make_link(4, 'LINK 2') !== false) echo '<br />'; ?></p>
                <p><?php if ($pages->make_link(17, 'LINK 3') !== false) echo '<br />'; ?></p>
                <p><?php if ($pages->make_link(11, 'LINK 4') !== false) echo '<br />'; ?></p>
                <p><?php if ($pages->make_link(36, 'LINK 5') !== false) echo '<br />'; ?></p>
                <p><?php if ($pages->make_link(12, 'LINK 6','', 'rel=\"nofollow\"') !== false) echo '<br />'; ?></p>
		</div> <!-- section1 -->	
        
		<div id="footer-col2">	
        <?php include_once('includes/inc-testimonial.php'); ?>
        
		</div> <!-- section -->	
		
        <div id="footer-col3">	
                <p>If you have any questions you can conatct us using the information below, or follow us on social networks.</p>
		</div> <!-- section -->	

<div style="clear:both"></div>

<div class="copyright">
	<div style="float:left; text-align:left; width:45%;">&nbsp; &copy; Copyright <?php print date("Y"); ?> &nbsp; | &nbsp; <a href="http://cms-test.com" target="_blank" rel="nofollow"></a></div>
	<div style="float:right; text-align:right; width:45%;"> <a href="site-map" rel="nofollow">Site Map</a> &nbsp; | &nbsp; <a href="privacy-policy" rel="nofollow">Privacy Policy</a> &nbsp; | &nbsp; <a href="terms-conditions" rel="nofollow">Terms</a> &nbsp;</div>
</div>
	
	</div> <!-- footer -->
    
<!-- ************* END OF FOOTER ********************* -->

</div> <!-- bottom -->
<script type="text/javascript">
	$('.ajaxForm').each(function(){
		$(this).load($(this).attr('data-location'),$.parseJSON($(this).attr('data-post')) )
	});
</script>
<?php
// ** INCLUDE STIKITAB ** //
include_once( $_config['rootpath'] . 'stikitab/stikitab.php');
include_once( $_config['admin_path'] . 'components/inline_editing/inline_modal.php');
?>
</body>
</html>
