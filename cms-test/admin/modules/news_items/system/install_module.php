<?php
//include('../../../includes/config.php');
//include('../../../includes/functions.php');
$news_items_news_itemsList ="
CREATE TABLE IF NOT EXISTS `news_items` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `news_item_title` varchar(300) CHARACTER SET utf8 DEFAULT NULL,  
  `url` text CHARACTER SET utf8 NOT NULL,
  `contributor_name` varchar(60) CHARACTER SET utf8 NOT NULL,
  `email` varchar(75) CHARACTER SET utf8 DEFAULT NULL,
  `caption` varchar(100) CHARACTER SET utf8 NOT NULL,
  `content` text CHARACTER SET utf8,
  `news_item_image` varchar(100) CHARACTER SET utf8 DEFAULT NULL,
  `status` tinyint(1) NOT NULL DEFAULT '0',
  `date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;";
    $result = logged_query($news_items_news_itemsList,0,array());
	if($result === false) die("There was an error creating table news_items");
	
$news_items_optionsTable ="
CREATE TABLE IF NOT EXISTS `news_items_options` (
  `id` int(11) NOT NULL DEFAULT '1',
  `news_items_per_pg` int(3) NOT NULL DEFAULT '25',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 ;";
    $result = logged_query($news_items_optionsTable,0,array());
	if($result === false) die("There was an error creating table news_items_options");

$news_items_homeTable ="
CREATE TABLE IF NOT EXISTS `news_items_home` (
  `id` int(11) NOT NULL DEFAULT '1',
  `title` text CHARACTER SET utf8,
  `desc` text CHARACTER SET utf8,
  `status` tinyint(1) NOT NULL DEFAULT '0',
  `date` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 ;";
    $result = logged_query($news_items_homeTable,0,array());
	if($result === false) die("There was an error creating table news_items_home");

$news_items_homeRevTable ="
CREATE TABLE IF NOT EXISTS `news_items_home_rev` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `news_items_home_id` int(11) NOT NULL,
  `title` text CHARACTER SET utf8,
  `desc` text CHARACTER SET utf8,
  `status` tinyint(1) NOT NULL DEFAULT '0',
  `date` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;";	
    $result = logged_query($news_items_homeRevTable,0,array());
	if($result === false) die("There was an error creating table news_items_home_rev");
	if($result) $moduleAdded['News_Items'] = true;

?>
