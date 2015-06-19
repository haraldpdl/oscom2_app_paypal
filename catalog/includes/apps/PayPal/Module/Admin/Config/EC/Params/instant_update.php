<?php
/**
  * osCommerce Online Merchant
  *
  * @copyright Copyright (c) 2015 osCommerce; http://www.oscommerce.com
  * @license GPL; http://www.oscommerce.com/gpllicense.txt
  */

namespace OSC\OM\Apps\PayPal\Module\Admin\Config\EC\Params;

class instant_update extends \OSC\OM\Apps\PayPal\Module\Admin\Config\ParamsAbstract
{
    public $default = '1';
    public $sort_order = 400;

    protected function init()
    {
        $this->title = $this->app->getDef('cfg_ec_instant_update_title');
        $this->description = $this->app->getDef('cfg_ec_instant_update_desc');
    }

    public function getSetField()
    {
        $input = '<input type="radio" id="instantUpdateSelectionEnabled" name="instant_update" value="1"' . (OSCOM_APP_PAYPAL_EC_INSTANT_UPDATE == '1' ? ' checked="checked"' : '') . '><label for="instantUpdateSelectionEnabled">' . $this->app->getDef('cfg_ec_instant_update_enabled') . '</label>' .
                 '<input type="radio" id="instantUpdateSelectionDisabled" name="instant_update" value="0"' . (OSCOM_APP_PAYPAL_EC_INSTANT_UPDATE == '0' ? ' checked="checked"' : '') . '><label for="instantUpdateSelectionDisabled">' . $this->app->getDef('cfg_ec_instant_update_disabled') . '</label>';

        $result = <<<EOT
<div>
  <p>
    <label>{$this->title}</label>

    {$this->description}
  </p>

  <div id="instantUpdateSelection">
    {$input}
  </div>
</div>

<script>
$(function() {
  $('#instantUpdateSelection').buttonset();
});
</script>
EOT;

        return $result;
    }
}
