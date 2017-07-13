<?php
 /**
 * Sample code for the GetRates Canada Post service.
 * 
 * The GetRates service returns a list of shipping services, prices and transit times 
 * for a given item to be shipped. 
 *
 * This sample is configured to access the Developer Program sandbox environment. 
 * Use your development key username and password for the web service credentials.
 * 
 **/

// Your username and password are imported from the following file
// CPCWS_SOAP_Rating_PHP_Samples\SOAP\rating\user.ini
//$userProperties = parse_ini_file(realpath(dirname($_SERVER['SCRIPT_FILENAME'])) . '/../user.ini');
$userProperties['username'] = decrypt($ship_prefs['cp_username']);
$userProperties['password'] = decrypt($ship_prefs['cp_password']);
$userProperties['customerNumber'] = decrypt($ship_prefs['cp_customerNumber']);

$wsdl = $_config['admin_path'] . "modules/shopping_cart/frontend/includes/cp_rating.wsdl";

switch($_config['environment']) {
	case "live":
		$hostName = 'soa-gw.canadapost.ca';
		break;
	case "dev":
		$hostName = 'ct.soa-gw.canadapost.ca';
		break;
}

// SOAP URI
$location = 'https://' . $hostName . '/rs/soap/rating/v3';

// SSL Options
$opts = array('ssl' =>
	array(
		'verify_peer'=> false,
		'cafile' => $_config['admin_path'] . "modules/shopping_cart/frontend/includes/cert/cacert.pem",
		'CN_match' => $hostName
	)
);

$ctx = stream_context_create($opts);	
$client = new SoapClient($wsdl,array('location' => $location, 'features' => SOAP_SINGLE_ELEMENT_ARRAYS, 'stream_context' => $ctx));

// Set WS Security UsernameToken
$WSSENS = 'http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-secext-1.0.xsd';
$usernameToken = new stdClass(); 
$usernameToken->Username = new SoapVar($userProperties['username'], XSD_STRING, null, null, null, $WSSENS);
$usernameToken->Password = new SoapVar($userProperties['password'], XSD_STRING, null, null, null, $WSSENS);
$content = new stdClass(); 
$content->UsernameToken = new SoapVar($usernameToken, SOAP_ENC_OBJECT, null, null, null, $WSSENS);
$header = new SOAPHeader($WSSENS, 'Security', $content);
$client->__setSoapHeaders($header); 

try {
	// Execute Request
	$mailedBy = $userProperties['customerNumber'];
	$originPostalCode = str_replace(' ','',$ship_prefs['postal_code']); //no spaces
	$postalCode = str_replace(' ','',$_POST['postal_code']);
	$weight = $total_weight;
	switch($_POST['country']) {
		case "CA":
			$dest = "domestic";
			$code = "postal-code";
			break;
		case "US":
			$dest = "united-states";
			$code = "zip-code";
			break;
	}

	$result = $client->__soapCall('GetRates', array(
	    'get-rates-request' => array(
			'locale'			=> 'EN',
			'mailing-scenario' 			=> array(
				'customer-number'			=> $mailedBy,
				'parcel-characteristics'	=> array(
					'weight'					=> $weight
				),
				'origin-postal-code'		=> $originPostalCode,
				'destination' 			=> array(
					$dest 					=> array(
						$code					=> $postalCode
					)
				)
			)
		)
	), NULL, NULL);
	
	// Parse Response
	echo "<tr><th colspan=3>Canada Post</th></tr>";
	if ( isset($result->{'price-quotes'}) ) {
		foreach ( $result->{'price-quotes'}->{'price-quote'} as $priceQuote ) {
			echo '<tr>';
			$serviceType = "<td><input type='radio' class='ship_radio' name='ship_method' data-price='" . number_format($priceQuote->{'price-details'}->{'due'},2,".",",") ."' value='".$priceQuote->{'service-name'}."' />".$priceQuote->{'service-name'} . "</td>";
			$amount = '<td>$' . number_format($priceQuote->{'price-details'}->{'due'},2,".",",") . '</td>';
			
			if(isset($priceQuote->{'service-standard'}->{'expected-delivery-date'}) && $priceQuote->{'service-standard'}->{'expected-delivery-date'} != "") {
				$deldate = (strtotime($priceQuote->{'service-standard'}->{'expected-delivery-date'}) - time())/86400;
				$deldate = number_format($deldate,0,".",",");
				$deldate = $deldate <= 1 ? "1 business day" : $deldate." business days";
				$deliveryDate= '<td>' . $deldate . '</td>';
			} elseif (isset($priceQuote->{'service-standard'}->{'expected-transit-time'}) && $priceQuote->{'service-standard'}->{'expected-transit-time'} != "" ) {
				$deliveryDate= '<td>' . $priceQuote->{'service-standard'}->{'expected-transit-time'} . '</td>';
			} else {
				$deliveryDate= '<td>ETA Not Available</td>';
			}
			echo $serviceType . $amount. $deliveryDate;
			echo '</tr>';
			//echo 'Service Name: ' . $priceQuote->{'service-name'} . "\n";
			//echo 'Price: ' . $priceQuote->{'price-details'}->{'due'} . "\n\n";	
		}
	} else {
		foreach ( $result->{'messages'}->{'message'} as $message ) {
			//add in flat rate from $ship_prefs
			echo '<tr>';
			$serviceType = "<td><input type='radio' class='ship_radio' name='ship_method' data-price='" . number_format($ship_prefs['flat_rate'],2,".",",") ."' value='Flat Rate Shipping' />Flat Rate Shipping</td>";
			$amount = '<td>$' . number_format($ship_prefs['flat_rate'],2,".",",") . '</td>';
			$deliveryDate= "<td>{$ship_prefs['flat_rate_days']} business days</td>";
			echo $serviceType . $amount. $deliveryDate;
			echo '</tr>';
			/***/
			
			//echo 'Error Code: ' . $message->code . "\n";
			//echo 'Error Msg: ' . $message->description . "\n\n";
		}
	}
	
} catch (SoapFault $exception) {
	//add in flat rate from $ship_prefs
	echo '<tr>';
	$serviceType = "<td><input type='radio' class='ship_radio' name='ship_method' data-price='" . number_format($ship_prefs['flat_rate'],2,".",",") ."' value='Flat Rate Shipping' />Flat Rate Shipping</td>";
	$amount = '<td>$' . number_format($ship_prefs['flat_rate'],2,".",",") . '</td>';
	$deliveryDate= "<td>{$ship_prefs['flat_rate_days']} business days</td>";
	echo $serviceType . $amount. $deliveryDate;
	echo '</tr>';
	/***/
	//echo 'Fault Code: ' . trim($exception->faultcode) . "\n"; 
	//echo 'Fault Reason: ' . trim($exception->getMessage()) . "\n"; 
}
/** SAMPLE RETURN **/
/*
stdClass Object ( [price-quotes] => stdClass Object ( [price-quote] => Array ( [0] => stdClass Object ( [service-code] => USA.EP [service-name] => Expedited Parcel USA [price-details] => stdClass Object ( [base] => 24.53 [taxes] => stdClass Object ( [gst] => stdClass Object ( [_] => 0.00 ) [pst] => stdClass Object ( [_] => 0.00 ) [hst] => stdClass Object ( [_] => 0.00 ) ) [due] => 25.51 [options] => stdClass Object ( [option] => Array ( [0] => stdClass Object ( [option-code] => DC [option-name] => Delivery confirmation [option-price] => 0 ) ) ) [adjustments] => stdClass Object ( [adjustment] => Array ( [0] => stdClass Object ( [adjustment-code] => AUTDISC [adjustment-name] => Automation discount [adjustment-cost] => -0.74 [qualifier] => stdClass Object ( [percent] => 3.000 ) ) [1] => stdClass Object ( [adjustment-code] => FUELSC [adjustment-name] => Fuel surcharge [adjustment-cost] => 1.72 [qualifier] => stdClass Object ( [percent] => 7.25 ) ) ) ) ) [weight-details] => stdClass Object ( ) [service-standard] => stdClass Object ( [am-delivery] => [guaranteed-delivery] => [expected-transit-time] => 4 [expected-delivery-date] => 2014-06-30 ) )

 [1] => stdClass Object ( [service-code] => USA.PW.PARCEL [service-name] => Priority Worldwide parcel USA [price-details] => stdClass Object ( [base] => 92.77 [taxes] => stdClass Object ( [gst] => stdClass Object ( [_] => 0.00 ) [pst] => stdClass Object ( [_] => 0.00 ) [hst] => stdClass Object ( [_] => 0.00 ) ) [due] => 102.14 [options] => stdClass Object ( [option] => Array ( [0] => stdClass Object ( [option-code] => DC [option-name] => Delivery confirmation [option-price] => 0 ) [1] => stdClass Object ( [option-code] => SO [option-name] => Signature option [option-price] => 0 ) ) ) [adjustments] => stdClass Object ( [adjustment] => Array ( [0] => stdClass Object ( [adjustment-code] => AUTDISC [adjustment-name] => Automation discount [adjustment-cost] => -2.78 [qualifier] => stdClass Object ( [percent] => 3.000 ) ) [1] => stdClass Object ( [adjustment-code] => FUELSC [adjustment-name] => Fuel surcharge [adjustment-cost] => 12.15 [qualifier] => stdClass Object ( [percent] => 13.5 ) ) ) ) ) [weight-details] => stdClass Object ( ) [service-standard] => stdClass Object ( [am-delivery] => [guaranteed-delivery] => ) ) [2] => stdClass Object ( [service-code] => USA.XP [service-name] => Xpresspost USA [price-details] => stdClass Object ( [base] => 36.07 [taxes] => stdClass Object ( [gst] => stdClass Object ( [_] => 0.00 ) [pst] => stdClass Object ( [_] => 0.00 ) [hst] => stdClass Object ( [_] => 0.00 ) ) [due] => 39.28 [options] => stdClass Object ( [option] => Array ( [0] => stdClass Object ( [option-code] => DC [option-name] => Delivery confirmation [option-price] => 0 ) [1] => stdClass Object ( [option-code] => SO [option-name] => Signature option [option-price] => 0 ) ) ) [adjustments] => stdClass Object ( [adjustment] => Array ( [0] => stdClass Object ( [adjustment-code] => AUTDISC [adjustment-name] => Automation discount [adjustment-cost] => -1.08 [qualifier] => stdClass Object ( [percent] => 3.000 ) ) [1] => stdClass Object ( [adjustment-code] => FUELSC [adjustment-name] => Fuel surcharge [adjustment-cost] => 4.29 [qualifier] => stdClass Object ( [percent] => 12.25 ) ) ) ) ) [weight-details] => stdClass Object ( ) [service-standard] => stdClass Object ( [am-delivery] => [guaranteed-delivery] => 1 [expected-transit-time] => 4 [expected-delivery-date] => 2014-06-30 ) ) ) ) ) 
*/

?>

