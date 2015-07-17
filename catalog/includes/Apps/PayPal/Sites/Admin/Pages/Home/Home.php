<?php
/**
  * osCommerce Online Merchant
  *
  * @copyright Copyright (c) 2015 osCommerce; http://www.oscommerce.com
  * @license GPL; http://www.oscommerce.com/gpllicense.txt
  */

namespace OSC\OM\Apps\PayPal\Sites\Admin\Pages\Home;

use OSC\OM\Apps;
use OSC\OM\OSCOM;
use OSC\OM\Registry;

use OSC\OM\Apps\PayPal\PayPal;

class Home extends \OSC\OM\PagesAbstract
{
    protected $db;
    public $data;

    protected function init()
    {
        $this->db = Registry::get('Db');

        $Qcheck = $this->db->query('show tables like ":table_oscom_app_paypal_log"');

        if ($Qcheck->fetch() === false) {
            $sql = <<<EOD
CREATE TABLE :table_oscom_app_paypal_log (
  id int unsigned NOT NULL auto_increment,
  customers_id int NOT NULL,
  module varchar(8) NOT NULL,
  action varchar(255) NOT NULL,
  result tinyint NOT NULL,
  server tinyint NOT NULL,
  request text NOT NULL,
  response text NOT NULL,
  ip_address int unsigned,
  date_added datetime,
  PRIMARY KEY (id),
  KEY idx_oapl_module (module)
) CHARACTER SET utf8 COLLATE utf8_unicode_ci;
EOD;

            $this->db->exec($sql);
        }

        $OSCOM_PayPal = new PayPal();
        Registry::set('PayPal', $OSCOM_PayPal);

        $OSCOM_PayPal->loadLanguageFile('admin.php');
        $OSCOM_PayPal->loadLanguageFile('admin/start.php');

        if ($OSCOM_PayPal->migrate()) {
            $admin_dashboard_modules = explode(';', MODULE_ADMIN_DASHBOARD_INSTALLED);

            foreach (Apps::getModules('adminDashboard', 'PayPal') as $k => $v) {
                if (!in_array($k, $admin_dashboard_modules)) {
                    $admin_dashboard_modules[] = $k;

                    $adm = new $v();
                    $adm->install();
                }
            }

            if (isset($adm)) {
                $this->db->save('configuration', [
                    'configuration_value' => implode(';', $admin_dashboard_modules)
                ], [
                    'configuration_key' => 'MODULE_ADMIN_DASHBOARD_INSTALLED'
                ]);
            }

            OSCOM::redirect('index.php', tep_get_all_get_params());
        }
    }

    public function getFile()
    {
        if (isset($this->file)) {
            return __DIR__ . '/templates/' . $this->file;
        }
    }
}
