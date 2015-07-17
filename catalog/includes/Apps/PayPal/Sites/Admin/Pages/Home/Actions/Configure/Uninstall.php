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
$m->uninstall();

$OSCOM_PayPal->addAlert($OSCOM_PayPal->getDef('alert_module_uninstall_success'), 'success');

OSCOM::redirect('index.php', 'A&PayPal&action=configure&module=' . $current_module);
