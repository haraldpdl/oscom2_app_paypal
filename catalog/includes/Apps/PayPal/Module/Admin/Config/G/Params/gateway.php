<?php
/**
  * osCommerce Online Merchant
  *
  * @copyright Copyright (c) 2015 osCommerce; http://www.oscommerce.com
  * @license GPL; http://www.oscommerce.com/gpllicense.txt
  */

namespace OSC\OM\Apps\PayPal\Module\Admin\Config\G\Params;

class gateway extends \OSC\OM\Apps\PayPal\Module\Admin\Config\ParamsAbstract
{
    public $default = '1';
    public $sort_order = 100;

    protected function init()
    {
        $this->title = $this->app->getDef('cfg_gateway_title');
        $this->description = $this->app->getDef('cfg_gateway_desc');
    }

    public function getSetField()
    {
        $input = '<input type="radio" id="gatewaySelectionPayPal" name="gateway" value="1"' . (OSCOM_APP_PAYPAL_GATEWAY == '1' ? ' checked="checked"' : '') . '><label for="gatewaySelectionPayPal">' . $this->app->getDef('cfg_gateway_paypal') . '</label>' .
                 '<input type="radio" id="gatewaySelectionPayflow" name="gateway" value="0"' . (OSCOM_APP_PAYPAL_GATEWAY == '0' ? ' checked="checked"' : '') . '><label for="gatewaySelectionPayflow">' . $this->app->getDef('cfg_gateway_payflow') . '</label>';

        $result = <<<EOT
<div>
  <p>
    <label>{$this->title}</label>

    {$this->description}
  </p>

  <div id="gatewaySelection">
    {$input}
  </div>
</div>

<script>
$(function() {
  $('#gatewaySelection').buttonset();
});
</script>
EOT;

        return $result;
    }
}
