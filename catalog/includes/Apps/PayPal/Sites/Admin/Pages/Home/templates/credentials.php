<?php
/**
  * osCommerce Online Merchant
  *
  * @copyright Copyright (c) 2015 osCommerce; http://www.oscommerce.com
  * @license GPL; http://www.oscommerce.com/gpllicense.txt
  */

use OSC\OM\HTML;
use OSC\OM\OSCOM;
use OSC\OM\Registry;

$OSCOM_Page = Registry::get('Site')->getPage();

$current_module = $OSCOM_Page->data['current_module'];

require(__DIR__ . '/template_top.php');
?>

<div id="appPayPalToolbar" style="padding-bottom: 15px;">
  <?php echo $OSCOM_PayPal->drawButton($OSCOM_PayPal->getDef('section_paypal'), OSCOM::link('index.php', 'A&PayPal&Credentials&module=PP'), 'info', 'data-module="PP"'); ?>
  <?php echo $OSCOM_PayPal->drawButton($OSCOM_PayPal->getDef('section_payflow'), OSCOM::link('index.php', 'A&PayPal&Credentials&module=PF'), 'info', 'data-module="PF"'); ?>

<?php
  if ( ($current_module == 'PP') && $OSCOM_PayPal->isReqApiCountrySupported(STORE_COUNTRY) ) {
?>

  <span style="float: right;">
    <?php echo $OSCOM_PayPal->drawButton($OSCOM_PayPal->getDef('button_retrieve_live_credentials'), OSCOM::link('index.php', 'A&PayPal&Start&Process&type=live'), 'warning'); ?>
    <?php echo $OSCOM_PayPal->drawButton($OSCOM_PayPal->getDef('button_retrieve_sandbox_credentials'), OSCOM::link('index.php', 'A&PayPal&Start&Process&type=sandbox'), 'warning'); ?>
  </span>

<?php
  }
?>

</div>

<form name="paypalCredentials" action="<?php echo OSCOM::link('index.php', 'A&PayPal&Credentials&Process&module=' . $current_module); ?>" method="post" class="pp-form">

<?php
  if ( $current_module == 'PP' ) {
?>

<h3 class="pp-panel-header-warning"><?php echo $OSCOM_PayPal->getDef('paypal_live_title'); ?></h3>
<div class="pp-panel pp-panel-warning">
  <table>
    <tr>
      <td width="420px" valign="top">
        <div>
          <p>
            <label for="live_username"><?php echo $OSCOM_PayPal->getDef('paypal_live_api_username'); ?></label>
            <?php echo HTML::inputField('live_username', OSCOM_APP_PAYPAL_LIVE_API_USERNAME); ?>
          </p>
        </div>

        <div>
          <p>
            <label for="live_password"><?php echo $OSCOM_PayPal->getDef('paypal_live_api_password'); ?></label>
            <?php echo HTML::inputField('live_password', OSCOM_APP_PAYPAL_LIVE_API_PASSWORD); ?>
          </p>
        </div>

        <div>
          <p>
            <label for="live_signature"><?php echo $OSCOM_PayPal->getDef('paypal_live_api_signature'); ?></label>
            <?php echo HTML::inputField('live_signature', OSCOM_APP_PAYPAL_LIVE_API_SIGNATURE); ?>
          </p>
        </div>
      </td>
      <td width="420px" valign="top">
        <div>
          <p>
            <label for="live_email"><?php echo $OSCOM_PayPal->getDef('paypal_live_email_address'); ?></label>
            <?php echo HTML::inputField('live_email', OSCOM_APP_PAYPAL_LIVE_SELLER_EMAIL); ?>
          </p>
        </div>

        <div>
          <p>
            <label for="live_email_primary"><?php echo $OSCOM_PayPal->getDef('paypal_live_primary_email_address'); ?></label>
            <?php echo HTML::inputField('live_email_primary', OSCOM_APP_PAYPAL_LIVE_SELLER_EMAIL_PRIMARY); ?>
          </p>

          <p><em><?php echo $OSCOM_PayPal->getDef('paypal_live_primary_email_address_desc'); ?></em></p>
        </div>
      </td>
    </tr>
  </table>
</div>

<h3 class="pp-panel-header-warning"><?php echo $OSCOM_PayPal->getDef('paypal_sandbox_title'); ?></h3>
<div class="pp-panel pp-panel-warning">
  <table>
    <tr>
      <td width="420px" valign="top">
        <div>
          <p>
            <label for="sandbox_username"><?php echo $OSCOM_PayPal->getDef('paypal_sandbox_api_username'); ?></label>
            <?php echo HTML::inputField('sandbox_username', OSCOM_APP_PAYPAL_SANDBOX_API_USERNAME); ?>
          </p>
        </div>

        <div>
          <p>
            <label for="sandbox_password"><?php echo $OSCOM_PayPal->getDef('paypal_sandbox_api_password'); ?></label>
            <?php echo HTML::inputField('sandbox_password', OSCOM_APP_PAYPAL_SANDBOX_API_PASSWORD); ?>
          </p>
        </div>

        <div>
          <p>
            <label for="sandbox_signature"><?php echo $OSCOM_PayPal->getDef('paypal_sandbox_api_signature'); ?></label>
            <?php echo HTML::inputField('sandbox_signature', OSCOM_APP_PAYPAL_SANDBOX_API_SIGNATURE); ?>
          </p>
        </div>
      </td>
      <td width="420px" valign="top">
        <div>
          <p>
            <label for="sandbox_email"><?php echo $OSCOM_PayPal->getDef('paypal_sandbox_email_address'); ?></label>
            <?php echo HTML::inputField('sandbox_email', OSCOM_APP_PAYPAL_SANDBOX_SELLER_EMAIL); ?>
          </p>
        </div>

        <div>
          <p>
            <label for="sandbox_email_primary"><?php echo $OSCOM_PayPal->getDef('paypal_sandbox_primary_email_address'); ?></label>
            <?php echo HTML::inputField('sandbox_email_primary', OSCOM_APP_PAYPAL_SANDBOX_SELLER_EMAIL_PRIMARY); ?>
          </p>

          <p><em><?php echo $OSCOM_PayPal->getDef('paypal_sandbox_primary_email_address_desc'); ?></em></p>
        </div>
      </td>
    </tr>
  </table>
</div>

<?php
  } elseif ( $current_module == 'PF' ) {
?>

<h3 class="pp-panel-header-warning"><?php echo $OSCOM_PayPal->getDef('payflow_live_title'); ?></h3>
<div class="pp-panel pp-panel-warning">
  <div>
    <p>
      <label for="live_partner"><?php echo $OSCOM_PayPal->getDef('payflow_live_partner'); ?></label>
      <?php echo HTML::inputField('live_partner', OSCOM_APP_PAYPAL_PF_LIVE_PARTNER); ?>
    </p>
  </div>

  <div>
    <p>
      <label for="live_vendor"><?php echo $OSCOM_PayPal->getDef('payflow_live_merchant_login'); ?></label>
      <?php echo HTML::inputField('live_vendor', OSCOM_APP_PAYPAL_PF_LIVE_VENDOR); ?>
    </p>
  </div>

  <div>
    <p>
      <label for="live_user"><?php echo $OSCOM_PayPal->getDef('payflow_live_user'); ?></label>
      <?php echo HTML::inputField('live_user', OSCOM_APP_PAYPAL_PF_LIVE_USER); ?>
    </p>
  </div>

  <div>
    <p>
      <label for="live_password"><?php echo $OSCOM_PayPal->getDef('payflow_live_password'); ?></label>
      <?php echo HTML::inputField('live_password', OSCOM_APP_PAYPAL_PF_LIVE_PASSWORD); ?>
    </p>
  </div>
</div>

<h3 class="pp-panel-header-warning"><?php echo $OSCOM_PayPal->getDef('payflow_sandbox_title'); ?></h3>
<div class="pp-panel pp-panel-warning">
  <div>
    <p>
      <label for="sandbox_partner"><?php echo $OSCOM_PayPal->getDef('payflow_sandbox_partner'); ?></label>
      <?php echo HTML::inputField('sandbox_partner', OSCOM_APP_PAYPAL_PF_SANDBOX_PARTNER); ?>
    </p>
  </div>

  <div>
    <p>
      <label for="sandbox_vendor"><?php echo $OSCOM_PayPal->getDef('payflow_sandbox_merchant_login'); ?></label>
      <?php echo HTML::inputField('sandbox_vendor', OSCOM_APP_PAYPAL_PF_SANDBOX_VENDOR); ?>
    </p>
  </div>

  <div>
    <p>
      <label for="sandbox_user"><?php echo $OSCOM_PayPal->getDef('payflow_sandbox_user'); ?></label>
      <?php echo HTML::inputField('sandbox_user', OSCOM_APP_PAYPAL_PF_SANDBOX_USER); ?>
    </p>
  </div>

  <div>
    <p>
      <label for="sandbox_password"><?php echo $OSCOM_PayPal->getDef('payflow_sandbox_password'); ?></label>
      <?php echo HTML::inputField('sandbox_password', OSCOM_APP_PAYPAL_PF_SANDBOX_PASSWORD); ?>
    </p>
  </div>
</div>

<?php
  }
?>

<p><?php echo $OSCOM_PayPal->drawButton($OSCOM_PayPal->getDef('button_save'), null, 'success'); ?></p>

</form>

<script>
$(function() {
  $('#appPayPalToolbar a[data-module="<?php echo $current_module; ?>"]').addClass('pp-button-primary');
});
</script>

<?php
require(__DIR__ . '/template_bottom.php');
?>
