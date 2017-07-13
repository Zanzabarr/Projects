<?php 

//include('../../../includes/config.php');
//include('../../../includes/functions.php');

$query ="CREATE TABLE IF NOT EXISTS `ecom_category` (
  `category_data_id` int(11) NOT NULL,
  `lft` int(11) NOT NULL,
  `rgt` int(11) NOT NULL,
  PRIMARY KEY (`category_data_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;";
$result = logged_query($query,0,array());
if($result === false) die('Error Creating Products Category Table');

$query ="CREATE TABLE IF NOT EXISTS `ecom_category_data` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `url` varchar(100) NOT NULL,
  `name` varchar(100) NOT NULL,
  `desc` text NOT NULL,
  `seo_keywords` text NOT NULL,
  `seo_description` varchar(250) NOT NULL,
  `seo_title` varchar(100) NOT NULL,
  `status` tinyint(1) NOT NULL,
  `date_created` varchar(30) NOT NULL,
  `date_updated` varchar(30) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uni` (`url`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;";
$result = logged_query($query,0,array());
if($result === false) die('Error Creating Category Data Table');

$query ="CREATE TABLE IF NOT EXISTS `ecom_category_data_rev` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `ecom_cat_data_id` int(11) NOT NULL,
  `url` varchar(100) NOT NULL,
  `name` varchar(100) NOT NULL,
  `desc` text NOT NULL,
  `seo_keywords` text NOT NULL,
  `seo_description` varchar(250) NOT NULL,
  `seo_title` varchar(100) NOT NULL,
  `status` tinyint(1) NOT NULL,
  `date_created` varchar(30) NOT NULL,
  `date_updated` varchar(30) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;";
$result = logged_query($query,0,array());
if($result === false) die('Error Creating Category Data Revision Table');

$query ="CREATE TABLE IF NOT EXISTS `ecom_collection` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `url` text NOT NULL,
  `title` text NOT NULL,
  `desc` text NOT NULL,
  `seo_title` text NOT NULL,
  `seo_keywords` text NOT NULL,
  `seo_description` text NOT NULL,
  `status` int(1) NOT NULL,
  `date_created` varchar(30) NOT NULL,
  `date_updated` varchar(30) NOT NULL,
  `valid_category` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;";
$result = logged_query($query,0,array());
if($result === false) die('Error Creating Collection Table');

$query ="CREATE TABLE IF NOT EXISTS `ecom_collection_rev` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `ecom_col_id` int(11) NOT NULL,
  `url` text NOT NULL,
  `title` text NOT NULL,
  `desc` text NOT NULL,
  `seo_title` text NOT NULL,
  `seo_keywords` text NOT NULL,
  `seo_description` text NOT NULL,
  `status` int(1) NOT NULL,
  `date_created` varchar(30) NOT NULL,
  `date_updated` varchar(30) NOT NULL,
  `valid_category` varchar(250) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;";
$result = logged_query($query,0,array());
if($result === false) die('Error Creating Collection Revision Table');

$query ="CREATE TABLE IF NOT EXISTS `ecom_coupons` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `code` varchar(30) NOT NULL,
  `exp` varchar(30) NOT NULL,
  `discount` decimal(4,2) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;";
$result = logged_query($query,0,array());
if($result === false) die('Error Creating Coupons Table');

$query ="CREATE TABLE IF NOT EXISTS `ecom_image` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `item_id` int(11) NOT NULL,
  `posn` int(4) NOT NULL,
  `name` varchar(250) NOT NULL,
  `desc` text NOT NULL,
  `html_desc` text NOT NULL,
  `alt` varchar(250) NOT NULL,
  `date` varchar(30) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;";
$result = logged_query($query,0,array());
if($result === false) die('Error Creating Products Image Table');

$query ="CREATE TABLE IF NOT EXISTS `ecom_moneris_prefs` (
  `id` int(11) NOT NULL DEFAULT '1',
  `profile_key` varchar(254) DEFAULT NULL,
  `store_id` varchar(254) DEFAULT NULL,
  `api_token` varchar(254) DEFAULT NULL,
  `email` varchar(254) DEFAULT NULL,
  `dev_url` varchar(254) NOT NULL DEFAULT 'http://esqa.moneris.com',
  `live_url` varchar(254) NOT NULL DEFAULT 'http://www3.moneris.com',
  `crypt_type` int(1) NOT NULL DEFAULT '7',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;";
$result = logged_query($query,0,array());
if($result === false) die('Error Creating Moneris Preferences Table');

$query = "SELECT `id` FROM `ecom_paypal_prefs` WHERE `id`=1";
$result = logged_query($query,0,array());
if($result === false) die('Error Creating Moneris Preferences Table');
if(!isset($result[0])) {
	$query = "INSERT INTO `ecom_moneris_prefs` (`id`, `profile_key`, `store_id`, `api_token`, `email`, `dev_url`, `live_url`, `crypt_type`) VALUES
(1, NULL, NULL, NULL, NULL, NULL, NULL, 7);";
	$result = logged_query($query,0,array());
	if($result === false) die ('Error Creating Moneris Table');
}

$query ="CREATE TABLE IF NOT EXISTS `ecom_moneris_txn` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `order_id` int(11) NOT NULL,
  `approved` int(1) NOT NULL,
  `DataKey` varchar(30) NOT NULL,
  `ReceiptId` varchar(20) NOT NULL,
  `ReferenceNum` varchar(18) NOT NULL,
  `ResponseCode` int(3) NOT NULL,
  `ISO` int(2) NOT NULL,
  `AuthCode` varchar(8) NOT NULL,
  `Message` varchar(100) NOT NULL,
  `TransTime` time NOT NULL,
  `TransDate` date NOT NULL,
  `TransType` int(2) NOT NULL,
  `Complete` varchar(5) NOT NULL,
  `TransAmount` decimal(10,2) NOT NULL,
  `CardType` varchar(1) NOT NULL,
  `TransID` varchar(250) NOT NULL,
  `TimedOut` varchar(5) NOT NULL,
  `CorporateCard` varchar(5) NOT NULL,
  `RecurSuccess` varchar(5) DEFAULT NULL,
  `AvsResultCode` varchar(1) DEFAULT NULL,
  `CvdResultCode` varchar(2) DEFAULT NULL,
  `ResSuccess` varchar(5) NOT NULL,
  `PaymentType` varchar(5) NOT NULL,
  `IsVisaDebit` varchar(5) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1;";
$result = logged_query($query,0,array());
if($result === false) die('Error Creating Moneris Txn Table');

$query ="CREATE TABLE IF NOT EXISTS `ecom_multi_price` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `prod_id` int(11) NOT NULL,
  `qty_from` int(11) NOT NULL DEFAULT '0',
  `qty_to` int(11) DEFAULT NULL,
  `price_difference` decimal(6,2) NOT NULL DEFAULT '0.00',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;";
$result = logged_query($query,0,array());
if($result === false) die('Error Creating Multi Price Table');

$query ="CREATE TABLE IF NOT EXISTS `ecom_orders` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `txn_id` varchar(250) NOT NULL,
  `info` text NOT NULL,
  `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `ip` text NOT NULL,
  `md5` text NOT NULL,
  `confirm` int(11) NOT NULL,
  `shipped` tinyint(1) NOT NULL DEFAULT '0',
  `ship_method` varchar(254) NOT NULL,
  `ship_price` decimal(10,2) NOT NULL,
  `tax_name` varchar(250) NOT NULL,
  `tax` decimal(11,2) NOT NULL,
  `subtotal` decimal(10,2) NOT NULL,
  `total` decimal(10,2) NOT NULL,
  `first_name` tinytext,
  `last_name` tinytext,
  `address1` tinytext,
  `address2` tinytext,
  `city` tinytext,
  `province` tinytext,
  `postal_code` tinytext,
  `country` tinytext,
  `email` tinytext,
  `phone` tinytext,
  `artifact_id` bigint(20) DEFAULT NULL,
  `shipment_id` bigint(20) DEFAULT NULL,
  `tracking_pin` bigint(20) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1;";
$result = logged_query($query,0,array());
if($result === false) die('Error Creating Orders Table');

$query ="CREATE TABLE IF NOT EXISTS `ecom_orders_shipping` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `order_id` int(11) NOT NULL,
  `first_name` tinytext NOT NULL,
  `last_name` tinytext NOT NULL,
  `address1` tinytext NOT NULL,
  `address2` tinytext NOT NULL,
  `city` tinytext NOT NULL,
  `province` tinytext NOT NULL,
  `postal_code` tinytext NOT NULL,
  `country` tinytext NOT NULL,
  `notes` text,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1;";
$result = logged_query($query,0,array());
if($result === false) die('Error Creating Orders-Shipping Table');

$query ="CREATE TABLE IF NOT EXISTS `ecom_paypal_prefs` (
  `id` int(11) NOT NULL DEFAULT '1',
  `dev_paypal` varchar(254) NOT NULL,
  `dev_orderemail` varchar(254) NOT NULL,
  `live_paypal` varchar(254) DEFAULT NULL,
  `live_orderemail` varchar(254) DEFAULT NULL, 
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;";
$result = logged_query($query,0,array());
if($result === false) die('Error Creating Paypal-Prefs Table 1');

$query = "SELECT `id` FROM `ecom_paypal_prefs` WHERE `id`=1";
$result = logged_query($query,0,array());
if($result === false) die('Error Creating Paypal-Prefs Table 2');
if(!isset($result[0])) {
	$query = "INSERT INTO `ecom_paypal_prefs` (`id`, `dev_paypal`, `dev_orderemail`, `live_paypal`, `live_orderemail`) VALUES
(1, '', '', '', '');";
	$result = logged_query($query,0,array());
	if($result === false) die ('Error Creating Paypal-Prefs Table 3');
}

$query ="CREATE TABLE IF NOT EXISTS `ecom_preferences` (
  `id` int(11) NOT NULL DEFAULT '1',
  `hasCart` tinyint(1) NOT NULL DEFAULT '0',
  `includePricing` tinyint(1) NOT NULL DEFAULT '0',
  `altPriceText` varchar(50) NOT NULL DEFAULT 'call for pricing',
  `limit_collection` tinyint(1) NOT NULL DEFAULT '0',
  `one_collection_per_product` tinyint(1) NOT NULL DEFAULT '0',
  `currency` varchar(3) NOT NULL DEFAULT 'CAD',
  `orderemail` varchar(254) NOT NULL,
  `phone` varchar(25) NOT NULL,
  `purchasing_on` tinyint(1) NOT NULL DEFAULT '1',
  `shipping_on` tinyint(1) NOT NULL DEFAULT '1',
  `no_note` tinyint(1) NOT NULL DEFAULT '0',
  `note_comment` varchar(100) NOT NULL,
  `no_shipping` tinyint(1) NOT NULL DEFAULT '1',
  `environment` varchar(4) NOT NULL DEFAULT 'dev',
  `prod_per_page` int(11) NOT NULL DEFAULT '10',
  `name` varchar(250) NOT NULL,
  `desc` text NOT NULL,
  `seo_title` text NOT NULL,
  `seo_description` text NOT NULL,
  `seo_keywords` text NOT NULL,
  `date_created` varchar(30) NOT NULL,
  `date_updated` varchar(30) NOT NULL,
  `gateway` varchar(20) NOT NULL DEFAULT 'nogateway',
  `returns_policy` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;";
$result = logged_query($query,0,array());
if($result === false) die('Error Creating Preferences Table');

$query = "SELECT `id` FROM `ecom_preferences` WHERE `id`=1";
$result = logged_query($query,0,array());

if(!isset($result[0])) {
	$query = "INSERT INTO `ecom_preferences` (`id`, `hasCart`, `includePricing`, `altPriceText`, `limit_collection`, `one_collection_per_product`, `currency`, `orderemail`, `phone`, `purchasing_on`, `shipping_on`, `no_note`, `note_comment`, `no_shipping`, `environment`, `prod_per_page`, `name`, `desc`, `seo_title`, `seo_description`, `seo_keywords`, `date_created`, `date_updated`, `gateway`, `returns_policy`) VALUES
(1, 1, 1, 'call for pricing', 0, 0, 'CAD', 'test@test.com', '18002788730', 1, 1, 0, 'Add Special Instructions', 1, 'dev', 9, 'Products List', '&lt;p&gt;This is the products page text&lt;/p&gt;', 'Shopping Cart', 'Shopping Cart', 'Shopping Cart', '".date('Y-m-d H:i:s')."', '".date('Y-m-d H:i:s')."', 'moneris', '30 days - must be unopened. 10% restocking charge.');";
	$result = logged_query($query,0,array());
	if($result === false) die ('Error Creating Preferences Table');
}

$query ="CREATE TABLE IF NOT EXISTS `ecom_preferences_rev` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `pref_id` int(11) NOT NULL DEFAULT '1',
  `hasCart` tinyint(1) NOT NULL,
  `includePricing` tinyint(1) NOT NULL,
  `altPriceText` varchar(50) NOT NULL,
  `limit_collection` tinyint(1) NOT NULL,
  `one_collection_per_product` tinyint(1) NOT NULL,
  `currency` varchar(3) NOT NULL,
  `orderemail` varchar(254) NOT NULL,
  `phone` varchar(25) NOT NULL,
  `purchasing_on` tinyint(1) NOT NULL,
  `shipping_on` tinyint(1) NOT NULL,
  `no_note` tinyint(1) NOT NULL,
  `note_comment` varchar(100) NOT NULL,
  `no_shipping` tinyint(1) NOT NULL,
  `environment` varchar(4) NOT NULL,
  `paypal_on` tinyint(1) NOT NULL,
  `prod_per_page` int(11) NOT NULL,
  `name` varchar(250) NOT NULL,
  `desc` text NOT NULL,
  `seo_title` text NOT NULL,
  `seo_description` text NOT NULL,
  `seo_keywords` text NOT NULL,
  `dev_paypal` varchar(254) NOT NULL,
  `live_paypal` varchar(254) DEFAULT NULL,
  `dev_orderemail` varchar(254) NOT NULL,
  `live_orderemail` varchar(254) DEFAULT NULL,
  `date_created` varchar(30) NOT NULL,
  `date_updated` varchar(30) NOT NULL,
  `returns_policy` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1;";
$result = logged_query($query,0,array());
if($result === false) die('Error Creating Preferences Revision Table');

$query ="CREATE TABLE IF NOT EXISTS `ecom_product` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `url` text NOT NULL,
  `title` varchar(250) NOT NULL,
  `desc` text NOT NULL,
  `short_desc` text NOT NULL,
  `specs` text,
  `price` decimal(11,2) NOT NULL,
  `sale` decimal(11,2) NOT NULL DEFAULT '0.00',
  `q1` int(11) NOT NULL,
  `q2` int(11) NOT NULL,
  `price2` decimal(11,2) NOT NULL,
  `q3` int(11) NOT NULL,
  `q4` int(11) NOT NULL,
  `price3` decimal(11,2) NOT NULL,
  `q5` int(11) NOT NULL,
  `q6` int(11) NOT NULL,
  `seo_title` text NOT NULL,
  `seo_keywords` text NOT NULL,
  `seo_description` text NOT NULL,
  `category_id` int(11) NOT NULL,
  `product_type` int(11) NOT NULL DEFAULT '1',
  `featured` tinyint(1) NOT NULL,
  `status` int(1) NOT NULL DEFAULT '0',
  `date_created` varchar(30) NOT NULL,
  `date_updated` varchar(30) NOT NULL,
  `weight_lb` decimal(7,4) NOT NULL DEFAULT '0.0000',
  `weight_kg` decimal(7,4) NOT NULL DEFAULT '0.0000',
  `agent` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1;";
$result = logged_query($query,0,array());
if($result === false) die('Error Creating Product Table');

$query ="CREATE TABLE IF NOT EXISTS `ecom_product_options` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `prod_id` int(11) NOT NULL,
  `opt_name` varchar(250) NOT NULL,
  `option` varchar(250) NOT NULL,
  `price` decimal(11,2) NOT NULL DEFAULT '0.00',
  `weight` decimal(9,4) NOT NULL DEFAULT '0.0000',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;";
$result = logged_query($query,0,array());
if($result === false) die('Error Creating Product Options Table');

$query ="CREATE TABLE IF NOT EXISTS `ecom_product_rev` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `url` text NOT NULL,
  `title` varchar(250) NOT NULL,
  `desc` text NOT NULL,
  `short_desc` text NOT NULL,
  `specs` text,
  `price` decimal(11,2) NOT NULL,
  `sale` decimal(11,2) NOT NULL,
  `q1` int(11) NOT NULL,
  `q2` int(11) NOT NULL,
  `price2` decimal(11,2) NOT NULL,
  `q3` int(11) NOT NULL,
  `q4` int(11) NOT NULL,
  `price3` decimal(11,2) NOT NULL,
  `q5` int(11) NOT NULL,
  `q6` int(11) NOT NULL,
  `seo_title` text NOT NULL,
  `seo_keywords` text NOT NULL,
  `seo_description` text NOT NULL,
  `category_id` int(11) NOT NULL,
  `product_type` int(11) NOT NULL DEFAULT '1',
  `featured` tinyint(1) NOT NULL,
  `status` int(1) NOT NULL,
  `date_created` varchar(30) NOT NULL,
  `date_updated` varchar(30) NOT NULL,
  `weight_lb` decimal(7,4) NOT NULL DEFAULT '0.0000',
  `weight_kg` decimal(7,4) NOT NULL DEFAULT '0.0000',
  `agent` int(11) DEFAULT NULL,
  `ecom_prod_id` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1;";
$result = logged_query($query,0,array());
if($result === false) die('Error Creating Product Revision Table');

$query ="CREATE TABLE IF NOT EXISTS `ecom_prod_in_col` (
  `product_id` int(11) NOT NULL,
  `collection_id` int(11) NOT NULL,
  PRIMARY KEY (`product_id`,`collection_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;";
$result = logged_query($query,0,array());
if($result === false) die('Error Creating Products in Collection Table');

$query ="CREATE TABLE IF NOT EXISTS `ecom_ship_prefs` (
  `id` int(11) NOT NULL,
  `ship_per` decimal(11,2) NOT NULL,
  `dimensionUnit` varchar(2) NOT NULL DEFAULT 'CM',
  `weight_type` varchar(2) NOT NULL DEFAULT 'KG',
  `free_ship` decimal(11,2) NOT NULL DEFAULT '0.00',
  `min_ship` decimal(11,2) NOT NULL DEFAULT '0.00',
  `max_ship` decimal(11,2) NOT NULL DEFAULT '0.00',
  `free_shipping` tinyint(1) NOT NULL DEFAULT '1',
  `street_address` varchar(254) NOT NULL,
  `city` varchar(120) NOT NULL,
  `province` varchar(2) NOT NULL,
  `postal_code` varchar(20) NOT NULL,
  `country` varchar(2) NOT NULL,
  `fedex_on` tinyint(1) NOT NULL DEFAULT '0',
  `fedex_key` tinytext,
  `fedex_password` tinytext,
  `fedex_meter` tinytext,
  `fedex_shipaccount` tinytext,
  `fedex_billaccount` tinytext,
  `fedex_dutyaccount` tinytext,
  `fedex_freightaccount` tinytext,
  `fedex_trackaccount` tinytext,
  `canpost_on` tinyint(1) NOT NULL DEFAULT '0',
  `cp_username` tinytext,
  `cp_password` tinytext,
  `cp_customerNumber` tinytext,
  `date_created` varchar(30) NOT NULL,
  `date_updated` varchar(30) NOT NULL,
  `inhousemethod` varchar(40) DEFAULT NULL,
  `discount` decimal(11,2) NOT NULL DEFAULT '0.00',
  `discount_min` decimal(11,2) NOT NULL DEFAULT '0.00',
  `process_text` varchar(256) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;";
$result = logged_query($query,0,array());
if($result === false) die('Error Creating Shipping Preferences Table');

$query = "SELECT `id` FROM `ecom_ship_prefs` WHERE `id`=1";
$result = logged_query($query,0,array());

if(!isset($result[0])) {
	$query = "INSERT INTO `ecom_ship_prefs` (`id`, `ship_per`, `dimensionUnit`, `weight_type`, `free_ship`, `min_ship`, `max_ship`, `free_shipping`, `street_address`, `city`, `province`, `postal_code`, `country`, `fedex_on`, `fedex_key`, `fedex_password`, `fedex_meter`, `fedex_shipaccount`, `fedex_billaccount`, `fedex_dutyaccount`, `fedex_freightaccount`, `fedex_trackaccount`, `canpost_on`, `cp_username`, `cp_password`, `cp_customerNumber`, `date_created`, `date_updated`, `inhousemethod`, `discount`, `discount_min`, `process_text`) VALUES
(1, 2.40, 'CM', 'KG', 0.00, 4.80, 0.00, 0, '10598 58A Ave.', 'Surrey', 'BC', 'V3S 5H1', 'CA', 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, NULL, NULL, NULL, '".date('Y-m-d H:i:s')."', '".date('Y-m-d H:i:s')."', 'In-Store Pickup', 0.00, 0.00, NULL);";
	$result = logged_query($query,0,array());
	if($result === false) die ('Error Creating Shipping Preferences Table');
}

$query ="CREATE TABLE IF NOT EXISTS `ecom_ship_prefs_rev` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `pref_id` int(11) NOT NULL,
  `ship_per` decimal(11,2) NOT NULL,
  `dimensionUnit` varchar(2) NOT NULL,
  `weight_type` varchar(2) NOT NULL,
  `free_ship` decimal(11,2) NOT NULL DEFAULT '0.00',
  `min_ship` decimal(11,2) NOT NULL DEFAULT '0.00',
  `max_ship` decimal(11,2) NOT NULL DEFAULT '0.00',
  `free_shipping` tinyint(1) NOT NULL,
  `street_address` varchar(254) NOT NULL,
  `city` varchar(120) NOT NULL,
  `province` varchar(2) NOT NULL,
  `postal_code` varchar(20) NOT NULL,
  `country` varchar(2) NOT NULL,
  `fedex_on` tinyint(1) NOT NULL,
  `fedex_key` tinytext,
  `fedex_password` tinytext,
  `fedex_meter` tinytext,
  `fedex_shipaccount` tinytext,
  `fedex_billaccount` tinytext,
  `fedex_dutyaccount` tinytext,
  `fedex_freightaccount` tinytext,
  `fedex_trackaccount` tinytext,
  `canpost_on` tinyint(1) NOT NULL,
  `cp_username` tinytext,
  `cp_password` tinytext,
  `cp_customerNumber` tinytext,
  `date_created` varchar(30) NOT NULL,
  `date_updated` varchar(30) NOT NULL,
  `inhousemethod` varchar(40) DEFAULT NULL,
  `discount` decimal(11,2) NOT NULL DEFAULT '0.00',
  `discount_min` decimal(11,2) NOT NULL DEFAULT '0.00',
  `process_text` varchar(256) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1;";
$result = logged_query($query,0,array());
if($result === false) die('Error Creating Shipping Preferences Revision Table');

$query ="CREATE TABLE IF NOT EXISTS `ecom_taxes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `tax` decimal(4,2) NOT NULL,
  `tax_name` varchar(250) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1;";
$result = logged_query($query,0,array());
if($result === false) die('Error Creating Taxes Table');

$query ="CREATE TABLE IF NOT EXISTS `ecom_tmp_category` (
  `category_data_id` int(11) NOT NULL,
  `lft` int(11) NOT NULL,
  `rgt` int(11) NOT NULL,
  PRIMARY KEY (`category_data_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;";
$result = logged_query($query,0,array());
if($result === false) die('Error Creating Tmp Category Table');

	if($result) $moduleAdded['Products'] = true;


/*	EMPTY SAMPLE

$query ="CREATE";
$result = logged_query($query,0,array());
if($result === false) die('Error Creating Products Table');

$query = "SELECT `id` FROM `` WHERE `id`=1";
$result = logged_query($query,0,array());
if($result === false) die('Error Creating Products Table');
if(!isset($result[0])) {
	$query = "INSERT INTO ";
	$result = logged_query($query,0,array());
	if($result === false) die ('Error Creating Products Table');
	if($result) $moduleAdded['Products'] = true;
}

//insert current dates with ".date('Y-m-d H:i:s')."

*/
