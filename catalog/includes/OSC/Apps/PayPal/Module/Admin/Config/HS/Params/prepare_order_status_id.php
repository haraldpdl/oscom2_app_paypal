<?php
/**
  * osCommerce Online Merchant
  *
  * @copyright Copyright (c) 2015 osCommerce; http://www.oscommerce.com
  * @license GPL; http://www.oscommerce.com/gpllicense.txt
  */

namespace OSC\OM\Apps\PayPal\Module\Admin\Config\HS\Params;

use OSC\OM\HTML;
use OSC\OM\Registry;

class prepare_order_status_id extends \OSC\OM\Apps\PayPal\Module\Admin\Config\ParamsAbstract
{
    public $default = '0';
    public $sort_order = 300;

    protected $db;

    protected function init()
    {
        $this->db = Registry::get('Db');

        $this->title = $this->app->getDef('cfg_hs_prepare_order_status_id_title');
        $this->description = $this->app->getDef('cfg_hs_prepare_order_status_id_desc');

        if (!defined('OSCOM_APP_PAYPAL_HS_PREPARE_ORDER_STATUS_ID')) {
            $Qcheck = $this->db->get('orders_status', 'orders_status_id', ['orders_status_name' => 'Preparing [PayPal Pro HS]'], null, 1);

            if ($Qcheck->fetch() === false) {
                $Qstatus = $this->db->get('orders_status', 'max(orders_status_id) as status_id');

                $status_id = $Qstatus->valueInt('status_id') + 1;

                $languages = tep_get_languages();

                foreach ($languages as $lang) {
                    $this->db->save('orders_status', [
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

    public function getSetField()
    {
        $statuses_array = [
            [
                'id' => '0',
                'text' => $this->app->getDef('cfg_hs_prepare_order_status_id_default')
            ]
        ];

        $Qstatuses = $this->db->get('orders_status', ['orders_status_id', 'orders_status_name'], ['language_id' => $_SESSION['languages_id']], 'orders_status_name');

        while ($Qstatuses->fetch()) {
            $statuses_array[] = [
                'id' => $Qstatuses->valueInt('orders_status_id'),
                'text' => $Qstatuses->value('orders_status_name')
            ];
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
