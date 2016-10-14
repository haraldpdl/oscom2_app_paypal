<?php
/**
  * osCommerce Online Merchant
  *
  * @copyright Copyright (c) 2015 osCommerce; http://www.oscommerce.com
  * @license GPL; http://www.oscommerce.com/gpllicense.txt
  */

namespace OSC\Apps\PayPal\PayPal\Sites\Admin\Pages\Home\Actions;

use OSC\OM\Registry;

class Configure extends \OSC\OM\PagesActionsAbstract
{
    public function execute()
    {
        $OSCOM_PayPal = Registry::get('PayPal');

        $this->page->setFile('configure.php');
        $this->page->data['action'] = 'Configure';

        $OSCOM_PayPal->loadDefinitionFile('admin/configure.php');

        $modules = $OSCOM_PayPal->getConfigModules();

        $default_module = 'G';

        foreach ($modules as $m) {
            if ($OSCOM_PayPal->getConfigModuleInfo($m, 'is_installed') === true ) {
                $default_module = $m;
                break;
            }
        }

        $this->page->data['current_module'] = (isset($_GET['module']) && in_array($_GET['module'], $modules)) ? $_GET['module'] : $default_module;

        if (!defined('OSCOM_APP_PAYPAL_TRANSACTIONS_ORDER_STATUS_ID')) {
            $Qcheck = $OSCOM_PayPal->db->get('orders_status', 'orders_status_id', [
                'orders_status_name' => 'PayPal [Transactions]'
            ], null, 1);

            if ($Qcheck->fetch() === false) {
                $Qstatus = $OSCOM_PayPal->db->get('orders_status', 'max(orders_status_id) as status_id');

                $status_id = $Qstatus->valueInt('status_id') + 1;

                $languages = tep_get_languages();

                foreach ($languages as $lang) {
                    $OSCOM_PayPal->db->save('orders_status', [
                        'orders_status_id' => $status_id,
                        'language_id' => $lang['id'],
                        'orders_status_name' => 'PayPal [Transactions]',
                        'public_flag' => 0,
                        'downloads_flag' => 0
                    ]);
                }
            } else {
                $status_id = $Qcheck->valueInt('orders_status_id');
            }

            $OSCOM_PayPal->saveCfgParam('OSCOM_APP_PAYPAL_TRANSACTIONS_ORDER_STATUS_ID', $status_id);
        }

        if (!defined('OSCOM_APP_PAYPAL_VERIFY_SSL')) {
            $OSCOM_PayPal->saveCfgParam('OSCOM_APP_PAYPAL_VERIFY_SSL', '1');
        }

        if (!defined('OSCOM_APP_PAYPAL_PROXY')) {
            $OSCOM_PayPal->saveCfgParam('OSCOM_APP_PAYPAL_PROXY', '');
        }

        if (!defined('OSCOM_APP_PAYPAL_GATEWAY')) {
            $OSCOM_PayPal->saveCfgParam('OSCOM_APP_PAYPAL_GATEWAY', '1');
        }

        if (!defined('OSCOM_APP_PAYPAL_LOG_TRANSACTIONS')) {
            $OSCOM_PayPal->saveCfgParam('OSCOM_APP_PAYPAL_LOG_TRANSACTIONS', '1');
        }
    }
}
