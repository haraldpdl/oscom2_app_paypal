<?php
/**
  * osCommerce Online Merchant
  *
  * @copyright Copyright (c) 2015 osCommerce; http://www.oscommerce.com
  * @license GPL; http://www.oscommerce.com/gpllicense.txt
  */

namespace OSC\Apps\PayPal;

use OSC\OM\Registry;

abstract class APIAbstract
{
    protected $app;
    protected $type = 'paypal';
    protected $server = 'live';
    protected $url;

    abstract public function execute(array $extra_params = null);

    public function __construct($server)
    {
        $this->app = Registry::get('PayPal');

        $this->server = $server;

        switch ($this->type) {
            case 'payflow':
                $this->url = 'https://' . ($this->server != 'live' ? 'pilot-' : '') . 'payflowpro.paypal.com';
                break;

            case 'login':
                $this->url = 'https://api.' . ($this->server != 'live' ? 'sandbox.' : '') . 'paypal.com/v1/identity/openidconnect/tokenservice';
                break;

            case 'paypal':
            default:
                $this->url = 'https://api-3t.' . ($this->server != 'live' ? 'sandbox.' : '') . 'paypal.com/nvp';
        }
    }

    protected function getResult(array &$params, array $headers = null)
    {
        switch ($this->type) {
            case 'paypal':
                $params['VERSION'] = $this->app->getApiVersion();
                break;
        }

        $post = [];

        foreach ($params as $key => $value) {
            $value = utf8_encode(trim($value));

            if ($this->type == 'payflow') {
                $key = $key . '[' . strlen($value) . ']';
            }

            $post[$key] = $value;
        }

        $response = $this->app->makeApiCall($this->url, http_build_query($post, '', '&'), $headers);

        $result = [];

        switch ($this->type) {
            case 'payflow':
            case 'paypal':
                parse_str($response, $result);
                break;

            case 'login':
                $result = @json_decode($response, true);
                break;
        }

        return $result;
    }
}
