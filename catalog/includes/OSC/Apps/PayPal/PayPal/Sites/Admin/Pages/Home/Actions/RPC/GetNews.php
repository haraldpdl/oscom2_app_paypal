<?php
/**
  * osCommerce Online Merchant
  *
  * @copyright Copyright (c) 2015 osCommerce; http://www.oscommerce.com
  * @license GPL; http://www.oscommerce.com/gpllicense.txt
  */

namespace OSC\Apps\PayPal\Sites\Admin\Pages\Home\Actions\RPC;

use OSC\OM\HTTP;

class GetNews extends \OSC\OM\PagesActionsAbstract
{
    public function execute()
    {
        $result = [
            'rpcStatus' => -1
        ];

        $response = @json_decode(HTTP::getResponse([
            'url' => 'http://www.oscommerce.com/index.php?RPC&Website&Index&GetPartnerBanner&forumid=105&onlyjson=true'
        ]), true);

        if (is_array($response) && isset($response['title'])) {
            $result = $response;

            $result['rpcStatus'] = 1;
        }

        echo json_encode($result);
    }
}
