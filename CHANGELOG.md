# PayPal App for osCommerce Online Merchant v2.x

## Changelog

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
