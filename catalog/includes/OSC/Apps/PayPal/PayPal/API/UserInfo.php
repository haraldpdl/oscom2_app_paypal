<?php
/**
  * osCommerce Online Merchant
  *
  * @copyright Copyright (c) 2015 osCommerce; http://www.oscommerce.com
  * @license GPL; http://www.oscommerce.com/gpllicense.txt
  */

namespace OSC\Apps\PayPal\PayPal\API;

class UserInfo extends \OSC\Apps\PayPal\PayPal\APIAbstract
{
    protected $type = 'login';

    public function execute(array $extra_params = null)
    {
        $this->url = 'https://api.' . ($this->server != 'live' ? 'sandbox.' : '') . 'paypal.com/v1/identity/openidconnect/userinfo/?schema=openid&access_token=' . $extra_params['access_token'];

        $response = $this->getResult($params);

        return [
            'res' => $response,
            'success' => (is_array($response) && !isset($response['error'])),
            'req' => $params
        ];
    }
}
