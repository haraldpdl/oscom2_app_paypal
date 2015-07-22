<?php
/**
  * osCommerce Online Merchant
  *
  * @copyright Copyright (c) 2015 osCommerce; http://www.oscommerce.com
  * @license GPL; http://www.oscommerce.com/gpllicense.txt
  */

namespace OSC\Apps\PayPal\PayPal\Module\Admin\Config\EC\Params;

use OSC\OM\HTML;

class page_style extends \OSC\Apps\PayPal\PayPal\Module\Admin\Config\ParamsAbstract
{
    public $sort_order = 600;

    protected function init()
    {
        $this->title = $this->app->getDef('cfg_ec_page_style_title');
        $this->description = $this->app->getDef('cfg_ec_page_style_desc');
    }

    public function getSetField()
    {
        $input = HTML::inputField('page_style', OSCOM_APP_PAYPAL_EC_PAGE_STYLE, 'id="inputEcPageStyle"');

        $result = <<<EOT
<div>
  <p>
    <label for="inputEcPageStyle">{$this->title}</label>

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
