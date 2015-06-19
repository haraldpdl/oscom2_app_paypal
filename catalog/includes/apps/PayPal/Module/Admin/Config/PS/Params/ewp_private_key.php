<?php
/**
  * osCommerce Online Merchant
  *
  * @copyright Copyright (c) 2015 osCommerce; http://www.oscommerce.com
  * @license GPL; http://www.oscommerce.com/gpllicense.txt
  */

namespace OSC\OM\Apps\PayPal\Module\Admin\Config\PS\Params;

use OSC\OM\HTML;

class ewp_private_key extends \OSC\OM\Apps\PayPal\Module\Admin\Config\ParamsAbstract
{
    public $sort_order = 800;

    protected function init()
    {
        $this->title = $this->app->getDef('cfg_ps_ewp_private_key_title');
        $this->description = $this->app->getDef('cfg_ps_ewp_private_key_desc');
    }

    public function getSetField()
    {
        $input = HTML::inputField('ewp_private_key', OSCOM_APP_PAYPAL_PS_EWP_PRIVATE_KEY, 'id="inputPsEwpPrivateKey"');

        $result = <<<EOT
<div>
  <p>
    <label for="inputPsEwpPrivateKey">{$this->title}</label>

    {$this->description}
  </p>

  <div>
    {$input}
  </div>
</div>
EOT;

        return $result;
    }
}
