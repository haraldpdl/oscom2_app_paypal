<?php
/**
  * osCommerce Online Merchant
  *
  * @copyright Copyright (c) 2015 osCommerce; http://www.oscommerce.com
  * @license GPL; http://www.oscommerce.com/gpllicense.txt
  */

namespace OSC\OM\Apps\PayPal\API;

use OSC\OM\OSCOM;

class SetExpressCheckout extends \OSC\OM\Apps\PayPal\APIAbstract
{
    public function execute(array $extra_params = null)
    {
        $params = [
            'METHOD' => 'SetExpressCheckout',
            'PAYMENTREQUEST_0_PAYMENTACTION' => ((OSCOM_APP_PAYPAL_EC_TRANSACTION_METHOD == '1') || !$this->app->hasCredentials('EC') ? 'Sale' : 'Authorization'),
            'RETURNURL' => OSCOM::link('public/apps/PayPal/Module/Payment/EC.php', 'osC_Action=retrieve', 'SSL'),
            'CANCELURL' => OSCOM::link('public/apps/PayPal/Module/Payment/EC.php', 'osC_Action=cancel', 'SSL'),
            'BRANDNAME' => STORE_NAME,
            'SOLUTIONTYPE' => (OSCOM_APP_PAYPAL_EC_ACCOUNT_OPTIONAL == '1') ? 'Sole' : 'Mark'
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
