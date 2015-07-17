<?php
/**
  * osCommerce Online Merchant
  *
  * @copyright Copyright (c) 2015 osCommerce; http://www.oscommerce.com
  * @license GPL; http://www.oscommerce.com/gpllicense.txt
  */

namespace OSC\Apps\PayPal\API;

class GetExpressCheckoutDetails extends \OSC\Apps\PayPal\APIAbstract
{
    public function execute(array $extra_params = null)
    {
        $params = [
            'METHOD' => 'GetExpressCheckoutDetails'
        ];

        if ($this->app->hasCredentials('EC')) {
            $params['USER'] = $this->app->getCredentials('EC', 'username');
            $params['PWD'] = $this->app->getCredentials('EC', 'password');
            $params['SIGNATURE'] = $this->app->getCredentials('EC', 'signature');
        } else {
            $params['SUBJECT'] = $this->app->getCredentials('EC', 'email');
        }

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
