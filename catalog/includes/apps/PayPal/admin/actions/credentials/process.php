<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2014 osCommerce

  Released under the GNU General Public License
*/

  use OSC\OM\HTML;
  use OSC\OM\OSCOM;

  $data = array();

  if ( $current_module == 'PP' ) {
    $data = array('OSCOM_APP_PAYPAL_LIVE_SELLER_EMAIL' => isset($_POST['live_email']) ? HTML::sanitize($_POST['live_email']) : '',
                  'OSCOM_APP_PAYPAL_LIVE_SELLER_EMAIL_PRIMARY' => isset($_POST['live_email_primary']) ? HTML::sanitize($_POST['live_email_primary']) : '',
                  'OSCOM_APP_PAYPAL_LIVE_API_USERNAME' => isset($_POST['live_username']) ? HTML::sanitize($_POST['live_username']) : '',
                  'OSCOM_APP_PAYPAL_LIVE_API_PASSWORD' => isset($_POST['live_password']) ? HTML::sanitize($_POST['live_password']) : '',
                  'OSCOM_APP_PAYPAL_LIVE_API_SIGNATURE' => isset($_POST['live_signature']) ? HTML::sanitize($_POST['live_signature']) : '',
                  'OSCOM_APP_PAYPAL_SANDBOX_SELLER_EMAIL' => isset($_POST['sandbox_email']) ? HTML::sanitize($_POST['sandbox_email']) : '',
                  'OSCOM_APP_PAYPAL_SANDBOX_SELLER_EMAIL_PRIMARY' => isset($_POST['sandbox_email_primary']) ? HTML::sanitize($_POST['sandbox_email_primary']) : '',
                  'OSCOM_APP_PAYPAL_SANDBOX_API_USERNAME' => isset($_POST['sandbox_username']) ? HTML::sanitize($_POST['sandbox_username']) : '',
                  'OSCOM_APP_PAYPAL_SANDBOX_API_PASSWORD' => isset($_POST['sandbox_password']) ? HTML::sanitize($_POST['sandbox_password']) : '',
                  'OSCOM_APP_PAYPAL_SANDBOX_API_SIGNATURE' => isset($_POST['sandbox_signature']) ? HTML::sanitize($_POST['sandbox_signature']) : '');
  } elseif ( $current_module == 'PF' ) {
    $data = array('OSCOM_APP_PAYPAL_PF_LIVE_PARTNER' => isset($_POST['live_partner']) ? HTML::sanitize($_POST['live_partner']) : '',
                  'OSCOM_APP_PAYPAL_PF_LIVE_VENDOR' => isset($_POST['live_vendor']) ? HTML::sanitize($_POST['live_vendor']) : '',
                  'OSCOM_APP_PAYPAL_PF_LIVE_USER' => isset($_POST['live_user']) ? HTML::sanitize($_POST['live_user']) : '',
                  'OSCOM_APP_PAYPAL_PF_LIVE_PASSWORD' => isset($_POST['live_password']) ? HTML::sanitize($_POST['live_password']) : '',
                  'OSCOM_APP_PAYPAL_PF_SANDBOX_PARTNER' => isset($_POST['sandbox_partner']) ? HTML::sanitize($_POST['sandbox_partner']) : '',
                  'OSCOM_APP_PAYPAL_PF_SANDBOX_VENDOR' => isset($_POST['sandbox_vendor']) ? HTML::sanitize($_POST['sandbox_vendor']) : '',
                  'OSCOM_APP_PAYPAL_PF_SANDBOX_USER' => isset($_POST['sandbox_user']) ? HTML::sanitize($_POST['sandbox_user']) : '',
                  'OSCOM_APP_PAYPAL_PF_SANDBOX_PASSWORD' => isset($_POST['sandbox_password']) ? HTML::sanitize($_POST['sandbox_password']) : '');
  }

  foreach ( $data as $key => $value ) {
    $OSCOM_PayPal->saveParameter($key, $value);
  }

  $OSCOM_PayPal->addAlert($OSCOM_PayPal->getDef('alert_credentials_saved_success'), 'success');

  OSCOM::redirect('apps.php', 'PayPal&action=credentials&module=' . $current_module);
?>
