<?php
/**
  * osCommerce Online Merchant
  *
  * @copyright Copyright (c) 2015 osCommerce; http://www.oscommerce.com
  * @license GPL; http://www.oscommerce.com/gpllicense.txt
  */

namespace OSC\OM\Apps\PayPal\Module\Admin\Config\G\Params;

use OSC\OM\HTML;

class proxy extends \OSC\OM\Apps\PayPal\Module\Admin\Config\ParamsAbstract
{
    public $sort_order = 400;

    protected function init()
    {
        $this->title = $this->app->getDef('cfg_proxy_title');
        $this->description = $this->app->getDef('cfg_proxy_desc');
    }

    public function getSetField()
    {
        $input = HTML::inputField('proxy', OSCOM_APP_PAYPAL_PROXY, 'id="inputProxy"');

        $result = <<<EOT
<div>
  <p>
    <label for="inputProxy">{$this->title}</label>

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
