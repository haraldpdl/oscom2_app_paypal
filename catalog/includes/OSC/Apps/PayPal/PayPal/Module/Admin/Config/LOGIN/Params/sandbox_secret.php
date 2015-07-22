<?php
/**
  * osCommerce Online Merchant
  *
  * @copyright Copyright (c) 2015 osCommerce; http://www.oscommerce.com
  * @license GPL; http://www.oscommerce.com/gpllicense.txt
  */

namespace OSC\Apps\PayPal\PayPal\Module\Admin\Config\LOGIN\Params;

use OSC\OM\HTML;

class sandbox_secret extends \OSC\Apps\PayPal\PayPal\Module\Admin\Config\ParamsAbstract
{
    public $sort_order = 500;

    protected function init()
    {
        $this->title = $this->app->getDef('cfg_login_sandbox_secret_title');
        $this->description = $this->app->getDef('cfg_login_sandbox_secret_desc');
    }

    public function getSetField()
    {
        $input = HTML::inputField('sandbox_secret', OSCOM_APP_PAYPAL_LOGIN_SANDBOX_SECRET, 'id="inputLogInSandboxSecret"');

        $result = <<<EOT
<div>
  <p>
    <label for="inputLogInSandboxSecret">{$this->title}</label>

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
