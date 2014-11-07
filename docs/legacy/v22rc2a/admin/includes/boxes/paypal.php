<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2014 osCommerce

  Released under the GNU General Public License
*/

  include(DIR_FS_ADMIN . 'includes/languages/' . $language . '/modules/boxes/paypal.php');
  include(DIR_FS_CATALOG . 'includes/apps/paypal/admin/functions/boxes.php');
?>
<!-- paypal //-->
          <tr>
            <td>
<?php
  $heading = array();
  $contents = array();

  $heading[] = array('text'  => MODULES_ADMIN_MENU_PAYPAL_HEADING,
                     'link'  => tep_href_link('paypal.php', 'selected_box=paypal'));

  if ($selected_box == 'paypal') {
    $pp_menu = array();

    foreach ( app_paypal_get_admin_box_links() as $pp ) {
      $pp_menu[] = '<a href="' . $pp['link'] . '" class="menuBoxContentLink">' . $pp['title'] . '</a>';
    }

    $contents[] = array('text'  => implode('<br>', $pp_menu));
  }

  $box = new box;
  echo $box->menuBox($heading, $contents);
?>
            </td>
          </tr>
<!-- paypal_eof //-->
