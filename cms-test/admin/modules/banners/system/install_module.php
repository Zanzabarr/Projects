<?php
//include('../../includes/config.php');
//include('../../includes/functions.php');
$table ="
CREATE TABLE IF NOT EXISTS `banners` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(256) NOT NULL,
  `alt` varchar(256) DEFAULT NULL,
  `link` varchar(250) DEFAULT NULL,
  `desc` text,
  `html_desc` text,
  `posn` int(4) NOT NULL DEFAULT '0',
  `date` varchar(30) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;";
$result = logged_query($table,0,array());	
if($result === false) die('Error Creating Members Table');

if($result) $moduleAdded['Banners'] = true;
