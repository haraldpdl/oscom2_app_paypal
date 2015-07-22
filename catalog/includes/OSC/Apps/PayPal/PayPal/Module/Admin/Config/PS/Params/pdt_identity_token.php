<?php
/**
  * osCommerce Online Merchant
  *
  * @copyright Copyright (c) 2015 osCommerce; http://www.oscommerce.com
  * @license GPL; http://www.oscommerce.com/gpllicense.txt
  */

namespace OSC\Apps\PayPal\PayPal\Module\Admin\Config\PS\Params;

use OSC\OM\HTML;

class pdt_identity_token extends \OSC\Apps\PayPal\PayPal\Module\Admin\Config\ParamsAbstract
{
    public $sort_order = 650;

    protected function init()
    {
        $this->title = $this->app->getDef('cfg_ps_pdt_identity_token_title');
        $this->description = $this->app->getDef('cfg_ps_pdt_identity_token_desc');
    }

    public function getSetField()
    {
        $input = HTML::inputField('pdt_identity_token', OSCOM_APP_PAYPAL_PS_PDT_IDENTITY_TOKEN, 'id="inputPsPdtIdentityToken"');

        $result = <<<EOT
<div>
  <p>
    <label for="inputPsPdtIdentityToken">{$this->title}</label>

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
