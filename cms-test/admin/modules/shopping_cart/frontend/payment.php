<?php
##	TestResPurchaseCC.php
## This program takes 3 arguments from the command line:
## 1. Store id
## 2. api token
## 3. order id
##
## Example php -q TestResPurchaseCC.php store3 yesguy unique_order_id 1.00
##
error_reporting(0);

require($_config['admin_path'] . 'modules/shopping_cart/frontend/includes/mpgClasses.php');

/************************ Request Variables **********************************/

$monprefs = logged_query("SELECT * FROM ecom_moneris_prefs WHERE id = 1",0,array());
$monprefs = $monprefs[0];

if(isset($_POST['paymentform'])) {
	$billbind = array();
	foreach($_POST as $k=>$v) {
		if($k != "total" && $k != "order_id" && $v != "") {
			$billbind[":$k"] = encrypt(trim($v));
		} else {
			$billbind[":$k"] = trim($v);
		}
	}
	unset($billbind[':data_key']);
	unset($billbind[':expmonth']);
	unset($billbind[':expyear']);
	unset($billbind[':cvd_value']);
	unset($billbind[':paymentform']);
	unset($billbind[':email']);
	unset($billbind[':phone']);
	
	$savedbilling = logged_query("UPDATE `ecom_orders` SET `total` = :total, `subtotal` = :total, `first_name` = :first_name, `last_name` = :last_name, `address1` = :address1, `address2` = :address2, `city` = :city, `province` = :province, `postal_code` = :postal_code, `country` = :country WHERE `id` = :order_id",0,$billbind);
}

$store_id=decrypt($monprefs['store_id']);
$api_token=decrypt($monprefs['api_token']);

/************************ Transaction Variables ******************************/

//$data_key='duZ2UOuosdySQPbfvNAvOUcDE';
$data_key=$_POST['data_key'];
$orderid=$_POST['order_id'].'--'.time();
$amount=$_POST['total'];
$custid=$_POST['order_id'].'--'.time();
$crypt_type='7';
$expdate = date('y',strtotime('1/1/'.$_POST['expyear'])).$_POST['expmonth'];
//$expdate is yymm

/************************ Transaction Array **********************************/

$txnArray=array('type'=>'res_purchase_cc',
			'data_key'=>$data_key,
		        'order_id'=>$orderid,
		        'cust_id'=>$custid,
		        'amount'=>$amount,
		        'crypt_type'=>$crypt_type,
		        'expdate'=>$expdate
		        );

/************************ Transaction Object *******************************/

$mpgTxn = new mpgTransaction($txnArray);

/************************ Request Object **********************************/

$mpgRequest = new mpgRequest($mpgTxn);

/************************ mpgHttpsPost Object ******************************/

$mpgHttpPost = new mpgHttpsPost($store_id,$api_token,$mpgRequest);

/************************ Response Object **********************************/

$mpgResponse = $mpgHttpPost->getMpgResponse();
echo "<div style='clear:both;height:2em;'></div>";
// get responses

$responseData = $mpgResponse->responseData;

//check response codes
if($responseData['Complete']=="true" && $responseData['TimedOut']=="false") {
	if($responseData['ResponseCode'] <= 49) {	//RB, Royal Bank, Response code: <50 = Approved, >=50 = Declined
		$confirm = 1;
	} else {
		$confirm = 0;
	}
	$txn_id = $mpgResponse->getTxnNumber();
	$recid = explode("--",$mpgResponse->getReceiptId());
	$txnbind[":order_id"] = $recid[0];
	$txnbind[":txn_id"] = $txn_id;
	$updTxn = logged_query("UPDATE `ecom_orders` SET `txn_id` = :txn_id, `confirm` = {$confirm} WHERE `id` = :order_id",0,$txnbind);
	
	$respFields = "`order_id`, `approved`";
	$respVals = "{$recid[0]}, {$confirm}";
	foreach($responseData as $k=>$v) {
		$respFields .= ", `$k`";
		$respVals .= ", '{$v}'";
	}
	
	$insTxn = logged_query("INSERT INTO `ecom_moneris_txn` ({$respFields}) VALUES ({$respVals})",0,array());
	if($updTxn !== "false" && $insTxn !== "false") {	//database save successful
		switch($confirm) {
			case 0:
				unset($_SESSION['cart']);
				unset($_SESSION['cart_qty']);
				unset($_SESSION['cart_price']);
				$receipt = "This transaction was declined by the bank.<br /><br /><a href='shopping'>Return to {$_config['company_name']}</a> or wait 10 seconds<script>setTimeout('location.href = \'shopping\';',10000);</script>";
				logged_query("DELETE FROM `ecom_orders` WHERE `id` = {$recid[0]}",0,array());
				break;
			case 1:
				$ship_prefs = logged_query_assoc_array("SELECT * FROM `ecom_ship_prefs` WHERE `id` = '1'",null,0,array());
				$ship_prefs = $ship_prefs[0];

				$order = logged_query_assoc_array("SELECT * FROM ecom_orders WHERE id = :orderid",null,0,array(":orderid" => $recid[0]));
				$order = $order[0];

				$shipaddy = logged_query("SELECT * FROM ecom_orders_shipping WHERE order_id = :orderid",0,array(":orderid" => $recid[0]));
				$shipaddy = $shipaddy[0];

				$enc_keys = array('first_name','last_name','address1','address2','city','province','postal_code','country','email','phone','notes');

				foreach($order as $k => $v) {
					if(in_array($k,$enc_keys) && $v!="") {
						$$k = decrypt($v);
					} else {
						$$k = $v;
					}
				}
				$order['subtotal'] = number_format($order['total']-$order['ship_price']-$order['tax'], 2, '.', ',');

				$shipto = array();
				foreach($shipaddy as $k => $v) {
					if(in_array($k,$enc_keys) && $v!="") {
						$shipto[$k] = decrypt($v);
					} else {
						$shipto[$k] = $v;
					}
				}
				if($shipto['notes']=="") $shipto['notes'] = "&nbsp;";
				$info = unserialize($info);
				
				logged_query("UPDATE `ecom_orders` SET `subtotal` = {$order['subtotal']} WHERE `id` = {$recid[0]}",0,array());

				$receipt = "<table id='receipt_table' style='width:100%;'><tbody class='borderless'>";
				$receipt .= "<tr><th colspan=5 style='text-align:center; font-weight:600; background:#ddd; vertical-align:middle; text-indent:2px;' class='bgswap'> <h2 style='margin-top:.6em;'>{$_config['company_name']} Receipt</h2></th></tr>";
				$receipt .= "<tr><td colspan=5 style='text-align:center;border:none;'><p>Your order has been Approved.<br />You may print this receipt for your records.</p><p>A copy of this receipt has been sent to {$_POST['email']}.</p></td></tr>";
				$receipt .= "<tr><td colspan=5 class='darkbg bgswap' style='border:none;width:100%; height:.4em; background:#ddd;'/></tr>";
				$receipt .= "<tr><td colspan=5 class='merchant-address' style='border:none;text-align:center; font-weight:600;'>";
				$receipt .= "<p><h3>{$_config['company_name']}</h3></p>";
				$receipt .= "<p>{$ship_prefs['street_address']}</p>";
				$receipt .= "<p>{$ship_prefs['city']}, {$ship_prefs['province']} {$ship_prefs['postal_code']}<p>";
				$receipt .= "<p>Email: <a href='mailto:{$_config['orderemail']}'>{$_config['orderemail']}</a></p>";
				$receipt .= "</td></tr>";
				$receipt .= "<tr><td colspan=5 class='darkbg bgswap' style='border:none; width:100%; height:.4em; background:#ddd;'></td></tr>";
				$receipt .= "<tr><td colspan=5 style='border:none;'>&nbsp;</td></tr>";
				$receipt .= "<tr><th style='font-weight:600;background:#ddd;vertical-align:middle;text-indent:2px;' colspan=5 class='bgswap'>Transaction Type: Purchase</th></tr>";
				$receipt .= "<tr><td style='border:none;'>Order ID:</td><td style='border:none;'>{$recid[0]}</td><td  style='border:none;'/><td style='border:none;'>Receipt ID:</td><td style='border:none;'>{$responseData['ReceiptId']}</td></tr>";
				$receipt .= "<tr><td style='border:none;'>Date/Time:</td><td style='border:none;'>{$responseData['TransDate']} {$responseData['TransTime']}</td><td style='border:none;'/><td style='border:none;'>Approval Code:</td><td style='border:none;'>{$responseData['AuthCode']}</td></tr>";
				$receipt .= "<tr><td style='border:none;'>Reference Number:</td><td style='border:none;'>{$responseData['ReferenceNum']}</td><td style='border:none;' /><td style='border:none;'>Response/ISO:</td><td style='border:none;'>{$responseData['ResponseCode']}/{$responseData['ISO']}</td></tr>";
				$receipt .= "<tr><td style='border:none;'>Amount:</td><td style='border:none;'>$ {$responseData['TransAmount']}</td><td style='border:none;' /><td style='border:none;'>{$responseData['Message']}</td><td style='border:none;' /></tr></tbody>";
				$receipt .= "<tbody class='bordered'>";
				$receipt .= "<tr><td colspan=5 style='border:none;'>&nbsp;</td></tr>";
				$receipt .= "<tr><th class='bgswap' style='width:15%; border:1px solid #000; font-weight:600;background:#ddd;vertical-align:middle;text-indent:2px;'>Item</th><th class='bgswap' style='width:40%;border:1px solid #000; font-weight:600;background:#ddd;vertical-align:middle;text-indent:2px;'>Description</th><th class='bgswap' style='width:5%;border:1px solid #000; font-weight:600;background:#ddd;vertical-align:middle;text-indent:2px;'>Qty</th><td style='width:20%;border:none;' /><td style='width:20%;border:none;' /></tr>";
				
				foreach($info as $item) {
					$item_info = logged_query("SELECT * FROM `ecom_product` WHERE `url` = '{$item['name']}' LIMIT 1",0,array());
					$item_info = $item_info[0];
					$receipt .= "<tr><td style='border:1px solid #000;'>{$item_info['id']}</td><td style='border:1px solid #000;'>{$item_info['title']}";
					if($item['option'] != "none") {
						$optionnum = 0;
						$receipt .="<br />";
						foreach($item['option'] as $k=>$v) {
							$optionnum++;
							$receipt .= "$k: $v";
							if($optionnum < count($item['option'])) {
								$receipt .= ", ";
							}
						}
					}
					$receipt .= "</td><td style='border:1px solid #000;'>{$item['count']}</td><td colspan=2 style='border:none;' /></tr>";
				}
				$receipt .= "<tr><td style='border:1px solid #000;' /><td style='border:1px solid #000;' /><td style='border:1px solid #000;' /><td style='border:1px solid #000; width:100%; height:.4em; background:#ddd;' colspan=2 class='darkbg bgswap' /></tr>";
				$receipt .= "<tr><td style='border:1px solid #000;' /><td style='border:1px solid #000;' /><td style='border:1px solid #000;' /><th class='bgswap' style='border:1px solid #000; font-weight:600;background:#ddd;vertical-align:middle;text-indent:2px;'>SubTotal:</th><td style='border:1px solid #000;'>&nbsp;$ {$order['subtotal']}</td></tr>";
				$receipt .= "<tr><td style='border:1px solid #000;' /><td style='border:1px solid #000;' /><td style='border:1px solid #000;' /><th class='bgswap' style='border:1px solid #000; font-weight:600;background:#ddd;vertical-align:middle;text-indent:2px;'>Shipping:</th><td style='border:1px solid #000;'>&nbsp;$ {$order['ship_price']}</td></tr>";
				$receipt .= "<tr><td style='border:1px solid #000;' /><td style='border:1px solid #000;' /><td style='border:1px solid #000;' /><th class='bgswap' style='border:1px solid #000; font-weight:600;background:#ddd;vertical-align:middle;text-indent:2px;'>{$order['tax_name']}:</th><td style='border:1px solid #000;'>&nbsp;$ {$order['tax']}</td></tr>";
				
				$receipt .= "<tr><td style='border:1px solid #000;' /><td style='border:1px solid #000;' /><td  style='border:1px solid #000;'/><th class='bgswap' style='border:1px solid #000; font-weight:600;background:#ddd;vertical-align:middle;text-indent:2px;'>Total:</th><td style='border:1px solid #000;'>&nbsp;$ {$order['total']} {$_config['currency']}</td></tr>";
				$receipt .= "</tbody><tbody class='borderless'>";
				$receipt .= "<tr><th class='bgswap' style='font-weight:600;background:#ddd;vertical-align:middle;text-indent:2px;' colspan=5>Shipping Method: {$order['ship_method']}</th></tr>";
				$receipt .= "<tr><td style='border:none;' colspan=5>&nbsp;</td></tr>";
				$receipt .= "<tr><th class='bgswap' style='font-weight:600;background:#ddd;vertical-align:middle;text-indent:2px;' colspan=2>Bill To:</th><td style='border:none;' /><th class='bgswap' style='font-weight:600;background:#ddd;vertical-align:middle;text-indent:2px;' colspan=2>Ship To:</th></tr>";
				$receipt .= "<tr><td style='border:none;' colspan=2><p>{$first_name} {$last_name}<br />{$address1}";
				if(isset($address2) && $address2 != "") {
					$receipt .= "<br />{$address2}";
				}
				$receipt .= "<br />{$city}, {$province} {$postal_code}<br />{$country}<br />Telephone: {$phone}<br />Email: {$email}</p></td><td style='border:none;' />";
				$receipt .= "<td style='border:none;' colspan=2><p>{$shipto['first_name']} {$shipto['last_name']}<br />{$shipto['address1']}";
				if(isset($shipto['address2']) && $shipto['address2']!="") {
					$receipt .= "<br />{$shipto['address2']}";
				}
				$receipt .= "<br />{$shipto['city']}, {$shipto['province']} {$shipto['postal_code']}<br />{$shipto['country']}</p></td></tr>";
				$receipt .= "<tr><th class='bgswap' style='font-weight:600;background:#ddd;vertical-align:middle;text-indent:2px;' colspan=5>Special Instructions:</th></tr><tr><td style='border:none;' colspan=5>{$shipto['notes']}</td></tr>";
				$receipt .= "<tr><th class='bgswap' style='font-weight:600;background:#ddd;vertical-align:middle;text-indent:2px;' colspan=5>Return Policy:</th></tr><tr><td style='border:none;' colspan=5>{$_config['shopping_cart']['preferences']['returns_policy']}</td></tr>";
				
				$receipt .= "</tbody></table>";
				
				//send email
				$mail_from = $_config['orderemail'];
				
				/* MPDF ISSUES WITH MONERIS PAYMENT CLASSES, HAD TO REMOVE PDF ATTACHMENTS FOR NOW
				
				include($_config['admin_path'] . 'modules/shopping_cart/frontend/mpdf/mpdf.php');
				$mpdf=new mPDF();
				
				$mpdf->WriteHTML($receipt);
				
				$content = $mpdf->Output('', 'S');
				$content = chunk_split(base64_encode($content));
				$filename = "Receipt-".$responseData['ReceiptId'].".pdf";
				$message = "Thank you for purchasing from {$_config['company_name']}. Your receipt of purchase is attached to this email as {$filename}.<br /><br /><section style='width:60%; margin:0 auto;'>{$receipt}</section>";
								
				//Headers of PDF and e-mail
				$boundary = "$filename";

				$header = "--$boundary\n";
				$header .= "Content-Transfer-Encoding: 8bits\n";
				$header .= "Content-Type: text/html; charset=ISO-8859-1\r\n"; //plain
				$header .= "$message\n";
				$header .= "--$boundary\n";
				$header .= "Content-Type: application/octet-stream; name=\"".$filename."\"\n";
				$header .= "Content-Disposition: attachment; filename=\"".$filename."\"\n";
				$header .= "Content-Transfer-Encoding: base64"."\r\n";
				$header .= "$content\n";
				$header .= "--$boundary--\r\n";

				$header2 = "MIME-Version: 1.0\n";
				$header2 .= $mail_from ? "From: {$mail_from}\n" : '';
				$header2 .= "Return-Path: $mail_from\n";
				$header2 .= "Content-type: multipart/mixed; boundary=\"$boundary\"\n";
				$header2 .= "$boundary\r\n";

				mail($email,$_config['company_name']." Order Receipt",$header,$header2, "-r".$mail_from);
				mail($_config['orderemail'],$_config['company_name']." Order # {$recid[0]} - RECEIPT COPY",$header,$header2, "-r".$mail_from);
				
				//mail($email, $_config['company_name']." Order Receipt", $mailstring, $headers);
				//mail($_config['orderemail'],$_config['company_name']." Order # {$recid[0]}",$receipt,$headers);
				*/
				
				/** THIS BLOCK REPLACES PREVIOUS CODE WITH PDF ATTACHMENT **/
				$message = "Thank you for purchasing from {$_config['company_name']}. Here is your receipt.<br /><br /><section style='width:60%; margin:0 auto;'>{$receipt}</section>";
				
				$copymessage = "The following order has been placed, this recept is a copy of the receipt sent to the customer.<br /><br /><section style='width:60%; margin:0 auto;'>{$receipt}</section>";
				
				$headers  = $mail_from ? "From: {$mail_from}\n" : '';  
				$headers .= 'MIME-Version: 1.0' . "\n";
				$headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n"; 
				mail($email, $_config['company_name']." Order Receipt", $message, $headers);
				mail($_config['orderemail'],$_config['company_name']." Order # {$recid[0]} - RECEIPT COPY",$copymessage,$headers);
				/****/
				
				break;
		}
	} else {	// database error - not payment related
		echo "<h2>There has been an error. <br />This error is not related to your purchase - do not re-order as your card may be charged twice should you do so.</h2>";
	}
	
	echo $receipt;
}
?>
<script>
	$(document).ready( function() {
		$('.bgswap').css('background-color','#23262B');
	});
</script>
<?php
/* DATA
print("\nDataKey = " . $mpgResponse->getDataKey());
print("\nReceiptId = " . $mpgResponse->getReceiptId());
print("\nReferenceNum = " . $mpgResponse->getReferenceNum());
print("\nResponseCode = " . $mpgResponse->getResponseCode());
print("\nISO = " . $mpgResponse->getISO());
print("\nAuthCode = " . $mpgResponse->getAuthCode());
print("\nMessage = " . $mpgResponse->getMessage());
print("\nTransDate = " . $mpgResponse->getTransDate());
print("\nTransTime = " . $mpgResponse->getTransTime());
print("\nTransType = " . $mpgResponse->getTransType());
print("\nComplete = " . $mpgResponse->getComplete());
print("\nTransAmount = " . $mpgResponse->getTransAmount());
print("\nCardType = " . $mpgResponse->getCardType());
print("\nTxnNumber = " . $mpgResponse->getTxnNumber());
print("\nTimedOut = " . $mpgResponse->getTimedOut());
print("\nAVSResponse = " . $mpgResponse->getAvsResultCode());
print("\nResSuccess = " . $mpgResponse->getResSuccess());
print("\nPaymentType = " . $mpgResponse->getPaymentType());

//----------------- ResolveData ------------------------------

print("\n\nCust ID = " . $mpgResponse->getResDataCustId());
print("\nPhone = " . $mpgResponse->getResDataPhone());
print("\nEmail = " . $mpgResponse->getResDataEmail());
print("\nNote = " . $mpgResponse->getResDataNote());
print("\nMasked Pan = " . $mpgResponse->getResDataMaskedPan());
print("\nExp Date = " . $mpgResponse->getResDataExpDate());
print("\nCrypt Type = " . $mpgResponse->getResDataCryptType());
print("\nAvs Street Number = " . $mpgResponse->getResDataAvsStreetNumber());
print("\nAvs Street Name = " . $mpgResponse->getResDataAvsStreetName());
print("\nAvs Zipcode = " . $mpgResponse->getResDataAvsZipcode());
*/
?>