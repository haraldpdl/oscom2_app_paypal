<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2014 osCommerce

  Released under the GNU General Public License
*/

  $content = 'configure.php';

  $modules = $OSCOM_PayPal->getModules();
  $modules[] = 'G';

  $default_module = 'G';

  foreach ( $modules as $m ) {
    if ( $OSCOM_PayPal->isInstalled($m) ) {
      $default_module = $m;
      break;
    }
  }

  $current_module = (isset($_GET['module']) && in_array($_GET['module'], $modules)) ? $_GET['module'] : $default_module;

  if ( !defined('OSCOM_APP_PAYPAL_TRANSACTIONS_ORDER_STATUS_ID') ) {
    $Qcheck = $OSCOM_Db->get('orders_status', 'orders_status_id', ['orders_status_name' => 'PayPal [Transactions]'], null, 1);

    if ($Qcheck->fetch() === false) {
      $Qstatus = $OSCOM_Db->get('orders_status', 'max(orders_status_id) as status_id');

      $status_id = $Qstatus->valueInt('status_id') + 1;

      $languages = tep_get_languages();

      foreach ($languages as $lang) {
        $OSCOM_Db->save('orders_status', [
          'orders_status_id' => $status_id,
          'language_id' => $lang['id'],
          'orders_status_name' => 'PayPal [Transactions]',
          'public_flag' => 0,
          'downloads_flag' => 0
        ]);
      }
    } else {
      $status_id = $Qcheck->valueInt('orders_status_id');
    }

    $OSCOM_PayPal->saveParameter('OSCOM_APP_PAYPAL_TRANSACTIONS_ORDER_STATUS_ID', $status_id);
  }

  if ( !defined('OSCOM_APP_PAYPAL_VERIFY_SSL') ) {
    $OSCOM_PayPal->saveParameter('OSCOM_APP_PAYPAL_VERIFY_SSL', '1');
  }

  if ( !defined('OSCOM_APP_PAYPAL_PROXY') ) {
    $OSCOM_PayPal->saveParameter('OSCOM_APP_PAYPAL_PROXY', '');
  }

  if ( !defined('OSCOM_APP_PAYPAL_GATEWAY') ) {
    $OSCOM_PayPal->saveParameter('OSCOM_APP_PAYPAL_GATEWAY', '1');
  }

  if ( !defined('OSCOM_APP_PAYPAL_LOG_TRANSACTIONS') ) {
    $OSCOM_PayPal->saveParameter('OSCOM_APP_PAYPAL_LOG_TRANSACTIONS', '1');
  }
?>
