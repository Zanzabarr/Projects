<?php
// this module doesn't automatically add any data so need to set a check for install
$result = logged_query("SELECT count(*) FROM `markers`",0,array());
$needs_install = $result === false;

$query ="
CREATE TABLE IF NOT EXISTS `markers` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(60) NOT NULL,
  `address` varchar(80) NOT NULL,
  `lat` float(10,6) NOT NULL,
  `lng` float(10,6) NOT NULL,
  `opt1` varchar(100) NOT NULL,
  `opt2` varchar(100) NOT NULL,
  `opt3` varchar(100) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1;";
$result = logged_query($query,0,array());
if($result === false) die('Error Creating Markers Table');

// got to the end: so updates occured where needed
if($needs_install) $moduleAdded['Store Locator'] = true;
?>