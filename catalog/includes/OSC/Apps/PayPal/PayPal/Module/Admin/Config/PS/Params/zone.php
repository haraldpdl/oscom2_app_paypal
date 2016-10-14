<?php
/**
  * osCommerce Online Merchant
  *
  * @copyright Copyright (c) 2015 osCommerce; http://www.oscommerce.com
  * @license GPL; http://www.oscommerce.com/gpllicense.txt
  */

namespace OSC\Apps\PayPal\PayPal\Module\Admin\Config\PS\Params;

use OSC\OM\HTML;

class zone extends \OSC\Apps\PayPal\PayPal\Module\Admin\Config\ConfigParamAbstract
{
    public $default = '0';
    public $sort_order = 600;

    protected function init()
    {
        $this->title = $this->app->getDef('cfg_ps_zone_title');
        $this->description = $this->app->getDef('cfg_ps_zone_desc');
    }

    public function getInputField()
    {
        $zone_class_array = [
            [
                'id' => '0',
                'text' => $this->app->getDef('cfg_ps_zone_global')
            ]
        ];

        $Qclasses = $this->app->db->get('geo_zones', [
            'geo_zone_id',
            'geo_zone_name'
        ], null, 'geo_zone_name');

        while ($Qclasses->fetch()) {
            $zone_class_array[] = [
                'id' => $Qclasses->valueInt('geo_zone_id'),
                'text' => $Qclasses->value('geo_zone_name')
            ];
        }

        $input = HTML::selectField($this->key, $zone_class_array, $this->getInputValue());

        return $input;
    }
}
