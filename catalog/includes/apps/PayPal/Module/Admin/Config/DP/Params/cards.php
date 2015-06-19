<?php
/**
  * osCommerce Online Merchant
  *
  * @copyright Copyright (c) 2015 osCommerce; http://www.oscommerce.com
  * @license GPL; http://www.oscommerce.com/gpllicense.txt
  */

namespace OSC\OM\Apps\PayPal\Module\Admin\Config\DP\Params;

class cards extends \OSC\OM\Apps\PayPal\Module\Admin\Config\ParamsAbstract
{
    public $default = 'visa;mastercard;discover;amex;maestro';
    public $sort_order = 200;

    protected $cards = [
        'visa' => 'Visa',
        'mastercard' => 'MasterCard',
        'discover' => 'Discover Card',
        'amex' => 'American Express',
        'maestro' => 'Maestro'
    ];

    protected function init()
    {
        $this->title = $this->app->getDef('cfg_dp_cards_title');
        $this->description = $this->app->getDef('cfg_dp_cards_desc');
    }

    public function getSetField()
    {
        $active = explode(';', OSCOM_APP_PAYPAL_DP_CARDS);

        $input = '';

        foreach ($this->cards as $key => $value) {
            $input .= '<input type="checkbox" id="cardsSelection' . ucfirst($key) . '" name="card_types[]" value="' . $key . '"' . (in_array($key, $active) ? ' checked="checked"' : '') . '><label for="cardsSelection' . ucfirst($key) . '">' . $value . '</label>';
        }

        $result = <<<EOT
<div>
  <p>
    <label>{$this->title}</label>

    {$this->description}
  </p>

  <div id="cardsSelection">
    {$input}
    <input type="hidden" name="cards" value="" />
  </div>
</div>

<script>
$(function() {
  $('#cardsSelection').buttonset();

  $('form[name="paypalConfigure"]').submit(function() {
    $('input[name="cards"]').val($('input[name="card_types[]"]:checked').map(function() {
      return this.value;
    }).get().join(';'));
  });
});
</script>
EOT;

        return $result;
    }
}
