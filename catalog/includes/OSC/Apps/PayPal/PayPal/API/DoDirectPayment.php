<?php
/**
  * osCommerce Online Merchant
  *
  * @copyright Copyright (c) 2015 osCommerce; http://www.oscommerce.com
  * @license GPL; http://www.oscommerce.com/gpllicense.txt
  */

namespace OSC\Apps\PayPal\API;

class DoDirectPayment extends \OSC\Apps\PayPal\APIAbstract
{
    public function execute(array $extra_params = null)
    {
        $params = [
          'USER' => $this->app->getCredentials('DP', 'username'),
          'PWD' => $this->app->getCredentials('DP', 'password'),
          'SIGNATURE' => $this->app->getCredentials('DP', 'signature'),
          'METHOD' => 'DoDirectPayment',
          'PAYMENTACTION' => (OSCOM_APP_PAYPAL_DP_TRANSACTION_METHOD == '1') ? 'Sale' : 'Authorization',
          'IPADDRESS' => tep_get_ip_address(),
          'BUTTONSOURCE' => 'OSCOM24_DP'
        ];

        if (!empty($extra_params)) {
            $params = array_merge($params, $extra_params);
        }

        $response = $this->getResult($params);

        return [
            'res' => $response,
            'success' => in_array($response['ACK'], ['Success', 'SuccessWithWarning']),
            'req' => $params
        ];
    }
}
