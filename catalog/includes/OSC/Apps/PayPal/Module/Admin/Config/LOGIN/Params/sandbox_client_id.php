<?php
/**
  * osCommerce Online Merchant
  *
  * @copyright Copyright (c) 2015 osCommerce; http://www.oscommerce.com
  * @license GPL; http://www.oscommerce.com/gpllicense.txt
  */

namespace OSC\OM\Apps\PayPal\Module\Admin\Config\LOGIN\Params;

use OSC\OM\HTML;

class sandbox_client_id extends \OSC\OM\Apps\PayPal\Module\Admin\Config\ParamsAbstract
{
    public $sort_order = 400;

    protected function init()
    {
        $this->title = $this->app->getDef('cfg_login_sandbox_client_id_title');
        $this->description = $this->app->getDef('cfg_login_sandbox_client_id_desc');
    }

    public function getSetField()
    {
        $input = HTML::inputField('sandbox_client_id', OSCOM_APP_PAYPAL_LOGIN_SANDBOX_CLIENT_ID, 'id="inputLogInSandboxClientId"');

        $result = <<<EOT
<div>
  <p>
    <label for="inputLogInSandboxClientId">{$this->title}</label>

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
