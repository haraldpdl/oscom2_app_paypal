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
        $OSCOM_PayPal = Registry::get('PayPal');

        $current_module = $this->page->data['current_module'];

        $m = Registry::get('PayPalAdminConfig' . $current_module);

        if ($current_module == 'G') {
            $cut = 'OSCOM_APP_PAYPAL_';
        } else {
            $cut = 'OSCOM_APP_PAYPAL_' . $current_module . '_';
        }

        $cut_length = strlen($cut);

        foreach ($m->getParameters() as $key) {
            $p = strtolower(substr($key, $cut_length));

            if (isset($_POST[$p])) {
                $OSCOM_PayPal->saveParameter($key, $_POST[$p]);
            }
        }

        $OSCOM_PayPal->addAlert($OSCOM_PayPal->getDef('alert_cfg_saved_success'), 'success');

        $OSCOM_PayPal->redirect('Configure&module=' . $current_module);
    }
}
