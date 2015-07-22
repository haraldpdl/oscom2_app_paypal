<?php
/**
  * osCommerce Online Merchant
  *
  * @copyright Copyright (c) 2015 osCommerce; http://www.oscommerce.com
  * @license GPL; http://www.oscommerce.com/gpllicense.txt
  */

namespace OSC\Apps\PayPal\PayPal\API;

class GetBalance extends \OSC\Apps\PayPal\PayPal\APIAbstract
{
    public function execute(array $extra_params = null)
    {
        $params = [
            'USER' => $this->app->getApiCredentials($this->server, 'username'),
            'PWD' => $this->app->getApiCredentials($this->server, 'password'),
            'SIGNATURE' => $this->app->getApiCredentials($this->server, 'signature'),
            'METHOD' => 'GetBalance',
            'RETURNALLCURRENCIES' => '1'
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
