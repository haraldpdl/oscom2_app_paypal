<?php
/**
  * osCommerce Online Merchant
  *
  * @copyright Copyright (c) 2015 osCommerce; http://www.oscommerce.com
  * @license GPL; http://www.oscommerce.com/gpllicense.txt
  */

use OSC\OM\OSCOM;

require(__DIR__ . '/template_top.php');
?>

<div id="ppStartDashboard" style="width: 100%;">
  <div style="float: left; width: 50%;">
    <div style="padding: 2px;">
      <h3 class="pp-panel-header-info"><?php echo $OSCOM_PayPal->getDef('online_documentation_title'); ?></h3>
      <div class="pp-panel pp-panel-info">
        <?php echo $OSCOM_PayPal->getDef('online_documentation_body', array('button_online_documentation' => $OSCOM_PayPal->drawButton($OSCOM_PayPal->getDef('button_online_documentation'), 'http://library.oscommerce.com/Package&paypal&oscom23', 'info', 'target="_blank"'))); ?>
      </div>
    </div>
  </div>

  <div style="float: left; width: 50%;">
    <div style="padding: 2px;">
      <h3 class="pp-panel-header-warning"><?php echo $OSCOM_PayPal->getDef('online_forum_title'); ?></h3>
      <div class="pp-panel pp-panel-warning">
        <?php echo $OSCOM_PayPal->getDef('online_forum_body', array('button_online_forum' => $OSCOM_PayPal->drawButton($OSCOM_PayPal->getDef('button_online_forum'), 'http://forums.oscommerce.com/forum/54-paypal/', 'warning', 'target="_blank"'))); ?>
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

  OSCOM.APP.PAYPAL.versionCheck();

  $.getJSON('<?php echo OSCOM::link('index.php', 'A&PayPal&RPC&GetNews'); ?>', function (data) {
    if ( (typeof data == 'object') && ('rpcStatus' in data) && (data['rpcStatus'] == 1) ) {
      var ppNewsContent = '<div style="display: block; margin-top: 5px; min-height: 65px;"><a href="' + data.url + '" target="_blank"><img src="' + data.image + '" width="468" height="60" alt="' + data.title + '" border="0" /></a>';

      if ( data.status_update.length > 0 ) {
        ppNewsContent = ppNewsContent + '<div style="font-size: 0.95em; padding-left: 480px; margin-top: -70px; padding-top: 4px; min-height: 60px;"><p>' + data.status_update + '</p></div>';
      }

      ppNewsContent = ppNewsContent + '</div>';

      $('#ppStartDashboard').after(ppNewsContent);
    }
  });
});
</script>

<?php
require(__DIR__ . '/template_bottom.php');
?>
