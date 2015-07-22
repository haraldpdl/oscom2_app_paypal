<?php
/**
  * osCommerce Online Merchant
  *
  * @copyright Copyright (c) 2015 osCommerce; http://www.oscommerce.com
  * @license GPL; http://www.oscommerce.com/gpllicense.txt
  */

namespace OSC\Apps\PayPal\Module\Admin\Config\EC\Params;

use OSC\OM\OSCOM;

class checkout_flow extends \OSC\Apps\PayPal\Module\Admin\Config\ParamsAbstract
{
    public $default = '0';
    public $sort_order = 200;

    protected function init()
    {
        $this->title = $this->app->getDef('cfg_ec_checkout_flow_title');
        $this->description = $this->app->getDef('cfg_ec_checkout_flow_desc');
    }

    public function getSetField()
    {
        if (!file_exists(OSCOM::BASE_DIR . 'OSC/Apps/PayPal/with_beta.txt')) {
            return false;
        }

        $input = '<input type="radio" id="checkoutFlowSelectionDefault" name="checkout_flow" value="0"' . (OSCOM_APP_PAYPAL_EC_CHECKOUT_FLOW == '0' ? ' checked="checked"' : '') . '><label for="checkoutFlowSelectionDefault">' . $this->app->getDef('cfg_ec_checkout_flow_default') . '</label>' .
                 '<input type="radio" id="checkoutFlowSelectionInContext" name="checkout_flow" value="1"' . (OSCOM_APP_PAYPAL_EC_CHECKOUT_FLOW == '1' ? ' checked="checked"' : '') . '><label for="checkoutFlowSelectionInContext">' . $this->app->getDef('cfg_ec_checkout_flow_in_context') . '</label>';

        $result = <<<EOT
<div>
  <p>
    <label>{$this->title}</label>

    {$this->description}
  </p>

  <div id="checkoutFlowSelection">
    {$input}
  </div>
</div>

<script>
$(function() {
  $('#checkoutFlowSelection').buttonset();
});
</script>
EOT;

        return $result;
    }
}
