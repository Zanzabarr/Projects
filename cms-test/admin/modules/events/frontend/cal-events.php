<?php
function buildRecent($recentEvents)
{
	global $base_url, $_config;
?>			
			<div id="recent-posts-container">
				<h2 class="subpage ">Upcoming Events</h2>
                <ul class="blog-list">
			<?php // build the recent list
			foreach($recentEvents as $rec)
			{ 	?>
				<li><a  class="subpage" href="<?php echo $_config['path']['events'] .$rec['url']; ?>"> <?php echo $rec['title']; ?> </a></li>
				<?php
			}
			?>
			</ul>
            </div><!-- recent-posts-container -->
<?php
}
//----------------------------begin html----------------------------------------------
// this section has been entered from /index.php and this is the beginning of:

/* CUSTOM SITE CONTENT TOP */
	echo $topper;

 /* END SITE CONTENT TOP */
 
 /* MODULE FRONTEND CONTENT */
	// make the module path available to js
	?>
	<input type="hidden" id="module_path" value="<?php echo $_config['admin_url'] . 'modules/events/events.php'; ?>" />
<div id="right-content">
	<div id="page-content">
		<div id='events-body'>
		<?php
		// display the appropriate page portions:
		if (isset($arOutput['singleEvent'])) {
			echo $arOutput['singleEvent'];
		}
		elseif (isset($arOutput['eventPag']))
		{
			echo $arOutput['eventPag'];
		}
		elseif (isset($arOutput['exportPag'])) {
			echo $arOutput['exportPag'];
		} else {
			echo $arOutput['catPag'];
		}
		?>
		</div><!-- end events body -->
	</div><!-- end page-content -->
<?php /* END MODULE FRONTEND CONTENT */ ?>
</div><!-- end right-content - from custom site content top-->
<script>
$(document).ready( function() {
	$('#setview').load('external/setview.php',{eventsview:'<?php echo $eventsview;?>'});
});
</script>