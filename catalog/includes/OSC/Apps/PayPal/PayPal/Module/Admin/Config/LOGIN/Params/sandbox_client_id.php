<?php
/**
  * PayPal App for osCommerce Online Merchant
  *
  * @copyright (c) 2016 osCommerce; https://www.oscommerce.com
  * @license MIT; https://www.oscommerce.com/license/mit.txt
  */

namespace OSC\Apps\PayPal\PayPal\Module\Admin\Config\LOGIN\Params;

class sandbox_client_id extends \OSC\Apps\PayPal\PayPal\Module\Admin\Config\ConfigParamAbstract
{
    public $sort_order = 400;

    protected function init()
    {
        $this->title = $this->app->getDef('cfg_login_sandbox_client_id_title');
        $this->description = $this->app->getDef('cfg_login_sandbox_client_id_desc');
    }
}
