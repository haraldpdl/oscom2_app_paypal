<?php
/**
  * osCommerce Online Merchant
  *
  * @copyright Copyright (c) 2015 osCommerce; http://www.oscommerce.com
  * @license GPL; http://www.oscommerce.com/gpllicense.txt
  */

namespace OSC\Apps\PayPal\PayPal\Sites\Admin\Pages\Home\Actions\Start;

use OSC\OM\HTTP;
use OSC\OM\Registry;

class Process extends \OSC\OM\PagesActionsAbstract
{
    public function execute()
    {
        $OSCOM_PayPal = Registry::get('PayPal');

        if (isset($_GET['type']) && in_array($_GET['type'], [
            'live',
            'sandbox'
        ])) {
            $params = [
                'return_url' => $OSCOM_PayPal->link('Start&Retrieve', 'SSL'),
                'type' => $_GET['type']
            ];

            $result_string = HTTP::getResponse([
              'url' => 'https://ssl.oscommerce.com/index.php?RPC&Website&Index&PayPalStart',
              'parameters' => $params
            ]);

            $result = [];

            if (!empty($result_string) && (substr($result_string, 0, 9) == 'rpcStatus')) {
                $raw = explode("\n", $result_string);

                foreach ($raw as $r) {
                    $key = explode('=', $r, 2);

                    if (is_array($key) && (count($key) === 2) && !empty($key[0]) && !empty($key[1])) {
                        $result[$key[0]] = $key[1];
                    }
                }

                if (isset($result['rpcStatus']) && ($result['rpcStatus'] === '1') && isset($result['merchant_id']) && (preg_match('/^[A-Za-z0-9]{32}$/', $result['merchant_id']) === 1) && isset($result['redirect_url']) && isset($result['secret'])) {
                    $OSCOM_PayPal->saveParameter('OSCOM_APP_PAYPAL_START_MERCHANT_ID', $result['merchant_id']);
                    $OSCOM_PayPal->saveParameter('OSCOM_APP_PAYPAL_START_SECRET', $result['secret']);

                    HTTP::redirect($result['redirect_url']);
                } else {
                    $OSCOM_PayPal->addAlert($OSCOM_PayPal->getDef('alert_onboarding_initialization_error'), 'error');
                }
            } else {
                $OSCOM_PayPal->addAlert($OSCOM_PayPal->getDef('alert_onboarding_connection_error'), 'error');
            }
        } else {
            $OSCOM_PayPal->addAlert($OSCOM_PayPal->getDef('alert_onboarding_account_type_error'), 'error');
        }
    }
}
