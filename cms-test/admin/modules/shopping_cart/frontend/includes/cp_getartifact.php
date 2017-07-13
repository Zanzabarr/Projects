<?php
/**
 * The cp_getartifact is used to retrieve a pdf of the shipping label 
 * created by a prior cp_createNCshipment call.
 */
if(isset($_POST['artifact_id'])) {
	// Your username and password are provided by Canada Post
	$userProperties['username'] = $_POST['username'];
	$userProperties['password'] = $_POST['password'];
	$userProperties['customerNumber'] = $_POST['customerNumber'];

	$wsdl = $_config['admin_path'] . "modules/shopping_cart/frontend/includes/cp_artifact.wsdl";
	
	switch($_config['environment']) {
		case "live":
			$hostName = 'soa-gw.canadapost.ca';
			break;
		case "dev":
			$hostName = 'ct.soa-gw.canadapost.ca';
			break;
	}

	// SOAP URI
	$location = 'https://' . $hostName . '/rs/soap/artifact';

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
		// Execute Request
		$result = $client->__soapCall('GetArtifact', array(
			'get-artifact-request' => array(
				'locale'			=> 'EN',
				'mailed-by'			=> $mailedBy,
				'artifact-id'		=> $_POST['artifact_id'],
				'page-index'		=> '0'
			)
		), NULL, NULL);
		
		// Parse Response
		if ( isset($result->{'artifact-data'}) ) {
			//echo 'base64 Encoded: ' . $result->{'artifact-data'}->{'image'} . "\n";
			//echo 'Mime type: ' . $result->{'artifact-data'}->{'mime-type'} . "\n";
			// Decoding base64 certificate to a file
			$fileLoc = $_config['upload_path']."labels/".$_POST['artifact_id'].".pdf";
			$fileUrl = $_config['upload_url']."labels/".$_POST['artifact_id'].".pdf";
			//echo 'Decoding to' . $fileLoc . "\n";
			$fp = fopen($fileLoc, 'w');
			stream_filter_append($fp, 'convert.base64-decode');
			fwrite($fp, $result->{'artifact-data'}->{'image'});
			fclose($fp);
			echo "<div style='text-align:center;'><a id='labellink' href='{$fileUrl}' target='_blank'><h1>Open Label</h1></a></div>";

		} else {
			foreach ( $result->{'messages'}->{'message'} as $message ) {
				echo 'Error Code: ' . $message->code . "\n";
				echo 'Error Msg: ' . $message->description . "\n\n";
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
	$('#labellink').click( function() {
		$.fancybox.close();
	});
});
</script>