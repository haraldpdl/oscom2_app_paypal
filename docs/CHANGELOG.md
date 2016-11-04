# PayPal App for osCommerce Online Merchant v2.x

## Changelog

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
