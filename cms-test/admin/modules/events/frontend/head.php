<?php
include_once('inc.php');
$base_url = $_config['site_path'];
global $base_url;

// 'list' set in events/system/config.php
if($_config['eventsview']=='list'){
	if(isset($_GET['eventsview']) && $_GET['eventsview']=='calendar') {
		$eventsview = 'calendar';
	} else {
		$eventsview = 'list';
	}
} 
// 'calendar' set in events/system/config.php
else { 
	if(isset($_GET['eventsview']) && $_GET['eventsview']=='list') {
		$eventsview = 'list';
	} else {
		$eventsview = 'calendar';
	}
}

// cases:
// no extra parameters: display all events
// one and only one extra parameter: an event page is selected 
// figure out the uri
$uri[1] = (isset($uri[1]) && $uri[1] == '' || ! isset($uri[1])) ? false : $uri[1];
$uri[2] = (isset($uri[2]) && $uri[2] == '' || ! isset($uri[2])) ? false : $uri[2];

$topper = "<div id='crust' class='longbread'>
	<div id='crumbtext' style='padding-left:2em;'>
		<div class='clear'></div>
		<h2>Calendar of Events<div id='setview'></div></h2>
	</div>
	<div class='clear' style='height:2em;'></div>
	</div>";
if($eventsview == 'list') {
	include('list-head.php');
} else {
	include('cal-head.php');
}
?>