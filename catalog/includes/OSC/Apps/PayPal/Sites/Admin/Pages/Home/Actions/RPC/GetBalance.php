<?php
/**
  * osCommerce Online Merchant
  *
  * @copyright Copyright (c) 2015 osCommerce; http://www.oscommerce.com
  * @license GPL; http://www.oscommerce.com/gpllicense.txt
  */

namespace OSC\OM\Apps\PayPal\Sites\Admin\Pages\Home\Actions\RPC;

use OSC\OM\Registry;

class GetBalance extends \OSC\OM\PagesActionsAbstract
{
    public function execute()
    {
        $OSCOM_PayPal = Registry::get('PayPal');

        include(DIR_FS_ADMIN . 'includes/classes/currencies.php');
        $currencies = new \currencies();

        $result = [
            'rpcStatus' => -1
        ];

        if (isset($_GET['type']) && in_array($_GET['type'], [
            'live',
            'sandbox'
        ])) {
            $response = $OSCOM_PayPal->getApiResult('APP', 'GetBalance', null, $_GET['type']);

            if (is_array($response) && isset($response['ACK']) && ($response['ACK'] == 'Success')) {
                $result['rpcStatus'] = 1;

                $counter = 0;

                while (true) {
                    if (isset($response['L_AMT' . $counter]) && isset($response['L_CURRENCYCODE' . $counter])) {
                        $balance = $response['L_AMT' . $counter];

                        if (isset($currencies->currencies[$response['L_CURRENCYCODE' . $counter]])) {
                            $balance = $currencies->format($balance, false, $response['L_CURRENCYCODE' . $counter]);
                        }

                        $result['balance'][$response['L_CURRENCYCODE' . $counter]] = $balance;

                        $counter++;
                    } else {
                        break;
                    }
                }
            }
        }

        echo json_encode($result);
    }
}
