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

  class OSCOM_PayPal_HS_Cfg_zone {
    var $default = '0';
    var $title;
    var $description;
    var $sort_order = 500;

    function OSCOM_PayPal_HS_Cfg_zone() {
      global $OSCOM_PayPal;

      $this->title = $OSCOM_PayPal->getDef('cfg_hs_zone_title');
      $this->description = $OSCOM_PayPal->getDef('cfg_hs_zone_desc');
    }

    function getSetField() {
      global $OSCOM_PayPal;

      $OSCOM_Db = Registry::get('Db');

      $zone_class_array = array(array('id' => '0', 'text' => $OSCOM_PayPal->getDef('cfg_hs_zone_global')));

      $Qclasses = $OSCOM_Db->get('geo_zones', ['geo_zone_id', 'geo_zone_name'], null, 'geo_zone_name');

      while ($Qclasses->fetch()) {
        $zone_class_array[] = array('id' => $Qclasses->valueInt('geo_zone_id'),
                                    'text' => $Qclasses->value('geo_zone_name'));
      }

      $input = HTML::selectField('zone', $zone_class_array, OSCOM_APP_PAYPAL_HS_ZONE, 'id="inputHsZone"');

      $result = <<<EOT
<div>
  <p>
    <label for="inputHsZone">{$this->title}</label>

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