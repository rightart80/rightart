<?php
/**
 *
 * NOTICE OF LICENSE
 *
 *  @author    SmartPresta <tehran.alishov@gmail.com>
 *  @copyright 2023 SmartPresta
 *  @license   Commercial License
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

require_once dirname(__FILE__) . '/../../classes/EIHelper.php';
require_once dirname(__FILE__) . '/../../classes/EIDataProvider.php';
require_once(dirname(__FILE__) . '/../../classes/EIDataSaver.php');

class AdminIpCatalogExportController extends ModuleAdminController
{

    public function __construct()
    {
        // Enable bootstrap
        $this->bootstrap = true;

        // Call of the parent constructor method
        parent::__construct();

        // Save the module path in a variable
        $this->path = __PS_BASE_URI__ . 'modules/ipcatalogexportimport/';

        $this->exportDir = dirname(__FILE__) . '/../../export';
    }

    public function init()
    {
        error_reporting(E_ERROR | E_PARSE);
        ini_set('max_execution_time', 0);
//        ini_set('memory_limit', '-1');

        /*
         * If values have been submitted in the form, process.
         */
        if ((bool) Tools::isSubmit('ajax_action')) {
            $action = Tools::getValue('ajax_action');
            $type = Tools::getValue('type');
            if ($type === 'dml') {
                require_once dirname(__FILE__) . '/../../classes/EIDataSaver.php';
                $saver = new EIDataSaver($this->module);
                if (method_exists($saver, $action)) {
                    $saver->{$action}();
                }
            } else {
                if (method_exists('EIDataProvider', $action)) {
                    EIDataProvider::$action($this->module, 'ajax');
                }
            }
        } elseif (($entity = Tools::getValue('export'))) {
            $this->startExport($entity);
            exit;
        } else {
            $actions_list = array(
                'getFile' => 'getFile',
                'getDoc' => 'getDoc',
                'getSetting' => 'getSetting',
                'importSetting' => 'importSetting'
            );
            $action = Tools::getValue('action');
            if (isset($actions_list[$action])) {
                $this->{$actions_list[$action]}();
            }
        }

        parent::init();
    }

    public function initContent()
    {
        parent::initContent();

        $tree = new HelperTreeCategories(
            'product_categories_tree',
            $this->l('Filter by Category'),
            (int) Category::getRootCategory()->id,
            $this->context->language->id,
            false
        );
        $tree->setUseCheckBox(true)
            ->setUseSearch(true)
            ->setInputName('categories')
//            ->setFullTree(true)
            ->setUseShopRestriction(false);

        $tree2 = new HelperTreeCategories(
            'category_categories_tree',
            $this->l('Filter by Category'),
            (int) Category::getRootCategory()->id,
            $this->context->language->id,
            false
        );
        $tree2->setUseCheckBox(true)
            ->setUseSearch(true)
            ->setInputName('categories')
            ->setUseShopRestriction(false);
        
        $tree3 = new HelperTreeCategories(
            'combination_categories_tree',
            $this->l('Filter by Category'),
            (int) Category::getRootCategory()->id,
            $this->context->language->id,
            false
        );
        $tree3->setUseCheckBox(true)
            ->setUseSearch(true)
            ->setInputName('categories')
            ->setUseShopRestriction(false);
        
        $this->context->smarty->assign(array(
            'module_path' => $this->path,
            'ajax_url' => $this->context->link->getAdminLink('AdminIpCatalogExport'),
            'lang_iso_code' => $this->context->language->iso_code,
            'lang_id' => $this->context->language->id,
            'languages' => $this->context->controller->getLanguages(),
            'shop_feature' => Shop::isFeatureActive(),
            'shops' => Shop::getShops(),
            'context_shop' => $this->context->shop->id,
            'category_tree' => $tree->render(),
            'category_tree_2' => $tree2->render(),
            'category_tree_3' => $tree3->render(),
            'configs' => $this->getConfigs(),
            'entities' => EIHelper::allEntities($this->module),
            'initial_data' => EIDataProvider::getAllInitialData($this->module),
            'schedule_url' => $this->context->link->getModuleLink('ipcatalogexportimport', 'export', array('token' => md5(Configuration::getGlobalValue('IPEI_SECURE_KEY')))),
            'schedule_enabled' => Configuration::getGlobalValue('IPE_SCHDL_ENABLE'),
            'schedule_email_enabled' => Configuration::getGlobalValue('IPE_SCHDL_USE_EMAIL'),
            'schedule_ftp_enabled' => Configuration::getGlobalValue('IPE_SCHDL_USE_FTP'),
            'schedule_dont_send_empty' => Configuration::getGlobalValue('IPE_SCHDL_DNSEM'),
            'old_ps' => version_compare(_PS_VERSION_, '1.7', '<'),
            'id_type_redirected' => !empty(Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS("SHOW COLUMNS FROM `" . _DB_PREFIX_ . "product_shop` LIKE 'id_type_redirected'")),
            'store_table' => empty(Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS("SHOW TABLES LIKE '" . _DB_PREFIX_ . "store_lang'")) ? 'store' : 'store_lang',
            'adModules' => $this->module->getRandomModulesForAd(),
            'path_uri' => $this->path,
            'lang_iso' => $this->context->language->iso_code,
        ));
        $this->setTemplate('export.tpl');
    }

    public function setMedia($isNewTheme = false)
    {
        // We call the parent method
        parent::setMedia($isNewTheme);
        $this->bootstrap = true;
        $this->context->controller->addJquery();
        $this->context->controller->addJS($this->path . 'views/js/datatables.min.js');
        $this->context->controller->addJS($this->path . 'views/js/dataTables.checkboxes.min.js');
        $this->context->controller->addJQueryUI('ui.sortable');
        $this->context->controller->addCSS($this->path . 'views/css/datatables.min.css', 'all');
        $this->context->controller->addCSS($this->path . 'views/css/dataTables.checkboxes.css', 'all');
        
        // If the PS version supports including assets with any version, then include this way
        $ref = new ReflectionMethod($this->context->controller, 'addJs');
        if (count($ref->getParameters()) == 2) {
            $this->context->controller->addJS($this->path . 'views/js/back.js?v=' . $this->module->version, false);
            $this->context->controller->addJS($this->path . 'views/js/export.js?v=' . $this->module->version, false);
            $this->context->controller->addCSS($this->path . 'views/css/back.css?v=' . $this->module->version, 'all', null, false);
        } else {
            $this->context->controller->addJS($this->path . 'views/js/back.js');
            $this->context->controller->addJS($this->path . 'views/js/export.js');
            $this->context->controller->addCSS($this->path . 'views/css/back.css', 'all');
        }
        
        $this->context->controller->addJqueryPlugin('tagify');
    }

    private function cleanDir($str)
    {
        if (is_file($str)) {
            //Attempt to delete it.
            return unlink($str);
        } elseif (is_dir($str)) {
            //Get a list of the files in this directory.
            $scan = glob(rtrim($str, '/') . '/*');
            //Loop through the list of files.
            foreach ($scan as $path) {
                //Call our recursive function.
                $this->cleanDir($path);
            }
            //Remove the directory itself.
            if ($str !== $this->exportDir) {
                return @rmdir($str);
            } else {
                return 1;
            }
        }
    }

    public function startExport($entity)
    {
//        if (Tools::getValue('offset') == '0') {
//            $this->cleanDir($this->exportDir);
//        }
        
        try {
            $class = EIHelper::allEntities($this->module)[$entity]['class'];
            require_once dirname(__FILE__) . '/../../classes/export/' . $class . '.php';
            $export = new $class($this->module);
            $export->run();
            exit;
        } catch (Exception $e) {
            $errMsg = $e->getMessage();
            if (_PS_MODE_DEV_) {
                $errMsg .= ' --- ' . $e->getTraceAsString();
            }
            die($errMsg);
        }
    }

    public static function getConfigs()
    {
        $confs = DB::getInstance()->executeS('SELECT 
                                                    `id_ipcatalogexport`, 
                                                    `name`,
                                                    `configuration`,
                                                    "' . Context::getContext()->controller->l('Delete') . '" `title`
                                                FROM `' . _DB_PREFIX_ . 'ipcatalogexport`
                                                ORDER BY `id_ipcatalogexport` DESC;');
        foreach ($confs as &$conf) {
            $conf['configuration'] = str_replace("'", '&apos;', $conf['configuration']);
        }
        return $confs;
    }

    private function getFile()
    {
        $id = pSQL(Tools::getValue('id'));
        $type = pSQL(Tools::getValue('type'));
        $name = pSQL(Tools::getValue('name'));
        $filePath = realpath(dirname(__FILE__) . '/../../export/' . $id . '.' . $type);
        $size = filesize($filePath);

        if (file_exists($filePath)) {
            if ($type === 'xlsx') {
                header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
                header('Content-Disposition: attachment; filename="' . $name . '.xlsx"');
            } elseif ($type === 'csv') {
                header('Content-Type: text/csv');
                header('Content-Disposition: attachment; filename="' . $name . '.csv"');
            }
            header('Content-Length: ' . $size);

            header('Cache-Control: max-age=0');
            // If you're serving to IE 9, then the following may be needed
//            header('Cache-Control: max-age=1');
            // If you're serving to IE over SSL, then the following may be needed
            header('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
            header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT'); // always modified
            header('Cache-Control: cache, must-revalidate'); // HTTP/1.1

            header('Pragma: public'); // HTTP/1.0
            readfile($filePath);
//            echo file_get_contents($filePath);
//            unlink($filePath);
            $this->cleanDir($this->exportDir);
            die;
        } else {
            die($this->l("File doesn't exist."));
        }
    }

    private function getDoc()
    {
        $lang = pSQL(Tools::getValue('lang'));
        $file_path = realpath(dirname(__FILE__) . '/../../doc/readme_' . $lang . '.pdf');
        if (file_exists($file_path)) {
            header('Content-type:application/pdf');
            header('Content-Disposition:inline;filename="' . $this->module->displayName . '"');
            readfile($file_path);
            die;
        } else {
            die($this->l("File doesn't exist."));
        }
    }

    private function getSetting()
    {
        $dP = new EIDataProvider();

        $txt_arr = $dP->getExportSetting(Tools::getValue('st_id'));
        $serialized_txt = json_encode($txt_arr);

        if (count($txt_arr) > 1) {
            header('Content-Disposition: attachment; filename="all_catalog_export_settings.txt"');
        } else {
            header('Content-Disposition: attachment;' . 'filename=' . $txt_arr[0]['name'] . '.txt');
        }
        header('Content-Type: text/plain'); # Don't use application/force-download - it's not a real MIME type, and the Content-Disposition header is sufficient
        header('Content-Length: ' . strlen($serialized_txt));
        header('Connection: close');

        die($serialized_txt);
    }

    private function importSetting()
    {
        try {
            $files = $_FILES['file']['tmp_name'];
            if ($files) {
                foreach ($files as $key => $value) {
                    $file_name = $_FILES['file']['name'][$key];
                    $target = dirname(__FILE__) . '/../../export/' . $file_name;
                    move_uploaded_file($files[$key], $target);

                    $file = fopen($target, 'r');
                    $data = fread($file, filesize($target));
                    $unserialized_data = json_decode($data, true);
                    fclose($file);
                    @unlink($target);

                    $eC = new EIDataSaver($this->module);
                    if (!$eC->setExportSetting($unserialized_data)) {
                        throw new Exception($this->l('Could not import some settings.'));
                    }
                }
                die(json_encode(array(
                    'type' => 'success',
                    'message' => $this->l('File was succesfully imported.'),
                    'configs' => $this->getConfigs(),
                )));
            }
            throw new Exception($this->l('No file to import!'));

        } catch (Exception $e) {
            die(json_encode(array(
                'type' => 'error',
                'message' => $e->getMessage()
            )));
        }
    }


}
