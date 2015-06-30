<?php
/**
  * osCommerce Online Merchant
  *
  * @copyright Copyright (c) 2015 osCommerce; http://www.oscommerce.com
  * @license GPL; http://www.oscommerce.com/gpllicense.txt
  */

namespace OSC\OM\Apps\PayPal\Module\Admin\Menu;

use OSC\OM\OSCOM;
use OSC\OM\Registry;

use OSC\OM\Apps\PayPal\PayPal as PayPalApp;

class PayPal implements \OSC\OM\Modules\AdminMenuInterface
{
    public static function execute()
    {
        global $cl_box_groups;

        if (!Registry::exists('PayPal')) {
            Registry::set('PayPal', new PayPalApp());
        }

        $OSCOM_PayPal = Registry::get('PayPal');

        $OSCOM_PayPal->loadLanguageFile('admin/modules/boxes/paypal.php');

        $paypal_menu = [
            [
                'code' => 'App/PayPal',
                'title' => $OSCOM_PayPal->getDef('module_admin_menu_start'),
                'link' => OSCOM::link('apps.php', 'PayPal')
            ]
        ];

        $paypal_menu_check = [
            'OSCOM_APP_PAYPAL_LIVE_SELLER_EMAIL',
            'OSCOM_APP_PAYPAL_LIVE_API_USERNAME',
            'OSCOM_APP_PAYPAL_SANDBOX_SELLER_EMAIL',
            'OSCOM_APP_PAYPAL_SANDBOX_API_USERNAME',
            'OSCOM_APP_PAYPAL_PF_LIVE_VENDOR',
            'OSCOM_APP_PAYPAL_PF_SANDBOX_VENDOR'
        ];

        foreach ($paypal_menu_check as $value) {
            if (defined($value) && !empty(constant($value))) {
                $paypal_menu = [
                    [
                        'code' => 'App/PayPal',
                        'title' => $OSCOM_PayPal->getDef('module_admin_menu_balance'),
                        'link' => OSCOM::link('apps.php', 'PayPal&action=balance')
                    ],
                    [
                        'code' => 'App/PayPal',
                        'title' => $OSCOM_PayPal->getDef('module_admin_menu_configure'),
                        'link' => OSCOM::link('apps.php', 'PayPal&action=configure')
                    ],
                    [
                        'code' => 'App/PayPal',
                        'title' => $OSCOM_PayPal->getDef('module_admin_menu_manage_credentials'),
                        'link' => OSCOM::link('apps.php', 'PayPal&action=credentials')
                    ],
                    [
                        'code' => 'App/PayPal',
                        'title' => $OSCOM_PayPal->getDef('module_admin_menu_log'),
                        'link' => OSCOM::link('apps.php', 'PayPal&action=log')
                    ]
                ];

                break;
            }
        }

        $cl_box_groups[] = array('heading' => $OSCOM_PayPal->getDef('module_admin_menu_title'),
                                 'apps' => $paypal_menu);
    }
}
