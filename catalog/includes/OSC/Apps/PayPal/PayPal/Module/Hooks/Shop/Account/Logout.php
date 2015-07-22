<?php
/**
  * osCommerce Online Merchant
  *
  * @copyright Copyright (c) 2015 osCommerce; http://www.oscommerce.com
  * @license GPL; http://www.oscommerce.com/gpllicense.txt
  */

namespace OSC\Apps\PayPal\Module\Hooks\Shop\Account;

class Logout implements \OSC\OM\Modules\HooksInterface
{
    public function execute()
    {
        if (isset($_SESSION['paypal_login_access_token'])) {
            unset($_SESSION['paypal_login_access_token']);
        }
    }
}
