<?php
/**
  * osCommerce Online Merchant
  *
  * @copyright Copyright (c) 2015 osCommerce; http://www.oscommerce.com
  * @license GPL; http://www.oscommerce.com/gpllicense.txt
  */

namespace OSC\OM\Apps\PayPal\Module\Admin\Config\PS\Params;

class transaction_method extends \OSC\OM\Apps\PayPal\Module\Admin\Config\ParamsAbstract
{
    public $default = '1';
    public $sort_order = 300;

    protected function init()
    {
        $this->title = $this->app->getDef('cfg_ps_transaction_method_title');
        $this->description = $this->app->getDef('cfg_ps_transaction_method_desc');
    }

    public function getSetField()
    {
        $input = '<input type="radio" id="transactionMethodSelectionAuthorize" name="transaction_method" value="0"' . (OSCOM_APP_PAYPAL_PS_TRANSACTION_METHOD == '0' ? ' checked="checked"' : '') . '><label for="transactionMethodSelectionAuthorize">' . $this->app->getDef('cfg_ps_transaction_method_authorize') . '</label>' .
                 '<input type="radio" id="transactionMethodSelectionSale" name="transaction_method" value="1"' . (OSCOM_APP_PAYPAL_PS_TRANSACTION_METHOD == '1' ? ' checked="checked"' : '') . '><label for="transactionMethodSelectionSale">' . $this->app->getDef('cfg_ps_transaction_method_sale') . '</label>';

        $result = <<<EOT
<div>
  <p>
    <label>{$this->title}</label>

    {$this->description}
  </p>

  <div id="transactionMethodSelection">
    {$input}
  </div>
</div>

<script>
$(function() {
  $('#transactionMethodSelection').buttonset();
});
</script>
EOT;

        return $result;
    }
}
