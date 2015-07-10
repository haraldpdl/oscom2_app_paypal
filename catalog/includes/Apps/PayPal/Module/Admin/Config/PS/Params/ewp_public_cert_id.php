<?php
/**
  * osCommerce Online Merchant
  *
  * @copyright Copyright (c) 2015 osCommerce; http://www.oscommerce.com
  * @license GPL; http://www.oscommerce.com/gpllicense.txt
  */

namespace OSC\OM\Apps\PayPal\Module\Admin\Config\PS\Params;

use OSC\OM\HTML;

class ewp_public_cert_id extends \OSC\OM\Apps\PayPal\Module\Admin\Config\ParamsAbstract
{
    public $sort_order = 1000;

    protected function init()
    {
        $this->title = $this->app->getDef('cfg_ps_ewp_public_cert_id_title');
        $this->description = $this->app->getDef('cfg_ps_ewp_public_cert_id_desc');
    }

    public function getSetField()
    {
        $input = HTML::inputField('ewp_public_cert_id', OSCOM_APP_PAYPAL_PS_EWP_PUBLIC_CERT_ID, 'id="inputPsEwpPublicCertId"');

        $result = <<<EOT
<div>
  <p>
    <label for="inputPsEwpPublicCertId">{$this->title}</label>

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
