# PayPal App for osCommerce Online Merchant v2.3.x

## Changelog

**v5.010 01-Mar-2017**

* Add new general App parameter to test and use the default server configured SSL version when performing API requests to PayPals servers or force TLS v1.2 connections. (TLS v1.2 connections are required from June 30, 2017)

* Use the customer address suburb value as the street2 parameter value for Payments Standard, Express Checkout, Direct Payment, and Hosted Solution.

* Payments Standard: If "receiver_email" is not returned back to the store, fallback to "business" to verify the transaction with.

* Payments Standard: Disable the module if the App API Credentials or module PDT configuration parameters have not been entered. Either is now required to be able to verify the transaction when the customer returns back to the store after payment is made.

* Payments Standard: Strip extra slashes that were being logged.

* Payments Standard: Remove deprecated NO_NOTE and PAGE_STYLE parameters. (deprecated Sept. 2016)

* Express Checkout: Remove deprecated ALLOWNOTE and PAGESTYLE parameters. (deprecated Sept. 2016)

* Update osCommerce links from http to https.

**v5.001 19-Feb-2017**

* Fix getIdentifier() usage in the PayPal Payments Standard module.

**v5.000 03-Nov-2016**

* Use PayPal API 204.

* Add Merchant Account ID field to Manage Credentials page.

* PayPal Express Checkout: Enable In-Context checkout flow by default.

* PayPal Express Checkout: Add configuration parameters to control Checkout with PayPal button color, shape, and size.

* Remove country restrictions for API Retrieval service.

* General improvements and bugfixes.

**v4.039 09-Dec-2014**

* Initial public release.

* Add Administration Dashboard module to show the live or sandbox account balance and to check if an online update exists.

* Payments Standard: When the customer returns back to the store after payment has been made, detect if the IPN has been received and skip over a check in checkout_process.php that verified if product quantities existed in stock. (If the IPN deducated the quantity already and stock reached 0, checkout_process.php would see this and redirect the customer to the shopping cart page)

* Fix compatibility conflict in the admin orders class.

* Show a success or failure message when order administration actions are performed.

* Show missing configuration requirements on module configuration page.

**v4.027 28-Nov-2014**

* Add support for Payments Standard Payment Data Transfer (PDT). If the Identity Token has not been configured, use the API Credentials to retrieve the transaction information.

* Add support for admin order transactions to Payments Standard and Hosted Solution.

* Redirect to the order status history tab after an admin order transaction has been performed.

* Bug: Fix order total and transaction total comparison mismatch notification stored in the order status comment.

**v4.016 08-Nov-2014**

* Minor compatibility updates for v2.2rc2a.

* Payments Standard: Add stock level management and order email notifications to IPN.

* Add compatibility with v2.3.5 shipping class get_first() class method. If class method is not found, revert to using cheapest() class method.

**v4.000 31-Oct-2014**

* Initial release
* Bundle PayPal modules into PayPal App
