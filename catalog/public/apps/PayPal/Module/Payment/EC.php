<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2014 osCommerce

  Released under the GNU General Public License
*/

  use OSC\OM\HTML;
  use OSC\OM\HTTP;
  use OSC\OM\OSCOM;

  use OSC\OM\Apps\PayPal\Module\Payment\EC;

  chdir('../../../../../');
  require('includes/application_top.php');

  require(DIR_WS_LANGUAGES . $_SESSION['language'] . '/create_account.php');

  $paypal_express = new EC();

  if ( !$paypal_express->check() || !$paypal_express->enabled ) {
    OSCOM::redirect('shopping_cart.php', '', 'SSL');
  }

  if ( !isset($_SESSION['sendto']) ) {
    if ( isset($_SESSION['customer_id']) ) {
      $_SESSION['sendto'] = $_SESSION['customer_default_address_id'];
    } else {
      $country = tep_get_countries(STORE_COUNTRY, true);

      $_SESSION['sendto'] = [
        'firstname' => '',
        'lastname' => '',
        'company' => '',
        'street_address' => '',
        'suburb' => '',
        'postcode' => '',
        'city' => '',
        'zone_id' => STORE_ZONE,
        'zone_name' => tep_get_zone_name(STORE_COUNTRY, STORE_ZONE, ''),
        'country_id' => STORE_COUNTRY,
        'country_name' => $country['countries_name'],
        'country_iso_code_2' => $country['countries_iso_code_2'],
        'country_iso_code_3' => $country['countries_iso_code_3'],
        'address_format_id' => tep_get_address_format_id(STORE_COUNTRY)
      ];
    }
  }

  if ( !isset($_SESSION['billto']) ) {
    $_SESSION['billto'] = $_SESSION['sendto'];
  }

// register a random ID in the session to check throughout the checkout procedure
// against alterations in the shopping cart contents
  $_SESSION['cartID'] = $_SESSION['cart']->cartID;

  if (!isset($_GET['osC_Action'])) {
    $_GET['osC_Action'] = null;
  }

  switch ($_GET['osC_Action']) {
    case 'cancel':
      if (isset($_SESSION['appPayPalEcResult'])) {
        unset($_SESSION['appPayPalEcResult']);
      }

      if (isset($_SESSION['appPayPalEcSecret'])) {
        unset($_SESSION['appPayPalEcSecret']);
      }

      if ( empty($_SESSION['sendto']['firstname']) && empty($_SESSION['sendto']['lastname']) && empty($_SESSION['sendto']['street_address']) ) {
        unset($_SESSION['sendto']);
      }

      if ( empty($_SESSION['billto']['firstname']) && empty($_SESSION['billto']['lastname']) && empty($_SESSION['billto']['street_address']) ) {
        unset($_SESSION['billto']);
      }

      OSCOM::redirect('shopping_cart.php', '', 'SSL');

      break;
    case 'callbackSet':
      if ( (OSCOM_APP_PAYPAL_GATEWAY == '1') && (OSCOM_APP_PAYPAL_EC_INSTANT_UPDATE == '1') ) {
        $log_sane = array();

        $counter = 0;

        if (isset($_POST['CURRENCYCODE']) && $currencies->is_set($_POST['CURRENCYCODE']) && ($_SESSION['currency'] != $_POST['CURRENCYCODE'])) {
          $_SESSION['currency'] = $_POST['CURRENCYCODE'];

          $log_sane['CURRENCYCODE'] = $_POST['CURRENCYCODE'];
        }

        while (true) {
          if ( isset($_POST['L_NUMBER' . $counter]) && isset($_POST['L_QTY' . $counter]) ) {
            $_SESSION['cart']->add_cart($_POST['L_NUMBER' . $counter], $_POST['L_QTY' . $counter]);

            $log_sane['L_NUMBER' . $counter] = $_POST['L_NUMBER' . $counter];
            $log_sane['L_QTY' . $counter] = $_POST['L_QTY' . $counter];
          } else {
            break;
          }

          $counter++;
        }

// exit if there is nothing in the shopping cart
        if ($_SESSION['cart']->count_contents() < 1) {
          exit;
        }

        $_SESSION['sendto'] = [
          'firstname' => '',
          'lastname' => '',
          'company' => '',
          'street_address' => $_POST['SHIPTOSTREET'],
          'suburb' => '',
          'postcode' => $_POST['SHIPTOZIP'],
          'city' => $_POST['SHIPTOCITY'],
          'zone_id' => '',
          'zone_name' => $_POST['SHIPTOSTATE'],
          'country_id' => '',
          'country_name' => $_POST['SHIPTOCOUNTRY'],
          'country_iso_code_2' => '',
          'country_iso_code_3' => '',
          'address_format_id' => ''
        ];

        $log_sane['SHIPTOSTREET'] = $_POST['SHIPTOSTREET'];
        $log_sane['SHIPTOZIP'] = $_POST['SHIPTOZIP'];
        $log_sane['SHIPTOCITY'] = $_POST['SHIPTOCITY'];
        $log_sane['SHIPTOSTATE'] = $_POST['SHIPTOSTATE'];
        $log_sane['SHIPTOCOUNTRY'] = $_POST['SHIPTOCOUNTRY'];

        $Qcountry = $OSCOM_Db->get('countries', '*', ['countries_iso_code_2' => $_SESSION['sendto']['country_name']], null, 1);

        if ($Qcountry->fetch() !== false) {
          $_SESSION['sendto']['country_id'] = $Qcountry->valueInt('countries_id');
          $_SESSION['sendto']['country_name'] = $Qcountry->value('countries_name');
          $_SESSION['sendto']['country_iso_code_2'] = $Qcountry->value('countries_iso_code_2');
          $_SESSION['sendto']['country_iso_code_3'] = $Qcountry->value('countries_iso_code_3');
          $_SESSION['sendto']['address_format_id'] = $Qcountry->value('address_format_id');
        }

        if ($_SESSION['sendto']['country_id'] > 0) {
          $Qzone = $OSCOM_Db->prepare('select * from :zones where zone_country_id = :zone_country_id and (zone_name = :zone_name or zone_code = :zone_code) limit 1');
          $Qzone->bindInt(':zone_country_id', $_SESSION['sendto']['country_id']);
          $Qzone->bindValue(':zone_name', $_SESSION['sendto']['zone_name']);
          $Qzone->bindValue(':zone_code', $_SESSION['sendto']['zone_name']);
          $Qzone->execute();

          if ($Qzone->fetch() !== false) {
            $_SESSION['sendto']['zone_id'] = $Qzone->valueInt('zone_id');
            $_SESSION['sendto']['zone_name'] = $Qzone->value('zone_name');
          }
        }

        $_SESSION['billto'] = $_SESSION['sendto'];

        $quotes_array = array();

        include(DIR_WS_CLASSES . 'order.php');
        $order = new order;

        if ($_SESSION['cart']->get_content_type() != 'virtual') {
          $total_weight = $_SESSION['cart']->show_weight();
          $total_count = $_SESSION['cart']->count_contents();

// load all enabled shipping modules
          include(DIR_WS_CLASSES . 'shipping.php');
          $shipping_modules = new shipping;

          $free_shipping = false;

          if ( defined('MODULE_ORDER_TOTAL_SHIPPING_FREE_SHIPPING') && (MODULE_ORDER_TOTAL_SHIPPING_FREE_SHIPPING == 'true') ) {
            $pass = false;

            switch (MODULE_ORDER_TOTAL_SHIPPING_DESTINATION) {
              case 'national':
                if ($order->delivery['country_id'] == STORE_COUNTRY) {
                  $pass = true;
                }
                break;

              case 'international':
                if ($order->delivery['country_id'] != STORE_COUNTRY) {
                  $pass = true;
                }
                break;

              case 'both':
                $pass = true;
                break;
            }

            if ( ($pass == true) && ($order->info['total'] >= MODULE_ORDER_TOTAL_SHIPPING_FREE_SHIPPING_OVER) ) {
              $free_shipping = true;

              include(DIR_WS_LANGUAGES . $_SESSION['language'] . '/modules/order_total/ot_shipping.php');
            }
          }

          if ( (tep_count_shipping_modules() > 0) || ($free_shipping == true) ) {
            if ($free_shipping == true) {
              $quotes_array[] = array('id' => 'free_free',
                                      'name' => FREE_SHIPPING_TITLE,
                                      'label' => '',
                                      'cost' => '0',
                                      'tax' => '0');
            } else {
// get all available shipping quotes
              $quotes = $shipping_modules->quote();

              foreach ($quotes as $quote) {
                if (!isset($quote['error'])) {
                  foreach ($quote['methods'] as $rate) {
                    $quotes_array[] = array('id' => $quote['id'] . '_' . $rate['id'],
                                            'name' => $quote['module'],
                                            'label' => $rate['title'],
                                            'cost' => $rate['cost'],
                                            'tax' => isset($quote['tax']) ? $quote['tax'] : '0');
                  }
                }
              }
            }
          }
        } else {
          $quotes_array[] = array('id' => 'null',
                                  'name' => 'No Shipping',
                                  'label' => '',
                                  'cost' => '0',
                                  'tax' => '0');
        }

        include(DIR_WS_CLASSES . 'order_total.php');
        $order_total_modules = new order_total;
        $order_totals = $order_total_modules->process();

        $params = array('METHOD' => 'CallbackResponse',
                        'CALLBACKVERSION' => $paypal_express->api_version);

        if ( !empty($quotes_array) ) {
          $params['CURRENCYCODE'] = $_SESSION['currency'];
          $params['OFFERINSURANCEOPTION'] = 'false';

          $counter = 0;
          $cheapest_rate = null;
          $cheapest_counter = $counter;

          foreach ($quotes_array as $quote) {
            $shipping_rate = $paypal_express->_app->formatCurrencyRaw($quote['cost'] + tep_calculate_tax($quote['cost'], $quote['tax']));

            $params['L_SHIPPINGOPTIONNAME' . $counter] = $quote['name'];
            $params['L_SHIPPINGOPTIONLABEL' . $counter] = $quote['label'];
            $params['L_SHIPPINGOPTIONAMOUNT' . $counter] = $shipping_rate;
            $params['L_SHIPPINGOPTIONISDEFAULT' . $counter] = 'false';

            if ( DISPLAY_PRICE_WITH_TAX == 'false' ) {
              $params['L_TAXAMT' . $counter] = $paypal_express->_app->formatCurrencyRaw($order->info['tax']);
            }

            if (is_null($cheapest_rate) || ($shipping_rate < $cheapest_rate)) {
              $cheapest_rate = $shipping_rate;
              $cheapest_counter = $counter;
            }

            $counter++;
          }

          if ( method_exists($shipping_modules, 'get_first') ) { // select first shipping method
            $params['L_SHIPPINGOPTIONISDEFAULT0'] = 'true';
          } else { // select cheapest shipping method
            $params['L_SHIPPINGOPTIONISDEFAULT' . $cheapest_counter] = 'true';
          }
        } else {
          $params['NO_SHIPPING_OPTION_DETAILS'] = '1';
        }

        $post_string = '';

        foreach ($params as $key => $value) {
          $post_string .= $key . '=' . urlencode(utf8_encode(trim($value))) . '&';
        }

        $post_string = substr($post_string, 0, -1);

        $paypal_express->_app->log('EC', 'CallbackResponse', 1, $log_sane, $params);

        echo $post_string;
      }

      tep_session_destroy();

      exit;

      break;
    case 'retrieve':
      if ( ($_SESSION['cart']->count_contents() < 1) || !isset($_GET['token']) || empty($_GET['token']) || !isset($_SESSION['appPayPalEcSecret']) ) {
        OSCOM::redirect('shopping_cart.php', '', 'SSL');
      }

      if ( !isset($_SESSION['appPayPalEcResult']) || ($_SESSION['appPayPalEcResult']['TOKEN'] != $_GET['token']) ) {
        if ( OSCOM_APP_PAYPAL_GATEWAY == '1' ) { // PayPal
          $_SESSION['appPayPalEcResult'] = $paypal_express->_app->getApiResult('EC', 'GetExpressCheckoutDetails', array('TOKEN' => $_GET['token']));
        } else { // Payflow
          $_SESSION['appPayPalEcResult'] = $paypal_express->_app->getApiResult('EC', 'PayflowGetExpressCheckoutDetails', array('TOKEN' => $_GET['token']));
        }
      }

      $pass = false;

      if ( OSCOM_APP_PAYPAL_GATEWAY == '1' ) { // PayPal
        if ( in_array($_SESSION['appPayPalEcResult']['ACK'], array('Success', 'SuccessWithWarning')) ) {
          $pass = true;
        }
      } else { // Payflow
        if ( $_SESSION['appPayPalEcResult']['RESULT'] == '0' ) {
          $pass = true;
        }
      }

      if ( $pass === true ) {
        if ( OSCOM_APP_PAYPAL_GATEWAY == '1' ) { // PayPal
          if ( $_SESSION['appPayPalEcResult']['PAYMENTREQUEST_0_CUSTOM'] != $_SESSION['appPayPalEcSecret'] ) {
            OSCOM::redirect('shopping_cart.php', '', 'SSL');
          }
        } else { // Payflow
          if ( $_SESSION['appPayPalEcResult']['CUSTOM'] != $_SESSION['appPayPalEcSecret'] ) {
            OSCOM::redirect('shopping_cart.php', '', 'SSL');
          }
        }

        $_SESSION['payment'] = $paypal_express->code;

        $force_login = false;

// check if e-mail address exists in database and login or create customer account
        if ( !isset($_SESSION['customer_id']) ) {
          $force_login = true;
          $force_redirect = false;

          $email_address = HTML::sanitize($_SESSION['appPayPalEcResult']['EMAIL']);

          if (!tep_validate_email($email_address)) {
            $force_redirect = true;
          } else {
            $Qcheck = $OSCOM_Db->get('customers', '*', ['customers_email_address' => $email_address], null, 1);

            if ($Qcheck->fetch() !== false) {
// Force the customer to log into their local account if payerstatus is unverified and a local password is set
              if ( ($_SESSION['appPayPalEcResult']['PAYERSTATUS'] == 'unverified') && !empty($Qcheck->value('customers_password')) ) {
                $force_redirect = true;
              } else {
                $_SESSION['customer_id'] = $Qcheck->valueInt('customers_id');
                $_SESSION['customer_first_name'] = $customers_firstname = $Qcheck->value('customers_firstname');
                $_SESSION['customer_default_address_id'] = $Qcheck->valueInt('customers_default_address_id');
              }
            } else {
              $customers_firstname = HTML::sanitize($_SESSION['appPayPalEcResult']['FIRSTNAME']);
              $customers_lastname = HTML::sanitize($_SESSION['appPayPalEcResult']['LASTNAME']);

              $sql_data_array = array('customers_firstname' => $customers_firstname,
                                      'customers_lastname' => $customers_lastname,
                                      'customers_email_address' => $email_address,
                                      'customers_telephone' => '',
                                      'customers_fax' => '',
                                      'customers_newsletter' => '0',
                                      'customers_password' => '',
                                      'customers_gender' => '');

              if ( isset($_SESSION['appPayPalEcResult']['PHONENUM']) && tep_not_null($_SESSION['appPayPalEcResult']['PHONENUM']) ) {
                $customers_telephone = HTML::sanitize($_SESSION['appPayPalEcResult']['PHONENUM']);

                $sql_data_array['customers_telephone'] = $customers_telephone;
              }

              $OSCOM_Db->save('customers', $sql_data_array);

              $_SESSION['customer_id'] = $OSCOM_Db->lastInsertId();

              $OSCOM_Db->save('customers_info', [
                'customers_info_id' => $_SESSION['customer_id'],
                'customers_info_number_of_logons' => '0',
                'customers_info_date_account_created' => 'now()'
              ]);

// Only generate a password and send an email if the Set Password Content Module is not enabled
              if ( !defined('MODULE_CONTENT_ACCOUNT_SET_PASSWORD_STATUS') || (MODULE_CONTENT_ACCOUNT_SET_PASSWORD_STATUS != 'True') ) {
                $customer_password = tep_create_random_value(max(ENTRY_PASSWORD_MIN_LENGTH, 8));

                $OSCOM_Db->save('customers', ['customers_password' => tep_encrypt_password($customer_password)], ['customers_id' => $_SESSION['customer_id']]);

// build the message content
                $name = $customers_firstname . ' ' . $customers_lastname;
                $email_text = sprintf(EMAIL_GREET_NONE, $customers_firstname) . EMAIL_WELCOME . $paypal_express->_app->getDef('module_ec_email_account_password', array('email_address' => $email_address, 'password' => $customer_password)) . "\n\n" . EMAIL_TEXT . EMAIL_CONTACT . EMAIL_WARNING;
                tep_mail($name, $email_address, EMAIL_SUBJECT, $email_text, STORE_OWNER, STORE_OWNER_EMAIL_ADDRESS);
              }
            }
          }

          if ($force_redirect === true) {
            $messageStack->add_session('login', $paypal_express->_app->getDef('module_ec_error_local_login_required'), 'warning');

            $_SESSION['navigation']->set_snapshot();

            $login_url = OSCOM::link('login.php', '', 'SSL');

            $output = <<<EOD
<form name="pe" action="{$login_url}" method="post" target="_top">
  <input type="hidden" name="email_address" value="{$email_address}" />
</form>
<script type="text/javascript">
document.pe.submit();
</script>
EOD;

            echo $output;
            exit;
          }

          if ( SESSION_RECREATE == 'True' ) {
            tep_session_recreate();
          }

// reset session token
          $sessiontoken = md5(tep_rand() . tep_rand() . tep_rand() . tep_rand());
        }

// check if paypal shipping address exists in the address book
        if ( OSCOM_APP_PAYPAL_GATEWAY == '1' ) { // PayPal
          $ship_firstname = HTML::sanitize(substr($_SESSION['appPayPalEcResult']['PAYMENTREQUEST_0_SHIPTONAME'], 0, strpos($_SESSION['appPayPalEcResult']['PAYMENTREQUEST_0_SHIPTONAME'], ' ')));
          $ship_lastname = HTML::sanitize(substr($_SESSION['appPayPalEcResult']['PAYMENTREQUEST_0_SHIPTONAME'], strpos($_SESSION['appPayPalEcResult']['PAYMENTREQUEST_0_SHIPTONAME'], ' ')+1));
          $ship_address = HTML::sanitize($_SESSION['appPayPalEcResult']['PAYMENTREQUEST_0_SHIPTOSTREET']);
          $ship_city = HTML::sanitize($_SESSION['appPayPalEcResult']['PAYMENTREQUEST_0_SHIPTOCITY']);
          $ship_zone = HTML::sanitize($_SESSION['appPayPalEcResult']['PAYMENTREQUEST_0_SHIPTOSTATE']);
          $ship_postcode = HTML::sanitize($_SESSION['appPayPalEcResult']['PAYMENTREQUEST_0_SHIPTOZIP']);
          $ship_country = HTML::sanitize($_SESSION['appPayPalEcResult']['PAYMENTREQUEST_0_SHIPTOCOUNTRYCODE']);
        } else { // Payflow
          $ship_firstname = HTML::sanitize(substr($_SESSION['appPayPalEcResult']['SHIPTONAME'], 0, strpos($_SESSION['appPayPalEcResult']['SHIPTONAME'], ' ')));
          $ship_lastname = HTML::sanitize(substr($_SESSION['appPayPalEcResult']['SHIPTONAME'], strpos($_SESSION['appPayPalEcResult']['SHIPTONAME'], ' ')+1));
          $ship_address = HTML::sanitize($_SESSION['appPayPalEcResult']['SHIPTOSTREET']);
          $ship_city = HTML::sanitize($_SESSION['appPayPalEcResult']['SHIPTOCITY']);
          $ship_zone = HTML::sanitize($_SESSION['appPayPalEcResult']['SHIPTOSTATE']);
          $ship_postcode = HTML::sanitize($_SESSION['appPayPalEcResult']['SHIPTOZIP']);
          $ship_country = HTML::sanitize($_SESSION['appPayPalEcResult']['SHIPTOCOUNTRY']);
        }

        $ship_zone_id = 0;
        $ship_country_id = 0;
        $ship_address_format_id = 1;

        $Qcountry = $OSCOM_Db->get('countries', ['countries_id', 'address_format_id'], ['countries_iso_code_2' => $ship_country], null, 1);

        if ($Qcountry->fetch() !== false) {
          $ship_country_id = $Qcountry->valueInt('countries_id');
          $ship_address_format_id = $Qcountry->valueInt('address_format_id');

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
        $Qcheck->bindInt(':customers_id', $_SESSION['customer_id']);
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
          $sql_data_array = array('customers_id' => $_SESSION['customer_id'],
                                  'entry_firstname' => $ship_firstname,
                                  'entry_lastname' => $ship_lastname,
                                  'entry_street_address' => $ship_address,
                                  'entry_postcode' => $ship_postcode,
                                  'entry_city' => $ship_city,
                                  'entry_country_id' => $ship_country_id,
                                  'entry_gender' => '');

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
            $OSCOM_Db->save('customers', ['customers_default_address_id' => $address_id], ['customers_id' => $_SESSION['customer_id']]);

            $_SESSION['customer_default_address_id'] = $address_id;
          }
        }

        $_SESSION['billto'] = $_SESSION['sendto'];

        if ( $force_login == true ) {
          $_SESSION['customer_country_id'] = $ship_country_id;
          $_SESSION['customer_zone_id'] = $ship_zone_id;
        }

        include(DIR_WS_CLASSES . 'order.php');
        $order = new order;

        if ($_SESSION['cart']->get_content_type() != 'virtual') {
          $total_weight = $_SESSION['cart']->show_weight();
          $total_count = $_SESSION['cart']->count_contents();

// load all enabled shipping modules
          include(DIR_WS_CLASSES . 'shipping.php');
          $shipping_modules = new shipping;

          $free_shipping = false;

          if ( defined('MODULE_ORDER_TOTAL_SHIPPING_FREE_SHIPPING') && (MODULE_ORDER_TOTAL_SHIPPING_FREE_SHIPPING == 'true') ) {
            $pass = false;

            switch (MODULE_ORDER_TOTAL_SHIPPING_DESTINATION) {
              case 'national':
                if ($order->delivery['country_id'] == STORE_COUNTRY) {
                  $pass = true;
                }
                break;

              case 'international':
                if ($order->delivery['country_id'] != STORE_COUNTRY) {
                  $pass = true;
                }
                break;

              case 'both':
                $pass = true;
                break;
            }

            if ( ($pass == true) && ($order->info['total'] >= MODULE_ORDER_TOTAL_SHIPPING_FREE_SHIPPING_OVER) ) {
              $free_shipping = true;

              include(DIR_WS_LANGUAGES . $_SESSION['language'] . '/modules/order_total/ot_shipping.php');
            }
          }

          $_SESSION['shipping'] = false;

          if ( (tep_count_shipping_modules() > 0) || ($free_shipping == true) ) {
            if ($free_shipping == true) {
              $_SESSION['shipping'] = 'free_free';
            } else {
// get all available shipping quotes
              $quotes = $shipping_modules->quote();

              $shipping_set = false;

              if ( (OSCOM_APP_PAYPAL_GATEWAY == '1') && (OSCOM_APP_PAYPAL_EC_INSTANT_UPDATE == '1') && ((OSCOM_APP_PAYPAL_EC_STATUS == '0') || ((OSCOM_APP_PAYPAL_EC_STATUS == '1') && (ENABLE_SSL == true))) && (OSCOM_APP_PAYPAL_EC_CHECKOUT_FLOW == '0') ) { // Live server requires SSL to be enabled
// if available, set the selected shipping rate from PayPals order review page
                if (isset($_SESSION['appPayPalEcResult']['SHIPPINGOPTIONNAME']) && isset($_SESSION['appPayPalEcResult']['SHIPPINGOPTIONAMOUNT'])) {
                  foreach ($quotes as $quote) {
                    if (!isset($quote['error'])) {
                      foreach ($quote['methods'] as $rate) {
                        if ($_SESSION['appPayPalEcResult']['SHIPPINGOPTIONNAME'] == trim($quote['module'] . ' ' . $rate['title'])) {
                          $shipping_rate = $paypal_express->_app->formatCurrencyRaw($rate['cost'] + tep_calculate_tax($rate['cost'], $quote['tax']));

                          if ($_SESSION['appPayPalEcResult']['SHIPPINGOPTIONAMOUNT'] == $shipping_rate) {
                            $_SESSION['shipping'] = $quote['id'] . '_' . $rate['id'];
                            $shipping_set = true;
                            break 2;
                          }
                        }
                      }
                    }
                  }
                }
              }

              if ($shipping_set == false) {
                if ( method_exists($shipping_modules, 'get_first') ) { // select first shipping method
                  $_SESSION['shipping'] = $shipping_modules->get_first();
                } else { // select cheapest shipping method
                  $_SESSION['shipping'] = $shipping_modules->cheapest();
                }

                $_SESSION['shipping'] = $_SESSION['shipping']['id'];
              }
            }
          } else {
            if ( defined('SHIPPING_ALLOW_UNDEFINED_ZONES') && (SHIPPING_ALLOW_UNDEFINED_ZONES == 'False') ) {
              unset($_SESSION['shipping']);

              $messageStack->add_session('checkout_address', $paypal_express->_app->getDef('module_ec_error_no_shipping_available'), 'error');

              $_SESSION['appPayPalEcRightTurn'] = true;

              OSCOM::redirect('checkout_shipping_address.php', '', 'SSL');
            }
          }

          if (strpos($_SESSION['shipping'], '_')) {
            list($module, $method) = explode('_', $_SESSION['shipping']);

            if ( is_object($$module) || ($_SESSION['shipping'] == 'free_free') ) {
              if ($_SESSION['shipping'] == 'free_free') {
                $quote[0]['methods'][0]['title'] = FREE_SHIPPING_TITLE;
                $quote[0]['methods'][0]['cost'] = '0';
              } else {
                $quote = $shipping_modules->quote($method, $module);
              }

              if (isset($quote['error'])) {
                unset($_SESSION['shipping']);

                OSCOM::redirect('checkout_shipping.php', '', 'SSL');
              } else {
                if ( (isset($quote[0]['methods'][0]['title'])) && (isset($quote[0]['methods'][0]['cost'])) ) {
                  $_SESSION['shipping'] = array('id' => $_SESSION['shipping'],
                                                'title' => (($free_shipping == true) ?  $quote[0]['methods'][0]['title'] : $quote[0]['module'] . ' ' . $quote[0]['methods'][0]['title']),
                                                'cost' => $quote[0]['methods'][0]['cost']);
                }
              }
            }
          }
        } else {
          $_SESSION['shipping'] = false;
          $_SESSION['sendto'] = false;
        }

        if ( isset($_SESSION['shipping']) ) {
          OSCOM::redirect('checkout_confirmation.php', '', 'SSL');
        } else {
          $_SESSION['appPayPalEcRightTurn'] = true;

          OSCOM::redirect('checkout_shipping.php', '', 'SSL');
        }
      } else {
        if ( OSCOM_APP_PAYPAL_GATEWAY == '1' ) { // PayPal
          $messageStack->add_session('header', stripslashes($_SESSION['appPayPalEcResult']['L_LONGMESSAGE0']), 'error');
        } else { // Payflow
          $messageStack->add_session('header', $_SESSION['appPayPalEcResult']['OSCOM_ERROR_MESSAGE'], 'error');
        }

        OSCOM::redirect('shopping_cart.php', '', 'SSL');
      }

      break;

    default:
// if there is nothing in the customers cart, redirect them to the shopping cart page
      if ( $_SESSION['cart']->count_contents() < 1 ) {
        OSCOM::redirect('shopping_cart.php', '', 'SSL');
      }

      if ( OSCOM_APP_PAYPAL_EC_STATUS == '1' ) {
        if ( (OSCOM_APP_PAYPAL_GATEWAY == '1') && (OSCOM_APP_PAYPAL_EC_CHECKOUT_FLOW == '1') ) {
          $paypal_url = 'https://www.paypal.com/checkoutnow?';
        } else {
          $paypal_url = 'https://www.paypal.com/cgi-bin/webscr?cmd=_express-checkout&';
        }
      } else {
        if ( (OSCOM_APP_PAYPAL_GATEWAY == '1') && (OSCOM_APP_PAYPAL_EC_CHECKOUT_FLOW == '1') ) {
          $paypal_url = 'https://www.sandbox.paypal.com/checkoutnow?';
        } else {
          $paypal_url = 'https://www.sandbox.paypal.com/cgi-bin/webscr?cmd=_express-checkout&';
        }
      }

      include(DIR_WS_CLASSES . 'order.php');
      $order = new order();

      $params = array();

      if ( OSCOM_APP_PAYPAL_GATEWAY == '1' ) { // PayPal
        $params['PAYMENTREQUEST_0_CURRENCYCODE'] = $order->info['currency'];
        $params['ALLOWNOTE'] = '0';
      } else { // Payflow
        $params['CURRENCY'] = $order->info['currency'];
        $params['EMAIL'] = $order->customer['email_address'];
        $params['ALLOWNOTE'] = '0';

        $params['BILLTOFIRSTNAME'] = $order->billing['firstname'];
        $params['BILLTOLASTNAME'] = $order->billing['lastname'];
        $params['BILLTOSTREET'] = $order->billing['street_address'];
        $params['BILLTOCITY'] = $order->billing['city'];
        $params['BILLTOSTATE'] = tep_get_zone_code($order->billing['country']['id'], $order->billing['zone_id'], $order->billing['state']);
        $params['BILLTOCOUNTRY'] = $order->billing['country']['iso_code_2'];
        $params['BILLTOZIP'] = $order->billing['postcode'];
      }

// A billing address is required for digital orders so we use the shipping address PayPal provides
//      if ($order->content_type == 'virtual') {
//        $params['NOSHIPPING'] = '1';
//      }

      $item_params = array();

      $line_item_no = 0;

      foreach ( $order->products as $product ) {
        if ( DISPLAY_PRICE_WITH_TAX == 'true' ) {
          $product_price = $paypal_express->_app->formatCurrencyRaw($product['final_price'] + tep_calculate_tax($product['final_price'], $product['tax']));
        } else {
          $product_price = $paypal_express->_app->formatCurrencyRaw($product['final_price']);
        }

        if ( OSCOM_APP_PAYPAL_GATEWAY == '1' ) { // PayPal
          $item_params['L_PAYMENTREQUEST_0_NAME' . $line_item_no] = $product['name'];
          $item_params['L_PAYMENTREQUEST_0_AMT' . $line_item_no] = $product_price;
          $item_params['L_PAYMENTREQUEST_0_NUMBER' . $line_item_no] = $product['id'];
          $item_params['L_PAYMENTREQUEST_0_QTY' . $line_item_no] = $product['qty'];
          $item_params['L_PAYMENTREQUEST_0_ITEMURL' . $line_item_no] = OSCOM::link('product_info.php', 'products_id=' . $product['id'], 'NONSSL', false);

          if ( (DOWNLOAD_ENABLED == 'true') && isset($product['attributes']) ) {
            $item_params['L_PAYMENTREQUEST_0_ITEMCATEGORY' . $line_item_no] = $paypal_express->getProductType($product['id'], $product['attributes']);
          } else {
            $item_params['L_PAYMENTREQUEST_0_ITEMCATEGORY' . $line_item_no] = 'Physical';
          }
        } else { // Payflow
          $item_params['L_NAME' . $line_item_no] = $product['name'];
          $item_params['L_COST' . $line_item_no] = $product_price;
          $item_params['L_QTY' . $line_item_no] = $product['qty'];
        }

        $line_item_no++;
      }

      if ( tep_not_null($order->delivery['street_address']) ) {
        if ( OSCOM_APP_PAYPAL_GATEWAY == '1' ) { // PayPal
          $params['PAYMENTREQUEST_0_SHIPTONAME'] = $order->delivery['firstname'] . ' ' . $order->delivery['lastname'];
          $params['PAYMENTREQUEST_0_SHIPTOSTREET'] = $order->delivery['street_address'];
          $params['PAYMENTREQUEST_0_SHIPTOCITY'] = $order->delivery['city'];
          $params['PAYMENTREQUEST_0_SHIPTOSTATE'] = tep_get_zone_code($order->delivery['country']['id'], $order->delivery['zone_id'], $order->delivery['state']);
          $params['PAYMENTREQUEST_0_SHIPTOCOUNTRYCODE'] = $order->delivery['country']['iso_code_2'];
          $params['PAYMENTREQUEST_0_SHIPTOZIP'] = $order->delivery['postcode'];
        } else { // Payflow
          $params['SHIPTONAME'] = $order->delivery['firstname'] . ' ' . $order->delivery['lastname'];
          $params['SHIPTOSTREET'] = $order->delivery['street_address'];
          $params['SHIPTOCITY'] = $order->delivery['city'];
          $params['SHIPTOSTATE'] = tep_get_zone_code($order->delivery['country']['id'], $order->delivery['zone_id'], $order->delivery['state']);
          $params['SHIPTOCOUNTRY'] = $order->delivery['country']['iso_code_2'];
          $params['SHIPTOZIP'] = $order->delivery['postcode'];
        }
      }

      $paypal_item_total = $paypal_express->_app->formatCurrencyRaw($order->info['subtotal']);

      if ( (OSCOM_APP_PAYPAL_GATEWAY == '1') && (OSCOM_APP_PAYPAL_EC_INSTANT_UPDATE == '1') && ((OSCOM_APP_PAYPAL_EC_STATUS == '0') || ((OSCOM_APP_PAYPAL_EC_STATUS == '1') && (ENABLE_SSL == true))) && (OSCOM_APP_PAYPAL_EC_CHECKOUT_FLOW == '0') ) { // Live server requires SSL to be enabled
        $quotes_array = array();

        if ( $_SESSION['cart']->get_content_type() != 'virtual' ) {
          $total_weight = $_SESSION['cart']->show_weight();
          $total_count = $_SESSION['cart']->count_contents();

// load all enabled shipping modules
          include(DIR_WS_CLASSES . 'shipping.php');
          $shipping_modules = new shipping();

          $free_shipping = false;

          if ( defined('MODULE_ORDER_TOTAL_SHIPPING_FREE_SHIPPING') && (MODULE_ORDER_TOTAL_SHIPPING_FREE_SHIPPING == 'true') ) {
            $pass = false;

            switch (MODULE_ORDER_TOTAL_SHIPPING_DESTINATION) {
              case 'national':
                if ($order->delivery['country_id'] == STORE_COUNTRY) {
                  $pass = true;
                }
                break;

              case 'international':
                if ($order->delivery['country_id'] != STORE_COUNTRY) {
                  $pass = true;
                }
                break;

              case 'both':
                $pass = true;
                break;
            }

            if ( ($pass == true) && ($order->info['total'] >= MODULE_ORDER_TOTAL_SHIPPING_FREE_SHIPPING_OVER) ) {
              $free_shipping = true;

              include(DIR_WS_LANGUAGES . $_SESSION['language'] . '/modules/order_total/ot_shipping.php');
            }
          }

          if ( (tep_count_shipping_modules() > 0) || ($free_shipping == true) ) {
            if ($free_shipping == true) {
              $quotes_array[] = array('id' => 'free_free',
                                      'name' => FREE_SHIPPING_TITLE,
                                      'label' => '',
                                      'cost' => '0.00',
                                      'tax' => '0');
            } else {
// get all available shipping quotes
              $quotes = $shipping_modules->quote();

              foreach ($quotes as $quote) {
                if (!isset($quote['error'])) {
                  foreach ($quote['methods'] as $rate) {
                    $quotes_array[] = array('id' => $quote['id'] . '_' . $rate['id'],
                                            'name' => $quote['module'],
                                            'label' => $rate['title'],
                                            'cost' => $rate['cost'],
                                            'tax' => (isset($quote['tax']) ? $quote['tax'] : null));
                  }
                }
              }
            }
          } else {
            if ( defined('SHIPPING_ALLOW_UNDEFINED_ZONES') && (SHIPPING_ALLOW_UNDEFINED_ZONES == 'False') ) {
              unset($_SESSION['shipping']);

              $messageStack->add_session('checkout_address', $paypal_express->_app->getDef('module_ec_error_no_shipping_available'), 'error');

              OSCOM::redirect('checkout_shipping_address.php', '', 'SSL');
            }
          }
        }

        $counter = 0;
        $cheapest_rate = null;
        $expensive_rate = 0;
        $cheapest_counter = $counter;
        $default_shipping = null;

        foreach ($quotes_array as $quote) {
          $shipping_rate = $paypal_express->_app->formatCurrencyRaw($quote['cost'] + tep_calculate_tax($quote['cost'], $quote['tax']));

          $item_params['L_SHIPPINGOPTIONNAME' . $counter] = trim($quote['name'] . ' ' . $quote['label']);
          $item_params['L_SHIPPINGOPTIONAMOUNT' . $counter] = $shipping_rate;
          $item_params['L_SHIPPINGOPTIONISDEFAULT' . $counter] = 'false';

          if (is_null($cheapest_rate) || ($shipping_rate < $cheapest_rate)) {
            $cheapest_rate = $shipping_rate;
            $cheapest_counter = $counter;
          }

          if ($shipping_rate > $expensive_rate) {
            $expensive_rate = $shipping_rate;
          }

          if (isset($_SESSION['shipping']) && ($_SESSION['shipping']['id'] == $quote['id'])) {
            $default_shipping = $counter;
          }

          $counter++;
        }

        if ( !isset($default_shipping) && !empty($quotes_array) ) {
          if ( method_exists($shipping_modules, 'get_first') ) { // select first shipping method
            $cheapest_counter = 0;
          }

          $_SESSION['shipping'] = array('id' => $quotes_array[$cheapest_counter]['id'],
                                        'title' => $item_params['L_SHIPPINGOPTIONNAME' . $cheapest_counter],
                                        'cost' => $paypal_express->_app->formatCurrencyRaw($quotes_array[$cheapest_counter]['cost']));

          $default_shipping = $cheapest_counter;
        }

        if ( !isset($default_shipping) ) {
          $_SESSION['shipping'] = false;
        } else {
          $item_params['PAYMENTREQUEST_0_INSURANCEOPTIONOFFERED'] = 'false';
          $item_params['L_SHIPPINGOPTIONISDEFAULT' . $default_shipping] = 'true';

// Instant Update
          $item_params['CALLBACK'] = OSCOM::link('public/apps/PayPal/Module/Payment/EC.php', 'osC_Action=callbackSet', 'SSL', false, false);
          $item_params['CALLBACKTIMEOUT'] = '6';
          $item_params['CALLBACKVERSION'] = $paypal_express->api_version;

// set shipping for order total calculations; shipping in $item_params includes taxes
          $order->info['shipping_method'] = $item_params['L_SHIPPINGOPTIONNAME' . $default_shipping];
          $order->info['shipping_cost'] = $item_params['L_SHIPPINGOPTIONAMOUNT' . $default_shipping];

          $order->info['total'] = $order->info['subtotal'] + $order->info['shipping_cost'];

          if ( DISPLAY_PRICE_WITH_TAX == 'false' ) {
            $order->info['total'] += $order->info['tax'];
          }
        }

        include(DIR_WS_CLASSES . 'order_total.php');
        $order_total_modules = new order_total;
        $order_totals = $order_total_modules->process();

// Remove shipping tax from total that was added again in ot_shipping
        if ( isset($default_shipping) ) {
          if (DISPLAY_PRICE_WITH_TAX == 'true') $order->info['shipping_cost'] = $order->info['shipping_cost'] / (1.0 + ($quotes_array[$default_shipping]['tax'] / 100));
          $module = substr($_SESSION['shipping']['id'], 0, strpos($_SESSION['shipping']['id'], '_'));
          $order->info['tax'] -= tep_calculate_tax($order->info['shipping_cost'], $quotes_array[$default_shipping]['tax']);
          $order->info['tax_groups'][tep_get_tax_description($GLOBALS[$module]->tax_class, $order->delivery['country']['id'], $order->delivery['zone_id'])] -= tep_calculate_tax($order->info['shipping_cost'], $quotes_array[$default_shipping]['tax']);
          $order->info['total'] -= tep_calculate_tax($order->info['shipping_cost'], $quotes_array[$default_shipping]['tax']);
        }

        $items_total = $paypal_express->_app->formatCurrencyRaw($order->info['subtotal']);

        foreach ($order_totals as $ot) {
          if ( !in_array($ot['code'], array('ot_subtotal', 'ot_shipping', 'ot_tax', 'ot_total')) ) {
            $item_params['L_PAYMENTREQUEST_0_NAME' . $line_item_no] = $ot['title'];
            $item_params['L_PAYMENTREQUEST_0_AMT' . $line_item_no] = $paypal_express->_app->formatCurrencyRaw($ot['value']);

            $items_total += $paypal_express->_app->formatCurrencyRaw($ot['value']);

            $line_item_no++;
          }
        }

        $params['PAYMENTREQUEST_0_AMT'] = $paypal_express->_app->formatCurrencyRaw($order->info['total']);

        $item_params['MAXAMT'] = $paypal_express->_app->formatCurrencyRaw($params['PAYMENTREQUEST_0_AMT'] + $expensive_rate + 100, '', 1); // safely pad higher for dynamic shipping rates (eg, USPS express)
        $item_params['PAYMENTREQUEST_0_ITEMAMT'] = $items_total;
        $item_params['PAYMENTREQUEST_0_SHIPPINGAMT'] = $paypal_express->_app->formatCurrencyRaw($order->info['shipping_cost']);

        $paypal_item_total = $item_params['PAYMENTREQUEST_0_ITEMAMT'] + $item_params['PAYMENTREQUEST_0_SHIPPINGAMT'];

        if ( DISPLAY_PRICE_WITH_TAX == 'false' ) {
          $item_params['PAYMENTREQUEST_0_TAXAMT'] = $paypal_express->_app->formatCurrencyRaw($order->info['tax']);

          $paypal_item_total += $item_params['PAYMENTREQUEST_0_TAXAMT'];
        }
      } else {
        if ( OSCOM_APP_PAYPAL_GATEWAY == '1' ) { // PayPal
          $params['PAYMENTREQUEST_0_AMT'] = $paypal_item_total;
        } else { // Payflow
          $params['AMT'] = $paypal_item_total;
        }
      }

      if ( OSCOM_APP_PAYPAL_GATEWAY == '1' ) { // PayPal
        if ( $paypal_express->_app->formatCurrencyRaw($paypal_item_total) == $params['PAYMENTREQUEST_0_AMT'] ) {
          $params = array_merge($params, $item_params);
        }
      } else { // Payflow
        if ( $paypal_express->_app->formatCurrencyRaw($paypal_item_total) == $params['AMT'] ) {
          $params = array_merge($params, $item_params);
        }
      }

      if ( tep_not_null(OSCOM_APP_PAYPAL_EC_PAGE_STYLE) && (OSCOM_APP_PAYPAL_EC_CHECKOUT_FLOW == '0') ) {
        $params['PAGESTYLE'] = OSCOM_APP_PAYPAL_EC_PAGE_STYLE;
      }

      $_SESSION['appPayPalEcSecret'] = tep_create_random_value(16, 'digits');

      if ( OSCOM_APP_PAYPAL_GATEWAY == '1' ) { // PayPal
        $params['PAYMENTREQUEST_0_CUSTOM'] = $_SESSION['appPayPalEcSecret'];

// Log In with PayPal token for seamless checkout
        if (isset($_SESSION['paypal_login_access_token'])) {
          $params['IDENTITYACCESSTOKEN'] = $_SESSION['paypal_login_access_token'];
        }

        $response_array = $paypal_express->_app->getApiResult('EC', 'SetExpressCheckout', $params);

        if ( in_array($response_array['ACK'], array('Success', 'SuccessWithWarning')) ) {
          HTTP::redirect($paypal_url . 'token=' . $response_array['TOKEN']);
        } else {
          OSCOM::redirect('shopping_cart.php', 'error_message=' . stripslashes($response_array['L_LONGMESSAGE0']), 'SSL');
        }
      } else { // Payflow
        $params['CUSTOM'] = $_SESSION['appPayPalEcSecret'];

        $params['_headers'] = array('X-VPS-REQUEST-ID: ' . md5($_SESSION['cartID'] . session_id() . $paypal_express->_app->formatCurrencyRaw($paypal_item_total)),
                                    'X-VPS-CLIENT-TIMEOUT: 45',
                                    'X-VPS-VIT-INTEGRATION-PRODUCT: OSCOM',
                                    'X-VPS-VIT-INTEGRATION-VERSION: 2.3');

        $response_array = $paypal_express->_app->getApiResult('EC', 'PayflowSetExpressCheckout', $params);

        if ( $response_array['RESULT'] == '0' ) {
          HTTP::redirect($paypal_url . 'token=' . $response_array['TOKEN']);
        } else {
          OSCOM::redirect('shopping_cart.php', 'error_message=' . urlencode($response_array['OSCOM_ERROR_MESSAGE']), 'SSL');
        }
      }

      break;
  }

  OSCOM::redirect('shopping_cart.php', '', 'SSL');

  require(DIR_WS_INCLUDES . 'application_bottom.php');
?>
