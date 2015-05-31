<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2014 osCommerce

  Released under the GNU General Public License
*/

  use OSC\OM\HTML;
  use OSC\OM\Registry;

  class OSCOM_PayPal_Cfg_transactions_order_status_id {
    var $default = '0';
    var $title;
    var $description;
    var $sort_order = 200;

    function OSCOM_PayPal_Cfg_transactions_order_status_id() {
      global $OSCOM_PayPal;

      $this->title = $OSCOM_PayPal->getDef('cfg_transactions_order_status_id_title');
      $this->description = $OSCOM_PayPal->getDef('cfg_transactions_order_status_id_desc');
    }

    function getSetField() {
      $OSCOM_Db = Registry::get('Db');

      $statuses_array = array();

      $Qstatuses = $OSCOM_Db->get('orders_status', ['orders_status_id', 'orders_status_name'], ['language_id' => $_SESSION['languages_id'], 'public_flag' => '0'], 'orders_status_name');

      while ($Qstatuses->next()) {
        $statuses_array[] = array('id' => $Qstatuses->valueInt('orders_status_id'),
                                  'text' => $Qstatuses->value('orders_status_name'));
      }

      $input = HTML::selectField('transactions_order_status_id', $statuses_array, OSCOM_APP_PAYPAL_TRANSACTIONS_ORDER_STATUS_ID, 'id="inputTransactionsOrderStatusId"');

      $result = <<<EOT
<div>
  <p>
    <label for="inputTransactionsOrderStatusId">{$this->title}</label>

    {$this->description}
  </p>

  <div>
    {$input}
  </div>
</div>
EOT;

      return $result;
    }
  }
?>
