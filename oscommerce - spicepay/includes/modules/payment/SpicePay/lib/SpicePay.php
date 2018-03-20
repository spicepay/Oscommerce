<?php
namespace SpicePay;

class SpicePay
{
    const VERSION           = '2.0.1';
    const USER_AGENT_ORIGIN = 'SpicePay PHP Library';

    public static $app_id      = '';
    public static $api_secret  = '';
    public static $user_agent  = '';

    public static function config($authentication)
    {
        if (isset($authentication['app_id']))
            self::$app_id = $authentication['app_id'];

        if (isset($authentication['api_secret']))
            self::$api_secret = $authentication['api_secret'];

        if (isset($authentication['user_agent']))
            self::$user_agent = $authentication['user_agent'];
    }

    public static function testConnection($authentication = array())
    {
        try {
            self::request('/auth/test', 'GET', array(), $authentication);

            return true;
        } catch (\Exception $e) {
            return get_class($e) . ': ' . $e->getMessage();
        }
    }

    public static function request($url, $method = 'POST', $params = array(), $authentication = array())
    {
        $app_id      = isset($authentication['app_id']) ? $authentication['app_id'] : self::$app_id;
        $app_secret  = isset($authentication['api_secret']) ? $authentication['api_secret'] : self::$api_secret;
        $user_agent  = isset($authentication['user_agent']) ? $authentication['user_agent'] : (isset(self::$user_agent) ? self::$user_agent : (self::USER_AGENT_ORIGIN . ' v' . self::VERSION));

        # Check if credentials was passed
        if (empty($app_id) || empty($app_secret))
            \SpicePay\Exception::throwException(400, array('reason' => 'CredentialsMissing'));

global $cart;
$cart->reset(true);
echo '<div class="pwait">Please wait...<div class="loader"></div></div>

<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.12.4/jquery.min.js"></script>

<script>
$(document).ready(function(){
     $("#paymentForm").submit();
});
</script>

<div class="buttons">

  <form method="POST" action="https://www.spicepay.com/p.php?siteId='.$app_id.'&amountUSD='.$params['price'].'&orderId='.$params['order_id'].'&language=en" id="paymentForm">

  <div class="pull-right">

    <button type="submit">uhu</button>

  </div>

  </form>

</div>

<script type="text/javascript"><!--

$("#button-confirm").on("click", function() {

  $("#paymentForm").submit();

});

//--></script>
<style>
#paymentForm {
    display:none;
}
.pwait {
    top:48%;
    position: relative;
    text-align: center;
    padding-bottom: 42px;
    padding-right: 35px;
}
.loader {
  border: 16px solid #f3f3f3;
  border-radius: 50%;
  border-top: 16px solid #3498db;
  width: 120px;
  height: 120px;
  -webkit-animation: spin 2s linear infinite; /* Safari */
  animation: spin 2s linear infinite;
  position: absolute;
  z-index: 15;
  top: 50%;
  left: 50%;
  margin: -100px 0 0 -100px;
}

/* Safari */
@-webkit-keyframes spin {
  0% { -webkit-transform: rotate(0deg); }
  100% { -webkit-transform: rotate(360deg); }
}

@keyframes spin {
  0% { transform: rotate(0deg); }
  100% { transform: rotate(360deg); }
}
</style>
';

    }
}
