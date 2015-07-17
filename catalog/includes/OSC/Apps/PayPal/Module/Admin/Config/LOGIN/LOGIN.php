<?php
/**
  * osCommerce Online Merchant
  *
  * @copyright Copyright (c) 2015 osCommerce; http://www.oscommerce.com
  * @license GPL; http://www.oscommerce.com/gpllicense.txt
  */

namespace OSC\OM\Apps\PayPal\Module\Admin\Config\LOGIN;

use OSC\OM\OSCOM;

class LOGIN extends \OSC\OM\Apps\PayPal\Module\Admin\Config\ConfigAbstract
{
    protected $_cm_code = 'login/cm_paypal_login';

    public $is_uninstallable = true;
    public $is_migratable = true;
    public $sort_order = 1000;

    protected function init()
    {
        $this->title = $this->app->getDef('module_login_title');
        $this->short_title = $this->app->getDef('module_login_short_title');
        $this->introduction = $this->app->getDef('module_login_introduction');

        $this->is_installed = defined('OSCOM_APP_PAYPAL_LOGIN_STATUS') && (trim(OSCOM_APP_PAYPAL_LOGIN_STATUS) != '');

        if (!function_exists('curl_init')) {
            $this->req_notes[] = $this->app->getDef('module_login_error_curl');
        }

        if (defined('OSCOM_APP_PAYPAL_LOGIN_STATUS')) {
            if (((OSCOM_APP_PAYPAL_LOGIN_STATUS == '1') && (!tep_not_null(OSCOM_APP_PAYPAL_LOGIN_LIVE_CLIENT_ID) || !tep_not_null(OSCOM_APP_PAYPAL_LOGIN_LIVE_SECRET))) || ((OSCOM_APP_PAYPAL_LOGIN_STATUS == '0') && (!tep_not_null(OSCOM_APP_PAYPAL_LOGIN_SANDBOX_CLIENT_ID) || !tep_not_null(OSCOM_APP_PAYPAL_LOGIN_SANDBOX_SECRET)))) {
                $this->req_notes[] = $this->app->getDef('module_login_error_credentials');
            }
        }
    }

    public function install()
    {
        parent::install();

        $installed = explode(';', MODULE_CONTENT_INSTALLED);
        $installed[] = 'login/' . $this->app->code . '\\' . $this->code;

        $this->app->saveParameter('MODULE_CONTENT_INSTALLED', implode(';', $installed));
    }

    public function uninstall()
    {
        parent::uninstall();

        $installed = explode(';', MODULE_CONTENT_INSTALLED);
        $installed_pos = array_search('login/' . $this->app->code . '\\' . $this->code, $installed);

        if ($installed_pos !== false) {
            unset($installed[$installed_pos]);

            $this->app->saveParameter('MODULE_CONTENT_INSTALLED', implode(';', $installed));
        }
    }

    public function canMigrate()
    {
        $class = basename($this->_cm_code);

        if (file_exists(OSCOM::BASE_DIR . 'modules/content/' . $this->_cm_code . '.php')) {
            if (!class_exists($class)) {
                include(OSCOM::BASE_DIR . 'modules/content/' . $this->_cm_code . '.php');
            }

            $module = new $class();

            if (isset($module->signature)) {
                $sig = explode('|', $module->signature);

                if (isset($sig[0]) && ($sig[0] == 'paypal') && isset($sig[1]) && ($sig[1] == 'paypal_login') && isset($sig[2])) {
                    return version_compare($sig[2], 4) >= 0;
                }
            }
        }

        return false;
    }

    public function migrate()
    {
        if (defined('MODULE_CONTENT_PAYPAL_LOGIN_SERVER_TYPE')) {
            $server = (MODULE_CONTENT_PAYPAL_LOGIN_SERVER_TYPE == 'Live') ? 'LIVE' : 'SANDBOX';

            if (defined('MODULE_CONTENT_PAYPAL_LOGIN_CLIENT_ID')) {
                if (tep_not_null(MODULE_CONTENT_PAYPAL_LOGIN_CLIENT_ID)) {
                    if (!defined('OSCOM_APP_PAYPAL_LOGIN_' . $server . '_CLIENT_ID') || !tep_not_null(constant('OSCOM_APP_PAYPAL_LOGIN_' . $server . '_CLIENT_ID'))) {
                        $this->app->saveParameter('OSCOM_APP_PAYPAL_LOGIN_' . $server . '_CLIENT_ID', MODULE_CONTENT_PAYPAL_LOGIN_CLIENT_ID);
                    }
                }

                $this->app->deleteParameter('MODULE_CONTENT_PAYPAL_LOGIN_CLIENT_ID');
            }

            if (defined('MODULE_CONTENT_PAYPAL_LOGIN_SECRET')) {
                if (tep_not_null(MODULE_CONTENT_PAYPAL_LOGIN_SECRET)) {
                    if (!defined('OSCOM_APP_PAYPAL_LOGIN_' . $server . '_SECRET') || !tep_not_null(constant('OSCOM_APP_PAYPAL_LOGIN_' . $server . '_SECRET'))) {
                        $this->app->saveParameter('OSCOM_APP_PAYPAL_LOGIN_' . $server . '_SECRET', MODULE_CONTENT_PAYPAL_LOGIN_SECRET);
                    }
                }

                $this->app->deleteParameter('MODULE_CONTENT_PAYPAL_LOGIN_SECRET');
            }
        }

        if (defined('MODULE_CONTENT_PAYPAL_LOGIN_THEME')) {
            $this->app->saveParameter('OSCOM_APP_PAYPAL_LOGIN_THEME', MODULE_CONTENT_PAYPAL_LOGIN_THEME);
            $this->app->deleteParameter('MODULE_CONTENT_PAYPAL_LOGIN_THEME');
        }

        if (defined('MODULE_CONTENT_PAYPAL_LOGIN_ATTRIBUTES')) {
            $this->app->saveParameter('OSCOM_APP_PAYPAL_LOGIN_ATTRIBUTES', MODULE_CONTENT_PAYPAL_LOGIN_ATTRIBUTES);
            $this->app->deleteParameter('MODULE_CONTENT_PAYPAL_LOGIN_ATTRIBUTES');
        }

        if (defined('MODULE_CONTENT_PAYPAL_LOGIN_CONTENT_WIDTH')) {
            $this->app->saveParameter('OSCOM_APP_PAYPAL_LOGIN_CONTENT_WIDTH', MODULE_CONTENT_PAYPAL_LOGIN_CONTENT_WIDTH, 'Content Width', 'Should the content be shown in a full or half width container?', 'tep_cfg_select_option(array(\'Full\', \'Half\'), ');
            $this->app->deleteParameter('MODULE_CONTENT_PAYPAL_LOGIN_CONTENT_WIDTH');
        }

        if (defined('MODULE_CONTENT_PAYPAL_LOGIN_SORT_ORDER')) {
            $this->app->saveParameter('OSCOM_APP_PAYPAL_LOGIN_SORT_ORDER', MODULE_CONTENT_PAYPAL_LOGIN_SORT_ORDER, 'Sort Order', 'Sort order of display (lowest to highest).');
            $this->app->deleteParameter('MODULE_CONTENT_PAYPAL_LOGIN_SORT_ORDER');
        }

        if (defined('MODULE_CONTENT_PAYPAL_LOGIN_STATUS')) {
            $status = '-1';

            if ((MODULE_CONTENT_PAYPAL_LOGIN_STATUS == 'True') && defined('MODULE_CONTENT_PAYPAL_LOGIN_SERVER_TYPE')) {
                if (MODULE_CONTENT_PAYPAL_LOGIN_SERVER_TYPE == 'Live') {
                    $status = '1';
                } else {
                    $status = '0';
                }
            }

            $this->app->saveParameter('OSCOM_APP_PAYPAL_LOGIN_STATUS', $status);
            $this->app->deleteParameter('MODULE_CONTENT_PAYPAL_LOGIN_STATUS');
        }

        if (defined('MODULE_CONTENT_PAYPAL_LOGIN_SERVER_TYPE')) {
            $this->app->deleteParameter('MODULE_CONTENT_PAYPAL_LOGIN_SERVER_TYPE');
        }

        if (defined('MODULE_CONTENT_PAYPAL_LOGIN_VERIFY_SSL')) {
            if (!defined('OSCOM_APP_PAYPAL_VERIFY_SSL')) {
                $this->app->saveParameter('OSCOM_APP_PAYPAL_VERIFY_SSL', (MODULE_CONTENT_PAYPAL_LOGIN_VERIFY_SSL == 'True') ? '1' : '0');
            }

            $this->app->deleteParameter('MODULE_CONTENT_PAYPAL_LOGIN_VERIFY_SSL');
        }

        if (defined('MODULE_CONTENT_PAYPAL_LOGIN_PROXY')) {
            if (!defined('OSCOM_APP_PAYPAL_PROXY')) {
                $this->app->saveParameter('OSCOM_APP_PAYPAL_PROXY', MODULE_CONTENT_PAYPAL_LOGIN_PROXY);
            }

            $this->app->deleteParameter('MODULE_CONTENT_PAYPAL_LOGIN_PROXY');
        }
    }
}
