<?php
/**
  * osCommerce Online Merchant
  *
  * @copyright Copyright (c) 2015 osCommerce; http://www.oscommerce.com
  * @license GPL; http://www.oscommerce.com/gpllicense.txt
  */

namespace OSC\Apps\PayPal\Module\Admin\Config\G\Params;

class log_transactions extends \OSC\Apps\PayPal\Module\Admin\Config\ParamsAbstract
{
    public $default = '1';
    public $sort_order = 500;

    protected function init()
    {
        $this->title = $this->app->getDef('cfg_log_transactions_title');
        $this->description = $this->app->getDef('cfg_log_transactions_desc');
    }

    public function getSetField()
    {
        $input = '<input type="radio" id="logTransactionsSelectionAll" name="log_transactions" value="1"' . (OSCOM_APP_PAYPAL_LOG_TRANSACTIONS == '1' ? ' checked="checked"' : '') . '><label for="logTransactionsSelectionAll">' . $this->app->getDef('cfg_log_transactions_all') . '</label>' .
                 '<input type="radio" id="logTransactionsSelectionErrors" name="log_transactions" value="0"' . (OSCOM_APP_PAYPAL_LOG_TRANSACTIONS == '0' ? ' checked="checked"' : '') . '><label for="logTransactionsSelectionErrors">' . $this->app->getDef('cfg_log_transactions_errors') . '</label>' .
                 '<input type="radio" id="logTransactionsSelectionDisabled" name="log_transactions" value="-1"' . (OSCOM_APP_PAYPAL_LOG_TRANSACTIONS == '-1' ? ' checked="checked"' : '') . '><label for="logTransactionsSelectionDisabled">' . $this->app->getDef('cfg_log_transactions_disabled') . '</label>';

        $result = <<<EOT
<div>
  <p>
    <label>{$this->title}</label>

    {$this->description}
  </p>

  <div id="logSelection">
    {$input}
  </div>
</div>

<script>
$(function() {
  $('#logSelection').buttonset();
});
</script>
EOT;

        return $result;
    }
}
