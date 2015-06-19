<?php
/**
  * osCommerce Online Merchant
  *
  * @copyright Copyright (c) 2015 osCommerce; http://www.oscommerce.com
  * @license GPL; http://www.oscommerce.com/gpllicense.txt
  */

namespace OSC\OM\Apps\PayPal\Module\Admin\Config\PS\Params;

use OSC\OM\HTML;

class ewp_openssl extends \OSC\OM\Apps\PayPal\Module\Admin\Config\ParamsAbstract
{
    public $default = '/usr/bin/openssl';
    public $sort_order = 1300;

    protected function init()
    {
        $this->title = $this->app->getDef('cfg_ps_ewp_openssl_title');
        $this->description = $this->app->getDef('cfg_ps_ewp_openssl_desc');
    }

    public function getSetField()
    {
        $input = HTML::inputField('ewp_openssl', OSCOM_APP_PAYPAL_PS_EWP_OPENSSL, 'id="inputPsEwpOpenSsl"');

        $result = <<<EOT
<div>
  <p>
    <label for="inputPsEwpOpenSsl">{$this->title}</label>

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
