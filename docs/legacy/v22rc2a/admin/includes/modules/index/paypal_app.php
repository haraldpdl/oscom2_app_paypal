<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2014 osCommerce

  Released under the GNU General Public License
*/

  if ( file_exists(DIR_FS_ADMIN . DIR_WS_MODULES . 'dashboard/d_paypal_app.php') ) {
    include(DIR_FS_ADMIN . DIR_WS_MODULES . 'dashboard/d_paypal_app.php');

    $d_paypal_app = new d_paypal_app();

    echo $d_paypal_app->getOutput();
  }
?>
