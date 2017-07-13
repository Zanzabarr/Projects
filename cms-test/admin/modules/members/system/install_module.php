<?php
$query ="
CREATE TABLE IF NOT EXISTS `members` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `url` text CHARACTER SET utf8,
  `first_name` varchar(80) CHARACTER SET utf8 DEFAULT NULL,
  `last_name` varchar(80) CHARACTER SET utf8 DEFAULT NULL,
  `bio` text NOT NULL,
  `phone_number` varchar(30) CHARACTER SET utf8 DEFAULT NULL,
  `mailing_address` varchar(175) CHARACTER SET utf8 DEFAULT NULL,
  `city` varchar(50) CHARACTER SET utf8 DEFAULT NULL,
  `province_state_region` varchar(50) CHARACTER SET utf8 DEFAULT NULL,
  `country` varchar(50) CHARACTER SET utf8 DEFAULT NULL,
  `postal_code` varchar(10) CHARACTER SET utf8 DEFAULT NULL,
  `email` varchar(75) CHARACTER SET utf8 NOT NULL,
  `password` varchar(100) CHARACTER SET utf8 NOT NULL,
  `tmp_password` varchar(100) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `tmp_password_date` datetime NOT NULL,
  `member_image` varchar(100) CHARACTER SET utf8 DEFAULT NULL,
  `status` tinyint(1) NOT NULL DEFAULT '0',
  `ftp_access` tinyint(1) NOT NULL,
  `level` tinyint(1) NOT NULL DEFAULT '0',
  `payment_status` tinyint(1) NOT NULL DEFAULT '0',
  `pw_change_request` tinyint(1) NOT NULL DEFAULT '0',
  `unpaid_signup` tinyint(1) NOT NULL DEFAULT '0',
  `membership_expiry` datetime NOT NULL,
  `eBulletin` tinyint(1) NOT NULL DEFAULT '0',
  `list_name` tinyint(1) NOT NULL DEFAULT '0',
  `list_address` tinyint(1) NOT NULL DEFAULT '0',
  `list_phone` tinyint(1) NOT NULL DEFAULT '0',
  `list_email` tinyint(1) NOT NULL DEFAULT '0',
  `creation_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `confirmation_code` varchar(250) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;
";
$result = logged_query($query,0,array());
if($result === false) die('Error Creating Members Table');

$query ="
CREATE TABLE IF NOT EXISTS `members_home` (
  `id` int(11) NOT NULL,
  `title` varchar(250) CHARACTER SET utf8 DEFAULT 'Members Hompage',
  `desc` text CHARACTER SET utf8,
  `status` tinyint(1) NOT NULL DEFAULT '0',
  `first_page_only` tinyint(1) NOT NULL DEFAULT '1',
  `members_per_pg` tinyint(3) NOT NULL DEFAULT '10',
  `date` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;
";
$result = logged_query($query,0,array());
if($result === false) die('Error Creating Members Table');

// add initial page if not already present
$query ="SELECT `id` FROM `members_home` WHERE `id`=1";
$result = logged_query($query,0,array());
if ($result === false) die('Error Creating Members Table');
if(!isset($result[0]))
{
	$query ="INSERT INTO `members_home` VALUES (1,'','',0,1,10,NOW())";
	$result = logged_query($query,0,array());
	if ($result === false) die('Error Creating Members Table');
}

$query ="
CREATE TABLE IF NOT EXISTS `members_home_rev` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `members_home_id` int(11) NOT NULL,
  `title` text CHARACTER SET utf8,
  `desc` text CHARACTER SET utf8,
  `status` tinyint(1) NOT NULL DEFAULT '0',
  `first_page_only` tinyint(1) NOT NULL DEFAULT '1',
  `members_per_pg` tinyint(3) NOT NULL,
  `date` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;
";
$result = logged_query($query,0,array());
if($result === false) die('Error Creating Members Table');

$query ="
CREATE TABLE IF NOT EXISTS `members_options` (
  `id` int(11) NOT NULL DEFAULT '1',
  `member_req_login` tinyint(1) NOT NULL DEFAULT '1',
  `online_signup` tinyint(1) NOT NULL DEFAULT '0',
  `pay_signup` tinyint(1) NOT NULL DEFAULT '0',
  `renewal_req` tinyint(1) NOT NULL DEFAULT '0',
  `ftp_front` tinyint(1) NOT NULL DEFAULT '0',
  `members_front` tinyint(1) NOT NULL DEFAULT '1',
  `confirmation_period` tinyint(3) NOT NULL DEFAULT '5',
  `confirm_from` varchar(250) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `confirm_title` varchar(100) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `confirm_body` text CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;
";
$result = logged_query($query,0,array());
if($result === false) die('Error Creating Members Table');

// add initial options if not already present
$query ="SELECT `id` FROM `members_options` WHERE `id`=1";
$result = logged_query($query,0,array());
if ($result === false) die('Error Creating Members Table');
if(!isset($result[0]))
{
	$query ="INSERT INTO `members_options` (id) VALUES(1)";
	$result = logged_query($query,0,array());
	if ($result === false) die('Error Creating Members Table');
	if($result) $moduleAdded['Members'] = true;
}

$query ="
CREATE TABLE IF NOT EXISTS `members_paypal` (
  `id` int(11) NOT NULL DEFAULT '1',
  `environment` varchar(4) NOT NULL DEFAULT 'dev',
  `paypal_on` tinyint(1) NOT NULL DEFAULT '0',
  `dev_paypal` varchar(250) NOT NULL,
  `live_paypal` varchar(250) NOT NULL,
  `dev_orderemail` varchar(250) NOT NULL,
  `live_orderemail` varchar(250) NOT NULL,
  `no_note` int(1) NOT NULL DEFAULT '0',
  `note_comment` varchar(100) NOT NULL,
  `currency` varchar(3) NOT NULL DEFAULT 'CAD',
  `orderemail` varchar(254) NOT NULL,
  `confirm_from` varchar(250) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `confirm_title` varchar(100) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `confirm_body` text CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;
";
$result = logged_query($query,0,array());
if($result === false) die('Error Creating Members Table');

// add initial options if not already present
$query ="SELECT `id` FROM `members_paypal` WHERE `id`=1";
$result = logged_query($query,0,array());
if ($result === false) die('Error Creating Members Table');
if(!isset($result[0]))
{
	$query ="INSERT INTO `members_paypal` (id) VALUES(1)";
	$result = logged_query($query,0,array());
	if ($result === false) die('Error Creating Members Table');
	if($result) $moduleAdded['Members'] = true;
}


$query ="
CREATE TABLE IF NOT EXISTS `members_signup_rev` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `members_signup_id` int(11) NOT NULL,
  `title` text CHARACTER SET utf8,
  `desc` text CHARACTER SET utf8,
  `status` tinyint(1) NOT NULL DEFAULT '0',
  `email_notification` tinyint(1) NOT NULL,
  `notify_from` varchar(250) NOT NULL,
  `notify_to` varchar(250) NOT NULL,
  `notify_title` varchar(100) NOT NULL,
  `notify_body` varchar(500) NOT NULL,
  `success` text CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `confirmed` text CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `date` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;
";
$result = logged_query($query,0,array());
if($result === false) die('Error Creating Members Table');

$query ="
CREATE TABLE IF NOT EXISTS `ftp_folders` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `folder` varchar(25) NOT NULL,
  `status` tinyint(1) NOT NULL DEFAULT '1',
  `date_updated` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;
";
$result = logged_query($query,0,array());
if($result === false) die('Error Creating Members Table');

$query ="
CREATE TABLE IF NOT EXISTS `ftp_user` (
  `id` int(11) NOT NULL,
  `username` varchar(25) NOT NULL,
  `password` varchar(90) NOT NULL,
  `status` tinyint(1) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;
";
$result = logged_query($query,0,array());
if($result === false) die('Error Creating Members Table');

$query ="
CREATE TABLE IF NOT EXISTS `ftp_user_folders` (
  `ftp_user_id` int(11) NOT NULL,
  `folder_id` int(11) NOT NULL,
  `restriction` char(15) NOT NULL DEFAULT 'none' COMMENT 'valid options: none, read only, write only',
  PRIMARY KEY (`ftp_user_id`,`folder_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;
";
$result = logged_query($query,0,array());
if($result === false) die('Error Creating Members Table');

$query ="
CREATE TABLE IF NOT EXISTS `members_signup` (
  `id` int(11) NOT NULL DEFAULT '1',
  `title` text CHARACTER SET utf8,
  `desc` text CHARACTER SET utf8,
  `status` tinyint(1) NOT NULL DEFAULT '0',
  `signup_from_name` varchar(250) NOT NULL,
  `signup_from` varchar(250) NOT NULL,
  `email_notification` tinyint(1) NOT NULL DEFAULT '0' COMMENT '0=none 1=notifyAdmin 2=adminApproval',
  `notify_from_name` varchar(250) NOT NULL,
  `notify_from` varchar(250) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `notify_to` varchar(250) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `notify_title` varchar(100) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT 'New Member Request',
  `notify_body` varchar(500) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `success` text CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `confirmed` text CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `date` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;
";
$result = logged_query($query,0,array());
if($result === false) die('Error Creating Members Table');

// add initial options if not already present
$query ="SELECT `id` FROM `members_signup` WHERE `id`=1";
$result = logged_query($query,0,array());
if ($result === false) die('Error Creating Members Table');
if(!isset($result[0]))
{
	$query ="INSERT INTO `members_signup` (`title`,`desc`,`notify_body`,`confirmed`,`success`) VALUES('Membership Signup Form', '<h2>Please Fill In the Form</h2><p>Thank you for your interest. Please fill in the following information to become a Member!</p><h3></h3><h3 style=\"text-align: center;\">Membership Signup Form</h3>', 'You&#039;ve received a new Membership Application.(If Email Notification is set to &#039;Approve&#039;, a sign in link will appear at the bottom of this email. Don&#039;t forget to sign in and Approve or Decline the application)This is what the Applicant provided on the signup form:','&lt;h2&gt;Success!&lt;/h2&gt;
&lt;p&gt;You are now a member! &lt;a href=&quot;members&quot;&gt;Login now&lt;/a&gt;.&lt;/p&gt;','&lt;h2&gt;Success!&lt;/h2&gt;
&lt;h3&gt;Regular Example Message:&lt;/h3&gt;
&lt;p&gt;Thank you for signing up with US! You will receive a confirmation email at the address you provided. Be sure to check your spam box so it doesn&#039;t get lost. Once you receive your email, follow the link provided to confirm your identity.&lt;/p&gt;
&lt;p&gt;&lt;/p&gt;
&lt;h3&gt;Paypal Example Message:&lt;/h3&gt;
&lt;p&gt;Thank you for signing up with US! You will receive a confirmation email at the address you provided. Be sure to check your spam box so it doesn&#039;t get lost. Once you receive your email, follow the link provided to confirm your identity and finalize your Membership Application by paying through Paypal&lt;/p&gt;
&lt;h3&gt;Admin Approval Example Message:&lt;/h3&gt;
&lt;p&gt;Thank you for signing up with US! One of our great Admins will review your application and get back to you with your next step! Thanks for applying.&lt;/p&gt;')";
	$result = logged_query($query,0,array());
	if ($result === false) die('Error Creating Members Table');
	if($result) $moduleAdded['Members'] = true;
}

