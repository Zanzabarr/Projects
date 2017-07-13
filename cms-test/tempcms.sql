-- phpMyAdmin SQL Dump
-- version 4.5.4.1deb2ubuntu2
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Jul 12, 2017 at 11:32 PM
-- Server version: 5.7.17-0ubuntu0.16.04.1
-- PHP Version: 7.0.8-0ubuntu0.16.04.2

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `tempcms`
--

-- --------------------------------------------------------

--
-- Table structure for table `auth_users`
--

CREATE TABLE `auth_users` (
  `user_id` int(11) NOT NULL,
  `first_name` varchar(50) DEFAULT NULL,
  `last_name` varchar(50) DEFAULT NULL,
  `company_name` varchar(150) NOT NULL,
  `office_address` varchar(150) NOT NULL,
  `office_city` varchar(150) NOT NULL,
  `office_postal` varchar(10) NOT NULL,
  `office_number` varchar(15) NOT NULL,
  `fax_number` varchar(15) NOT NULL,
  `cell_number` varchar(15) NOT NULL,
  `email` varchar(150) DEFAULT NULL,
  `timezone` varchar(150) DEFAULT 'America/Los_Angeles',
  `username` varchar(25) DEFAULT NULL,
  `password` varchar(100) DEFAULT NULL,
  `tmp_password` varchar(100) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `tmp_password_date` datetime NOT NULL,
  `admin` varchar(3) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Dumping data for table `auth_users`
--

INSERT INTO `auth_users` (`user_id`, `first_name`, `last_name`, `company_name`, `office_address`, `office_city`, `office_postal`, `office_number`, `fax_number`, `cell_number`, `email`, `timezone`, `username`, `password`, `tmp_password`, `tmp_password_date`, `admin`) VALUES
(1, 'Ryan', 'Osler', 'sf', 'sf', 'sf', 'sf', 's', 'f', 'sf', 'Rosler19@hotmail.com', 'America/Los_Angeles', 'admin', '$2a$08$hRIkaa2MesdvRre90ApK3eMVX/4rrAq3Jh6FnTWVZadv/Qc1j4bXuhRIkaa2MesdvRre90ApK3t', '', '0000-00-00 00:00:00', 'yes');

-- --------------------------------------------------------

--
-- Table structure for table `auth_users_permit`
--

CREATE TABLE `auth_users_permit` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `module` varchar(256) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Dumping data for table `auth_users_permit`
--

INSERT INTO `auth_users_permit` (`id`, `user_id`, `module`) VALUES
(7, 5, 'testimonials'),
(8, 6, 'pages'),
(9, 6, 'banners'),
(10, 6, 'store_locator'),
(11, 6, 'testimonials'),
(12, 6, 'blog'),
(13, 6, 'galleries'),
(14, 6, 'newsletter'),
(15, 6, 'events'),
(16, 6, 'members'),
(17, 6, 'news_items'),
(18, 6, 'shopping_cart');

-- --------------------------------------------------------

--
-- Table structure for table `banners`
--

CREATE TABLE `banners` (
  `id` int(11) NOT NULL,
  `name` varchar(256) NOT NULL,
  `alt` varchar(256) DEFAULT NULL,
  `link` varchar(250) DEFAULT NULL,
  `desc` text,
  `html_desc` text,
  `posn` int(4) NOT NULL DEFAULT '0',
  `date` varchar(30) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `blog_cat`
--

CREATE TABLE `blog_cat` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `url` text NOT NULL,
  `title` text NOT NULL,
  `seo_title` text NOT NULL,
  `seo_keywords` text NOT NULL,
  `seo_description` text NOT NULL,
  `status` int(1) NOT NULL,
  `date` varchar(30) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `blog_cat_rev`
--

CREATE TABLE `blog_cat_rev` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `blog_cat_id` int(11) NOT NULL,
  `url` text NOT NULL,
  `title` text NOT NULL,
  `desc` text NOT NULL,
  `seo_title` text NOT NULL,
  `seo_keywords` text NOT NULL,
  `seo_description` text NOT NULL,
  `status` int(1) NOT NULL,
  `date` varchar(30) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `blog_comments`
--

CREATE TABLE `blog_comments` (
  `id` int(11) NOT NULL,
  `post_id` int(11) NOT NULL,
  `post` text NOT NULL,
  `name` varchar(25) NOT NULL,
  `email` varchar(254) NOT NULL,
  `comments` text NOT NULL,
  `approve` int(1) NOT NULL DEFAULT '0',
  `time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `blog_home`
--

CREATE TABLE `blog_home` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `title` text NOT NULL,
  `url` text NOT NULL,
  `desc` text NOT NULL,
  `seo_title` text NOT NULL,
  `seo_keywords` text NOT NULL,
  `seo_description` text NOT NULL,
  `status` int(1) NOT NULL DEFAULT '0',
  `date` varchar(30) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `blog_home_rev`
--

CREATE TABLE `blog_home_rev` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `blog_home_id` int(11) NOT NULL,
  `title` text NOT NULL,
  `url` text NOT NULL,
  `desc` text NOT NULL,
  `seo_title` text NOT NULL,
  `seo_keywords` text NOT NULL,
  `seo_description` text NOT NULL,
  `status` int(1) NOT NULL DEFAULT '0',
  `date` varchar(30) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `blog_options`
--

CREATE TABLE `blog_options` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL DEFAULT '1',
  `post_per_pg` int(11) NOT NULL DEFAULT '1',
  `show_date` tinyint(1) NOT NULL DEFAULT '1',
  `show_author` tinyint(1) NOT NULL DEFAULT '1',
  `comments` int(1) NOT NULL DEFAULT '1',
  `approve` int(1) NOT NULL DEFAULT '1',
  `com_per_pg` int(11) NOT NULL DEFAULT '10'
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `blog_post`
--

CREATE TABLE `blog_post` (
  `id` int(11) NOT NULL,
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
  `url` text NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `blog_post_rev`
--

CREATE TABLE `blog_post_rev` (
  `id` int(11) NOT NULL,
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
  `url` text NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `ecom_category`
--

CREATE TABLE `ecom_category` (
  `category_data_id` int(11) NOT NULL,
  `lft` int(11) NOT NULL,
  `rgt` int(11) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `ecom_category_data`
--

CREATE TABLE `ecom_category_data` (
  `id` int(11) NOT NULL,
  `url` varchar(100) NOT NULL,
  `name` varchar(100) NOT NULL,
  `desc` text NOT NULL,
  `seo_keywords` text NOT NULL,
  `seo_description` varchar(250) NOT NULL,
  `seo_title` varchar(100) NOT NULL,
  `status` tinyint(1) NOT NULL,
  `date_created` varchar(30) NOT NULL DEFAULT 'UTC_TIMESTAMP()',
  `date_updated` varchar(30) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `ecom_category_data_rev`
--

CREATE TABLE `ecom_category_data_rev` (
  `id` int(11) NOT NULL,
  `ecom_cat_data_id` int(11) NOT NULL,
  `url` varchar(100) NOT NULL,
  `name` varchar(100) NOT NULL,
  `desc` text NOT NULL,
  `seo_keywords` text NOT NULL,
  `seo_description` varchar(250) NOT NULL,
  `seo_title` varchar(100) NOT NULL,
  `status` tinyint(1) NOT NULL,
  `date_created` varchar(30) NOT NULL,
  `date_updated` varchar(30) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `ecom_collection`
--

CREATE TABLE `ecom_collection` (
  `id` int(11) NOT NULL,
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
  `valid_category` text NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `ecom_collection_rev`
--

CREATE TABLE `ecom_collection_rev` (
  `id` int(11) NOT NULL,
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
  `valid_category` varchar(250) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `ecom_coupons`
--

CREATE TABLE `ecom_coupons` (
  `id` int(11) NOT NULL,
  `code` varchar(30) NOT NULL,
  `exp` varchar(30) NOT NULL,
  `discount` decimal(4,2) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `ecom_image`
--

CREATE TABLE `ecom_image` (
  `id` int(11) NOT NULL,
  `item_id` int(11) NOT NULL,
  `posn` int(4) NOT NULL,
  `name` varchar(250) NOT NULL,
  `desc` text NOT NULL,
  `html_desc` text NOT NULL,
  `alt` varchar(250) NOT NULL,
  `date` varchar(30) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `ecom_moneris_prefs`
--

CREATE TABLE `ecom_moneris_prefs` (
  `id` int(11) NOT NULL DEFAULT '1',
  `profile_key` varchar(254) DEFAULT NULL,
  `store_id` varchar(254) DEFAULT NULL,
  `api_token` varchar(254) DEFAULT NULL,
  `email` varchar(254) DEFAULT NULL,
  `dev_url` varchar(254) NOT NULL DEFAULT 'http://esqa.moneris.com',
  `live_url` varchar(254) NOT NULL DEFAULT 'http://www3.moneris.com',
  `crypt_type` int(1) NOT NULL DEFAULT '7'
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `ecom_moneris_txn`
--

CREATE TABLE `ecom_moneris_txn` (
  `id` int(11) NOT NULL,
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
  `IsVisaDebit` varchar(5) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `ecom_multi_price`
--

CREATE TABLE `ecom_multi_price` (
  `id` int(11) NOT NULL,
  `prod_id` int(11) NOT NULL,
  `qty_from` int(11) NOT NULL DEFAULT '0',
  `qty_to` int(11) DEFAULT NULL,
  `price_difference` decimal(6,2) NOT NULL DEFAULT '0.00'
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `ecom_orders`
--

CREATE TABLE `ecom_orders` (
  `id` int(11) NOT NULL,
  `txn_id` varchar(250) NOT NULL,
  `info` text NOT NULL,
  `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `ip` text NOT NULL,
  `md5` text NOT NULL,
  `confirm` int(11) NOT NULL,
  `shipped` tinyint(1) NOT NULL DEFAULT '0',
  `ship_method` varchar(254) NOT NULL,
  `ship_price` decimal(10,2) NOT NULL,
  `tax_name` varchar(250) DEFAULT NULL,
  `tax` decimal(4,2) NOT NULL DEFAULT '0.00',
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
  `shipdiscount` decimal(11,2) NOT NULL DEFAULT '0.00'
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `ecom_orders_shipping`
--

CREATE TABLE `ecom_orders_shipping` (
  `id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `first_name` tinytext NOT NULL,
  `last_name` tinytext NOT NULL,
  `address1` tinytext NOT NULL,
  `address2` tinytext NOT NULL,
  `city` tinytext NOT NULL,
  `province` tinytext NOT NULL,
  `postal_code` tinytext NOT NULL,
  `country` tinytext NOT NULL,
  `notes` text
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `ecom_paypal_prefs`
--

CREATE TABLE `ecom_paypal_prefs` (
  `id` int(11) NOT NULL DEFAULT '1',
  `dev_paypal` varchar(254) NOT NULL,
  `dev_orderemail` varchar(254) NOT NULL,
  `live_paypal` varchar(254) DEFAULT NULL,
  `live_orderemail` varchar(254) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `ecom_preferences`
--

CREATE TABLE `ecom_preferences` (
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
  `prod_per_page` int(11) NOT NULL DEFAULT '9',
  `name` varchar(250) NOT NULL,
  `desc` text NOT NULL,
  `seo_title` text NOT NULL,
  `seo_description` text NOT NULL,
  `seo_keywords` text NOT NULL,
  `date_created` varchar(30) NOT NULL,
  `date_updated` varchar(30) NOT NULL,
  `gateway` varchar(20) NOT NULL DEFAULT 'nogateway',
  `returns_policy` text NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `ecom_preferences_rev`
--

CREATE TABLE `ecom_preferences_rev` (
  `id` int(11) NOT NULL,
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
  `prod_per_page` int(11) NOT NULL,
  `name` varchar(250) NOT NULL,
  `desc` text NOT NULL,
  `seo_title` text NOT NULL,
  `seo_description` text NOT NULL,
  `seo_keywords` text NOT NULL,
  `date_created` varchar(30) NOT NULL,
  `date_updated` varchar(30) NOT NULL,
  `gateway` varchar(20) NOT NULL,
  `returns_policy` text NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `ecom_product`
--

CREATE TABLE `ecom_product` (
  `id` int(11) NOT NULL,
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
  `weight_kg` decimal(7,4) NOT NULL,
  `weight_lb` decimal(7,4) NOT NULL,
  `agent` int(11) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `ecom_product_options`
--

CREATE TABLE `ecom_product_options` (
  `id` int(11) NOT NULL,
  `prod_id` int(11) NOT NULL,
  `opt_name` varchar(250) NOT NULL,
  `option` varchar(250) NOT NULL,
  `price` decimal(11,2) NOT NULL DEFAULT '0.00',
  `weight` decimal(9,4) NOT NULL DEFAULT '0.0000'
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `ecom_product_rev`
--

CREATE TABLE `ecom_product_rev` (
  `id` int(11) NOT NULL,
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
  `weight_kg` decimal(7,4) NOT NULL,
  `weight_lb` decimal(7,4) NOT NULL,
  `agent` int(11) DEFAULT NULL,
  `ecom_prod_id` int(11) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `ecom_prod_in_col`
--

CREATE TABLE `ecom_prod_in_col` (
  `product_id` int(11) NOT NULL,
  `collection_id` int(11) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `ecom_ship_prefs`
--

CREATE TABLE `ecom_ship_prefs` (
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
  `flat_rate` decimal(11,2) NOT NULL DEFAULT '0.00',
  `flat_rate_days` int(11) NOT NULL DEFAULT '3'
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `ecom_ship_prefs_rev`
--

CREATE TABLE `ecom_ship_prefs_rev` (
  `id` int(11) NOT NULL,
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
  `flat_rate` decimal(11,2) NOT NULL,
  `flat_rate_days` int(11) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `ecom_taxes`
--

CREATE TABLE `ecom_taxes` (
  `id` int(11) NOT NULL,
  `tax` decimal(4,2) NOT NULL,
  `tax_name` varchar(250) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `ecom_taxes_rev`
--

CREATE TABLE `ecom_taxes_rev` (
  `id` int(11) NOT NULL,
  `tax_id` int(11) NOT NULL,
  `tax` decimal(11,2) NOT NULL,
  `tax_name` varchar(250) NOT NULL,
  `tax2` decimal(2,2) NOT NULL,
  `tax2_name` varchar(250) NOT NULL,
  `date_created` varchar(30) NOT NULL,
  `date_updated` varchar(30) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `ecom_tmp_category`
--

CREATE TABLE `ecom_tmp_category` (
  `category_data_id` int(11) NOT NULL,
  `lft` int(11) NOT NULL,
  `rgt` int(11) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `events`
--

CREATE TABLE `events` (
  `id` int(11) NOT NULL,
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
  `custom_date` text CHARACTER SET utf8
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `events_cat`
--

CREATE TABLE `events_cat` (
  `id` int(11) NOT NULL,
  `title` text CHARACTER SET utf8 NOT NULL,
  `url` text CHARACTER SET utf8 NOT NULL,
  `desc` text CHARACTER SET utf8 NOT NULL,
  `status` tinyint(1) NOT NULL DEFAULT '0',
  `seo_title` text CHARACTER SET utf8 NOT NULL,
  `seo_keywords` text CHARACTER SET utf8 NOT NULL,
  `seo_description` text CHARACTER SET utf8 NOT NULL,
  `events_per_pg` int(3) NOT NULL DEFAULT '2'
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `events_colors`
--

CREATE TABLE `events_colors` (
  `id` int(11) NOT NULL,
  `cat_id` int(11) NOT NULL,
  `color` varchar(7) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `events_options`
--

CREATE TABLE `events_options` (
  `id` int(11) NOT NULL DEFAULT '1',
  `events_per_pg` int(3) NOT NULL DEFAULT '2'
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `ftp_folders`
--

CREATE TABLE `ftp_folders` (
  `id` int(11) NOT NULL,
  `folder` varchar(25) NOT NULL,
  `status` tinyint(1) NOT NULL DEFAULT '1',
  `date_updated` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `ftp_user`
--

CREATE TABLE `ftp_user` (
  `id` int(11) NOT NULL COMMENT 'WORKS IN TANDEM WITH MEMBERS MODULE',
  `username` varchar(25) NOT NULL,
  `password` varchar(90) NOT NULL,
  `status` tinyint(1) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `ftp_user_folders`
--

CREATE TABLE `ftp_user_folders` (
  `ftp_user_id` int(11) NOT NULL,
  `folder_id` int(11) NOT NULL,
  `restriction` char(15) NOT NULL DEFAULT 'none' COMMENT 'valid options: none, read only, write only'
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `gallery`
--

CREATE TABLE `gallery` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `title` varchar(256) NOT NULL,
  `sort_type` int(1) NOT NULL DEFAULT '2' COMMENT '1=''chronological'' : 0=''reverse chronological'' : 3=''Alphabetically'' : 2=''Sorted Order''''',
  `status` int(1) NOT NULL DEFAULT '0' COMMENT '0 = ''draft'' 1 = ''published''',
  `date` varchar(30) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `gallery_image`
--

CREATE TABLE `gallery_image` (
  `id` int(11) NOT NULL,
  `gallery_id` int(11) NOT NULL,
  `posn` int(4) NOT NULL COMMENT '0 = ''not in gallery''',
  `name` varchar(256) NOT NULL,
  `desc` text,
  `html_desc` text,
  `alt` varchar(256) NOT NULL,
  `url` varchar(256) NOT NULL,
  `date` varchar(30) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `markers`
--

CREATE TABLE `markers` (
  `id` int(11) NOT NULL,
  `name` varchar(60) NOT NULL,
  `address` varchar(80) NOT NULL,
  `lat` float(10,6) NOT NULL,
  `lng` float(10,6) NOT NULL,
  `opt1` varchar(100) NOT NULL,
  `opt2` varchar(100) NOT NULL,
  `opt3` varchar(100) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `members`
--

CREATE TABLE `members` (
  `id` int(11) NOT NULL,
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
  `confirmation_code` varchar(250) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `members_home`
--

CREATE TABLE `members_home` (
  `id` int(11) NOT NULL DEFAULT '1',
  `title` text CHARACTER SET utf8,
  `desc` text CHARACTER SET utf8,
  `status` tinyint(1) NOT NULL DEFAULT '0',
  `first_page_only` tinyint(1) NOT NULL DEFAULT '1',
  `members_per_pg` tinyint(3) NOT NULL DEFAULT '10',
  `date` datetime NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `members_home_rev`
--

CREATE TABLE `members_home_rev` (
  `id` int(11) NOT NULL,
  `members_home_id` int(11) NOT NULL,
  `title` text CHARACTER SET utf8,
  `desc` text CHARACTER SET utf8,
  `status` tinyint(1) NOT NULL DEFAULT '0',
  `first_page_only` tinyint(1) NOT NULL DEFAULT '1',
  `members_per_pg` tinyint(3) NOT NULL,
  `date` datetime NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `members_options`
--

CREATE TABLE `members_options` (
  `id` int(11) NOT NULL DEFAULT '1',
  `member_req_login` tinyint(1) NOT NULL DEFAULT '1',
  `online_signup` tinyint(1) NOT NULL DEFAULT '0',
  `pay_signup` tinyint(1) NOT NULL DEFAULT '0',
  `renewal_req` tinyint(1) NOT NULL,
  `ftp_front` tinyint(1) NOT NULL DEFAULT '0',
  `members_front` tinyint(1) NOT NULL DEFAULT '1',
  `confirmation_period` tinyint(3) NOT NULL DEFAULT '5',
  `confirm_from` varchar(250) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `confirm_title` varchar(100) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `confirm_body` text CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `members_paypal`
--

CREATE TABLE `members_paypal` (
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
  `confirm_body` text CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `members_signup`
--

CREATE TABLE `members_signup` (
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
  `notify_title` varchar(100) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `notify_body` varchar(500) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `success` text CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `confirmed` text CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `date` datetime NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `members_signup_rev`
--

CREATE TABLE `members_signup_rev` (
  `id` int(11) NOT NULL,
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
  `date` datetime NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `newsletter`
--

CREATE TABLE `newsletter` (
  `id` int(11) NOT NULL,
  `subject` varchar(250) NOT NULL,
  `content` text NOT NULL,
  `status` tinyint(1) NOT NULL COMMENT '0=draft; 1=ready; 2=sent',
  `date_updated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `date_created` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00'
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Triggers `newsletter`
--
DELIMITER $$
CREATE TRIGGER `newsletter_before_insert` BEFORE INSERT ON `newsletter` FOR EACH ROW SET NEW.date_created = NOW(),
      NEW.date_updated = NOW()
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `newsletter_recip`
--

CREATE TABLE `newsletter_recip` (
  `id` int(11) NOT NULL,
  `news_id` int(11) NOT NULL,
  `list_id` varchar(20) NOT NULL,
  `date_sent` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `newsletter_settings`
--

CREATE TABLE `newsletter_settings` (
  `id` int(11) NOT NULL,
  `apikey` varchar(254) NOT NULL,
  `to_name` varchar(254) NOT NULL DEFAULT 'Subscribers'
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `newsletter_subs`
--

CREATE TABLE `newsletter_subs` (
  `id` int(11) NOT NULL,
  `email` varchar(254) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `newsletter_subs_groups`
--

CREATE TABLE `newsletter_subs_groups` (
  `id` int(11) NOT NULL,
  `sub_id` int(11) NOT NULL,
  `list_id` varchar(20) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `news_items`
--

CREATE TABLE `news_items` (
  `id` int(11) NOT NULL,
  `news_item_title` varchar(300) CHARACTER SET utf8 DEFAULT NULL,
  `url` text CHARACTER SET utf8 NOT NULL,
  `contributor_name` varchar(60) CHARACTER SET utf8 NOT NULL,
  `email` varchar(75) CHARACTER SET utf8 DEFAULT NULL,
  `caption` varchar(100) CHARACTER SET utf8 NOT NULL,
  `content` text CHARACTER SET utf8,
  `news_item_image` varchar(100) CHARACTER SET utf8 DEFAULT NULL,
  `status` tinyint(1) NOT NULL DEFAULT '0',
  `date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `news_items_home`
--

CREATE TABLE `news_items_home` (
  `id` int(11) NOT NULL DEFAULT '1',
  `title` text CHARACTER SET utf8,
  `desc` text CHARACTER SET utf8,
  `status` tinyint(1) NOT NULL DEFAULT '0',
  `date` datetime NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `news_items_home_rev`
--

CREATE TABLE `news_items_home_rev` (
  `id` int(11) NOT NULL,
  `news_items_home_id` int(11) NOT NULL,
  `title` text CHARACTER SET utf8,
  `desc` text CHARACTER SET utf8,
  `status` tinyint(1) NOT NULL DEFAULT '0',
  `date` datetime NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `news_items_options`
--

CREATE TABLE `news_items_options` (
  `id` int(11) NOT NULL DEFAULT '1',
  `news_items_per_pg` int(3) NOT NULL DEFAULT '25'
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `pages`
--

CREATE TABLE `pages` (
  `id` int(11) NOT NULL,
  `parent_id` int(11) NOT NULL,
  `has_menu` tinyint(1) DEFAULT '0',
  `menu_order` smallint(3) DEFAULT '0',
  `slug` varchar(150) NOT NULL,
  `page_title` varchar(100) NOT NULL,
  `no_follow` int(1) DEFAULT '0',
  `content` text NOT NULL,
  `seo_keywords` text NOT NULL,
  `seo_description` varchar(250) NOT NULL,
  `seo_title` varchar(100) NOT NULL,
  `status` tinyint(1) DEFAULT '0',
  `visibility` tinyint(1) DEFAULT '0',
  `date` varchar(30) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `page_revisions`
--

CREATE TABLE `page_revisions` (
  `id` int(11) NOT NULL,
  `page_id` int(11) NOT NULL,
  `slug` varchar(150) NOT NULL,
  `page_title` varchar(100) NOT NULL,
  `no_follow` int(1) DEFAULT '0',
  `content` text NOT NULL,
  `seo_keywords` text NOT NULL,
  `seo_description` varchar(250) NOT NULL,
  `seo_title` varchar(100) NOT NULL,
  `status` tinyint(1) DEFAULT '0',
  `visibility` tinyint(1) DEFAULT '0',
  `date` varchar(30) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `paypal_ipn`
--

CREATE TABLE `paypal_ipn` (
  `txn_id` varchar(250) COLLATE utf8_unicode_ci NOT NULL,
  `date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `pictures`
--

CREATE TABLE `pictures` (
  `id` int(11) NOT NULL,
  `page_id` bigint(20) NOT NULL,
  `page_type` varchar(25) DEFAULT 'dflt',
  `filename` varchar(250) NOT NULL,
  `alt` varchar(250) NOT NULL,
  `status` int(1) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `testimonials`
--

CREATE TABLE `testimonials` (
  `id` int(11) NOT NULL,
  `name` text NOT NULL,
  `business` text NOT NULL,
  `short_test` text NOT NULL,
  `full_test` text NOT NULL,
  `status` int(1) NOT NULL DEFAULT '0',
  `date` datetime NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `testimonials_home`
--

CREATE TABLE `testimonials_home` (
  `id` int(11) NOT NULL DEFAULT '1',
  `title` text CHARACTER SET utf8,
  `desc` text CHARACTER SET utf8,
  `status` tinyint(1) NOT NULL DEFAULT '0',
  `date` datetime NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `testimonials_home_rev`
--

CREATE TABLE `testimonials_home_rev` (
  `id` int(11) NOT NULL,
  `testimonials_home_id` int(11) NOT NULL,
  `title` text CHARACTER SET utf8,
  `desc` text CHARACTER SET utf8,
  `status` tinyint(1) NOT NULL DEFAULT '0',
  `date` datetime NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `upload_file`
--

CREATE TABLE `upload_file` (
  `id` int(11) NOT NULL,
  `page_id` bigint(20) NOT NULL,
  `page_type` varchar(25) DEFAULT 'dflt',
  `filename` varchar(250) NOT NULL,
  `alt` varchar(250) NOT NULL,
  `status` int(1) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `auth_users`
--
ALTER TABLE `auth_users`
  ADD PRIMARY KEY (`user_id`);

--
-- Indexes for table `auth_users_permit`
--
ALTER TABLE `auth_users_permit`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `user_module` (`user_id`,`module`);

--
-- Indexes for table `banners`
--
ALTER TABLE `banners`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `blog_cat`
--
ALTER TABLE `blog_cat`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `blog_cat_rev`
--
ALTER TABLE `blog_cat_rev`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `blog_comments`
--
ALTER TABLE `blog_comments`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `blog_home`
--
ALTER TABLE `blog_home`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `blog_home_rev`
--
ALTER TABLE `blog_home_rev`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `blog_options`
--
ALTER TABLE `blog_options`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `blog_post`
--
ALTER TABLE `blog_post`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `blog_post_rev`
--
ALTER TABLE `blog_post_rev`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `ecom_category`
--
ALTER TABLE `ecom_category`
  ADD PRIMARY KEY (`category_data_id`);

--
-- Indexes for table `ecom_category_data`
--
ALTER TABLE `ecom_category_data`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uni` (`url`);

--
-- Indexes for table `ecom_category_data_rev`
--
ALTER TABLE `ecom_category_data_rev`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `ecom_collection`
--
ALTER TABLE `ecom_collection`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `ecom_collection_rev`
--
ALTER TABLE `ecom_collection_rev`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `ecom_coupons`
--
ALTER TABLE `ecom_coupons`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `ecom_image`
--
ALTER TABLE `ecom_image`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `ecom_moneris_prefs`
--
ALTER TABLE `ecom_moneris_prefs`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `ecom_moneris_txn`
--
ALTER TABLE `ecom_moneris_txn`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `ecom_multi_price`
--
ALTER TABLE `ecom_multi_price`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `ecom_orders`
--
ALTER TABLE `ecom_orders`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `ecom_orders_shipping`
--
ALTER TABLE `ecom_orders_shipping`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `ecom_paypal_prefs`
--
ALTER TABLE `ecom_paypal_prefs`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `ecom_preferences`
--
ALTER TABLE `ecom_preferences`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `ecom_preferences_rev`
--
ALTER TABLE `ecom_preferences_rev`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `ecom_product`
--
ALTER TABLE `ecom_product`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `ecom_product_options`
--
ALTER TABLE `ecom_product_options`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `ecom_product_rev`
--
ALTER TABLE `ecom_product_rev`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `ecom_prod_in_col`
--
ALTER TABLE `ecom_prod_in_col`
  ADD PRIMARY KEY (`product_id`,`collection_id`);

--
-- Indexes for table `ecom_ship_prefs`
--
ALTER TABLE `ecom_ship_prefs`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `ecom_ship_prefs_rev`
--
ALTER TABLE `ecom_ship_prefs_rev`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `ecom_taxes`
--
ALTER TABLE `ecom_taxes`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `ecom_taxes_rev`
--
ALTER TABLE `ecom_taxes_rev`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `ecom_tmp_category`
--
ALTER TABLE `ecom_tmp_category`
  ADD PRIMARY KEY (`category_data_id`);

--
-- Indexes for table `events`
--
ALTER TABLE `events`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `events_cat`
--
ALTER TABLE `events_cat`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `events_colors`
--
ALTER TABLE `events_colors`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `cat_id` (`cat_id`);

--
-- Indexes for table `events_options`
--
ALTER TABLE `events_options`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `ftp_folders`
--
ALTER TABLE `ftp_folders`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `ftp_user`
--
ALTER TABLE `ftp_user`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `ftp_user_folders`
--
ALTER TABLE `ftp_user_folders`
  ADD PRIMARY KEY (`ftp_user_id`,`folder_id`);

--
-- Indexes for table `gallery`
--
ALTER TABLE `gallery`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `gallery_image`
--
ALTER TABLE `gallery_image`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `markers`
--
ALTER TABLE `markers`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `members`
--
ALTER TABLE `members`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `members_home`
--
ALTER TABLE `members_home`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `members_home_rev`
--
ALTER TABLE `members_home_rev`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `members_options`
--
ALTER TABLE `members_options`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `members_paypal`
--
ALTER TABLE `members_paypal`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `members_signup`
--
ALTER TABLE `members_signup`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `members_signup_rev`
--
ALTER TABLE `members_signup_rev`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `newsletter`
--
ALTER TABLE `newsletter`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `newsletter_recip`
--
ALTER TABLE `newsletter_recip`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `newsletter_settings`
--
ALTER TABLE `newsletter_settings`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `newsletter_subs`
--
ALTER TABLE `newsletter_subs`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `newsletter_subs_groups`
--
ALTER TABLE `newsletter_subs_groups`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `news_items`
--
ALTER TABLE `news_items`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `news_items_home`
--
ALTER TABLE `news_items_home`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `news_items_home_rev`
--
ALTER TABLE `news_items_home_rev`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `news_items_options`
--
ALTER TABLE `news_items_options`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `pages`
--
ALTER TABLE `pages`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `page_revisions`
--
ALTER TABLE `page_revisions`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `paypal_ipn`
--
ALTER TABLE `paypal_ipn`
  ADD PRIMARY KEY (`txn_id`);

--
-- Indexes for table `pictures`
--
ALTER TABLE `pictures`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `testimonials`
--
ALTER TABLE `testimonials`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `testimonials_home`
--
ALTER TABLE `testimonials_home`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `testimonials_home_rev`
--
ALTER TABLE `testimonials_home_rev`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `upload_file`
--
ALTER TABLE `upload_file`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `auth_users`
--
ALTER TABLE `auth_users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;
--
-- AUTO_INCREMENT for table `auth_users_permit`
--
ALTER TABLE `auth_users_permit`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;
--
-- AUTO_INCREMENT for table `banners`
--
ALTER TABLE `banners`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=58;
--
-- AUTO_INCREMENT for table `blog_cat`
--
ALTER TABLE `blog_cat`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;
--
-- AUTO_INCREMENT for table `blog_cat_rev`
--
ALTER TABLE `blog_cat_rev`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `blog_comments`
--
ALTER TABLE `blog_comments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;
--
-- AUTO_INCREMENT for table `blog_home`
--
ALTER TABLE `blog_home`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;
--
-- AUTO_INCREMENT for table `blog_home_rev`
--
ALTER TABLE `blog_home_rev`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;
--
-- AUTO_INCREMENT for table `blog_options`
--
ALTER TABLE `blog_options`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;
--
-- AUTO_INCREMENT for table `blog_post`
--
ALTER TABLE `blog_post`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;
--
-- AUTO_INCREMENT for table `blog_post_rev`
--
ALTER TABLE `blog_post_rev`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;
--
-- AUTO_INCREMENT for table `ecom_category_data`
--
ALTER TABLE `ecom_category_data`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=39;
--
-- AUTO_INCREMENT for table `ecom_category_data_rev`
--
ALTER TABLE `ecom_category_data_rev`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=60;
--
-- AUTO_INCREMENT for table `ecom_collection`
--
ALTER TABLE `ecom_collection`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;
--
-- AUTO_INCREMENT for table `ecom_collection_rev`
--
ALTER TABLE `ecom_collection_rev`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;
--
-- AUTO_INCREMENT for table `ecom_coupons`
--
ALTER TABLE `ecom_coupons`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;
--
-- AUTO_INCREMENT for table `ecom_image`
--
ALTER TABLE `ecom_image`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=95;
--
-- AUTO_INCREMENT for table `ecom_moneris_txn`
--
ALTER TABLE `ecom_moneris_txn`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `ecom_multi_price`
--
ALTER TABLE `ecom_multi_price`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `ecom_orders`
--
ALTER TABLE `ecom_orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;
--
-- AUTO_INCREMENT for table `ecom_orders_shipping`
--
ALTER TABLE `ecom_orders_shipping`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;
--
-- AUTO_INCREMENT for table `ecom_preferences_rev`
--
ALTER TABLE `ecom_preferences_rev`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;
--
-- AUTO_INCREMENT for table `ecom_product`
--
ALTER TABLE `ecom_product`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;
--
-- AUTO_INCREMENT for table `ecom_product_options`
--
ALTER TABLE `ecom_product_options`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=143;
--
-- AUTO_INCREMENT for table `ecom_product_rev`
--
ALTER TABLE `ecom_product_rev`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=76;
--
-- AUTO_INCREMENT for table `ecom_ship_prefs_rev`
--
ALTER TABLE `ecom_ship_prefs_rev`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;
--
-- AUTO_INCREMENT for table `ecom_taxes`
--
ALTER TABLE `ecom_taxes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;
--
-- AUTO_INCREMENT for table `ecom_taxes_rev`
--
ALTER TABLE `ecom_taxes_rev`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;
--
-- AUTO_INCREMENT for table `events`
--
ALTER TABLE `events`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=106;
--
-- AUTO_INCREMENT for table `events_cat`
--
ALTER TABLE `events_cat`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;
--
-- AUTO_INCREMENT for table `events_colors`
--
ALTER TABLE `events_colors`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;
--
-- AUTO_INCREMENT for table `ftp_folders`
--
ALTER TABLE `ftp_folders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=29;
--
-- AUTO_INCREMENT for table `gallery`
--
ALTER TABLE `gallery`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;
--
-- AUTO_INCREMENT for table `gallery_image`
--
ALTER TABLE `gallery_image`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=98;
--
-- AUTO_INCREMENT for table `markers`
--
ALTER TABLE `markers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;
--
-- AUTO_INCREMENT for table `members`
--
ALTER TABLE `members`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=101;
--
-- AUTO_INCREMENT for table `members_home_rev`
--
ALTER TABLE `members_home_rev`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;
--
-- AUTO_INCREMENT for table `members_signup_rev`
--
ALTER TABLE `members_signup_rev`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=35;
--
-- AUTO_INCREMENT for table `newsletter`
--
ALTER TABLE `newsletter`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;
--
-- AUTO_INCREMENT for table `newsletter_recip`
--
ALTER TABLE `newsletter_recip`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;
--
-- AUTO_INCREMENT for table `newsletter_settings`
--
ALTER TABLE `newsletter_settings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;
--
-- AUTO_INCREMENT for table `newsletter_subs`
--
ALTER TABLE `newsletter_subs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;
--
-- AUTO_INCREMENT for table `newsletter_subs_groups`
--
ALTER TABLE `newsletter_subs_groups`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;
--
-- AUTO_INCREMENT for table `news_items`
--
ALTER TABLE `news_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;
--
-- AUTO_INCREMENT for table `news_items_home_rev`
--
ALTER TABLE `news_items_home_rev`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;
--
-- AUTO_INCREMENT for table `pages`
--
ALTER TABLE `pages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=85;
--
-- AUTO_INCREMENT for table `page_revisions`
--
ALTER TABLE `page_revisions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=755;
--
-- AUTO_INCREMENT for table `pictures`
--
ALTER TABLE `pictures`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=383;
--
-- AUTO_INCREMENT for table `testimonials`
--
ALTER TABLE `testimonials`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;
--
-- AUTO_INCREMENT for table `testimonials_home_rev`
--
ALTER TABLE `testimonials_home_rev`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;
--
-- AUTO_INCREMENT for table `upload_file`
--
ALTER TABLE `upload_file`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=347;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
