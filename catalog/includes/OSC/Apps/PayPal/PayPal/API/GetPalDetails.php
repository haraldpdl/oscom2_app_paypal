<?php
/**
  * osCommerce Online Merchant
  *
  * @copyright Copyright (c) 2015 osCommerce; http://www.oscommerce.com
  * @license GPL; http://www.oscommerce.com/gpllicense.txt
  */

namespace OSC\Apps\PayPal\API;

class GetPalDetails extends \OSC\Apps\PayPal\APIAbstract
{
    public function execute(array $extra_params = null)
    {
        $params = [
            'METHOD' => 'GetPalDetails',
            'USER' => $this->app->getCredentials('EC', 'username'),
            'PWD' => $this->app->getCredentials('EC', 'password'),
            'SIGNATURE' => $this->app->getCredentials('EC', 'signature')
        ];

        if (!empty($extra_params)) {
            $params = array_merge($params, $extra_params);
        }

        $response = $this->getResult($params);

        return [
            'res' => $response,
            'success' => isset($response['PAL']),
            'req' => $params
        ];
    }
}
