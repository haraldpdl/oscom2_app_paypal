<?php
/**
  * osCommerce Online Merchant
  *
  * @copyright Copyright (c) 2015 osCommerce; http://www.oscommerce.com
  * @license GPL; http://www.oscommerce.com/gpllicense.txt
  */

namespace OSC\Apps\PayPal\PayPal\Module\Admin\Config\LOGIN\Params;

class content_width extends \OSC\Apps\PayPal\PayPal\Module\Admin\Config\ParamsAbstract
{
    public $default = 'Full';
    public $app_configured = false;
    public $set_func = 'tep_cfg_select_option(array(\'Full\', \'Half\'), ';

    protected function init()
    {
        $this->title = $this->app->getDef('cfg_login_content_width_title');
        $this->description = $this->app->getDef('cfg_login_content_width_desc');
    }

    public function getSetField()
    {
        $input = '<input type="radio" id="contentWidthSelectionHalf" name="content_width" value="Half"' . (OSCOM_APP_PAYPAL_LOGIN_CONTENT_WIDTH == 'Half' ? ' checked="checked"' : '') . '><label for="contentWidthSelectionHalf">' . $this->app->getDef('cfg_login_content_width_half') . '</label>' .
                 '<input type="radio" id="contentWidthSelectionFull" name="content_width" value="Full"' . (OSCOM_APP_PAYPAL_LOGIN_CONTENT_WIDTH == 'Full' ? ' checked="checked"' : '') . '><label for="contentWidthSelectionFull">' . $this->app->getDef('cfg_login_content_width_full') . '</label>';

        $result = <<<EOT
<div>
  <p>
    <label>{$this->title}</label>

    {$this->description}
  </p>

  <div id="contentWidthSelection">
    {$input}
  </div>
</div>

<script>
$(function() {
  $('#contentWidthSelection').buttonset();
});
</script>
EOT;

        return $result;
    }
}
