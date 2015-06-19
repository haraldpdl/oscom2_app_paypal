<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2014 osCommerce

  Released under the GNU General Public License
*/

  use OSC\OM\HTML;
  use OSC\OM\OSCOM;
  use OSC\OM\Registry;

  if ( !class_exists('OSCOM_PayPal') ) {
    include(DIR_FS_CATALOG . 'includes/apps/PayPal/OSCOM_PayPal.php');
  }

  class paypal_hook_admin_orders_action {
    function paypal_hook_admin_orders_action() {
      global $OSCOM_PayPal;

      if ( !isset($OSCOM_PayPal) || !is_object($OSCOM_PayPal) || (get_class($OSCOM_PayPal) != 'OSCOM_PayPal') ) {
        $OSCOM_PayPal = new OSCOM_PayPal();
      }

      $this->_app = $OSCOM_PayPal;

      $this->_app->loadLanguageFile('hooks/admin/orders/action.php');
    }

    function execute() {
      $OSCOM_Db = Registry::get('Db');

      if ( isset($_GET['tabaction']) ) {
        $Qstatus = $OSCOM_Db->prepare('select comments from :table_orders_status_history where orders_id = :orders_id and orders_status_id = :orders_status_id and comments like "%Transaction ID:%" order by date_added limit 1');
        $Qstatus->bindInt(':orders_id', $_GET['oID']);
        $Qstatus->bindInt(':orders_status_id', OSCOM_APP_PAYPAL_TRANSACTIONS_ORDER_STATUS_ID);
        $Qstatus->execute();

        if ($Qstatus->fetch() !== false) {
          $pp = array();

          foreach ( explode("\n", $Qstatus->value('comments')) as $s ) {
            if ( !empty($s) && (strpos($s, ':') !== false) ) {
              $entry = explode(':', $s, 2);

              $pp[trim($entry[0])] = trim($entry[1]);
            }
          }

          if ( isset($pp['Transaction ID']) ) {
            $Qorder = $OSCOM_Db->prepare('select o.orders_id, o.payment_method, o.currency, o.currency_value, ot.value as total from :table_orders o, :table_orders_total ot where o.orders_id = :orders_id and o.orders_id = ot.orders_id and ot.class = "ot_total"');
            $Qorder->bindInt(':orders_id', $_GET['oID']);
            $Qorder->execute();

            switch ( $_GET['tabaction'] ) {
              case 'getTransactionDetails':
                $this->getTransactionDetails($pp, $Qorder->toArray());
                break;

              case 'doCapture':
                $this->doCapture($pp, $Qorder->toArray());
                break;

              case 'doVoid':
                $this->doVoid($pp, $Qorder->toArray());
                break;

              case 'refundTransaction':
                $this->refundTransaction($pp, $Qorder->toArray());
                break;
            }

            OSCOM::redirect('orders.php', 'page=' . $_GET['page'] . '&oID=' . $_GET['oID'] . '&action=edit#section_status_history_content');
          }
        }
      }
    }

    function getTransactionDetails($comments, $order) {
      global $messageStack;

      $OSCOM_Db = Registry::get('Db');

      $result = null;

      if ( !isset($comments['Gateway']) ) {
        $response = $this->_app->getApiResult('APP', 'GetTransactionDetails', array('TRANSACTIONID' => $comments['Transaction ID']), (strpos($order['payment_method'], 'Sandbox') === false) ? 'live' : 'sandbox');

        if ( in_array($response['ACK'], array('Success', 'SuccessWithWarning')) ) {
          $result = 'Transaction ID: ' . HTML::sanitize($response['TRANSACTIONID']) . "\n" .
                    'Payer Status: ' . HTML::sanitize($response['PAYERSTATUS']) . "\n" .
                    'Address Status: ' . HTML::sanitize($response['ADDRESSSTATUS']) . "\n" .
                    'Payment Status: ' . HTML::sanitize($response['PAYMENTSTATUS']) . "\n" .
                    'Payment Type: ' . HTML::sanitize($response['PAYMENTTYPE']) . "\n" .
                    'Pending Reason: ' . HTML::sanitize($response['PENDINGREASON']);
        }
      } elseif ( $comments['Gateway'] == 'Payflow' ) {
        $response = $this->_app->getApiResult('APP', 'PayflowInquiry', array('ORIGID' => $comments['Transaction ID']), (strpos($order['payment_method'], 'Sandbox') === false) ? 'live' : 'sandbox');

        if ( isset($response['RESULT']) && ($response['RESULT'] == '0') ) {
          $result = 'Transaction ID: ' . HTML::sanitize($response['ORIGPNREF']) . "\n" .
                    'Gateway: Payflow' . "\n";

          $pending_reason = $response['TRANSSTATE'];
          $payment_status = null;

          switch ( $response['TRANSSTATE'] ) {
            case '3':
              $pending_reason = 'authorization';
              $payment_status = 'Pending';
              break;

            case '4':
              $pending_reason = 'other';
              $payment_status = 'In-Progress';
              break;

            case '6':
              $pending_reason = 'scheduled';
              $payment_status = 'Pending';
              break;

            case '8':
            case '9':
              $pending_reason = 'None';
              $payment_status = 'Completed';
              break;
          }

          if ( isset($payment_status) ) {
            $result .= 'Payment Status: ' . HTML::sanitize($payment_status) . "\n";
          }

          $result .= 'Pending Reason: ' . HTML::sanitize($pending_reason) . "\n";

          switch ( $response['AVSADDR'] ) {
            case 'Y':
              $result .= 'AVS Address: Match' . "\n";
              break;

            case 'N':
              $result .= 'AVS Address: No Match' . "\n";
              break;
          }

          switch ( $response['AVSZIP'] ) {
            case 'Y':
              $result .= 'AVS ZIP: Match' . "\n";
              break;

            case 'N':
              $result .= 'AVS ZIP: No Match' . "\n";
              break;
          }

          switch ( $response['IAVS'] ) {
            case 'Y':
              $result .= 'IAVS: International' . "\n";
              break;

            case 'N':
              $result .= 'IAVS: USA' . "\n";
              break;
          }

          switch ( $response['CVV2MATCH'] ) {
            case 'Y':
              $result .= 'CVV2: Match' . "\n";
              break;

            case 'N':
              $result .= 'CVV2: No Match' . "\n";
              break;
          }
        }
      }

      if ( !empty($result) ) {
        $sql_data_array = array('orders_id' => (int)$order['orders_id'],
                                'orders_status_id' => OSCOM_APP_PAYPAL_TRANSACTIONS_ORDER_STATUS_ID,
                                'date_added' => 'now()',
                                'customer_notified' => '0',
                                'comments' => $result);

        $OSCOM_Db->save('orders_status_history', $sql_data_array);

        $messageStack->add_session($this->_app->getDef('ms_success_getTransactionDetails'), 'success');
      } else {
        $messageStack->add_session($this->_app->getDef('ms_error_getTransactionDetails'), 'error');
      }
    }

    function doCapture($comments, $order) {
      global $messageStack;

      $OSCOM_Db = Registry::get('Db');

      $pass = false;

      $capture_total = $capture_value = $this->_app->formatCurrencyRaw($order['total'], $order['currency'], $order['currency_value']);
      $capture_final = true;

      if ( $this->_app->formatCurrencyRaw($_POST['ppCaptureAmount'], $order['currency'], 1) < $capture_value ) {
        $capture_value = $this->_app->formatCurrencyRaw($_POST['ppCaptureAmount'], $order['currency'], 1);
        $capture_final = (isset($_POST['ppCatureComplete']) && ($_POST['ppCatureComplete'] == 'true')) ? true : false;
      }

      if ( !isset($comments['Gateway']) ) {
        $params = array('AUTHORIZATIONID' => $comments['Transaction ID'],
                        'AMT' => $capture_value,
                        'CURRENCYCODE' => $order['currency'],
                        'COMPLETETYPE' => ($capture_final === true) ? 'Complete' : 'NotComplete');

        $response = $this->_app->getApiResult('APP', 'DoCapture', $params, (strpos($order['payment_method'], 'Sandbox') === false) ? 'live' : 'sandbox');

        if ( in_array($response['ACK'], array('Success', 'SuccessWithWarning')) ) {
          $transaction_id = $response['TRANSACTIONID'];

          $pass = true;
        }
      } elseif ( $comments['Gateway'] == 'Payflow' ) {
        $params = array('ORIGID' => $comments['Transaction ID'],
                        'AMT' => $capture_value,
                        'CAPTURECOMPLETE' => ($capture_final === true) ? 'Y' : 'N');

        $response = $this->_app->getApiResult('APP', 'PayflowCapture', $params, (strpos($order['payment_method'], 'Sandbox') === false) ? 'live' : 'sandbox');

        if ( isset($response['RESULT']) && ($response['RESULT'] == '0') ) {
          $transaction_id = $response['PNREF'];

          $pass = true;
        }
      }

      if ( $pass === true ) {
        $result = 'PayPal App: Capture (' . $capture_value . ')' . "\n";

        if ( ($capture_value < $capture_total) && ($capture_final === true) ) {
          $result .= 'PayPal App: Void (' . $this->_app->formatCurrencyRaw($capture_total - $capture_value, $order['currency'], 1) . ')' . "\n";
        }

        $result .= 'Transaction ID: ' . HTML::sanitize($transaction_id);

        $sql_data_array = array('orders_id' => (int)$order['orders_id'],
                                'orders_status_id' => OSCOM_APP_PAYPAL_TRANSACTIONS_ORDER_STATUS_ID,
                                'date_added' => 'now()',
                                'customer_notified' => '0',
                                'comments' => $result);

        $OSCOM_Db->save('orders_status_history', $sql_data_array);

        $messageStack->add_session($this->_app->getDef('ms_success_doCapture'), 'success');
      } else {
        $messageStack->add_session($this->_app->getDef('ms_error_doCapture'), 'error');
      }
    }

    function doVoid($comments, $order) {
      global $messageStack;

      $OSCOM_Db = Registry::get('Db');

      $pass = false;

      if ( !isset($comments['Gateway']) ) {
        $response = $this->_app->getApiResult('APP', 'DoVoid', array('AUTHORIZATIONID' => $comments['Transaction ID']), (strpos($order['payment_method'], 'Sandbox') === false) ? 'live' : 'sandbox');

        if ( in_array($response['ACK'], array('Success', 'SuccessWithWarning')) ) {
          $pass = true;
        }
      } elseif ( $comments['Gateway'] == 'Payflow' ) {
        $response = $this->_app->getApiResult('APP', 'PayflowVoid', array('ORIGID' => $comments['Transaction ID']), (strpos($order['payment_method'], 'Sandbox') === false) ? 'live' : 'sandbox');

        if ( isset($response['RESULT']) && ($response['RESULT'] == '0') ) {
          $pass = true;
        }
      }

      if ( $pass === true ) {
        $capture_total = $this->_app->formatCurrencyRaw($order['total'], $order['currency'], $order['currency_value']);

        $Qc = $OSCOM_Db->prepare('select comments from :table_orders_status_history where orders_id = :orders_id and orders_status_id = :orders_status_id and comments like "PayPal App: Capture (%"');
        $Qc->bindInt(':orders_id', $order['orders_id']);
        $Qc->bindInt(':orders_status_id', OSCOM_APP_PAYPAL_TRANSACTIONS_ORDER_STATUS_ID);
        $Qc->execute();

        while ($Qc->fetch()) {
          if ( preg_match('/^PayPal App\: Capture \(([0-9\.]+)\)\n/', $Qc->value('comments'), $c_matches) ) {
            $capture_total -= $this->_app->formatCurrencyRaw($c_matches[1], $order['currency'], 1);
          }
        }

        $result = 'PayPal App: Void (' . $capture_total . ')';

        $sql_data_array = array('orders_id' => (int)$order['orders_id'],
                                'orders_status_id' => OSCOM_APP_PAYPAL_TRANSACTIONS_ORDER_STATUS_ID,
                                'date_added' => 'now()',
                                'customer_notified' => '0',
                                'comments' => $result);

        $OSCOM_Db->save('orders_status_history', $sql_data_array);

        $messageStack->add_session($this->_app->getDef('ms_success_doVoid'), 'success');
      } else {
        $messageStack->add_session($this->_app->getDef('ms_error_doVoid'), 'error');
      }
    }

    function refundTransaction($comments, $order) {
      global $messageStack;

      $OSCOM_Db = Registry::get('Db');

      if ( isset($_POST['ppRefund']) ) {
        $tids = array();

        $Qc = $OSCOM_Db->prepare('select comments from :table_orders_status_history where orders_id = :orders_id and orders_status_id = :orders_status_id and comments like "PayPal App: %" order by date_added desc');
        $Qc->bindInt(':orders_id', $order['orders_id']);
        $Qc->bindInt(':orders_status_id', OSCOM_APP_PAYPAL_TRANSACTIONS_ORDER_STATUS_ID);
        $Qc->execute();

        if ($Qc->fetch() !== false) {
          do {
            if ( strpos($Qc->value('comments'), 'PayPal App: Refund') !== false ) {
              preg_match('/Parent ID\: ([A-Za-z0-9]+)$/', $Qc->value('comments'), $ppr_matches);

              $tids[$ppr_matches[1]]['Refund'] = true;
            } elseif ( strpos($Qc->value('comments'), 'PayPal App: Capture') !== false ) {
              preg_match('/^PayPal App\: Capture \(([0-9\.]+)\).*Transaction ID\: ([A-Za-z0-9]+)/s', $Qc->value('comments'), $ppr_matches);

              $tids[$ppr_matches[2]]['Amount'] = $ppr_matches[1];
            }
          } while ($Qc->fetch());
        } elseif ( $comments['Payment Status'] == 'Completed' ) {
          $tids[$comments['Transaction ID']]['Amount'] = $this->_app->formatCurrencyRaw($order['total'], $order['currency'], $order['currency_value']);
        }

        $rids = array();

        foreach ( $_POST['ppRefund'] as $id ) {
          if ( isset($tids[$id]) && !isset($tids[$id]['Refund']) ) {
            $rids[] = $id;
          }
        }

        foreach ( $rids as $id ) {
          $pass = false;

          if ( !isset($comments['Gateway']) ) {
            $response = $this->_app->getApiResult('APP', 'RefundTransaction', array('TRANSACTIONID' => $id), (strpos($order['payment_method'], 'Sandbox') === false) ? 'live' : 'sandbox');

            if ( in_array($response['ACK'], array('Success', 'SuccessWithWarning')) ) {
              $transaction_id = $response['REFUNDTRANSACTIONID'];

              $pass = true;
            }
          } elseif ( $comments['Gateway'] == 'Payflow' ) {
            $response = $this->_app->getApiResult('APP', 'PayflowRefund', array('ORIGID' => $id), (strpos($order['payment_method'], 'Sandbox') === false) ? 'live' : 'sandbox');

            if ( isset($response['RESULT']) && ($response['RESULT'] == '0') ) {
              $transaction_id = $response['PNREF'];

              $pass = true;
            }
          }

          if ( $pass === true ) {
            $result = 'PayPal App: Refund (' . $tids[$id]['Amount'] . ')' . "\n" .
                      'Transaction ID: ' . HTML::sanitize($transaction_id) . "\n" .
                      'Parent ID: ' . HTML::sanitize($id);

            $sql_data_array = array('orders_id' => (int)$order['orders_id'],
                                    'orders_status_id' => OSCOM_APP_PAYPAL_TRANSACTIONS_ORDER_STATUS_ID,
                                    'date_added' => 'now()',
                                    'customer_notified' => '0',
                                    'comments' => $result);

            $OSCOM_Db->save('orders_status_history', $sql_data_array);

            $messageStack->add_session($this->_app->getDef('ms_success_refundTransaction', array('refund_amount' => $tids[$id]['Amount'])), 'success');
          } else {
            $messageStack->add_session($this->_app->getDef('ms_error_refundTransaction', array('refund_amount' => $tids[$id]['Amount'])), 'error');
          }
        }
      }
    }
  }
?>
