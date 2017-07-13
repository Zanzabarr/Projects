<?php
function buildCatList($cats)
{ 
	global $base_url, $_config;

if (count($cats) > 0) 
	{ ?>
		<div id="category-list-container">		
			<h2 class="subpage ">Categories</h2>
            <ul class="blog-list">
			<li><a class="subpage" href="<?php echo $_config['path']['events'] ?>?eventsview=list">All Events  </a></li>
		<?php
		foreach($cats as $cat)
		{ ?>
			<li><a class="subpage" href="<?php echo $_config['path']['events'] .'category/'. $cat['url']; ?>?eventsview=list"> <?php echo $cat['title']; ?> </a></li>
		<?php  
		} ?>
        </ul>
		</div><!-- category-list-container -->
	<?php
	}
}

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
				<li><a  class="subpage" href="<?php echo $_config['path']['events'] .$rec['url']; ?>?eventsview=list"> <?php echo $rec['title']; ?> </a></li>
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
// make the module path available to js
?>

<input type="hidden" id="module_path" value="<?php echo $_config['admin_url'] . 'modules/events/'; ?>" />

<div id="sidebar" class="blog">

	<div id="int_menu">
		<!--<a href="<?php //echo $_config['path']['blog']; ?>" class="subpage active" ><h4>Peace Portal Uganda Blog</h4></a>-->
<?php


// if there is more than one category, build a catetory list
if ($num_of_cats > 0) buildCatList($cats);
else buildCatList(array());

// build most recent list
//if ($num_of_recent > 0) buildRecent($recent_events);

?>
		<div style="clear:both;line-height:0;"></div>
	</div>

</div>

<div id='blog-body'>
	
<?php

if(!isset($arOutput['singleEvent'])) {
    echo "<h1>Calendar of Events</h1>";
}

// display the appropriate page portions:
if (isset($arOutput['singleEvent'])) echo $arOutput['singleEvent'];
elseif (isset($arOutput['eventPag'])) 
{
	echo $arOutput['eventPag'];
} else {
	echo $arOutput['catPag'];
}

?>
</div><!-- end events body -->
<script>
$(document).ready( function() {
	$('#setview').load('external/setview.php',{eventsview:'<?php echo $eventsview;?>'});
});
</script>