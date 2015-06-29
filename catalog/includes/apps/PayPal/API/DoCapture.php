<?php
/**
  * osCommerce Online Merchant
  *
  * @copyright Copyright (c) 2015 osCommerce; http://www.oscommerce.com
  * @license GPL; http://www.oscommerce.com/gpllicense.txt
  */

namespace OSC\OM\Apps\PayPal\API;

class DoCapture extends \OSC\OM\Apps\PayPal\APIAbstract
{
    public function execute(array $extra_params = null)
    {
        $params = [
            'USER' => $this->app->getApiCredentials($this->server, 'username'),
            'PWD' => $this->app->getApiCredentials($this->server, 'password'),
            'SIGNATURE' => $this->app->getApiCredentials($this->server, 'signature'),
            'METHOD' => 'DoCapture'
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
