<?php
/**
  * osCommerce Online Merchant
  *
  * @copyright Copyright (c) 2015 osCommerce; http://www.oscommerce.com
  * @license GPL; http://www.oscommerce.com/gpllicense.txt
  */

namespace OSC\OM\Apps\PayPal\Module\Admin\Config;

use OSC\OM\Registry;

abstract class ParamsAbstract
{
    protected $app;
    protected $config_module;

    public $default;
    public $title;
    public $description;
    public $set_func;
    public $use_func;
    public $app_configured = true;
    public $sort_order = 0;

    abstract protected function init();
    abstract public function getSetField();

    final public function __construct($config_module)
    {
        $this->app = Registry::get('PayPal');

        $this->config_module = $config_module;

        $this->code = (new \ReflectionClass($this))->getShortName();

        $this->app->loadLanguageFile('modules/' . $config_module . '/Params/' . $this->code . '.php');

        $this->init();
    }
}
