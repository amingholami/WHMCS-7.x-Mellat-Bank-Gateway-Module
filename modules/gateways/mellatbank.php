<?php
/**
 * WHMCS Sample Payment Gateway Module
 *
 * Payment Gateway modules allow you to integrate payment solutions with the
 * WHMCS platform.
 *
 * This sample file demonstrates how a payment gateway module for WHMCS should
 * be structured and all supported functionality it can contain.
 *
 * Within the module itself, all functions must be prefixed with the module
 * filename, followed by an underscore, and then the function name. For this
 * example file, the filename is "gatewaymodule" and therefore all functions
 * begin "mellatbank_".
 *
 * If your module or third party API does not support a given function, you
 * should not define that function within your module. Only the _config
 * function is required.
 *
 * For more information, please refer to the online documentation.
 *
 * @see https://developers.whmcs.com/payment-gateways/
 *
 * @copyright Copyright (c) WHMCS Limited 2017
 * @license http://www.whmcs.com/license/ WHMCS Eula
 */

if (!defined("WHMCS")) {
    die("This file cannot be accessed directly");
}

/**
 * Define module related meta data.
 *
 * Values returned here are used to determine module related capabilities and
 * settings.
 *
 * @see https://developers.whmcs.com/payment-gateways/meta-data-params/
 *
 * @return array
 */
function mellatbank_MetaData()
{
    return array(
        'DisplayName' => 'درگاه بانک ملت',
        'APIVersion' => '1.2', // Use API Version 1.2
        'DisableLocalCredtCardInput' => true,
        'TokenisedStorage' => false,
    );
}

/**
 * Define gateway configuration options.
 *
 * The fields you define here determine the configuration options that are
 * presented to administrator users when activating and configuring your
 * payment gateway module for use.
 *
 * Supported field types include:
 * * text
 * * password
 * * yesno
 * * dropdown
 * * radio
 * * textarea
 *
 * Examples of each field type and their possible configuration parameters are
 * provided in the sample function below.
 *
 * @return array
 */
function mellatbank_config()
{
    return array(
        // the friendly display name for a payment gateway should be
        // defined here for backwards compatibility
        'FriendlyName' => array(
            'Type' => 'System',
            'Value' => 'درگاه اختصاصی بانک ملت',
        ),
        // a text field type allows for single line text input
        'terminalID' => array(
            'FriendlyName' => 'Terminal ID',
            'Type' => 'text',
            'Size' => '25',
            'Default' => '',
            'Description' => 'Enter your Terminal ID here',
        ),
		// a text field type allows for single line text input
        'userID' => array(
            'FriendlyName' => 'User ID',
            'Type' => 'text',
            'Size' => '25',
            'Default' => '',
            'Description' => 'Enter your User ID here',
        ),
        // a password field type allows for masked text input
        'passwordID' => array(
            'FriendlyName' => 'Password ID',
            'Type' => 'password',
            'Size' => '25',
            'Default' => '',
            'Description' => 'Enter Password ID here',
        ),
    );
}

function mellatbank_activate() {

	$query = "CREATE TABLE IF NOT EXISTS `morders` (
			  `oid` int(20) NOT NULL AUTO_INCREMENT,
			  `invoiceid` int(20) NOT NULL,
			  `amount` double NOT NULL,
			  `description` text NOT NULL,
			  `isPayed` int(2) NOT NULL,
			  `resLink` int(4) NOT NULL,
			  `resBank` int(4) NOT NULL,
			  `refid` varchar(255) NOT NULL,
			  `saleorderid` bigint(30) NOT NULL,
			  `refund` int(2) NOT NULL,
			  PRIMARY KEY (`oid`)
			) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;";
	mysql_query($query);


    return array('status'=>'success','description'=>'ماژول درگاه بانک ملت با موفقیت فعال شد.');
}

function mellatbank_deactivate() {

    $query = "DROP TABLE `morders`";
	mysql_query($query);

    return array('status'=>'success','description'=>'ماژول درگاه بانک ملت با موفقیت غیر فعال شد.');
}
/*
function mellatbank_upgrade($vars) {
    $version = $vars['version'];

    switch($version){
        case "1":
        case "1.0.1":
            
			$sql = "";
            mysql_query($sql);
			
            break;
 }
*/	


/**
 * Payment link.
 *
 * Required by third party payment gateway modules only.
 *
 * Defines the HTML output displayed on an invoice. Typically consists of an
 * HTML form that will take the user to the payment gateway endpoint.
 *
 * @param array $params Payment Gateway Module Parameters
 *
 * @see https://developers.whmcs.com/payment-gateways/third-party-gateway/
 *
 * @return string
 */
function mellatbank_link($params)
{
    // Gateway Configuration Parameters
    $terminalID = $params['terminalID'];
	$userID = $params['userID'];
    $passwordID = $params['passwordID'];

    // Invoice Parameters
    $invoiceId = $params['invoiceid'];
    $description = $params["description"];
    $amount = round($params['amount']);
    $currencyCode = $params['currency'];
	mysql_query("INSERT INTO `morders` (`oid`, `invoiceid`, `amount`, `description`, `isPayed`, `resLink`, `resBank`, `refid`, `saleorderid`, `refund`) VALUES (NULL, '$invoiceId', '$amount', '$description', '0', '0', '-1', 'no', '-1', '0');");
    $orderId = mysql_insert_id();

    // Client Parameters
    $firstname = $params['clientdetails']['firstname'];
    $lastname = $params['clientdetails']['lastname'];
    $email = $params['clientdetails']['email'];
    $address1 = $params['clientdetails']['address1'];
    $address2 = $params['clientdetails']['address2'];
    $city = $params['clientdetails']['city'];
    $state = $params['clientdetails']['state'];
    $postcode = $params['clientdetails']['postcode'];
    $country = $params['clientdetails']['country'];
    $phone = $params['clientdetails']['phonenumber'];

    // System Parameters
    $companyName = $params['companyname'];
    $systemUrl = $params['systemurl'];
    $returnUrl = $params['returnurl'];
    $langPayNow = $params['langpaynow'];
    $moduleDisplayName = $params['name'];
    $moduleName = $params['paymentmethod'];
    $whmcsVersion = $params['whmcsVersion'];

    $url = 'https://bpm.shaparak.ir/pgwchannel/startpay.mellat';

    $postfields = array();
    $postfields['username'] = $username;
    $postfields['invoice_id'] = $invoiceId;
    $postfields['description'] = $description;
    $postfields['amount'] = $amount;
    $postfields['currency'] = $currencyCode;
    $postfields['first_name'] = $firstname;
    $postfields['last_name'] = $lastname;
    $postfields['email'] = $email;
    $postfields['address1'] = $address1;
    $postfields['address2'] = $address2;
    $postfields['city'] = $city;
    $postfields['state'] = $state;
    $postfields['postcode'] = $postcode;
    $postfields['country'] = $country;
    $postfields['phone'] = $phone;
    $postfields['callback_url'] = $systemUrl . 'modules/gateways/callback/' . $moduleName . '.php?iid=' . $invoiceId;
    $postfields['return_url'] = $returnUrl;
	$localDate =  date("Ymd");
	$localTime =  date("His");
	$payerId = '0';
	
	
	
	require_once("callback/lib/nusoap.php");

	//curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
	//$page = curl_exec ($ch);
	
	$client = new nusoap_client('https://bpm.shaparak.ir/pgwchannel/services/pgw?wsdl');
	$namespace='http://interfaces.core.sw.bps.com/';
	
	// Check for an error
	$err = $client->getError();
	if ($err) {
		echo '<h2>Constructor error</h2><pre>' . $err . '</pre>';
		die();
	}
	
	$parameters = array(
			'terminalId' => $terminalID,
			'userName' => $userID,
			'userPassword' => $passwordID,
			'orderId' => $orderId,
			'amount' => $amount,
			'localDate' => $localDate,
			'localTime' => $localTime,
			'additionalData' => $description,
			'callBackUrl' => $postfields['callback_url'],
			'payerId' => $payerId);
	
		// Call the SOAP method
		$result = $client->call('bpPayRequest', $parameters, $namespace);
	
	
	// Check for a fault
		if ($client->fault) {
			echo '<h2>Fault</h2><pre>';
			print_r($result);
			echo '</pre>';
			die();
		} 
		else {
			// Check for errors
			
			$resultStr  = $result;

			$err = $client->getError();
			if ($err) {
				// Display the error
				echo '<h2>Error</h2><pre>' . $err . '</pre>';
				die();
			} 
			else {
				
				

				
				$res = explode (',',$resultStr);
				$ResCode = $res[0];
				
				if ($ResCode == "0") {
					// Update table, Save RefId
					$refid = $res[1];
                    $resLink = $res[0];
                    mysql_query("UPDATE `morders` SET `refid` = '$refid',`resLink` = '$resLink' WHERE `morders`.`oid` ='$orderId';");
					
					//echo "<script language='javascript' type='text/javascript'>postRefId('" . $res[1] . "');</script>";
					
					$htmlOutput = '<form method="post" action="' . $url . '" method="post" target="_self">';
					$htmlOutput .= '<input type="hidden" name="RefId" value="' . $res[1] . '" />';
					$htmlOutput .= '<input type="submit" value="' . $langPayNow . '" />';
					$htmlOutput .= '</form>';

					return $htmlOutput;
					// Display the result
				} 
				else {
				// log error in app
					// Update table, log the error
					// Show proper message to user
					$resLink = $res[0];
                    mysql_query("UPDATE `morders` SET `resLink` = '$resLink' WHERE `morders`.`oid` ='$orderId';");
                    return ("اتصال به بانک دچار مشکل شد است .خطا: $resLink");
				}
			}// end Display the result
		}// end Check for errors
	
}

/**
 * Refund transaction.
 *
 * Called when a refund is requested for a previously successful transaction.
 *
 * @param array $params Payment Gateway Module Parameters
 *
 * @see https://developers.whmcs.com/payment-gateways/refunds/
 *
 * @return array Transaction response status
 */
function mellatbank_refund($params)
{
    // Gateway Configuration Parameters
    $terminalID = $params['terminalID'];
	$userID = $params['userID'];
    $passwordID = $params['passwordID'];

    // Transaction Parameters
    $transactionIdToRefund = $params['transid'];
    $refundAmount = $params['amount'];
    $currencyCode = $params['currency'];

    // Client Parameters
    $firstname = $params['clientdetails']['firstname'];
    $lastname = $params['clientdetails']['lastname'];
    $email = $params['clientdetails']['email'];
    $address1 = $params['clientdetails']['address1'];
    $address2 = $params['clientdetails']['address2'];
    $city = $params['clientdetails']['city'];
    $state = $params['clientdetails']['state'];
    $postcode = $params['clientdetails']['postcode'];
    $country = $params['clientdetails']['country'];
    $phone = $params['clientdetails']['phonenumber'];

    // System Parameters
    $companyName = $params['companyname'];
    $systemUrl = $params['systemurl'];
    $langPayNow = $params['langpaynow'];
    $moduleDisplayName = $params['name'];
    $moduleName = $params['paymentmethod'];
    $whmcsVersion = $params['whmcsVersion'];

    // perform API call to initiate refund and interpret result

    return array(
        // 'success' if successful, otherwise 'declined', 'error' for failure
        'status' => 'success',
        // Data to be recorded in the gateway log - can be a string or array
        'rawdata' => $responseData,
        // Unique Transaction ID for the refund transaction
        'transid' => $refundTransactionId,
        // Optional fee amount for the fee value refunded
        'fees' => $feeAmount,
    );
}
