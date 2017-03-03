<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2017 osCommerce

  Released under the GNU General Public License
*/

if (!isset($HTTP_GET_VARS)) {
  $HTTP_GET_VARS =& $_GET;
}

if (!isset($HTTP_POST_VARS)) {
  $HTTP_POST_VARS =& $_POST;
}

if (!isset($HTTP_SERVER_VARS)) {
  $HTTP_SERVER_VARS =& $_SERVER;
}

if (!defined('FILENAME_ACCOUNT_HISTORY_INFO')) {
  define('FILENAME_ACCOUNT_HISTORY_INFO', 'account_history_info.php');
}

if (!defined('FILENAME_CHECKOUT_CONFIRMATION')) {
  define('FILENAME_CHECKOUT_CONFIRMATION', 'checkout_confirmation.php');
}

if (!defined('FILENAME_CHECKOUT_PAYMENT')) {
  define('FILENAME_CHECKOUT_PAYMENT', 'checkout_payment.php');
}

if (!defined('FILENAME_CHECKOUT_PROCESS')) {
  define('FILENAME_CHECKOUT_PROCESS', 'checkout_process.php');
}

if (!defined('FILENAME_CHECKOUT_SHIPPING')) {
  define('FILENAME_CHECKOUT_SHIPPING', 'checkout_shipping.php');
}

if (!defined('FILENAME_CHECKOUT_SHIPPING_ADDRESS')) {
  define('FILENAME_CHECKOUT_SHIPPING_ADDRESS', 'checkout_shipping_address.php');
}

if (!defined('FILENAME_CHECKOUT_SUCCESS')) {
  define('FILENAME_CHECKOUT_SUCCESS', 'checkout_success.php');
}

if (!defined('FILENAME_CREATE_ACCOUNT')) {
  define('FILENAME_CREATE_ACCOUNT', 'create_account.php');
}

if (!defined('FILENAME_DEFAULT')) {
  define('FILENAME_DEFAULT', 'index.php');
}

if (!defined('FILENAME_LOGIN')) {
  define('FILENAME_LOGIN', 'login.php');
}

if (!defined('FILENAME_ORDERS')) {
  define('FILENAME_ORDERS', 'orders.php');
}

if (!defined('FILENAME_PRODUCT_INFO')) {
  define('FILENAME_PRODUCT_INFO', 'product_info.php');
}

if (!defined('FILENAME_SHOPPING_CART')) {
  define('FILENAME_SHOPPING_CART', 'shopping_cart.php');
}
?>
