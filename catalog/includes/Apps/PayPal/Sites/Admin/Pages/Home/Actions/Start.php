<?php
/**
  * osCommerce Online Merchant
  *
  * @copyright Copyright (c) 2015 osCommerce; http://www.oscommerce.com
  * @license GPL; http://www.oscommerce.com/gpllicense.txt
  */

namespace OSC\OM\Apps\PayPal\Sites\Admin\Pages\Home\Actions;

class Start extends \OSC\OM\PagesActionsAbstract
{
    public function execute()
    {
        $this->page->data['action'] = 'Start';
    }
}
