<?php
/**
  * osCommerce Online Merchant
  *
  * @copyright Copyright (c) 2015 osCommerce; http://www.oscommerce.com
  * @license GPL; http://www.oscommerce.com/gpllicense.txt
  */

namespace OSC\OM\Apps\PayPal\Module\Admin\Config\G\Params;

class verify_ssl extends \OSC\OM\Apps\PayPal\Module\Admin\Config\ParamsAbstract
{
    public $default = '1';
    public $sort_order = 300;

    protected function init()
    {
        $this->title = $this->app->getDef('cfg_verify_ssl_title');
        $this->description = $this->app->getDef('cfg_verify_ssl_desc');
    }

    public function getSetField()
    {
        $input = '<input type="radio" id="verifySslSelectionTrue" name="verify_ssl" value="1"' . (OSCOM_APP_PAYPAL_VERIFY_SSL == '1' ? ' checked="checked"' : '') . '><label for="verifySslSelectionTrue">' . $this->app->getDef('cfg_verify_ssl_true') . '</label>' .
                 '<input type="radio" id="verifySslSelectionFalse" name="verify_ssl" value="0"' . (OSCOM_APP_PAYPAL_VERIFY_SSL == '0' ? ' checked="checked"' : '') . '><label for="verifySslSelectionFalse">' . $this->app->getDef('cfg_verify_ssl_false') . '</label>';

        $result = <<<EOT
<div>
  <p>
    <label>{$this->title}</label>

    {$this->description}
  </p>

  <div id="verifySslSelection">
    {$input}
  </div>
</div>

<script>
$(function() {
  $('#verifySslSelection').buttonset();
});
</script>
EOT;

        return $result;
    }
}
