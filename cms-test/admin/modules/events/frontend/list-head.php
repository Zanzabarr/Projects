<?php
// get categories array
$cats = logged_query("SELECT * FROM `events_cat` WHERE `status` = '1' ORDER BY `title` ASC LIMIT 0 , 30",0,array());
$num_of_cats = $cats ? count($cats) : 0;

$recent_events = logged_query("SELECT * FROM `events` WHERE `status` = '1' ORDER BY `start_date` DESC LIMIT 5",0,array());
$num_of_recent = $recent_events ? count($recent_events) : 0;

$userPath = $base_url.'events';
$index = 1;
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

if ($uri[1] === false) {
        $arOutput = doEventsHome();
}
elseif ($uri[1] !== false && $uri[2] === false ) $arOutput = doEventsPage($uri[1]);
elseif ($uri[1] === "category" && $uri[2] !== false) $arOutput = doCategoryPage($uri[2]);

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
<?php $moduleLink = '<link rel="stylesheet" type="text/css" href="' . $_config['admin_url'] . 'modules/' . $module . '/frontend/list.css" /> <script type="text/javascript" src="'.$_config['admin_url'].'modules/'.$module.'/frontend/inc.js"></script>'; 

 //---------------------------------------------functions-----------------------------------------------------//
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
	$prev = array ('lastPage' => $_SESSION['events']['lastPage'], 'lastPart' => $lastPart);
	
	return array('eventPag' => false , 'singleEvent' => $singleEvent, 'seoData' => $seoData, 'eventData' => $result, 'prev' => $prev);
}

function doEventsHome()
{
	global $_config, $base_url;

    $events = logged_query("SELECT * FROM `events` WHERE `id` > 0 AND `status` = 1 AND `end_date` >= DATE_ADD(NOW(), INTERVAL -16 HOUR) ORDER BY `start_date` ASC",0,array());

    $eventPag = '';

    if($events):foreach($events as $event) {
        $eventPag .= buildEventsPage($event);    
    } endif;

    if(count($events) == 0) {
        $eventPag .= '<div class="entry-events">';
        $eventPag .= '<p>There Are No Events</p>';
        $eventPag .= '</div><!-- END entry-events -->';
    }

	$_SESSION['events']['userPath'] =  $base_url.'events';

	return array('eventPag' => $eventPag);
}

function doCategoryPage($category) {
	global $_config, $base_url;
	//get category id
	$cat = logged_query("SELECT * FROM `events_cat` WHERE `status` = '1' AND `url` = :category LIMIT 1",0,array(":category" => $category));
	$catid = isset($cat[0]['id']) ? $cat[0]['id'] : 'nomatch'; //substr($cat[0]['id'],1,-1);
	
	$events = logged_query("SELECT * FROM `events` WHERE `status` = 1 AND `cate` LIKE :catid AND `cate` != '.-1.' AND `end_date` >= DATE_ADD(NOW(), INTERVAL -16 HOUR) ORDER BY `start_date` ASC",0,array(":catid" => '%.'.$catid.'.%',));
	
	$catPag = '<h2>'.$cat[0]['title'].'</h2>';
	if($events) : foreach($events as $event) {
		$catPag .= buildEventsPage($event);
	}
    if(count($events) == 0) {
        $catPag .= '<div class="entry-events">';
        $catPag .= "<p>At this time, there are no upcoming {$cat[0]['title']}.</p>";
        $catPag .= '</div><!-- END entry-events -->';
    } endif;
	
	$_SESSION['events']['userPath'] = $base_url.'events';
	
	return array('catPag' => $catPag);
}

// get event by url
function getEvent($eventUrl)
{
	$event = logged_query("SELECT * FROM `events` WHERE `status` = 1 AND `id` > 0 AND `url` = :eventUrl LIMIT 1",0,array(":eventUrl" => $eventUrl));

	if (! $event || count($event) == 0 ) $event = false ;
	else $event = $event[0];

	return $event;
}

function buildEventPage( $result )
{
	global $base_url;
    
    $startDate = toEventDate(strtotime($result['start_date']));
    $endDate = toEventDate(strtotime($result['end_date']));

	$return ="
		<div class='eventcontent'>
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
            <p><div class='fixed'>Location:</div>".$result['location']."<br /><div class='fixed'>Date:</div>";
   
    if($result['custom_date'] == NULL || $result['custom_date'] == '') $return .= getFullEventDate($startDate, $endDate, $result['enable_time']);
    else $return .= $result['custom_date'];

    $return .="</p>
            <div style='clear:both'></div>
        </form>
        </div><!-- END entry-events -->
    ";

    return $return;
}
?>