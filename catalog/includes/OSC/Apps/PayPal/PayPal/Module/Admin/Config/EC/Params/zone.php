<?php
/**
  * osCommerce Online Merchant
  *
  * @copyright Copyright (c) 2015 osCommerce; http://www.oscommerce.com
  * @license GPL; http://www.oscommerce.com/gpllicense.txt
  */

namespace OSC\Apps\PayPal\Module\Admin\Config\EC\Params;

use OSC\OM\HTML;
use OSC\OM\Registry;

class zone extends \OSC\Apps\PayPal\Module\Admin\Config\ParamsAbstract
{
    public $default = '0';
    public $sort_order = 900;

    protected $db;

    protected function init()
    {
        $this->db = Registry::get('Db');

        $this->title = $this->app->getDef('cfg_ec_zone_title');
        $this->description = $this->app->getDef('cfg_ec_zone_desc');
    }

    public function getSetField()
    {
        $zone_class_array = [
            [
                'id' => '0',
                'text' => $this->app->getDef('cfg_ec_zone_global')
            ]
        ];

        $Qclasses = $this->db->get('geo_zones', ['geo_zone_id', 'geo_zone_name'], null, 'geo_zone_name');

        while ($Qclasses->fetch()) {
            $zone_class_array[] = [
                'id' => $Qclasses->valueInt('geo_zone_id'),
                'text' => $Qclasses->value('geo_zone_name')
            ];
        }

        $input = HTML::selectField('zone', $zone_class_array, OSCOM_APP_PAYPAL_EC_ZONE, 'id="inputEcZone"');

        $result = <<<EOT
<div>
  <p>
    <label for="inputEcZone">{$this->title}</label>

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
