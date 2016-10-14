<?php
/**
  * osCommerce Online Merchant
  *
  * @copyright Copyright (c) 2015 osCommerce; http://www.oscommerce.com
  * @license GPL; http://www.oscommerce.com/gpllicense.txt
  */

namespace OSC\Apps\PayPal\PayPal\Module\Admin\Config\PS\Params;

class page_style extends \OSC\Apps\PayPal\PayPal\Module\Admin\Config\ConfigParamAbstract
{
    public $sort_order = 200;

    protected function init()
    {
        $this->title = $this->app->getDef('cfg_ps_page_style_title');
        $this->description = $this->app->getDef('cfg_ps_page_style_desc');
    }
}
