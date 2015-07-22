<?php
/**
  * osCommerce Online Merchant
  *
  * @copyright Copyright (c) 2015 osCommerce; http://www.oscommerce.com
  * @license GPL; http://www.oscommerce.com/gpllicense.txt
  */

namespace OSC\Apps\PayPal\PayPal\Sites\Admin\Pages\Home\Actions\RPC;

use OSC\OM\HTTP;
use OSC\OM\Registry;

class CheckVersion extends \OSC\OM\PagesActionsAbstract
{
    public function execute()
    {
        $OSCOM_PayPal = Registry::get('PayPal');

        if (class_exists('ZipArchive') && function_exists('openssl_verify')) {
            $result = [
                'rpcStatus' => -1
            ];

            $response = @json_decode(HTTP::getResponse([
                'url' => 'http://apps.oscommerce.com/index.php?RPC&GetUpdates&paypal&app&2_4&' . str_replace('.', '_', number_format($OSCOM_PayPal->getVersion(), 3))
            ]), true);

            if (is_array($response) && isset($response['rpcStatus']) && ($response['rpcStatus'] === 1)) {
                $result['rpcStatus'] = 1;

                if (isset($response['app']['releases'])) {
                    $ppMaxVersion = 0;

                    foreach ($response['app']['releases'] as $ppUpdateRelease) {
                        if (is_numeric($ppUpdateRelease['version'])) {
                            $result['releases'][] = $ppUpdateRelease;

                            if ($ppUpdateRelease['version'] > $ppMaxVersion) {
                                $ppMaxVersion = $ppUpdateRelease['version'];
                            }
                        }
                    }
                }
            }

            echo json_encode($result);
        } else {
            $result = 'rpcStatus=-1';

            $response = HTTP::getResponse([
                'url' => 'http://apps.oscommerce.com/index.php?RPC&GetUpdates&paypal&app&2_4&' . str_replace('.', '_', number_format($OSCOM_PayPal->getVersion(), 3)) . '&format=simple'
            ]);

            if (!empty($response) && (strpos($response, 'rpcStatus') !== false)) {
                parse_str($response, $ppUpdateRelease);

                if (isset($ppUpdateRelease['rpcStatus']) && ($ppUpdateRelease['rpcStatus'] == '1')) {
                    $result = 'rpcStatus=1' . "\n";

                    if (isset($ppUpdateRelease['version']) && is_numeric($ppUpdateRelease['version'])) {
                        $result .= 'release=' . $ppUpdateRelease['version'];

                        $ppMaxVersion = $ppUpdateRelease['version'];
                    }
                }
            }

            echo $result;
        }

        $OSCOM_PayPal->saveParameter('OSCOM_APP_PAYPAL_VERSION_CHECK', date('j') . (isset($ppMaxVersion) && ($ppMaxVersion > 0) ? '-' . $ppMaxVersion : ''));
    }
}
