<?php
//include('../../includes/config.php');
//include('../../includes/functions.php');

$query ="CREATE TABLE IF NOT EXISTS `blog_cat` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `url` text NOT NULL,
  `title` text NOT NULL,
  `desc` text NOT NULL,
  `seo_title` text NOT NULL,
  `seo_keywords` text NOT NULL,
  `seo_description` text NOT NULL,
  `status` int(1) NOT NULL,
  `date` varchar(30) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;";
$result = logged_query($query,0,array());
if($result === false) die('Error Creating Blog Table');

$query ="CREATE TABLE IF NOT EXISTS `blog_cat_rev` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `blog_cat_id` int(11) NOT NULL,
  `url` text NOT NULL,
  `title` text NOT NULL,
  `desc` text NOT NULL,
  `seo_title` text NOT NULL,
  `seo_keywords` text NOT NULL,
  `seo_description` text NOT NULL,
  `status` int(1) NOT NULL,
  `date` varchar(30) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;";
$result = logged_query($query,0,array());
if($result === false) die('Error Creating Blog Table');

$query ="CREATE TABLE IF NOT EXISTS `blog_comments` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `post_id` int(11) NOT NULL,
  `post` text NOT NULL,
  `name` varchar(25) NOT NULL,
  `email` varchar(254) NOT NULL,
  `comments` text NOT NULL,
  `approve` int(1) NOT NULL DEFAULT '0',
  `time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1;";
$result = logged_query($query,0,array());
if($result === false) die('Error Creating Blog Table');

$query ="CREATE TABLE IF NOT EXISTS `blog_home` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `title` text NOT NULL,
  `url` text NOT NULL,
  `desc` text NOT NULL,
  `seo_title` text NOT NULL,
  `seo_keywords` text NOT NULL,
  `seo_description` text NOT NULL,
  `status` int(1) NOT NULL DEFAULT '0',
  `date` varchar(30) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1;";
$result = logged_query($query,0,array());
if($result === false) die('Error Creating Blog Table');

$query ="CREATE TABLE IF NOT EXISTS `blog_home_rev` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `blog_home_id` int(11) NOT NULL,
  `title` text NOT NULL,
  `url` text NOT NULL,
  `desc` text NOT NULL,
  `seo_title` text NOT NULL,
  `seo_keywords` text NOT NULL,
  `seo_description` text NOT NULL,
  `status` int(1) NOT NULL DEFAULT '0',
  `date` varchar(30) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1;";
$result = logged_query($query,0,array());
if($result === false) die('Error Creating Blog Table');

$query ="CREATE TABLE IF NOT EXISTS `blog_options` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL DEFAULT '1',
  `post_per_pg` int(11) NOT NULL DEFAULT '1',
  `show_date` tinyint(1) NOT NULL DEFAULT '1',
  `show_author` tinyint(1) NOT NULL DEFAULT '1',
  `comments` int(1) NOT NULL DEFAULT '1',
  `approve` int(1) NOT NULL DEFAULT '1',
  `com_per_pg` int(11) NOT NULL DEFAULT '10',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1;";
$result = logged_query($query,0,array());
if($result === false) die('Error Creating Blog Table');

$query ="CREATE TABLE IF NOT EXISTS `blog_post` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `title` text NOT NULL,
  `intro` tinytext NOT NULL,
  `post` mediumtext NOT NULL,
  `cate` text NOT NULL,
  `date` varchar(30) NOT NULL,
  `status` int(1) NOT NULL,
  `featured` tinyint(1) NOT NULL DEFAULT '0',
  `image_name` varchar(250) NOT NULL,
  `image_alt` varchar(250) NOT NULL,
  `seo_description` text NOT NULL,
  `seo_keywords` text NOT NULL,
  `seo_title` text NOT NULL,
  `url` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;";
$result = logged_query($query,0,array());
if($result === false) die('Error Creating Blog Table');

$query ="
CREATE TABLE IF NOT EXISTS `blog_post_rev` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `post_id` int(11) NOT NULL,
  `title` text NOT NULL,
  `intro` tinytext NOT NULL,
  `post` mediumtext NOT NULL,
  `cate` text NOT NULL,
  `date` varchar(30) NOT NULL,
  `status` int(1) NOT NULL,
  `featured` tinyint(1) NOT NULL DEFAULT '0',
  `image_name` varchar(250) NOT NULL,
  `image_alt` varchar(250) NOT NULL,
  `seo_description` text NOT NULL,
  `seo_keywords` text NOT NULL,
  `seo_title` text NOT NULL,
  `url` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;";
$result = logged_query($query,0,array());
if($result === false) die('Error Creating Blog Table');

/*	EMPTY SAMPLE

$query ="";
$result = logged_query($query,0,array());
if($result === false) die('Error Creating Blog Table');

*/

// add initial options if not already present
$query ="SELECT `id` FROM `blog_options` WHERE `id`=1";
$result = logged_query($query,0,array());
if ($result === false) die('Error Creating Blog Table');
if(!isset($result[0]))
{
	$query ="INSERT INTO `blog_options` (`id`, `user_id`, `post_per_pg`, `show_date`, `show_author`, `comments`, `approve`, `com_per_pg`) VALUES (1, 1, 1, 1, 1, 0, 1, 10);";
	$result = logged_query($query,0,array());
	if ($result === false) die('Error Creating Blog Options');
	if($result) $moduleAdded['Blog'] = true;
}

// add initial options if not already present
$query ="SELECT `id` FROM `blog_home` WHERE `id`=1";
$result = logged_query($query,0,array());
if ($result === false) die('Error Creating Blog Table');
if(!isset($result[0]))
{
	$query ="INSERT INTO `blog_home` (`id`, `user_id`, `title`, `url`, `desc`, `seo_title`, `seo_keywords`, `seo_description`, `status`, `date`) VALUES
(1, 1, 'Blog Home', 'blog_home', 'Blog Home', 'Blog Home', 'Blog Home', 'Blog Home', 1, NOW());";
	$result = logged_query($query,0,array());
	if ($result === false) die('Error Creating Blog Options');
	if($result) $moduleAdded['Blog'] = true;
}

?>