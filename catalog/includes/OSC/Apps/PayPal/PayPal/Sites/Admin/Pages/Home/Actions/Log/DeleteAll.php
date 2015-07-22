<?php
/**
  * osCommerce Online Merchant
  *
  * @copyright Copyright (c) 2015 osCommerce; http://www.oscommerce.com
  * @license GPL; http://www.oscommerce.com/gpllicense.txt
  */

namespace OSC\Apps\PayPal\PayPal\Sites\Admin\Pages\Home\Actions\Log;

use OSC\OM\Registry;

class DeleteAll extends \OSC\OM\PagesActionsAbstract
{
    public function execute()
    {
        $OSCOM_Db = Registry::get('Db');
        $OSCOM_PayPal = Registry::get('PayPal');

        $OSCOM_Db->delete('oscom_app_paypal_log');

        $OSCOM_PayPal->addAlert($OSCOM_PayPal->getDef('alert_delete_success'), 'success');

        $OSCOM_PayPal->redirect('Log');
    }
}
