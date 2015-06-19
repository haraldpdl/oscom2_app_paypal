<?php
/**
  * osCommerce Online Merchant
  *
  * @copyright Copyright (c) 2015 osCommerce; http://www.oscommerce.com
  * @license GPL; http://www.oscommerce.com/gpllicense.txt
  */

namespace OSC\OM\Apps\PayPal\Module\Admin\Config\LOGIN\Params;

use OSC\OM\HTML;

class live_secret extends \OSC\OM\Apps\PayPal\Module\Admin\Config\ParamsAbstract
{
    public $sort_order = 300;

    protected function init()
    {
        $this->title = $this->app->getDef('cfg_login_live_secret_title');
        $this->description = $this->app->getDef('cfg_login_live_secret_desc');
    }

    public function getSetField()
    {
        $input = HTML::inputField('live_secret', OSCOM_APP_PAYPAL_LOGIN_LIVE_SECRET, 'id="inputLogInLiveSecret"');

        $result = <<<EOT
<div>
  <p>
    <label for="inputLogInLiveSecret">{$this->title}</label>

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
