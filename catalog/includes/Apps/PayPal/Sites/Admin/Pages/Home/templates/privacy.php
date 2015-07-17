<?php
/**
  * osCommerce Online Merchant
  *
  * @copyright Copyright (c) 2015 osCommerce; http://www.oscommerce.com
  * @license GPL; http://www.oscommerce.com/gpllicense.txt
  */

require(__DIR__ . '/template_top.php');
?>

<h2><?php echo $OSCOM_PayPal->getDef('privacy_title'); ?></h2>

<?php echo $OSCOM_PayPal->getDef('privacy_body', array('api_req_countries' => implode(', ', $OSCOM_PayPal->getReqApiCountries()))); ?>

<?php
require(__DIR__ . '/template_bottom.php');
?>
