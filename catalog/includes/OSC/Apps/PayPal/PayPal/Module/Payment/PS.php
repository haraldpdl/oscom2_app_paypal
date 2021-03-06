<?php
/**
  * PayPal App for osCommerce Online Merchant
  *
  * @copyright (c) 2016 osCommerce; https://www.oscommerce.com
  * @license MIT; https://www.oscommerce.com/license/mit.txt
  */

  namespace OSC\Apps\PayPal\PayPal\Module\Payment;

  use OSC\OM\HTML;
  use OSC\OM\Mail;
  use OSC\OM\OSCOM;
  use OSC\OM\Registry;

  use OSC\Apps\PayPal\PayPal\PayPal as PayPalApp;

  class PS implements \OSC\OM\Modules\PaymentInterface {
    public $code, $title, $description, $enabled, $app;

    protected $lang;

    function __construct() {
      global $PHP_SELF, $order;

      $this->lang = Registry::get('Language');

      if (!Registry::exists('PayPal')) {
        Registry::set('PayPal', new PayPalApp());
      }

      $this->app = Registry::get('PayPal');
      $this->app->loadDefinitions('modules/PS/PS');

      $this->signature = 'paypal|paypal_standard|' . $this->app->getVersion() . '|2.4';
      $this->api_version = $this->app->getApiVersion();

      $this->code = 'PS';
      $this->title = $this->app->getDef('module_ps_title');
      $this->public_title = $this->app->getDef('module_ps_public_title');
      $this->description = '<div align="center">' . HTML::button($this->app->getDef('module_ps_legacy_admin_app_button'), null, $this->app->link('Configure&module=PS'), null, 'btn-primary') . '</div>';
      $this->sort_order = defined('OSCOM_APP_PAYPAL_PS_SORT_ORDER') ? OSCOM_APP_PAYPAL_PS_SORT_ORDER : 0;
      $this->enabled = defined('OSCOM_APP_PAYPAL_PS_STATUS') && in_array(OSCOM_APP_PAYPAL_PS_STATUS, array('1', '0')) ? true : false;
      $this->order_status = defined('OSCOM_APP_PAYPAL_PS_PREPARE_ORDER_STATUS_ID') && ((int)OSCOM_APP_PAYPAL_PS_PREPARE_ORDER_STATUS_ID > 0) ? (int)OSCOM_APP_PAYPAL_PS_PREPARE_ORDER_STATUS_ID : 0;

      if ( defined('OSCOM_APP_PAYPAL_PS_STATUS') ) {
        if ( OSCOM_APP_PAYPAL_PS_STATUS == '0' ) {
          $this->title .= ' [Sandbox]';
          $this->public_title .= ' (' . $this->app->vendor . '\\' . $this->app->code . '\\' . $this->code . '; Sandbox)';
        }

        if ( OSCOM_APP_PAYPAL_PS_STATUS == '1' ) {
          $this->form_action_url = 'https://www.paypal.com/cgi-bin/webscr';
        } else {
          $this->form_action_url = 'https://www.sandbox.paypal.com/cgi-bin/webscr';
        }
      }

      if ( !function_exists('curl_init') ) {
        $this->description .= '<div class="secWarning">' . $this->app->getDef('module_ps_error_curl') . '</div>';

        $this->enabled = false;
      }

      if ( $this->enabled === true ) {
        if ( !$this->app->hasCredentials('PS', 'email') ) {
          $this->description .= '<div class="secWarning">' . $this->app->getDef('module_ps_error_credentials') . '</div>';

          $this->enabled = false;
        }
      }

      if ( $this->enabled === true ) {
        if ( isset($order) && is_object($order) ) {
          $this->update_status();
        }
      }

// Before the stock quantity check is performed in checkout_process.php, detect if the quantity
// has already beed deducated in the IPN to avoid a quantity == 0 redirect
      if ( $this->enabled === true ) {
        if ( basename($PHP_SELF) == 'checkout_process.php' ) {
          if ( isset($_SESSION['payment']) && ($_SESSION['payment'] == $this->app->vendor . '\\' . $this->app->code . '\\' . $this->code) ) {
            $this->pre_before_check();
          }
        }
      }
    }

    function update_status() {
      global $order;

      if ( ($this->enabled == true) && ((int)OSCOM_APP_PAYPAL_PS_ZONE > 0) ) {
        $check_flag = false;

        $Qcheck = $this->app->db->get('zones_to_geo_zones', 'zone_id', ['geo_zone_id' => OSCOM_APP_PAYPAL_PS_ZONE, 'zone_country_id' => $order->billing['country']['id']], 'zone_id');

        while ($Qcheck->fetch()) {
          if (($Qcheck->valueInt('zone_id') < 1) || ($Qcheck->valueInt('zone_id') == $order->billing['zone_id'])) {
            $check_flag = true;
            break;
          }
        }

        if ($check_flag == false) {
          $this->enabled = false;
        }
      }
    }

    function javascript_validation() {
      return false;
    }

    function selection() {
      if (isset($_SESSION['cart_PayPal_Standard_ID'])) {
        $order_id = substr($_SESSION['cart_PayPal_Standard_ID'], strpos($_SESSION['cart_PayPal_Standard_ID'], '-')+1);

        $Qcheck = $this->app->db->get('orders_status_history', 'orders_id', ['orders_id' => $order_id], null, 1);

        if ($Qcheck->fetch() === false) {
          $this->app->db->delete('orders', ['orders_id' => $order_id]);
          $this->app->db->delete('orders_total', ['orders_id' => $order_id]);
          $this->app->db->delete('orders_products', ['orders_id' => $order_id]);
          $this->app->db->delete('orders_products_attributes', ['orders_id' => $order_id]);
          $this->app->db->delete('orders_products_download', ['orders_id' => $order_id]);

          unset($_SESSION['cart_PayPal_Standard_ID']);
        }
      }

      return array('id' => $this->app->vendor . '\\' . $this->app->code . '\\' . $this->code,
                   'module' => $this->public_title);
    }

    function pre_confirmation_check() {
      global $order;

      if (empty($_SESSION['cart']->cartID)) {
        $_SESSION['cartID'] = $_SESSION['cart']->cartID = $_SESSION['cart']->generate_cart_id();
      }

      $order->info['payment_method_raw'] = $order->info['payment_method'];
      $order->info['payment_method'] = '<img src="https://www.paypalobjects.com/webstatic/mktg/Logo/pp-logo-100px.png" border="0" alt="PayPal Logo" style="padding: 3px;" />';
    }

    function confirmation() {
      global $order, $order_total_modules;

      if (isset($_SESSION['cartID'])) {
        $insert_order = false;

        if (isset($_SESSION['cart_PayPal_Standard_ID'])) {
          $order_id = substr($_SESSION['cart_PayPal_Standard_ID'], strpos($_SESSION['cart_PayPal_Standard_ID'], '-')+1);

          $Qorder = $this->app->db->get('orders', 'currency', ['orders_id' => $order_id]);

          if ( ($Qorder->value('currency') != $order->info['currency']) || ($_SESSION['cartID'] != substr($_SESSION['cart_PayPal_Standard_ID'], 0, strlen($_SESSION['cartID']))) ) {
            $Qcheck = $this->app->db->get('orders_status_history', 'orders_id', ['orders_id' => $order_id], null, 1);

            if ($Qcheck->fetch() === false) {
              $this->app->db->delete('orders', ['orders_id' => $order_id]);
              $this->app->db->delete('orders_total', ['orders_id' => $order_id]);
              $this->app->db->delete('orders_products', ['orders_id' => $order_id]);
              $this->app->db->delete('orders_products_attributes', ['orders_id' => $order_id]);
              $this->app->db->delete('orders_products_download', ['orders_id' => $order_id]);
            }

            $insert_order = true;
          }
        } else {
          $insert_order = true;
        }

        if ($insert_order == true) {
          $order_totals = array();
          if (is_array($order_total_modules->modules)) {
            foreach ($order_total_modules->modules as $value) {
              $class = substr($value, 0, strrpos($value, '.'));
              if ($GLOBALS[$class]->enabled) {
                for ($i=0, $n=sizeof($GLOBALS[$class]->output); $i<$n; $i++) {
                  if (tep_not_null($GLOBALS[$class]->output[$i]['title']) && tep_not_null($GLOBALS[$class]->output[$i]['text'])) {
                    $order_totals[] = array('code' => $GLOBALS[$class]->code,
                                            'title' => $GLOBALS[$class]->output[$i]['title'],
                                            'text' => $GLOBALS[$class]->output[$i]['text'],
                                            'value' => $GLOBALS[$class]->output[$i]['value'],
                                            'sort_order' => $GLOBALS[$class]->sort_order);
                  }
                }
              }
            }
          }

          if ( isset($order->info['payment_method_raw']) ) {
            $order->info['payment_method'] = $order->info['payment_method_raw'];
            unset($order->info['payment_method_raw']);
          }

          $sql_data_array = array('customers_id' => $_SESSION['customer_id'],
                                  'customers_name' => $order->customer['firstname'] . ' ' . $order->customer['lastname'],
                                  'customers_company' => $order->customer['company'],
                                  'customers_street_address' => $order->customer['street_address'],
                                  'customers_suburb' => $order->customer['suburb'],
                                  'customers_city' => $order->customer['city'],
                                  'customers_postcode' => $order->customer['postcode'],
                                  'customers_state' => $order->customer['state'],
                                  'customers_country' => $order->customer['country']['title'],
                                  'customers_telephone' => $order->customer['telephone'],
                                  'customers_email_address' => $order->customer['email_address'],
                                  'customers_address_format_id' => $order->customer['format_id'],
                                  'delivery_name' => $order->delivery['firstname'] . ' ' . $order->delivery['lastname'],
                                  'delivery_company' => $order->delivery['company'],
                                  'delivery_street_address' => $order->delivery['street_address'],
                                  'delivery_suburb' => $order->delivery['suburb'],
                                  'delivery_city' => $order->delivery['city'],
                                  'delivery_postcode' => $order->delivery['postcode'],
                                  'delivery_state' => $order->delivery['state'],
                                  'delivery_country' => $order->delivery['country']['title'],
                                  'delivery_address_format_id' => $order->delivery['format_id'],
                                  'billing_name' => $order->billing['firstname'] . ' ' . $order->billing['lastname'],
                                  'billing_company' => $order->billing['company'],
                                  'billing_street_address' => $order->billing['street_address'],
                                  'billing_suburb' => $order->billing['suburb'],
                                  'billing_city' => $order->billing['city'],
                                  'billing_postcode' => $order->billing['postcode'],
                                  'billing_state' => $order->billing['state'],
                                  'billing_country' => $order->billing['country']['title'],
                                  'billing_address_format_id' => $order->billing['format_id'],
                                  'payment_method' => $order->info['payment_method'],
                                  'cc_type' => $order->info['cc_type'],
                                  'cc_owner' => $order->info['cc_owner'],
                                  'cc_number' => $order->info['cc_number'],
                                  'cc_expires' => $order->info['cc_expires'],
                                  'date_purchased' => 'now()',
                                  'orders_status' => $order->info['order_status'],
                                  'currency' => $order->info['currency'],
                                  'currency_value' => $order->info['currency_value']);

          $this->app->db->save('orders', $sql_data_array);

          $insert_id = $this->app->db->lastInsertId();

          for ($i=0, $n=sizeof($order_totals); $i<$n; $i++) {
            $sql_data_array = array('orders_id' => $insert_id,
                                    'title' => $order_totals[$i]['title'],
                                    'text' => $order_totals[$i]['text'],
                                    'value' => $order_totals[$i]['value'],
                                    'class' => $order_totals[$i]['code'],
                                    'sort_order' => $order_totals[$i]['sort_order']);

            $this->app->db->save('orders_total', $sql_data_array);
          }

          for ($i=0, $n=sizeof($order->products); $i<$n; $i++) {
            $sql_data_array = array('orders_id' => $insert_id,
                                    'products_id' => tep_get_prid($order->products[$i]['id']),
                                    'products_model' => $order->products[$i]['model'],
                                    'products_name' => $order->products[$i]['name'],
                                    'products_price' => $order->products[$i]['price'],
                                    'final_price' => $order->products[$i]['final_price'],
                                    'products_tax' => $order->products[$i]['tax'],
                                    'products_quantity' => $order->products[$i]['qty']);

            $this->app->db->save('orders_products', $sql_data_array);

            $order_products_id = $this->app->db->lastInsertId();

            $attributes_exist = '0';
            if (isset($order->products[$i]['attributes'])) {
              $attributes_exist = '1';
              for ($j=0, $n2=sizeof($order->products[$i]['attributes']); $j<$n2; $j++) {
                if (DOWNLOAD_ENABLED == 'true') {
                  $attributes_query = 'select popt.products_options_name, poval.products_options_values_name, pa.options_values_price, pa.price_prefix, pad.products_attributes_maxdays, pad.products_attributes_maxcount, pad.products_attributes_filename
                                       from :table_products_options popt, :table_products_options_values poval, :table_products_attributes pa
                                       left join :table_products_attributes_download pad on pa.products_attributes_id = pad.products_attributes_id
                                       where pa.products_id = :products_id
                                       and pa.options_id = :options_id
                                       and pa.options_id = popt.products_options_id
                                       and pa.options_values_id = :options_values_id
                                       and pa.options_values_id = poval.products_options_values_id
                                       and popt.language_id = :language_id
                                       and popt.language_id = poval.language_id';
                } else {
                  $attributes_query = 'select popt.products_options_name, poval.products_options_values_name, pa.options_values_price, pa.price_prefix
                                       from :table_products_options popt, :table_products_options_values poval, :table_products_attributes pa
                                       where pa.products_id = :products_id
                                       and pa.options_id = :options_id
                                       and pa.options_id = popt.products_options_id
                                       and pa.options_values_id = :options_values_id
                                       and pa.options_values_id = poval.products_options_values_id
                                       and popt.language_id = :language_id
                                       and popt.language_id = poval.language_id';
                }

                $Qattributes = $this->app->db->prepare($attributes_query);
                $Qattributes->bindInt(':products_id', $order->products[$i]['id']);
                $Qattributes->bindInt(':options_id', $order->products[$i]['attributes'][$j]['option_id']);
                $Qattributes->bindInt(':options_values_id', $order->products[$i]['attributes'][$j]['value_id']);
                $Qattributes->bindInt(':language_id', $this->app->lang->getId());
                $Qattributes->execute();

                $sql_data_array = array('orders_id' => $insert_id,
                                        'orders_products_id' => $order_products_id,
                                        'products_options' => $Qattributes->value('products_options_name'),
                                        'products_options_values' => $Qattributes->value('products_options_values_name'),
                                        'options_values_price' => $Qattributes->value('options_values_price'),
                                        'price_prefix' => $Qattributes->value('price_prefix'));

                $this->app->db->save('orders_products_attributes', $sql_data_array);

                if ((DOWNLOAD_ENABLED == 'true') && $Qattributes->hasValue('products_attributes_filename') && !empty($Qattributes->value('products_attributes_filename'))) {
                  $sql_data_array = array('orders_id' => $insert_id,
                                          'orders_products_id' => $order_products_id,
                                          'orders_products_filename' => $Qattributes->value('products_attributes_filename'),
                                          'download_maxdays' => $Qattributes->value('products_attributes_maxdays'),
                                          'download_count' => $Qattributes->value('products_attributes_maxcount'));

                  $this->app->db->save('orders_products_download', $sql_data_array);
                }
              }
            }
          }

          $_SESSION['cart_PayPal_Standard_ID'] = $_SESSION['cartID'] . '-' . $insert_id;
        }
      }

      return false;
    }

    function process_button() {
      global $order, $order_total_modules;

      $total_tax = $order->info['tax'];

// remove shipping tax in total tax value
      if ( isset($_SESSION['shipping']['cost']) ) {
        $total_tax -= ($order->info['shipping_cost'] - $_SESSION['shipping']['cost']);
      }

      $process_button_string = '';
      $parameters = array('cmd' => '_cart',
                          'upload' => '1',
                          'item_name_1' => STORE_NAME,
                          'shipping_1' => $this->app->formatCurrencyRaw($order->info['shipping_cost']),
                          'business' => $this->app->getCredentials('PS', 'email'),
                          'amount_1' => $this->app->formatCurrencyRaw($order->info['total'] - $order->info['shipping_cost'] - $total_tax),
                          'currency_code' => $_SESSION['currency'],
                          'invoice' => substr($_SESSION['cart_PayPal_Standard_ID'], strpos($_SESSION['cart_PayPal_Standard_ID'], '-')+1),
                          'custom' => $_SESSION['customer_id'],
                          'no_note' => '1',
                          'notify_url' => OSCOM::link('index.php', 'order&ipn&paypal&ps&language=' . $this->lang->get('code'), false, false),
                          'rm' => '2',
                          'return' => OSCOM::link('checkout_process.php'),
                          'cancel_return' => OSCOM::link('checkout_payment.php'),
                          'bn' => $this->app->getIdentifier(),
                          'paymentaction' => (OSCOM_APP_PAYPAL_PS_TRANSACTION_METHOD == '1') ? 'sale' : 'authorization');

      $return_link_title = $this->app->getDef('module_ps_button_return_to_store', [
        'storename' => STORE_NAME
      ]);

      if ( strlen($return_link_title) <= 60 ) {
        $parameters['cbt'] = $return_link_title;
      }

      if (is_numeric($_SESSION['sendto']) && ($_SESSION['sendto'] > 0)) {
        $parameters['address_override'] = '1';
        $parameters['first_name'] = $order->delivery['firstname'];
        $parameters['last_name'] = $order->delivery['lastname'];
        $parameters['address1'] = $order->delivery['street_address'];
        $parameters['city'] = $order->delivery['city'];
        $parameters['state'] = tep_get_zone_code($order->delivery['country']['id'], $order->delivery['zone_id'], $order->delivery['state']);
        $parameters['zip'] = $order->delivery['postcode'];
        $parameters['country'] = $order->delivery['country']['iso_code_2'];
      } else {
        $parameters['no_shipping'] = '1';
        $parameters['first_name'] = $order->billing['firstname'];
        $parameters['last_name'] = $order->billing['lastname'];
        $parameters['address1'] = $order->billing['street_address'];
        $parameters['city'] = $order->billing['city'];
        $parameters['state'] = tep_get_zone_code($order->billing['country']['id'], $order->billing['zone_id'], $order->billing['state']);
        $parameters['zip'] = $order->billing['postcode'];
        $parameters['country'] = $order->billing['country']['iso_code_2'];
      }

      if (tep_not_null(OSCOM_APP_PAYPAL_PS_PAGE_STYLE)) {
        $parameters['page_style'] = OSCOM_APP_PAYPAL_PS_PAGE_STYLE;
      }

      $item_params = array();

      $line_item_no = 1;

      foreach ($order->products as $product) {
        if ( DISPLAY_PRICE_WITH_TAX == 'true' ) {
          $product_price = $this->app->formatCurrencyRaw($product['final_price'] + tep_calculate_tax($product['final_price'], $product['tax']));
        } else {
          $product_price = $this->app->formatCurrencyRaw($product['final_price']);
        }

        $item_params['item_name_' . $line_item_no] = $product['name'];
        $item_params['amount_' . $line_item_no] = $product_price;
        $item_params['quantity_' . $line_item_no] = $product['qty'];

        $line_item_no++;
      }

      $items_total = $this->app->formatCurrencyRaw($order->info['subtotal']);

      $has_negative_price = false;

// order totals are processed on checkout confirmation but not captured into a variable
      if (is_array($order_total_modules->modules)) {
        foreach ($order_total_modules->modules as $value) {
          $class = substr($value, 0, strrpos($value, '.'));

          if ($GLOBALS[$class]->enabled) {
            for ($i=0, $n=sizeof($GLOBALS[$class]->output); $i<$n; $i++) {
              if (tep_not_null($GLOBALS[$class]->output[$i]['title']) && tep_not_null($GLOBALS[$class]->output[$i]['text'])) {
                if ( !in_array($GLOBALS[$class]->code, array('ot_subtotal', 'ot_shipping', 'ot_tax', 'ot_total')) ) {
                  $item_params['item_name_' . $line_item_no] = $GLOBALS[$class]->output[$i]['title'];
                  $item_params['amount_' . $line_item_no] = $this->app->formatCurrencyRaw($GLOBALS[$class]->output[$i]['value']);

                  $items_total += $item_params['amount_' . $line_item_no];

                  if ( $item_params['amount_' . $line_item_no] < 0 ) {
                    $has_negative_price = true;
                  }

                  $line_item_no++;
                }
              }
            }
          }
        }
      }

      $paypal_item_total = $items_total + $parameters['shipping_1'];

      if ( DISPLAY_PRICE_WITH_TAX == 'false' ) {
        $item_params['tax_cart'] = $this->app->formatCurrencyRaw($total_tax);

        $paypal_item_total += $item_params['tax_cart'];
      }

      if ( ($has_negative_price == false) && ($this->app->formatCurrencyRaw($paypal_item_total) == $this->app->formatCurrencyRaw($order->info['total'])) ) {
        $parameters = array_merge($parameters, $item_params);
      } else {
        $parameters['tax_cart'] = $this->app->formatCurrencyRaw($total_tax);
      }

      if ( OSCOM_APP_PAYPAL_PS_EWP_STATUS == '1' ) {
        $parameters['cert_id'] = OSCOM_APP_PAYPAL_PS_EWP_PUBLIC_CERT_ID;

        $random_string = rand(100000, 999999) . '-' . $_SESSION['customer_id'] . '-';

        $data = '';
        foreach ($parameters as $key => $value) {
          $data .= $key . '=' . $value . "\n";
        }

        $fp = fopen(OSCOM_APP_PAYPAL_PS_EWP_WORKING_DIRECTORY . '/' . $random_string . 'data.txt', 'w');
        fwrite($fp, $data);
        fclose($fp);

        unset($data);

        if (function_exists('openssl_pkcs7_sign') && function_exists('openssl_pkcs7_encrypt')) {
          openssl_pkcs7_sign(OSCOM_APP_PAYPAL_PS_EWP_WORKING_DIRECTORY . '/' . $random_string . 'data.txt', OSCOM_APP_PAYPAL_PS_EWP_WORKING_DIRECTORY . '/' . $random_string . 'signed.txt', file_get_contents(OSCOM_APP_PAYPAL_PS_EWP_PUBLIC_CERT), file_get_contents(OSCOM_APP_PAYPAL_PS_EWP_PRIVATE_KEY), array('From' => $this->app->getCredentials('PS', 'email')), PKCS7_BINARY);

          unlink(OSCOM_APP_PAYPAL_PS_EWP_WORKING_DIRECTORY . '/' . $random_string . 'data.txt');

// remove headers from the signature
          $signed = file_get_contents(OSCOM_APP_PAYPAL_PS_EWP_WORKING_DIRECTORY . '/' . $random_string . 'signed.txt');
          $signed = explode("\n\n", $signed);
          $signed = base64_decode($signed[1]);

          $fp = fopen(OSCOM_APP_PAYPAL_PS_EWP_WORKING_DIRECTORY . '/' . $random_string . 'signed.txt', 'w');
          fwrite($fp, $signed);
          fclose($fp);

          unset($signed);

          openssl_pkcs7_encrypt(OSCOM_APP_PAYPAL_PS_EWP_WORKING_DIRECTORY . '/' . $random_string . 'signed.txt', OSCOM_APP_PAYPAL_PS_EWP_WORKING_DIRECTORY . '/' . $random_string . 'encrypted.txt', file_get_contents(OSCOM_APP_PAYPAL_PS_EWP_PAYPAL_CERT), array('From' => $this->app->getCredentials('PS', 'email')), PKCS7_BINARY);

          unlink(OSCOM_APP_PAYPAL_PS_EWP_WORKING_DIRECTORY . '/' . $random_string . 'signed.txt');

// remove headers from the encrypted result
          $data = file_get_contents(OSCOM_APP_PAYPAL_PS_EWP_WORKING_DIRECTORY . '/' . $random_string . 'encrypted.txt');
          $data = explode("\n\n", $data);
          $data = '-----BEGIN PKCS7-----' . "\n" . $data[1] . "\n" . '-----END PKCS7-----';

          unlink(OSCOM_APP_PAYPAL_PS_EWP_WORKING_DIRECTORY . '/' . $random_string . 'encrypted.txt');
        } else {
          exec(OSCOM_APP_PAYPAL_PS_EWP_OPENSSL . ' smime -sign -in ' . OSCOM_APP_PAYPAL_PS_EWP_WORKING_DIRECTORY . '/' . $random_string . 'data.txt -signer ' . OSCOM_APP_PAYPAL_PS_EWP_PUBLIC_CERT . ' -inkey ' . OSCOM_APP_PAYPAL_PS_EWP_PRIVATE_KEY . ' -outform der -nodetach -binary > ' . OSCOM_APP_PAYPAL_PS_EWP_WORKING_DIRECTORY . '/' . $random_string . 'signed.txt');
          unlink(OSCOM_APP_PAYPAL_PS_EWP_WORKING_DIRECTORY . '/' . $random_string . 'data.txt');

          exec(OSCOM_APP_PAYPAL_PS_EWP_OPENSSL . ' smime -encrypt -des3 -binary -outform pem ' . OSCOM_APP_PAYPAL_PS_EWP_PAYPAL_CERT . ' < ' . OSCOM_APP_PAYPAL_PS_EWP_WORKING_DIRECTORY . '/' . $random_string . 'signed.txt > ' . OSCOM_APP_PAYPAL_PS_EWP_WORKING_DIRECTORY . '/' . $random_string . 'encrypted.txt');
          unlink(OSCOM_APP_PAYPAL_PS_EWP_WORKING_DIRECTORY . '/' . $random_string . 'signed.txt');

          $fh = fopen(OSCOM_APP_PAYPAL_PS_EWP_WORKING_DIRECTORY . '/' . $random_string . 'encrypted.txt', 'rb');
          $data = fread($fh, filesize(OSCOM_APP_PAYPAL_PS_EWP_WORKING_DIRECTORY . '/' . $random_string . 'encrypted.txt'));
          fclose($fh);

          unlink(OSCOM_APP_PAYPAL_PS_EWP_WORKING_DIRECTORY . '/' . $random_string . 'encrypted.txt');
        }

        $process_button_string = HTML::hiddenField('cmd', '_s-xclick') .
                                 HTML::hiddenField('encrypted', $data);

        unset($data);
      } else {
        foreach ($parameters as $key => $value) {
          $process_button_string .= HTML::hiddenField($key, $value);
        }
      }

      return $process_button_string;
    }

    function pre_before_check() {
      global $messageStack, $order_id;

      $result = false;

      $pptx_params = array();

      $seller_accounts = array($this->app->getCredentials('PS', 'email'));

      if ( tep_not_null($this->app->getCredentials('PS', 'email_primary')) ) {
        $seller_accounts[] = $this->app->getCredentials('PS', 'email_primary');
      }

      if ( isset($_POST['receiver_email']) && in_array($_POST['receiver_email'], $seller_accounts) ) {
        $parameters = 'cmd=_notify-validate&';

        foreach ( $_POST as $key => $value ) {
          if ( $key != 'cmd' ) {
            $parameters .= $key . '=' . urlencode(stripslashes($value)) . '&';
          }
        }

        $parameters = substr($parameters, 0, -1);

        $result = $this->app->makeApiCall($this->form_action_url, $parameters);

        $pptx_params = $_POST;
        $pptx_params['cmd'] = '_notify-validate';

        foreach ( $_GET as $key => $value ) {
          $pptx_params['GET ' . $key] = $value;
        }

        $this->app->log('PS', $pptx_params['cmd'], ($result == 'VERIFIED') ? 1 : -1, $pptx_params, $result, (OSCOM_APP_PAYPAL_PS_STATUS == '1') ? 'live' : 'sandbox');
      } elseif ( isset($_GET['tx']) ) { // PDT
        if ( tep_not_null(OSCOM_APP_PAYPAL_PS_PDT_IDENTITY_TOKEN) ) {
          $pptx_params['cmd'] = '_notify-synch';

          $parameters = 'cmd=_notify-synch&tx=' . urlencode($_GET['tx']) . '&at=' . urlencode(OSCOM_APP_PAYPAL_PS_PDT_IDENTITY_TOKEN);

          $pdt_raw = $this->app->makeApiCall($this->form_action_url, $parameters);

          if ( !empty($pdt_raw) ) {
            $pdt = explode("\n", trim($pdt_raw));

            if ( isset($pdt[0]) ) {
              if ( $pdt[0] == 'SUCCESS' ) {
                $result = 'VERIFIED';

                unset($pdt[0]);
              } else {
                $result = $pdt_raw;
              }
            }

            if ( is_array($pdt) && !empty($pdt) ) {
              foreach ( $pdt as $line ) {
                $p = explode('=', $line, 2);

                if ( count($p) === 2 ) {
                  $pptx_params[trim($p[0])] = trim(urldecode($p[1]));
                }
              }
            }
          }

          foreach ( $_GET as $key => $value ) {
            $pptx_params['GET ' . $key] = $value;
          }

          $this->app->log('PS', $pptx_params['cmd'], ($result == 'VERIFIED') ? 1 : -1, $pptx_params, $result, (OSCOM_APP_PAYPAL_PS_STATUS == '1') ? 'live' : 'sandbox');
        } else {
          $details = $this->app->getApiResult('PS', 'GetTransactionDetails', array('TRANSACTIONID' => $_GET['tx']), (OSCOM_APP_PAYPAL_DP_STATUS == '1') ? 'live' : 'sandbox');

          if ( in_array($details['ACK'], array('Success', 'SuccessWithWarning')) ) {
            $result = 'VERIFIED';

            $pptx_params = array('txn_id' => $details['TRANSACTIONID'],
                                 'invoice' => $details['INVNUM'],
                                 'custom' => $details['CUSTOM'],
                                 'payment_status' => $details['PAYMENTSTATUS'],
                                 'payer_status' => $details['PAYERSTATUS'],
                                 'mc_gross' => $details['AMT'],
                                 'mc_currency' => $details['CURRENCYCODE'],
                                 'pending_reason' => $details['PENDINGREASON'],
                                 'reason_code' => $details['REASONCODE'],
                                 'address_status' => $details['ADDRESSSTATUS'],
                                 'payment_type' => $details['PAYMENTTYPE']);
          }
        }
      } else {
        $pptx_params = $_POST;
        $pptx_params['cmd'] = '_notify-validate';

        foreach ( $_GET as $key => $value ) {
          $pptx_params['GET ' . $key] = $value;
        }

        $this->app->log('PS', $pptx_params['cmd'], ($result == 'VERIFIED') ? 1 : -1, $pptx_params, $result, (OSCOM_APP_PAYPAL_PS_STATUS == '1') ? 'live' : 'sandbox');
      }

      if ( $result != 'VERIFIED' ) {
        $messageStack->add_session('header', $this->app->getDef('module_ps_error_invalid_transaction'));

        OSCOM::redirect('shopping_cart.php');
      }

      $this->verifyTransaction($pptx_params);

      $order_id = substr($_SESSION['cart_PayPal_Standard_ID'], strpos($_SESSION['cart_PayPal_Standard_ID'], '-')+1);

      $Qorder = $this->app->db->get('orders', 'orders_status', ['orders_id' => $order_id, 'customers_id' => $_SESSION['customer_id']]);

      if (($Qorder->fetch() === false) || ($order_id != $pptx_params['invoice']) || ($_SESSION['customer_id'] != $pptx_params['custom'])) {
        OSCOM::redirect('shopping_cart.php');
      }

// skip before_process() if order was already processed in IPN
      if ( $Qorder->valueInt('orders_status') != OSCOM_APP_PAYPAL_PS_PREPARE_ORDER_STATUS_ID ) {
        if ( isset($_SESSION['comments']) && !empty($_SESSION['comments']) ) {
          $sql_data_array = array('orders_id' => $order_id,
                                  'orders_status_id' => $Qorder->valueInt('orders_status'),
                                  'date_added' => 'now()',
                                  'customer_notified' => '0',
                                  'comments' => $_SESSION['comments']);

          $this->app->db->save('orders_status_history', $sql_data_array);
        }

// load the after_process function from the payment modules
        $this->after_process();
      }
    }

    function before_process() {
      global $order_id, $order, $currencies, $order_totals;

      $new_order_status = DEFAULT_ORDERS_STATUS_ID;

      if ( OSCOM_APP_PAYPAL_PS_ORDER_STATUS_ID > 0) {
        $new_order_status = OSCOM_APP_PAYPAL_PS_ORDER_STATUS_ID;
      }

      $this->app->db->save('orders', ['orders_status' => $new_order_status, 'last_modified' => 'now()'], ['orders_id' => $order_id]);

      $sql_data_array = array('orders_id' => $order_id,
                              'orders_status_id' => (int)$new_order_status,
                              'date_added' => 'now()',
                              'customer_notified' => (SEND_EMAILS == 'true') ? '1' : '0',
                              'comments' => $order->info['comments']);

      $this->app->db->save('orders_status_history', $sql_data_array);

// initialized for the email confirmation
      $products_ordered = '';

      for ($i=0, $n=sizeof($order->products); $i<$n; $i++) {
        if (STOCK_LIMITED == 'true') {
          if (DOWNLOAD_ENABLED == 'true') {
            $stock_query_sql = 'select p.products_quantity, pad.products_attributes_filename
                                from :table_products p
                                left join :table_products_attributes pa
                                on p.products_id = pa.products_id
                                left join :table_products_attributes_download pad
                                on pa.products_attributes_id = pad.products_attributes_id
                                where p.products_id = :products_id';

// Will work with only one option for downloadable products
// otherwise, we have to build the query dynamically with a loop
            $products_attributes = (isset($order->products[$i]['attributes'])) ? $order->products[$i]['attributes'] : '';
            if (is_array($products_attributes)) {
              $stock_query_sql .= ' and pa.options_id = :options_id and pa.options_values_id = :options_values_id';
            }

            $Qstock = $this->app->db->prepare($stock_query_sql);
            $Qstock->bindInt(':products_id', tep_get_prid($order->products[$i]['id']));

            if (is_array($products_attributes)) {
              $Qstock->bindInt(':options_id', $products_attributes[0]['option_id']);
              $Qstock->bindInt(':options_values_id', $products_attributes[0]['value_id']);
            }

            $Qstock->execute();
          } else {
            $Qstock = $this->app->db->prepare('select products_quantity from :table_products where products_id = :products_id');
            $Qstock->bindInt(':products_id', tep_get_prid($order->products[$i]['id']));
            $Qstock->execute();
          }

          if ($Qstock->fetch() !== false) {
// do not decrement quantities if products_attributes_filename exists
            if ((DOWNLOAD_ENABLED != 'true') || !empty($Qstock->value('products_attributes_filename'))) {
              $stock_left = $Qstock->valueInt('products_quantity') - $order->products[$i]['qty'];
            } else {
              $stock_left = $Qstock->valueInt('products_quantity');
            }

            if ($stock_left != $Qstock->valueInt('products_quantity')) {
              $this->app->db->save('products', ['products_quantity' => $stock_left], ['products_id' => tep_get_prid($order->products[$i]['id'])]);
            }

            if ( ($stock_left < 1) && (STOCK_ALLOW_CHECKOUT == 'false') ) {
             $this->app->db->save('products', ['products_status' => '0'], ['products_id' => tep_get_prid($order->products[$i]['id'])]);
            }
          }
        }

// Update products_ordered (for bestsellers list)
        $Qupdate = $this->app->db->prepare('update :table_products set products_ordered = products_ordered + :products_ordered where products_id = :products_id');
        $Qupdate->bindInt(':products_ordered', $order->products[$i]['qty']);
        $Qupdate->bindInt(':products_id', tep_get_prid($order->products[$i]['id']));
        $Qupdate->execute();

//------insert customer choosen option to order--------
        $attributes_exist = '0';
        $products_ordered_attributes = '';
        if (isset($order->products[$i]['attributes'])) {
          $attributes_exist = '1';
          for ($j=0, $n2=sizeof($order->products[$i]['attributes']); $j<$n2; $j++) {
            if (DOWNLOAD_ENABLED == 'true') {
              $attributes_query = 'select popt.products_options_name, poval.products_options_values_name, pa.options_values_price, pa.price_prefix, pad.products_attributes_maxdays, pad.products_attributes_maxcount, pad.products_attributes_filename
                                   from :table_products_options popt, :table_products_options_values poval, :table_products_attributes pa
                                   left join :table_products_attributes_download pad on pa.products_attributes_id = pad.products_attributes_id
                                   where pa.products_id = :products_id
                                   and pa.options_id = :options_id
                                   and pa.options_id = popt.products_options_id
                                   and pa.options_values_id = :options_values_id
                                   and pa.options_values_id = poval.products_options_values_id
                                   and popt.language_id = :language_id
                                   and popt.language_id = poval.language_id';
            } else {
              $attributes_query = 'select popt.products_options_name, poval.products_options_values_name, pa.options_values_price, pa.price_prefix
                                   from :table_products_options popt, :table_products_options_values poval, :table_products_attributes pa
                                   where pa.products_id = :products_id
                                   and pa.options_id = :options_id
                                   and pa.options_id = popt.products_options_id
                                   and pa.options_values_id = :options_values_id
                                   and pa.options_values_id = poval.products_options_values_id
                                   and popt.language_id = :language_id
                                   and popt.language_id = poval.language_id';
            }

            $Qattributes = $this->app->db->prepare($attributes_query);
            $Qattributes->bindInt(':products_id', $order->products[$i]['id']);
            $Qattributes->bindInt(':options_id', $order->products[$i]['attributes'][$j]['option_id']);
            $Qattributes->bindInt(':options_values_id', $order->products[$i]['attributes'][$j]['value_id']);
            $Qattributes->bindInt(':language_id', $this->app->lang->getId());
            $Qattributes->execute();

            $products_ordered_attributes .= "\n\t" . $Qattributes->value('products_options_name') . ' ' . $Qattributes->value('products_options_values_name');
          }
        }

//------insert customer choosen option eof ----
        $products_ordered .= $order->products[$i]['qty'] . ' x ' . $order->products[$i]['name'] . ' (' . $order->products[$i]['model'] . ') = ' . $currencies->display_price($order->products[$i]['final_price'], $order->products[$i]['tax'], $order->products[$i]['qty']) . $products_ordered_attributes . "\n";
      }

// lets start with the email confirmation
      $email_order = STORE_NAME . "\n" .
                     OSCOM::getDef('email_separator') . "\n" .
                     OSCOM::getDef('email_text_order_number') . ' ' . $order_id . "\n" .
                     OSCOM::getDef('email_text_invoice_url') . ' ' . OSCOM::link('account_history_info.php', 'order_id=' . $order_id, false) . "\n" .
                     OSCOM::getDef('email_text_date_ordered') . ' ' . strftime(OSCOM::getDef('date_format_long')) . "\n\n";
      if ($order->info['comments']) {
        $email_order .= HTML::outputProtected($order->info['comments']) . "\n\n";
      }
      $email_order .= OSCOM::getDef('email_text_products') . "\n" .
                      OSCOM::getDef('email_separator') . "\n" .
                      $products_ordered .
                      OSCOM::getDef('email_separator') . "\n";

      for ($i=0, $n=sizeof($order_totals); $i<$n; $i++) {
        $email_order .= strip_tags($order_totals[$i]['title']) . ' ' . strip_tags($order_totals[$i]['text']) . "\n";
      }

      if ($order->content_type != 'virtual') {
        $email_order .= "\n" . OSCOM::getDef('email_text_delivery_address') . "\n" .
                        OSCOM::getDef('email_separator') . "\n" .
                        tep_address_label($_SESSION['customer_id'], $_SESSION['sendto'], 0, '', "\n") . "\n";
      }

      $email_order .= "\n" . OSCOM::getDef('email_text_billing_address') . "\n" .
                      OSCOM::getDef('email_separator') . "\n" .
                      tep_address_label($_SESSION['customer_id'], $_SESSION['billto'], 0, '', "\n") . "\n\n" .
                      OSCOM::getDef('email_text_payment_method') . "\n" .
                      OSCOM::getDef('email_separator') . "\n" .
                      $this->public_title . "\n\n";

      $orderEmail = new Mail($order->customer['email_address'], $order->customer['firstname'] . ' ' . $order->customer['lastname'], STORE_OWNER_EMAIL_ADDRESS, STORE_OWNER, OSCOM::getDef('email_text_subject'));
      $orderEmail->setBody($email_order);
      $orderEmail->send();

// send emails to other people
      if (SEND_EXTRA_ORDER_EMAILS_TO != '') {
        $extraEmail = new Mail(SEND_EXTRA_ORDER_EMAILS_TO, null, STORE_OWNER_EMAIL_ADDRESS, STORE_OWNER, OSCOM::getDef('email_text_subject'));
        $extraEmail->setBody($email_order);
        $extraEmail->send();
      }

// load the after_process function from the payment modules
      $this->after_process();
    }

    function after_process() {
      $_SESSION['cart']->reset(true);

// unregister session variables used during checkout
      unset($_SESSION['sendto']);
      unset($_SESSION['billto']);
      unset($_SESSION['shipping']);
      unset($_SESSION['payment']);
      unset($_SESSION['comments']);

      unset($_SESSION['cart_PayPal_Standard_ID']);

      OSCOM::redirect('checkout_success.php');
    }

    function get_error() {
      return false;
    }

    function check() {
      return defined('OSCOM_APP_PAYPAL_PS_STATUS') && (trim(OSCOM_APP_PAYPAL_PS_STATUS) != '');
    }

    function install() {
      $this->app->redirect('Configure&Install&module=PS');
    }

    function remove() {
      $this->app->redirect('Configure&Uninstall&module=PS');
    }

    function keys() {
      return array('OSCOM_APP_PAYPAL_PS_SORT_ORDER');
    }

    function verifyTransaction($pptx_params, $is_ipn = false) {
      global $currencies;

      if ( isset($pptx_params['invoice']) && is_numeric($pptx_params['invoice']) && ($pptx_params['invoice'] > 0) && isset($pptx_params['custom']) && is_numeric($pptx_params['custom']) && ($pptx_params['custom'] > 0) ) {
        $Qorder = $this->app->db->get('orders', ['orders_id', 'currency', 'currency_value'], ['orders_id' => $pptx_params['invoice'], 'customers_id' => $pptx_params['custom']]);

        if ($Qorder->fetch() !== false) {
          $Qtotal = $this->app->db->get('orders_total', 'value', ['orders_id' => $Qorder->valueInt('orders_id'), 'class' => 'ot_total'], null, 1);

          $comment_status = 'Transaction ID: ' . HTML::outputProtected($pptx_params['txn_id']) . "\n" .
                            'Payer Status: ' . HTML::outputProtected($pptx_params['payer_status']) . "\n" .
                            'Address Status: ' . HTML::outputProtected($pptx_params['address_status']) . "\n" .
                            'Payment Status: ' . HTML::outputProtected($pptx_params['payment_status']) . "\n" .
                            'Payment Type: ' . HTML::outputProtected($pptx_params['payment_type']) . "\n" .
                            'Pending Reason: ' . HTML::outputProtected($pptx_params['pending_reason']);

          if ( $pptx_params['mc_gross'] != $this->app->formatCurrencyRaw($Qtotal->value('value'), $Qorder->value('currency'), $Qorder->value('currency_value')) ) {
            $comment_status .= "\n" . 'OSCOM Error Total Mismatch: PayPal transaction value (' . HTML::outputProtected($pptx_params['mc_gross']) . ') does not match order value (' . $this->app->formatCurrencyRaw($Qtotal->value('value'), $Qorder->value('currency'), $Qorder->value('currency_value')) . ')';
          }

          if ( $is_ipn === true ) {
            $comment_status .= "\n" . 'Source: IPN';
          }

          $sql_data_array = array('orders_id' => $Qorder->valueInt('orders_id'),
                                  'orders_status_id' => OSCOM_APP_PAYPAL_TRANSACTIONS_ORDER_STATUS_ID,
                                  'date_added' => 'now()',
                                  'customer_notified' => '0',
                                  'comments' => $comment_status);

          $this->app->db->save('orders_status_history', $sql_data_array);
        }
      }
    }
  }
?>
