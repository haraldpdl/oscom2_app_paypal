<?php
/**
  * osCommerce Online Merchant
  *
  * @copyright Copyright (c) 2015 osCommerce; http://www.oscommerce.com
  * @license GPL; http://www.oscommerce.com/gpllicense.txt
  */

use OSC\OM\HTML;

require(__DIR__ . '/template_top.php');

$Qlog = $OSCOM_Db->prepare('select SQL_CALC_FOUND_ROWS l.id, l.customers_id, l.module, l.action, l.result, l.ip_address, unix_timestamp(l.date_added) as date_added, c.customers_firstname, c.customers_lastname from :table_oscom_app_paypal_log l left join :table_customers c on (l.customers_id = c.customers_id) order by l.date_added desc limit :page_set_offset, :page_set_max_results');
$Qlog->setPageSet(MAX_DISPLAY_SEARCH_RESULTS);
$Qlog->execute();
?>

<table width="100%" style="margin-bottom: 5px;">
  <tr>
    <td><?php echo $OSCOM_PayPal->drawButton($OSCOM_PayPal->getDef('button_dialog_delete'), '#', 'warning', 'data-button="delLogs"'); ?></td>
    <td style="text-align: right;"><?php echo $Qlog->getPageSetLinks(tep_get_all_get_params(array('page'))); ?></td>
  </tr>
</table>

<table id="ppTableLog" class="pp-table pp-table-hover" width="100%">
  <thead>
    <tr>
      <th colspan="2"><?php echo $OSCOM_PayPal->getDef('table_heading_action'); ?></th>
      <th><?php echo $OSCOM_PayPal->getDef('table_heading_ip'); ?></th>
      <th><?php echo $OSCOM_PayPal->getDef('table_heading_customer'); ?></th>
      <th colspan="2"><?php echo $OSCOM_PayPal->getDef('table_heading_date'); ?></th>
    </tr>
  </thead>
  <tbody>

<?php
if ($Qlog->getPageSetTotalRows() > 0) {
    while ($Qlog->fetch()) {
        $customers_name = null;

        if ($Qlog->valueInt('customers_id') > 0) {
            $customers_name = trim($Qlog->value('customers_firstname') . ' ' . $Qlog->value('customers_lastname'));

            if (empty($customers_name)) {
                $customers_name = '- ? -';
            }
        }
?>

    <tr>
      <td style="text-align: center; width: 30px;"><span class="<?php echo ($Qlog->valueInt('result') === 1) ? 'logSuccess' : 'logError'; ?>"><?php echo $Qlog->value('module'); ?></span></td>
      <td><?php echo $Qlog->value('action'); ?></td>
      <td><?php echo long2ip($Qlog->value('ip_address')); ?></td>
      <td><?php echo (!empty($customers_name)) ? HTML::outputProtected($customers_name) : '<i>' . $OSCOM_PayPal->getDef('guest') . '</i>'; ?></td>
      <td><?php echo date(PHP_DATE_TIME_FORMAT, $Qlog->value('date_added')); ?></td>
      <td class="pp-table-action"><small><?php echo $OSCOM_PayPal->drawButton($OSCOM_PayPal->getDef('button_view'), $OSCOM_PayPal->link('Log&View&page=' . $_GET['page'] . '&lID=' . $Qlog->valueInt('id')), 'info'); ?></small></td>
    </tr>

<?php
    }
  } else {
?>

    <tr>
      <td colspan="6" style="padding: 10px;"><?php echo $OSCOM_PayPal->getDef('no_entries'); ?></td>
    </tr>

<?php
  }
?>

  </tbody>
</table>

<table width="100%">
  <tr>
    <td valign="top"><?php echo $Qlog->getPageSetLabel($OSCOM_PayPal->getDef('listing_number_of_log_entries')); ?></td>
    <td style="text-align: right;"><?php echo $Qlog->getPageSetLinks(tep_get_all_get_params(array('page'))); ?></td>
  </tr>
</table>

<div id="delLogs-dialog-confirm" title="<?php echo HTML::output($OSCOM_PayPal->getDef('dialog_delete_title')); ?>">
  <p><span class="ui-icon ui-icon-alert" style="float:left; margin:0 7px 20px 0;"></span><?php echo $OSCOM_PayPal->getDef('dialog_delete_body'); ?></p>
</div>

<script>
$(function() {
  $('#delLogs-dialog-confirm').dialog({
    autoOpen: false,
    resizable: false,
    height: 140,
    modal: true,
    buttons: {
      "<?php echo addslashes($OSCOM_PayPal->getDef('button_delete')); ?>": function() {
        window.location = '<?php echo $OSCOM_PayPal->link('Log&DeleteAll'); ?>';
      },
      "<?php echo addslashes($OSCOM_PayPal->getDef('button_cancel')); ?>": function() {
        $( this ).dialog('close');
      }
    }
  });

  $('a[data-button="delLogs"]').click(function(e) {
    e.preventDefault();

    $('#delLogs-dialog-confirm').dialog('open');
  });
});
</script>

<?php
require(__DIR__ . '/template_bottom.php');
?>
