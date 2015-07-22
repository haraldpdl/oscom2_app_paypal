<?php
/**
  * osCommerce Online Merchant
  *
  * @copyright Copyright (c) 2015 osCommerce; http://www.oscommerce.com
  * @license GPL; http://www.oscommerce.com/gpllicense.txt
  */

namespace OSC\Apps\PayPal\PayPal\Module\Admin\Config\LOGIN\Params;

use OSC\OM\HTML;

class live_client_id extends \OSC\Apps\PayPal\PayPal\Module\Admin\Config\ParamsAbstract
{
    public $sort_order = 200;

    protected function init()
    {
        $this->title = $this->app->getDef('cfg_login_live_client_id_title');
        $this->description = $this->app->getDef('cfg_login_live_client_id_desc');
    }

    public function getSetField()
    {
        $input = HTML::inputField('live_client_id', OSCOM_APP_PAYPAL_LOGIN_LIVE_CLIENT_ID, 'id="inputLogInLiveClientId"');

        $result = <<<EOT
<div>
  <p>
    <label for="inputLogInLiveClientId">{$this->title}</label>

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
