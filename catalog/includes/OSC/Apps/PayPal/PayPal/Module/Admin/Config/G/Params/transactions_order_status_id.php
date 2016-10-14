<?php
/**
  * osCommerce Online Merchant
  *
  * @copyright Copyright (c) 2015 osCommerce; http://www.oscommerce.com
  * @license GPL; http://www.oscommerce.com/gpllicense.txt
  */

namespace OSC\Apps\PayPal\PayPal\Module\Admin\Config\G\Params;

use OSC\OM\HTML;

class transactions_order_status_id extends \OSC\Apps\PayPal\PayPal\Module\Admin\Config\ConfigParamAbstract
{
    public $default = '0';
    public $sort_order = 200;

    protected function init()
    {
        $this->title = $this->app->getDef('cfg_transactions_order_status_id_title');
        $this->description = $this->app->getDef('cfg_transactions_order_status_id_desc');
    }

    public function getInputField()
    {
        $statuses_array = [];

        $Qstatuses = $this->app->db->get('orders_status', [
            'orders_status_id',
            'orders_status_name'
        ], [
            'language_id' => $_SESSION['languages_id'],
            'public_flag' => '0'
        ], 'orders_status_name');

        while ($Qstatuses->fetch()) {
            $statuses_array[] = [
                'id' => $Qstatuses->valueInt('orders_status_id'),
                'text' => $Qstatuses->value('orders_status_name')
            ];
        }

        $input = HTML::selectField($this->key, $statuses_array, $this->getInputValue());

        return $input;
    }
}
