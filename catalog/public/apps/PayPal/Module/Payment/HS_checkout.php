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

  chdir('../../../../');
  require('includes/application_top.php');

  $error = false;

  if ( !defined('OSCOM_APP_PAYPAL_HS_STATUS') || !in_array(OSCOM_APP_PAYPAL_HS_STATUS, array('1', '0')) ) {
    $error = true;
  }

  if ( $error === false ) {
    if ( !isset($_GET['key']) || !isset($_SESSION['pphs_key']) || ($_GET['key'] != $_SESSION['pphs_key']) || !isset($_SESSION['pphs_result']) ) {
      $error = true;
    }
  }

  if ( $error === false ) {
    if (($_SESSION['pphs_result']['ACK'] != 'Success') && ($_SESSION['pphs_result']['ACK'] != 'SuccessWithWarning')) {
      $error = true;

      $_SESSION['pphs_error_msg'] = $_SESSION['pphs_result']['L_LONGMESSAGE0'];
    }
  }

  if ( $error === false ) {
    if ( OSCOM_APP_PAYPAL_HS_STATUS == '1' ) {
      $form_url = 'https://securepayments.paypal.com/webapps/HostedSoleSolutionApp/webflow/sparta/hostedSoleSolutionProcess';
    } else {
      $form_url = 'https://securepayments.sandbox.paypal.com/webapps/HostedSoleSolutionApp/webflow/sparta/hostedSoleSolutionProcess';
    }
  } else {
    $form_url = OSCOM::link('checkout_payment.php', 'payment_error=paypal_pro_hs', 'SSL');
  }
?>
<!DOCTYPE html>
<html <?php echo HTML_PARAMS; ?>>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=<?php echo CHARSET; ?>" />
<title><?php echo HTML::outputProtected(TITLE); ?></title>
<base href="<?php echo (($request_type == 'SSL') ? HTTPS_SERVER : HTTP_SERVER) . DIR_WS_CATALOG; ?>" />
</head>
<body>

<div style="text-align: center;">
  <?php echo tep_image('ext/modules/payment/paypal/images/hss_load.gif');?>
</div>

<form name="pphs" action="<?php echo $form_url; ?>" method="post" <?php echo ($error == true ? 'target="_top"' : ''); ?>>
  <input type="hidden" name="hosted_button_id" value="<?php echo (isset($_SESSION['pphs_result']['HOSTEDBUTTONID']) ? HTML::outputProtected($_SESSION['pphs_result']['HOSTEDBUTTONID']) : ''); ?>" />
</form>

<script>
  document.pphs.submit();
</script>

</body>
</html>

<?php
  require(DIR_WS_INCLUDES . 'application_bottom.php');
?>
