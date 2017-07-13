<?php 
/**
 * The cp_createNCshipment is used to generate and pay for a 
 * shipping label, including validation and payment authorization. 
 * 
 * 
/* if $ship_method is from Canada Post, it can be any of the following:
Domestic
DOM.RP 	Regular Parcel
DOM.EP 	Expedited Parcel
DOM.XP 	Xpresspost
DOM.PC 	Priority
USA
USA.PW.ENV 	Priority Worldwide Envelope USA
USA.PW.PAK 	Priority Worldwide pak USA
USA.PW.PARCEL 	Priority Worldwide Parcel USA
USA.XP 	Xpresspost USA
USA.EP 	Expedited Parcel USA
USA.SP.AIR 	Small Packet USA Air
USA.TP 	Tracked Packet – USA
*/

if(isset($_POST['orderid'])) {

	//get shipping preferences
	$ship_prefs = logged_query_assoc_array("SELECT * FROM `ecom_ship_prefs` WHERE `id` = '1'",null,0,array());
	$ship_prefs = $ship_prefs[0];
	
	//hack the weight
	$_POST['weight'] = $ship_prefs['weight_type'] == "LB" ? number_format($_POST['weight']/2.2, 2, '.', ',') : $_POST['weight'];
	//hack the dimensions
	if($ship_prefs['dimensionUnit'] == "IN") {
		$_POST['length'] == number_format($_POST['length']*2.54, 2, '.', ',');
		$_POST['width'] == number_format($_POST['width']*2.54, 2, '.', ',');
		$_POST['height'] == number_format($_POST['height']*2.54, 2, '.', ',');
	}
	
	// Your username and password are provided by Canada Post
	$userProperties['username'] = $_POST['username'];
	$userProperties['password'] = $_POST['password'];
	$userProperties['customerNumber'] = $_POST['customerNumber'];

	$wsdl = $_config['admin_path'] . "modules/shopping_cart/frontend/includes/cp_ncshipment.wsdl";
	
	switch($_config['environment']) {
		case "live":
			$hostName = 'soa-gw.canadapost.ca';
			break;
		case "dev":
			$hostName = 'ct.soa-gw.canadapost.ca';
			break;
	}

	// SOAP URI
	$location = 'https://' . $hostName . '/rs/soap/ncshipment/v3';

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
		$mailedBy = $userProperties['customerNumber'];
		$requestedShippingPoint = str_replace(' ','',$_POST['postal_code']); //no spaces
		$_POST['postal_code'] = str_replace(' ','',$_POST['postal_code']);
	
		//create customs array
		foreach($_POST['items'] as $item) {
			$skulist[] = $item;
		}

		$customs = array(
							'currency'		=> $_POST['currency'],
							'reason-for-export'	=> 'SOG',
							'conversion-from-cad' => $_POST['conversion-from-cad'],
							'sku-list'	=>$skulist
						);

		// Execute Request
		$result = $client->__soapCall('CreateNCShipment', array(
			'create-non-contract-shipment-request' => array(
				'locale'			=> 'EN',
				'mailed-by'			=> $mailedBy,
				'non-contract-shipment' 		=> array(
					'requested-shipping-point'		=> $requestedShippingPoint,
					'delivery-spec'		=> array(
						'service-code'		=> $_POST['servicecode'],
						'sender'			=> array(
							'company'			=> $_config['company_name'],
							'contact-phone'		=> $_POST['phone'],
							'address-details'	=> array(
								'address-line-1'	=> $_POST['addressLine1'],
								'city'				=> $_POST['city'],
								'prov-state'		=> $_POST['province'],
								'postal-zip-code'	=> $_POST['postal_code']
							)
						),
						'destination'			=> array(
							'name'				=> $_POST['cust_name'],
							'address-details'	=> array(
								'address-line-1'	=> $_POST['cust_address'],
								'city'				=> $_POST['cust_city'],
								'prov-state'		=> $_POST['cust_province'],
								'country-code'		=> $_POST['cust_country'],
								'postal-zip-code'	=> $_POST['cust_postal_code']
							),
							'client-voice-number'	=> $_POST['cust_phone']
						),
						'options' 			=> array(
							'option' 				=> array(
								'option-code'			=> 'RASE'
							)
						),
						'parcel-characteristics'	=> array(
							'weight'		=> $_POST['weight'],
							'dimensions'	=> array(
								'length'		=> $_POST['length'],
								'width'			=> $_POST['width'],
								'height'		=> $_POST['height']
							)
						),
						'preferences' 	=> array(
							'show-packing-instructions'	=> true,
						),
						'customs'		=> $customs
					)
				)
			)
		), NULL, NULL);
		
		// Parse Response
		if ( isset($result->{'non-contract-shipment-info'}) ) {
			$shipment_id = $result->{'non-contract-shipment-info'}->{'shipment-id'};
			$tracking_pin = $result->{'non-contract-shipment-info'}->{'tracking-pin'};
			foreach ( $result->{'non-contract-shipment-info'}->{'artifacts'}->{'artifact'} as $artifact ) { 
				$artifact_id = $artifact->{'artifact-id'};
			}
			$saved = logged_query("UPDATE ecom_orders SET shipment_id = {$shipment_id}, tracking_pin = {$tracking_pin}, artifact_id = {$artifact_id} WHERE id = {$_POST['orderid']}",0,array());
			if($saved!=="false") {
				echo "<h2>Shipment for Order #{$_POST['orderid']} Created Successfully</h2><p><strong>Shipment ID:</strong>&nbsp;$shipment_id</p>";
				$form = "";
				$form .= "<form id='labelForm' action='{$_config['admin_url']}modules/shopping_cart/ajax/getartifact.php' method='post'>";
				$form .= "<input type='hidden' name='username' value='{$userProperties['username']}' />";
				$form .= "<input type='hidden' name='password' value='{$userProperties['password']}' />";
				$form .= "<input type='hidden' name='customerNumber' value='{$userProperties['customerNumber']}' />";
				$form .= "<input type='hidden' name='artifact_id' value='{$artifact_id}' />";
				$form .= "<button id='getartifact' type='button'>Create Shipping Label</button>";
				$form .= "</form>";
				echo $form;
			} else {
				if ($logfile = fopen("admin/logs/log.txt", "a")) {
					fwrite($logfile, sprintf("\r%s:- %s",date("D M j G:i:s T Y"),"Could not write to ecom_orders table\r\n Order ID: {$_POST['orderid']}\r\nShipment ID: $shipment_id, Tracking Pin: $tracking_pin, Artifct ID: $artifact_id"));
				}
				echo "There has been an error writing to the database. See the log for more details.";
			}
		} else {
			//handle errors in admin frontend - TODO: replace with remedial process to try another shipping option if first attempt fails
			echo "There is an error with this shipment:<br /><br />";
			foreach ( $result->{'messages'}->{'message'} as $message ) {
				echo "**" . $message->description . "**";
			}
			echo "<br /><br />Depending on the error, you may need to manually ship this package due to Canada Post restrictions.";
			
			//log error if possible
			if ($logfile = fopen($_config['rootpath']."admin/logs/log.txt", "a")) {
				foreach ( $result->{'messages'}->{'message'} as $message ) {
					fwrite($logfile, sprintf('Error Code: ' . $message->code . "\n".'Error Msg: ' . $message->description . "\n\n"));
				}
			}
		}
	} catch (SoapFault $exception) {
		echo 'Fault Code: ' . trim($exception->faultcode) . "\n";
		echo 'Fault Reason: ' . trim($exception->getMessage()) . "\n"; 
	}
}
?>
<script>
$(document).ready( function() {
	$('#getartifact').click( function() {
		$(this).replaceWith("<img src='../../images/loader_light_blue.gif' />");
		$.ajax({
			url: $('#labelForm').attr("action"),
			type: 'POST',
			dataType: 'html',
			data: $('#labelForm').serialize(),
			success: function(data, textStatus, xhr) {
				$.fancybox({
					'content': data,
					'autoDimensions': false,
					'width': '70%',
					'height': '100%',
					'overlayColor': '#000',
					'overlayOpacity': 0.6,
					'transitionIn': 'elastic',
					'transitionOut': 'elastic',
					'centerOnScroll': true,
					'titlePosition': 'outside',
					'easingIn': 'easeOutBack',
					'easingOut': 'easeInBack'
				});
			},
			error: function(xhr, textStatus, errorThrown) {
			alert("An error occurred.");
			}
		});
		return false;
	});
});
</script>