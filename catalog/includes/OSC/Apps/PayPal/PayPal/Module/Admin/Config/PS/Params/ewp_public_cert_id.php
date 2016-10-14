<?php
/**
  * osCommerce Online Merchant
  *
  * @copyright Copyright (c) 2015 osCommerce; http://www.oscommerce.com
  * @license GPL; http://www.oscommerce.com/gpllicense.txt
  */

namespace OSC\Apps\PayPal\PayPal\Module\Admin\Config\PS\Params;

class ewp_public_cert_id extends \OSC\Apps\PayPal\PayPal\Module\Admin\Config\ConfigParamAbstract
{
    public $sort_order = 1000;

    protected function init()
    {
        $this->title = $this->app->getDef('cfg_ps_ewp_public_cert_id_title');
        $this->description = $this->app->getDef('cfg_ps_ewp_public_cert_id_desc');
    }
}
