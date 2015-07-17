<?php
/**
  * osCommerce Online Merchant
  *
  * @copyright Copyright (c) 2015 osCommerce; http://www.oscommerce.com
  * @license GPL; http://www.oscommerce.com/gpllicense.txt
  */
?>
  </div>
</div>

<script>
$(function() {
  if ( (OSCOM.APP.PAYPAL.action != 'Update') && (OSCOM.APP.PAYPAL.action != 'Info') ) {
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
  }
});
</script>

<?php
  include(DIR_FS_ADMIN . 'includes/template_bottom.php');
?>
