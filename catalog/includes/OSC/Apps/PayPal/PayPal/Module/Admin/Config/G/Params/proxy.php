<?php
/**
  * osCommerce Online Merchant
  *
  * @copyright Copyright (c) 2015 osCommerce; http://www.oscommerce.com
  * @license GPL; http://www.oscommerce.com/gpllicense.txt
  */

namespace OSC\Apps\PayPal\PayPal\Module\Admin\Config\G\Params;

class proxy extends \OSC\Apps\PayPal\PayPal\Module\Admin\Config\ConfigParamAbstract
{
    public $sort_order = 400;

    protected function init()
    {
        $this->title = $this->app->getDef('cfg_proxy_title');
        $this->description = $this->app->getDef('cfg_proxy_desc');
    }
}
