<?php
/**
  * osCommerce Online Merchant
  *
  * @copyright Copyright (c) 2015 osCommerce; http://www.oscommerce.com
  * @license GPL; http://www.oscommerce.com/gpllicense.txt
  */

namespace OSC\Apps\PayPal\PayPal\Module\Admin\Config\EC\Params;

use OSC\OM\HTML;

class checkout_flow extends \OSC\Apps\PayPal\PayPal\Module\Admin\Config\ConfigParamAbstract
{
    public $default = '1';
    public $sort_order = 200;

    protected function init()
    {
        $this->title = $this->app->getDef('cfg_ec_checkout_flow_title');
        $this->description = $this->app->getDef('cfg_ec_checkout_flow_desc');
    }

    public function getInputField()
    {
        $value = $this->getInputValue();

        $input = '<div class="btn-group" data-toggle="buttons">' .
                 '  <label class="btn btn-info' . ($value == '1' ? ' active' : '') . '">' . HTML::radioField($this->key, '1', ($value == '1')) . $this->app->getDef('cfg_ec_checkout_flow_in_context') . '</label>' .
                 '  <label class="btn btn-info' . ($value == '0' ? ' active' : '') . '">' . HTML::radioField($this->key, '0', ($value == '0')) . $this->app->getDef('cfg_ec_checkout_flow_default') . '</label>' .
                 '</div>';

        return $input;
    }
}
