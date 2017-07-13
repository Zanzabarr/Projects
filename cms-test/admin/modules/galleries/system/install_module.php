<?php
// this module doesn't automatically add any data so need to set a check for install
$result = logged_query("SELECT count(*) FROM `gallery_image`",0,array());
$needs_install = $result === false;

$query ="
CREATE TABLE IF NOT EXISTS `gallery` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `title` varchar(256) NOT NULL,
  `sort_type` int(1) NOT NULL DEFAULT '2' COMMENT '1=''chronological'' : 0=''reverse chronological'' : 3=''Alphabetically'' : 2=''Sorted Order''''',
  `status` int(1) NOT NULL DEFAULT '0' COMMENT '0 = ''draft'' 1 = ''published''',
  `date` varchar(30) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;
";
$result = logged_query($query,0,array());
if($result === false) die('Error Creating Gallery Table');

$query ="
CREATE TABLE IF NOT EXISTS `gallery_image` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `gallery_id` int(11) NOT NULL,
  `posn` int(4) NOT NULL COMMENT '0 = ''not in gallery''',
  `name` varchar(256) NOT NULL,
  `desc` text,
  `html_desc` text,
  `alt` varchar(256) NOT NULL,
  `url` varchar(256) NOT NULL,
  `date` varchar(30) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;
";
$result = logged_query($query,0,array());
if($result === false) die('Error Creating Gallery Table');

// got to the end: so updates occured where needed
if($needs_install) $moduleAdded['Gallery'] = true;