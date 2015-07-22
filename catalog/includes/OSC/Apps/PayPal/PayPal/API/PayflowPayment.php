<?php
/**
  * osCommerce Online Merchant
  *
  * @copyright Copyright (c) 2015 osCommerce; http://www.oscommerce.com
  * @license GPL; http://www.oscommerce.com/gpllicense.txt
  */

namespace OSC\Apps\PayPal\PayPal\API;

class PayflowPayment extends \OSC\Apps\PayPal\PayPal\APIAbstract
{
    protected $type = 'payflow';

    public function execute(array $extra_params = null)
    {
        $params = [
            'USER' => $this->app->hasCredentials('DP', 'payflow_user') ? $this->app->getCredentials('DP', 'payflow_user') : $this->app->getCredentials('DP', 'payflow_vendor'),
            'VENDOR' => $this->app->getCredentials('DP', 'payflow_vendor'),
            'PARTNER' => $this->app->getCredentials('DP', 'payflow_partner'),
            'PWD' => $this->app->getCredentials('DP', 'payflow_password'),
            'TENDER' => 'C',
            'TRXTYPE' => (OSCOM_APP_PAYPAL_DP_TRANSACTION_METHOD == '1') ? 'S' : 'A',
            'CUSTIP' => tep_get_ip_address(),
            'BUTTONSOURCE' => 'OSCOM24_DP'
        ];

        if (!empty($extra_params)) {
            $params = array_merge($params, $extra_params);
        }

        $headers = [];

        if (isset($params['_headers'])) {
            $headers = $params['_headers'];

            unset($params['_headers']);
        }

        $response = $this->getResult($params, $headers);

        return [
            'res' => $response,
            'success' => ($response['RESULT'] == '0'),
            'req' => $params
        ];
    }
}
