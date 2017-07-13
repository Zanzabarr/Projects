<?php 
// initialize the page
$headerComponents = array();
$headerModule = 'events';
include('../../includes/headerClass.php');
include('inc/functions.php');

$pageInit = new headerClass($headerComponents,$headerModule);

$baseUrl = $_config['admin_url'];
$categories = getCategoriesData();
?>

<?php

$newEvent = false;

if ( array_key_exists('option', $_GET) && ($_GET['option'] == 'create') ) 
{
	$newEvent = true;
	$success = 0;
	$result = logged_query('
		SELECT MAX( CAST( SUBSTRING( `url` , 5 ) AS Unsigned ) ) AS num
		FROM `events`
		WHERE `id` >0
		AND `url` REGEXP "^new_[0-9]*$"',0,array()
	);
	$slug_num = is_array($result) && count($result) ? (int)$result[0]['num'] : 0;
	$tmp_slug = 'new_' . ++$slug_num;
	$result = logged_query("INSERT INTO `events` SET title=:tmp_slug, url=:tmp_slug, `cate`='.-1.'",0,array(":tmp_slug" => $tmp_slug));
	$event_id = $_config['db']->getLastInsertId();
	$list = logged_query("SELECT * FROM `events` WHERE `id` =:event_id",0,array(":event_id" => $event_id));
	$list = $list[0];
}

// if this is an edit of an existing event, get the event info
elseif (array_key_exists('eventid', $_GET) && is_numeric($_GET['eventid']) && ! array_key_exists('option', $_GET) ) 
{
	$new = logged_query("SELECT * FROM events WHERE id = :eventid ORDER BY id DESC LIMIT 1;",0,array(":eventid" => $_GET['eventid'])); 
	if($new === false || !count($new))
	{
		header( "Location: " . $_config['admin_url'] . "modules/events/events.php" );
		exit;
	}
	$list = $new[0];

    if(isset($list['start_date']) && $list['start_date'] != NULL) {
        // parse start_date data
        if($list['start_date'] instanceof DateTime) {
            $startDateTime = $list['start_date']->getTimestamp();
        } else {
            $startDateTime = strtotime($list['start_date']);
        }

        $startDateTime = explode(" ", date("Y-m-d g:i A", $startDateTime));
    
        $startDate = $startDateTime[0];
        $startTime = explode(":", $startDateTime[1]);
        $startMeridiem = $startDateTime[2];

        $list['start_date'] = $startDate;
        $list['start_hour'] = $startTime[0];
        $list['start_minute'] = ltrim($startTime[1], "0");
        $list['start_meridiem'] = $startMeridiem;
    }
    
    if(isset($list['end_date']) && $list['end_date'] != NULL) {
        // parse end_date data
        if($list['end_date'] instanceof DateTime) {
            $endDateTime = $list['end_date']->getTimestamp();
        } else {
            $endDateTime = strtotime($list['end_date']);
        }

        $endDateTime = explode(" ", date("Y-m-d g:i A", $endDateTime));
    
        $endDate = $endDateTime[0];
        $endTime = explode(":", $endDateTime[1]);
        $endMeridiem = $endDateTime[2];

        $list['end_date'] = $endDate;
        $list['end_hour'] = $endTime[0];
        $list['end_minute'] = ltrim($endTime[1], "0");
        $list['end_meridiem'] = $endMeridiem;
    }

    if(isset($list['custom_date']) && $list['custom_date'] != '') $list['override_date'] = 1;
    else $list['override_date'] = 0;

    $cats = explode(".", $list['cate']);
	
	$event_id = $_GET['eventid'];
}
// if we aren't editing or creating either, we shouldn't be here
elseif (! array_key_exists('option', $_GET) ) 
{
	header( "Location: " . $_config['admin_url'] . "modules/events/events.php" );
	exit;
}	


#==================
# process event info
#==================
if(isset($_POST['submit-event'])) {

	// validate:
	//	pick error type/messages based on if its status is Draft or Published
	if ($_POST['status'] == 1) // Published: thus error
	{
		$errorMsgType = 'errorMsg';
		$errorType = 'error';
		$errorHeading = 'Published Version';
		$errorMessage = 'all fields must be complete, saved as Draft';
	}
	else  // Draft: thus warning
	{
		$errorMsgType = 'warningMsg';
		$errorType = 'warning';
		$errorHeading = 'Draft Version';
		$errorMessage = 'saved with incomplete fields on page';
	}

	$list = array();
	
	foreach($_POST as $k=>$v)
	{
			if($k == 'content') ${$k} = trim(htmlspecialchars($v, ENT_QUOTES));
			else {
                if(is_array($v)) {
                    // value is an array
                    foreach($v as $value) {
                       trim(htmlspecialchars($value, ENT_QUOTES));
                    }

                    ${$k} = $v;
                }
                else {
                    ${$k} = trim(htmlspecialchars($v, ENT_QUOTES));
                }
			}

            $list[$k] = ${$k};
			if (${$k} == '' && $k != 'custom_date' && $k != 'submit-event' && $k != 'event_id' && $k != 'eventid' && $k != 'cate' && $k != 'seo_title' && $k != 'seo_description' && $k != 'seo_keywords' && $k != 'start_hour' && $k != 'start_minute' && $k != 'start_meridiem' && $k != 'end_date' && $k != 'end_hour' && $k != 'end_minute' && $k != 'end_meridiem') {$message['inline'][$k] = array ('type' => $errorMsgType, 'msg' => 'Required field');}
			
			//deal with default times
//			if($k == 'end_date') {
//				$end_date = $list['start_date'];
//				$list['end_date'] = $end_date;
//			}
    }
            
    if (isset($_POST['override_date']) && $_POST['override_date'] == '1') {
            // if override date is enabled, a custom date must be given	
            if($_POST['custom_date'] == '') {
                $message['inline']['custom_date'] = array('type' => $errorMsgType, 'msg' => 'Required field');
            }
    }	
    else {
        // date not overriden. Clear custom date value if one exists
        $list['custom_date'] = '';
        $custom_date = '';
    }
	
    if (!isset($_POST['enable_time'])) {
        // time not enabled, set custom times for date expiry calculation purposes
        $start_hour = $end_hour = $list['start_hour'] = $list['end_hour'] = '11';
        $start_minute = $end_minute = $list['start_minute'] = $list['end_minute'] = '55';
        $start_meridiem = $end_meridiem = $list['start_meridiem'] = $list['end_meridiem'] = 'PM';

        $list['enable_time'] = 0;
        $enable_time = 0;
    }

	$list['desc'] = $list['content'];

    // validate uniqueness of url
    // get all existing events
    $events = getEventsData();
    $eventTitles = array();
    $eventUrls = array();

    foreach ($events as $event)
    {
        $eventUrls[$event['url']] = $event;
    }

    // while not unique, append!
    $namingError = false;

    while (array_key_exists($url, $eventUrls) && ($_GET['option'] == 'create' || $eventUrls[$url]['id'] != $event_id)) 
    {
        $namingError = true;
        $url .= '_';
        $list['url'] = $url;
        $message['inline']['url'] = array ('type' => 'errorMsg', 'msg' => 'Url altered to be unique');
    }
    
    // if renaming occured, set alerts
    if ($namingError)
    {
        $errorMsgType = 'errorMsg';
        $errorType = 'error';
        $errorHeading = 'Naming Error';
        $errorMessage = 'event url already exists, saved as draft with modified names';
    }

    // set category data
    $cats = array();

	if(isset($_POST['cate'])) {
    	foreach($_POST['cate'] as $c=>$i) {
       		array_push($cats, $i);
    	}

    	$cleancate = '.';
    	$cleancate .= implode(".", $cats);
    	$cleancate .=  '.';
	}
	else {
		$cleancate = '.-1.';	
	}

    // set start_date data
    if($start_meridiem == "AM") {
        if((int)$start_hour == 12) {
            $start_hour = "0";
        }

        $start_hour = "0" . $start_hour;
    }
    else {
        // PM so convert to 24 hour clock
        if((int)$start_hour != 12) {
            $start_hour = ((int)$start_hour) + 12;
            $start_hour = "$start_hour";
        }
    }
    
    if((int)$start_minute < 10) {
        $start_minute = "0" . $start_minute;
    }

    $startDateTime = $start_date . " " . $start_hour . ":" . $start_minute . ":00";
    
    // set end_date data
    if($end_meridiem == "AM") {
        if((int)$end_hour == 12) {
            $end_hour = "0";
        }
		if($end_hour < 10) {
			$end_hour = "0" . $end_hour;
		}
    }
    else {
        // PM so convert to 24 hour clock
        if((int)$end_hour != 12) {
            $end_hour = ((int)$end_hour) + 12;
            $end_hour = "$end_hour";
        }
    }
    
    if((int)$end_minute < 10) {
        $end_minute = "0" . $end_minute;
    }

    $endDateTime = $end_date . " " . $end_hour . ":" . $end_minute . ":00";

    $dateError = false;

    if(strtotime($startDateTime) > strtotime($endDateTime)) {
        // error, start date later than end date
        $dateError = true;

        $errorMsgType = 'errorMsg';
        $errorType = 'error';
        $errorHeading = 'Date Error';
        $errorMessage = 'End date is earlier than start date, saved as draft';
    }	

	// if an error was found, create the error banner
	if ($errorsExist = count(isset($message['inline']) ? $message['inline'] : array()) || $dateError)
	{
		$message['banner'] = array ('heading' => $errorHeading, 'message' => $errorMessage, 'type' => $errorType);
		$status = $list['status'] = 0; // saving as Draft
	}
	else // set the success message
	{
		$message['banner'] = array ('heading' => 'Successfully Saved', 'type' => 'success');
	}

    // save even if errors exist: but save as draft
	if (array_key_exists('option', $_GET) && ($_GET['option'] == 'create'))
	{
		$result = logged_query("INSERT INTO `events` (`title`, `url`, `cate`, `location`, `desc`, `status`, `seo_title`, `seo_keywords`, `seo_description`, `start_date`, `end_date`, `enable_time`, `custom_date`) 
VALUES (:title, :url, :cleancate, :location, :content, :status, :seo_title, :seo_keywords, :seo_description, :startDateTime, :endDateTime, :enable_time, :custom_date);",0,array(
			":title" => $title,
			":url" => $url,
			":cleancate" => $cleancate,
			":content" => $content,
			":location" => $location,
			":status" => $status,	
			":seo_title" => $seo_title,	
			":seo_keywords" => $seo_keywords,	
			":seo_description" => $seo_description,	
			":startDateTime" => $startDateTime,	
			":endDateTime" => $endDateTime,	
			":enable_time" => $enable_time,	
			":custom_date" => $custom_date
		)); 
		$event_id = $_config['db']->getLastInsertId();

		// banners: if there was an error, overwrite the previously set success message
		if ($result === false)	$message['banner'] = array ('heading' => 'Error Saving Event', 'message' => 'there was an error writing to the database', 'type' => 'error' );
        
		// successfully created event: no longer a new event!
		$newEvent = false;
	}
	elseif (array_key_exists('option', $_GET) && ($_GET['option'] == 'edit'))
	{
        $event_id = $_GET['eventid'];

		$result = logged_query("UPDATE `events` SET 
		`title` = :title, 
		`url` = :url,
		`cate` =:cleancate,
        `location` = :location,
		`desc` = :content,
		`status` = :status,
		`seo_title` = :seo_title,
		`seo_keywords` = :seo_keywords,
		`seo_description` = :seo_description,
		`start_date` = :startDateTime,
		`end_date` = :endDateTime,
        `enable_time` = :enable_time,
        `custom_date` = :custom_date
         WHERE `id` = :event_id LIMIT 1;",0,array(
		 	":title" => $title,
			":url" => $url,
			":cleancate" => $cleancate,
			":content" => $content,
			":location" => $location,
			":status" => $status,	
			":seo_title" => $seo_title,	
			":seo_keywords" => $seo_keywords,	
			":seo_description" => $seo_description,	
			":startDateTime" => $startDateTime,	
			":endDateTime" => $endDateTime,	
			":enable_time" => $enable_time,	
			":custom_date" => $custom_date,	
			":event_id" => $event_id
		 ));
			
		// banners
		// banners: if there was an error, overwrite the previously set success message
		if ($result === false)	$message['banner'] = array ('heading' => 'Error Saving Event', 'message' => 'there was an error writing to the database', 'type' => 'error' );
	}
}	

if (! isset($message)) $message=array();

// title
$input_title = new inputField('Title', 'title');
$input_title->toolTip('Title as it appears in the events list');
$input_title->value(isset($list['title']) ? $list['title'] : '');
$input_title->counterMax(100);
$input_title->size('small');
$input_title->arErr($message);


// url
$input_url = new inputField('URL', 'url');
$input_url->toolTip('Title as it appears in the url:<br />Use only letters, numbers, underscores and hyphens. No Spaces!');
$input_url->value(isset($list['url']) ? $list['url'] : '');
$input_url->counterMax(100);
$input_url->size('small');
$input_url->arErr($message);


// category
$input_cate = new inputField('Category', 'cate');
$input_cate->toolTip('Select the category, if any, to which this event belongs.<br />Hold down CTRL to select more than one.');
$input_cate->type('multiselect');
$input_cate->selected(isset($cats) ? $cats : '');
$input_cate->option(-1, 'No Category' );

foreach($categories as $cat)
{           
    $input_cate->option($cat['id'], $cat['title']);
}

$input_cate->arErr($message);


// location
$input_location = new inputField('Location', 'location');
$input_location->toolTip('Location for the event');
$input_location->value(isset($list['location']) ? $list['location'] : '');
$input_location->counterMax(100);
$input_location->size('small');
$input_location->arErr($message);


// status
$input_status = new inputField( 'Status', 'status' );	
$input_status->toolTip('A draft version can have blank fields. A published version must have all fields completed.');
$input_status->type('select');
$input_status->selected(isset($list['status']) ? $list['status'] : '');
$input_status->option( 0, 'Draft' );
$input_status->option( 1, 'Published' );
$input_status->arErr($message);


/* Date Inputs */

// date override checkbox
$input_override_date = new inputField('Override Date', 'override_date');
$input_override_date->toolTip('Check this box to override the standard date display, and show a custom date.');
$input_override_date->type('checkbox');
$input_override_date->value(isset($list['override_date']) ? $list['override_date'] : '0');
$input_override_date->arErr($message);

// enable time checkbox
$input_enable_time = new inputField('Enable Time', 'enable_time');
$input_enable_time->toolTip('Check this box to include time of day (hours and minutes) in the display for date');
$input_enable_time->type('checkbox');
$input_enable_time->value(isset($list['enable_time']) ? $list['enable_time'] : '1');
$input_enable_time->arErr($message);

// custom date
$input_custom_date = new inputField('Custom Date', 'custom_date');
$input_custom_date->toolTip('Custom date to display if date override is enabled');
$input_custom_date->value(isset($list['custom_date']) ? $list['custom_date'] : '');
$input_custom_date->counterMax(100);
$input_custom_date->size('small');
$input_custom_date->arErr($message);

// start date
$input_start_date = new inputField('Start Date', 'start_date');
$input_start_date->size('verysmall');
$input_start_date->value(isset($list['start_date']) ? $list['start_date'] : '');
$input_start_date->readonly('true');
$input_start_date->arErr($message);


// end date
$input_end_date = new inputField('End Date', 'end_date');
$input_end_date->size('verysmall');
$input_end_date->value(isset($list['end_date']) ? $list['end_date'] : '');
$input_end_date->readonly('true');
$input_end_date->arErr($message);


/* time */

// start hour
$input_start_hour = new inputField('Start Hour', 'start_hour');
$input_start_hour->type('select');
$input_start_hour->size('verytiny');
$input_start_hour->selected(isset($list['start_hour']) ? $list['start_hour'] : '');

for($i = 1; $i <= 12; $i++) {
    $input_start_hour->option($i, "$i");
}

$input_start_hour->arErr($message);


// end hour
$input_end_hour = new inputField('End Hour', 'end_hour');
$input_end_hour->type('select');
$input_end_hour->size('verytiny');
$input_end_hour->selected(isset($list['end_hour']) ? $list['end_hour'] : '');

for($i = 1; $i <= 12; $i++) {
    $input_end_hour->option($i, "$i");
}

$input_end_hour->arErr($message);


// start minute
$input_start_minute = new inputField('Start Minute', 'start_minute');
$input_start_minute->type('select');
$input_start_minute->size('verytiny');
$input_start_minute->selected(isset($list['start_minute']) ? $list['start_minute'] : '');

for($i = 0; $i <= 55; $i = $i + 5) {
    $s = "$i";

    if($i < 10) {
        $s = "0" . $s;
    }    

    $input_start_minute->option($i, $s);
}

$input_start_minute->arErr($message);


// end minute
$input_end_minute = new inputField('End Minute', 'end_minute');
$input_end_minute->type('select');
$input_end_minute->size('verytiny');
$input_end_minute->selected(isset($list['end_minute']) ? $list['end_minute'] : '');

for($i = 0; $i <= 55; $i = $i + 5) {
    $s = "$i";

    if($i < 10) {
        $s = "0" . $s;
    }    

    $input_end_minute->option($i, $s);
}

$input_end_minute->arErr($message);


// start meridiem
$input_start_meridiem = new inputField('Start Meridiem', 'start_meridiem');
$input_start_meridiem->type('select');
$input_start_meridiem->size('tiny');
$input_start_meridiem->selected(isset($list['start_meridiem']) ? $list['start_meridiem'] : '');
$input_start_meridiem->option('AM', 'AM');
$input_start_meridiem->option('PM', 'PM');
$input_start_meridiem->arErr($message);


// end meridiem
$input_end_meridiem = new inputField('End Meridiem', 'end_meridiem');
$input_end_meridiem->type('select');
$input_end_meridiem->size('tiny');
$input_end_meridiem->selected(isset($list['end_meridiem']) ? $list['end_meridiem'] : '');
$input_end_meridiem->option('AM', 'AM');
$input_end_meridiem->option('PM', 'PM');
$input_end_meridiem->arErr($message);


/* SEO Inputs */

// seo title
$input_seo_title = new inputField( 'SEO Title', 'seo_title' );  
$input_seo_title->toolTip('Start with most important words<br />65 characters or less.');
$input_seo_title->value( isset($list['seo_title']) ? $list['seo_title'] : '') ;
$input_seo_title->counterWarning(65);
$input_seo_title->counterMax(100);
$input_seo_title->size('large');
$input_seo_title->arErr($message);

// seo description
$input_seo_description = new inputField( 'Description', 'seo_description' );    
$input_seo_description->toolTip('Start with the same words used in the title<br />150 characters or less.');
$input_seo_description->type('textarea');
$input_seo_description->value( isset($list['seo_description']) ? $list['seo_description'] : '') ;
$input_seo_description->counterWarning(150);
$input_seo_description->counterMax(250);
$input_seo_description->size('large');
$input_seo_description->arErr($message);

// seo seo_keywords
$input_seo_keywords = new inputField( 'Keywords', 'seo_keywords' ); 
$input_seo_keywords->toolTip('List of phrases, separated by commas, with most important phrases first. <br /> Note: the words have to appear somewhere on the page.');
$input_seo_keywords->type('textarea');
$input_seo_keywords->value(isset($list['seo_keywords']) ? $list['seo_keywords'] : '') ;
$input_seo_keywords->counterMax(1000);
$input_seo_keywords->size('large');
$input_seo_keywords->arErr($message);


$pageResources ="

<script type=\"text/javascript\" src=\"".$_config['admin_url']."modules/events/js/jquery.ui.datepicker.js\"></script>
<script type=\"text/javascript\" src=\"".$_config['admin_url']."modules/events/js/events_new.js\"></script>
<link rel=\"stylesheet\" type=\"text/css\" href=\"".$_config['admin_url']."modules/events/style.css\" />
";
$pageInit->createPageTop($pageResources);
 
 ?>
 <div class="page_container">
    <div id="h1">
        <?php if($newEvent) {
            echo '<h1>Add Event</h1>';
        } else {
            echo '<h1>Edit Event</h1>';
        } ?>
    </div>
    <div id="info_container">
		<?php 
		// ----------------------------------------subnav--------------------------------
		$selectedEvents = 'tabSel';
		$selectedCats = '';
		$selectedOpts = '';
		include("inc/subnav.php"); 
		echo '<hr />';
		//---------------------------------------Error Banner----------------------------- 
		// create a banner if $message['banner'] exists
		createBanner($message); 
		
		$parms = "?eventid={$event_id}&option=edit";
		?>
		<form action="events_new.php<?php echo $parms; ?>" method="post" enctype="application/x-www-form-urlencoded" name="addevent" id="addevent" class="form">
            <input type="hidden" name="event_id" id="event_id" value="<?php echo $event_id; ?>" /> 
	
    		<!--  properties_wrap -->
			<h2 id="prop-toggle" class="tiptip toggle" title="Information about the event.">Event Properties</h2><br />
			<div id="prop-toggle-wrap">
			<?php
				$input_title->createInputField();
				$input_url->createInputField();
				$input_cate->createInputField();
                $input_location->createInputField();
				$input_status->createInputField();
                echo '<hr style="margin-bottom:18px;" />';
                $input_override_date->createInputField();
                echo '<div id="custom_date_container">';
                $input_custom_date->createInputField();
                echo '</div><!-- custom_date_container -->';
                echo '<hr style="margin-bottom:18px;" />';
                $input_enable_time->createInputField();
			?>
           
                <div id="startdate-container">  
                <hr />          
                <div id="togglestartdate" class="toggle tiptip" title="Start Date for the event"><a>Start Date/Time</a></div>
                <div id="togglestartdate-wrap">
                    <?php
                        echo '<label class="tiptip" title="Select the date for when this event starts. Format: YYYY-MM-DD">Date</label>';
                        $input_start_date->createBareInputField();

                        echo '<div id="input_start_time_box">'; 
                        echo '<label class="tiptip" title="Select the time for when this event starts.">Time</label>';
                        $input_start_hour->createBareInputField();
                        echo '<label class="timeColon">:</label>';
                        $input_start_minute->createBareInputField();
                        $input_start_meridiem->createBareInputField();
                        echo '</div><!-- input_start_time_box -->'; 
                    ?>
                </div><!-- end togglestartdate-wrap -->
                </div><!-- end startdate-container -->
                
                <hr />          
                
                <div id="enddate-container">
                <div id="toggleenddate" class="toggle tiptip" title="End Date for the event"><a>End Date/Time</a></div>
                <div id="toggleenddate-wrap">
                    <?php
                        echo '<label class="tiptip" title="Select the date for when this event ends. Format: YYYY-MM-DD">Date</label>';
                        $input_end_date->createBareInputField();
                       
                        echo '<div id="input_end_time_box">'; 
                        echo '<label class="tiptip" title="Select the time for when this event ends">Time</label>';
                        $input_end_hour->createBareInputField();
                        echo '<label class="timeColon">:</label>';
                        $input_end_minute->createBareInputField();
                        $input_end_meridiem->createBareInputField();
                        echo '</div><!-- input_end_time_box -->'; 
                    ?>
                </div><!-- end toggleenddate-wrap -->
                <hr />          
                </div><!-- end enddate-container-->
			</div><!-- end prop_wrap -->		   

             <!-- content area -->
			<h2 id="content-toggle" class="tiptip toggle" title="Event information.">Description</h2>
			<?php if (isset($message['inline']) && array_key_exists('content', $message['inline'])) :?>
				<span class="<?php echo $message['inline']['content']['type'] ;?>"><?php echo $message['inline']['content']['msg'] ;?> </span>
			<?php endif; ?>
			<br />
			<div id="content-toggle-wrap">
			<?php
			// create tinymce
			$editable = array(
				'editable_class' => 'mceUploadable',
				'attributes' => array(
					'name' => 'content',
					'id' => 'content'),
				'secure_data' => array(
					'id-field' => 'id',				// req for save && upload
					'id-val' => $event_id,	// req for save && upload
					'upload-type' => 'events'			// req for upload
				)
			);
			$wrapper = getContentWrapper($editable);
			echo $wrapper['open'];
			echo isset($list['desc']) ? htmlspecialchars_decode($list['desc']) : '' ;
			echo $wrapper['close'];
			?>
			</div>
			<!-- end content area -->
	
	
            <!-- SEO area -->
            <h2 class="tiptip toggle" id="seo-toggle" title="Search Engine Optimization fields">Meta Tags</h2><br />
            <div id="seo-toggle-wrap">
            <?php
                $input_seo_title->createInputField();
                $input_seo_description->createInputField();
                $input_seo_keywords->createInputField();
            ?>
            </div><!-- end SEO area -->
	
			<!-- page buttons -->
			<div class='clearFix' ></div>
			<input name="submit-event" type="hidden" />
		</form>	
		<a id="submit-btn" class="blue button" href="#">Save</a>

		<a class="grey button" id="cancel-btn" href="events.php">Cancel</a>
		<div class='clearFix' ></div> 
		<!-- end page buttons -->		
	</div>
</div>	
	

<?php 
include($_config['admin_includes'] . "footer.php"); 
?>
