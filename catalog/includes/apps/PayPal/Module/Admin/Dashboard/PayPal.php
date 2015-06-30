<?php
/**
  * osCommerce Online Merchant
  *
  * @copyright Copyright (c) 2015 osCommerce; http://www.oscommerce.com
  * @license GPL; http://www.oscommerce.com/gpllicense.txt
  */

namespace OSC\OM\Apps\PayPal\Module\Admin\Dashboard;

use OSC\OM\OSCOM;
use OSC\OM\Registry;

use OSC\OM\Apps\PayPal\PayPal as PayPalApp;

class PayPal extends \OSC\OM\Modules\AdminDashboardAbstract
{
    protected $app;

    protected function init()
    {
        if (!Registry::exists('PayPal')) {
            Registry::set('PayPal', new PayPalApp());
        }

        $this->app = Registry::get('PayPal');

        $this->app->loadLanguageFile('admin/balance.php');
        $this->app->loadLanguageFile('admin/modules/dashboard/d_paypal_app.php');

        $this->title = $this->app->getDef('module_admin_dashboard_title');
        $this->description = $this->app->getDef('module_admin_dashboard_description');

        if ( defined('MODULE_ADMIN_DASHBOARD_PAYPAL_APP_SORT_ORDER') ) {
            $this->sort_order = MODULE_ADMIN_DASHBOARD_PAYPAL_APP_SORT_ORDER;
            $this->enabled = true;
        }
    }

    public function getOutput()
    {
        $version = $this->app->getVersion();
        $version_check_result = defined('OSCOM_APP_PAYPAL_VERSION_CHECK') ? '"' . OSCOM_APP_PAYPAL_VERSION_CHECK . '"' : 'undefined';
        $can_apply_online_updates = class_exists('ZipArchive') && function_exists('openssl_verify') ? 'true' : 'false';
        $has_live_account = ($this->app->hasApiCredentials('live') === true) ? 'true' : 'false';
        $has_sandbox_account = ($this->app->hasApiCredentials('sandbox') === true) ? 'true' : 'false';
        $version_check_url = OSCOM::link('apps.php', 'PayPal&action=checkVersion');
        $new_update_notice = $this->app->getDef('update_available_body', array('button_view_update' => $this->app->drawButton($this->app->getDef('button_view_update'), OSCOM::link('apps.php', 'PayPal&action=update'), 'success', null, true)));
        $heading_live_account = $this->app->getDef('heading_live_account', array('account' => str_replace('_api1.', '@', $this->app->getApiCredentials('live', 'username'))));
        $heading_sandbox_account = $this->app->getDef('heading_sandbox_account', array('account' => str_replace('_api1.', '@', $this->app->getApiCredentials('sandbox', 'username'))));
        $receiving_balance_progress = $this->app->getDef('retrieving_balance_progress');
        $app_get_started = $this->app->drawButton($this->app->getDef('button_app_get_started'), OSCOM::link('apps.php', 'PayPal'), 'warning', null, true);
        $error_balance_retrieval = addslashes($this->app->getDef('error_balance_retrieval'));
        $get_balance_url = OSCOM::link('apps.php', 'PayPal&action=balance&subaction=retrieve&type=PPTYPE');

        $output = <<<EOD
<style>
.pp-container {
  font-size: 12px;
  line-height: 1.5;
}

.pp-panel {
  padding: 1px 10px;
  margin-bottom: 15px;
}

.pp-panel.pp-panel-success {
  background-color: #e8ffe1;
  border-left: 2px solid #a0e097;
  color: #349a20;
}

.pp-panel-header-success {
  background-color: #a0e097;
  background-image: linear-gradient(transparent, rgba(0, 0, 0, 0.05) 40%, rgba(0, 0, 0, 0.1));
  font-size: 12px;
  color: #fff;
  margin: 0;
  padding: 3px 15px;
}

.pp-panel.pp-panel-warning {
  background-color: #fff4dd;
  border-left: 2px solid #e2ab62;
  color: #cd7c20;
}

.pp-panel-header-warning {
  background-color: #e2ab62;
  background-image: linear-gradient(transparent, rgba(0, 0, 0, 0.05) 40%, rgba(0, 0, 0, 0.1));
  font-size: 12px;
  color: #fff;
  margin: 0;
  padding: 3px 15px;
}

small .pp-button {
  font-size: 11px !important;
}
</style>
<script>
if ( typeof jQuery == 'undefined' ) {
  document.write('<scr' + 'ipt src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.1/jquery.min.js"></scr' + 'ipt>');
}
</script>
<script>
var OSCOM = {
  dateNow: new Date(),
  htmlSpecialChars: function(string) {
    if ( string == null ) {
      string = '';
    }

    return $('<span />').text(string).html();
  },
  nl2br: function(string) {
    return string.replace(/\\n/g, '<br />');
  },
  APP: {
    PAYPAL: {
      version: '{$version}',
      versionCheckResult: {$version_check_result},
      doOnlineVersionCheck: false,
      canApplyOnlineUpdates: {$can_apply_online_updates},
      accountTypes: {
        live: {$has_live_account},
        sandbox: {$has_sandbox_account}
      },
      versionCheck: function() {
        $.get('{$version_check_url}', function (data) {
          var versions = [];

          if ( OSCOM.APP.PAYPAL.canApplyOnlineUpdates == true ) {
            try {
              data = $.parseJSON(data);
            } catch (ex) {
            }

            if ( (typeof data == 'object') && ('rpcStatus' in data) && (data['rpcStatus'] == 1) && (typeof data['releases'] != 'undefined') && (data['releases'].length > 0) ) {
              for ( var i = 0; i < data['releases'].length; i++ ) {
                versions.push(data['releases'][i]['version']);
              }
            }
          } else {
            if ( (typeof data == 'string') && (data.indexOf('rpcStatus') > -1) ) {
              var result = data.split("\\n", 2);

              if ( result.length == 2 ) {
                var rpcStatus = result[0].split('=', 2);

                if ( rpcStatus[1] == 1 ) {
                  var release = result[1].split('=', 2);

                  versions.push(release[1]);
                }
              }
            }
          }

          if ( versions.length > 0 ) {
            OSCOM.APP.PAYPAL.versionCheckResult = [ OSCOM.dateNow.getDate(), Math.max.apply(Math, versions) ];

            OSCOM.APP.PAYPAL.versionCheckNotify();
          }
        });
      },
      versionCheckNotify: function() {
        if ( (typeof this.versionCheckResult[0] != 'undefined') && (typeof this.versionCheckResult[1] != 'undefined') ) {
          if ( this.versionCheckResult[1] > this.version ) {
            $('#ppAppUpdateNotice').show();
          }
        }
      }
    }
  }
};

if ( typeof OSCOM.APP.PAYPAL.versionCheckResult != 'undefined' ) {
  OSCOM.APP.PAYPAL.versionCheckResult = OSCOM.APP.PAYPAL.versionCheckResult.split('-', 2);
}
</script>

<div class="pp-container">
  <div id="ppAppUpdateNotice" style="display: none;">
    <div class="pp-panel pp-panel-success">
      {$new_update_notice}
    </div>
  </div>

  <div id="ppAccountBalanceLive">
    <h3 class="pp-panel-header-success">{$heading_live_account}</h3>
    <div id="ppBalanceLiveInfo" class="pp-panel pp-panel-success">
      <p>{$receiving_balance_progress}</p>
    </div>
  </div>

  <div id="ppAccountBalanceSandbox">
    <h3 class="pp-panel-header-warning">{$heading_sandbox_account}</h3>
    <div id="ppBalanceSandboxInfo" class="pp-panel pp-panel-warning">
      <p>{$receiving_balance_progress}</p>
    </div>
  </div>

  <div id="ppAccountBalanceNone" style="display: none;">
    <div class="pp-panel pp-panel-warning">
      <p>{$app_get_started}</p>
    </div>
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
  if ( typeof OSCOM.APP.PAYPAL.versionCheckResult == 'undefined' ) {
    OSCOM.APP.PAYPAL.doOnlineVersionCheck = true;
  } else {
    if ( typeof OSCOM.APP.PAYPAL.versionCheckResult[0] != 'undefined' ) {
      if ( OSCOM.dateNow.getDate() != OSCOM.APP.PAYPAL.versionCheckResult[0] ) {
        OSCOM.APP.PAYPAL.doOnlineVersionCheck = true;
      }
    }
  }

  if ( OSCOM.APP.PAYPAL.doOnlineVersionCheck == true ) {
    OSCOM.APP.PAYPAL.versionCheck();
  } else {
    OSCOM.APP.PAYPAL.versionCheckNotify();
  }

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
        $this->db->save('configuration', [
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
