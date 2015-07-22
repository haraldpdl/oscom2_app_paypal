<?php
/**
  * osCommerce Online Merchant
  *
  * @copyright Copyright (c) 2015 osCommerce; http://www.oscommerce.com
  * @license GPL; http://www.oscommerce.com/gpllicense.txt
  */

namespace OSC\Apps\PayPal\PayPal\Sites\Admin\Pages\Home\Actions\RPC;

use OSC\OM\HTTP;
use OSC\OM\OSCOM;
use OSC\OM\Registry;

class DownloadUpdate extends \OSC\OM\PagesActionsAbstract
{
    public function execute()
    {
        $OSCOM_PayPal = Registry::get('PayPal');

        $OSCOM_PayPal->loadLanguageFile('admin/update.php');

        $result = [
            'rpcStatus' => -1
        ];

        if (isset($_GET['v']) && is_numeric($_GET['v']) && ($_GET['v'] > $OSCOM_PayPal->getVersion())) {
            if ($OSCOM_PayPal->isWritable(OSCOM::BASE_DIR . 'OSC/Apps/PayPal/PayPal/work')) {
                if (!file_exists(OSCOM::BASE_DIR . 'OSC/Apps/PayPal/PayPal/work')) {
                    mkdir(OSCOM::BASE_DIR . 'OSC/Apps/PayPal/PayPal/work', 0777, true);
                }

                $filepath = OSCOM::BASE_DIR . 'OSC/Apps/PayPal/PayPal/work/update.zip';

                if (file_exists($filepath) && is_writable($filepath)) {
                    unlink($filepath);
                }

                $downloadFile = HTTP::getResponse([
                    'url' => 'http://apps.oscommerce.com/index.php?Download&paypal&app&2_400&' . str_replace('.', '_', $_GET['v']) . '&update'
                ]);

                $save_result = @file_put_contents($filepath, $downloadFile);

                if (($save_result !== false) && ($save_result > 0)) {
                    $result['rpcStatus'] = 1;
                } else {
                    $result['error'] = $OSCOM_PayPal->getDef('error_saving_download', [
                        'filepath' => $OSCOM_PayPal->displayPath($filepath)
                    ]);
                }
            } else {
                $result['error'] = $OSCOM_PayPal->getDef('error_download_directory_permissions', [
                    'filepath' => $OSCOM_PayPal->displayPath(OSCOM::BASE_DIR . 'OSC/Apps/PayPal/PayPal/work')
                ]);
            }
        }

        echo json_encode($result);
    }
}
