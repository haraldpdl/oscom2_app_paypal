<?php
/**
  * osCommerce Online Merchant
  *
  * @copyright Copyright (c) 2015 osCommerce; http://www.oscommerce.com
  * @license GPL; http://www.oscommerce.com/gpllicense.txt
  */

namespace OSC\Apps\PayPal\PayPal\Module\Admin\Config\EC\Params;

use OSC\OM\HTML;
use OSC\OM\Registry;

class order_status_id extends \OSC\Apps\PayPal\PayPal\Module\Admin\Config\ParamsAbstract
{
    public $default = '0';
    public $sort_order = 800;

    protected $db;

    protected function init()
    {
        $this->db = Registry::get('Db');

        $this->title = $this->app->getDef('cfg_ec_order_status_id_title');
        $this->description = $this->app->getDef('cfg_ec_order_status_id_desc');
    }

    public function getSetField()
    {
        $statuses_array = [
            [
                'id' => '0',
                'text' => $this->app->getDef('cfg_ec_order_status_id_default')
            ]
        ];

        $Qstatuses = $this->db->get('orders_status', ['orders_status_id', 'orders_status_name'], ['language_id' => $_SESSION['languages_id']], 'orders_status_name');

        while ($Qstatuses->fetch()) {
            $statuses_array[] = [
                'id' => $Qstatuses->valueInt('orders_status_id'),
                'text' => $Qstatuses->value('orders_status_name')
            ];
        }

        $input = HTML::selectField('order_status_id', $statuses_array, OSCOM_APP_PAYPAL_EC_ORDER_STATUS_ID, 'id="inputEcOrderStatusId"');

        $result = <<<EOT
<div>
  <p>
    <label for="inputEcOrderStatusId">{$this->title}</label>

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
