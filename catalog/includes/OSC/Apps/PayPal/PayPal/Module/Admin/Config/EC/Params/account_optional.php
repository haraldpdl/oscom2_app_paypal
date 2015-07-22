<?php
/**
  * osCommerce Online Merchant
  *
  * @copyright Copyright (c) 2015 osCommerce; http://www.oscommerce.com
  * @license GPL; http://www.oscommerce.com/gpllicense.txt
  */

namespace OSC\Apps\PayPal\PayPal\Module\Admin\Config\EC\Params;

class account_optional extends \OSC\Apps\PayPal\PayPal\Module\Admin\Config\ParamsAbstract
{
    public $default = '0';
    public $sort_order = 300;

    protected function init()
    {
        $this->title = $this->app->getDef('cfg_ec_account_optional_title');
        $this->description = $this->app->getDef('cfg_ec_account_optional_desc');
    }

    public function getSetField()
    {
        $input = '<input type="radio" id="accountOptionalSelectionTrue" name="account_optional" value="1"' . (OSCOM_APP_PAYPAL_EC_ACCOUNT_OPTIONAL == '1' ? ' checked="checked"' : '') . '><label for="accountOptionalSelectionTrue">' . $this->app->getDef('cfg_ec_account_optional_true') . '</label>' .
                 '<input type="radio" id="accountOptionalSelectionFalse" name="account_optional" value="0"' . (OSCOM_APP_PAYPAL_EC_ACCOUNT_OPTIONAL == '0' ? ' checked="checked"' : '') . '><label for="accountOptionalSelectionFalse">' . $this->app->getDef('cfg_ec_account_optional_false') . '</label>';

        $result = <<<EOT
<div>
  <p>
    <label>{$this->title}</label>

    {$this->description}
  </p>

  <div id="accountOptionalSelection">
    {$input}
  </div>
</div>

<script>
$(function() {
  $('#accountOptionalSelection').buttonset();
});
</script>
EOT;

        return $result;
    }
}
