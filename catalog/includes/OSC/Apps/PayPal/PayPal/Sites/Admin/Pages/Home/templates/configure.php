<?php
/**
  * osCommerce Online Merchant
  *
  * @copyright Copyright (c) 2015 osCommerce; http://www.oscommerce.com
  * @license GPL; http://www.oscommerce.com/gpllicense.txt
  */

use OSC\OM\HTML;
use OSC\OM\Registry;

$OSCOM_Page = Registry::get('Site')->getPage();

$current_module = $OSCOM_Page->data['current_module'];

$OSCOM_PayPal_Config = Registry::get('PayPalAdminConfig' . $current_module);

require(__DIR__ . '/template_top.php');
?>

<div id="appPayPalToolbar" style="padding-bottom: 15px;">

<?php
foreach ($OSCOM_PayPal->getConfigModules() as $m) {
    if ($OSCOM_PayPal->getConfigModuleInfo($m, 'is_installed') === true) {
        echo $OSCOM_PayPal->drawButton($OSCOM_PayPal->getConfigModuleInfo($m, 'short_title'), $OSCOM_PayPal->link('Configure&module=' . $m), 'info', 'data-module="' . $m . '"') . "\n";
    }
}

echo $OSCOM_PayPal->drawButton($OSCOM_PayPal->getDef('section_more'), '#', 'info', 'data-module="appPayPalToolbarMoreButton"');
?>

</div>

<ul id="appPayPalToolbarMore" class="pp-button-menu">

<?php
foreach ($OSCOM_PayPal->getConfigModules() as $m) {
    if ($OSCOM_PayPal->getConfigModuleInfo($m, 'is_installed') === false) {
        echo '<li><a href="' . $OSCOM_PayPal->link('Configure&module=' . $m) . '">' . $OSCOM_PayPal->getConfigModuleInfo($m, 'title') . '</a></li>';
    }
}
?>

</ul>

<script>
$(function() {
  $('#appPayPalToolbarMore').hide();

  if ( $('#appPayPalToolbarMore li').size() > 0 ) {
    $('#appPayPalToolbarMore').menu().hover(function() {
      $(this).show();
    }, function() {
      $(this).hide();
    });

    $('#appPayPalToolbar a[data-module="appPayPalToolbarMoreButton"]').click(function() {
      return false;
    }).hover(function() {
      $('#appPayPalToolbarMore').show().position({
        my: 'left top',
        at: 'left bottom',
        of: this
      });
    }, function() {
      $('#appPayPalToolbarMore').hide();
    });
  } else {
    $('#appPayPalToolbar a[data-module="appPayPalToolbarMoreButton"]').hide();
  }
});
</script>

<?php
if ($OSCOM_PayPal_Config->is_installed === true) {
    foreach ($OSCOM_PayPal_Config->req_notes as $rn) {
        echo '<div class="pp-panel pp-panel-warning"><p>' . $rn . '</p></div>';
    }
?>

<form name="paypalConfigure" action="<?php echo $OSCOM_PayPal->link('Configure&Process&module=' . $current_module); ?>" method="post" class="pp-form">

<h3 class="pp-panel-header-info"><?php echo $OSCOM_PayPal->getConfigModuleInfo($current_module, 'title'); ?></h3>
<div class="pp-panel pp-panel-info" style="padding-bottom: 15px;">

<?php
    foreach ($OSCOM_PayPal_Config->getInputParameters() as $cfg) {
        echo $cfg;
    }
?>

</div>

<p>

<?php
    echo $OSCOM_PayPal->drawButton($OSCOM_PayPal->getDef('button_save'), null, 'success');

    if ($OSCOM_PayPal->getConfigModuleInfo($current_module, 'is_uninstallable') === true) {
        echo '  <span style="float: right;">' . $OSCOM_PayPal->drawButton($OSCOM_PayPal->getDef('button_dialog_uninstall'), '#', 'warning', 'data-button="paypalButtonUninstallModule"') . '</span>';
    }
?>

</p>

</form>

<?php
    if ($OSCOM_PayPal->getConfigModuleInfo($current_module, 'is_uninstallable') === true) {
?>

<div id="paypal-dialog-uninstall" title="<?php echo HTML::output($OSCOM_PayPal->getDef('dialog_uninstall_title')); ?>">
  <p><span class="ui-icon ui-icon-alert" style="float:left; margin:0 7px 20px 0;"></span><?php echo $OSCOM_PayPal->getDef('dialog_uninstall_body'); ?></p>
</div>

<script>
$(function() {
  $('#paypal-dialog-uninstall').dialog({
    autoOpen: false,
    resizable: false,
    modal: true,
    buttons: {
      "<?php echo addslashes($OSCOM_PayPal->getDef('button_uninstall')); ?>": function() {
        window.location = '<?php echo addslashes($OSCOM_PayPal->link('Configure&Uninstall&module=' . $current_module)); ?>';
      },
      "<?php echo addslashes($OSCOM_PayPal->getDef('button_cancel')); ?>": function() {
        $(this).dialog('close');
      }
    }
  });

  $('a[data-button="paypalButtonUninstallModule"]').click(function(e) {
    e.preventDefault();

    $('#paypal-dialog-uninstall').dialog('open');
  });
});
</script>

<?php
    }
} else {
?>

<h3 class="pp-panel-header-warning"><?php echo $OSCOM_PayPal->getConfigModuleInfo($current_module, 'title'); ?></h3>
<div class="pp-panel pp-panel-warning">
  <?php echo $OSCOM_PayPal->getConfigModuleInfo($current_module, 'introduction'); ?>
</div>

<p>
  <?php echo $OSCOM_PayPal->drawButton($OSCOM_PayPal->getDef('button_install_title', array('title' => $OSCOM_PayPal->getConfigModuleInfo($current_module, 'title'))), $OSCOM_PayPal->link('Configure&Install&module=' . $current_module), 'success'); ?>
</p>

<?php
}
?>

<script>
$(function() {
  $('#appPayPalToolbar a[data-module="<?php echo (($OSCOM_PayPal->getConfigModuleInfo($current_module, 'is_installed') === true) ? $current_module : 'appPayPalToolbarMoreButton'); ?>"]').addClass('pp-button-primary');
});
</script>

<?php
require(__DIR__ . '/template_bottom.php');
?>
