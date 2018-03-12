<?php
/**
 * WHMCS Sample Payment Callback File
 *
 * This sample file demonstrates how a payment gateway callback should be
 * handled within WHMCS.
 *
 * It demonstrates verifying that the payment gateway module is active,
 * validating an Invoice ID, checking for the existence of a Transaction ID,
 * Logging the Transaction for debugging and Adding Payment to an Invoice.
 *
 * For more information, please refer to the online documentation.
 *
 * @see https://developers.whmcs.com/payment-gateways/callbacks/
 *
 * @copyright Copyright (c) WHMCS Limited 2017
 * @license http://www.whmcs.com/license/ WHMCS Eula
 */

// Require libraries needed for gateway module functions.
require_once __DIR__ . '/../../../init.php';
require_once __DIR__ . '/../../../includes/gatewayfunctions.php';
require_once __DIR__ . '/../../../includes/invoicefunctions.php';

// Detect module name from filename.
$gatewayModuleName = basename(__FILE__, '.php');

// Fetch gateway configuration parameters.
$gatewayParams = getGatewayVariables($gatewayModuleName);

// Die if module is not active.
if (!$gatewayParams['type']) {
    die("Module Not Activated");
}

// Retrieve data returned in payment gateway callback
$resBank = $_POST['ResCode'];
$orderId = $_POST['SaleOrderId'];
$verifySaleOrderId = $_POST['SaleOrderId'];
$verifySaleReferenceId = $_POST['SaleReferenceId'];
$invoiceid = $_GET['iid'];
$amount = '';

//Get terminal information
$terminalID = $gatewayParams['terminalID'];
$userID = $gatewayParams['userID'];
$passwordID = $gatewayParams['passwordID'];
$gatewaymodule = $gatewayParams['paymentmethod'];


/**
 * Validate Callback Invoice ID.
 *
 * Checks invoice ID is a valid invoice number. Note it will count an
 * invoice in any status as valid.
 *
 * Performs a die upon encountering an invalid Invoice ID.
 *
 * Returns a normalised invoice ID.
 *
 * @param int $invoiceId Invoice ID
 * @param string $gatewayName Gateway Name
 */
$invoiceid = checkCbInvoiceID($invoiceid, $gatewayParams['name']);

/**
 * Check Callback Transaction ID.
 *
 * Performs a check for any existing transactions with the same given
 * transaction number.
 *
 * Performs a die upon encountering a duplicate.
 *
 * @param string $transactionId Unique Transaction ID
 */
checkCbTransID($invoiceid);


require_once("lib/nusoap.php");
$client = new nusoap_client('https://bpm.shaparak.ir/pgwchannel/services/pgw?wsdl');
$namespace='http://interfaces.core.sw.bps.com/';

$ops = array(
    'terminalId' => $terminalID,
    'userName' => $userID,
    'userPassword' => $passwordID,
    'orderId' => $orderId,
    'saleOrderId' => $verifySaleOrderId,
    'saleReferenceId' => $verifySaleReferenceId
);




if ($resBank == "0") { // transaction Success
	
	$amount = null;
	// get amount of invoice
	$where = array("id" => array("sqltype" => "LIKE", "value" => $invoiceid));
	$result_sql = select_query("tblinvoices", "total", $where);

	while ($data = mysql_fetch_array($result_sql)) {
	   $amount = $data['total'];
	}

	
    $result = $client->call('bpVerifyRequest', $ops, $namespace);// VERIFY REQUEST
    if ($result == "0") {
        $result = $client->call('bpSettleRequest', $ops, $namespace);// SETTLE REQUEST
		
        if ($result == "0") {
			echo('SETTLE REQUEST'.'<br>');
            mysql_query("UPDATE `morders` SET `resBank` = '$resBank',`saleorderid`='$verifySaleReferenceId',`isPayed`='1' WHERE `morders`.`oid` ='$orderId';");
			
			
            addInvoicePayment($invoiceid, $verifySaleReferenceId, $amount, 0, $gatewaymodule);
			logTransaction($gatewayParams['name'], $_POST, "Success");
			
            header("Location: " . $gatewayParams['systemurl'] . "viewinvoice.php?id=" . $invoiceid);
			exit();
        }
    } else {
        $result = $client->call('bpInquiryRequest', $ops, $namespace);
        if ($result == "0") {
            $result = $client->call('bpSettleRequest', $ops, $namespace);
            if ($result == "0") {
                mysql_query("UPDATE `morders` SET `resBank` = '$resBank',`saleorderid`='$verifySaleReferenceId',`isPayed`='1' WHERE `morders`.`oid` ='$orderId';");
                addInvoicePayment($invoiceid, $verifySaleReferenceId, $amount, 0, $gatewaymodule);
                logTransaction($gatewayParams['name'], $_POST, "Success");
				
                header("Location: " . $gatewayParams['systemurl'] . "viewinvoice.php?id=" . $invoiceid);
				exit();
            }
        } else {
            $result = $client->call('bpReversalRequest', $ops, $namespace);
            if ($result == "0") {
                mysql_query("UPDATE `morders` SET `resBank` = '$resBank',`saleorderid`='$verifySaleReferenceId',`isPayed`='2' WHERE `morders`.`oid` ='$orderId';");
				logTransaction($gatewayParams['name'], $_POST, "Failure");
            }
            header("Location: " . $gatewayParams['systemurl'] . "viewinvoice.php?id=" . $invoiceid);
			exit();
        }

    }
} else {
    mysql_query("UPDATE `morders` SET `resBank` = '$resBank',`saleorderid`='$verifySaleReferenceId' WHERE `morders`.`oid` ='$orderId';");
    logTransaction($gatewayParams['name'], $_POST, "Failure");
    header("Location: " . $gatewayParams['systemurl'] . "viewinvoice.php?id=" . $invoiceid);
	exit();
}

header("Location: " . $gatewayParams['systemurl']);