<?php
/**
  * osCommerce Online Merchant
  *
  * @copyright Copyright (c) 2015 osCommerce; http://www.oscommerce.com
  * @license GPL; http://www.oscommerce.com/gpllicense.txt
  */

namespace OSC\OM\Apps\PayPal\Module\Admin\Menu;

use OSC\OM\OSCOM;

if ( !class_exists('OSCOM_PayPal', false) ) {
    include(DIR_FS_CATALOG . 'includes/apps/PayPal/OSCOM_PayPal.php');
}

class PayPal implements \OSC\OM\ModuleAdminMenuInterface
{
    public static function execute()
    {
        global $cl_box_groups, $OSCOM_PayPal;

        if ( !isset($OSCOM_PayPal) || !is_object($OSCOM_PayPal) || (isset($OSCOM_PayPal) && (get_class($OSCOM_PayPal) != 'OSCOM_PayPal')) ) {
            $OSCOM_PayPal = new \OSCOM_PayPal();
        }

        $OSCOM_PayPal->loadLanguageFile('admin/modules/boxes/paypal.php');

        $paypal_menu = [
            [
                'code' => 'App/PayPal',
                'title' => $OSCOM_PayPal->getDef('module_admin_menu_start'),
                'link' => OSCOM::link('admin/apps.php', 'PayPal')
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
                        'title' => MODULES_ADMIN_MENU_PAYPAL_BALANCE,
                        'link' => OSCOM::link('admin/apps.php', 'PayPal&action=balance')
                    ],
                    [
                        'code' => 'App/PayPal',
                        'title' => MODULES_ADMIN_MENU_PAYPAL_CONFIGURE,
                        'link' => OSCOM::link('admin/apps.php', 'PayPal&action=configure')
                    ],
                    [
                        'code' => 'App/PayPal',
                        'title' => MODULES_ADMIN_MENU_PAYPAL_MANAGE_CREDENTIALS,
                        'link' => OSCOM::link('admin/apps.php', 'PayPal&action=credentials')
                    ],
                    [
                        'code' => 'App/PayPal',
                        'title' => MODULES_ADMIN_MENU_PAYPAL_LOG,
                        'link' => OSCOM::link('admin/apps.php', 'PayPal&action=log')
                    ]
                ];

                break;
            }
        }

        $cl_box_groups[] = array('heading' => $OSCOM_PayPal->getDef('module_admin_menu_title'),
                                 'apps' => $paypal_menu);
    }
}
