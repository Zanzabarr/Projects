<?php

class My_Date {

	private $tzTimezone		=	'';
	
	public function __construct()
	{
		// get the user's timezone
		$query = "SELECT timezone from auth_users where user_id = :sess_uid";

		$timezoneQuery = logged_query($query,0,array(":sess_uid" => $_SESSION['uid']));
		if( !$timezoneQuery || ! count($timezoneQuery) ) return false;
		//build timezone object
		$timezone = $timezoneQuery[0]['timezone'];

		$timezone = $timezone == '' ? "America/Los_Angeles" : $timezone;
		$this->tzTimezone = new DateTimeZone($timezone);
		return true;
	}

	public function makeDate ($setDate = 'now', $dateFormat = 'M  d,  h:i  a -- Y')
	{
			$dt_obj = new DateTime($setDate." UTC");
			$dt_obj->setTimezone($this->tzTimezone);
			return date_format($dt_obj, $dateFormat);
	}
}

