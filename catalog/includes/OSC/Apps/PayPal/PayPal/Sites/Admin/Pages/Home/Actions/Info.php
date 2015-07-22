<?php
/**
  * osCommerce Online Merchant
  *
  * @copyright Copyright (c) 2015 osCommerce; http://www.oscommerce.com
  * @license GPL; http://www.oscommerce.com/gpllicense.txt
  */

namespace OSC\Apps\PayPal\Sites\Admin\Pages\Home\Actions;

use OSC\OM\Registry;

class Info extends \OSC\OM\PagesActionsAbstract
{
    public function execute()
    {
        $OSCOM_PayPal = Registry::get('PayPal');

        $this->page->setFile('info.php');
        $this->page->data['action'] = 'Info';

        $OSCOM_PayPal->loadLanguageFile('admin/info.php');
    }
}
