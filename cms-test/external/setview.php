<?php
if(isset($_POST['eventsview']) && $_POST['eventsview']=='calendar') {
	$newview = 'list';
} else {
	$newview = 'calendar';
}
echo "<a id='viewlink' href='events?eventsview={$newview}' title='{$newview} view'>{$newview} view</a>";
?>
