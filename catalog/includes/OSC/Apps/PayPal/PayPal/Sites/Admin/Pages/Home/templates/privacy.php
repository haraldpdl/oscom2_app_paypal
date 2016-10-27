<?php
require(__DIR__ . '/template_top.php');
?>

<h2><?= $OSCOM_PayPal->getDef('privacy_title'); ?></h2>

<?=
    $OSCOM_PayPal->getDef('privacy_body', [
        'api_req_countries' => implode(', ', $OSCOM_PayPal->getReqApiCountries())
    ]);
?>

<?php
require(__DIR__ . '/template_bottom.php');
?>
