<?php
/**
  * osCommerce Online Merchant
  *
  * @copyright Copyright (c) 2015 osCommerce; http://www.oscommerce.com
  * @license GPL; http://www.oscommerce.com/gpllicense.txt
  */

namespace OSC\Apps\PayPal\Module\Admin\Config\PS\Params;

class ewp_status extends \OSC\Apps\PayPal\Module\Admin\Config\ParamsAbstract
{
    public $default = '-1';
    public $sort_order = 700;

    protected function init()
    {
        $this->title = $this->app->getDef('cfg_ps_ewp_status_title');
        $this->description = $this->app->getDef('cfg_ps_ewp_status_desc');
    }

    public function getSetField()
    {
        $input = '<input type="radio" id="ewpStatusSelectionTrue" name="ewp_status" value="1"' . (OSCOM_APP_PAYPAL_PS_EWP_STATUS == '1' ? ' checked="checked"' : '') . '><label for="ewpStatusSelectionTrue">' . $this->app->getDef('cfg_ps_ewp_status_true') . '</label>' .
                 '<input type="radio" id="ewpStatusSelectionFalse" name="ewp_status" value="-1"' . (OSCOM_APP_PAYPAL_PS_EWP_STATUS == '-1' ? ' checked="checked"' : '') . '><label for="ewpStatusSelectionFalse">' . $this->app->getDef('cfg_ps_ewp_status_false') . '</label>';

        $result = <<<EOT
<div>
  <p>
    <label>{$this->title}</label>

    {$this->description}
  </p>

  <div id="ewpStatusSelection">
    {$input}
  </div>
</div>

<script>
$(function() {
  $('#ewpStatusSelection').buttonset();
});
</script>
EOT;

        return $result;
    }
}
