<?php
/**
  * osCommerce Online Merchant
  *
  * @copyright Copyright (c) 2015 osCommerce; http://www.oscommerce.com
  * @license GPL; http://www.oscommerce.com/gpllicense.txt
  */

namespace OSC\Apps\PayPal\PayPal\Module\Admin\Config\PS\Params;

class pdt_identity_token extends \OSC\Apps\PayPal\PayPal\Module\Admin\Config\ConfigParamAbstract
{
    public $sort_order = 650;

    protected function init()
    {
        $this->title = $this->app->getDef('cfg_ps_pdt_identity_token_title');
        $this->description = $this->app->getDef('cfg_ps_pdt_identity_token_desc');
    }
}
