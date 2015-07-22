<?php
/**
  * osCommerce Online Merchant
  *
  * @copyright Copyright (c) 2015 osCommerce; http://www.oscommerce.com
  * @license GPL; http://www.oscommerce.com/gpllicense.txt
  */

require(__DIR__ . '/template_top.php');
?>

<div id="ppStartDashboard" style="width: 100%;">

<?php
if ($OSCOM_PayPal->isReqApiCountrySupported(STORE_COUNTRY)) {
?>

  <div style="float: left; width: 50%;">
    <div style="padding: 2px;">
      <h3 class="pp-panel-header-info"><?php echo $OSCOM_PayPal->getDef('onboarding_intro_title'); ?></h3>
      <div class="pp-panel pp-panel-info">
        <?php echo $OSCOM_PayPal->getDef('onboarding_intro_body', array('button_retrieve_live_credentials' => $OSCOM_PayPal->drawButton($OSCOM_PayPal->getDef('button_retrieve_live_credentials'), $OSCOM_PayPal->link('Start&Process&type=live'), 'info'), 'button_retrieve_sandbox_credentials' => $OSCOM_PayPal->drawButton($OSCOM_PayPal->getDef('button_retrieve_sandbox_credentials'), $OSCOM_PayPal->link('Start&Process&type=sandbox'), 'info'))); ?>
      </div>
    </div>
  </div>

<?php
}
?>

  <div style="float: left; width: 50%;">
    <div style="padding: 2px;">
      <h3 class="pp-panel-header-warning"><?php echo $OSCOM_PayPal->getDef('manage_credentials_title'); ?></h3>
      <div class="pp-panel pp-panel-warning">
        <?php echo $OSCOM_PayPal->getDef('manage_credentials_body', array('button_manage_credentials' => $OSCOM_PayPal->drawButton($OSCOM_PayPal->getDef('button_manage_credentials'), $OSCOM_PayPal->link('Credentials'), 'warning'))); ?>
      </div>
    </div>
  </div>
</div>

<script>
$(function() {
  $('#ppStartDashboard > div:nth-child(2)').each(function() {
    if ( $(this).prev().height() < $(this).height() ) {
      $(this).prev().height($(this).height());
    } else {
      $(this).height($(this).prev().height());
    }
  });
});
</script>

<?php
require(__DIR__ . '/template_bottom.php');
?>
