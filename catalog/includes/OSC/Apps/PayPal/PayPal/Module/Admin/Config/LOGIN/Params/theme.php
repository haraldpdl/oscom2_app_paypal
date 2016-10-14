<?php
/**
  * osCommerce Online Merchant
  *
  * @copyright Copyright (c) 2015 osCommerce; http://www.oscommerce.com
  * @license GPL; http://www.oscommerce.com/gpllicense.txt
  */

namespace OSC\Apps\PayPal\PayPal\Module\Admin\Config\LOGIN\Params;

use OSC\OM\HTML;

class theme extends \OSC\Apps\PayPal\PayPal\Module\Admin\Config\ConfigParamAbstract
{
    public $default = 'Blue';
    public $sort_order = 600;

    protected function init()
    {
        $this->title = $this->app->getDef('cfg_login_theme_title');
        $this->description = $this->app->getDef('cfg_login_theme_desc');
    }

    public function getInputField()
    {
        $value = $this->getInputValue();

        $input = '<div class="btn-group" data-toggle="buttons">' .
                 '  <label class="btn btn-info' . ($value == 'Blue' ? ' active' : '') . '">' . HTML::radioField($this->key, 'Blue', ($value == 'Blue')) . $this->app->getDef('cfg_login_theme_blue') . '</label>' .
                 '  <label class="btn btn-info' . ($value == 'Neutral' ? ' active' : '') . '">' . HTML::radioField($this->key, 'Neutral', ($value == 'Neutral')) . $this->app->getDef('cfg_login_theme_neutral') . '</label>' .
                 '</div>';

        return $input;
    }
}
