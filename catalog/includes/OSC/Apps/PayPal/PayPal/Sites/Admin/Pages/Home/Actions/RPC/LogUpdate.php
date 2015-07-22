<?php
/**
  * osCommerce Online Merchant
  *
  * @copyright Copyright (c) 2015 osCommerce; http://www.oscommerce.com
  * @license GPL; http://www.oscommerce.com/gpllicense.txt
  */

namespace OSC\Apps\PayPal\Sites\Admin\Pages\Home\Actions\RPC;

use OSC\OM\OSCOM;
use OSC\OM\Registry;

class LogUpdate extends \OSC\OM\PagesActionsAbstract
{
    public function execute()
    {
        $OSCOM_PayPal = Registry::get('PayPal');

        $result = [
            'rpcStatus' => -1
        ];

        if (isset($_GET['v']) && is_numeric($_GET['v']) && file_exists(OSCOM::BASE_DIR . 'OSC/Apps/PayPal/work/update_log-' . basename($_GET['v']) . '.php')) {
            $result['rpcStatus'] = 1;
            $result['log'] = file_get_contents(OSCOM::BASE_DIR . 'OSC/Apps/PayPal/work/update_log-' . basename($_GET['v']) . '.php');
        }

        echo json_encode($result);
    }
}
