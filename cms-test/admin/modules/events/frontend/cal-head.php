<?php

// get categories array
$cats = logged_query("SELECT * FROM `events_cat` WHERE `status` = '1' ORDER BY `title` ASC LIMIT 0 , 30",0,array());
$num_of_cats = $cats ? count($cats) : 0;

/*$recent_events = logged_query_assoc_array("SELECT * FROM `events` WHERE `status` = '1' ORDER BY `start_date` DESC LIMIT 5");
$num_of_recent = count($recent_events);*/

$userPath = $base_url.'events';
$index = 1;

/* date settings */
$month = (int)(isset($_GET['month']) ? $_GET['month'] : date('m'));
$year = (int)(isset($_GET['year']) ? $_GET['year'] : date('Y'));
if(isset($_GET['category'])) {
	$dateString = "?category={$_GET['category']}&eventsview=calendar";
}

if($uri[1]=="category") $dateString = "?category={$uri[2]}";

if(isset($_GET['month']) && isset($dateString)) {
	$dateString .= "&month={$month}&year={$year}&eventsview=calendar";
} elseif(isset($_GET['month']) && !isset($dateString)) {
	$dateString = "?month={$month}&year={$year}&eventsview=calendar";
} else {
	$dateString = "?eventsview=calendar";
}

/* build category dropdown */
function buildCatList($cats)
{
	global $base_url, $_config, $uri, $dateString;
	$catlist = '';
	
	if (count($cats) > 0) 
	{ 
		$catTitle = (!empty($uri[2])) ? $uri[2] : 'Categories';
		
		$catlist .= "<div id='cat-container'>
		<div class='cat-toggle'><img src='{$_config['admin_url']}modules/events/images/downarrow.png' /><span style='margin-left:1.5em;'>{$catTitle}</span></div>
		<ul class='catmenu'><li><a href='events{$dateString}'>All</a></li>";
		foreach($cats as $cat)
		{ 
			$selected = $cat['url'] == $uri[2] ? 'selected' : '';
			$getcolor = logged_query("SELECT * FROM events_colors WHERE cat_id = :cat_id",0,array(":cat_id" => $cat['id']) );
			if($getcolor) : foreach($getcolor as $row) {
					$bgcolor = $row['color'];
			}
			else : $bgcolor = "transparent";
			endif;
			$catlist .= "<li class='{$selected}'><span class='indicator' style='background:{$bgcolor};'>&nbsp;</span><a href='{$_config['path']['events']}category/{$cat['url']}{$dateString}'>{$cat['title']}</a></li>";
		}
        $catlist .= "</ul></div><!-- end cat-container -->";
		return $catlist;
	}
}

function buildExport($result = array()) {
	global $base_url, $_config, $uri, $dateString;
	$export = "<div id='exp-container'><div class='exp-toggle'><img src='{$_config['admin_url']}modules/events/images/downarrow.png' /><span style='margin-left:1.5em;'>Export</span></div>";
	$monthlist = array('January','February','March','April','May','June','July','August','September','October','November','December');
	$export .= "<ul class='expmenu'>";
	$googlebtn = "";
	$yahoobtn = "";
	$outlook = "";
	if(!empty($result)) {
		$eventtitle = $result['title'];
		
		$stdate = date("Ymd",strtotime($result['start_date']));
		$stdate .= "T".date("His",strtotime($result['start_date']));
		$edate = date("Ymd",strtotime($result['end_date']));
		$edate .= "T".date("His",strtotime($result['end_date']));
		
		$details = htmlspecialchars_decode($result['desc']);
		$location = $result['location'];
		$url = $result['url'];
		
		$googlebtn = "<a href='http://www.google.com/calendar/event?action=TEMPLATE&text=$eventtitle&dates=$stdate/$edate&details=$details&location=$location&trp=false&sprop=$location&sprop=name:$url' target='_blank'><img src='{$_config['admin_url']}modules/events/images/gcalendar-16.png' style='vertical-align:middle;' />&nbsp;Google Calendar</a>";
		
		$outlook = "<a href='http://calendar.live.com/calendar/calendar.aspx?rru=addevent&dtstart=$stdate&dtend=$edate&summary=$eventtitle&description=$details&location=$location' target='_blank'><img src='https://gfx6.hotmail.com/cal//11.00/updatebeta/ltr/OCalFav.ico' style='vertical-align:middle;' />&nbsp;&nbsp;Live Calendar</a>";
		
		$export .= "<li>$googlebtn</li><li>$outlook</li>";
	} else {
		$export .= "<li><a href='events/export{$dateString}'>Choose Dates &amp; Categories</a></li>";
	}
	$export .= "</ul></div>";
	
	return $export;
}

// build userPath
while(isset($uri[ $index ]))
{
	$userPath .= '/'.$uri[$index];
	$index++;
}
// record last visited for returning to page
if( isset($_SESSION['events']['userPath']) ) $_SESSION['events']['lastPage'] = $_SESSION['events']['userPath'];
$_SESSION['events']['userPath'] = $userPath;

if ( isset($_SESSION['events']['target']) && $_SESSION['events']['userPath'] != $_SESSION['events']['target'])
{
	unset($_SESSION['events']['target']);
	if(isset($_SESSION['events']['referrer'])) unset($_SESSION['events']['referrer']);
	if(isset($_SESSION['events']['referrerPart'])) unset($_SESSION['events']['referrerPart']);
}

//make calendar controls

/* KEEP SELECT CONTROLS - NOT NEEDED INITIALLY */
/* select month control 
$select_month_control = '<select name="month" id="month">';
for($x = 1; $x <= 12; $x++) {
	$select_month_control.= '<option value="'.$x.'"'.($x != $month ? '' : ' selected="selected"').'>'.date('F',mktime(0,0,0,$x,1,$year)).'</option>';
}
$select_month_control.= '</select>';

/* select year control 
$year_range = 7;
$select_year_control = '<select name="year" id="year">';
for($x = ($year-floor($year_range/2)); $x <= ($year+floor($year_range/2)); $x++) {
	$select_year_control.= '<option value="'.$x.'"'.($x != $year ? '' : ' selected="selected"').'>'.$x.'</option>';
}
$select_year_control.= '</select>';*/
/** SELECT CONTROLS NOT NEEDED INITIALLY **/

/* "next month" control */
$next_month_link = '<a href="'.rtrim($userPath,'/').'?eventsview=calendar&month='.($month != 12 ? $month + 1 : 1).'&year='.($month != 12 ? $year : $year + 1).'" class="control"><img src="'.$_config['admin_url'].'modules/events/images/next-arrow.png" /></a>';

/* "previous month" control */
$previous_month_link = '<a href="'.rtrim($userPath,'/').'?eventsview=calendar&month='.($month != 1 ? $month - 1 : 12).'&year='.($month != 1 ? $year : $year - 1).'" class="control"><img src="'.$_config['admin_url'].'modules/events/images/prev-arrow.png" /></a>';


/* bringing the controls together */
$controls = '<form id="calcontrols" method="get">'.$previous_month_link.'<div id="monthyear"><h2>'.date('F',mktime(0,0,0,$month,1,$year)).'&nbsp;&nbsp;&nbsp;'.$year.'</h2></div>'.$next_month_link.'&nbsp;&nbsp;</form>';


$caltop = '<div id="controlbar">'.$controls;
$caltop .= '<div class="mobile-clear"></div>';
$caltop .= buildCatList($cats);
$caltop .= '<div class="mobile-mini-clear"></div>';
$caltop .= buildExport();
$caltop .= '<div class="mobile-mini-clear"></div>';
$caltop .= '<button type="button" id="print-btn"><span class="print-text">PRINT</span></button><div class="mini-clear"></div><div class="mini-clear"></div></div><div class="mini-clear"></div>';

/* START MAKING EVENTS PAGES */
if ($uri[1] === false) {
        $arOutput = doEventsHome();
}
elseif ($uri[1] !== false && $uri[1] !== "export" && $uri[2] === false && !isset($day) ) $arOutput = doEventsPage($uri[1]);
elseif ($uri[1] === "category" && $uri[2] !== false) $arOutput = doCategoryPage($uri[2]);
elseif ($uri[1] === "export" && $uri[2] === false) {
	if(!isset($_GET['exsubmit'])) {
		$arOutput = doExportPage();
	} else {
		$arOutput = doFullExport();
	}
}

else $arOutput = doEventsHome();

// now that we've had a chance to be redirected from malformed destinations:
// set last page's parts
if (isset($_SESSION['events']['part']) ) $_SESSION['events']['lastPart'] = $_SESSION['events']['part'];
elseif (isset($_SESSION['events']['lastPart'])) unset($_SESSION['events']['lastPart']);

// find out if there was a part assigned : set the part,
// otherwise, unset the part (if it was previously set)
if(isset($_GET['part']) && is_numeric($_GET['part'])) 
	$_SESSION['events']['part'] = $_GET['part'];
elseif ( isset($_SESSION['events']['part']) ) unset($_SESSION['events']['part']);



// sort data and perform redirects as needed
$seoData = 	isset($arOutput['seoData']) ? 
				$arOutput['seoData'] : 
				array(	'seo_title' => 'Events', 
						'seo_description' => 'Events', 
						'seo_keywords' => 'Events' 
				);
// set seo data for use in the main head
$seot = $seoData['seo_title'];
$seod = $seoData['seo_description'];
$seok = $seoData['seo_keywords'];

?>
<!-- events module includes -->
<?php $moduleLink = '<link rel="stylesheet" type="text/css" href="' . $_config['admin_url'] . 'modules/' . $module . '/frontend/style.css" /> <script type="text/javascript" src="'.$_config['admin_url'].'modules/'.$module.'/frontend/inc.js"></script><script type="text/javascript" src="'.$_config['admin_url'].'modules/'.$module.'/js/printThis.js"></script>';

  //---------------------------------------------functions-----------------------------------------------------//
function dayDiff($start, $end) {
	return floor((strtotime($end)-strtotime($start))/86400);
}

/* draws a calendar */
function draw_cal($month,$year,$eventlist = array()) {
	$calendar = '<div class="calendar">';
	
	// days and weeks 
	$running_day = date('w',mktime(0,0,0,$month,1,$year));
	$days_in_month = date('t',mktime(0,0,0,$month,1,$year));
	$today = date('Y-m-d');
	
	/* print "blank" days until the first of the month */
	for($x = 0; $x < $running_day; $x++):
		$calendar.= '<div class="calendar-day non-day"> </div>';
	endfor;

	// days 
	for($list_day = 1; $list_day <= $days_in_month; $list_day++):
		$day_of_week = date('D',mktime(0,0,0,$month,$list_day,$year));
		$longtip = date('l, d F Y',mktime(0,0,0,$month,$list_day,$year));
		$event_day = date('Y-m-d',strtotime($year.'-'.$month.'-'.$list_day));
		$iftoday = ($today == $event_day) ? " today" : "";
		
		$calendar.= "<div class='calendar-day{$iftoday}'>";
			// add in the "MON | 23" etc...
			$calendar.= '<div class="day-number" title="'.$longtip.'">'.$day_of_week.'&nbsp;&nbsp;|&nbsp;&nbsp;'.$list_day.'</div>';
			
			// day content
			$calendar.= '<div class="day-list">';
			
			if(isset($eventlist[$event_day])) {
				foreach($eventlist[$event_day] as $event) {
				$catid = substr($event['cate'],1,-1);
				$getcat = logged_query("SELECT * FROM events_cat WHERE id = :catid",0,array(":catid" => $catid) );
				if($getcat) : foreach($getcat as $row) {
					$catname = $row['title'];
				} endif;
				$getcolor = logged_query("SELECT * FROM events_colors WHERE cat_id =  :catid",0,array(":catid" => $catid) );
				if($getcat) : foreach($getcolor as $row) {
					$bgcolor = $row['color'];
				}
				else : $bgcolor = "transparent";
				endif;
					// the popup for each event (can be several in a day)
					$poptext = htmlspecialchars_decode($event['desc']);
					$popml = '
					<span class="pop">
						<span class="poptitle">
							<p>'.$event['title'].'</p>
							<p><a href="events/'.$event['url'].'">Event Details</a></p>
						</span>
						<span class="popbody">
							<p>Location: '.$event['location'].'</p>
							'.$poptext.'
						</span>
					</span>';
					
					
					// date square holding popup
					$indicator = ($bgcolor == "transparent") ? "" 
					: '<span style="font-size: 18px;color:'.$bgcolor.';">&#x25a0;</span>';
					$calendar.= "
					<div class=\"event\" title=\"{$event['title']} \nClick for more information\">
						{$indicator}
						{$event['title']}
						{$popml}
					</div><!-- end event-->";
				}
			}
			//else {
			//	$calendar.= str_repeat('<p></p>',2);
			//}
			
		$calendar.= '</div><!-- end day-list--></div><!-- end calendar-day -->';
		$running_day++;
	endfor;
	
	
	// close it up 
	$calendar .= '</div><!-- end calendar --><div class="mini-clear"></div>';
	return $calendar;
}

function doFullExport() {
	global $_GET, $_config, $base_url, $cats;
	
	$stDate = date('Y-m-d',strtotime($_GET['stMonth'].'/'.$_GET['stDay'].'/'.$_GET['stYear']));
	$endDate = date('Y-m-d',strtotime($_GET['endMonth'].'/'.$_GET['endDay'].'/'.$_GET['endYear']));
	$pgstDate = date('F j, Y',strtotime($stDate));
	$pgendDate = date('F j, Y',strtotime($endDate));
	$exCat = "All";
	$getcolor = logged_query("SELECT * FROM events_colors",0,array());
	$bgcolor = array();
	if($getcolor):foreach($getcolor as $row) {
		$bgcolor['.'.$row['cat_id'].'.'] = $row['color'];
	}endif;
	
	foreach($cats as $scat) {
		if($scat['id']==$_GET['exCat']) $exCat = $scat['title'];
	}
	if($exCat != "All") {
		$events = logged_query("SELECT * FROM `events` WHERE `id` > 0 AND `status` = 1 AND `cate` LIKE :exCat AND `start_date` BETWEEN :stDate AND :endDate",0,array(
			":exCat" => '%.'.$_GET['exCat'].'.%',
			":stDate" => $stDate,
			":endDate" => $endDate
		));
		$catcolor = $bgcolor['.'.$_GET['exCat'].'.'];
	} else {
		$events = logged_query("SELECT * FROM `events` WHERE `id` > 0 AND `status` = 1 AND `start_date` BETWEEN :stDate AND :endDate",0,array(
			":stDate" => $stDate,
			":endDate" => $endDate
		));
		$catcolor = "none";
	}
	
	$export = "<div id='controlbar'><h2 style='color:#fff;margin:0;'>Export Dates: {$pgstDate} - {$pgendDate}<span style='margin-left:2em;'><span class='indicator' style='background:{$catcolor};'>&nbsp;</span>{$exCat}</span></h2><div class='clear' style='height:.4em;'></div></div><div class='clear'></div><table id='exportTable'>";
	foreach($events as $event) {
		$catcolor = $bgcolor[$event['cate']];
		$export .= "<tr><td><span class='indicator' style='background:{$catcolor};margin-bottom:4px;'>&nbsp;</span></td><td colspan=2 style='font-weight:600;'>".$event['title']."</td></tr><tr><td>Begin:</td><td>".date('F j, Y',strtotime($event['start_date']))."</td><td class='timecell'>".date('h:i a',strtotime($event['start_date']))."</td></tr><tr style='border-bottom:1px solid #ebebeb;'><td>End:</td><td>".date('F j, Y',strtotime($event['end_date']))."</td><td class='timecell'>".date('h:i a',strtotime($event['end_date']))."</td></tr><tr><td colspan=3></td></tr>";
	}
	$export .= "</table>";
	//make button to create downloadable file? - ajax?
	$expFile = makeICalendar($events);
	$export .= "<div style='float:right;margin-right:2em;'><a href='events?eventsview=calendar'>Return to Calendar</a><div class='clear'></div><a href='$expFile'>Download iCalendar File</a></div>";
	return array('exportPag' => $export);
}

function doExportPage() {
	global $_config, $base_url, $cats, $month, $year;
	
	$previous_month_link = '<a href="events" class="control"><img src="'.$_config['admin_url'].'modules/events/images/prev-arrow.png" /></a>';
	$controls = $previous_month_link . '<div id="monthyear"><h2>Reset Calendar</h2></div>';
	//put it together
	$caltop = '<div id="controlbar">'.$controls;
	$caltop .= '<div class="clear" style="height:.4em;"></div></div>';
	
	//load array of months
	$listmonths = array();
	for($i=1;$i<=12;$i++) {
		$listmonths[] = date("F",strtotime($i."/01/2013"));
	}
	/**/
	
	$export = $caltop."<div class='clear'></div><form id='frmExport' method='get' action=''><input type='hidden' name='eventsview' value='calendar'><fieldset><legend>Date Range</legend><label for=stMonth>Start Date</label><select id='stMonth' name='stMonth'>";
	foreach($listmonths as $m) {
		$selected = ($m == date('F',strtotime($month.'/1/2013'))) ? " selected" : "";
		$monthoption = date('m',strtotime($m." 1, 2013"));
		$export .= "<option value='".$monthoption."'{$selected}>{$m}</option>";
	}
	$export .= "</select><select id='stDay' name='stDay'>";
	//days
	for($d=1;$d<=31;$d++) {
		$export .= "<option value='{$d}'>{$d}</option>";
	}
	$export .= "</select><select id='stYear' name='stYear'>";
	$lastyear = date('Y',strtotime('last year'));
	//years
	for($y=0;$y<=4;$y++) {
		$newyear = $lastyear + $y;
		$selected = ($newyear == date('Y',strtotime('3/1/'.$year))) ? " selected='selected'" : "";
		$export .= "<option value='{$newyear}'{$selected}>{$newyear}</option>";
	}
	$export .= "</select><div class='clear'></div>";
	
	//same as above but for end date
	$export .= "<label for=endMonth>End Date  </label><select id='endMonth' name='endMonth'>";
	//months
	foreach($listmonths as $m) {
		$selected = ($m == date('F',strtotime($month.'/1/2013'))) ? " selected" : "";
		$monthoption = date('m',strtotime($m." 1, 2013"));
		$export .= "<option value='".$monthoption."'{$selected}>{$m}</option>";
	}
	$export .= "</select><select id='endDay' name='endDay'>";
	//days
	for($d=1;$d<=31;$d++) {
		/* select last day of month */
		$selected = ($d == cal_days_in_month(CAL_GREGORIAN, $month, $year)) ? " selected='selected'" : "";
		$export .= "<option value='{$d}'{$selected}>{$d}</option>";
	}
	$export .= "</select><select id='endYear' name='endYear'>";
	$lastyear = date('Y',strtotime('last year'));
	//years
	for($y=0;$y<=4;$y++) {
		$newyear = $lastyear + $y;
		$selected = ($newyear == date('Y',strtotime('3/1/'.$year))) ? " selected='selected'" : "";
		$export .= "<option value='{$newyear}'{$selected}>{$newyear}</option>";
	}
	$export .= "</select></fieldset>";
	
	//category
	$selcat = (isset($_GET['category'])) ? $_GET['category'] : ""; //get selected category if available

	$export .= "<fieldset><legend>Category</legend><select id='exCat' name='exCat'><option value='all'>All</option>";
	foreach($cats as $cat) {
		$selected = $selcat == $cat['title'] ? " selected='selected'" : "";
		$export .= "<option value='{$cat['id']}'{$selected}>{$cat['title']}</option>";
	}
	$export .= "</select></fieldset>";
	
	//close it up and complete form
	$export .= "<div class='clear'></div><input type='submit' name='exsubmit' value='Export' /></form>";
	return array('exportPag' => $export);
}

function doEventsPage($eventUrl)
{
	global $_config, $base_url;
	
	// validate the event page
	$result = getEvent($eventUrl);
	if ( $result === false ) //event doesn't exist
	{
		return doEventsHome();
	}
	
	if (isset($_POST['target']) && isset($_POST['referrer']))
	{
		$_SESSION['events']['target'] = $_POST['target'];
		$_SESSION['events']['referrer'] = $_POST['referrer'];
		$_SESSION['events']['referrerPart'] = $_POST['referrerPart'];
	}
	
	// build the event page
	$singleEvent = buildEventPage($result);
	$seoData = array('seo_title'        => htmlspecialchars($result['seo_title']),
                    'seo_description'   => htmlspecialchars($result['seo_description']),
                    'seo_keywords'      => htmlspecialchars($result['seo_keywords'])
    );


	// set the last part data: used to create back button
	$lastPart = isset($_SESSION['events']['part']) ? $_SESSION['events']['part'] : false;
	
	if(!isset($_SESSION['events']['lastPage'])) $_SESSION['events']['lastPage'] = "";
	$prev = array ('lastPage' => $_SESSION['events']['lastPage'], 'lastPart' => $lastPart);
	
	return array('eventPag' => false , 'singleEvent' => $singleEvent, 'seoData' => $seoData, 'eventData' => $result, 'prev' => $prev);
}

function doEventsHome()
{
	global $_config, $base_url, $caltop, $month, $year;

    $events = logged_query("SELECT * FROM `events` WHERE `id` > 0 AND `status` = 1 ORDER BY `start_date` ASC",0,array());

	$eventPag = $caltop;
	$eventlist = array();
    foreach($events as $event) {
		$event_date = date('Y-m-d',strtotime($event['start_date']));
		$end_date = date('Y-m-d',strtotime($event['end_date']));
		$diff = dayDiff($event_date,$end_date);
		if($diff < 1) {
			$eventlist[$event_date][] = $event;
		} else {
			$eventlist[$event_date][] = $event;
			for($i=1;$i<=$diff;$i++) {
				$newdate = strtotime("{$event['start_date']} + $i days");
				$newday = date('Y-m-d',$newdate);
				$eventlist[$newday][] = $event;
			}
		}
    }
	
	$eventPag .= draw_cal($month,$year,$eventlist);
	$_SESSION['events']['userPath'] =  $base_url.'events';
	return array('eventPag' => $eventPag);
}

function doCategoryPage($category) {
	global $_config, $base_url, $caltop, $month, $year;
	//get category id
	$cat = logged_query("SELECT * FROM `events_cat` WHERE `status` = '1' AND `url` = :category LIMIT 1",0,array(":category" => $category));
	$catid = isset($cat[0]['id']) ? $cat[0]['id'] : 'nomatch'; //substr($cat[0]['id'],1,-1);
	
	$events = logged_query("SELECT * FROM `events` WHERE `status` = 1 AND `cate` LIKE '%$catid%' AND `cate` != '.-1.' ORDER BY `start_date` ASC",0,array());
	
	$catPag = $caltop;
	$eventlist = array();
    if($events): foreach($events as $event) {
		$event_date = date('Y-m-d',strtotime($event['start_date']));
		$end_date = date('Y-m-d',strtotime($event['end_date']));
		$diff = dayDiff($event_date,$end_date);
		if($diff < 1) {
			$eventlist[$event_date][] = $event;
		} else {
			$eventlist[$event_date][] = $event;
			for($i=1;$i<=$diff;$i++) {
				$newdate = strtotime("{$event['start_date']} + $i days");
				$newday = date('Y-m-d',$newdate);
				$eventlist[$newday][] = $event;
			}
		}
    }endif;
	
    $catPag .= draw_cal($month,$year,$eventlist);
	$_SESSION['events']['userPath'] = $base_url.'events';
	return array('catPag' => $catPag);
}

// get event by url
function getEvent($eventUrl)
{

	$event = logged_query("SELECT * FROM `events` WHERE `status` = 1 AND `id` > 0 AND `url` =:eventUrl LIMIT 1",0,array(":eventUrl" => $eventUrl) );

	if (!$event || count($event) == 0 ) $event = false ;
	else $event = $event[0];

	return $event;
}

function buildEventPage( $result )
{
	global $_config, $base_url, $cats;
    
    $startDate = toEventDate(strtotime($result['start_date']));
    $endDate = toEventDate(strtotime($result['end_date']));

$previous_month_link = '<a href="events" class="control"><img src="'.$_config['admin_url'].'modules/events/images/prev-arrow.png" /></a>';
$controls = $previous_month_link . '<div id="monthyear"><h2>Events Calendar</h2></div>';


$caltop = '<div id="controlbar">'.$controls;
$caltop .= '<div class="mobile-clear"></div>';
$caltop .= buildCatList($cats);
$caltop .= '<div class="mobile-mini-clear"></div>';
$caltop .= buildExport($result);
$caltop .= '<div class="mobile-mini-clear"></div>';
$caltop .= '<button type="button" id="print-btn"><span class="print-text">PRINT</span></button><div class="mini-clear"></div><div class="mini-clear"></div></div><div class="mini-clear"></div>';

	$return = $caltop."
		<div class='eventcontent'><div class='clear'></div>
            <h1>".$result['title']."</h1>
            <p><strong>Location:</strong> ".$result['location']."</p>
            <p><strong>Date:</strong> ";
   
    if($result['custom_date'] == NULL || $result['custom_date'] == '') $return .= getFullEventDate($startDate, $endDate, $result['enable_time']);    
    else $return .= $result['custom_date'];

    $return .="</p>
            <h2 id=\"desc-header\">Event Details</h2>
			<p>".htmlspecialchars_decode($result['desc'])."</p>
            <div style='clear:both'></div>
		</div>
	";	
	return $return;
}

function buildEventsPage( $result, $source  = NULL)
{
    global $base_url;

    $startDate = toEventDate(strtotime($result['start_date']));
    $endDate = toEventDate(strtotime($result['end_date']));

    $return ="
        <div class='entry-events'>
        <form action='".$base_url."events/".$result['url']."' method='post' enctype='application/x-www-form-urlencoded' >
            <input type='hidden' name='target' value ='events/".$result['url']."' />
            <input type='hidden' name='referrer' value ='".$_SESSION['events']['userPath']."' />
            <input type='hidden' name='referrerPart' value =\"";

    if(isset($source['part'])) {
        $return .= $source['part'] . "\" />";  
    }
    else {
        $return .= "'1'\" />";
    }

    $return .="
            <input class='postTitle' type='submit' value='".$result['title']."' />
            <p style='margin-top:.5em;'><div style='line-height:1.3;'>Location:&nbsp;".$result['location']."</div><div style='line-height:1.3;'>Date:&nbsp;";
   
    if($result['custom_date'] == NULL || $result['custom_date'] == '') $return .= getFullEventDate($startDate, $endDate, $result['enable_time']);
    else $return .= $result['custom_date'];

    $return .="</div></p>
            <div style='clear:both'></div>
        </form>
        </div><!-- END entry-events -->
    ";

    return $return;
}

?>