<?php
/**
  * osCommerce Online Merchant
  *
  * @copyright Copyright (c) 2015 osCommerce; http://www.oscommerce.com
  * @license GPL; http://www.oscommerce.com/gpllicense.txt
  */

namespace OSC\Apps\PayPal\Module\Admin\Config\PS\Params;

use OSC\OM\HTML;

class ewp_paypal_cert extends \OSC\Apps\PayPal\Module\Admin\Config\ParamsAbstract
{
    public $sort_order = 1100;

    protected function init()
    {
        $this->title = $this->app->getDef('cfg_ps_ewp_paypal_cert_title');
        $this->description = $this->app->getDef('cfg_ps_ewp_paypal_cert_desc');
    }

    public function getSetField()
    {
        $input = HTML::inputField('ewp_paypal_cert', OSCOM_APP_PAYPAL_PS_EWP_PAYPAL_CERT, 'id="inputPsEwpPayPalCert"');

        $result = <<<EOT
<div>
  <p>
    <label for="inputPsEwpPayPalCert">{$this->title}</label>

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
