<?php
/**
  * osCommerce Online Merchant
  *
  * @copyright Copyright (c) 2015 osCommerce; http://www.oscommerce.com
  * @license GPL; http://www.oscommerce.com/gpllicense.txt
  */

namespace OSC\OM\Apps\PayPal\Module\Hooks\Admin\Orders;

use OSC\OM\HTML;
use OSC\OM\OSCOM;
use OSC\OM\Registry;

use OSC\OM\Apps\PayPal\PayPal as PayPalApp;

class PageTab implements \OSC\OM\Modules\HooksInterface
{
    protected $app;
    protected $db;

    public function __construct()
    {
        if (!Registry::exists('PayPal')) {
            Registry::set('PayPal', new PayPalApp());
        }

        $this->app = Registry::get('PayPal');
        $this->db = Registry::get('Db');
    }

    public function display()
    {
        global $oID, $base_url;

        if (!defined('OSCOM_APP_PAYPAL_TRANSACTIONS_ORDER_STATUS_ID')) {
            return false;
        }

        $this->app->loadLanguageFile('hooks/admin/orders/tab.php');

        $output = '';

        $status = [];

        $Qc = $this->db->prepare('select comments from :table_orders_status_history where orders_id = :orders_id and orders_status_id = :orders_status_id and comments like "Transaction ID:%" order by date_added desc limit 1');
        $Qc->bindInt(':orders_id', $oID);
        $Qc->bindInt(':orders_status_id', OSCOM_APP_PAYPAL_TRANSACTIONS_ORDER_STATUS_ID);
        $Qc->execute();

        if ($Qc->fetch() !== false) {
            foreach (explode("\n", $Qc->value('comments')) as $s) {
                if (!empty($s) && (strpos($s, ':') !== false)) {
                    $entry = explode(':', $s, 2);

                    $status[trim($entry[0])] = trim($entry[1]);
                }
            }

            if (isset($status['Transaction ID'])) {
                $Qorder = $this->db->prepare('select o.orders_id, o.payment_method, o.currency, o.currency_value, ot.value as total from :table_orders o, :table_orders_total ot where o.orders_id = :orders_id and o.orders_id = ot.orders_id and ot.class = "ot_total"');
                $Qorder->bindInt(':orders_id', $oID);
                $Qorder->execute();

                $pp_server = (strpos(strtolower($Qorder->value('payment_method')), 'sandbox') !== false) ? 'sandbox' : 'live';

                $info_button = $this->app->drawButton($this->app->getDef('button_details'), OSCOM::link('orders.php', 'page=' . $_GET['page'] . '&oID=' . $oID . '&action=edit&tabaction=getTransactionDetails'), 'primary', null, true);
                $capture_button = $this->getCaptureButton($status, $Qorder->toArray());
                $void_button = $this->getVoidButton($status, $Qorder->toArray());
                $refund_button = $this->getRefundButton($status, $Qorder->toArray());
                $paypal_button = $this->app->drawButton($this->app->getDef('button_view_at_paypal'), 'https://www.' . ($pp_server == 'sandbox' ? 'sandbox.' : '') . 'paypal.com/cgi-bin/webscr?cmd=_view-a-trans&id=' . $status['Transaction ID'], 'info', 'target="_blank"', true);

                $tab_title = addslashes($this->app->getDef('tab_title'));
                $tab_link = substr(OSCOM::link('orders.php', tep_get_all_get_params()), strlen($base_url)) . '#section_paypal_content';

                $output = <<<EOD
<script>
$(function() {
  $('#orderTabs ul').append('<li><a href="{$tab_link}">{$tab_title}</a></li>');
});
</script>

<div id="section_paypal_content" style="padding: 10px;">
  {$info_button} {$capture_button} {$void_button} {$refund_button} {$paypal_button}
</div>
EOD;

            }
        }

        return $output;
    }

    protected function getCaptureButton($status, $order)
    {
        $output = '';

        if (($status['Pending Reason'] == 'authorization') || ($status['Payment Status'] == 'In-Progress')) {
            $Qv = $this->db->prepare('select comments from :table_orders_status_history where orders_id = :orders_id and orders_status_id = :orders_status_id and comments like "%PayPal App: Void (%" limit 1');
            $Qv->bindInt(':orders_id', $order['orders_id']);
            $Qv->bindInt(':orders_status_id', OSCOM_APP_PAYPAL_TRANSACTIONS_ORDER_STATUS_ID);
            $Qv->execute();

            if ($Qv->fetch() === false) {
                $capture_total = $this->app->formatCurrencyRaw($order['total'], $order['currency'], $order['currency_value']);

                $Qc = $this->db->prepare('select comments from :table_orders_status_history where orders_id = :orders_id and orders_status_id = :orders_status_id and comments like "PayPal App: Capture (%"');
                $Qc->bindInt(':orders_id', $order['orders_id']);
                $Qc->bindInt(':orders_status_id', OSCOM_APP_PAYPAL_TRANSACTIONS_ORDER_STATUS_ID);
                $Qc->execute();

                while ($Qc->fetch()) {
                    if (preg_match('/^PayPal App\: Capture \(([0-9\.]+)\)\n/', $Qc->value('comments'), $c_matches)) {
                        $capture_total -= $this->app->formatCurrencyRaw($c_matches[1], $order['currency'], 1);
                    }
                }

                if ($capture_total > 0) {
                    $output .= $this->app->drawButton($this->app->getDef('button_dialog_capture'), '#', 'success', 'data-button="paypalButtonDoCapture"', true);

                    $dialog_title = HTML::outputProtected($this->app->getDef('dialog_capture_title'));
                    $dialog_body = $this->app->getDef('dialog_capture_body');
                    $field_amount_title = $this->app->getDef('dialog_capture_amount_field_title');
                    $field_last_capture_title = $this->app->getDef('dialog_capture_last_capture_field_title', [
                        'currency' => $order['currency']
                    ]);
                    $capture_link = OSCOM::link('orders.php', 'page=' . $_GET['page'] . '&oID=' . $order['orders_id'] . '&action=edit&tabaction=doCapture');
                    $capture_currency = $order['currency'];
                    $dialog_button_capture = addslashes($this->app->getDef('dialog_capture_button_capture'));
                    $dialog_button_cancel = addslashes($this->app->getDef('dialog_capture_button_cancel'));

                    $output .= <<<EOD
<div id="paypal-dialog-capture" title="{$dialog_title}">
  <form id="ppCaptureForm" action="{$capture_link}" method="post">
    <p>{$dialog_body}</p>

    <p>
      <label for="ppCaptureAmount"><strong>{$field_amount_title}</strong></label>
      <input type="text" name="ppCaptureAmount" value="{$capture_total}" id="ppCaptureAmount" style="text-align: right;" />
      {$capture_currency}
    </p>

    <p id="ppPartialCaptureInfo" style="display: none;"><input type="checkbox" name="ppCatureComplete" value="true" id="ppCaptureComplete" /> <label for="ppCaptureComplete">{$field_last_capture_title}</label></p>
  </form>
</div>

<script>
$(function() {
  $('#paypal-dialog-capture').dialog({
    autoOpen: false,
    resizable: false,
    modal: true,
    buttons: {
      "{$dialog_button_capture}": function() {
        $('#ppCaptureForm').submit();
      },
      "{$dialog_button_cancel}": function() {
        $(this).dialog('close');
      }
    }
  });

  $('a[data-button="paypalButtonDoCapture"]').click(function(e) {
    e.preventDefault();

    $('#paypal-dialog-capture').dialog('open');
  });

  (function() {
    var ppCaptureTotal = {$capture_total};

    $('#ppCaptureAmount').keyup(function() {
      if (this.value != this.value.replace(/[^0-9\.]/g, '')) {
        this.value = this.value.replace(/[^0-9\.]/g, '');
      }

      if ( this.value < ppCaptureTotal ) {
        $('#ppCaptureVoidedValue').text((ppCaptureTotal - this.value).toFixed(2));
        $('#ppPartialCaptureInfo').show();
      } else {
        $('#ppPartialCaptureInfo').hide();
      }
    });
  })();
});
</script>
EOD;
                }
            }
        }

        return $output;
    }

    protected function getVoidButton($status, $order)
    {
        $output = '';

        if ($status['Pending Reason'] == 'authorization') {
            $Qv = $this->db->prepare('select comments from :table_orders_status_history where orders_id = :orders_id and orders_status_id = :orders_status_id and comments like "%PayPal App: Void (%" limit 1');
            $Qv->bindInt(':orders_id', $order['orders_id']);
            $Qv->bindInt(':orders_status_id', OSCOM_APP_PAYPAL_TRANSACTIONS_ORDER_STATUS_ID);
            $Qv->execute();

            if ($Qv->fetch() === false) {
                $capture_total = $this->app->formatCurrencyRaw($order['total'], $order['currency'], $order['currency_value']);

                $Qc = $this->db->prepare('select comments from :table_orders_status_history where orders_id = :orders_id and orders_status_id = :orders_status_id and comments like "PayPal App: Capture (%"');
                $Qc->bindInt(':orders_id', $order['orders_id']);
                $Qc->bindInt(':orders_status_id', OSCOM_APP_PAYPAL_TRANSACTIONS_ORDER_STATUS_ID);
                $Qc->execute();

                while ($Qc->fetch()) {
                    if (preg_match('/^PayPal App\: Capture \(([0-9\.]+)\)\n/', $Qc->value('comments'), $c_matches)) {
                    $capture_total -= $this->app->formatCurrencyRaw($c_matches[1], $order['currency'], 1);
                }
            }

            if ($capture_total > 0) {
                $output .= $this->app->drawButton($this->app->getDef('button_dialog_void'), '#', 'warning', 'data-button="paypalButtonDoVoid"', true);

                $dialog_title = HTML::outputProtected($this->app->getDef('dialog_void_title'));
                $dialog_body = $this->app->getDef('dialog_void_body');
                $void_link = OSCOM::link('orders.php', 'page=' . $_GET['page'] . '&oID=' . $order['orders_id'] . '&action=edit&tabaction=doVoid');
                $dialog_button_void = addslashes($this->app->getDef('dialog_void_button_void'));
                $dialog_button_cancel = addslashes($this->app->getDef('dialog_void_button_cancel'));

                $output .= <<<EOD
<div id="paypal-dialog-void" title="{$dialog_title}">
  <p><span class="ui-icon ui-icon-alert" style="float:left; margin:0 7px 20px 0;"></span>{$dialog_body}</p>
</div>

<script>
$(function() {
  $('#paypal-dialog-void').dialog({
    autoOpen: false,
    resizable: false,
    modal: true,
    buttons: {
      "{$dialog_button_void}": function() {
        window.location = '{$void_link}';
      },
      "{$dialog_button_cancel}": function() {
        $(this).dialog('close');
      }
    }
  });

  $('a[data-button="paypalButtonDoVoid"]').click(function(e) {
    e.preventDefault();

    $('#paypal-dialog-void').dialog('open');
  });
});
</script>
EOD;
                }
            }
        }

        return $output;
    }

    protected function getRefundButton($status, $order)
    {
        $output = '';

        $tids = [];

        $Qc = $this->db->prepare('select comments from :table_orders_status_history where orders_id = :orders_id and orders_status_id = :orders_status_id and comments like "PayPal App: %" order by date_added desc');
        $Qc->bindInt(':orders_id', $_GET['oID']);
        $Qc->bindInt(':orders_status_id', OSCOM_APP_PAYPAL_TRANSACTIONS_ORDER_STATUS_ID);
        $Qc->execute();

        if ($Qc->fetch() !== false) {
            do {
                if (strpos($Qc->value('comments'), 'PayPal App: Refund') !== false) {
                    preg_match('/Parent ID\: ([A-Za-z0-9]+)$/', $Qc->value('comments'), $ppr_matches);

                    $tids[$ppr_matches[1]]['Refund'] = true;
                } elseif (strpos($Qc->value('comments'), 'PayPal App: Capture') !== false) {
                    preg_match('/^PayPal App\: Capture \(([0-9\.]+)\).*Transaction ID\: ([A-Za-z0-9]+)/s', $Qc->value('comments'), $ppr_matches);

                    $tids[$ppr_matches[2]]['Amount'] = $ppr_matches[1];
                }
            } while ($Qc->fetch());
        } elseif ($status['Payment Status'] == 'Completed') {
            $tids[$status['Transaction ID']]['Amount'] = $this->app->formatCurrencyRaw($order['total'], $order['currency'], $order['currency_value']);
        }

        $can_refund = false;

        foreach ($tids as $value) {
            if (!isset($value['Refund'])) {
                $can_refund = true;
                break;
            }
        }

        if ($can_refund === true) {
            $output .= $this->app->drawButton($this->app->getDef('button_dialog_refund'), '#', 'error', 'data-button="paypalButtonRefundTransaction"', true);

            $dialog_title = HTML::outputProtected($this->app->getDef('dialog_refund_title'));
            $dialog_body = $this->app->getDef('dialog_refund_body');
            $refund_link = OSCOM::link('orders.php', 'page=' . $_GET['page'] . '&oID=' . $_GET['oID'] . '&action=edit&tabaction=refundTransaction');
            $dialog_button_refund = addslashes($this->app->getDef('dialog_refund_button_refund'));
            $dialog_button_cancel = addslashes($this->app->getDef('dialog_refund_button_cancel'));

            $refund_fields = '';

            $counter = 0;

            foreach ($tids as $key => $value) {
                $refund_fields .= '<p><input type="checkbox" name="ppRefund[]" value="' . $key . '" id="ppRefundPartial' . $counter . '"' . (isset($value['Refund']) ? ' disabled="disabled"' : '') . ' /> <label for="ppRefundPartial' . $counter . '"' . (isset($value['Refund']) ? ' style="text-decoration: line-through;"' : '') . '>' . $this->app->getDef('dialog_refund_payment_title', [
                    'amount' => $value['Amount']
                ]) . '</label></p>';

                $counter++;
            }

            $output .= <<<EOD
<div id="paypal-dialog-refund" title="{$dialog_title}">
  <form id="ppRefundForm" action="{$refund_link}" method="post">
    <p>{$dialog_body}</p>

    {$refund_fields}
  </form>
</div>

<script>
$(function() {
  $('#paypal-dialog-refund').dialog({
    autoOpen: false,
    resizable: false,
    modal: true,
    buttons: {
      "{$dialog_button_refund}": function() {
        $('#ppRefundForm').submit();
      },
      "{$dialog_button_cancel}": function() {
        $(this).dialog('close');
      }
    }
  });

  $('a[data-button="paypalButtonRefundTransaction"]').click(function(e) {
    e.preventDefault();

    $('#paypal-dialog-refund').dialog('open');
  });
});
</script>
EOD;
        }

        return $output;
    }
}
