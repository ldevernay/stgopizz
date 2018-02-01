<?php
/*
Doc sur PayPal standard :
https://cms.paypal.com/uk/cgi-bin/?cmd=_render-content&content_ID=developer/e_howto_html_Appx_websitestandard_htmlvariables
*/

define( "WEBMASTER_LANG", "fr" );
define( "PAYMENT_TIME_SERVER", 0 );
define( "PAYMENT_TIME_OFFSET", -1 );
define( "PW_PAYPAL_MERCHANT_ID", "dom4@live.fr" );
define( "PW_PAYPAL_LOGO_URL", "" );
define( "PW_PAYPAL_BTN_TEXT", "Procéder au paiement" );
define( "PW_PAYPAL_BTN_IMG", "" );
define( "PW_PAYPAL_CURRENCY", "EUR" );
define( "PW_PAYPAL_CURRENCY_LIST", "AUD|BRL|CAD|CHF|CZK|DKK|EUR|GBP|HKD|HUF|ILS|JPY|MXN|MYR|NOK|NZD|PHP|PLN|SEK|SGD|THB|TWD|USD" );
define( "PW_PAYPAL_TEST_MODE", 0 );
define( "PW_PAYPAL_UPLOAD_CI", 1 );
define( "PW_PAYPAL_UPLOAD_SHOPCART", 1 );
define( "PW_PAYPAL_URL_OK", "http://www.stgopizz.com/index.html" );
define( "PW_PAYPAL_URL_OK_TEXT", "Dans 48h, vous pognez la Pogne et (ou) le ST-GO !" );
define( "PW_PAYPAL_URL_CANCEL", "http://www.stgopizz.com/index.html" );
define( "PW_PAYPAL_URL_CANCEL_TEXT", "Vous renoncez au bonheur de la Pogne?" );
define( "PW_PAYPAL_IPN_URL", "http://www.stgopizz.com/_iserv/twsc/ppipn.php" );


// https://developer.paypal.com/docs/classic/ipn/gs_IPN/
if( file_exists( "../common/ipn.php" ) ) {
	@require_once( "../common/ipn.php" );
}

// read the post request from PayPal system and add 'cmd' (+ save in $content the request in text format for logs in case of errors)
$content = "";
$req = 'cmd=_notify-validate';
foreach ($_POST as $key => $value) {
	$content .= $key."=".stripslashes($value)."\r\n";
	$value = urlencode(stripslashes($value));
	$req .= "&$key=$value";
}

$simulation = ( $_POST['test'] == "simulation" );

function report_error( $ref, $errmsg ) {
	global $simulation;
	if( $simulation )
		echo "(err-".$ref.".txt) ".$errmsg;
	// file_put_contents( "err-".$ref.".txt", $errmsg );
}

// post back to PayPal system to validate
$header .= "POST /cgi-bin/webscr HTTP/1.1\r\n";
$header .= "Content-Type: application/x-www-form-urlencoded\r\n";
$header .= "Content-Length: " . strlen($req) . "\r\n\r\n";
if( $simulation ) {	
	$fp = true; 
} else {
	// Send an empty HTTP 200 OK response to acknowledge receipt of the notification 
	header('HTTP/1.1 200 OK'); 
	if( PW_PAYPAL_TEST_MODE ) {
		$fp = fsockopen('ssl://www.sandbox.paypal.com', 443, $errno, $errstr, 30);
	} else {
		$fp = fsockopen('ssl://www.paypal.com', 443, $errno, $errstr, 30);
	}
}
// assign posted variables to local variables
// More info: https://cms.paypal.com/fr/cgi-bin/?cmd=_render-content&content_ID=developer/e_howto_html_IPNandPDTVariables
// Example of PP ipn implementation: https://developer.paypal.com/docs/classic/ipn/gs_IPN/
// { convertion en UTF8 du libellé produit car Paypal ne renvoi pas toujours cette chaine dans le bon format
//	$item_name = $_POST['item_name'];
$charset = strtolower($_POST['charset']);
if( $charset != "" && $charset != "utf-8" ) {
	$item_name = utf8_encode($_POST['item_name']);
} else {
	$item_name = $_POST['item_name'];
}
// }

$item_number = $_POST['item_number'];
$payment_status = strtolower( $_POST['payment_status'] );
$payment_amount = $_POST['mc_gross'];
$payment_currency = $_POST['mc_currency'];
$txn_id = $_POST['txn_id'];
$txn_type = $_POST['txn_type'];
$receiver_email = $_POST['receiver_email'];
$payer_email = $_POST['payer_email'];
$residence_country = $_POST['residence_country'];

// our own signature is saved in "custom" parameter
$signature = $_POST['custom'];
$reference = $_POST['invoice'];

$Payment_Accepted = false;
$Status_Msg = "";
$newfilename = "";
$nfp = false;

//echo "simulation ($simulation)";
if (!$fp) 
{
	// HTTP ERROR
	report_error( "config", "PayPal HTTP configuration error" );
	die("");
} 
else 
{
	if( !$simulation ) 
		fputs ($fp, $header . $req);
	while ( ( $simulation && !$Payment_Accepted ) || ( !$simulation && !feof($fp) ) ) 
	{
		if( $simulation ) { 
			// mode TEST = set response to verified
			$res = "VERIFIED"; 
			$Payment_Accepted = true;
		} else {
			// mode PROD = read the PayPal response
			$res = fgets ($fp, 1024);
		}	
		if (strcmp($res, "VERIFIED") == 0 || $SANDBOX ) 
		{
			if( $payment_status == "pending" ) 
			{
				if( WEBMASTER_LANG == "fr" ) {
					$Status_Msg = "Paiement PayPal en attente pour la raison suivante: ".($_POST['pending_reason']);
				} else
					$Status_Msg = "Pending PayPal payment due to the following reason: ".($_POST['pending_reason']);				
			} 			
			else // check signature
			//if( $signature == md5( $reference ) ) 
			{
				if( WEBMASTER_LANG == "fr" ) {
					$Status_Msg = "Paiement accepté et validé";
				} else {
					$Status_Msg = "Paypal payment verified and validated";
				}
				$Payment_Accepted = true;
			}
		} 
		else if (strcmp ($res, "INVALID") == 0) 
		{
			if( WEBMASTER_LANG == "fr" ) {
				$Status_Msg = "Paiement rejeté car réponse non validée par PayPal";
			} else {
				$Status_Msg = "Payment rejected because not validated by PayPal";
			}
		}
	}
	if( !$fp )
		fclose ($fp);	
}

//echo "$Status_Msg<br>$Status_Code<br>";
//report_error( $Status_Msg );

/*
$Payment_Date = date("d/m/Y-H:i:s");
$xml_content .= "  <date>".CR.LF;
$xml_content .= "    <year>".$Payment_Date{6}.$Payment_Date{7}.$Payment_Date{8}.$Payment_Date{9}."</year>".CR.LF;
$xml_content .= "    <month>".$Payment_Date{3}.$Payment_Date{4}."</month>".CR.LF;
$xml_content .= "    <day>".$Payment_Date{0}.$Payment_Date{1}."</day>".CR.LF; 
$xml_content .= "    <hour>".$Payment_Date{11}.$Payment_Date{12}."</hour>".CR.LF;
$xml_content .= "    <minute>".$Payment_Date{14}.$Payment_Date{15}."</minute>".CR.LF;
$xml_content .= "    <second>".$Payment_Date{17}.$Payment_Date{18}."</second>".CR.LF;
$xml_content .= "  </date>".CR.LF; 
$xml_content .= "  <from_ip>".$_SERVER['REMOTE_ADDR']."</from_ip>".CR.LF;

$xml_content .= "  <paypal_ipn>".CR.LF; 
$xml_content .= "    <txn_id>".$txn_id."</txn_id>".CR.LF;
$xml_content .= "    <charset>".$charset."</charset>".CR.LF;
$xml_content .= "    <txn_type>".$txn_type."</txn_type>".CR.LF;
$xml_content .= "    <amount>".$payment_amount."</amount>".CR.LF;
$xml_content .= "    <currency>".$payment_currency."</currency>".CR.LF;
$xml_content .= "    <label>".$item_name."</label>".CR.LF;
$xml_content .= "    <residence_country>".$residence_country."</residence_country>".CR.LF;
$xml_content .= "    <status>".$payment_status."</status>".CR.LF;
$xml_content .= "  </paypal_ipn>".CR.LF; 
*/

if( $Payment_Accepted ) {
	//-- deliver by email
	if( function_exists( 'DeliverOrderByEmail' ) ) {
		// echo "deliver $reference";
		DeliverOrderByEmail( $reference );
	}
} else {
	report_error( $reference, $Status_Msg );
}

//if( $simulation )
//	die( "CGI5 simulation terminated <br> Status_Code = $Status_Code ( $Status_Msg ) <br> order send to $email_order / error send to $email_webmaster" );

?>