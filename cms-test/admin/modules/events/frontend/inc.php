<?php
// Not using this as head info, moved that elsewhere. This is where frontend module includes will go instead. (Most should no need)

/******************* FUNCTIONS *************************/

// Takes a timestamp as a parameter, and returns an array containing date values used to output event dates
function toEventDate($timestamp) {
    $date = getdate($timestamp);
    
    $year = $date['year'];
    $month = substr($date['month'], 0, 3);
    $day = $date['mday'];
    $hours = $date['hours'];

    if($hours <= 11) {
        if($hours == 0) {
            $hours = 12;
        }

        $meridiem = 'am';
    } else {
        if($hours != 12) {
            $hours = $hours - 12;
        }

        $meridiem = 'pm';
    }

    $minutes = $date['minutes'];

    if($minutes < 10) {
        // add leading 0 to make two digits
        $minutes = "0" . "$minutes";
    }
    else {
        $minutes = "$minutes";
    } 

    $eventDate = array('timestamp' => $timestamp, 'year' => $year, 'month' => $month, 'day' => $day, 'hours' => $hours, 'minutes' => $minutes, 'meridiem' => $meridiem);

    return $eventDate;
}


// Takes a start and end eventDate array, and enableTime boolean as parameters, and returns an appropriate event date string indicating 
// the full duration of the event. If enableTime is true, the date returned contains time of day values (minutes and hours). 
//If false, the date returned only contains day, month, and year values.
function getFullEventDate($startDate, $endDate, $enableTime) {
    $startMinutes = true; 
    $endMinutes = true;

    if($startDate['minutes'] == '00') {
        // no start date minutes need to be displayed
        $startMinutes = false;
    }
    
    if($endDate['minutes'] == '00') {
        // no end date minutes need to be displayed
        $endMinutes = false;
    }

    if($startDate['year'] != $endDate['year']) {
        // get full start and end date
            
        if($enableTime) {
            // display date with time
            if($startMinutes || $endMinutes) {
                $fullEventDate = date('g:ia, M j, Y', $startDate['timestamp']) . " to " . date('g:ia, M j, Y', $endDate['timestamp']);
            }
            else {
                $fullEventDate = date('ga, M j, Y', $startDate['timestamp']) . " to " . date('ga, M j, Y', $endDate['timestamp']);
            }
        } else {
            // display date without time
            $fullEventDate = date('M j, Y', $startDate['timestamp']) . " to " . date('M j, Y', $endDate['timestamp']);
        }

    } else if($startDate['month'] != $endDate['month'] || $startDate['day'] != $endDate['day']) {
        
        if($enableTime) {
            // display date with time
            if($startMinutes || $endMinutes) {
                $fullEventDate = date('g:ia, M j', $startDate['timestamp']) . " to " . date('g:ia, M j, Y', $endDate['timestamp']);
            }
            else {
                $fullEventDate = date('ga, M j', $startDate['timestamp']) . " to " . date('ga, M j, Y', $endDate['timestamp']);
            }
        } else {
            // display date without time
            if($startDate['month'] != $endDate['month']) {
                $fullEventDate = date('M j', $startDate['timestamp']) . " to " . date('M j, Y', $endDate['timestamp']);
            } 
            else {
                $fullEventDate = date('M j', $startDate['timestamp']) . "-" . date('j, Y', $endDate['timestamp']);
            }
        }
    } else if($startDate['hours'] != $endDate['hours'] && $enableTime) {
        if($startMinutes || $endMinutes) {
            $fullEventDate = date('g:ia', $startDate['timestamp']) . "-" . date('g:ia, M j, Y', $endDate['timestamp']);
        }
        else {
            $fullEventDate = date('ga', $startDate['timestamp']) . "-" . date('ga, M j, Y', $endDate['timestamp']);
        }
    } else if($startDate['minutes'] != $startDate['minutes'] && $enableTime) {
        $fullEventDate = date('g:i', $startDate['timestamp']) . "-" . date('g:ia, M j, Y', $endDate['timestamp']);
    } else {
        // dates are the same as far as events are concerned. Get only the start date
        if($enableTime) {
            // display date with time
            if($startMinutes) {
                $fullEventDate = date('g:ia, M j, Y', $startDate['timestamp']);
            }
            else {
                $fullEventDate = date('ga, M j, Y', $startDate['timestamp']);
            }
        }
        else {
            // display date without time
                $fullEventDate = date('M j, Y', $startDate['timestamp']);
        }
    }

    return $fullEventDate;
}

/* takes an array parameter of events from the Events module ($events = logged_query_assoc_array();) and creates a downloadable iCalendar file for import to any compatible calendar app (Google Calendar, Yahoo Calendar, MacMail, Outlook, etc.) */
function makeICalendar($events) {
	global $_config;
	
	// CREATE ICAL
	$icalcontents = "";
	define("DATE_ICAL", "Ymd\THis");
		//start iCal file	
		$icalcontents .= "BEGIN:VCALENDAR\r\n";
		$icalcontents .= "VERSION:2.0\r\n";
		$icalcontents .= "PRODID:-//Test CMS//NONSGML {$_config['company_name']}//EN\r\n";
	foreach ($events as $row) {
		$icalstarttime   = date(DATE_ICAL, strtotime( $row['start_date']));
		$icalendtime     = date(DATE_ICAL, strtotime( $row['end_date']));
		$idsnip			 = md5(uniqid(mt_rand(), true));
		$eventid         = $idsnip . "@" . $_SERVER['SERVER_NAME'];
		$dateStamp       = date(DATE_ICAL, strtotime("now"));
		$content		 = preg_replace("/{{(.*)}}/e","substitute_tags('$1');", $row['desc']);

		$icalcontents .= "BEGIN:VEVENT\r\n";
		$icalcontents .= "UID:{$eventid}\r\n";
		$icalcontents .= "DTSTAMP:{$dateStamp}\r\n";
		$icalcontents .= "DTSTART:{$icalstarttime}\r\n";
		$icalcontents .= "DTEND:{$icalendtime}\r\n";
		$icalcontents .= "SUMMARY:".urldecode($row['title'])."\r\n";
		$icalcontents .= "DESCRIPTION:".strip_tags(htmlspecialchars_decode($content))."\r\n";
		$icalcontents .= "END:VEVENT\r\n";
	}
	$icalcontents .= "END:VCALENDAR";
	//make a file
	$foldername = $_config['upload_path'].'events/';
	// build path if it doesn't exist
	if(!is_dir($foldername))
	{
		$old = umask(0);
		@mkdir($foldername, 0777, true);
		umask($old);
	}
	
	// CLEAN UP OLD FILES
	// remove the current user's last generated file
	if(isset($_SESSION['last_ical']) && is_file($foldername . $_SESSION['last_ical']) ) @unlink($foldername . $_SESSION['last_ical']);
	
	$filename = 'calendar_export.ics';
	// remove old files
	foreach ( glob($foldername . "*.ics") as $tmpfile)
	{
		if (time()-filemtime($tmpfile) > 10 * 60) {
		  // delete older than 2 minutes
		  unlink($tmpfile);
		}
	}	
	
	// CREATE UNIQUE FILENAME
	// if this name already exists: upcount it
	while(is_file($foldername . $filename)) {
		$filename = calendar_upcount_name($filename);
	}
	$_SESSION['last_ical'] = $filename;
	
	// WRITE FILE TO DISK
	$filepath = $_config['upload_url'].'events/'.$filename;
	$handle = fopen($foldername . $filename, 'w') or die("Error: can't create file");
	fwrite($handle, $icalcontents);
	fclose($handle);
	
	return $filepath;
}

function calendar_upcount_name_callback($matches) 
{
	$index = isset($matches[1]) ? intval($matches[1]) + 1 : 1;
	$ext = isset($matches[2]) ? $matches[2] : '';
	return ' ('.$index.')'.$ext;
}

function calendar_upcount_name($name) 
{
	return preg_replace_callback(
		'/(?:(?: \(([\d]+)\))?(\.[^.]+))?$/',
		'calendar_upcount_name_callback',
		$name,
		1
	);
}
