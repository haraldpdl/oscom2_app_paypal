<?php
/**
  * osCommerce Online Merchant
  *
  * @copyright Copyright (c) 2015 osCommerce; http://www.oscommerce.com
  * @license GPL; http://www.oscommerce.com/gpllicense.txt
  */

namespace OSC\Apps\PayPal\Module\Admin\Config\HS;

use OSC\OM\OSCOM;

class HS extends \OSC\Apps\PayPal\Module\Admin\Config\ConfigAbstract
{
    protected $pm_code = 'paypal_pro_hs';

    public $is_uninstallable = true;
    public $is_migratable = true;
    public $sort_order = 300;

    protected function init()
    {
        $this->title = $this->app->getDef('module_hs_title');
        $this->short_title = $this->app->getDef('module_hs_short_title');
        $this->introduction = $this->app->getDef('module_hs_introduction');

        $this->is_installed = defined('OSCOM_APP_PAYPAL_HS_STATUS') && (trim(OSCOM_APP_PAYPAL_HS_STATUS) != '');

        if (!function_exists('curl_init')) {
            $this->req_notes[] = $this->app->getDef('module_hs_error_curl');
        }

        if (defined('OSCOM_APP_PAYPAL_GATEWAY')) {
            if ((OSCOM_APP_PAYPAL_GATEWAY == '1') && !$this->app->hasCredentials('HS')) { // PayPal
                $this->req_notes[] = $this->app->getDef('module_hs_error_credentials');
            } elseif (OSCOM_APP_PAYPAL_GATEWAY == '0') { // Payflow
                $this->req_notes[] = $this->app->getDef('module_hs_error_payflow');
            }
        }
    }

    public function install()
    {
        parent::install();

        $installed = explode(';', MODULE_PAYMENT_INSTALLED);
        $installed[] = $this->app->code . '\\' . $this->code;

        $this->app->saveParameter('MODULE_PAYMENT_INSTALLED', implode(';', $installed));
    }

    public function uninstall()
    {
        parent::uninstall();

        $installed = explode(';', MODULE_PAYMENT_INSTALLED);
        $installed_pos = array_search($this->app->code . '\\' . $this->code, $installed);

        if ($installed_pos !== false) {
            unset($installed[$installed_pos]);

            $this->app->saveParameter('MODULE_PAYMENT_INSTALLED', implode(';', $installed));
        }
    }

    public function canMigrate()
    {
        if (file_exists(OSCOM::BASE_DIR . 'modules/payment/' . $this->pm_code . '.php')) {
            if (!class_exists($this->pm_code)) {
                include(OSCOM::BASE_DIR . 'modules/payment/' . $this->pm_code . '.php');
            }

            $module = new $this->pm_code();

            if (isset($module->signature)) {
                $sig = explode('|', $module->signature);

                if (isset($sig[0]) && ($sig[0] == 'paypal') && isset($sig[1]) && ($sig[1] == $this->pm_code) && isset($sig[2])) {
                    return version_compare($sig[2], 4) >= 0;
                }
            }
        }

        return false;
    }

    public function migrate()
    {
        if (defined('MODULE_PAYMENT_PAYPAL_PRO_HS_GATEWAY_SERVER')) {
            $server = (MODULE_PAYMENT_PAYPAL_PRO_HS_GATEWAY_SERVER == 'Live') ? 'LIVE' : 'SANDBOX';

            if (defined('MODULE_PAYMENT_PAYPAL_PRO_HS_ID')) {
                if (tep_not_null(MODULE_PAYMENT_PAYPAL_PRO_HS_ID)) {
                    if (!defined('OSCOM_APP_PAYPAL_' . $server . '_SELLER_EMAIL') || !tep_not_null(constant('OSCOM_APP_PAYPAL_' . $server . '_SELLER_EMAIL'))) {
                        $this->app->saveParameter('OSCOM_APP_PAYPAL_' . $server . '_SELLER_EMAIL', MODULE_PAYMENT_PAYPAL_PRO_HS_ID);
                    }
                }

                $this->app->deleteParameter('MODULE_PAYMENT_PAYPAL_PRO_HS_ID');
            }

            if (defined('MODULE_PAYMENT_PAYPAL_PRO_HS_PRIMARY_ID')) {
                if (tep_not_null(MODULE_PAYMENT_PAYPAL_PRO_HS_PRIMARY_ID)) {
                    if (!defined('OSCOM_APP_PAYPAL_' . $server . '_SELLER_EMAIL_PRIMARY') || !tep_not_null(constant('OSCOM_APP_PAYPAL_' . $server . '_SELLER_EMAIL_PRIMARY'))) {
                        $this->app->saveParameter('OSCOM_APP_PAYPAL_' . $server . '_SELLER_EMAIL_PRIMARY', MODULE_PAYMENT_PAYPAL_PRO_HS_PRIMARY_ID);
                    }
                }

                $this->app->deleteParameter('MODULE_PAYMENT_PAYPAL_PRO_HS_PRIMARY_ID');
            }

            if (defined('MODULE_PAYMENT_PAYPAL_PRO_HS_API_USERNAME') && defined('MODULE_PAYMENT_PAYPAL_PRO_HS_API_PASSWORD') && defined('MODULE_PAYMENT_PAYPAL_PRO_HS_API_SIGNATURE')) {
                if (tep_not_null(MODULE_PAYMENT_PAYPAL_PRO_HS_API_USERNAME) && tep_not_null(MODULE_PAYMENT_PAYPAL_PRO_HS_API_PASSWORD) && tep_not_null(MODULE_PAYMENT_PAYPAL_PRO_HS_API_SIGNATURE)) {
                    if (!defined('OSCOM_APP_PAYPAL_' . $server . '_API_USERNAME') || !tep_not_null(constant('OSCOM_APP_PAYPAL_' . $server . '_API_USERNAME'))) {
                        if (!defined('OSCOM_APP_PAYPAL_' . $server . '_API_PASSWORD') || !tep_not_null(constant('OSCOM_APP_PAYPAL_' . $server . '_API_PASSWORD'))) {
                            if (!defined('OSCOM_APP_PAYPAL_' . $server . '_API_SIGNATURE') || !tep_not_null(constant('OSCOM_APP_PAYPAL_' . $server . '_API_SIGNATURE'))) {
                                $this->app->saveParameter('OSCOM_APP_PAYPAL_' . $server . '_API_USERNAME', MODULE_PAYMENT_PAYPAL_PRO_HS_API_USERNAME);
                                $this->app->saveParameter('OSCOM_APP_PAYPAL_' . $server . '_API_PASSWORD', MODULE_PAYMENT_PAYPAL_PRO_HS_API_PASSWORD);
                                $this->app->saveParameter('OSCOM_APP_PAYPAL_' . $server . '_API_SIGNATURE', MODULE_PAYMENT_PAYPAL_PRO_HS_API_SIGNATURE);
                            }
                        }
                    }
                }

                $this->app->deleteParameter('MODULE_PAYMENT_PAYPAL_PRO_HS_API_USERNAME');
                $this->app->deleteParameter('MODULE_PAYMENT_PAYPAL_PRO_HS_API_PASSWORD');
                $this->app->deleteParameter('MODULE_PAYMENT_PAYPAL_PRO_HS_API_SIGNATURE');
            }
        }

        if (defined('MODULE_PAYMENT_PAYPAL_PRO_HS_TRANSACTION_METHOD')) {
            $this->app->saveParameter('OSCOM_APP_PAYPAL_HS_TRANSACTION_METHOD', (MODULE_PAYMENT_PAYPAL_PRO_HS_TRANSACTION_METHOD == 'Sale') ? '1' : '0');
            $this->app->deleteParameter('MODULE_PAYMENT_PAYPAL_PRO_HS_TRANSACTION_METHOD');
        }

        if (defined('MODULE_PAYMENT_PAYPAL_PRO_HS_PREPARE_ORDER_STATUS_ID')) {
            $this->app->saveParameter('OSCOM_APP_PAYPAL_HS_PREPARE_ORDER_STATUS_ID', MODULE_PAYMENT_PAYPAL_PRO_HS_PREPARE_ORDER_STATUS_ID);
            $this->app->deleteParameter('MODULE_PAYMENT_PAYPAL_PRO_HS_PREPARE_ORDER_STATUS_ID');
        }

        if (defined('MODULE_PAYMENT_PAYPAL_PRO_HS_ORDER_STATUS_ID')) {
            $this->app->saveParameter('OSCOM_APP_PAYPAL_HS_ORDER_STATUS_ID', MODULE_PAYMENT_PAYPAL_PRO_HS_ORDER_STATUS_ID);
            $this->app->deleteParameter('MODULE_PAYMENT_PAYPAL_PRO_HS_ORDER_STATUS_ID');
        }

        if (defined('MODULE_PAYMENT_PAYPAL_PRO_HS_ZONE')) {
            $this->app->saveParameter('OSCOM_APP_PAYPAL_HS_ZONE', MODULE_PAYMENT_PAYPAL_PRO_HS_ZONE);
            $this->app->deleteParameter('MODULE_PAYMENT_PAYPAL_PRO_HS_ZONE');
        }

        if (defined('MODULE_PAYMENT_PAYPAL_PRO_HS_SORT_ORDER')) {
            $this->app->saveParameter('OSCOM_APP_PAYPAL_HS_SORT_ORDER', MODULE_PAYMENT_PAYPAL_PRO_HS_SORT_ORDER, 'Sort Order', 'Sort order of display (lowest to highest).');
            $this->app->deleteParameter('MODULE_PAYMENT_PAYPAL_PRO_HS_SORT_ORDER');
        }

        if (defined('MODULE_PAYMENT_PAYPAL_PRO_HS_TRANSACTIONS_ORDER_STATUS_ID')) {
            $this->app->deleteParameter('MODULE_PAYMENT_PAYPAL_PRO_HS_TRANSACTIONS_ORDER_STATUS_ID');
        }

        if (defined('MODULE_PAYMENT_PAYPAL_PRO_HS_STATUS')) {
            $status = '-1';

            if ((MODULE_PAYMENT_PAYPAL_PRO_HS_STATUS == 'True') && defined('MODULE_PAYMENT_PAYPAL_PRO_HS_GATEWAY_SERVER')) {
                if (MODULE_PAYMENT_PAYPAL_PRO_HS_GATEWAY_SERVER == 'Live') {
                    $status = '1';
                } else {
                    $status = '0';
                }
            }

            $this->app->saveParameter('OSCOM_APP_PAYPAL_HS_STATUS', $status);
            $this->app->deleteParameter('MODULE_PAYMENT_PAYPAL_PRO_HS_STATUS');
        }

        if (defined('MODULE_PAYMENT_PAYPAL_PRO_HS_GATEWAY_SERVER')) {
            $this->app->deleteParameter('MODULE_PAYMENT_PAYPAL_PRO_HS_GATEWAY_SERVER');
        }

        if (defined('MODULE_PAYMENT_PAYPAL_PRO_HS_VERIFY_SSL')) {
            if (!defined('OSCOM_APP_PAYPAL_VERIFY_SSL')) {
                $this->app->saveParameter('OSCOM_APP_PAYPAL_VERIFY_SSL', (MODULE_PAYMENT_PAYPAL_PRO_HS_VERIFY_SSL == 'True') ? '1' : '0');
            }

            $this->app->deleteParameter('MODULE_PAYMENT_PAYPAL_PRO_HS_VERIFY_SSL');
        }

        if (defined('MODULE_PAYMENT_PAYPAL_PRO_HS_PROXY')) {
            if (!defined('OSCOM_APP_PAYPAL_PROXY')) {
                $this->app->saveParameter('OSCOM_APP_PAYPAL_PROXY', MODULE_PAYMENT_PAYPAL_PRO_HS_PROXY);
            }

            $this->app->deleteParameter('MODULE_PAYMENT_PAYPAL_PRO_HS_PROXY');
        }

        if (defined('MODULE_PAYMENT_PAYPAL_PRO_HS_DEBUG_EMAIL')) {
            $this->app->deleteParameter('MODULE_PAYMENT_PAYPAL_PRO_HS_DEBUG_EMAIL');
        }
    }
}
