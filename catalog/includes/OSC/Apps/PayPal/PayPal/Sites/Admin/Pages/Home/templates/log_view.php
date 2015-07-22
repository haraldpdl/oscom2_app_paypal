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

require(__DIR__ . '/template_top.php');
?>

<div style="text-align: right; padding-bottom: 15px;">
  <?php echo $OSCOM_PayPal->drawButton($OSCOM_PayPal->getDef('button_back'), $OSCOM_PayPal->link('Log&page=' . $_GET['page']), 'info'); ?>
</div>

<table class="pp-table pp-table-hover" width="100%">
  <thead>
    <tr>
      <th colspan="2"><?php echo $OSCOM_PayPal->getDef('table_heading_entries_request'); ?></th>
    </tr>
  </thead>
  <tbody>

<?php
foreach ($OSCOM_Page->data['log_request'] as $key => $value) {
?>

    <tr>
      <td width="25%"><?php echo HTML::outputProtected($key); ?></td>
      <td><?php echo HTML::outputProtected($value); ?></td>
    </tr>

<?php
}
?>

  </tbody>
</table>

<table class="pp-table pp-table-hover" width="100%">
  <thead>
    <tr>
      <th colspan="2"><?php echo $OSCOM_PayPal->getDef('table_heading_entries_response'); ?></th>
    </tr>
  </thead>
  <tbody>

<?php
foreach ($OSCOM_Page->data['log_response'] as $key => $value) {
?>

    <tr>
      <td width="25%"><?php echo HTML::outputProtected($key); ?></td>
      <td><?php echo HTML::outputProtected($value); ?></td>
    </tr>

<?php
}
?>

  </tbody>
</table>

<?php
require(__DIR__ . '/template_bottom.php');
?>
