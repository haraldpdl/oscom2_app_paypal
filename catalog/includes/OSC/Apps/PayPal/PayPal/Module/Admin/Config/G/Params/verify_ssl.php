<?php
/**
  * osCommerce Online Merchant
  *
  * @copyright Copyright (c) 2015 osCommerce; http://www.oscommerce.com
  * @license GPL; http://www.oscommerce.com/gpllicense.txt
  */

namespace OSC\Apps\PayPal\PayPal\Module\Admin\Config\G\Params;

use OSC\OM\HTML;

class verify_ssl extends \OSC\Apps\PayPal\PayPal\Module\Admin\Config\ConfigParamAbstract
{
    public $default = '1';
    public $sort_order = 300;

    protected function init()
    {
        $this->title = $this->app->getDef('cfg_verify_ssl_title');
        $this->description = $this->app->getDef('cfg_verify_ssl_desc');
    }

    public function getInputField()
    {
        $value = $this->getInputValue();

        $input = '<div class="btn-group" data-toggle="buttons">' .
                 '  <label class="btn btn-info' . ($value == '1' ? ' active' : '') . '">' . HTML::radioField($this->key, '1', ($value == '1')) . $this->app->getDef('cfg_verify_ssl_true') . '</label>' .
                 '  <label class="btn btn-info' . ($value == '0' ? ' active' : '') . '">' . HTML::radioField($this->key, '0', ($value == '0')) . $this->app->getDef('cfg_verify_ssl_false') . '</label>' .
                 '</div>';

        return $input;
    }
}
