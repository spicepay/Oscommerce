<?php

require('includes/application_top.php');
require_once(dirname(__FILE__) . "/includes/modules/payment/SpicePay/init.php");
require_once(dirname(__FILE__) . "/includes/modules/payment/SpicePay/version.php");

    if (isset($_POST['paymentId']) && isset($_POST['orderId']) && isset($_POST['hash']) && isset($_POST['paymentAmountUSD']) && isset($_POST['receivedAmountUSD']))
    {

      $paymentId = addslashes(filter_input(INPUT_POST, 'paymentId', FILTER_SANITIZE_STRING));
      $orderId = addslashes(filter_input(INPUT_POST, 'orderId', FILTER_SANITIZE_STRING));
      $hash = addslashes(filter_input(INPUT_POST, 'hash', FILTER_SANITIZE_STRING, FILTER_FLAG_STRIP_HIGH));    
      $clientId = addslashes(filter_input(INPUT_POST, 'clientId', FILTER_SANITIZE_STRING));
      $paymentAmountBTC = addslashes(filter_input(INPUT_POST, 'paymentAmountBTC', FILTER_SANITIZE_NUMBER_INT));
      $paymentAmountUSD = addslashes(filter_input(INPUT_POST, 'paymentAmountUSD', FILTER_SANITIZE_STRING));
      $receivedAmountBTC = addslashes(filter_input(INPUT_POST, 'receivedAmountBTC', FILTER_SANITIZE_NUMBER_INT));
      $receivedAmountUSD = addslashes(filter_input(INPUT_POST, 'receivedAmountUSD', FILTER_SANITIZE_STRING));
      $status = addslashes(filter_input(INPUT_POST, 'status', FILTER_SANITIZE_STRING));

global $db;

$order_id = $_REQUEST['order_id'];

$order = tep_db_query("select orders_id from " . TABLE_ORDERS . " where orders_id = '" . intval($order_id) . "' limit 1");
if (tep_db_num_rows($order) <= 0)
  throw new Exception('Order #' . $order_id . ' does not exists');


  if(isset($_POST['paymentCryptoAmount']) && isset($_POST['receivedCryptoAmount'])) {
      $paymentCryptoAmount = addslashes(filter_input(INPUT_POST, 'paymentCryptoAmount', FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION));
      $receivedCryptoAmount = addslashes(filter_input(INPUT_POST, 'receivedCryptoAmount', FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION));
  }
  else {
      $paymentCryptoAmount = addslashes(filter_input(INPUT_POST, 'paymentAmountBTC', FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION));
      $receivedCryptoAmount = addslashes(filter_input(INPUT_POST, 'receivedAmountBTC', FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION));
  }

$spicepay_order = \SpicePay\Merchant\Order::findOrFail(34, array(), array(
  'app_id' => MODULE_PAYMENT_SPICEPAY_SITE_ID,
  'api_secret' => MODULE_PAYMENT_SPICEPAY_API_SECRET,
  'user_agent' => 'SpicePay - osCommerce Extension v' . SPICEPAY_OSCOMMERCE_EXTENSION_VERSION));
$secret_spicepay_code=MODULE_PAYMENT_SPICEPAY_API_SECRET;

$hashString = md5($secret_spicepay_code . $paymentId . $orderId . $clientId . $paymentCryptoAmount . $paymentAmountUSD . $receivedCryptoAmount . $receivedAmountUSD . $status);

if (0 == strcmp($hashString, $hash)) 
{
 
  switch ($status) {
    case 'paid':
      $cg_order_status = MODULE_PAYMENT_SPICEPAY_PAID_STATUS_ID;
      break;
    case 'canceled':
      $cg_order_status = MODULE_PAYMENT_SPICEPAY_CANCELED_STATUS_ID;
      break;
    case 'expired':
      $cg_order_status = MODULE_PAYMENT_SPICEPAY_EXPIRED_STATUS_ID;
      break;
    case 'invalid':
      $cg_order_status = MODULE_PAYMENT_SPICEPAY_INVALID_STATUS_ID;
      break;
    case 'refunded':
      $cg_order_status = MODULE_PAYMENT_SPICEPAY_REFUNDED_STATUS_ID;
      break;
    default:
      $cg_order_status = NULL;
  }

  if ($cg_order_status)
    tep_db_query("update ". TABLE_ORDERS. " set orders_status = " . $cg_order_status . " where orders_id = ". intval($orderId));
}

} else {
  echo "no";
}
