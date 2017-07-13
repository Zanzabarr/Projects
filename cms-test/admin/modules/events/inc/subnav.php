<div id="eventsnav">
    <ul>
        <li><a href="events.php" class='<?php echo $selectedEvents; ?>' >Events</a></li>
        <li><a href="events_category.php" class='<?php echo $selectedCats; ?>' >Categories</a></li>
		<li><a href="events_options.php" class='<?php echo $selectedOpts; ?>' >Options</a></li>
    </ul>
	<div id='eventsnavbtn'>
		<a class="blue button tipTop" title='Add an event to the list of events.' href="events_new.php?option=create">Add Event</a>
		<a class="blue button tipTop" title='Create a new category to put your events under.' href="events_category_new.php?option=create">Add Category</a>
		<div class='clearFix'></div>
	</div>	
</div>
