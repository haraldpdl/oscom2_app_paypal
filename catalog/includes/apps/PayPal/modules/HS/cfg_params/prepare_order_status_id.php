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

  class OSCOM_PayPal_HS_Cfg_prepare_order_status_id {
    var $default = '0';
    var $title;
    var $description;
    var $sort_order = 300;

    function OSCOM_PayPal_HS_Cfg_prepare_order_status_id() {
      global $OSCOM_PayPal;

      $OSCOM_Db = Registry::get('Db');

      $this->title = $OSCOM_PayPal->getDef('cfg_hs_prepare_order_status_id_title');
      $this->description = $OSCOM_PayPal->getDef('cfg_hs_prepare_order_status_id_desc');

      if ( !defined('OSCOM_APP_PAYPAL_HS_PREPARE_ORDER_STATUS_ID') ) {
        $Qcheck = $OSCOM_Db->get('orders_status', 'orders_status_id', ['orders_status_name' => 'Preparing [PayPal Pro HS]'], null, 1);

        if ($Qcheck->fetch() === false) {
          $Qstatus = $OSCOM_Db->get('orders_status', 'max(orders_status_id) as status_id');

          $status_id = $Qstatus->valueInt('status_id') + 1;

          $languages = tep_get_languages();

          foreach ($languages as $lang) {
            $OSCOM_Db->save('orders_status', [
              'orders_status_id' => $status_id,
              'language_id' => $lang['id'],
              'orders_status_name' => 'Preparing [PayPal Pro HS]',
              'public_flag' => '0',
              'downloads_flag' => '0'
            ]);
          }
        } else {
          $status_id = $Qcheck->valueInt('orders_status_id');
        }
      } else {
        $status_id = OSCOM_APP_PAYPAL_HS_PREPARE_ORDER_STATUS_ID;
      }

      $this->default = $status_id;
    }

    function getSetField() {
      global $OSCOM_PayPal;

      $OSCOM_Db = Registry::get('Db');

      $statuses_array = array(array('id' => '0', 'text' => $OSCOM_PayPal->getDef('cfg_hs_prepare_order_status_id_default')));

      $Qstatuses = $OSCOM_Db->get('orders_status', ['orders_status_id', 'orders_status_name'], ['language_id' => $_SESSION['languages_id']], 'orders_status_name');

      while ($Qstatuses->next()) {
        $statuses_array[] = array('id' => $Qstatuses->valueInt('orders_status_id'),
                                  'text' => $Qstatuses->value('orders_status_name'));
      }

      $input = HTML::selectField('prepare_order_status_id', $statuses_array, OSCOM_APP_PAYPAL_HS_PREPARE_ORDER_STATUS_ID, 'id="inputHsPrepareOrderStatusId"');

      $result = <<<EOT
<div>
  <p>
    <label for="inputHsPrepareOrderStatusId">{$this->title}</label>

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
