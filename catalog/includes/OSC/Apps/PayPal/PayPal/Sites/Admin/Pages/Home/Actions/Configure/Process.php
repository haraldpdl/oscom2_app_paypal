<?php
/**
  * osCommerce Online Merchant
  *
  * @copyright Copyright (c) 2015 osCommerce; http://www.oscommerce.com
  * @license GPL; http://www.oscommerce.com/gpllicense.txt
  */

namespace OSC\Apps\PayPal\PayPal\Sites\Admin\Pages\Home\Actions\Configure;

use OSC\OM\Registry;

class Process extends \OSC\OM\PagesActionsAbstract
{
    public function execute()
    {
        $OSCOM_MessageStack = Registry::get('MessageStack');
        $OSCOM_PayPal = Registry::get('PayPal');

        $current_module = $this->page->data['current_module'];

        $m = Registry::get('PayPalAdminConfig' . $current_module);

        foreach ($m->getParameters() as $key) {
            $p = strtolower($key);

            if (isset($_POST[$p])) {
                $OSCOM_PayPal->saveCfgParam($key, $_POST[$p]);
            }
        }

        $OSCOM_MessageStack->add($OSCOM_PayPal->getDef('alert_cfg_saved_success'), 'success', 'PayPal');

        $OSCOM_PayPal->redirect('Configure&module=' . $current_module);
    }
}
