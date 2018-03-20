<?php

class spicepay
{
  public $code;
  public $title;
  public $description;
  public $enabled;

  function spicepay()
  {
    $this->code             = 'spicepay';
    $this->title            = MODULE_PAYMENT_SPICEPAY_TEXT_TITLE;
    $this->description      = MODULE_PAYMENT_SPICEPAY_TEXT_DESCRIPTION;
    $this->app_id           = MODULE_PAYMENT_SPICEPAY_SITE_ID;
    $this->api_secret       = MODULE_PAYMENT_SPICEPAY_API_SECRET;
    $this->receive_currency = MODULE_PAYMENT_SPICEPAY_RECEIVE_CURRENCY;
    $this->enabled          = ((MODULE_PAYMENT_SPICEPAY_STATUS == 'True') ? true : false);
  }

  function javascript_validation()
  {
    return false;
  }

  function selection()
  {
    return array('id' => $this->code, 'module' => $this->title);
  }

  function pre_confirmation_check()
  {
    return false;
  }

  function confirmation()
  {
    return false;
  }

  function process_button()
  {
    return false;
  }

  function before_process()
  {
    return false;
  }

  function after_process()
  {
    global $insert_id, $order;

    $info = $order->info;

    $configuration = tep_db_query("select configuration_value from " . TABLE_CONFIGURATION . " where configuration_key='STORE_NAME' limit 1");
    $configuration = tep_db_fetch_array($configuration);
    $products = tep_db_query("select oc.products_id, oc.products_quantity, pd.products_name from " . TABLE_ORDERS_PRODUCTS . " as oc left join " . TABLE_PRODUCTS_DESCRIPTION . " as pd on pd.products_id=oc.products_id  where orders_id=" . intval($insert_id));

    $description = array();
    foreach ($products as $product) {
      $description[] = $product['products_quantity'] . ' × ' . $product['products_name'];
    }

    $callback = tep_href_link('spicepay_callback.php', $parameters='', $connection='NONSSL', $add_session_id=true, $search_engine_safe=true, $static=true );

    $params = array(
      'order_id'         => $insert_id,
      'price'            => number_format($info['total'], 2, '.', ''),
      'currency'         => $info['currency'],
      'receive_currency' => MODULE_PAYMENT_SPICEPAY_RECEIVE_CURRENCY,
      'callback_url'     => $callback,
      'cancel_url'       => tep_href_link(FILENAME_CHECKOUT_PAYMENT),
      'success_url'      => tep_href_link(FILENAME_CHECKOUT_SUCCESS),
      'title'            => $configuration->fields['configuration_value'] . ' Order #' . $insert_id,
      'description'      => join($description, ', ')
    );

    require_once(dirname(__FILE__) . "/SpicePay/init.php");
    require_once(dirname(__FILE__) . "/SpicePay/version.php");

    $order = \SpicePay\Merchant\Order::createOrFail($params, array(), array(
      'app_id' => MODULE_PAYMENT_SPICEPAY_SITE_ID,
      'api_secret' => MODULE_PAYMENT_SPICEPAY_API_SECRET,
      'user_agent' => 'SpicePay - osCommerce Extension v' . SPICEPAY_OSCOMMERCE_EXTENSION_VERSION));

    $_SESSION['cart']->reset(true);
    tep_redirect($order->payment_url);

    return false;
  }

  function get_error()
  {
    return false;
  }

  function check()
  {
    if (!isset($this->_check)) {
      $check_query  = tep_db_query("select configuration_value from " . TABLE_CONFIGURATION . " where configuration_key = 'MODULE_PAYMENT_SPICEPAY_STATUS'");
      $this->_check = tep_db_num_rows($check_query);
    }

    return $this->_check;
  }

  function install()
  {
    $callbackSecret = md5('zencart_' . mt_rand());

    tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Enable SpicePay Module', 'MODULE_PAYMENT_SPICEPAY_STATUS', 'False', 'Enable the SpicePay bitcoin plugin?', '6', '0', 'tep_cfg_select_option(array(\'True\', \'False\'), ', now())");
    tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('SpicePay APP ID', 'MODULE_PAYMENT_SPICEPAY_SITE_ID', '0', 'Your SpicePay Site ID', '6', '0', now())");
    tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('SpicePay APP Secret', 'MODULE_PAYMENT_SPICEPAY_API_SECRET', '0', 'Your SpicePay Secret Code', '6', '0', now())");
    tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, use_function, date_added) values ('Set Pending Order Status', 'MODULE_PAYMENT_SPICEPAY_PENDING_STATUS_ID', '" . intval(DEFAULT_ORDERS_STATUS_ID) .  "', 'Status in your store when SpicePay order status is pending.<br />(\'Pending\' recommended)', '6', '5', 'tep_cfg_pull_down_order_statuses(', 'tep_get_order_status_name', now())");
    tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, use_function, date_added) values ('Set Paid Order Status', 'MODULE_PAYMENT_SPICEPAY_PAID_STATUS_ID', '2', 'Status in your store when SpicePay order status is paid.<br />(\'Processing\' recommended)', '6', '6', 'tep_cfg_pull_down_order_statuses(', 'tep_get_order_status_name', now())");
    tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, use_function, date_added) values ('Set Invalid Order Status', 'MODULE_PAYMENT_SPICEPAY_INVALID_STATUS_ID', '2', 'Status in your store when SpicePay order status is invalid.<br />(\'Failed\' recommended)', '6', '6', 'tep_cfg_pull_down_order_statuses(', 'tep_get_order_status_name', now())");
    tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, use_function, date_added) values ('Set Expired Order Status', 'MODULE_PAYMENT_SPICEPAY_EXPIRED_STATUS_ID', '2', 'Status in your store when SpicePay order status is expired.<br />(\'Expired\' recommended)', '6', '6', 'tep_cfg_pull_down_order_statuses(', 'tep_get_order_status_name', now())");
    tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, use_function, date_added) values ('Set Canceled Order Status', 'MODULE_PAYMENT_SPICEPAY_CANCELED_STATUS_ID', '2', 'Status in your store when SpicePay order status is canceled.<br />(\'Canceled\' recommended)', '6', '6', 'tep_cfg_pull_down_order_statuses(', 'tep_get_order_status_name', now())");
  }

  function remove ()
  {
    tep_db_query("delete from " . TABLE_CONFIGURATION . " where configuration_key LIKE 'MODULE\_PAYMENT\_SPICEPAY\_%'");
  }

  function keys()
  {
    return array(
      'MODULE_PAYMENT_SPICEPAY_STATUS',
      'MODULE_PAYMENT_SPICEPAY_SITE_ID',
      'MODULE_PAYMENT_SPICEPAY_API_SECRET',
      'MODULE_PAYMENT_SPICEPAY_PENDING_STATUS_ID',
      'MODULE_PAYMENT_SPICEPAY_PAID_STATUS_ID',
      'MODULE_PAYMENT_SPICEPAY_INVALID_STATUS_ID',
      'MODULE_PAYMENT_SPICEPAY_EXPIRED_STATUS_ID'
    );
  }
}
function spicepay_censorize($value) {
  return "(hidden for security reasons)";
}
