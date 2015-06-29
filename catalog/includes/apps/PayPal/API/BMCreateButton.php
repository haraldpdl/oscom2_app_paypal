<?php
/**
  * osCommerce Online Merchant
  *
  * @copyright Copyright (c) 2015 osCommerce; http://www.oscommerce.com
  * @license GPL; http://www.oscommerce.com/gpllicense.txt
  */

namespace OSC\OM\Apps\PayPal\API;

class BMCreateButton extends \OSC\OM\Apps\PayPal\APIAbstract
{
    public function execute(array $extra_params = null)
    {
        $params = [
            'USER' => $this->app->getCredentials('HS', 'username'),
            'PWD' => $this->app->getCredentials('HS', 'password'),
            'SIGNATURE' => $this->app->getCredentials('HS', 'signature'),
            'METHOD' => 'BMCreateButton',
            'BUTTONCODE' => 'TOKEN',
            'BUTTONTYPE' => 'PAYMENT'
        ];

        $l_params = [
            'business' => $this->app->getCredentials('HS', 'email'),
            'bn' => 'OSCOM24_HS'
        ];

        if (!empty($extra_params)) {
            $l_params = array_merge($l_params, $extra_params);
        }

        $counter = 0;

        foreach ($l_params as $key => $value) {
            $params['L_BUTTONVAR' . $counter] = $key . '=' . $value;

            $counter++;
        }

        $response = $this->getResult($params);

        return [
            'res' => $response,
            'success' => in_array($response['ACK'], ['Success', 'SuccessWithWarning']),
            'req' => $params
        ];
    }
}
