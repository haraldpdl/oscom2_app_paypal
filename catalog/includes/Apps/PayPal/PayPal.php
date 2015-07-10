<?php
/**
  * osCommerce Online Merchant
  *
  * @copyright Copyright (c) 2015 osCommerce; http://www.oscommerce.com
  * @license GPL; http://www.oscommerce.com/gpllicense.txt
  */

namespace OSC\OM\Apps\PayPal;

use OSC\OM\HTML;
use OSC\OM\HTTP;
use OSC\OM\OSCOM;
use OSC\OM\Registry;

class PayPal extends \OSC\OM\AppAbstract
{
    protected $api_version = 123;
    protected $definitions = [];

    protected $db;

    protected function init()
    {
        $this->db = Registry::get('Db');
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

            if (file_exists(OSCOM::BASE_DIR . 'apps/PayPal/req_api_countries.txt')) {
                foreach (file(OSCOM::BASE_DIR . 'apps/PayPal/req_api_countries.txt') as $c) {
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

        if (in_array(OSCOM_APP_PAYPAL_LOG_TRANSACTIONS, ['1', '0'])) {
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
                $this->saveParameter('OSCOM_APP_PAYPAL_' . $module . '_STATUS', '');

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

            $directory = OSCOM::BASE_DIR . 'apps/PayPal/Module/Admin/Config';

            if ($dir = new \DirectoryIterator($directory)) {
                foreach ($dir as $file) {
                    if (!$file->isDot() && $file->isDir() && file_exists($file->getPathname() . '/' . $file->getFilename() . '.php')) {
                        $class = 'OSC\OM\Apps\PayPal\Module\Admin\Config\\' . $file->getFilename() . '\\' . $file->getFilename();

                        if (is_subclass_of($class, 'OSC\OM\Apps\PayPal\Module\Admin\Config\ConfigAbstract')) {
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
                            trigger_error('OSC\OM\Apps\PayPal\PayPal::getConfigModules(): OSC\OM\Apps\PayPal\Module\Admin\Config\\' . $file->getFilename() . '\\' . $file->getFilename() . ' is not a subclass of OSC\OM\Apps\PayPal\Module\Admin\Config\ConfigAbstract and cannot be loaded.');
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
            $class = 'OSC\OM\Apps\PayPal\Module\Admin\Config\\' . $module . '\\' . $module;

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
        $class = 'OSC\OM\Apps\PayPal\API\\' . $call;

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
            $p['cafile'] = OSCOM::BASE_DIR . 'apps/PayPal/paypal.com.crt';
        }

        if (defined('OSCOM_APP_PAYPAL_PROXY')) {
            $p['proxy'] = OSCOM_APP_PAYPAL_PROXY;
        }

        return HTTP::getResponse($p);
    }

    function drawButton($title = null, $link = null, $type = null, $params = null, $force_css = false) {
      $colours = array('success' => '#1cb841',
                       'error' => '#ca3c3c',
                       'warning' => '#ebaa16',
                       'info' => '#42B8DD',
                       'primary' => '#0078E7');

      if ( !isset($type) || !in_array($type, array_keys($colours)) ) {
        $type = 'info';
      }

      $css = 'font-size:14px;color:#fff;padding:8px 16px;border:0;border-radius:4px;text-shadow:0 1px 1px rgba(0, 0, 0, 0.2);text-decoration:none;display:inline-block;cursor:pointer;white-space:nowrap;vertical-align:baseline;text-align:center;background-color:' . $colours[$type] . ';';

      $button = '';

      if ( isset($link) ) {
        $button .= '<a href="' . $link . '" class="pp-button';

        if ( isset($type) ) {
          $button .= ' pp-button-' . $type;
        }

        $button .= '"';

        if ( isset($params) ) {
          $button .= ' ' . $params;
        }

        if ( $force_css == true ) {
          $button .= ' style="' . $css . '"';
        }

        $button .= '>' . $title . '</a>';
      } else {
        $button .= '<button type="submit" class="pp-button';

        if ( isset($type) ) {
          $button .= ' pp-button-' . $type;
        }

        $button .= '"';

        if ( isset($params) ) {
          $button .= ' ' . $params;
        }

        if ( $force_css == true ) {
          $button .= ' style="' . $css . '"';
        }

        $button .= '>' . $title . '</button>';
      }

      return $button;
    }

    function createRandomValue($length, $type = 'mixed') {
      if ( ($type != 'mixed') && ($type != 'chars') && ($type != 'digits')) $type = 'mixed';

      $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
      $digits = '0123456789';

      $base = '';

      if ( ($type == 'mixed') || ($type == 'chars') ) {
        $base .= $chars;
      }

      if ( ($type == 'mixed') || ($type == 'digits') ) {
        $base .= $digits;
      }

      $value = '';

      if ( !class_exists('PasswordHash') && file_exists(DIR_FS_CATALOG . 'includes/classes/passwordhash.php') ) {
        include(DIR_FS_CATALOG . 'includes/classes/passwordhash.php');

        $hasher = new PasswordHash(10, true);

        do {
          $random = base64_encode($hasher->get_random_bytes($length));

          for ($i = 0, $n = strlen($random); $i < $n; $i++) {
            $char = substr($random, $i, 1);

            if ( strpos($base, $char) !== false ) {
              $value .= $char;
            }
          }
        } while ( strlen($value) < $length );

        if ( strlen($value) > $length ) {
          $value = substr($value, 0, $length);
        }

        return $value;
      }

// fallback for v2.3.1
      while ( strlen($value) < $length ) {
        if ($type == 'digits') {
          $char = tep_rand(0,9);
        } else {
          $char = chr(tep_rand(0,255));
        }

        if ( $type == 'mixed' ) {
          if (preg_match('/^[a-z0-9]$/i', $char)) $value .= $char;
        } elseif ($type == 'chars') {
          if (preg_match('/^[a-z]$/i', $char)) $value .= $char;
        } elseif ($type == 'digits') {
          if (preg_match('/^[0-9]$/i', $char)) $value .= $char;
        }
      }

      return $value;
    }

    function saveParameter($key, $value, $title = null, $description = null, $set_func = null) {
      if (is_null($value)) {
        $value = '';
      }

      if ( !defined($key) ) {
        if ( !isset($title) ) {
          $title = 'PayPal App Parameter';
        }

        if ( !isset($description) ) {
          $description = 'A parameter for the PayPal Application.';
        }

        $data = [
          'configuration_title' => $title,
          'configuration_key' => $key,
          'configuration_value' => $value,
          'configuration_description' => $description,
          'configuration_group_id' => '6',
          'sort_order' => '0',
          'date_added' => 'now()'
        ];

        if ( isset($set_func) ) {
          $data['set_function'] = $set_func;
        }

        $this->db->save('configuration', $data);

        define($key, $value);
      } else {
        $this->db->save('configuration', ['configuration_value' => $value], ['configuration_key' => $key]);
      }
    }

    function deleteParameter($key) {
      $this->db->delete('configuration', ['configuration_key' => $key]);
    }

    function formatCurrencyRaw($total, $currency_code = null, $currency_value = null) {
      global $currencies;

      if ( !isset($currency_code) ) {
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

    function hasAlert() {
      return isset($_SESSION['OSCOM_PayPal_Alerts']);
    }

    function addAlert($message, $type) {
      if ( in_array($type, array('error', 'warning', 'success')) ) {
        if ( !isset($_SESSION['OSCOM_PayPal_Alerts']) ) {
          $_SESSION['OSCOM_PayPal_Alerts'] = array();
        }

        $_SESSION['OSCOM_PayPal_Alerts'][$type][] = $message;
      }
    }

    function getAlerts() {
      $output = '';

      if ( isset($_SESSION['OSCOM_PayPal_Alerts']) && !empty($_SESSION['OSCOM_PayPal_Alerts']) ) {
        $result = array();

        foreach ( $_SESSION['OSCOM_PayPal_Alerts'] as $type => $messages ) {
          if ( in_array($type, array('error', 'warning', 'success')) ) {
            $m = '<ul class="pp-alerts-' . $type . '">';

            foreach ( $messages as $message ) {
              $m .= '<li>' . HTML::outputProtected($message) . '</li>';
            }

            $m .= '</ul>';

            $result[] = $m;
          }
        }

        if ( !empty($result) ) {
          $output .= '<div class="pp-alerts">' . implode("\n", $result) . '</div>';
        }

        unset($_SESSION['OSCOM_PayPal_Alerts']);
      }

      return $output;
    }

    function logUpdate($message, $version) {
      if ( is_writable(DIR_FS_CATALOG . 'includes/apps/PayPal/work') ) {
        file_put_contents(DIR_FS_CATALOG . 'includes/apps/PayPal/work/update_log-' . $version . '.php', '[' . date('d-M-Y H:i:s') . '] ' . $message . "\n", FILE_APPEND);
      }
    }

    public function loadLanguageFile($filename, $lang = null) {
      $lang = isset($lang) ? basename($lang) : basename($_SESSION['language']);

      if ( $lang != 'english' ) {
        $this->loadLanguageFile($filename, 'english');
      }

      $pathname = DIR_FS_CATALOG . 'includes/apps/PayPal/languages/' . $lang . '/' . $filename;

      if ( file_exists($pathname) ) {
        $contents = file($pathname);

        $ini_array = array();

        foreach ( $contents as $line ) {
          $line = trim($line);

          if ( !empty($line) && (substr($line, 0, 1) != '#') ) {
            $delimiter = strpos($line, '=');

            if ( ($delimiter !== false) && (preg_match('/^[A-Za-z0-9_-]/', substr($line, 0, $delimiter)) === 1) && (substr_count(substr($line, 0, $delimiter), ' ') == 1) ) {
              $key = trim(substr($line, 0, $delimiter));
              $value = trim(substr($line, $delimiter + 1));

              $ini_array[$key] = $value;
            } elseif ( isset($key) ) {
              $ini_array[$key] .= "\n" . $line;
            }
          }
        }

        unset($contents);

        $this->definitions = array_merge($this->definitions, $ini_array);

        unset($ini_array);
      }
    }

    function getDef($key, $values = null) {
      $def = isset($this->definitions[$key]) ? $this->definitions[$key] : $key;

      if ( is_array($values) ) {
        $keys = array_keys($values);

        foreach ( $keys as &$k ) {
          $k = ':' . $k;
        }

        $def = str_replace($keys, array_values($values), $def);
      }

      return $def;
    }

    function getDirectoryContents($base, &$result = array()) {
      foreach ( scandir($base) as $file ) {
        if ( ($file == '.') || ($file == '..') ) {
          continue;
        }

        $pathname = $base . '/' . $file;

        if ( is_dir($pathname) ) {
          $this->getDirectoryContents($pathname, $result);
        } else {
          $result[] = str_replace('\\', '/', $pathname); // Unix style directory separator "/"
        }
      }

      return $result;
    }

    function isWritable($location) {
      if ( !file_exists($location) ) {
        while ( true ) {
          $location = dirname($location);

          if ( file_exists($location) ) {
            break;
          }
        }
      }

      return is_writable($location);
    }

    function rmdir($dir) {
      foreach ( scandir($dir) as $file ) {
        if ( !in_array($file, array('.', '..')) ) {
          if ( is_dir($dir . '/' . $file) ) {
            $this->rmdir($dir . '/' . $file);
          } else {
            unlink($dir . '/' . $file);
          }
        }
      }

      return rmdir($dir);
    }

    function displayPath($pathname) {
      if ( DIRECTORY_SEPARATOR == '/' ) {
        return $pathname;
      }

      return str_replace('/', DIRECTORY_SEPARATOR, $pathname);
    }
  }
?>
