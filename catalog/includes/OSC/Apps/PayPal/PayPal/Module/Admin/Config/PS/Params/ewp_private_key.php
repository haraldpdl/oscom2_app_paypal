<?php
/**
  * osCommerce Online Merchant
  *
  * @copyright Copyright (c) 2015 osCommerce; http://www.oscommerce.com
  * @license GPL; http://www.oscommerce.com/gpllicense.txt
  */

namespace OSC\Apps\PayPal\PayPal\Module\Admin\Config\PS\Params;

class ewp_private_key extends \OSC\Apps\PayPal\PayPal\Module\Admin\Config\ConfigParamAbstract
{
    public $sort_order = 800;

    protected function init()
    {
        $this->title = $this->app->getDef('cfg_ps_ewp_private_key_title');
        $this->description = $this->app->getDef('cfg_ps_ewp_private_key_desc');
    }
}
