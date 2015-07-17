<?php
/**
  * osCommerce Online Merchant
  *
  * @copyright Copyright (c) 2015 osCommerce; http://www.oscommerce.com
  * @license GPL; http://www.oscommerce.com/gpllicense.txt
  */

namespace OSC\OM\Apps\PayPal\Module\Admin\Config\G;

class G extends \OSC\OM\Apps\PayPal\Module\Admin\Config\ConfigAbstract
{
    public $is_installed = true;
    public $sort_order = 100000;

    protected function init()
    {
        $this->title = $this->app->getDef('module_g_title');
        $this->short_title = $this->app->getDef('module_g_short_title');
    }

    public function install()
    {
        return false;
    }

    public function uninstall()
    {
        return false;
    }
}
