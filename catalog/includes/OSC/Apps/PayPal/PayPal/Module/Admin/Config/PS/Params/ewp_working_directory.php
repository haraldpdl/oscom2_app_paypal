<?php
/**
  * osCommerce Online Merchant
  *
  * @copyright Copyright (c) 2015 osCommerce; http://www.oscommerce.com
  * @license GPL; http://www.oscommerce.com/gpllicense.txt
  */

namespace OSC\Apps\PayPal\PayPal\Module\Admin\Config\PS\Params;

use OSC\OM\HTML;

class ewp_working_directory extends \OSC\Apps\PayPal\PayPal\Module\Admin\Config\ParamsAbstract
{
    public $sort_order = 1200;

    protected function init()
    {
        $this->title = $this->app->getDef('cfg_ps_ewp_working_directory_title');
        $this->description = $this->app->getDef('cfg_ps_ewp_working_directory_desc');
    }

    public function getSetField()
    {
        $input = HTML::inputField('ewp_working_directory', OSCOM_APP_PAYPAL_PS_EWP_WORKING_DIRECTORY, 'id="inputPsEwpWorkingDirectory"');

        $result = <<<EOT
<div>
  <p>
    <label for="inputPsEwpWorkingDirectory">{$this->title}</label>

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
