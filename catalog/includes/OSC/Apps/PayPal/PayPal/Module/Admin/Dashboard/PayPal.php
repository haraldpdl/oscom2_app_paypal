<?php
/**
  * osCommerce Online Merchant
  *
  * @copyright Copyright (c) 2015 osCommerce; http://www.oscommerce.com
  * @license GPL; http://www.oscommerce.com/gpllicense.txt
  */

namespace OSC\Apps\PayPal\PayPal\Module\Admin\Dashboard;

use OSC\OM\HTML;
use OSC\OM\Registry;

use OSC\Apps\PayPal\PayPal\PayPal as PayPalApp;

class PayPal extends \OSC\OM\Modules\AdminDashboardAbstract
{
    protected $app;

    protected function init()
    {
        if (!Registry::exists('PayPal')) {
            Registry::set('PayPal', new PayPalApp());
        }

        $this->app = Registry::get('PayPal');

        $this->app->loadDefinitionFile('admin/balance.php');
        $this->app->loadDefinitionFile('admin/modules/dashboard/d_paypal_app.php');

        $this->title = $this->app->getDef('module_admin_dashboard_title');
        $this->description = $this->app->getDef('module_admin_dashboard_description');

        if (defined('MODULE_ADMIN_DASHBOARD_PAYPAL_APP_SORT_ORDER')) {
            $this->sort_order = MODULE_ADMIN_DASHBOARD_PAYPAL_APP_SORT_ORDER;
            $this->enabled = true;
        }
    }

    public function getOutput()
    {
        $has_live_account = ($this->app->hasApiCredentials('live') === true) ? 'true' : 'false';
        $has_sandbox_account = ($this->app->hasApiCredentials('sandbox') === true) ? 'true' : 'false';
        $heading_live_account = $this->app->getDef('heading_live_account', [
          ':account' => str_replace('_api1.', '@', $this->app->getApiCredentials('live', 'username'))
        ]);
        $heading_sandbox_account = $this->app->getDef('heading_sandbox_account', [
          ':account' => str_replace('_api1.', '@', $this->app->getApiCredentials('sandbox', 'username'))
        ]);
        $receiving_balance_progress = $this->app->getDef('retrieving_balance_progress');
        $app_get_started = HTML::button($this->app->getDef('button_app_get_started'), null, $this->app->link(), null, 'btn-primary');
        $error_balance_retrieval = addslashes($this->app->getDef('error_balance_retrieval'));
        $get_balance_url = addslashes($this->app->link('RPC&GetBalance&type=PPTYPE'));

        $output = <<<EOD
<script>
var OSCOM = {
  htmlSpecialChars: function(string) {
    if ( string == null ) {
      string = '';
    }

    return $('<span />').text(string).html();
  },
  APP: {
    PAYPAL: {
      accountTypes: {
        live: {$has_live_account},
        sandbox: {$has_sandbox_account}
      }
    }
  }
};
</script>

<div id="ppAccountBalanceLive" class="panel panel-success">
  <div class="panel-heading">
    <h3 class="panel-title">{$heading_live_account}</h3>
  </div>

  <div id="ppBalanceLiveInfo" class="panel-body">
    <p>{$receiving_balance_progress}</p>
  </div>
</div>

<div id="ppAccountBalanceSandbox" class="panel panel-warning">
  <div class="panel-heading">
    <h3 class="panel-title">{$heading_sandbox_account}</h3>
  </div>

  <div id="ppBalanceSandboxInfo" class="panel-body">
    <p>{$receiving_balance_progress}</p>
  </div>
</div>

<div id="ppAccountBalanceNone" class="panel panel-primary" style="display: none;">
  <div class="panel-heading">
    <h3 class="panel-title">PayPal</h3>
  </div>

  <div class="panel-body">
    <p>{$app_get_started}</p>
  </div>
</div>

<script>
OSCOM.APP.PAYPAL.getBalance = function(type) {
  var def = {
    'error_balance_retrieval': '{$error_balance_retrieval}'
  };

  var divId = 'ppBalance' + type.charAt(0).toUpperCase() + type.slice(1) + 'Info';

  $.get('{$get_balance_url}'.replace('PPTYPE', type), function (data) {
    var balance = {};

    $('#' + divId).empty();

    try {
      data = $.parseJSON(data);
    } catch (ex) {
    }

    if ( (typeof data == 'object') && ('rpcStatus' in data) && (data['rpcStatus'] == 1) ) {
      if ( ('balance' in data) && (typeof data['balance'] == 'object') ) {
        balance = data['balance'];
      }
    } else if ( (typeof data == 'string') && (data.indexOf('rpcStatus') > -1) ) {
      var result = data.split("\\n", 1);

      if ( result.length == 1 ) {
        var rpcStatus = result[0].split('=', 2);

        if ( rpcStatus[1] == 1 ) {
          var entries = data.split("\\n");

          for ( var i = 0; i < entries.length; i++ ) {
            var entry = entries[i].split('=', 2);

            if ( (entry.length == 2) && (entry[0] != 'rpcStatus') ) {
              balance[entry[0]] = entry[1];
            }
          }
        }
      }
    }

    var pass = false;

    for ( var key in balance ) {
      pass = true;

      $('#' + divId).append('<p><strong>' + OSCOM.htmlSpecialChars(key) + ':</strong> ' + OSCOM.htmlSpecialChars(balance[key]) + '</p>');
    }

    if ( pass == false ) {
      $('#' + divId).append('<p>' + def['error_balance_retrieval'] + '</p>');
    }
  }).fail(function() {
    $('#' + divId).empty().append('<p>' + def['error_balance_retrieval'] + '</p>');
  });
};

$(function() {
  (function() {
    var pass = false;

    if ( OSCOM.APP.PAYPAL.accountTypes['live'] == true ) {
      pass = true;

      $('#ppAccountBalanceSandbox').hide();

      OSCOM.APP.PAYPAL.getBalance('live');
    } else {
      $('#ppAccountBalanceLive').hide();

      if ( OSCOM.APP.PAYPAL.accountTypes['sandbox'] == true ) {
        pass = true;

        OSCOM.APP.PAYPAL.getBalance('sandbox');
      } else {
        $('#ppAccountBalanceSandbox').hide();
      }
    }

    if ( pass == false ) {
      $('#ppAccountBalanceNone').show();
    }
  })();
});
</script>
EOD;

        return $output;
    }

    public function install()
    {
        $this->app->db->save('configuration', [
            'configuration_title' => 'Sort Order',
            'configuration_key' => 'MODULE_ADMIN_DASHBOARD_PAYPAL_APP_SORT_ORDER',
            'configuration_value' => '0',
            'configuration_description' => 'Sort order of display. Lowest is displayed first.',
            'configuration_group_id' => '6',
            'sort_order' => '0',
            'date_added' => 'now()'
        ]);
    }

    public function keys()
    {
        return [
            'MODULE_ADMIN_DASHBOARD_PAYPAL_APP_SORT_ORDER'
        ];
    }
}
