<?php
// this module doesn't automatically add any data so need to set a check for install
$result = logged_query("SELECT count(*) FROM `newsletter_settings`",0,array());
/*$needs_install = $result === false;*/

$query ="
CREATE TABLE IF NOT EXISTS `newsletter` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `subject` varchar(250) NOT NULL,
  `content` text NOT NULL,
  `status` tinyint(1) NOT NULL COMMENT '0=draft; 1=ready; 2=sent',
  `date_updated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `date_created` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1;
";
$result = logged_query($query,0,array());
if($result === false) die("There was an error creating table newsletter");

// create the trigger to insert current time stamp in created/updated date fields
$query ="
CREATE TRIGGER `newsletter_before_insert` BEFORE INSERT ON `newsletter`
FOR EACH ROW
  SET NEW.date_created = NOW(),
      NEW.date_updated = NOW()
";
$result = logged_query($query,0,array());

$query = "CREATE TABLE IF NOT EXISTS `newsletter_recip` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `news_id` int(11) NOT NULL,
  `list_id` varchar(20) NOT NULL,
  `date_sent` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1;";
$result = logged_query($query,0,array());
if($result === false) die("There was an error creating table newsletter_recip");

$query = "CREATE TABLE IF NOT EXISTS `newsletter_settings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `apikey` varchar(254) NOT NULL,
  `to_name` varchar(254) NOT NULL DEFAULT 'Subscribers',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1;";
$result = logged_query($query,0,array());
if($result === false) die("There was an error creating table newsletter_settings");

$query = "CREATE TABLE IF NOT EXISTS `newsletter_subs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `email` varchar(254) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1;";
$result = logged_query($query,0,array());
if($result === false) die("There was an error creating table newsletter_subs");

$query = "CREATE TABLE IF NOT EXISTS `newsletter_subs_groups` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sub_id` int(11) NOT NULL,
  `list_id` varchar(20) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1;";
$result = logged_query($query,0,array());
if($result === false) die("There was an error creating table newsletter_subs_groups");

$query = "SELECT `id` FROM `newsletter_settings` WHERE `id`=1";
$result = logged_query($query,0,array());
if(!isset($result[0])) {
	$query = "INSERT INTO `newsletter_settings` (`id`, `apikey`, `to_name`) VALUES (1,'','Subscriber');";
	$result = logged_query($query,0,array());
	if($result === false) die ('Error Creating Newsletter Settings Table');
	if($result) $moduleAdded['Newsletter'] = true;
}
/*
// got to the end: so updates occured where needed
if($needs_install) $moduleAdded['Newsletter'] = true;
*/