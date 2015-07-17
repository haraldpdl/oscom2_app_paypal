<?php
/**
  * osCommerce Online Merchant
  *
  * @copyright Copyright (c) 2015 osCommerce; http://www.oscommerce.com
  * @license GPL; http://www.oscommerce.com/gpllicense.txt
  */

  namespace OSC\Apps\PayPal\Module\Payment;

  use OSC\OM\HTML;
  use OSC\OM\HTTP;
  use OSC\OM\OSCOM;
  use OSC\OM\Registry;

  use OSC\Apps\PayPal\PayPal as PayPalApp;

  class EC implements \OSC\OM\Modules\PaymentInterface {
    var $code, $title, $description, $enabled, $_app;

    function __construct() {
      global $PHP_SELF, $order, $request_type;

      if (!Registry::exists('PayPal')) {
        Registry::set('PayPal', new PayPalApp());
      }

      $this->_app = Registry::get('PayPal');
      $this->_app->loadLanguageFile('modules/EC/EC.php');

      $this->signature = 'paypal|paypal_express|' . $this->_app->getVersion() . '|2.3';
      $this->api_version = $this->_app->getApiVersion();

      $this->code = 'PayPal\EC';
      $this->title = $this->_app->getDef('module_ec_title');
      $this->public_title = $this->_app->getDef('module_ec_public_title');
      $this->description = '<div align="center">' . $this->_app->drawButton($this->_app->getDef('module_ec_legacy_admin_app_button'), OSCOM::link('index.php', 'A&PayPal&Configure&module=EC'), 'primary', null, true) . '</div>';
      $this->sort_order = defined('OSCOM_APP_PAYPAL_EC_SORT_ORDER') ? OSCOM_APP_PAYPAL_EC_SORT_ORDER : 0;
      $this->enabled = defined('OSCOM_APP_PAYPAL_EC_STATUS') && in_array(OSCOM_APP_PAYPAL_EC_STATUS, array('1', '0')) ? true : false;
      $this->order_status = defined('OSCOM_APP_PAYPAL_EC_ORDER_STATUS_ID') && ((int)OSCOM_APP_PAYPAL_EC_ORDER_STATUS_ID > 0) ? (int)OSCOM_APP_PAYPAL_EC_ORDER_STATUS_ID : 0;

      if ( defined('OSCOM_APP_PAYPAL_EC_STATUS') ) {
        if ( OSCOM_APP_PAYPAL_EC_STATUS == '0' ) {
          $this->title .= ' [Sandbox]';
          $this->public_title .= ' (' . $this->code . '; Sandbox)';
        }
      }

      if ( !function_exists('curl_init') ) {
        $this->description .= '<div class="secWarning">' . $this->_app->getDef('module_ec_error_curl') . '</div>';

        $this->enabled = false;
      }

      if ( $this->enabled === true ) {
        if ( OSCOM_APP_PAYPAL_GATEWAY == '1' ) { // PayPal
          if ( !$this->_app->hasCredentials('EC') ) {
            $this->description .= '<div class="secWarning">' . $this->_app->getDef('module_ec_error_credentials') . '</div>';

            $this->enabled = false;
          }
        } else { // Payflow
          if ( !$this->_app->hasCredentials('EC', 'payflow') ) {
            $this->description .= '<div class="secWarning">' . $this->_app->getDef('module_ec_error_credentials_payflow') . '</div>';

            $this->enabled = false;
          }
        }
      }

      if ( $this->enabled === true ) {
        if ( isset($order) && is_object($order) ) {
          $this->update_status();
        }
      }

      if ( basename($PHP_SELF) == 'shopping_cart.php' ) {
        if ( (OSCOM_APP_PAYPAL_GATEWAY == '1') && (OSCOM_APP_PAYPAL_EC_CHECKOUT_FLOW == '1') ) {
          if ( isset($request_type) && ($request_type != 'SSL') && (ENABLE_SSL == true) ) {
            OSCOM::redirect('shopping_cart.php', tep_get_all_get_params(), 'SSL');
          }

          header('X-UA-Compatible: IE=edge', true);
        }
      }

// When changing the shipping address due to no shipping rates being available, head straight to the checkout confirmation page
      if ( (basename($PHP_SELF) == 'checkout_payment.php') && isset($_SESSION['appPayPalEcRightTurn']) ) {
        unset($_SESSION['appPayPalEcRightTurn']);

        if ( isset($_SESSION['payment']) && ($_SESSION['payment'] == $this->code) ) {
          OSCOM::redirect('checkout_confirmation.php', '', 'SSL');
        }
      }
    }

    function update_status() {
      global $order;

      $OSCOM_Db = Registry::get('Db');

      if ( ($this->enabled == true) && ((int)OSCOM_APP_PAYPAL_EC_ZONE > 0) ) {
        $check_flag = false;

        $Qcheck = $OSCOM_Db->get('zones_to_geo_zones', 'zone_id', ['geo_zone_id' => OSCOM_APP_PAYPAL_EC_ZONE, 'zone_country_id' => $order->delivery['country']['id']], 'zone_id');

        while ($Qcheck->fetch()) {
          if (($Qcheck->valueInt('zone_id') < 1) || ($Qcheck->valueInt('zone_id') == $order->delivery['zone_id'])) {
            $check_flag = true;
            break;
          }
        }

        if ($check_flag == false) {
          $this->enabled = false;
        }
      }
    }

    function checkout_initialization_method() {
      if ( (OSCOM_APP_PAYPAL_GATEWAY == '1') && (OSCOM_APP_PAYPAL_EC_CHECKOUT_IMAGE == '1') ) {
        if (OSCOM_APP_PAYPAL_EC_STATUS == '1') {
          $image_button = 'https://fpdbs.paypal.com/dynamicimageweb?cmd=_dynamic-image';
        } else {
          $image_button = 'https://fpdbs.sandbox.paypal.com/dynamicimageweb?cmd=_dynamic-image';
        }

        $params = array('locale=' . $this->_app->getDef('module_ec_button_locale'));

        if ( $this->_app->hasCredentials('EC') ) {
          $response_array = $this->_app->getApiResult('EC', 'GetPalDetails');

          if ( isset($response_array['PAL']) ) {
            $params[] = 'pal=' . $response_array['PAL'];
            $params[] = 'ordertotal=' . $this->_app->formatCurrencyRaw($_SESSION['cart']->show_total());
          }
        }

        if ( !empty($params) ) {
          $image_button .= '&' . implode('&', $params);
        }
      } else {
        $image_button = $this->_app->getDef('module_ec_button_url');
      }

      $button_title = HTML::outputProtected($this->_app->getDef('module_ec_button_title'));

      if ( OSCOM_APP_PAYPAL_EC_STATUS == '0' ) {
        $button_title .= ' (' . $this->code . '; Sandbox)';
      }

      $string = '<a href="' . OSCOM::link('index.php', 'order&callback&paypal&ec', 'SSL') . '" data-paypal-button="true"><img src="' . $image_button . '" border="0" alt="" title="' . $button_title . '" /></a>';

      if ( (OSCOM_APP_PAYPAL_GATEWAY == '1') && (OSCOM_APP_PAYPAL_EC_CHECKOUT_FLOW == '1') ) {
        $string .= <<<EOD
<script>
(function(d, s, id){
  var js, ref = d.getElementsByTagName(s)[0];
  if (!d.getElementById(id)){
    js = d.createElement(s); js.id = id; js.async = true;
    js.src = "//www.paypalobjects.com/js/external/paypal.v1.js";
    ref.parentNode.insertBefore(js, ref);
  }
}(document, "script", "paypal-js"));
</script>
EOD;
      }

      return $string;
    }

    function javascript_validation() {
      return false;
    }

    function selection() {
      return array('id' => $this->code,
                   'module' => $this->public_title);
    }

    function pre_confirmation_check() {
      global $messageStack, $order;

      if ( !isset($_SESSION['appPayPalEcResult']) ) {
        OSCOM::redirect('index.php', 'order&callback&paypal&ec', 'SSL');
      }

      if ( OSCOM_APP_PAYPAL_GATEWAY == '1' ) { // PayPal
        if ( !in_array($_SESSION['appPayPalEcResult']['ACK'], array('Success', 'SuccessWithWarning')) ) {
          OSCOM::redirect('shopping_cart.php', 'error_message=' . stripslashes($_SESSION['appPayPalEcResult']['L_LONGMESSAGE0']), 'SSL');
        } elseif ( !isset($_SESSION['appPayPalEcSecret']) || ($_SESSION['appPayPalEcResult']['PAYMENTREQUEST_0_CUSTOM'] != $_SESSION['appPayPalEcSecret']) ) {
          OSCOM::redirect('shopping_cart.php', '', 'SSL');
        }
      } else { // Payflow
        if ($_SESSION['appPayPalEcResult']['RESULT'] != '0') {
          OSCOM::redirect('shopping_cart.php', 'error_message=' . urlencode($_SESSION['appPayPalEcResult']['OSCOM_ERROR_MESSAGE']), 'SSL');
        } elseif ( !isset($_SESSION['appPayPalEcSecret']) || ($_SESSION['appPayPalEcResult']['CUSTOM'] != $_SESSION['appPayPalEcSecret']) ) {
          OSCOM::redirect('shopping_cart.php', '', 'SSL');
        }
      }

      $order->info['payment_method'] = '<img src="https://www.paypalobjects.com/webstatic/mktg/Logo/pp-logo-100px.png" border="0" alt="PayPal Logo" style="padding: 3px;" />';
    }

    function confirmation() {
      if (!isset($_SESSION['comments'])) {
        $_SESSION['comments'] = null;
      }

      $confirmation = false;

      if (empty($_SESSION['comments'])) {
        $confirmation = array('fields' => array(array('title' => $this->_app->getDef('module_ec_field_comments'),
                                                      'field' => HTML::textareaField('ppecomments', '60', '5'))));
      }

      return $confirmation;
    }

    function process_button() {
      return false;
    }

    function before_process() {
      if ( OSCOM_APP_PAYPAL_GATEWAY == '1' ) {
        $this->before_process_paypal();
      } else {
        $this->before_process_payflow();
      }
    }

    function before_process_paypal() {
      global $order, $response_array;

      if ( !isset($_SESSION['appPayPalEcResult']) ) {
        OSCOM::redirect('index.php', 'order&callback&paypal&ec', 'SSL');
      }

      if ( in_array($_SESSION['appPayPalEcResult']['ACK'], array('Success', 'SuccessWithWarning')) ) {
        if ( !isset($_SESSION['appPayPalEcSecret']) || ($_SESSION['appPayPalEcResult']['PAYMENTREQUEST_0_CUSTOM'] != $_SESSION['appPayPalEcSecret']) ) {
          OSCOM::redirect('shopping_cart.php', '', 'SSL');
        }
      } else {
        OSCOM::redirect('shopping_cart.php', 'error_message=' . stripslashes($_SESSION['appPayPalEcResult']['L_LONGMESSAGE0']), 'SSL');
      }

      if (empty($_SESSION['comments'])) {
        if (isset($_POST['ppecomments']) && tep_not_null($_POST['ppecomments'])) {
          $_SESSION['comments'] = HTML::sanitize($_POST['ppecomments']);

          $order->info['comments'] = $_SESSION['comments'];
        }
      }

      $params = array('TOKEN' => $_SESSION['appPayPalEcResult']['TOKEN'],
                      'PAYERID' => $_SESSION['appPayPalEcResult']['PAYERID'],
                      'PAYMENTREQUEST_0_AMT' => $this->_app->formatCurrencyRaw($order->info['total']),
                      'PAYMENTREQUEST_0_CURRENCYCODE' => $order->info['currency']);

      if (is_numeric($_SESSION['sendto']) && ($_SESSION['sendto'] > 0)) {
        $params['PAYMENTREQUEST_0_SHIPTONAME'] = $order->delivery['firstname'] . ' ' . $order->delivery['lastname'];
        $params['PAYMENTREQUEST_0_SHIPTOSTREET'] = $order->delivery['street_address'];
        $params['PAYMENTREQUEST_0_SHIPTOCITY'] = $order->delivery['city'];
        $params['PAYMENTREQUEST_0_SHIPTOSTATE'] = tep_get_zone_code($order->delivery['country']['id'], $order->delivery['zone_id'], $order->delivery['state']);
        $params['PAYMENTREQUEST_0_SHIPTOCOUNTRYCODE'] = $order->delivery['country']['iso_code_2'];
        $params['PAYMENTREQUEST_0_SHIPTOZIP'] = $order->delivery['postcode'];
      }

      $response_array = $this->_app->getApiResult('EC', 'DoExpressCheckoutPayment', $params);

      if ( !in_array($response_array['ACK'], array('Success', 'SuccessWithWarning')) ) {
        if ( $response_array['L_ERRORCODE0'] == '10486' ) {
          if ( OSCOM_APP_PAYPAL_EC_STATUS == '1' ) {
            $paypal_url = 'https://www.paypal.com/cgi-bin/webscr?cmd=_express-checkout';
          } else {
            $paypal_url = 'https://www.sandbox.paypal.com/cgi-bin/webscr?cmd=_express-checkout';
          }

          $paypal_url .= '&token=' . $_SESSION['appPayPalEcResult']['TOKEN'];

          HTTP::redirect($paypal_url);
        }

        OSCOM::redirect('shopping_cart.php', 'error_message=' . stripslashes($response_array['L_LONGMESSAGE0']), 'SSL');
      }
    }

    function before_process_payflow() {
      global $order, $response_array;

      if ( !isset($_SESSION['appPayPalEcResult']) ) {
        OSCOM::redirect('index.php', 'order&callback&paypal&ec', 'SSL');
      }

      if ( $_SESSION['appPayPalEcResult']['RESULT'] == '0' ) {
        if ( !isset($_SESSION['appPayPalEcSecret']) || ($_SESSION['appPayPalEcResult']['CUSTOM'] != $_SESSION['appPayPalEcSecret']) ) {
          OSCOM::redirect('shopping_cart.php', '', 'SSL');
        }
      } else {
        OSCOM::redirect('shopping_cart.php', 'error_message=' . urlencode($_SESSION['appPayPalEcResult']['OSCOM_ERROR_MESSAGE']), 'SSL');
      }

      if ( empty($_SESSION['comments']) ) {
        if ( isset($_POST['ppecomments']) && tep_not_null($_POST['ppecomments']) ) {
          $_SESSION['comments'] = HTML::sanitize($_POST['ppecomments']);

          $order->info['comments'] = $_SESSION['comments'];
        }
      }

      $params = array('EMAIL' => $order->customer['email_address'],
                      'TOKEN' => $_SESSION['appPayPalEcResult']['TOKEN'],
                      'PAYERID' => $_SESSION['appPayPalEcResult']['PAYERID'],
                      'AMT' => $this->_app->formatCurrencyRaw($order->info['total']),
                      'CURRENCY' => $order->info['currency']);

      if ( is_numeric($_SESSION['sendto']) && ($_SESSION['sendto'] > 0) ) {
        $params['SHIPTONAME'] = $order->delivery['firstname'] . ' ' . $order->delivery['lastname'];
        $params['SHIPTOSTREET'] = $order->delivery['street_address'];
        $params['SHIPTOCITY'] = $order->delivery['city'];
        $params['SHIPTOSTATE'] = tep_get_zone_code($order->delivery['country']['id'], $order->delivery['zone_id'], $order->delivery['state']);
        $params['SHIPTOCOUNTRY'] = $order->delivery['country']['iso_code_2'];
        $params['SHIPTOZIP'] = $order->delivery['postcode'];
      }

      $response_array = $this->_app->getApiResult('EC', 'PayflowDoExpressCheckoutPayment', $params);

      if ( $response_array['RESULT'] != '0' ) {
        OSCOM::redirect('shopping_cart.php', 'error_message=' . urlencode($response_array['OSCOM_ERROR_MESSAGE']), 'SSL');
      }
    }

    function after_process() {
      if ( OSCOM_APP_PAYPAL_GATEWAY == '1' ) {
        $this->after_process_paypal();
      } else {
        $this->after_process_payflow();
      }
    }

    function after_process_paypal() {
      global $response_array, $insert_id;

      $OSCOM_Db = Registry::get('Db');

      $pp_result = 'Transaction ID: ' . HTML::outputProtected($response_array['PAYMENTINFO_0_TRANSACTIONID']) . "\n" .
                   'Payer Status: ' . HTML::outputProtected($_SESSION['appPayPalEcResult']['PAYERSTATUS']) . "\n" .
                   'Address Status: ' . HTML::outputProtected($_SESSION['appPayPalEcResult']['ADDRESSSTATUS']) . "\n" .
                   'Payment Status: ' . HTML::outputProtected($response_array['PAYMENTINFO_0_PAYMENTSTATUS']) . "\n" .
                   'Payment Type: ' . HTML::outputProtected($response_array['PAYMENTINFO_0_PAYMENTTYPE']) . "\n" .
                   'Pending Reason: ' . HTML::outputProtected($response_array['PAYMENTINFO_0_PENDINGREASON']);

      $sql_data_array = array('orders_id' => $insert_id,
                              'orders_status_id' => OSCOM_APP_PAYPAL_TRANSACTIONS_ORDER_STATUS_ID,
                              'date_added' => 'now()',
                              'customer_notified' => '0',
                              'comments' => $pp_result);

      $OSCOM_Db->save('orders_status_history', $sql_data_array);

      unset($_SESSION['appPayPalEcResult']);
      unset($_SESSION['appPayPalEcSecret']);
    }

    function after_process_payflow() {
      global $response_array, $insert_id;

      $OSCOM_Db = Registry::get('Db');

      $pp_result = 'Transaction ID: ' . HTML::outputProtected($response_array['PNREF']) . "\n" .
                   'Gateway: Payflow' . "\n" .
                   'PayPal ID: ' . HTML::outputProtected($response_array['PPREF']) . "\n" .
                   'Payer Status: ' . HTML::outputProtected($_SESSION['appPayPalEcResult']['PAYERSTATUS']) . "\n" .
                   'Address Status: ' . HTML::outputProtected($_SESSION['appPayPalEcResult']['ADDRESSSTATUS']) . "\n" .
                   'Payment Status: ' . HTML::outputProtected($response_array['PENDINGREASON']) . "\n" .
                   'Payment Type: ' . HTML::outputProtected($response_array['PAYMENTTYPE']) . "\n" .
                   'Response: ' . HTML::outputProtected($response_array['RESPMSG']) . "\n";

      $sql_data_array = array('orders_id' => $insert_id,
                              'orders_status_id' => OSCOM_APP_PAYPAL_TRANSACTIONS_ORDER_STATUS_ID,
                              'date_added' => 'now()',
                              'customer_notified' => '0',
                              'comments' => $pp_result);

      $OSCOM_Db->save('orders_status_history', $sql_data_array);

      unset($_SESSION['appPayPalEcResult']);
      unset($_SESSION['appPayPalEcSecret']);

// Manually call PayflowInquiry to retrieve more details about the transaction and to allow admin post-transaction actions
      $response = $this->_app->getApiResult('APP', 'PayflowInquiry', array('ORIGID' => $response_array['PNREF']));

      if ( isset($response['RESULT']) && ($response['RESULT'] == '0') ) {
        $result = 'Transaction ID: ' . HTML::outputProtected($response['ORIGPNREF']) . "\n" .
                  'Gateway: Payflow' . "\n";

        $pending_reason = $response['TRANSSTATE'];
        $payment_status = null;

        switch ( $response['TRANSSTATE'] ) {
          case '3':
            $pending_reason = 'authorization';
            $payment_status = 'Pending';
            break;

          case '4':
            $pending_reason = 'other';
            $payment_status = 'In-Progress';
            break;

          case '6':
            $pending_reason = 'scheduled';
            $payment_status = 'Pending';
            break;

          case '8':
          case '9':
            $pending_reason = 'None';
            $payment_status = 'Completed';
            break;
        }

        if ( isset($payment_status) ) {
          $result .= 'Payment Status: ' . HTML::outputProtected($payment_status) . "\n";
        }

        $result .= 'Pending Reason: ' . HTML::outputProtected($pending_reason) . "\n";

        switch ( $response['AVSADDR'] ) {
          case 'Y':
            $result .= 'AVS Address: Match' . "\n";
            break;

          case 'N':
            $result .= 'AVS Address: No Match' . "\n";
            break;
        }

        switch ( $response['AVSZIP'] ) {
          case 'Y':
            $result .= 'AVS ZIP: Match' . "\n";
            break;

          case 'N':
            $result .= 'AVS ZIP: No Match' . "\n";
            break;
        }

        switch ( $response['IAVS'] ) {
          case 'Y':
            $result .= 'IAVS: International' . "\n";
            break;

          case 'N':
            $result .= 'IAVS: USA' . "\n";
            break;
        }

        switch ( $response['CVV2MATCH'] ) {
          case 'Y':
            $result .= 'CVV2: Match' . "\n";
            break;

          case 'N':
            $result .= 'CVV2: No Match' . "\n";
            break;
        }

        $sql_data_array = array('orders_id' => $insert_id,
                                'orders_status_id' => OSCOM_APP_PAYPAL_TRANSACTIONS_ORDER_STATUS_ID,
                                'date_added' => 'now()',
                                'customer_notified' => '0',
                                'comments' => $result);

        $OSCOM_Db->save('orders_status_history', $sql_data_array);
      }
    }

    function get_error() {
      return false;
    }

    function check() {
      return defined('OSCOM_APP_PAYPAL_EC_STATUS') && (trim(OSCOM_APP_PAYPAL_EC_STATUS) != '');
    }

    function install() {
      OSCOM::redirect('index.php', 'A&PayPal&Configure&Install&module=EC');
    }

    function remove() {
      OSCOM::redirect('index.php', 'A&PayPal&Configure&Uninstall&module=EC');
    }

    function keys() {
      return array('OSCOM_APP_PAYPAL_EC_SORT_ORDER');
    }

    function getProductType($id, $attributes) {
      $OSCOM_Db = Registry::get('Db');

      foreach ( $attributes as $a ) {
        $Qcheck = $OSCOM_Db->prepare('select pad.products_attributes_id from :table_products_attributes pa, :table_products_attributes_download pad where pa.products_id = :products_id and pa.options_values_id = :options_values_id and pa.products_attributes_id = pad.products_attributes_id limit 1');
        $Qcheck->bindInt(':products_id', $id);
        $Qcheck->bindInt(':options_values_id', $a['value_id']);
        $Qcheck->execute();

        if ($Qcheck->fetch() !== false) {
          return 'Digital';
        }
      }

      return 'Physical';
    }
  }
?>
