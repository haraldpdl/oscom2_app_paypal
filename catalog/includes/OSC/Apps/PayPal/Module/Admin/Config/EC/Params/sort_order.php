<?php
/**
  * osCommerce Online Merchant
  *
  * @copyright Copyright (c) 2015 osCommerce; http://www.oscommerce.com
  * @license GPL; http://www.oscommerce.com/gpllicense.txt
  */

namespace OSC\OM\Apps\PayPal\Module\Admin\Config\EC\Params;

use OSC\OM\HTML;

class sort_order extends \OSC\OM\Apps\PayPal\Module\Admin\Config\ParamsAbstract
{
    public $default = '0';
    public $app_configured = false;

    protected function init()
    {
        $this->title = $this->app->getDef('cfg_ec_sort_order_title');
        $this->description = $this->app->getDef('cfg_ec_sort_order_desc');
    }

    public function getSetField()
    {
        $input = HTML::inputField('sort_order', OSCOM_APP_PAYPAL_EC_SORT_ORDER, 'id="inputEcSortOrder"');

        $result = <<<EOT
<div>
  <p>
    <label for="inputEcSortOrder">{$this->title}</label>

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
