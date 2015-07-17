<?php
/**
  * osCommerce Online Merchant
  *
  * @copyright Copyright (c) 2015 osCommerce; http://www.oscommerce.com
  * @license GPL; http://www.oscommerce.com/gpllicense.txt
  */

namespace OSC\OM\Apps\PayPal\Module\Admin\Config\HS\Params;

class status extends \OSC\OM\Apps\PayPal\Module\Admin\Config\ParamsAbstract
{
    public $default = '1';
    public $sort_order = 100;

    protected function init()
    {
        $this->title = $this->app->getDef('cfg_hs_status_title');
        $this->description = $this->app->getDef('cfg_hs_status_desc');
    }

    public function getSetField()
    {
        $input = '<input type="radio" id="statusSelectionLive" name="status" value="1"' . (OSCOM_APP_PAYPAL_HS_STATUS == '1' ? ' checked="checked"' : '') . '><label for="statusSelectionLive">' . $this->app->getDef('cfg_hs_status_live') . '</label>' .
                 '<input type="radio" id="statusSelectionSandbox" name="status" value="0"' . (OSCOM_APP_PAYPAL_HS_STATUS == '0' ? ' checked="checked"' : '') . '><label for="statusSelectionSandbox">' . $this->app->getDef('cfg_hs_status_sandbox') . '</label>' .
                 '<input type="radio" id="statusSelectionDisabled" name="status" value="-1"' . (OSCOM_APP_PAYPAL_HS_STATUS == '-1' ? ' checked="checked"' : '') . '><label for="statusSelectionDisabled">' . $this->app->getDef('cfg_hs_status_disabled') . '</label>';

        $result = <<<EOT
<div>
  <p>
    <label>{$this->title}</label>

    {$this->description}
  </p>

  <div id="statusSelection">
    {$input}
  </div>
</div>

<script>
$(function() {
  $('#statusSelection').buttonset();
});
</script>
EOT;

        return $result;
    }
}