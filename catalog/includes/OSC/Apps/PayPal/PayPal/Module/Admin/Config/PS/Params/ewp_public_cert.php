<?php
/**
  * osCommerce Online Merchant
  *
  * @copyright Copyright (c) 2015 osCommerce; http://www.oscommerce.com
  * @license GPL; http://www.oscommerce.com/gpllicense.txt
  */

namespace OSC\Apps\PayPal\PayPal\Module\Admin\Config\PS\Params;

use OSC\OM\HTML;

class ewp_public_cert extends \OSC\Apps\PayPal\PayPal\Module\Admin\Config\ParamsAbstract
{
    public $sort_order = 900;

    protected function init()
    {
        $this->title = $this->app->getDef('cfg_ps_ewp_public_cert_title');
        $this->description = $this->app->getDef('cfg_ps_ewp_public_cert_desc');
    }

    public function getSetField()
    {
        $input = HTML::inputField('ewp_public_cert', OSCOM_APP_PAYPAL_PS_EWP_PUBLIC_CERT, 'id="inputPsEwpPublicCert"');

        $result = <<<EOT
<div>
  <p>
    <label for="inputPsEwpPublicCert">{$this->title}</label>

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
