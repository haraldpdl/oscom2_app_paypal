<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2014 osCommerce

  Released under the GNU General Public License
*/

use OSC\OM\OSCOM;
use OSC\OM\Registry;

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

OSCOM::redirect('apps.php', 'PayPal&action=configure&module=' . $current_module);
