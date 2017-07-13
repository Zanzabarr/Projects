
			<div id="jsgallery">
			
			<!-- "previous page" action -->
			<a class="prev browse left"></a>
			 
			<!-- root element for scrollable -->
			<div class="scrollable" id="scrollable">
			 
			  <!-- root element for the items -->
			  <div class="items">
			 
				<!-- 1-6 -->
				<div>
				  <a href="#"><img src="images/logos/adams.png" alt="Adams Golf" /></a>
				  <a href="#"><img src="images/logos/sunice.png" alt="Sunice" /></a>
				  <a href="#"><img src="images/logos/ping.png" alt="Ping" /></a>
				  <a href="#"><img src="images/logos/nikegolf.png" alt="Nike Golf" /></a>
				  <a href="#"><img src="images/logos/ecco.png" alt="Ecco" /></a>
				  
				  
				</div>
			 
				<!-- 7-12 -->
				<div>
				  
				  
				</div>
			 
				<!-- 13-18 
				<div>
				  <a class="fancy" href="images/smiley.jpg"><img src="images/smiley.jpg" /></a>
				  <a class="fancy" href="images/smiley-g.jpg"><img src="images/smiley-g.jpg" /></a>
				  <a class="fancy" href="images/smiley-o.jpg"><img src="images/smiley-o.jpg" /></a>
				  <a class="fancy" href="images/smiley.jpg"><img src="images/smiley.jpg" /></a>
				  <a class="fancy" href="images/smiley-g.jpg"><img src="images/smiley-g.jpg" /></a>
				 
				</div>-->
			 
			  </div>
			 
			</div>
			 
			<!-- "next page" action -->
			<a class="next browse right"></a>
			</div>
			
<script>
$(document).ready( function() {
	$("a.fancy").each(function(){
		var attr = $(this).attr('href');

		// For some browsers, `attr` is undefined; for others,
		// `attr` is false.  Check for both.
		if (typeof attr !== 'undefined' && attr !== false) {
			$(this).fancybox({
				'transitionIn'	:	'elastic',
				'transitionOut'	:	'fade',
				'speedIn'		:	600, 
				'speedOut'		:	400
			});
		}
	});
});
</script>