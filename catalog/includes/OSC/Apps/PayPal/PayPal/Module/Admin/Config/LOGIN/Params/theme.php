<?php
/**
  * osCommerce Online Merchant
  *
  * @copyright Copyright (c) 2015 osCommerce; http://www.oscommerce.com
  * @license GPL; http://www.oscommerce.com/gpllicense.txt
  */

namespace OSC\Apps\PayPal\PayPal\Module\Admin\Config\LOGIN\Params;

class theme extends \OSC\Apps\PayPal\PayPal\Module\Admin\Config\ParamsAbstract
{
    public $default = 'Blue';
    public $sort_order = 600;

    protected function init()
    {
        $this->title = $this->app->getDef('cfg_login_theme_title');
        $this->description = $this->app->getDef('cfg_login_theme_desc');
    }

    public function getSetField()
    {
        $input = '<input type="radio" id="themeSelectionBlue" name="theme" value="Blue"' . (OSCOM_APP_PAYPAL_LOGIN_THEME == 'Blue' ? ' checked="checked"' : '') . '><label for="themeSelectionBlue">' . $this->app->getDef('cfg_login_theme_blue') . '</label>' .
                 '<input type="radio" id="themeSelectionNeutral" name="theme" value="Neutral"' . (OSCOM_APP_PAYPAL_LOGIN_THEME == 'Neutral' ? ' checked="checked"' : '') . '><label for="themeSelectionNeutral">' . $this->app->getDef('cfg_login_theme_neutral') . '</label>';

        $result = <<<EOT
<div>
  <p>
    <label>{$this->title}</label>

    {$this->description}
  </p>

  <div id="themeSelection">
    {$input}
  </div>
</div>

<script>
$(function() {
  $('#themeSelection').buttonset();
});
</script>
EOT;

        return $result;
    }
}
