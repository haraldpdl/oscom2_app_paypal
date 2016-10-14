<?php
/**
  * osCommerce Online Merchant
  *
  * @copyright Copyright (c) 2015 osCommerce; http://www.oscommerce.com
  * @license GPL; http://www.oscommerce.com/gpllicense.txt
  */

namespace OSC\Apps\PayPal\PayPal\Module\Admin\Config;

use OSC\OM\Registry;

abstract class ConfigParamAbstract extends \OSC\Sites\Admin\ConfigParamAbstract
{
    protected $app;
    protected $config_module;

    protected $key_prefix = 'oscom_app_paypal_';
    public $app_configured = true;

    public function __construct($config_module)
    {
        $this->app = Registry::get('PayPal');

        if ($config_module != 'G') {
            $this->key_prefix .= strtolower($config_module) . '_';
        }

        $this->config_module = $config_module;

        $this->code = (new \ReflectionClass($this))->getShortName();

        $this->app->loadDefinitionFile('modules/' . $config_module . '/Params/' . $this->code . '.php');

        parent::__construct();
    }
}
