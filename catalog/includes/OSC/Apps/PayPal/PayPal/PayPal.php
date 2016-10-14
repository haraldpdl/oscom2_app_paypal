<?php
/**
  * osCommerce Online Merchant
  *
  * @copyright Copyright (c) 2015 osCommerce; http://www.oscommerce.com
  * @license GPL; http://www.oscommerce.com/gpllicense.txt
  */

namespace OSC\Apps\PayPal\PayPal;

use OSC\OM\FileSystem;
use OSC\OM\HTTP;
use OSC\OM\OSCOM;
use OSC\OM\Registry;

class PayPal extends \OSC\OM\AppAbstract
{
    protected $api_version = 204;

    protected function init()
    {
    }

    public function isReqApiCountrySupported($country_id)
    {
        $Qcountry = $this->db->get('countries', 'countries_iso_code_2', ['countries_id' => $country_id]);

        if ($Qcountry->fetch() !== false) {
            return in_array($Qcountry->valueInt('countries_iso_code_2'), $this->getReqApiCountries());
        }

        return false;
    }

    public function getReqApiCountries()
    {
        static $countries;

        if (!isset($countries)) {
            $countries = [];

            if (is_file(OSCOM::BASE_DIR . 'Apps/PayPal/PayPal/req_api_countries.txt')) {
                foreach (file(OSCOM::BASE_DIR . 'Apps/PayPal/PayPal/req_api_countries.txt') as $c) {
                    $c = trim($c);

                    if (!empty($c)) {
                        $countries[] = $c;
                    }
                }
            }
        }

        return $countries;
    }

    public function log($module, $action, $result, $request, $response, $server, $is_ipn = false)
    {
        $do_log = false;

        if (defined('OSCOM_APP_PAYPAL_LOG_TRANSACTIONS') && in_array(OSCOM_APP_PAYPAL_LOG_TRANSACTIONS, ['1', '0'])) {
            $do_log = true;

            if ((OSCOM_APP_PAYPAL_LOG_TRANSACTIONS == '0') && ($result === 1)) {
                $do_log = false;
            }
        }

        if ($do_log !== true) {
            return false;
        }

        $filter = ['ACCT', 'CVV2', 'ISSUENUMBER'];

        $request_string = '';

        if (is_array($request)) {
            foreach ($request as $key => $value) {
                if ((strpos($key, '_nh-dns') !== false) || in_array($key, $filter)) {
                    $value = '**********';
                }

                $request_string .= $key . ': ' . $value . "\n";
            }
        } else {
            $request_string = $request;
        }

        $response_string = '';

        if (is_array($response)) {
            foreach ($response as $key => $value) {
                if (is_array($value)) {
                    $value = http_build_query($value);
                } elseif ((strpos($key, '_nh-dns') !== false) || in_array($key, $filter)) {
                    $value = '**********';
                }

                $response_string .= $key . ': ' . $value . "\n";
            }
        } else {
            $response_string = $response;
        }

        $this->db->save('oscom_app_paypal_log', [
            'customers_id' => isset($_SESSION['customer_id']) ? $_SESSION['customer_id'] : 0,
            'module' => $module,
            'action' => $action . (($is_ipn === true) ? ' [IPN]' : ''),
            'result' => $result,
            'server' => ($server == 'live') ? 1 : -1,
            'request' => trim($request_string),
            'response' => trim($response_string),
            'ip_address' => sprintf('%u', ip2long(tep_get_ip_address())),
            'date_added' => 'now()'
        ]);
    }

    public function migrate()
    {
        $migrated = false;

        foreach ($this->getConfigModules() as $module) {
            if (!defined('OSCOM_APP_PAYPAL_' . $module . '_STATUS') && $this->getConfigModuleInfo($module, 'is_migratable')) {
                $this->saveCfgParam('OSCOM_APP_PAYPAL_' . $module . '_STATUS', '');

                $m = Registry::get('PayPalAdminConfig' . $module);

                if ($m->canMigrate()) {
                    $m->migrate();

                    if ($migrated === false) {
                        $migrated = true;
                    }
                }
            }
        }

        return $migrated;
    }

    public function getConfigModules()
    {
        static $result;

        if (!isset($result)) {
            $result = [];

            $directory = OSCOM::BASE_DIR . 'Apps/PayPal/PayPal/Module/Admin/Config';

            if ($dir = new \DirectoryIterator($directory)) {
                foreach ($dir as $file) {
                    if (!$file->isDot() && $file->isDir() && is_file($file->getPathname() . '/' . $file->getFilename() . '.php')) {
                        $class = 'OSC\Apps\PayPal\PayPal\Module\Admin\Config\\' . $file->getFilename() . '\\' . $file->getFilename();

                        if (is_subclass_of($class, 'OSC\Apps\PayPal\PayPal\Module\Admin\Config\ConfigAbstract')) {
                            $sort_order = $this->getConfigModuleInfo($file->getFilename(), 'sort_order');

                            if ($sort_order > 0) {
                                $counter = $sort_order;
                            } else {
                                $counter = count($result);
                            }

                            while (true) {
                                if (isset($result[$counter])) {
                                    $counter++;

                                    continue;
                                }

                                $result[$counter] = $file->getFilename();

                                break;
                            }
                        } else {
                            trigger_error('OSC\Apps\PayPal\PayPal\PayPal::getConfigModules(): OSC\Apps\PayPal\PayPal\Module\Admin\Config\\' . $file->getFilename() . '\\' . $file->getFilename() . ' is not a subclass of OSC\Apps\PayPal\PayPal\Module\Admin\Config\ConfigAbstract and cannot be loaded.');
                        }
                    }
                }

                ksort($result, SORT_NUMERIC);
            }
        }

        return $result;
    }

    public function getConfigModuleInfo($module, $info)
    {
        if (!Registry::exists('PayPalAdminConfig' . $module)) {
            $class = 'OSC\Apps\PayPal\PayPal\Module\Admin\Config\\' . $module . '\\' . $module;

            Registry::set('PayPalAdminConfig' . $module, new $class);
        }

        return Registry::get('PayPalAdminConfig' . $module)->$info;
    }

    function hasCredentials($module, $type = null) {
      if (!defined('OSCOM_APP_PAYPAL_' . $module . '_STATUS')) {
        return false;
      }

      $server = constant('OSCOM_APP_PAYPAL_' . $module . '_STATUS');

      if ( !in_array($server, array('1', '0')) ) {
        return false;
      }

      $server = ($server == '1') ? 'LIVE' : 'SANDBOX';

      if ( $type == 'email') {
        $creds = array('OSCOM_APP_PAYPAL_' . $server . '_SELLER_EMAIL');
      } elseif ( substr($type, 0, 7) == 'payflow' ) {
        if ( strlen($type) > 7 ) {
          $creds = array('OSCOM_APP_PAYPAL_PF_' . $server . '_' . strtoupper(substr($type, 8)));
        } else {
          $creds = array('OSCOM_APP_PAYPAL_PF_' . $server . '_VENDOR',
                         'OSCOM_APP_PAYPAL_PF_' . $server . '_PASSWORD',
                         'OSCOM_APP_PAYPAL_PF_' . $server . '_PARTNER');
        }
      } else {
        $creds = array('OSCOM_APP_PAYPAL_' . $server . '_API_USERNAME',
                       'OSCOM_APP_PAYPAL_' . $server . '_API_PASSWORD',
                       'OSCOM_APP_PAYPAL_' . $server . '_API_SIGNATURE');
      }

      foreach ( $creds as $c ) {
        if ( !defined($c) || (strlen(trim(constant($c))) < 1) ) {
          return false;
        }
      }

      return true;
    }

    function getCredentials($module, $type) {
      if ( constant('OSCOM_APP_PAYPAL_' . $module . '_STATUS') == '1' ) {
        if ( $type == 'email') {
          return constant('OSCOM_APP_PAYPAL_LIVE_SELLER_EMAIL');
        } elseif ( $type == 'email_primary') {
          return constant('OSCOM_APP_PAYPAL_LIVE_SELLER_EMAIL_PRIMARY');
        } elseif ( substr($type, 0, 7) == 'payflow' ) {
          return constant('OSCOM_APP_PAYPAL_PF_LIVE_' . strtoupper(substr($type, 8)));
        } else {
          return constant('OSCOM_APP_PAYPAL_LIVE_API_' . strtoupper($type));
        }
      }

      if ( $type == 'email') {
        return constant('OSCOM_APP_PAYPAL_SANDBOX_SELLER_EMAIL');
      } elseif ( $type == 'email_primary') {
        return constant('OSCOM_APP_PAYPAL_SANDBOX_SELLER_EMAIL_PRIMARY');
      } elseif ( substr($type, 0, 7) == 'payflow' ) {
        return constant('OSCOM_APP_PAYPAL_PF_SANDBOX_' . strtoupper(substr($type, 8)));
      } else {
        return constant('OSCOM_APP_PAYPAL_SANDBOX_API_' . strtoupper($type));
      }
    }

    function hasApiCredentials($server, $type = null) {
      $server = ($server == 'live') ? 'LIVE' : 'SANDBOX';

      if ( $type == 'email') {
        $creds = array('OSCOM_APP_PAYPAL_' . $server . '_SELLER_EMAIL');
      } elseif ( substr($type, 0, 7) == 'payflow' ) {
        $creds = array('OSCOM_APP_PAYPAL_PF_' . $server . '_' . strtoupper(substr($type, 8)));
      } else {
        $creds = array('OSCOM_APP_PAYPAL_' . $server . '_API_USERNAME',
                       'OSCOM_APP_PAYPAL_' . $server . '_API_PASSWORD',
                       'OSCOM_APP_PAYPAL_' . $server . '_API_SIGNATURE');
      }

      foreach ( $creds as $c ) {
        if ( !defined($c) || (strlen(trim(constant($c))) < 1) ) {
          return false;
        }
      }

      return true;
    }

    function getApiCredentials($server, $type) {
      if ( ($server == 'live') && defined('OSCOM_APP_PAYPAL_LIVE_API_' . strtoupper($type)) ) {
        return constant('OSCOM_APP_PAYPAL_LIVE_API_' . strtoupper($type));
      } elseif ( defined('OSCOM_APP_PAYPAL_SANDBOX_API_' . strtoupper($type)) ) {
        return constant('OSCOM_APP_PAYPAL_SANDBOX_API_' . strtoupper($type));
      }
    }

// APP calls require $server to be "live" or "sandbox"
    public function getApiResult($module, $call, array $extra_params = null, $server = null, $is_ipn = false)
    {
        $class = 'OSC\Apps\PayPal\PayPal\API\\' . $call;

        $API = new $class($server);

        $result = $API->execute($extra_params);

        $this->log($module, $call, ($result['success'] === true) ? 1 : -1, $result['req'], $result['res'], $server, $is_ipn);

        return $result['res'];
    }

    public function makeApiCall($url, $parameters = null, array $headers = null)
    {
        $server = parse_url($url);

        $p = [
            'url' => $url,
            'parameters' => $parameters,
            'headers' => $headers
        ];

        if (defined('OSCOM_APP_PAYPAL_VERIFY_SSL') && (OSCOM_APP_PAYPAL_VERIFY_SSL == '1')) {
            $p['verify_ssl'] = true;
        }

        if ((substr($server['host'], -10) == 'paypal.com')) {
            $p['cafile'] = OSCOM::BASE_DIR . 'Apps/PayPal/PayPal/paypal.com.crt';
        }

        if (defined('OSCOM_APP_PAYPAL_PROXY')) {
            $p['proxy'] = OSCOM_APP_PAYPAL_PROXY;
        }

        return HTTP::getResponse($p);
    }

    function formatCurrencyRaw($total, $currency_code = null, $currency_value = null) {
      global $currencies;

      if ( empty($currency_code) ) {
        $currency_code = isset($_SESSION['currency']) ? $_SESSION['currency'] : DEFAULT_CURRENCY;
      }

      if ( !isset($currency_value) || !is_numeric($currency_value) ) {
        $currency_value = $currencies->currencies[$currency_code]['value'];
      }

      return number_format(tep_round($total * $currency_value, $currencies->currencies[$currency_code]['decimal_places']), $currencies->currencies[$currency_code]['decimal_places'], '.', '');
    }

    public function getApiVersion()
    {
        return $this->api_version;
    }

    function logUpdate($message, $version) {
      if ( FileSystem::isWritable(OSCOM::BASE_DIR . 'Apps/PayPal/PayPal/work') ) {
        file_put_contents(OSCOM::BASE_DIR . 'Apps/PayPal/PayPal/work/update_log-' . $version . '.php', '[' . date('d-M-Y H:i:s') . '] ' . $message . "\n", FILE_APPEND);
      }
    }
  }
?>
