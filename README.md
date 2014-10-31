# PayPal App for osCommerce Online Merchant v2.x

This is the base file-set of the PayPal App to be copied to an existing osCommerce Online Merchant v2.x installation.

The PayPal App is bundled in and optimized for osCommerce Online Merchant v2.3.5 onwards.

For earlier osCommerce Online Merchant installation versions, please review the Legacy Changes section below for changes that must be applied to your installation.

## Legacy Changes

1. Administration Tool Navigation Menu Box
2. Default Shipping Rate Selection
3. Administration Tool and Catalog Hooks
4. Administration Tool Order Administration Page

Only the changes in (1) and (2) are required to add the PayPal App links to the Administration Tool and to update the default shipping rate selection. Changes (3) and (4) are optional and are needed if transaction management is to be added to the Administration Tool Orders management page (this requires a new order management page which may overwrite installed Add-Ons).

_The versions described below are inclusive of the version stated (eg, v2.3.3.2 affects all versions up to and including v2.3.3.2)._

### 1. Administration Tool Navigation Menu Box

**osCommerce Online Merchant up to v2.3.3.2 (5-Sept-2013)**

In catalog/admin/includes/column_left.php, add the following to the collection of existing boxes being included:

    include(DIR_WS_BOXES . 'paypal.php');

**osCommerce Online Merchant up to v2.2 RC2a (30-Jan-2008)**

Copy (and overwrite) the following file from:

    docs/legacy/v22rc2a/admin/includes/boxes/paypal.php

to:

    catalog/admin/includes/boxes/paypal.php

### 2. Default Shipping Rate Selection

**osCommerce Online Merchant up to v2.3.4 (5-June-2014)**

Copy (and overwrite) the following file from:

    docs/legacy/v234/includes/classes/shipping.php

to:

    catalog/includes/classes/shipping.php

In the following file:

    catalog/checkout_shipping.php

replace the following (around line 156):

    if ( !tep_session_is_registered('shipping') || ( tep_session_is_registered('shipping') && ($shipping == false) && (tep_count_shipping_modules() > 1) ) ) $shipping = $shipping_modules->cheapest();

with:

    if ( !tep_session_is_registered('shipping') || ( tep_session_is_registered('shipping') && ($shipping == false) && (tep_count_shipping_modules() > 1) ) ) $shipping = $shipping_modules->get_first();

### 3. Administration Tool and Catalog Hooks

**osCommerce Online Merchant up to v2.3.4 (5-June-2014)**

Copy the following file from:

    docs/legacy/v234/includes/classes/hooks.php

to:

    catalog/includes/classes/hooks.php

In the following file:

    catalog/includes/application_top.php

add to the bottom (before ?>):

    require(DIR_FS_CATALOG . 'includes/classes/hooks.php');
    $OSCOM_Hooks = new hooks('shop');

In the following file:

    catalog/admin/includes/application_top.php

add to the bottom (before ?>):

    require(DIR_FS_CATALOG . 'includes/classes/hooks.php');
    $OSCOM_Hooks = new hooks('admin');

### 4. Administration Tool Order Administration Page

**osCommerce Online Merchant up to v2.3.4 (5-June-2014)**

Copy (and overwrite) the following file from:

    docs/legacy/v234/admin/includes/classes/order.php

to:

    catalog/admin/includes/classes/order.php

Copy (and overwrite) the following file from:

    docs/legacy/v234/admin/orders.php

to:

    catalog/admin/orders.php

In all catalog/admin/includes/languages/LANGUAGE/orders.php language files, add the following language definition:

    define('ENTRY_ADD_COMMENT', 'Add Comment:');

**osCommerce Online Merchant up to v2.2 RC2a (30-Jan-2008)**

**-- PENDING --**
