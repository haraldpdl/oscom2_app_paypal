<?php
/**
  * osCommerce Online Merchant
  *
  * @copyright Copyright (c) 2015 osCommerce; http://www.oscommerce.com
  * @license GPL; http://www.oscommerce.com/gpllicense.txt
  */

namespace OSC\OM\Apps\PayPal\Sites\Admin\Pages\Home\Actions;

use OSC\OM\Registry;

class Credentials extends \OSC\OM\PagesActionsAbstract
{
    public function execute()
    {
        $OSCOM_Db = Registry::get('Db');
        $OSCOM_PayPal = Registry::get('PayPal');

        $this->page->setFile('credentials.php');
        $this->page->data['action'] = 'Credentials';

        $OSCOM_PayPal->loadLanguageFile('admin/credentials.php');

        $modules = [
            'PP',
            'PF'
        ];

        $this->page->data['current_module'] = (isset($_GET['module']) && in_array($_GET['module'], $modules) ? $_GET['module'] : $modules[0]);

        $data = [
            'OSCOM_APP_PAYPAL_LIVE_SELLER_EMAIL',
            'OSCOM_APP_PAYPAL_LIVE_SELLER_EMAIL_PRIMARY',
            'OSCOM_APP_PAYPAL_LIVE_API_USERNAME',
            'OSCOM_APP_PAYPAL_LIVE_API_PASSWORD',
            'OSCOM_APP_PAYPAL_LIVE_API_SIGNATURE',
            'OSCOM_APP_PAYPAL_SANDBOX_SELLER_EMAIL',
            'OSCOM_APP_PAYPAL_SANDBOX_SELLER_EMAIL_PRIMARY',
            'OSCOM_APP_PAYPAL_SANDBOX_API_USERNAME',
            'OSCOM_APP_PAYPAL_SANDBOX_API_PASSWORD',
            'OSCOM_APP_PAYPAL_SANDBOX_API_SIGNATURE',
            'OSCOM_APP_PAYPAL_PF_LIVE_PARTNER',
            'OSCOM_APP_PAYPAL_PF_LIVE_VENDOR',
            'OSCOM_APP_PAYPAL_PF_LIVE_USER',
            'OSCOM_APP_PAYPAL_PF_LIVE_PASSWORD',
            'OSCOM_APP_PAYPAL_PF_SANDBOX_PARTNER',
            'OSCOM_APP_PAYPAL_PF_SANDBOX_VENDOR',
            'OSCOM_APP_PAYPAL_PF_SANDBOX_USER',
            'OSCOM_APP_PAYPAL_PF_SANDBOX_PASSWORD'
        ];

        foreach ($data as $key) {
            if (!defined($key)) {
                $OSCOM_PayPal->saveParameter($key, '');
            }
        }
    }
}
