<?php
/**
  * osCommerce Online Merchant
  *
  * @copyright Copyright (c) 2015 osCommerce; http://www.oscommerce.com
  * @license GPL; http://www.oscommerce.com/gpllicense.txt
  */

namespace OSC\Apps\PayPal\Sites\Admin\Pages\Home\Actions\Credentials;

use OSC\OM\HTML;
use OSC\OM\OSCOM;
use OSC\OM\Registry;

class Process extends \OSC\OM\PagesActionsAbstract
{
    public function execute()
    {
        $OSCOM_PayPal = Registry::get('PayPal');

        $current_module = $this->page->data['current_module'];

        $data = [];

        if ($current_module == 'PP') {
            $data = [
                'OSCOM_APP_PAYPAL_LIVE_SELLER_EMAIL' => isset($_POST['live_email']) ? HTML::sanitize($_POST['live_email']) : '',
                'OSCOM_APP_PAYPAL_LIVE_SELLER_EMAIL_PRIMARY' => isset($_POST['live_email_primary']) ? HTML::sanitize($_POST['live_email_primary']) : '',
                'OSCOM_APP_PAYPAL_LIVE_API_USERNAME' => isset($_POST['live_username']) ? HTML::sanitize($_POST['live_username']) : '',
                'OSCOM_APP_PAYPAL_LIVE_API_PASSWORD' => isset($_POST['live_password']) ? HTML::sanitize($_POST['live_password']) : '',
                'OSCOM_APP_PAYPAL_LIVE_API_SIGNATURE' => isset($_POST['live_signature']) ? HTML::sanitize($_POST['live_signature']) : '',
                'OSCOM_APP_PAYPAL_SANDBOX_SELLER_EMAIL' => isset($_POST['sandbox_email']) ? HTML::sanitize($_POST['sandbox_email']) : '',
                'OSCOM_APP_PAYPAL_SANDBOX_SELLER_EMAIL_PRIMARY' => isset($_POST['sandbox_email_primary']) ? HTML::sanitize($_POST['sandbox_email_primary']) : '',
                'OSCOM_APP_PAYPAL_SANDBOX_API_USERNAME' => isset($_POST['sandbox_username']) ? HTML::sanitize($_POST['sandbox_username']) : '',
                'OSCOM_APP_PAYPAL_SANDBOX_API_PASSWORD' => isset($_POST['sandbox_password']) ? HTML::sanitize($_POST['sandbox_password']) : '',
                'OSCOM_APP_PAYPAL_SANDBOX_API_SIGNATURE' => isset($_POST['sandbox_signature']) ? HTML::sanitize($_POST['sandbox_signature']) : ''
            ];
        } elseif ($current_module == 'PF') {
            $data = [
                'OSCOM_APP_PAYPAL_PF_LIVE_PARTNER' => isset($_POST['live_partner']) ? HTML::sanitize($_POST['live_partner']) : '',
                'OSCOM_APP_PAYPAL_PF_LIVE_VENDOR' => isset($_POST['live_vendor']) ? HTML::sanitize($_POST['live_vendor']) : '',
                'OSCOM_APP_PAYPAL_PF_LIVE_USER' => isset($_POST['live_user']) ? HTML::sanitize($_POST['live_user']) : '',
                'OSCOM_APP_PAYPAL_PF_LIVE_PASSWORD' => isset($_POST['live_password']) ? HTML::sanitize($_POST['live_password']) : '',
                'OSCOM_APP_PAYPAL_PF_SANDBOX_PARTNER' => isset($_POST['sandbox_partner']) ? HTML::sanitize($_POST['sandbox_partner']) : '',
                'OSCOM_APP_PAYPAL_PF_SANDBOX_VENDOR' => isset($_POST['sandbox_vendor']) ? HTML::sanitize($_POST['sandbox_vendor']) : '',
                'OSCOM_APP_PAYPAL_PF_SANDBOX_USER' => isset($_POST['sandbox_user']) ? HTML::sanitize($_POST['sandbox_user']) : '',
                'OSCOM_APP_PAYPAL_PF_SANDBOX_PASSWORD' => isset($_POST['sandbox_password']) ? HTML::sanitize($_POST['sandbox_password']) : ''
            ];
        }

        foreach ($data as $key => $value) {
            $OSCOM_PayPal->saveParameter($key, $value);
        }

        $OSCOM_PayPal->addAlert($OSCOM_PayPal->getDef('alert_credentials_saved_success'), 'success');

        OSCOM::redirect('index.php', 'A&PayPal&Credentials&module=' . $current_module);
    }
}
