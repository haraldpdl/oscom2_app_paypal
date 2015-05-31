<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2014 osCommerce

  Released under the GNU General Public License
*/

  if ( isset($_GET['lID']) && is_numeric($_GET['lID']) ) {
    $Qlog = $OSCOM_Db->prepare('select l.*, unix_timestamp(l.date_added) as date_added, c.customers_firstname, c.customers_lastname from :table_oscom_app_paypal_log l left join :table_customers c on (l.customers_id = c.customers_id) where id = :id');
    $Qlog->bindInt(':id', $_GET['lID']);
    $Qlog->execute();

    if ($Qlog->fetch() !== false) {
      $log_request = array();

      $req = explode("\n", $Qlog->value('request'));

      foreach ( $req as $r ) {
        $p = explode(':', $r, 2);

        $log_request[$p[0]] = $p[1];
      }

      $log_response = array();

      $res = explode("\n", $Qlog->value('response'));

      foreach ( $res as $r ) {
        $p = explode(':', $r, 2);

        $log_response[$p[0]] = $p[1];
      }

      $content = 'log_view.php';
    }
  }
?>
