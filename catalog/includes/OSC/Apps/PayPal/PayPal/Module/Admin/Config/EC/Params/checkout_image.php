<?php
/**
  * osCommerce Online Merchant
  *
  * @copyright Copyright (c) 2015 osCommerce; http://www.oscommerce.com
  * @license GPL; http://www.oscommerce.com/gpllicense.txt
  */

namespace OSC\Apps\PayPal\PayPal\Module\Admin\Config\EC\Params;

class checkout_image extends \OSC\Apps\PayPal\PayPal\Module\Admin\Config\ParamsAbstract
{
    public $default = '0';
    public $sort_order = 500;

    protected function init()
    {
        $this->title = $this->app->getDef('cfg_ec_checkout_image_title');
        $this->description = $this->app->getDef('cfg_ec_checkout_image_desc');
    }

    public function getSetField()
    {
        $input = '<input type="radio" id="checkoutImageSelectionStatic" name="checkout_image" value="0"' . (OSCOM_APP_PAYPAL_EC_CHECKOUT_IMAGE == '0' ? ' checked="checked"' : '') . '><label for="checkoutImageSelectionStatic">' . $this->app->getDef('cfg_ec_checkout_image_static') . '</label>' .
                 '<input type="radio" id="checkoutImageSelectionDynamic" name="checkout_image" value="1"' . (OSCOM_APP_PAYPAL_EC_CHECKOUT_IMAGE == '1' ? ' checked="checked"' : '') . '><label for="checkoutImageSelectionDynamic">' . $this->app->getDef('cfg_ec_checkout_image_dynamic') . '</label>';

        $result = <<<EOT
<div>
  <p>
    <label>{$this->title}</label>

    {$this->description}
  </p>

  <div id="checkoutImageSelection">
    {$input}
  </div>
</div>

<script>
$(function() {
  $('#checkoutImageSelection').buttonset();
});
</script>
EOT;

        return $result;
    }
}
