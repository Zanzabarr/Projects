<?php
$query ="
CREATE TABLE IF NOT EXISTS `events` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` text CHARACTER SET utf8 NOT NULL,
  `url` text CHARACTER SET utf8 NOT NULL,
  `cate` text CHARACTER SET utf8 NOT NULL,
  `location` text CHARACTER SET utf8 NOT NULL,
  `desc` text CHARACTER SET utf8 NOT NULL,
  `status` tinyint(1) NOT NULL DEFAULT '0',
  `seo_title` text CHARACTER SET utf8 NOT NULL,
  `seo_keywords` text CHARACTER SET utf8 NOT NULL,
  `seo_description` text CHARACTER SET ucs2 NOT NULL,
  `start_date` datetime DEFAULT NULL,
  `end_date` datetime DEFAULT NULL,
  `enable_time` tinyint(1) NOT NULL DEFAULT '1' COMMENT 'Specifies whether or not the time portion of the DateTime field is used. 1 for True, 0 for False',
  `custom_date` text CHARACTER SET utf8,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;
";
$result = logged_query($query,0,array());
if($result === false) die('Error Creating Events Table');
	
$query ="
CREATE TABLE IF NOT EXISTS `events_cat` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` text CHARACTER SET utf8 NOT NULL,
  `url` text CHARACTER SET utf8 NOT NULL,
  `desc` text CHARACTER SET utf8 NOT NULL,
  `status` tinyint(1) NOT NULL DEFAULT '0',
  `seo_title` text CHARACTER SET utf8 NOT NULL,
  `seo_keywords` text CHARACTER SET utf8 NOT NULL,
  `seo_description` text CHARACTER SET utf8 NOT NULL,
  `events_per_pg` int(3) NOT NULL DEFAULT '2',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;
";
$result = logged_query($query,0,array());
if($result === false) die('Error Creating Events Table');

$query ="
CREATE TABLE IF NOT EXISTS `events_colors` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `cat_id` int(11) NOT NULL,
  `color` varchar(7) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `cat_id` (`cat_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;
";
$result = logged_query($query,0,array());
if($result === false) die('Error Creating Events Table');

$query = "
CREATE TABLE IF NOT EXISTS `events_options` (
  `id` int(11) NOT NULL DEFAULT '1',
  `events_per_pg` int(3) NOT NULL DEFAULT '2',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
";
$result = logged_query($query,0,array());
if($result === false) die('Error Creating Events Table');

// add core user if not already present
$query ="SELECT `id` FROM `events_options` WHERE `id`=1";
$result = logged_query($query,0,array());
if ($result === false) die('Error Creating Events Table');
if(!isset($result[0]))
{
	$query ="INSERT INTO `events_options` VALUES (1,2)";
	$result = logged_query($query,0,array());
	if ($result === false) die('Error Creating Events Table');
	if($result) $moduleAdded['Events'] = true;
}