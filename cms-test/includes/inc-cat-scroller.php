<?php	/* this creates a scrollable "gallery" widget from product categories */

$cats = logged_query_assoc_array("SELECT * FROM ecom_category_data WHERE status > 0 ORDER BY id ASC");

?>
<link rel="stylesheet" type="text/css" href="css/scroll-gallery.css" />
<div id="scroll-gallery">
	<!-- "previous page" action -->
	<a class="prev browse left"></a>
	 
	<!-- root element for scrollable -->
	<div class="scrollable" id="scrollable">
	  <!-- root element for the items -->
		<div class="items">
<?php
foreach($cats as $cat) {
	echo "<div class='catscroll'><a href='shopping/category/{$cat['url']}'>{$cat['name']}</a></div>";
}
?>
		</div><!-- end .items -->

	</div><!-- end #scrollable -->

	<!-- "next page" action -->
	<a class="next browse right"></a>
</div><!-- end #scroll-gallery -->

<script src="js/jquery.tools.min.js"></script>
<script>
$(document).ready( function() {
	$(".scrollable").scrollable();
	
  // Get the Scrollable control
  var scrollable = $(".scrollable").data("scrollable");

  // Set to the number of visible items
  var size = 1;

  // Handle the Scrollable control's onSeek event
  scrollable.onSeek(function(event, index) {

    // Check to see if we're at the end
    if (this.getIndex() >= this.getSize() - size) {

      // Disable the Next link
      $("a.next.browse.right").addClass("disabled");

    }

  });

  // Handle the Scrollable control's onBeforeSeek event
  scrollable.onBeforeSeek(function(event, index) {

    // Check to see if we're at the end
    if (this.getIndex() >= this.getSize() - size) {
      
      // Check to see if we're trying to move forward
      if (index > this.getIndex()) {

        // Cancel navigation
        return false;

      }

    }

  });
});
</script>