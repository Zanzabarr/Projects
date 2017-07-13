<?php
$query ="
CREATE TABLE IF NOT EXISTS `testimonials` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` text NOT NULL,
  `business` text NOT NULL,
  `short_test` text NOT NULL,
  `full_test` text NOT NULL,
  `status` int(1) NOT NULL DEFAULT '0',
  `date` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;
";
$result = logged_query($query,0,array());
if($result === false) die('Error Creating Testimonials Table');

$query ="
CREATE TABLE IF NOT EXISTS `testimonials_home` (
  `id` int(11) NOT NULL DEFAULT '1',
  `title` text CHARACTER SET utf8,
  `desc` text CHARACTER SET utf8,
  `status` tinyint(1) NOT NULL DEFAULT '0',
  `date` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;
";
$result = logged_query($query,0,array());
if($result === false) die('Error Creating Testimonials Table');

$query ="
CREATE TABLE IF NOT EXISTS `testimonials_home_rev` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `testimonials_home_id` int(11) NOT NULL,
  `title` text CHARACTER SET utf8,
  `desc` text CHARACTER SET utf8,
  `status` tinyint(1) NOT NULL DEFAULT '0',
  `date` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;
";
$result = logged_query($query,0,array());
if($result === false) die('Error Creating Testimonials Table');

// add initial page if not already present
$query ="SELECT `id` FROM `testimonials_home` WHERE `id`=1";
$result = logged_query($query,0,array());
if ($result === false) die('Error Creating Testimonials Table');
if(!isset($result[0]))
{
	$query ="INSERT INTO `testimonials_home` VALUES (1,'Testimonials Home','Testimonials Home',0,NOW())";
	$result = logged_query($query,0,array());
	if ($result === false) die('Error Creating Testimonials Table');
	if($result) $moduleAdded['Testimonials'] = true;
}