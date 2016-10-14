<?php
/**
  * osCommerce Online Merchant
  *
  * @copyright Copyright (c) 2015 osCommerce; http://www.oscommerce.com
  * @license GPL; http://www.oscommerce.com/gpllicense.txt
  */

namespace OSC\Apps\PayPal\PayPal\Module\Admin\Config\HS\Params;

use OSC\OM\HTML;

class status extends \OSC\Apps\PayPal\PayPal\Module\Admin\Config\ConfigParamAbstract
{
    public $default = '1';
    public $sort_order = 100;

    protected function init()
    {
        $this->title = $this->app->getDef('cfg_hs_status_title');
        $this->description = $this->app->getDef('cfg_hs_status_desc');
    }

    public function getInputField()
    {
        $value = $this->getInputValue();

        $input = '<div class="btn-group" data-toggle="buttons">' .
                 '  <label class="btn btn-info' . ($value == '1' ? ' active' : '') . '">' . HTML::radioField($this->key, '1', ($value == '1')) . $this->app->getDef('cfg_hs_status_live') . '</label>' .
                 '  <label class="btn btn-info' . ($value == '0' ? ' active' : '') . '">' . HTML::radioField($this->key, '0', ($value == '0')) . $this->app->getDef('cfg_hs_status_sandbox') . '</label>' .
                 '  <label class="btn btn-info' . ($value == '-1' ? ' active' : '') . '">' . HTML::radioField($this->key, '-1', ($value == '-1')) . $this->app->getDef('cfg_hs_status_disabled') . '</label>' .
                 '</div>';

        return $input;
    }
}
