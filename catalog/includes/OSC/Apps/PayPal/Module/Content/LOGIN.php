<?php
/**
  * osCommerce Online Merchant
  *
  * @copyright Copyright (c) 2015 osCommerce; http://www.oscommerce.com
  * @license GPL; http://www.oscommerce.com/gpllicense.txt
  */

  namespace OSC\Apps\PayPal\Module\Content;

  use OSC\OM\HTML;
  use OSC\OM\OSCOM;
  use OSC\OM\Registry;

  use OSC\Apps\PayPal\PayPal as PayPalApp;

  class LOGIN implements \OSC\OM\Modules\ContentInterface {
    var $code, $group, $title, $description, $sort_order, $enabled, $_app;

    function __construct() {
      if (!Registry::exists('PayPal')) {
        Registry::set('PayPal', new PayPalApp());
      }

      $this->_app = Registry::get('PayPal');
      $this->_app->loadLanguageFile('modules/LOGIN/LOGIN.php');

      $this->signature = 'paypal|paypal_login|' . $this->_app->getVersion() . '|2.3';

      $this->code = 'login/PayPal\LOGIN';
      $this->group = 'login';

      $this->title = $this->_app->getDef('module_login_title');
      $this->description = '<div align="center">' . $this->_app->drawButton($this->_app->getDef('module_login_legacy_admin_app_button'), OSCOM::link('index.php', 'A&PayPal&Configure&module=LOGIN'), 'primary', null, true) . '</div>';

      if ( defined('OSCOM_APP_PAYPAL_LOGIN_STATUS') ) {
        $this->sort_order = OSCOM_APP_PAYPAL_LOGIN_SORT_ORDER;
        $this->enabled = in_array(OSCOM_APP_PAYPAL_LOGIN_STATUS, array('1', '0'));

        if ( OSCOM_APP_PAYPAL_LOGIN_STATUS == '0' ) {
          $this->title .= ' [Sandbox]';
        }

        if ( !function_exists('curl_init') ) {
          $this->description .= '<div class="secWarning">' . $this->_app->getDef('module_login_error_curl') . '</div>';

          $this->enabled = false;
        }

        if ( $this->enabled === true ) {
          if ( ((OSCOM_APP_PAYPAL_LOGIN_STATUS == '1') && (!tep_not_null(OSCOM_APP_PAYPAL_LOGIN_LIVE_CLIENT_ID) || !tep_not_null(OSCOM_APP_PAYPAL_LOGIN_LIVE_SECRET))) || ((OSCOM_APP_PAYPAL_LOGIN_STATUS == '0') && (!tep_not_null(OSCOM_APP_PAYPAL_LOGIN_SANDBOX_CLIENT_ID) || !tep_not_null(OSCOM_APP_PAYPAL_LOGIN_SANDBOX_SECRET))) ) {
            $this->description .= '<div class="secWarning">' . $this->_app->getDef('module_login_error_credentials') . '</div>';

            $this->enabled = false;
          }
        }
      }
    }

    function execute() {
      global $oscTemplate;

      if (isset($_SESSION['customer_id'])) {
        return false;
      }

      if ( isset($_GET['action']) ) {
        if ( $_GET['action'] == 'paypal_login' ) {
          $this->preLogin();
        } elseif ( $_GET['action'] == 'paypal_login_process' ) {
          $this->postLogin();
        }
      }

      $scopes = cm_paypal_login_get_attributes();
      $use_scopes = array('openid');

      foreach ( explode(';', OSCOM_APP_PAYPAL_LOGIN_ATTRIBUTES) as $a ) {
        foreach ( $scopes as $group => $attributes ) {
          foreach ( $attributes as $attribute => $scope ) {
            if ( $a == $attribute ) {
              if ( !in_array($scope, $use_scopes) ) {
                $use_scopes[] = $scope;
              }
            }
          }
        }
      }

      $cm_paypal_login = $this;

      ob_start();
      include(__DIR__ . '/templates/LOGIN.php');
      $template = ob_get_clean();

      $oscTemplate->addContent($template, $this->group);
    }

    function preLogin() {
      $OSCOM_Db = Registry::get('Db');

      $return_url = OSCOM::link('index.php', 'Account&LogIn', 'SSL');

      if ( isset($_GET['code']) ) {
        $_SESSION['paypal_login_customer_id'] = false;

        $params = array('code' => $_GET['code'],
                        'redirect_uri' => str_replace('&amp;', '&', OSCOM::link('index.php', 'Account&LogIn&action=paypal_login', 'SSL')));

        $response_token = $this->_app->getApiResult('LOGIN', 'GrantToken', $params);

        if ( !isset($response_token['access_token']) && isset($response_token['refresh_token']) ) {
          $params = array('refresh_token' => $response_token['refresh_token']);

          $response_token = $this->_app->getApiResult('LOGIN', 'RefreshToken', $params);
        }

        if ( isset($response_token['access_token']) ) {
          $params = array('access_token' => $response_token['access_token']);

          $response = $this->_app->getApiResult('LOGIN', 'UserInfo', $params);

          if ( isset($response['email']) ) {
            $_SESSION['paypal_login_access_token'] = $response_token['access_token'];

// check if e-mail address exists in database and login or create customer account
            $email_address = HTML::sanitize($response['email']);

            $Qcheck = $OSCOM_Db->get('customers', 'customers_id', ['customers_email_address' => $email_address], null, 1);

            if ($Qcheck->fetch() !== false) {
              $_SESSION['paypal_login_customer_id'] = $Qcheck->valueInt('customers_id');
            } else {
              $customers_firstname = HTML::sanitize($response['given_name']);
              $customers_lastname = HTML::sanitize($response['family_name']);

              $sql_data_array = array('customers_firstname' => $customers_firstname,
                                      'customers_lastname' => $customers_lastname,
                                      'customers_email_address' => $email_address,
                                      'customers_telephone' => '',
                                      'customers_fax' => '',
                                      'customers_newsletter' => '0',
                                      'customers_password' => '');

              if ($this->hasAttribute('phone') && isset($response['phone_number']) && tep_not_null($response['phone_number'])) {
                $customers_telephone = HTML::sanitize($response['phone_number']);

                $sql_data_array['customers_telephone'] = $customers_telephone;
              }

              $OSCOM_Db->save('customers', $sql_data_array);

              $_SESSION['paypal_login_customer_id'] = $OSCOM_Db->lastInsertId();

              $OSCOM_Db->save('customers_info', [
                'customers_info_id' => $_SESSION['paypal_login_customer_id'],
                'customers_info_number_of_logons' => '0',
                'customers_info_date_account_created' => 'now()'
              ]);
            }

// check if paypal shipping address exists in the address book
            $ship_firstname = HTML::sanitize($response['given_name']);
            $ship_lastname = HTML::sanitize($response['family_name']);
            $ship_address = HTML::sanitize($response['address']['street_address']);
            $ship_city = HTML::sanitize($response['address']['locality']);
            $ship_zone = HTML::sanitize($response['address']['region']);
            $ship_zone_id = 0;
            $ship_postcode = HTML::sanitize($response['address']['postal_code']);
            $ship_country = HTML::sanitize($response['address']['country']);
            $ship_country_id = 0;
            $ship_address_format_id = 1;

            $Qcountry = $OSCOM_Db->get('countries', ['countries_id', 'address_format_id'], ['countries_iso_code_2' => $ship_country], null, 1);

            if ($Qcountry->fetch() !== false) {
              $ship_country_id = $Qcountry->valueInt('countries_id');
              $ship_address_format_id = $Qcountry->valueInt('address_format_id');
            }

            if ($ship_country_id > 0) {
              $Qzone = $OSCOM_Db->prepare('select zone_id from :table_zones where zone_country_id = :zone_country_id and (zone_name = :zone_name or zone_code = :zone_code) limit 1');
              $Qzone->bindInt(':zone_country_id', $ship_country_id);
              $Qzone->bindValue(':zone_name', $ship_zone);
              $Qzone->bindValue(':zone_code', $ship_zone);
              $Qzone->execute();

              if ($Qzone->fetch() !== false) {
                $ship_zone_id = $Qzone->valueInt('zone_id');
              }
            }

            $Qcheck = $OSCOM_Db->prepare('select address_book_id from :table_address_book where customers_id = :customers_id and entry_firstname = :entry_firstname and entry_lastname = :entry_lastname and entry_street_address = :entry_street_address and entry_postcode = :entry_postcode and entry_city = :entry_city and (entry_state = :entry_state or entry_zone_id = :entry_zone_id) and entry_country_id = :entry_country_id limit 1');
            $Qcheck->bindInt(':customers_id', $_SESSION['paypal_login_customer_id']);
            $Qcheck->bindValue(':entry_firstname', $ship_firstname);
            $Qcheck->bindValue(':entry_lastname', $ship_lastname);
            $Qcheck->bindValue(':entry_street_address', $ship_address);
            $Qcheck->bindValue(':entry_postcode', $ship_postcode);
            $Qcheck->bindValue(':entry_city', $ship_city);
            $Qcheck->bindValue(':entry_state', $ship_zone);
            $Qcheck->bindInt(':entry_zone_id', $ship_zone_id);
            $Qcheck->bindInt(':entry_country_id', $ship_country_id);
            $Qcheck->execute();

            if ($Qcheck->fetch() !== false) {
              $_SESSION['sendto'] = $Qcheck->valueInt('address_book_id');
            } else {
              $sql_data_array = array('customers_id' => $_SESSION['paypal_login_customer_id'],
                                      'entry_firstname' => $ship_firstname,
                                      'entry_lastname' => $ship_lastname,
                                      'entry_street_address' => $ship_address,
                                      'entry_postcode' => $ship_postcode,
                                      'entry_city' => $ship_city,
                                      'entry_country_id' => $ship_country_id);

              if (ACCOUNT_STATE == 'true') {
                if ($ship_zone_id > 0) {
                  $sql_data_array['entry_zone_id'] = $ship_zone_id;
                  $sql_data_array['entry_state'] = '';
                } else {
                  $sql_data_array['entry_zone_id'] = '0';
                  $sql_data_array['entry_state'] = $ship_zone;
                }
              }

              $OSCOM_Db->save('address_book', $sql_data_array);

              $address_id = $OSCOM_Db->lastInsertId();

              $_SESSION['sendto'] = $address_id;

              if (!isset($_SESSION['customer_default_address_id'])) {
                $OSCOM_Db->save('customers', ['customers_default_address_id' => $address_id], ['customers_id' => $_SESSION['paypal_login_customer_id']]);

                $_SESSION['customer_default_address_id'] = $address_id;
              }
            }

            $_SESSION['billto'] = $_SESSION['sendto'];

            $return_url = OSCOM::link('index.php', 'Account&LogIn&action=paypal_login_process', 'SSL');
          }
        }
      }

      echo '<script>window.opener.location.href="' . str_replace('&amp;', '&', $return_url) . '";window.close();</script>';

      exit;
    }

    function postLogin() {
      global $login_customer_id;

      if ( isset($_SESSION['paypal_login_customer_id']) ) {
        if ( $_SESSION['paypal_login_customer_id'] !== false ) {
          $login_customer_id = $_SESSION['paypal_login_customer_id'];

// Register PayPal Express Checkout as the default payment method
          if ( !isset($_SESSION['payment']) || ($_SESSION['payment'] != 'paypal_express') ) {
            if (defined('MODULE_PAYMENT_INSTALLED') && !empty(MODULE_PAYMENT_INSTALLED)) {
              if ( in_array('paypal_express.php', explode(';', MODULE_PAYMENT_INSTALLED)) ) {
                if ( !class_exists('paypal_express') ) {
                  include(DIR_WS_LANGUAGES . $_SESSION['language'] . '/modules/payment/paypal_express.php');
                  include(DIR_WS_MODULES . 'payment/paypal_express.php');
                }

                $ppe = new paypal_express();

                if ( $ppe->enabled ) {
                  $_SESSION['payment'] = 'paypal_express';
                }
              }
            }
          }
        }

        unset($_SESSION['paypal_login_customer_id']);
      }
    }

    function isEnabled() {
      return $this->enabled;
    }

    function check() {
      return defined('OSCOM_APP_PAYPAL_LOGIN_STATUS');
    }

    function install() {
      OSCOM::redirect('index.php', 'A&PayPal&Configure&Install&module=LOGIN');
    }

    function remove() {
      OSCOM::redirect('index.php', 'A&PayPal&Configure&Uninstall&module=LOGIN');
    }

    function keys() {
      return array('OSCOM_APP_PAYPAL_LOGIN_CONTENT_WIDTH', 'OSCOM_APP_PAYPAL_LOGIN_SORT_ORDER');
    }

    function hasAttribute($attribute) {
      return in_array($attribute, explode(';', OSCOM_APP_PAYPAL_LOGIN_ATTRIBUTES));
    }

    function get_default_attributes() {
      $data = array();

      foreach ( cm_paypal_login_get_attributes() as $group => $attributes ) {
        foreach ( $attributes as $attribute => $scope ) {
          $data[] = $attribute;
        }
      }

      return $data;
    }
  }

  function cm_paypal_login_get_attributes() {
    return array('personal' => array('full_name' => 'profile',
                                     'date_of_birth' => 'profile',
                                     'age_range' => 'https://uri.paypal.com/services/paypalattributes',
                                     'gender' => 'profile'),
                 'address' => array('email_address' => 'email',
                                    'street_address' => 'address',
                                    'city' => 'address',
                                    'state' => 'address',
                                    'country' => 'address',
                                    'zip_code' => 'address',
                                    'phone' => 'phone'),
                 'account' => array('account_status' => 'https://uri.paypal.com/services/paypalattributes',
                                    'account_type' => 'https://uri.paypal.com/services/paypalattributes',
                                    'account_creation_date' => 'https://uri.paypal.com/services/paypalattributes',
                                    'time_zone' => 'profile',
                                    'locale' => 'profile',
                                    'language' => 'profile'),
                 'checkout' => array('seamless_checkout' => 'https://uri.paypal.com/services/expresscheckout'));
  }
?>
