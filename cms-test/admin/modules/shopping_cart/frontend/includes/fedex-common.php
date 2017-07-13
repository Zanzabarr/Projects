<?php
// Copyright 2009, FedEx Corporation. All rights reserved.

define('TRANSACTIONS_LOG_FILE', 'admin/logs/fedex_logs/fedex_txns.log');  // Transactions log file

/**
 *  Print SOAP request and response
 */
define('Newline',"<br />");

function printSuccess($client, $response) {
    echo '<h2>Transaction Successful</h2>';  
    echo "\n";
    printRequestResponse($client);
}

function printRequestResponse($client){
	echo '<h2>Request</h2>' . "\n";
	echo '<pre>' . htmlspecialchars($client->__getLastRequest()). '</pre>';  
	echo "\n";
   
	echo '<h2>Response</h2>'. "\n";
	echo '<pre>' . htmlspecialchars($client->__getLastResponse()). '</pre>';
	echo "\n";
}

/**
 *  Print SOAP Fault
 */  
function printFault($exception, $client) {
    echo '<h2>Fault</h2>' . "<br>\n";                        
    echo "<b>Code:</b>{$exception->faultcode}<br>\n";
    echo "<b>String:</b>{$exception->faultstring}<br>\n";
    writeToLog($client);
    
    echo '<h2>Request</h2>' . "\n";
	echo '<pre>' . htmlspecialchars($client->__getLastRequest()). '</pre>';  
	echo "\n";
}

/**
 * SOAP request/response logging to a file
 */                                  
function writeToLog($client){
//create history file when log gets large
$bytes = filesize(TRANSACTIONS_LOG_FILE);
if($bytes > 2000000000) {	//2000000000 = ~1.86Gb
	$newname = "admin/logs/fedex_logs/fedex_txn_history_" . time() . ".log";
	rename(TRANSACTIONS_LOG_FILE, $newname);
}
//write to log
if (!$logfile = fopen(TRANSACTIONS_LOG_FILE, "a"))
{
   error_func("Cannot open " . TRANSACTIONS_LOG_FILE . " file.\n", 0);
   exit(1);
}
fwrite($logfile, sprintf("\r%s:- %s",date("D M j G:i:s T Y"), $client->__getLastRequest(). "\n\n" . $client->__getLastResponse()));
}

/**
 * variables required for various fedex tasks
*/
function getProperty($var){
	$fedex_array = array('key','password','shipaccount','billaccount','dutyaccount','freightaccount','trackaccount','meter');
	$shipaddy = "`street_address`,`city`,`province`,`postal_code`,`country`";
	if(in_array($var,$fedex_array)) {
		$property = logged_query_assoc_array("SELECT `fedex_{$var}` from `ecom_ship_prefs` where `id`=1",null,0,array());
	} else {
		$property = logged_query_assoc_array("SELECT {$shipaddy} from `ecom_ship_prefs` where `id`=1",null,0,array());
	}
	$property = $property[0];
	if($var == 'key' && $property["fedex_{$var}"]!="") return decrypt($property['fedex_key']); 
	if($var == 'password' && $property["fedex_{$var}"]!="") return decrypt($property['fedex_password']); 
		
	if($var == 'shipaccount' && $property["fedex_{$var}"]!="") return decrypt($property['fedex_shipaccount']); 
	if($var == 'billaccount' && $property["fedex_{$var}"]!="") return decrypt($property['fedex_billaccount']); 
	if($var == 'dutyaccount' && $property["fedex_{$var}"]!="") return decrypt($property['fedex_dutyaccount']); 
	if($var == 'freightaccount' && $property["fedex_{$var}"]!="") return decrypt($property['fedex_freightaccount']); 
	if($var == 'trackaccount' && $property["fedex_{$var}"]!="") return decrypt($property['fedex_trackaccount']); 

	if($var == 'meter' && $property["fedex_{$var}"]!="") return decrypt($property['fedex_meter']);
	
	if($var == 'shiptimestamp') return mktime(10, 0, 0, date("m"), date("d")+1, date("Y"));

	if($var == 'spodshipdate') return '2013-05-21';
	if($var == 'serviceshipdate') return '2013-04-26';

	if($var == 'readydate') return '2010-05-31T08:44:07';
	if($var == 'closedate') return date("Y-m-d");

	if($var == 'pickupdate') return date("Y-m-d", mktime(8, 0, 0, date("m")  , date("d")+1, date("Y")));
	if($var == 'pickuptimestamp') return mktime(8, 0, 0, date("m")  , date("d")+1, date("Y"));
	if($var == 'pickuplocationid') return 'XXX';
	if($var == 'pickupconfirmationnumber') return 'XXX';

	if($var == 'dispatchdate') return date("Y-m-d", mktime(8, 0, 0, date("m")  , date("d")+1, date("Y")));
	if($var == 'dispatchlocationid') return 'XXX';
	if($var == 'dispatchconfirmationnumber') return 'XXX';		
	
	if($var == 'tag_readytimestamp') return mktime(10, 0, 0, date("m"), date("d")+1, date("Y"));
	if($var == 'tag_latesttimestamp') return mktime(20, 0, 0, date("m"), date("d")+1, date("Y"));	

	if($var == 'expirationdate') return '2013-05-24';
	if($var == 'begindate') return '2013-04-22';
	if($var == 'enddate') return '2013-04-25';

	if($var == 'trackingnumber') return 'XXX';

	if($var == 'hubid') return 'XXX';
	
	if($var == 'jobid') return 'XXX';

	if($var == 'searchlocationphonenumber') return '5555555555';
			
	if($var == 'shipper') return array(
		'Contact' => array(
			'PersonName' => 'Sender Name',
			'CompanyName' => 'Sender Company Name',
			'PhoneNumber' => '1234567890'
		),
		'Address' => array(
			'StreetLines' => array('Address Line 1'),
			'City' => 'Collierville',
			'StateOrProvinceCode' => 'TN',
			'PostalCode' => '38017',
			'CountryCode' => 'US',
			'Residential' => 1
		)
	);
	if($var == 'recipient') return array(
		'Contact' => array(
			'PersonName' => 'Recipient Name',
			'CompanyName' => 'Recipient Company Name',
			'PhoneNumber' => '1234567890'
		),
		'Address' => array(
			'StreetLines' => array('Address Line 1'),
			'City' => 'Herndon',
			'StateOrProvinceCode' => 'VA',
			'PostalCode' => '20171',
			'CountryCode' => 'US',
			'Residential' => 1
		)
	);	


/* $property contains fields "`street_address`,`city`,`province`,`postal_code`,`country`"; */

	if($var == 'CountryCode') return $property['country'];

	if($var == 'address1') return array(
		'StreetLines' => array("{$property['street_address']}"),
		'City' => "{$property['city']}",
		'StateOrProvinceCode' => "{$property['province']}",
		'PostalCode' => "{$property['postal_code']}",
		'CountryCode' => "{$property['country']}"
    );
	if($var == 'address2') {
		if (isset($_SERVER['HTTP_X_FORWARD_FOR'])) {
			$ip = $_SERVER['HTTP_X_FORWARD_FOR'];
		} else {
			$ip = $_SERVER['REMOTE_ADDR'];
		}
		$check = logged_query_assoc_array("SELECT `id` FROM `ecom_orders` WHERE `md5` = :md5 AND `ip` = :ip AND confirm = 0",null,0,array(":md5" => $_SESSION['md5id'],":ip" => $ip));
		$check = isset($check[0]) ? $check[0] : array();
		if(isset($check['id'])) {
			$recipient = logged_query_assoc_array("SELECT * FROM `ecom_orders` WHERE `id`= :id",null,0,array(":id" => $check['id']));
			$recipient = $recipient[0];
			foreach($recipient as $k=>$v) {
				$$k = decrypt($v);
			}
		} else {
			die('There has been an error and Fedex Shipping cannot be contacted. Please refresh your browser and try again.');
		}
		return array(
			'StreetLines' => array($address1,$address2),
			'City' => $city,
			'StateOrProvinceCode' => $province,
			'PostalCode' => $postal_code,
			'CountryCode' => $country
		);
	}

	if($var == 'searchlocationsaddress') return array(
		'StreetLines'=> array('240 Central Park S'),
		'City'=>'Austin',
		'StateOrProvinceCode'=>'TX',
		'PostalCode'=>'78701',
		'CountryCode'=>'US'
	);
									  
	if($var == 'shippingchargespayment') return array(
		'PaymentType' => 'SENDER',
		'Payor' => array(
			'ResponsibleParty' => array(
				'AccountNumber' => getProperty('billaccount'),
				'Contact' => null,
				'Address' => array('CountryCode' => 'US')
			)
		)
	);	
	if($var == 'freightbilling') return array(
		'Contact'=>array(
			'ContactId' => 'freight1',
			'PersonName' => 'Big Shipper',
			'Title' => 'Manager',
			'CompanyName' => 'Freight Shipper Co',
			'PhoneNumber' => '1234567890'
		),
		'Address'=>array(
			'StreetLines'=>array(
				'1202 Chalet Ln', 
				'Do Not Delete - Test Account'
			),
			'City' =>'Harrison',
			'StateOrProvinceCode' => 'AR',
			'PostalCode' => '72601-6353',
			'CountryCode' => 'US'
			)
	);
}

function setEndpoint($var){
	if($var == 'changeEndpoint') return false;
}

function printNotifications($notes){
	foreach($notes as $noteKey => $note){
		if(is_string($note)){    
            echo $noteKey . ': ' . $note . Newline;
        }
        else{
        	printNotifications($note);
        }
	}
	echo Newline;
}

function printError($client, $response){
    echo '<h2>Error returned in processing transaction</h2>';
	echo "\n";
	printNotifications($response -> Notifications);
    printRequestResponse($client, $response);
}

function trackDetails($details, $spacer){
	foreach($details as $key => $value){
		if(is_array($value) || is_object($value)){
        	$newSpacer = $spacer. '&nbsp;&nbsp;&nbsp;&nbsp;';
    		echo '<tr><td>'. $spacer . $key.'</td><td>&nbsp;</td></tr>';
    		trackDetails($value, $newSpacer);
    	}elseif(empty($value)){
    		echo '<tr><td>'.$spacer. $key .'</td><td>'.$value.'</td></tr>';
    	}else{
    		echo '<tr><td>'.$spacer. $key .'</td><td>'.$value.'</td></tr>';
    	}
    }
}
?>