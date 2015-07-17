<?php
/**
  * osCommerce Online Merchant
  *
  * @copyright Copyright (c) 2015 osCommerce; http://www.oscommerce.com
  * @license GPL; http://www.oscommerce.com/gpllicense.txt
  */

namespace OSC\Apps\PayPal\API;

class GrantToken extends \OSC\Apps\PayPal\APIAbstract
{
    protected $type = 'login';

    public function execute(array $extra_params = null)
    {
        $params = [
            'client_id' => (OSCOM_APP_PAYPAL_LOGIN_STATUS == '1') ? OSCOM_APP_PAYPAL_LOGIN_LIVE_CLIENT_ID : OSCOM_APP_PAYPAL_LOGIN_SANDBOX_CLIENT_ID,
            'client_secret' => (OSCOM_APP_PAYPAL_LOGIN_STATUS == '1') ? OSCOM_APP_PAYPAL_LOGIN_LIVE_SECRET : OSCOM_APP_PAYPAL_LOGIN_SANDBOX_SECRET,
            'grant_type' => 'authorization_code'
        ];

        if (!empty($extra_params)) {
            $params = array_merge($params, $extra_params);
        }

        $response = $this->getResult($params);

        return [
            'res' => $response,
            'success' => (is_array($response) && !isset($response['error'])),
            'req' => $params
        ];
    }
}
