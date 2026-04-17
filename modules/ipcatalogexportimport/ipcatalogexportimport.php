<?php
/**
 * 2007-2021 PrestaShop
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to theCommercial License!
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/afl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future. If you wish to customize PrestaShop for your
 * needs please refer to http://www.prestashop.com for more information.
 *
 *  @author    PrestaShop SA <contact@prestashop.com>
 *  @copyright 2007-2021 PrestaShop SA
 *  @license   http://opensource.org/licenses/afl-3.0.php Commercial License!
 *  International Registered Trademark & Property of PrestaShop SA
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

require_once dirname(__FILE__) . '/helpers/functions.php';
require_once dirname(__FILE__) . '/classes/EISellerApi.php';

class IpCatalogExportImport extends Module
{
    public function __construct()
    {
        $this->name = 'ipcatalogexportimport';
        $this->tab = 'migration_tools';
        $this->version = '6.3.5';
        $this->author = 'Smart Presta';
        $this->need_instance = 1;
        $this->module_key = '7378b445dbd56f8fbe91650fb68b7ed2';
        $this->id_product = 87189;

        /**
         * Set $this->bootstrap to true if your module is compliant with bootstrap (PrestaShop 1.6)
         */
        $this->bootstrap = true;

        parent::__construct();

        $this->displayName = $this->l('Products Catalog Export + Import + Update');
        $this->description = $this->l('With this module you can export, update and import your entire catalog, products, brands, suppliers, customers, addresses and etc.');

        $this->confirmUninstall = $this->l('Are you sure to uninstall this module?');

        $this->ps_versions_compliancy = array('min' => '1.6', 'max' => _PS_VERSION_);
    }

    /**
     * Don't forget to create update methods if needed:
     * http://doc.prestashop.com/display/PS16/Enabling+the+Auto-Update
     */
    public function install()
    {
        include(dirname(__FILE__) . '/sql/install.php');

        return parent::install() &&
            $this->installTab('AdminIpCatalogMigration', $this->l('Export') . ' & ' . $this->l('Import')) &&
                $this->installTab('AdminIpCatalogExport', $this->l('Export'), 'AdminIpCatalogMigration') &&
                $this->installTab('AdminIpCatalogImport', $this->l('Import'), 'AdminIpCatalogMigration') &&
                (version_compare(_PS_VERSION_, 8, '>=') ? $this->registerHook('displayBackOfficeHeader') : $this->registerHook('backOfficeHeader'));
    }

    public function uninstall()
    {
        include(dirname(__FILE__) . '/sql/uninstall.php');

        return parent::uninstall() && $this->uninstallTab();
    }

    public function installTab($className, $tabName, $tabParentName = null)
    {
        // Create new admin tab
        $tab = new Tab();
        if ($tabParentName) {
            $tab->id_parent = (int) Tab::getIdFromClassName($tabParentName);
        } else {
            $tab->id_parent = (int) Tab::getIdFromClassName('DEFAULT');
            Configuration::updateGlobalValue('PS_PRODUCT_SHORT_DESC_LIMIT', 1000000);
            Configuration::updateGlobalValue('PS_ALLOW_HTML_IFRAME', 1);
        }
        $tab->name = array();
        foreach (Language::getLanguages(false) as $lang) {
            $tab->name[$lang['id_lang']] = $tabName;
        }
        $tab->class_name = $className;
        $tab->module = $this->name;
        $tab->active = 1;

        if ($tab->add()) {
            if ($className == 'AdminIpCatalogMigration' && version_compare(_PS_VERSION_, '1.7', '>=')) {
                $this->updateTabIcon($tab->id);
            }
            return true;
        } else {
            return false;
        }
    }

    public function uninstallTab()
    {
        // Retrieve module tabs
        $moduleTabs = Tab::getCollectionFromModule($this->name);
        if (!empty($moduleTabs)) {
            foreach ($moduleTabs as $moduleTab) {
                // Delete it
                $moduleTab->delete();
            }
        }

        return true;
    }

    /**
     * Load the configuration form
     */
    public function getContent()
    {
        Tools::redirectAdmin($this->context->link->getAdminLink('AdminIpCatalogExport'));
        exit;
    }

    /**
     * Add the CSS & JavaScript files you want to be loaded in the BO.
     */
    public function hookDisplayBackOfficeHeader()
    {
        $this->backOfficeHeader();
    }
    
    
    public function hookBackOfficeHeader()
    {
        $this->backOfficeHeader();
    }
    
    public function backOfficeHeader()
    {
        // Menu icon in PS 1.6
        if (version_compare(_PS_VERSION_, '1.7', '<')) {
            $this->context->controller->addCSS($this->_path . 'views/css/menu_tab_icon.css');
        }
    }

    protected function updateTabIcon($id_tab)
    {
        return Db::getInstance()->execute('UPDATE ' . _DB_PREFIX_ . 'tab SET `icon` = "import_export"
            WHERE id_tab = ' . (int) $id_tab);
    }
    
    public function getRandomModulesForAd($count = 3)
    {
        $api = new EISellerApi();

        $options = array(
            'limit' => 100,
            'sort' => 'asc',
            'page' => 1
        );
        
        $lang_iso = $this->context->language->iso_code;
//        $lang_iso = 'en';

        $products = json_decode($api->getProducts($options), true);
//        ddd($products);
        $adModules = array();
        if (!empty($products['success']) && isset($products['products'])) {
            $products = $products['products'];
            shuffle($products);
            
            foreach ($products as $product) {
                if ($product['statut'] === 'certified') {
                    if ($product['id_product'] == $this->id_product) {
                        continue;
                    }
                    $product['url'] = "https://addons.prestashop.com/{$lang_iso}/product.php?id_product={$product['id_product']}";
                    $adModules[] = $product;
                }

                if (count($adModules) >= $count) {
                    break;
                }
            }
        }
        
        return $adModules;
    }
    
    public function convertToUsableColumns($autoColumns)
    {
        $newSelectedColumns = array();
        foreach ($autoColumns as $key => $col) {
            if ($col[1]) {
                $newSelectedColumns[$key] = $col[0];
            }
        }
        
        return  $newSelectedColumns;
    }

}
