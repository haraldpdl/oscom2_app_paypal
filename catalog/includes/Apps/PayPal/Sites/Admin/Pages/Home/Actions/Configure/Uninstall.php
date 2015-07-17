<?php
/**
  * osCommerce Online Merchant
  *
  * @copyright Copyright (c) 2015 osCommerce; http://www.oscommerce.com
  * @license GPL; http://www.oscommerce.com/gpllicense.txt
  */

namespace OSC\OM\Apps\PayPal\Sites\Admin\Pages\Home\Actions\Configure;

use OSC\OM\OSCOM;
use OSC\OM\Registry;

class Uninstall extends \OSC\OM\PagesActionsAbstract
{
    public function execute()
    {
        $OSCOM_PayPal = Registry::get('PayPal');

        $current_module = $this->page->data['current_module'];

        $m = Registry::get('PayPalAdminConfig' . $current_module);
        $m->uninstall();

        $OSCOM_PayPal->addAlert($OSCOM_PayPal->getDef('alert_module_uninstall_success'), 'success');

        OSCOM::redirect('index.php', 'A&PayPal&Configure&module=' . $current_module);
    }
}