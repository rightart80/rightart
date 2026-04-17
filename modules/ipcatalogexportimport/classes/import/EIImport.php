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

define('_IP_TMP_IMG_DIR_', _PS_MODULE_DIR_ . 'ipcatalogexportimport/img/');

require_once dirname(__FILE__) . '/EIAttribute.php';

use PhpOffice\PhpSpreadsheet\IOFactory;

class EIImport
{
    const DOWNLOAD_IMG_WITHOUT_CONTEXT = 1;
    const DOWNLOAD_IMG_WITH_CONTEXT = 2;

    public static $column_mask;
    public static $imageDownloadType;
    public $path;
    public $importDir;
    public $entity;
    public $file;
    public $fields;
    private $storeLangTableExists;
    public $available_fields = [];
    public $required_fields = [];
    public static $default_values = [];
    public static $validators = [
        'active' => ['EIImport', 'getBoolean'],
        'tax_rate' => ['EIImport', 'getPrice'],
        /* Tax excluded */
        'price_tex' => ['EIImport', 'getPrice'],
        /* Tax included */
        'price_tin' => ['EIImport', 'getPrice'],
        'reduction_price' => ['EIImport', 'getPrice'],
        'reduction_percent' => ['EIImport', 'getPrice'],
        'wholesale_price' => ['EIImport', 'getPrice'],
        'unit_price' => ['EIImport', 'getPrice'],
        'ecotax' => ['EIImport', 'getPrice'],
        'name' => ['EIImport', 'createMultiLangField'],
        'description' => ['EIImport', 'createMultiLangField'],
        'description_short' => ['EIImport', 'createMultiLangField'],
        'meta_title' => ['EIImport', 'createMultiLangField'],
        'meta_keywords' => ['EIImport', 'createMultiLangField'],
        'meta_description' => ['EIImport', 'createMultiLangField'],
        'link_rewrite' => ['EIImport', 'createMultiLangField'],
        'available_now' => ['EIImport', 'createMultiLangField'],
        'available_later' => ['EIImport', 'createMultiLangField'],
        'id_category' => ['EIImport', 'split'],
        'category' => ['EIImport', 'split'],
        'online_only' => ['EIImport', 'getBoolean'],
        'id_accessories' => ['EIImport', 'split'],
        'accessories' => ['EIImport', 'split'],
        'image_alt' => ['EIImport', 'split'],
        'delivery_in_stock' => ['EIImport', 'createMultiLangField'],
        'delivery_out_stock' => ['EIImport', 'createMultiLangField'],
    ];
    public $separator;
    public $enclosure;
    public $convert;
    public $row;
    public $multivalueSeparator;
    public $targetAction;
    public $nameExists;
    public $updateBy;
    public static $keepOldValue;

    public function __construct($that)
    {
        $this->module = $that->module;
        $this->context = Context::getContext();

        $this->importDir = dirname(__FILE__) . '/../../import/';

        $this->entity = $that->eIHelper->entity;
        $this->fields = $that->eIHelper->fields;
        $this->available_fields = $that->eIHelper->available_fields;
        $this->required_fields = $that->eIHelper->required_fields;

        switch ($this->entity) {
            case 'categories':
                self::$default_values = [
                    'active' => '1',
                    'parent' => Configuration::get('PS_HOME_CATEGORY'),
                    'link_rewrite' => '',
                    'date_upd' => date('Y-m-d H:i:s'),
                ];

                break;
            case 'products':
                self::$validators['image'] = ['EIImport', 'split'];

                self::$default_values = [
                    'id_category' => [(int) Configuration::get('PS_HOME_CATEGORY')],
                    'id_category_default' => null,
                    'active' => '1',
                    'width' => 0.000000,
                    'height' => 0.000000,
                    'depth' => 0.000000,
                    'weight' => 0.000000,
                    'visibility' => 'both',
                    'additional_shipping_cost' => 0.00,
                    'unit_price' => 0,
                    'quantity' => 0,
                    'minimal_quantity' => 1,
                    'low_stock_threshold' => null,
                    'low_stock_alert' => false,
                    'price' => 0,
                    'id_tax_rules_group' => 0,
                    'description_short' => [(int) Configuration::get('PS_LANG_DEFAULT') => ''],
                    'link_rewrite' => [(int) Configuration::get('PS_LANG_DEFAULT') => ''],
                    'online_only' => 0,
                    'condition' => 'new',
                    'available_date' => date('Y-m-d'),
                    'date_upd' => date('Y-m-d H:i:s'),
                    'customizable' => 0,
                    'uploadable_files' => 0,
                    'text_fields' => 0,
                    'advanced_stock_management' => 0,
                    'depends_on_stock' => 0,
                    'is_virtual' => 0,
                ];
                break;
            case 'combinations':
                self::$default_values = [
                    'reference' => '',
                    'supplier_reference' => '',
                    'ean13' => '',
                    'upc' => '',
                    'mpn' => '',
                    'wholesale_price' => 0,
                    'price' => 0,
                    'ecotax' => 0,
                    'quantity' => 0,
                    'minimal_quantity' => 1,
                    'low_stock_threshold' => null,
                    'low_stock_alert' => false,
                    'weight' => 0,
                    'default_on' => null,
                    'advanced_stock_management' => 0,
                    'depends_on_stock' => 0,
                    'available_date' => date('Y-m-d'),
                ];

                break;
            case 'discounts':
                break;
            case 'carriers':
                // Overwrite validators AS name is not MultiLangField
                self::$validators = ['delay' => [self::class, 'createMultiLangField']];

                self::$default_values = [
                    'active' => '1',
                ];

                break;
            case 'brands':
                // Overwrite validators AS name is not MultiLangField
                self::$validators = [
                    'description' => [self::class, 'createMultiLangField'],
                    'short_description' => [self::class, 'createMultiLangField'],
                    'meta_title' => [self::class, 'createMultiLangField'],
                    'meta_keywords' => [self::class, 'createMultiLangField'],
                    'meta_description' => [self::class, 'createMultiLangField'],
                ];

                self::$default_values = array(
                    'shop' => Shop::getGroupFromShop(Configuration::get('PS_SHOP_DEFAULT')),
                    'date_upd' => date('Y-m-d H:i:s'),
                );
                break;
            case 'suppliers':
                // Overwrite validators AS name is not MultiLangField
                self::$validators = [
                    'description' => [self::class, 'createMultiLangField'],
                    'short_description' => [self::class, 'createMultiLangField'],
                    'meta_title' => [self::class, 'createMultiLangField'],
                    'meta_keywords' => [self::class, 'createMultiLangField'],
                    'meta_description' => [self::class, 'createMultiLangField'],
                ];

                self::$default_values = array(
                    'shop' => Shop::getGroupFromShop(Configuration::get('PS_SHOP_DEFAULT')),
                    'date_upd' => date('Y-m-d H:i:s'),
                );
                break;
            case 'customers':
                self::$default_values = [
                    'date_upd' => date('Y-m-d H:i:s'),
                ];

                break;
            case 'attributes':
                self::$validators['public_name'] = ['EIImport', 'createMultiLangField'];

                break;
            case 'warehouses':
                unset(self::$validators['name']);

                break;
            case 'groups':
                self::$default_values = [
                    'shop' => Shop::getGroupFromShop(Configuration::get('PS_SHOP_DEFAULT')),
                    'date_upd' => date('Y-m-d H:i:s'),
                ];
                break;
            case 'addresses':
                self::$default_values = [
                    'alias' => 'Alias',
                    'postcode' => 'X',
                    'date_upd' => date('Y-m-d H:i:s'),
                ];

                break;
            case 'aliases':
                self::$default_values = [
                    'active' => '1',
                ];

                break;
            case 'stores':
                $this->storeLangTableExists = !empty(Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS("SHOW TABLES LIKE '" . _DB_PREFIX_ . "store_lang'"));
                if ($this->storeLangTableExists) {
                    self::$validators['address1'] = [self::class, 'createMultiLangField'];
                    self::$validators['address2'] = [self::class, 'createMultiLangField'];
                    self::$validators['name'] = [self::class, 'createMultiLangField'];
                    self::$validators['note'] = [self::class, 'createMultiLangField'];
                } else {
                    unset(self::$validators['name']);
                }

                self::$default_values = [
                    'active' => '1',
                    'date_upd' => date('Y-m-d H:i:s'),
                ];

                break;
        }
    }

    public function import()
    {
        // Caused session already started error. Maybe we may need to reactivate it.
//        if (session_status() === PHP_SESSION_NONE) {
//            session_start();
//        }
        
        self::$imageDownloadType = self::DOWNLOAD_IMG_WITHOUT_CONTEXT; // DOWNLOAD_IMG_WITH_CONTEXT

        $filename = Tools::getValue('filename');
        $this->file = $this->importDir . $this->entity . '/' . $filename;

        if (!file_exists($this->file)) {
            die(json_encode(['errors' => [sprintf($this->module->l('File "%s" does not exist!', 'EIImport'), $this->file)]]));
        }

        $this->separator = Tools::getValue('csv_separator');
        $this->enclosure = '"';
        $this->multivalueSeparator = Tools::getValue('multivalue_separator') ?: ',';
        if ($this->separator === 't') {
            $this->separator = "\t";
        }

        //        if ($this->enclosure === 'none') {
        //            $this->enclosure = '';
        //        } elseif ($this->enclosure === 'quot') {
        //            $this->enclosure = '"';
        //        }

        if (in_array(Tools::strtolower(pathinfo($filename, PATHINFO_EXTENSION)), ['xls', 'xlsx', 'ods'])) {
            if (file_exists($newFile = $this->importDir . 'tmp/' . pathinfo($filename, PATHINFO_FILENAME) . '.csv')) {
                $this->file = $newFile;
            } elseif (file_exists($this->file)) {
                require_once dirname(__FILE__) . '/../../vendor/autoload.php';
                $spreadsheet = IOFactory::load($this->file);
                $writer = IOFactory::createWriter($spreadsheet, 'Csv');
                $writer->setDelimiter($this->separator);
                $writer->setEnclosure($this->enclosure);
                $writer->save($this->file = $newFile);
            }
        }

        $this->shops = [];
        if (Shop::isFeatureActive() && ($shops = Tools::getValue('shops'))) {
            foreach ($shops as $shop => $true) {
                if ($true) {
                    $this->shops[] = $shop;
                }
            }
        }
        if (empty($this->shops)) {
            $this->shops[] = (int) $this->context->shop->id;
        }

        $this->convert = (int) Tools::getValue('convert');

        $offset = (int) Tools::getValue('offset');
        $limit = (int) Tools::getValue('limit');
        $validateOnly = ((int) Tools::getValue('validateOnly') == 1);
        $moreStep = (int) Tools::getValue('moreStep');

        if (!$offset && !$moreStep) {
            $_SESSION['ip_import'] = [
                'accessories' => [],
                'redirects' => []
            ];
        }

        $results = [];
        $this->importByGroups($offset, $limit, $results, $validateOnly, $moreStep);

        // Retrieve errors/warnings if any
        if (isset($this->errors) && count($this->errors) > 0) {
            $results['errors'] = $this->errors;
        }
        if (isset($this->warnings) && count($this->warnings) > 0) {
            $results['warnings'] = $this->warnings;
        }
        if (isset($this->informations) && count($this->informations) > 0) {
            $results['informations'] = $this->informations;
        }

        if (!$validateOnly && (bool) $results['isFinished'] && !isset($results['oneMoreStep'])) {
            if (isset($_SESSION['ip_import'])) {
                unset($_SESSION['ip_import']);
            }
            if ((bool) Tools::getValue('send_email')) {
                // Mail::Send() can sometimes throw an error...
                try {
                    // Mail send in last step because in case of failure, does NOT throw an error.
                    $mail_iso = $this->context->language->iso_code;
                    if (!file_exists(dirname(__FILE__) . '/../../mails/' . $mail_iso . '/import.txt') ||
                            !file_exists(dirname(__FILE__) . '/../../mails/' . $mail_iso . '/import.html')
                    ) {
                        $this->copyDir(dirname(__FILE__) . '/../../mails/en', dirname(__FILE__) . '/../../mails/' . $mail_iso);
                    }
                    $mail_dir = dirname(__FILE__) . '/../../mails/';

                    if ($this->context->employee) {
                        $templateVars = [
                            '{firstname}' => $this->context->employee->firstname,
                            '{lastname}' => $this->context->employee->lastname,
                            '{filename}' => Tools::getValue('filename'),
                        ];
                        $conf = [
                            'id_lang' => (int) $this->context->employee->id_lang,
                            'email' => $this->context->employee->email,
                            'name' => $this->context->employee->firstname . ' ' . $this->context->employee->lastname,
                        ];
                    } else {
                        $employee = new Employee((int) Tools::getValue('id_employee'));
                        $templateVars = [
                            '{firstname}' => $employee->firstname,
                            '{lastname}' => $employee->lastname,
                            '{filename}' => Tools::getValue('filename'),
                        ];
                        $conf = [
                            'id_lang' => (int) $employee->id_lang,
                            'email' => $employee->email,
                            'name' => $employee->firstname . ' ' . $employee->lastname,
                        ];
                    }

                    $mailSuccess = @Mail::Send(
                                    $conf['id_lang'],
                                    'import',
                                    $this->module->l('Import complete', 'EIImport'),
                                    $templateVars,
                                    $conf['email'],
                                    $conf['name'],
                                    null,
                                    null,
                                    null,
                                    null,
                                    $mail_dir,
                                    false, // do not die in failed! Warn only, it's not an import error, because import finished in fact.
                                    (int) $this->context->shop->id
                    );
                    if (!$mailSuccess) {
                        $results['warnings'][] = $this->module->l('The confirmation email couldn\'t be sent, but the import is successful. Yay!', 'EIImport');
                    }
                } catch (\Exception $e) {
                    $results['warnings'][] = $this->module->l('The confirmation email couldn\'t be sent, but the import is successful. Yay!', 'EIImport');
                }
            }
        }

        die(json_encode($results));
    }

    public function importByGroups($offset = false, $limit = false, &$results = null, $validateOnly = false, $moreStep = 0)
    {
        // Check if the file exist
        if (Tools::getValue('filename')) {
            $this->targetAction = Tools::getValue('target_action');
            $this->nameExists = Tools::getValue('name_exists');
            $this->updateBy = Tools::getValue('update_by');
            // It must be static, because we access it in the static method "fillInfo"
            self::$keepOldValue = Tools::getValue('keep_old_value');
            $shop_is_feature_active = Shop::isFeatureActive();
            // If i am a superadmin, i can truncate table (ONLY IF OFFSET == 0 or false and NOT FOR VALIDATION MODE!)
            if (!$offset && !$moreStep && !$validateOnly && (($shop_is_feature_active && !empty($this->context->employee) && $this->context->employee->isSuperAdmin()) || !$shop_is_feature_active) && $this->targetAction != 'update' && Tools::getValue('truncate')) {
                $this->truncateTables(Tools::getValue('entity'));
            }
            $import_type = false;
            $doneCount = 0;
            $moreStepLabels = [];
            // Sometime, import will use registers to memorize data across all elements to import (for trees, or else).
            // Since import is splitted in multiple ajax calls, we must keep these data across all steps of the full import.
            $crossStepsVariables = [];
            if (($crossStepsVars = Tools::getValue('crossStepsVars'))) {
                $crossStepsVars = json_decode($crossStepsVars, true);
                if (count($crossStepsVars) > 0) {
                    $crossStepsVariables = $crossStepsVars;
                }
            }
            Db::getInstance()->disableCache();
            $clearCache = false;
            switch (Tools::getValue('entity')) {
                case 'categories':
                    $doneCount += $this->categoryImport($offset, $limit, $crossStepsVariables, $validateOnly);
                    if ($doneCount < $limit && !$validateOnly) {
                        /* Import has finished, we can regenerate the categories nested tree */
                        Category::regenerateEntireNtree();
                        // Recalculate the depth level
                        (new Category)->recalculateLevelDepth(2);
                    }
                    $clearCache = true;

                    break;
                case 'products':
                    if (!defined('PS_MASS_PRODUCT_CREATION')) {
                        define('PS_MASS_PRODUCT_CREATION', true);
                    }
                    $moreStepLabels = [$this->module->l('Linking related products...', 'EIImport')];
                    $doneCount += $this->productImport($offset, $limit, $crossStepsVariables, $validateOnly, $moreStep);
                    $clearCache = true;

                    break;
                case 'features':
                    $doneCount += $this->featureImport($offset, $limit, $validateOnly);
                    $clearCache = true;

                    break;
                case 'attributes':
                    $doneCount += $this->attributeImport($offset, $limit, $validateOnly);
                    $clearCache = true;

                    break;
                case 'combinations':
                    $doneCount += $this->combinationImport($offset, $limit, $crossStepsVariables, $validateOnly);
                    $clearCache = true;

                    break;
                case 'packs':
                    $doneCount += $this->packImport($offset, $limit, $crossStepsVariables, $validateOnly);
                    $clearCache = true;

                    break;
                case 'discounts':
                    $doneCount += $this->discountImport($offset, $limit, $validateOnly);
                    $clearCache = true;

                    break;
                case 'customers':
                    $doneCount += $this->customerImport($offset, $limit, $validateOnly);

                    break;
                case 'groups':
                    $doneCount += $this->groupImport($offset, $limit, $validateOnly);

                    break;
                case 'addresses':
                    $doneCount += $this->addressImport($offset, $limit, $validateOnly);

                    break;
                case 'brands':
                    $doneCount += $this->manufacturerImport($offset, $limit, $validateOnly);
                    $clearCache = true;

                    break;
                case 'suppliers':
                    $doneCount += $this->supplierImport($offset, $limit, $validateOnly);
                    $clearCache = true;

                    break;
                case 'carriers':
                    $doneCount += $this->carrierImport($offset, $limit, $validateOnly);
                    $clearCache = true;

                    break;
                case 'warehouses':
                    $doneCount += $this->warehouseImport($offset, $limit, $validateOnly);
                    $clearCache = true;

                    break;
                case 'aliases':
                    $doneCount += $this->aliasImport($offset, $limit, $validateOnly);

                    break;
                case 'stores':
                    $doneCount += $this->storeContactImport($offset, $limit, $validateOnly);
                    $clearCache = true;

                    break;
            }

            // @since 1.5.0
            if (Configuration::get('PS_ADVANCED_STOCK_MANAGEMENT')) {
                switch (Tools::getValue('entity')) {
                    case 'supply_orders':
                        $doneCount += $this->supplyOrdersImport($offset, $limit, $validateOnly);

                        break;
                    case 'supply_order_details':
                        $doneCount += $this->supplyOrdersDetailsImport($offset, $limit, $crossStepsVariables, $validateOnly);

                        break;
                }
            }

            if ($results !== null) {
                $results['isFinished'] = ($doneCount < $limit);
                if ($results['isFinished'] && $clearCache && !$validateOnly) {
                    $this->clearSmartyCache();
                }
                $results['doneCount'] = $offset + $doneCount;
                if ($offset === 0) {
                    // compute total count only once, because it takes time
                    $handle = $this->openCsvFile(0);
                    if ($handle) {
                        $count = 0;
                        while (fgetcsv($handle, MAX_LINE_SIZE, $this->separator)) {
                            $count++;
                        }
                        $results['totalCount'] = $count;
                    }
                    $this->closeCsvFile($handle);
                }
                if (!$results['isFinished'] || (!$validateOnly && ($moreStep < count($moreStepLabels)))) {
                    // Since we'll have to POST this array from ajax for the next call, we should care about it size.
                    $nextPostSize = mb_strlen(json_encode($crossStepsVariables));
                    $results['crossStepsVariables'] = $crossStepsVariables;
                    $results['nextPostSize'] = $nextPostSize + (1024 * 64); // 64KB more for the rest of the POST query.
                    $results['postSizeLimit'] = Tools::getMaxUploadSize();
                }
                if ($results['isFinished'] && !$validateOnly && ($moreStep < count($moreStepLabels))) {
                    $results['oneMoreStep'] = $moreStep + 1;
                    $results['moreStepLabel'] = $moreStepLabels[$moreStep];
                }
            }

            if ($import_type !== false) {
                $log_message = sprintf($this->module->l('%s import', 'EIImport'), $import_type);
                if ($offset !== false && $limit !== false) {
                    $log_message .= ' ' . sprintf($this->module->l('(from %s to %s)', 'EIImport'), $offset, $limit);
                }
                if (Tools::getValue('truncate')) {
                    $log_message .= ' ' . $this->module->l('with truncate', 'EIImport');
                }
                PrestaShopLogger::addLog($log_message, 1, null, $import_type, null, true, (int) $this->context->employee->id);
            }

            Db::getInstance()->enableCache();
        } else {
            $this->errors[] = $this->module->l('To proceed, please upload a file first.', 'EIImport');
        }
    }

    public function clearSmartyCache()
    {
        Tools::enableCache();
        Tools::clearCache($this->context->smarty);
        Tools::restoreCacheSettings();
    }

    protected function getCategoryByName($id_lang, $name)
    {
        return Db::getInstance()->getRow('
			SELECT c.*, cl.*
			FROM `' . _DB_PREFIX_ . 'category` c
			LEFT JOIN `' . _DB_PREFIX_ . 'category_lang` cl ON c.`id_category` = cl.`id_category` 
                            AND `id_lang` = ' . (int) $id_lang . Shop::addSqlRestrictionOnLang('cl') . '
			WHERE `name` = "' . pSQL($name) . '"');
    }

    protected function getAttributeGroupByName($id_lang, $name)
    {
        return Db::getInstance()->getRow('
			SELECT ag.*, agl.*
			FROM `' . _DB_PREFIX_ . 'attribute_group` ag
			# JOIN `' . _DB_PREFIX_ . 'attribute_group_shop` ags ON ag.`id_attribute_group` = ags.`id_attribute_group` ' . Shop::addSqlRestrictionOnLang('ags') . ' 
                        LEFT JOIN `' . _DB_PREFIX_ . 'attribute_group_lang` agl ON ag.`id_attribute_group` = agl.`id_attribute_group` AND `id_lang` = ' . (int) $id_lang . '
			WHERE agl.`name` = "' . pSQL($name) . '"');
    }

    protected function getAttributeByName($id_lang, $id_attribute_group, $name)
    {
        return Db::getInstance()->getRow('
			SELECT a.*, al.*
			FROM `' . _DB_PREFIX_ . 'attribute` a
                        # JOIN `' . _DB_PREFIX_ . 'attribute_shop` ash ON a.`id_attribute` = ash.`id_attribute` ' . Shop::addSqlRestrictionOnLang('ash') . '
                        LEFT JOIN `' . _DB_PREFIX_ . 'attribute_lang` al ON a.`id_attribute` = al.`id_attribute` AND `id_lang` = ' . (int) $id_lang . '
			WHERE a.id_attribute_group = ' . (int) $id_attribute_group . ' AND al.`name` = "' . pSQL($name) . '"');
    }

    protected function getFeatureByName($id_lang, $name)
    {
        return Db::getInstance()->getRow('
			SELECT f.*, fl.*
			FROM `' . _DB_PREFIX_ . 'feature` f
			# JOIN `' . _DB_PREFIX_ . 'feature_shop` fs ON f.`id_feature` = fs.`id_feature` ' . Shop::addSqlRestrictionOnLang('fs') . '
                        LEFT JOIN `' . _DB_PREFIX_ . 'feature_lang` fl ON f.`id_feature` = fl.`id_feature` AND `id_lang` = ' . (int) $id_lang . '
			WHERE fl.`name` = "' . pSQL($name) . '"');
    }

    protected function getFeatureValueByName($id_lang, $id_feature, $value)
    {
        return Db::getInstance()->getRow('
			SELECT fv.*, fvl.*
			FROM `' . _DB_PREFIX_ . 'feature_value` fv
                        LEFT JOIN `' . _DB_PREFIX_ . 'feature_value_lang` fvl ON fv.`id_feature_value` = fvl.`id_feature_value` AND `id_lang` = ' . (int) $id_lang . '
			WHERE fv.`id_feature` = ' . (int) $id_feature . ' AND fvl.`value` = "' . pSQL($value) . '"');
    }

    public function productImport($offset = false, $limit = false, &$crossStepsVariables = false, $validateOnly = false, $moreStep = 0)
    {
        $id_lang = Tools::getValue('language');
        $this->force_id = $force_id = Tools::getValue('keep_id');
        $this->id_type_redirected = !empty(Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS("SHOW COLUMNS FROM `" . _DB_PREFIX_ . "product_shop` LIKE 'id_type_redirected'"));

        if ($moreStep == 1) {
            return $this->productImportAccessories($offset, $limit, $id_lang);
        }

        // Set context shop to avoid product import conflict
        Shop::setContext(Shop::CONTEXT_SHOP, $this->context->shop->id);

        $this->receiveColumns();
        $handle = $this->openCsvFile($offset);
        if (!$handle) {
            return false;
        }

        $default_language_id = (int) Configuration::get('PS_LANG_DEFAULT');
        if (!Validate::isUnsignedId($id_lang)) {
            $id_lang = $default_language_id;
        }
        self::setLocale();
        $shop_ids = Shop::getCompleteListOfShopsID();

        $match_ref = Tools::getValue('match_ref');
        if (Tools::getValue('disable_hooks')) {
            define('PS_INSTALLATION_IN_PROGRESS', true);
        }
        $regenerate = Tools::getValue('regenerate');
        $shop_is_feature_active = Shop::isFeatureActive();
        if (!$validateOnly) {
            Module::setBatchMode(true);
        }

        $accessories = [];
        if ($crossStepsVariables !== false && array_key_exists('accessories', $crossStepsVariables)) {
            $accessories = $crossStepsVariables['accessories'];
        }

        $line_count = 0;
        for ($current_line = 0; ($line = fgetcsv($handle, MAX_LINE_SIZE, $this->separator, $this->enclosure)) && (!$limit || $current_line < $limit); $current_line++) {
            $line_count++;
            $this->row = $offset + $line_count + 1;
            if ($this->convert) {
                $line = $this->utf8EncodeArray($line);
            }

            $isEmpty = true;
            foreach ($line as $ln) {
                if ($ln) {
                    $isEmpty = false;
                    break;
                }
            }

            if ($isEmpty) {
                $this->warnings[] = $this->module->l('There is an empty row in the file that won\'t be imported.', 'EIImport');

                continue;
            }

            $info = self::getMaskedRow($line);

            // Set product tax rules group
            if (!empty($info['id_tax_rules_group']) && !is_numeric($info['id_tax_rules_group'])) {
                $tax_rules = Db::getInstance()->executeS(
                        'SELECT `id_tax_rules_group`, `active`
                    FROM `' . _DB_PREFIX_ . 'tax_rules_group` rg
                    WHERE `name` = \'' . pSQL(trim($info['id_tax_rules_group'])) . '\' AND `deleted` = 0'
                );
                if (count($tax_rules) == 1) {
                    $info['id_tax_rules_group'] = $tax_rules[0]['id_tax_rules_group'];
                } elseif (count($tax_rules) > 1) {
                    $found = false;
                    foreach ($tax_rules as $tax_rule) {
                        if ($tax_rule['active'] == '1') {
                            $info['id_tax_rules_group'] = $tax_rule['id_tax_rules_group'];
                            $found = true;
                            break;
                        }
                    }
                    if (!$found) {
                        $info['id_tax_rules_group'] = $tax_rules[0]['id_tax_rules_group'];
                    }
                } else {
                    $info['id_tax_rules_group'] = 0;
                }
            }

            $this->productImportOne(
                    $info,
                    $default_language_id,
                    $id_lang,
                    $force_id,
                    $regenerate,
                    $shop_is_feature_active,
                    $shop_ids,
                    $match_ref,
                    $accessories, // by ref
                    $validateOnly
            );
        }
        $this->closeCsvFile($handle);
        if (!$validateOnly) {
            Module::processDeferedFuncCall();
            Module::processDeferedClearCache();
            Tag::updateTagCount();
        }

        if ($crossStepsVariables !== false) {
            $crossStepsVariables['accessories'] = $accessories;
        }

        return $line_count;
    }

    private function getProductIdByName($name, $id_lang, $id = null)
    {
        if ($this->force_id && (int) $id && ObjectModel::existsInDatabase((int) $id, 'product') && $this->targetAction != 'update') {
            return $id;
        }

        $data = Db::getInstance()->executeS('
                            SELECT p.*
                            FROM `' . _DB_PREFIX_ . 'product` p
                            LEFT JOIN `' . _DB_PREFIX_ . 'product_lang` pl
                                ON (p.`id_product` = pl.`id_product`
                                AND `id_lang` = ' . (int) $id_lang . Shop::addSqlRestrictionOnLang('pl') . ')
                            WHERE `name` = \'' . pSQL($name) . '\'');
        if ($data) {
            if ($id) {
                foreach ($data as $d) {
                    if ($d['id_product'] == $id) {
                        return (int) $id;
                    }
                }
                return (int) $data[0]['id_product'];
            } else {
                return (int) $data[0]['id_product'];
            }
        }
        return false;
    }

    private function getCategoryIdByName($name, $id_lang, $id = null)
    {
        if ($this->force_id && (int) $id && ObjectModel::existsInDatabase((int) $id, 'category') && $this->targetAction != 'update') {
            return $id;
        }

        $data = Db::getInstance()->executeS('
                            SELECT c.*
                            FROM `' . _DB_PREFIX_ . 'category` c
                            LEFT JOIN `' . _DB_PREFIX_ . 'category_lang` cl
                                ON (c.`id_category` = cl.`id_category`
                                AND `id_lang` = ' . (int) $id_lang . Shop::addSqlRestrictionOnLang('cl') . ')
                            WHERE `name` = \'' . pSQL($name) . '\'');
        if ($data) {
            if ($id) {
                foreach ($data as $d) {
                    if ($d['id_category'] == $id) {
                        return (int) $id;
                    }
                }
                return (int) $data[0]['id_category'];
            } else {
                return (int) $data[0]['id_category'];
            }
        }
        return false;
    }

    protected function productImportAccessories2($offset, $limit, $id_lang, &$crossStepsVariables)
    {
        if ($crossStepsVariables === false || !array_key_exists('accessories', $crossStepsVariables)) {
            return 0;
        }

        $accessories = $crossStepsVariables['accessories'];

        if ($offset == 0) {
            //             self::setLocale();
            Module::setBatchMode(true);
        }

        $line_count = 0;
        $i = 0;
        foreach ($accessories as $product_id => $links) {
            // skip elements until reaches offset
            if ($i < $offset) {
                $i++;

                continue;
            }

//            if (count($links) > 0) { // We delete and relink only if there is accessories to link...
            // Bulk jobs: for performances, we need to do a minimum amount of SQL queries. No product inflation.
            $unique_ids = self::getExistingIdsFromIdsOrNames($links, $id_lang);
            Db::getInstance()->delete('accessory', 'id_product_1 = ' . (int) $product_id);
            self::changeAccessoriesForProduct($unique_ids, $product_id);
//            }
            $line_count++;

            // Empty value to reduce array weight (that goes through HTTP requests each time) but do not unset array entry!
            $accessories[$product_id] = 0; // In JSON, 0 is lighter than null or false
            // stop when limit reached
            if ($line_count >= $limit) {
                break;
            }
        }

        if ($line_count < $limit) { // last pass only
            Module::processDeferedFuncCall();
            Module::processDeferedClearCache();
        }

        $crossStepsVariables['accessories'] = $accessories;

        return $line_count;
    }

    protected function productImportAccessories($offset, $limit, $id_lang)
    {
        if ($offset == 0) {
            //             self::setLocale();
            Module::setBatchMode(true);
        }

        $line_count = 0;
        $i = 0;
        if (!empty($_SESSION['ip_import']['accessories'])) {
            foreach ($_SESSION['ip_import']['accessories'] as $product_id => $links) {
                // skip elements until reaches offset
                if ($i < $offset) {
                    $i++;

                    continue;
                }

//            if (count($links) > 0) { // We delete and relink only if there is accessories to link...
                // Bulk jobs: for performances, we need to do a minimum amount of SQL queries. No product inflation.
                $unique_ids = $this->getExistingIdsFromIdsOrNames2($links, $id_lang);
                Db::getInstance()->delete('accessory', 'id_product_1 = ' . (int) $product_id);
                self::changeAccessoriesForProduct($unique_ids, $product_id);
//            }
                $line_count++;

                // stop when limit reached
                if ($line_count >= $limit) {
                    break;
                }
            }
        }

        // Also update the redirects
        $line_count2 = 0;
        $i = 0;
        if (!empty($_SESSION['ip_import']['redirects'])) {
            foreach ($_SESSION['ip_import']['redirects'] as $product_id => $redirect) {
                // skip elements until reaches offset
                if ($i < $offset) {
                    $i++;

                    continue;
                }

                $id = null;
                if (isset($redirect['type'])) {
                    if (in_array($redirect['type'], ['301-category', '302-category'])) {
                        if (isset($redirect['name'])) {
                            $id = $this->getCategoryIdByName($redirect['name'], $id_lang, isset($redirect['id']) ? $redirect['id'] : null);
                        } elseif (isset($redirect['id'])) {
                            $id = (int) $redirect['id'];
                        }
                        
                        if ($this->id_type_redirected) {
                            Db::getInstance()->update(
                                    'product',
                                    array(
                                        'redirect_type' => $redirect['type'],
                                        'id_type_redirected' => $id,
                                    ),
                                    'id_product = ' . (int) $product_id
                            );
                            Db::getInstance()->update(
                                    'product_shop',
                                    array(
                                        'redirect_type' => $redirect['type'],
                                        'id_type_redirected' => $id,
                                    ),
                                    'id_product = ' . (int) $product_id . ' AND id_shop IN (' . implode(',', $this->shops) . ')'
                            );
                        }
                    } elseif (in_array($redirect['type'], ['301', '301-product', '302', '302-product'])) {
                        if (isset($redirect['name'])) {
                            $id = $this->getProductIdByName($redirect['name'], $id_lang, isset($redirect['id']) ? $redirect['id'] : null);
                        } elseif (isset($redirect['id'])) {
                            $id = (int) $redirect['id'];
                        }
                        
                        if ($this->id_type_redirected) {
                            $data = ['redirect_type' => in_array($redirect['type'], ['', '301', '301-product']) ? '301-product' : '302-product'];
                            if ($id) {
                                $data['id_type_redirected'] = $id;
                            }
                            Db::getInstance()->update(
                                    'product',
                                    $data,
                                    'id_product = ' . (int) $product_id
                            );
                            Db::getInstance()->update(
                                    'product_shop',
                                    $data,
                                    'id_product = ' . (int) $product_id . ' AND id_shop IN (' . implode(',', $this->shops) . ')'
                            );
                        } else {
                            $data = ['redirect_type' => in_array($redirect['type'], ['', '301', '301-product']) ? '301' : '302'];
                            if ($id) {
                                $data['id_product_redirected'] = $id;
                            }
                            Db::getInstance()->update(
                                    'product',
                                    $data,
                                    'id_product = ' . (int) $product_id
                            );
                            Db::getInstance()->update(
                                    'product_shop',
                                    $data,
                                    'id_product = ' . (int) $product_id . ' AND id_shop IN (' . implode(',', $this->shops) . ')'
                            );
                        }
                    } else {
                        if (isset($redirect['id'])) {
                            $id = (int) $redirect['id'];
                        }
                        $data = ['redirect_type' => $redirect['type']];
                        if ($this->id_type_redirected) {
                            if ($id) {
                                $data['id_type_redirected'] = $id;
                            }
                            Db::getInstance()->update(
                                    'product',
                                    $data,
                                    'id_product = ' . (int) $product_id
                            );
                            Db::getInstance()->update(
                                    'product_shop',
                                    $data,
                                    'id_product = ' . (int) $product_id . ' AND id_shop IN (' . implode(',', $this->shops) . ')'
                            );
                        } else {
                            if ($id) {
                                $data['id_product_redirected'] = $id;
                            }
                            Db::getInstance()->update(
                                    'product',
                                    $data,
                                    'id_product = ' . (int) $product_id
                            );
                            Db::getInstance()->update(
                                    'product_shop',
                                    $data,
                                    'id_product = ' . (int) $product_id . ' AND id_shop IN (' . implode(',', $this->shops) . ')'
                            );
                        }
                    }
                } 

                $line_count2++;

                // stop when limit reached
                if ($line_count2 >= $limit) {
                    break;
                }
            }
        }

        if ($line_count > 0 && $line_count < $limit || $line_count2 > 0 && $line_count2 < $limit) { // last pass only
            Module::processDeferedFuncCall();
            Module::processDeferedClearCache();
        }

        return max($line_count, $line_count2);
    }

    public static function getExistingIdsFromIdsOrNames($ids_or_names, $id_lang)
    {
        // separate IDs and Refs
        $ids = [];
        $names = [];
        $whereStatements = [];
        foreach ((is_array($ids_or_names) ? $ids_or_names : [$ids_or_names]) as $id_or_name) {
            if (is_numeric($id_or_name)) {
                $ids[] = (int) $id_or_name;
            } elseif (is_string($id_or_name)) {
                $names[] = '\'' . pSQL($id_or_name) . '\'';
            }
        }

        // construct WHERE statement with OR combination
        if (count($ids) > 0) {
            $whereStatements[] = ' p.id_product IN (' . implode(',', $ids) . ') ';
        }
        if (count($names) > 0) {
            $whereStatements[] = ' p.reference IN (' . implode(',', $names) . ') OR pl.`name` IN (' . implode(',', $names) . ') ';
        }
        if (!count($whereStatements)) {
            return false;
        }

        $results = Db::getInstance()->executeS('
            SELECT DISTINCT p.id_product
            FROM `' . _DB_PREFIX_ . 'product` p
            LEFT JOIN `' . _DB_PREFIX_ . 'product_lang` pl ON p.id_product = pl.id_product AND pl.id_lang = ' . (int) $id_lang . '
            WHERE ' . implode(' OR ', $whereStatements));

        // simplify array since there is 1 useless dimension.
        // FIXME : find a better way to avoid this, directly in SQL?
        foreach ($results as $k => $v) {
            $results[$k] = (int) $v['id_product'];
        }

        return $results;
    }

    public function getExistingIdsFromIdsOrNames2($ids_or_names, $id_lang)
    {
        // separate IDs and Refs
        $ids = [];
        $whereStatements = [];
        if (!empty($ids_or_names['name'])) {
            foreach ($ids_or_names['name'] as $key => $name) {
                if ($id = $this->getProductIdByName($name, $id_lang, isset($ids_or_names['id'][$key]) ? $ids_or_names['id'][$key] : null)) {
                    $ids[] = $id;
                }
            }
        } elseif (!empty($ids_or_names['id'])) {
            $ids = $ids_or_names['id'];
        }

        // construct WHERE statement with OR combination
        if (count($ids) > 0) {
            $whereStatements[] = ' p.id_product IN (' . implode(',', $ids) . ') ';
        }

        if (!count($whereStatements)) {
            return false;
        }

        $results = Db::getInstance()->executeS('
        SELECT DISTINCT p.id_product
        FROM `' . _DB_PREFIX_ . 'product` p
        LEFT JOIN `' . _DB_PREFIX_ . 'product_lang` pl ON p.id_product = pl.id_product AND pl.id_lang = ' . (int) $id_lang . '
        WHERE ' . implode(' OR ', $whereStatements));

        // simplify array since there is 1 useless dimension.
        // FIXME : find a better way to avoid this, directly in SQL?
        foreach ($results as $k => $v) {
            $results[$k] = (int) $v['id_product'];
        }

        return $results;
    }

    public static function changeAccessoriesForProduct($accessories_id, $product_id)
    {
        foreach ($accessories_id as $id_product_2) {
            Db::getInstance()->insert('accessory', [
                'id_product_1' => (int) $product_id,
                'id_product_2' => (int) $id_product_2,
            ]);
        }
    }

    private function getProductUpdate(&$info, $id_lang)
    {
        $id_product = null;
        if ($this->updateBy == 'id' && !empty($info['id'])) {
            $id_product = (int) Db::getInstance()->getValue('
                        SELECT p.id_product
                        FROM `' . _DB_PREFIX_ . 'product` p
                        WHERE `id_product` = ' . (int) $info['id'], false);
        } elseif ($this->updateBy == 'reference' && !empty($info['reference'])) {
            if (!$id_product = (int) Db::getInstance()->getValue('
                        SELECT p.id_product
                        FROM `' . _DB_PREFIX_ . 'product` p
                        LEFT JOIN `' . _DB_PREFIX_ . 'product_lang` pl
                            ON (p.`id_product` = pl.`id_product`
                            AND `id_lang` = ' . (int) $id_lang . Shop::addSqlRestrictionOnLang('pl') . ')
                        WHERE `reference` = \'' . pSQL($info['reference']) . '\''
                            . (!empty($info['name']) ? ' AND `name` = \'' . pSQL($info['name']) . '\'' : ''), false)) {
                $id_product = (int) Db::getInstance()->getValue('
                        SELECT p.id_product
                        FROM `' . _DB_PREFIX_ . 'product` p
                        WHERE `reference` = \'' . pSQL($info['reference']) . '\'', false);
            }
        } elseif ($this->updateBy == 'name' && !empty($info['name'])) {
            if (!$id_product = (int) Db::getInstance()->getValue('
                        SELECT p.id_product
                        FROM `' . _DB_PREFIX_ . 'product` p
                        LEFT JOIN `' . _DB_PREFIX_ . 'product_lang` pl
                            ON (p.`id_product` = pl.`id_product`
                            AND `id_lang` = ' . (int) $id_lang . Shop::addSqlRestrictionOnLang('pl') . ')
                        WHERE `name` = \'' . pSQL($info['name']) . '\''
                            . (!empty($info['reference']) ? ' AND `reference` = \'' . pSQL($info['reference']) . '\'' : ''), false)) {
                $id_product = (int) Db::getInstance()->getValue('
                        SELECT p.id_product
                        FROM `' . _DB_PREFIX_ . 'product` p
                        LEFT JOIN `' . _DB_PREFIX_ . 'product_lang` pl
                            ON (p.`id_product` = pl.`id_product`
                            AND `id_lang` = ' . (int) $id_lang . Shop::addSqlRestrictionOnLang('pl') . ')
                        WHERE `name` = \'' . pSQL($info['name']) . '\'', false);
            }
        }
        if ($id_product && $this->targetAction != 'insert') {
            $info['id'] = $id_product;
        }
        return $id_product;
    }

    private function getProductInsertion(&$info, $id_lang, $force_id)
    {
        if ($force_id && !empty($info['id']) && ObjectModel::existsInDatabase((int) $info['id'], 'product')) {
            return false;
        }
        if ($this->nameExists == 'ignore' && (!empty($info['name']) || !empty($info['reference'])) &&
                Db::getInstance()->getRow('
                        SELECT p.*
                        FROM `' . _DB_PREFIX_ . 'product` p
                        LEFT JOIN `' . _DB_PREFIX_ . 'product_lang` pl
                            ON (p.`id_product` = pl.`id_product`
                            AND `id_lang` = ' . (int) $id_lang . Shop::addSqlRestrictionOnLang('pl') . ')
                        WHERE 1' . (!empty($info['name']) ? ' AND `name` = \'' . pSQL($info['name']) . '\'' : '')
                        . (!empty($info['reference']) ? ' OR `reference` = \'' . pSQL($info['reference']) . '\'' : ''))) {
            return false;
        } elseif ($this->nameExists == 'insert_if_name_exists' && !empty($info['reference']) &&
                Db::getInstance()->getRow('
                        SELECT p.*
                        FROM `' . _DB_PREFIX_ . 'product` p
                        WHERE `reference` = \'' . pSQL($info['reference']) . '\'')) {
            return false;
        } elseif ($this->nameExists == 'insert_if_reference_exists' && !empty($info['name']) &&
                Db::getInstance()->getRow('
                        SELECT p.*
                        FROM `' . _DB_PREFIX_ . 'product` p
                        LEFT JOIN `' . _DB_PREFIX_ . 'product_lang` pl
                            ON (p.`id_product` = pl.`id_product`
                            AND `id_lang` = ' . (int) $id_lang . Shop::addSqlRestrictionOnLang('pl') . ')
                        WHERE `name` = \'' . pSQL($info['name']) . '\'')) {
            return false;
        }
        if (!$force_id) {
            unset($info['id']);
        }
        return true;
    }

    protected function productImportOne($info, $default_language_id, $id_lang, $force_id, $regenerate, $shop_is_feature_active, $shop_ids, $match_ref, &$accessories, $validateOnly = false)
    {
        $id_product = null;

        if ($this->targetAction == 'insert') {
            if (!$this->getProductInsertion($info, $id_lang, $force_id)) {
                return false;
            }
        } elseif ($this->targetAction == 'update') {
            if (!$id_product = $this->getProductUpdate($info, $id_lang)) {
                return false;
            }
        } else {
            if ((!$id_product = $this->getProductUpdate($info, $id_lang)) && !$this->getProductInsertion($info, $id_lang, $force_id)) {
                return false;
            }
        }

        $product = new Product($id_product);

        $update_advanced_stock_management_value = false;
        if (isset($product->id) && $product->id && ObjectModel::existsInDatabase((int) $product->id, 'product')) {
            $product->loadStockData();
            $update_advanced_stock_management_value = true;
            $category_data = Product::getProductCategories((int) $product->id);

            if (is_array($category_data)) {
                foreach ($category_data as $tmp) {
                    if (!isset($product->id_category) || !$product->id_category || is_array($product->id_category)) {
                        $product->id_category[] = $tmp;
                    }
                }
            }

            $finalAction = 'update';
        } else {
            $finalAction = 'insert';
        }

        self::setEntityDefaultValues($product);
        // Correct some formats
        if (!empty($info['available_date'])) {
            $info['available_date'] = date("Y-m-d", strtotime($info['available_date']));
            if ($info['available_date'] === '1969-12-31' || Tools::substr($info['available_date'], 0, 1) === '-') {
                $info['available_date'] = '0000-00-00';
            }
        }
        if (!empty($info['date_add'])) {
            $info['date_add'] = date("Y-m-d H:i:s", strtotime($info['date_add']));
            if (Tools::substr($info['date_add'], 0, 10) === '1969-12-31' || Tools::substr($info['date_add'], 0, 1) === '-') {
                $info['date_add'] = date("Y-m-d H:i:s");
            }
        }
        self::arrayWalk($info, [self::class, 'fillInfo'], $product);
        if (isset($info['unit_price'])) {
            $product->unit_price_ratio = $product->unit_price != 0 ? $product->price / $product->unit_price : 0;
        }

        // Set defaul redirect
        $product->redirect_type2 = $product->redirect_type;
        $product->id_type_redirected2 = $product->id_type_redirected;
        $product->redirect_type = '404';
        $product->id_product_redirected = $product->id_type_redirected = 0;

        /** @var Product $product */
        if (!$product) {
            return;
        }

        if (!$shop_is_feature_active) {
            $product->shop = (int) Configuration::get('PS_SHOP_DEFAULT');
        } elseif (empty($product->shop)) {
            //            $product->shop = implode($this->multivalueSeparator, Shop::getContextListShopID());
            $product->shop = implode($this->multivalueSeparator, $this->shops);
        }

        if (!$shop_is_feature_active) {
            $product->id_shop_default = (int) Configuration::get('PS_SHOP_DEFAULT');
        } else {
            $product->id_shop_default = (int) Context::getContext()->shop->id;
        }

        // link product to shops
        foreach (explode($this->multivalueSeparator, $product->shop) as $shop) {
            if (!empty($shop) && !is_numeric($shop)) {
                $product->id_shop_list[] = Shop::getIdByName($shop);
            } elseif (!empty($shop)) {
                $product->id_shop_list[] = $shop;
            }
        }

        if ((int) $product->id_tax_rules_group != 0) {
            if (Validate::isLoadedObject(new TaxRulesGroup($product->id_tax_rules_group))) {
                $address = $this->context->shop->getAddress();
                $tax_manager = TaxManagerFactory::getManager($address, $product->id_tax_rules_group);
                $product_tax_calculator = $tax_manager->getTaxCalculator();
                $product->tax_rate = $product_tax_calculator->getTotalRate();
            } else {
                $this->addProductWarning(
                        'id_tax_rules_group',
                        $product->id_tax_rules_group,
                        $this->module->l('Unknown tax rule group ID. You need to create a group with this ID first.', 'EIImport')
                );
            }
        }

        if (isset($product->manufacturer) && is_numeric($product->manufacturer) && Manufacturer::manufacturerExists((int) $product->manufacturer)) {
            $product->id_manufacturer = (int) $product->manufacturer;
        } elseif (!empty($product->manufacturer) && is_string($product->manufacturer)) {
            if (($manufacturer = Manufacturer::getIdByName($product->manufacturer))) {
                $product->id_manufacturer = (int) $manufacturer;
            } else {
                $manufacturer = new Manufacturer();
                $manufacturer->name = $product->manufacturer;
                $manufacturer->active = true;
                if (($field_error = $manufacturer->validateFields(false, true)) === true &&
                        ($lang_field_error = $manufacturer->validateFieldsLang(false, true)) === true &&
                        !$validateOnly && // Do not move this condition: previous tests should be played always, but next ->add() test should not be played in validateOnly mode
                        $manufacturer->add()
                ) {
                    $product->id_manufacturer = (int) $manufacturer->id;
                    $manufacturer->associateTo($product->id_shop_list);
                } else {
                    if (!$validateOnly) {
                        $this->errors[] = sprintf(
                                        $this->module->l('%1$s (ID: %2$s) cannot be saved.', 'EIImport'),
                                        Tools::htmlentitiesUTF8($manufacturer->name),
                                        !empty($manufacturer->id) ? $manufacturer->id : 'null'
                                )
                                . '<br> ' . $this->module->l('Location', 'EIImport') . ': ' . __CLASS__ . ':' . __LINE__ . '.'
                                . '<br> ' . $this->module->l('Row number in file', 'EIImport') . ": {$this->row}. " . $this->module->l('Problematic row', 'EIImport') . ': <pre>' . print_r($info, true);
                    }
                    if ($field_error !== true || isset($lang_field_error) && $lang_field_error !== true) {
                        $this->errors[] = ($field_error !== true ? $field_error : '') . (isset($lang_field_error) && $lang_field_error !== true ? $lang_field_error : '') .
                                Db::getInstance()->getMsgError()
                                . '<br> ' . $this->module->l('Location', 'EIImport') . ': ' . __CLASS__ . ':' . __LINE__ . '.'
                                . '<br> ' . $this->module->l('Row number in file', 'EIImport') . ": {$this->row}. " . $this->module->l('Problematic row', 'EIImport') . ': <pre>' . print_r($info, true);
                    }
                }
            }
        }

        if (isset($product->supplier) && is_numeric($product->supplier) && Supplier::supplierExists((int) $product->supplier)) {
            $product->id_supplier = (int) $product->supplier;
        } elseif (!empty($product->supplier) && is_string($product->supplier)) {
            if (($supplier = Supplier::getIdByName($product->supplier))) {
                $product->id_supplier = (int) $supplier;
            } else {
                $supplier = new Supplier();
                $supplier->name = $product->supplier;
                $supplier->active = true;

                if (($field_error = $supplier->validateFields(false, true)) === true &&
                        ($lang_field_error = $supplier->validateFieldsLang(false, true)) === true &&
                        !$validateOnly && // Do not move this condition: previous tests should be played always, but next ->add() test should not be played in validateOnly mode
                        $supplier->add()
                ) {
                    $product->id_supplier = (int) $supplier->id;
                    $supplier->associateTo($product->id_shop_list);
                } else {
                    if (!$validateOnly) {
                        $this->errors[] = sprintf(
                                        $this->module->l('%1$s (ID: %2$s) cannot be saved.', 'EIImport'),
                                        Tools::htmlentitiesUTF8($supplier->name),
                                        !empty($supplier->id) ? Tools::htmlentitiesUTF8($supplier->id) : 'null'
                                )
                                . '<br> ' . $this->module->l('Location', 'EIImport') . ': ' . __CLASS__ . ':' . __LINE__ . '.'
                                . '<br> ' . $this->module->l('Row number in file', 'EIImport') . ": {$this->row}. " . $this->module->l('Problematic row', 'EIImport') . ': <pre>' . print_r($info, true);
                    }
                    if ($field_error !== true || isset($lang_field_error) && $lang_field_error !== true) {
                        $this->errors[] = ($field_error !== true ? $field_error : '') . (isset($lang_field_error) && $lang_field_error !== true ? $lang_field_error : '') .
                                Db::getInstance()->getMsgError()
                                . '<br> ' . $this->module->l('Location', 'EIImport') . ': ' . __CLASS__ . ':' . __LINE__ . '.'
                                . '<br> ' . $this->module->l('Row number in file', 'EIImport') . ": {$this->row}. " . $this->module->l('Problematic row', 'EIImport') . ': <pre>' . print_r($info, true);
                    }
                }
            }
        }

        if (isset($product->price_tex) && !isset($product->price_tin)) {
            $product->price = $product->price_tex;
        } elseif (isset($product->price_tin) && !isset($product->price_tex)) {
            $product->price = $product->price_tin;
            // If a tax is already included in price, withdraw it from price
            if ($product->tax_rate) {
                $product->price = (float) number_format($product->price / (1 + $product->tax_rate / 100), 6, '.', '');
            }
        } elseif (isset($product->price_tin, $product->price_tex)) {
            $product->price = $product->price_tex;
        }

        if (!Configuration::get('PS_USE_ECOTAX')) {
            $product->ecotax = 0;
        }

        if (isset($product->category) && is_array($product->category) && count($product->category)) {
            $product->id_cat = []; // Reset default values array
            foreach ($product->category as $key => $value) {
                // if (is_numeric($value)) {
                //     if (Category::categoryExists((int) $value)) {
                //         $product->id_category[] = (int) $value;
                //     } else {
                //         $category_to_create = new Category();
                //         $category_to_create->id = (int) $value;
                //         $category_to_create->name = self::createMultiLangField($value);
                //         $category_to_create->active = true;
                //         $category_to_create->id_parent = (int) Configuration::get('PS_HOME_CATEGORY'); // Default parent is home for unknown category to create
                //         $category_link_rewrite = Tools::link_rewrite($category_to_create->name[$default_language_id]);
                //         $category_to_create->link_rewrite = self::createMultiLangField($category_link_rewrite);
                //         if (($field_error = $category_to_create->validateFields(false, true)) === true &&
                //             ($lang_field_error = $category_to_create->validateFieldsLang(false, true)) === true &&
                //             !$validateOnly && // Do not move this condition: previous tests should be played always, but next ->add() test should not be played in validateOnly mode
                //             $category_to_create->add()
                //         ) {
                //             $product->id_category[] = (int) $category_to_create->id;
                //         } else {
                //             if (!$validateOnly) {
                //                 $this->errors[] = sprintf(
                //                     $this->module->l('%1$s (ID: %2$s) cannot be saved.', 'EIImport'),
                //                     Tools::htmlentitiesUTF8($category_to_create->name[$default_language_id]),
                //                     !empty($category_to_create->id) ? Tools::htmlentitiesUTF8($category_to_create->id) : 'null'
                //                 )
                //                 . '<br> ' . $this->module->l('Location', 'EIImport') . ': ' . __CLASS__ . ':' . __LINE__ . '.'
                //                 . '<br> ' . $this->module->l('Row number in file', 'EIImport') . ": {$this->row}. " . $this->module->l('Problematic row', 'EIImport') . ': <pre>' . print_r($info, true);
                //             }
                //             if ($field_error !== true || isset($lang_field_error) && $lang_field_error !== true) {
                //                 $this->errors[] = ($field_error !== true ? $field_error : '') . (isset($lang_field_error) && $lang_field_error !== true ? $lang_field_error : '') .
                //                     Db::getInstance()->getMsgError()
                //                     . '<br> ' . $this->module->l('Location', 'EIImport') . ': ' . __CLASS__ . ':' . __LINE__ . '.'
                //                     . '<br> ' . $this->module->l('Row number in file', 'EIImport') . ": {$this->row}. " . $this->module->l('Problematic row', 'EIImport') . ': <pre>' . print_r($info, true);
                //             }
                //         }
                //     }
                // }
                if (!$validateOnly && !empty($value) && is_string($value)) {
                    // $category = Category::searchByName($default_language_id, trim($value), true);
                    $category = $this->searchCategoryByName($default_language_id, trim($value));
                    if ($category) {
                        $found = false;
                        foreach ($category as $cat) {
                            if (isset($product->id_category[$key]) && $cat['id_category'] == $product->id_category[$key]) {
                                $category = $cat;
                                $found = true;
                                break;
                            }
                        }
                        if (!$found) {
                            $category = $category[0];
                        }
                    } else {
                        // $category = Category::searchByPath($default_language_id, trim($value), $this, 'productImportCreateCat');
                        $this->productImportCreateCat($default_language_id, trim($value), isset($product->id_category[$key]) ? $product->id_category[$key] : null);
                        $category = Category::searchByName($id_lang, trim($value), true, true);
                    }
                    if (isset($category['id_category']) && (int) $category['id_category']) {
                        $product->id_cat[] = (int) $category['id_category'];
                    } else {
                        $this->errors[] = sprintf(
                                        $this->module->l('%1$s cannot be saved.', 'EIImport'),
                                        Tools::htmlentitiesUTF8(trim($value))
                                )
                                . '<br> ' . $this->module->l('Location', 'EIImport') . ': ' . __CLASS__ . ':' . __LINE__ . '.'
                                . '<br> ' . $this->module->l('Row number in file', 'EIImport') . ": {$this->row}. " . $this->module->l('Problematic row', 'EIImport') . ': <pre>' . print_r($info, true);
                    }
                }
            }

            $product->id_category = array_values(array_unique($product->id_cat));
        }

        // Set the default category
        // if (isset($product->id_category_default)) {
        // if (is_numeric($product->id_category_default)) {
        //     if (!Category::categoryExists((int) $product->id_category_default)) {
        //         $category_to_create = new Category();
        //         $category_to_create->id = (int) $product->id_category_default;
        //         $category_to_create->name = self::createMultiLangField($product->id_category_default);
        //         $category_to_create->active = true;
        //         $category_to_create->id_parent = (int) Configuration::get('PS_HOME_CATEGORY'); // Default parent is home for unknown category to create
        //         $category_link_rewrite = Tools::link_rewrite($category_to_create->name[$default_language_id]);
        //         $category_to_create->link_rewrite = self::createMultiLangField($category_link_rewrite);
        //         if (($field_error = $category_to_create->validateFields(false, true)) === true &&
        //             ($lang_field_error = $category_to_create->validateFieldsLang(false, true)) === true &&
        //             !$validateOnly
        //         ) { // Do not move this condition: previous tests should be played always, but next ->add() test should not be played in validateOnly mode
        //             $category_to_create->add();
        //         } else {
        //             if (!$validateOnly) {
        //                 $this->errors[] = sprintf(
        //                     $this->module->l('%1$s (ID: %2$s) cannot be saved.', 'EIImport'),
        //                     Tools::htmlentitiesUTF8($category_to_create->name[$default_language_id]),
        //                     !empty($category_to_create->id) ? Tools::htmlentitiesUTF8($category_to_create->id) : 'null'
        //                 )
        //                 . '<br> ' . $this->module->l('Location', 'EIImport') . ': ' . __CLASS__ . ':' . __LINE__ . '.'
        //                        . '<br> ' . $this->module->l('Row number in file', 'EIImport') . ": {$this->row}. " . $this->module->l('Problematic row', 'EIImport') . ': <pre>' . print_r($info, true);
        //             }
        //             if ($field_error !== true || isset($lang_field_error) && $lang_field_error !== true) {
        //                 $this->errors[] = ($field_error !== true ? $field_error : '') . (isset($lang_field_error) && $lang_field_error !== true ? $lang_field_error : '') .
        //                     Db::getInstance()->getMsgError()
        //                     . '<br> ' . $this->module->l('Location', 'EIImport') . ': ' . __CLASS__ . ':' . __LINE__ . '.'
        //                        . '<br> ' . $this->module->l('Row number in file', 'EIImport') . ": {$this->row}. " . $this->module->l('Problematic row', 'EIImport') . ': <pre>' . print_r($info, true);
        //             }
        //         }
        //     }
        // }
        if (!$validateOnly) {
            if (!empty($product->category_default)) {
                // $category = Category::searchByName($default_language_id, trim($product->id_category_default), true);
                $category = $this->searchCategoryByName($default_language_id, trim($product->category_default));
                if ($category) {
                    $found = false;
                    foreach ($category as $cat) {
                        if (isset($product->id_category_default) && $cat['id_category'] == $product->id_category_default) {
                            $category = $cat;
                            $found = true;
                            break;
                        }
                    }
                    if (!$found) {
                        $category = $category[0];
                    }
                } else {
                    // $category = Category::searchByPath($default_language_id, trim($product->category_default), $this, 'productImportCreateCat');
                    $this->productImportCreateCat($default_language_id, trim($product->category_default), isset($product->id_category_default) ? $product->id_category_default : null);
                    $category = Category::searchByName($id_lang, trim($product->category_default), true, true);
                }
                if ($category['id_category']) {
                    $product->id_category_default = (int) $category['id_category'];
                } else {
                    $this->errors[] = sprintf(
                                    $this->module->l('%1$s cannot be saved.', 'EIImport'),
                                    Tools::htmlentitiesUTF8(trim($product->id_category_defaults))
                            )
                            . '<br> ' . $this->module->l('Location', 'EIImport') . ': ' . __CLASS__ . ':' . __LINE__ . '.'
                            . '<br> ' . $this->module->l('Row number in file', 'EIImport') . ": {$this->row}. " . $this->module->l('Problematic row', 'EIImport') . ': <pre>' . print_r($info, true);
                }
            }
            if (empty($product->id_category_default) && !empty($product->id_category) && is_array($product->id_category)) {
                $product->id_category_default = $product->id_category[0];
            }
        }
        // }
        // if (!empty($product->id_category_default) && !is_numeric($product->id_category_default)) {
        //     if (($cat = $this->getCategoryByName($id_lang, trim($product->id_category_default)))) {
        //         $product->id_category_default = (int) $cat['id_category'];
        //     } else {
        //         $defaultProductShop = new Shop($product->id_shop_default);
        //         $product->id_category_default = Category::getRootCategory(null, Validate::isLoadedObject($defaultProductShop) ? $defaultProductShop : null)->id;
        //     }
        // }

        $link_rewrite = (is_array($product->link_rewrite) && isset($product->link_rewrite[$id_lang])) ? trim($product->link_rewrite[$id_lang]) : '';
        $valid_link = Validate::isLinkRewrite($link_rewrite);
        if ((isset($product->link_rewrite[$id_lang]) && empty($product->link_rewrite[$id_lang])) || !$valid_link) {
            $link_rewrite = Tools::link_rewrite($product->name[$id_lang]);
            if ($link_rewrite == '') {
                $link_rewrite = 'friendly-url-autogeneration-failed';
            }
        }

        if (!$valid_link) {
            $this->informations[] = sprintf(
                    $this->module->l('Rewrite link for %1$s (ID %2$s): re-written as %3$s.', 'EIImport'),
                    Tools::htmlentitiesUTF8($product->name[$id_lang]),
                    !empty($info['id']) ? Tools::htmlentitiesUTF8($info['id']) : 'null',
                    Tools::htmlentitiesUTF8($link_rewrite)
            );
        }

        if (!$valid_link || !(is_array($product->link_rewrite) && count($product->link_rewrite))) {
            $product->link_rewrite = self::createMultiLangField($link_rewrite);
        } else {
            $product->link_rewrite[(int) $id_lang] = $link_rewrite;
        }

        // replace the value of separator by comma
        if ($this->multivalueSeparator != ',') {
            if (is_array($product->meta_keywords)) {
                foreach ($product->meta_keywords as &$meta_keyword) {
                    if (!empty($meta_keyword)) {
                        $meta_keyword = str_replace($this->multivalueSeparator, ',', $meta_keyword);
                    }
                }
            }
        }

        // Convert comma into dot for all floating values
        foreach (Product::$definition['fields'] as $key => $array) {
            if ($array['type'] == Product::TYPE_FLOAT && isset($product->{$key})) {
                $product->{$key} = str_replace(',', '.', $product->{$key});
            }
        }

        // Indexation is already 0 if it's a new product, but not if it's an update
        $product->indexed = true;
        $productExistsInDatabase = false;

        if ($product->id && ObjectModel::existsInDatabase((int) $product->id, 'product')) {
            $productExistsInDatabase = true;
        }

        if (($match_ref && $product->reference && $product->existsRefInDatabase($product->reference)) || $productExistsInDatabase) {
            $product->date_upd = date('Y-m-d H:i:s');
        }
        
        $res = false;
        $field_error = $product->validateFields(false, true);
        $lang_field_error = $product->validateFieldsLang(false, true);
        if ($field_error === true && $lang_field_error === true) {
            // check quantity
            if ($product->quantity == null) {
                $product->quantity = 0;
            }

            // If match ref is specified && ref product && ref product already in base, trying to update
            if ($match_ref && $product->reference && $product->existsRefInDatabase($product->reference)) {
                $data = Db::getInstance()->getRow('
					SELECT product_shop.`date_add`, p.`id_product`
					FROM `' . _DB_PREFIX_ . 'product` p
					' . Shop::addSqlAssociation('product', 'p') . '
					WHERE p.`reference` = "' . pSQL($product->reference) . '"', false);
                $product->id = (int) $data['id_product'];
                $product->date_add = pSQL($data['date_add']);
                $res = ($validateOnly || $product->update());
            } elseif ($productExistsInDatabase) {
                // Else If id product && id product already in base, trying to update
                $data = Db::getInstance()->getRow('
					SELECT product_shop.`date_add`
					FROM `' . _DB_PREFIX_ . 'product` p
					' . Shop::addSqlAssociation('product', 'p') . '
					WHERE p.`id_product` = ' . (int) $product->id, false);
                $product->date_add = pSQL($data['date_add']);
                $res = ($validateOnly || $product->update());
            }
            // If no id_product or update failed
            $product->force_id = (bool) $force_id;

            if (!$res) {
                if (isset($product->date_add) && $product->date_add != '') {
                    $res = ($validateOnly || $product->add(false));
                } else {
                    $res = ($validateOnly || $product->add());
                }
            }

            if (!$validateOnly && $res) {
                if ($product->getType() == Product::PTYPE_VIRTUAL) {
                    StockAvailable::setProductOutOfStock((int) $product->id, 1);
                } else {
                    StockAvailable::setProductOutOfStock((int) $product->id, (int) $product->out_of_stock);
                }

                if (($product_download_id = ProductDownload::getIdFromIdProduct((int) $product->id))) {
                    $product_download = new ProductDownload($product_download_id);
                    $product_download->delete(true);
                }

                if ($product->getType() == Product::PTYPE_VIRTUAL && !empty($info['file_url'])) {
                    $product_download = new ProductDownload();
                    $product_download->filename = ProductDownload::getNewFilename();
                    Tools::copy($info['file_url'], _PS_DOWNLOAD_DIR_ . $product_download->filename);
                    $product_download->id_product = (int) $product->id;
                    $product_download->nb_downloadable = (int) $info['nb_downloadable'];
                    $product_download->date_expiration = $info['date_expiration'] == '0000-00-00 00:00:00' ? '' : $info['date_expiration'];
                    $product_download->nb_days_accessible = (int) $info['nb_days_accessible'];
                    $product_download->display_filename = $info['display_filename'];
                    $product_download->add();
                }
            }
        }

        $shops = [];
        $product_shop = explode($this->multivalueSeparator, $product->shop);
        foreach ($product_shop as $shop) {
            if (empty($shop)) {
                continue;
            }
            $shop = trim($shop);
            if (!empty($shop) && !is_numeric($shop)) {
                $shop = Shop::getIdByName($shop);
            }

            if (in_array($shop, $shop_ids)) {
                $shops[] = $shop;
            } else {
                $this->addProductWarning(Tools::safeOutput($info['name']), $product->id, $this->module->l('Shop is not valid', 'EIImport'));
            }
        }
        if (empty($shops)) {
            $shops = Shop::getContextListShopID();
        }
        // If both failed, mysql error
        if (!$res) {
            $this->errors[] = sprintf(
                    $this->module->l('%1$s (ID: %2$s) cannot be saved.', 'EIImport'),
                    !empty($info['name']) ? Tools::safeOutput($info['name']) : 'No Name',
                    !empty($info['id']) ? Tools::safeOutput($info['id']) : 'No ID'
            );
            $this->errors[] = ($field_error !== true ? $field_error : '') . ($lang_field_error !== true ? $lang_field_error : '') .
                    Db::getInstance()->getMsgError()
                    . '<br> ' . $this->module->l('Location', 'EIImport') . ': ' . __CLASS__ . ':' . __LINE__ . '.'
                    . '<br> ' . $this->module->l('Row number in file', 'EIImport') . ": {$this->row}. " . $this->module->l('Problematic row', 'EIImport') . ': <pre>' . print_r($info, true);
        } else {
            // Product supplier
            if (!$validateOnly && !empty($product->id) && !empty($product->id_supplier) && property_exists($product, 'supplier_reference')) {
                $id_product_supplier = (int) ProductSupplier::getIdByProductAndSupplier((int) $product->id, 0, (int) $product->id_supplier);
                if ($id_product_supplier) {
                    $product_supplier = new ProductSupplier($id_product_supplier);
                } else {
                    $product_supplier = new ProductSupplier();
                }

                $product_supplier->id_product = (int) $product->id;
                $product_supplier->id_product_attribute = 0;
                $product_supplier->id_supplier = (int) $product->id_supplier;
                $product_supplier->product_supplier_price_te = $product->wholesale_price;
                $product_supplier->product_supplier_reference = $product->supplier_reference;
                $product_supplier->id_currency = Currency::getDefaultCurrency()->id;
                $product_supplier->save();
            }

            // Product suppliers
            if (!$validateOnly && !empty($product->id) && $product->id && !empty($product->supplier_names)) {
                $supp_names = explode($this->multivalueSeparator, $product->supplier_names);
                $supp_references = explode($this->multivalueSeparator, $product->supplier_references);
                $supp_prices = explode($this->multivalueSeparator, $product->supplier_prices);
                $supp_currencies = explode($this->multivalueSeparator, $product->supplier_currencies);

                foreach ($supp_names as $key => $name) {
                    $id_supplier = null;
                    if (isset($name) && is_numeric($name) && Supplier::supplierExists((int) $name)) {
                        $id_supplier = (int) $name;
                    } elseif (!empty($name) && is_string($name)) {
                        if (($supplier = Supplier::getIdByName($name))) {
                            $id_supplier = (int) $supplier;
                        } else {
                            $supplier = new Supplier();
                            $supplier->name = $name;
                            $supplier->active = true;

                            if (($field_error = $supplier->validateFields(false, true)) === true &&
                                    ($lang_field_error = $supplier->validateFieldsLang(false, true)) === true &&
                                    !$validateOnly && // Do not move this condition: previous tests should be played always, but next ->add() test should not be played in validateOnly mode
                                    $supplier->add()
                            ) {
                                $id_supplier = (int) $supplier->id;
                                $supplier->associateTo($product->id_shop_list);
                            } else {
                                if (!$validateOnly) {
                                    $this->errors[] = sprintf(
                                                    $this->module->l('%1$s (ID: %2$s) cannot be saved.', 'EIImport'),
                                                    Tools::htmlentitiesUTF8($supplier->name),
                                                    !empty($supplier->id) ? Tools::htmlentitiesUTF8($supplier->id) : 'null'
                                            )
                                            . '<br> ' . $this->module->l('Location', 'EIImport') . ': ' . __CLASS__ . ':' . __LINE__ . '.'
                                            . '<br> ' . $this->module->l('Row number in file', 'EIImport') . ": {$this->row}. " . $this->module->l('Problematic row', 'EIImport') . ': <pre>' . print_r($info, true);
                                }
                                if ($field_error !== true || isset($lang_field_error) && $lang_field_error !== true) {
                                    $this->errors[] = ($field_error !== true ? $field_error : '') . (isset($lang_field_error) && $lang_field_error !== true ? $lang_field_error : '') .
                                            Db::getInstance()->getMsgError()
                                            . '<br> ' . $this->module->l('Location', 'EIImport') . ': ' . __CLASS__ . ':' . __LINE__ . '.'
                                            . '<br> ' . $this->module->l('Row number in file', 'EIImport') . ": {$this->row}. " . $this->module->l('Problematic row', 'EIImport') . ': <pre>' . print_r($info, true);
                                }
                            }
                        }
                    }

                    if (!$validateOnly && isset($product->id) && $product->id && isset($id_supplier)) {
                        $id_product_supplier = (int) ProductSupplier::getIdByProductAndSupplier((int) $product->id, 0, (int) $id_supplier);
                        if ($id_product_supplier) {
                            $product_supplier = new ProductSupplier($id_product_supplier);
                        } else {
                            $product_supplier = new ProductSupplier();
                        }

                        $product_supplier->id_product = (int) $product->id;
                        $product_supplier->id_product_attribute = 0;
                        $product_supplier->id_supplier = (int) $id_supplier;
                        $product_supplier->product_supplier_price_te = isset($supp_prices[$key]) ? $supp_prices[$key] : 0;
                        $product_supplier->product_supplier_reference = isset($supp_references[$key]) ? $supp_references[$key] : '';
                        $product_supplier->id_currency = isset($supp_currencies[$key]) ? (int) Currency::getIdByIsoCode($supp_currencies[$key]) : Currency::getDefaultCurrency()->id;
                        $product_supplier->save();
                    }
                }
            }

            // SpecificPrice (only the basic reduction feature is supported by the import)
            if (!$shop_is_feature_active) {
                //                $info['shop'] = 1;
                $info['shop'] = (int) Configuration::get('PS_SHOP_DEFAULT');
            } elseif (!isset($info['shop']) || empty($info['shop'])) {
                $info['shop'] = implode($this->multivalueSeparator, $this->shops);
            }

            // Get shops for each product
            $info['shop'] = explode($this->multivalueSeparator, $info['shop']);

            $id_shop_list = [];
            foreach ($info['shop'] as $shop) {
                if (!empty($shop) && !is_numeric($shop)) {
                    $id_shop_list[] = (int) Shop::getIdByName($shop);
                } elseif (!empty($shop)) {
                    $id_shop_list[] = $shop;
                }
            }

            //            if ((isset($info['reduction_price']) && $info['reduction_price'] > 0) || (isset($info['reduction_percent']) && $info['reduction_percent'] > 0)) {
            //                foreach ($id_shop_list as $id_shop) {
            //                    $specific_price = SpecificPrice::getSpecificPrice($product->id, $id_shop, 0, 0, 0, 1, 0, 0, 0, 0);
            //
            //                    if (is_array($specific_price) && isset($specific_price['id_specific_price'])) {
            //                        $specific_price = new SpecificPrice((int) $specific_price['id_specific_price']);
            //                    } else {
            //                        $specific_price = new SpecificPrice();
            //                    }
            //                    $specific_price->id_product = (int) $product->id;
            //                    $specific_price->id_specific_price_rule = 0;
            //                    $specific_price->id_shop = $id_shop;
            //                    $specific_price->id_currency = 0;
            //                    $specific_price->id_country = 0;
            //                    $specific_price->id_group = 0;
            //                    $specific_price->price = -1;
            //                    $specific_price->id_customer = 0;
            //                    $specific_price->from_quantity = 1;
            //                    $specific_price->reduction = (isset($info['reduction_price']) && $info['reduction_price']) ? $info['reduction_price'] : $info['reduction_percent'] / 100;
            //                    $specific_price->reduction_type = (isset($info['reduction_price']) && $info['reduction_price']) ? 'amount' : 'percentage';
            //                    $specific_price->from = (isset($info['reduction_from']) && Validate::isDate($info['reduction_from'])) ? $info['reduction_from'] : '0000-00-00 00:00:00';
            //                    $specific_price->to = (isset($info['reduction_to']) && Validate::isDate($info['reduction_to'])) ? $info['reduction_to'] : '0000-00-00 00:00:00';
            //                    if (!$validateOnly && !$specific_price->save()) {
            //                        $this->addProductWarning(Tools::safeOutput($info['name']), $product->id, $this->module->l('Discount is invalid', 'EIImport'));
            //                    }
            //                }
            //            }
            // Specific Prices
            //            if (!empty($info['reduction_amount'])) {
            //                $red_amount = explode($this->multivalueSeparator, $product->reduction_amount);
            //                $reduction_type = explode($this->multivalueSeparator, $product->reduction_type);
            //                $reduction_from = explode($this->multivalueSeparator, $product->reduction_from);
            //                $reduction_to = explode($this->multivalueSeparator, $product->reduction_to);
            //                $reduction_price = explode($this->multivalueSeparator, $product->reduction_price);
            //                $reduction_from_quantity = explode($this->multivalueSeparator, $product->reduction_from_quantity);
            //                $reduction_customer_email = explode($this->multivalueSeparator, $product->reduction_customer_email);
            //                $reduction_group_name = explode($this->multivalueSeparator, $product->reduction_group_name);
            //                $reduction_id_country = explode($this->multivalueSeparator, $product->reduction_id_country);
            //                $reduction_currency = explode($this->multivalueSeparator, $product->reduction_currency);
            //
            //                foreach ($red_amount as $key => $amount) {
            //                    foreach ($id_shop_list as $id_shop) {
            //                        SpecificPrice::deleteByProductId($product->id);
            //
            //                        $specific_price = new SpecificPrice();
            //
            //                        $specific_price->id_product = (int) $product->id;
            //                        $specific_price->id_specific_price_rule = 0;
            //                        $specific_price->id_shop = $id_shop;
            //                        $specific_price->id_currency = !empty($reduction_currency[$key]) ? $reduction_currency[$key] : 0;
            //                        $specific_price->id_country = !empty($reduction_id_country[$key]) ? $reduction_id_country[$key] : 0;
            //                        $specific_price->id_group = 0;
            //                        $specific_price->price = !empty($reduction_price[$key]) ? $reduction_price[$key] : -1;
            //                        $specific_price->id_customer = Customer::customerExists($reduction_customer_email[$key], true);
            //                        $specific_price->from_quantity = !empty($reduction_from_quantity[$key]) ? $reduction_from_quantity[$key] : 1;
            //                        $specific_price->reduction = $amount;
            //                        $specific_price->reduction_type = !empty($reduction_type[$key]) ? $reduction_type[$key] : 'percentage';
            //                        $specific_price->from = (isset($reduction_from[$key]) && Validate::isDate($reduction_from[$key])) ? $reduction_from[$key] : '0000-00-00 00:00:00';
            //                        $specific_price->to = (isset($reduction_to[$key]) && Validate::isDate($reduction_to[$key])) ? $reduction_to[$key] : '0000-00-00 00:00:00';
            //
            //                        if (!empty($reduction_group_name[$key])) {
            //                            $group = trim($reduction_group_name[$key]);
            //                            if (empty($group)) {
            //                                continue;
            //                            }
            //
            //                            if (is_numeric($group) && $group) {
            //                                $my_group = new Group((int) $group);
            //                                if (Validate::isLoadedObject($my_group)) {
            //                                    $specific_price->id_group = (int) $group;
            //                                }
            //
            //                                continue;
            //                            }
            //                            $my_group = Group::searchByName($group);
            //                            if (isset($my_group['id_group']) && $my_group['id_group']) {
            //                                $specific_price->id_group = (int) $my_group['id_group'];
            //                            }
            //                            if (!$specific_price->id_group) {
            //                                $my_group = new Group();
            //                                $my_group->name = [$id_lang => $group];
            //                                if ($id_lang != $default_language_id) {
            //                                    $my_group->name = $my_group->name + [$default_language_id => $group];
            //                                }
            //                                $my_group->price_display_method = 1;
            //                                $my_group->add();
            //                                if (Validate::isLoadedObject($my_group)) {
            //                                    $specific_price->id_group = (int) $my_group->id;
            //                                }
            //                            }
            //                        } else {
            //                            $specific_price->id_group = 0;
            //                        }
            //
            //                        if (!$validateOnly && !$specific_price->save()) {
            //                            $this->addProductWarning(Tools::safeOutput($info['name']), $product->id, $this->module->l('Discount is invalid', 'EIImport'));
            //                        }
            //                    }
            //                }
            //            }
            // Set specific price rule
            //            SpecificPrice::setSpecificPriority($product->id, explode(';', $product->specific_price_priority));
            if (!$validateOnly && !empty($product->specific_price_priority)) {
                            Db::getInstance()->execute('INSERT INTO `' . _DB_PREFIX_ . 'specific_price_priority` (`id_product`, `priority`)
                VALUES (' . (int) $product->id . ',\'' . pSQL(rtrim($product->specific_price_priority, ';')) . '\')
                ON DUPLICATE KEY UPDATE `priority` = \'' . pSQL(rtrim($product->specific_price_priority, ';')) . '\'');
                        }

            if (!$validateOnly && !empty($product->tags)) {
                if (isset($product->id) && $product->id) {
                    $tags = Tag::getProductTags($product->id);
                    if (is_array($tags) && count($tags)) {
                        if (!empty($product->tags)) {
                            $product->tags = explode($this->multivalueSeparator, $product->tags);
                        }
                        if (is_array($product->tags) && count($product->tags)) {
                            foreach ($product->tags as $key => $tag) {
                                if (!empty($tag)) {
                                    $product->tags[$key] = trim($tag);
                                }
                            }
                            $tags[$id_lang] = $product->tags;
                            $product->tags = $tags;
                        }
                    }
                }
                // Delete tags for this id product, for no duplicating error
                Tag::deleteTagsForProduct($product->id);
                if (!is_array($product->tags) && !empty($product->tags)) {
                    $product->tags = self::createMultiLangField($product->tags);
                    foreach ($product->tags as $key => $tags) {
                        $is_tag_added = Tag::addTags($key, $product->id, $tags, $this->multivalueSeparator);
                        if (!$is_tag_added) {
                            $this->addProductWarning(Tools::safeOutput($info['name']), $product->id, $this->module->l('Tags list is invalid', 'EIImport'));

                            break;
                        }
                    }
                } else {
                    foreach ($product->tags as $key => $tags) {
                        $str = '';
                        foreach ($tags as $one_tag) {
                            $str .= $one_tag . $this->multivalueSeparator;
                        }
                        $str = rtrim($str, $this->multivalueSeparator);

                        $is_tag_added = Tag::addTags($key, $product->id, $str, $this->multivalueSeparator);
                        if (!$is_tag_added) {
                            $this->addProductWarning(Tools::safeOutput($info['name']), (int) $product->id, sprintf(
                                            $this->module->l('Invalid tag(s) (%s)', 'EIImport'),
                                            $str
                            ));

                            break;
                        }
                    }
                }
            }

            // Attachments
            if (!$validateOnly) {
                if (!empty($product->attachment_url)) {
                    $attach_urls = explode($this->multivalueSeparator, $product->attachment_url);
                    $attach_names = explode($this->multivalueSeparator, $product->attachment_name);
                    if (!empty($product->attachment_desc)) {
                        $attach_descs = explode($this->multivalueSeparator, $product->attachment_desc);
                    } else {
                        $attach_descs = array();
                    }
                    $attachments = Attachment::getAttachments($id_lang, $product->id);
                    foreach ($attach_urls as $key => $url) {
                        if (empty($attach_names[$key])) {
                            $this->errors[] = sprintf(
                                            $this->module->l('Invalid name (%1$s) for attachment URL %2$s', 'EIImport'),
                                            isset($attach_names[$key]) ? Tools::htmlentitiesUTF8($attach_names[$key]) : 'null',
                                            Tools::htmlentitiesUTF8($url)
                                    )
                                    . '<br> ' . $this->module->l('Location', 'EIImport') . ': ' . __CLASS__ . ':' . __LINE__ . '.'
                                    . '<br> ' . $this->module->l('Row number in file', 'EIImport') . ": {$this->row}. " . $this->module->l('Problematic row', 'EIImport') . ': <pre>' . print_r($info, true);
                        } else {
                            do {
                                $uniqid = sha1(microtime());
                            } while (file_exists(_PS_DOWNLOAD_DIR_ . $uniqid));

                            $attachment = new Attachment();

    //                        Tools::copy($url, _PS_DOWNLOAD_DIR_ . $uniqid);
                            // Own copy method
                            $ch = curl_init();

                            curl_setopt_array($ch, array(
                                CURLOPT_RETURNTRANSFER => true,
                                CURLOPT_FILE => fopen(_PS_DOWNLOAD_DIR_ . $uniqid, 'w'),
                                CURLOPT_TIMEOUT => 28800, // set this to 8 hours so we dont timeout on big files
                                CURLOPT_URL => $url,
                                CURLOPT_HEADERFUNCTION => function ($curl, $header) use ($attachment) {
                                    $len = Tools::strlen($header);
                                    $header = explode(':', $header, 2);
                                    if (trim($header[0]) === 'Content-Disposition') {
                                        if (!empty($header[1])) {
                                            $file_name = explode('filename=', trim($header[1]));
                                            if (!empty($file_name[1])) {
                                                $attachment->file_name = trim($file_name[1], '"');
                                            }
                                        }
                                    }
                                    return $len;
                                }
                            ));

                            curl_exec($ch);

                            // Check if the same attachment exists
                            foreach ($attachments as $atch) {
                                if (md5(file_get_contents(_PS_DOWNLOAD_DIR_ . $uniqid)) == md5(file_get_contents(_PS_DOWNLOAD_DIR_ . $atch['file']))) {
                                    @unlink(_PS_DOWNLOAD_DIR_ . $uniqid);
                                    continue 2;
                                }
                            }

                            $info = curl_getinfo($ch);

                            $attachment->file = $uniqid;
                            $attachment->mime = $info['content_type'] ?: 'application/octet-stream';
                            if (!$attachment->file_name) {
                                $attachment->file_name = 'file' . (($type = EIHelper::getTypeFromMime($attachment->mime)) ? '.' . $type : '');
                            }
                            $attachment->file_size = $info['size_download'];
                            $attachment->name = self::createMultiLangField($attach_names[$key]);
                            $attachment->description = self::createMultiLangField(isset($attach_descs[$key]) ? $attach_descs[$key] : '');
                            if ($attachment->add()) {
                                $attachment->attachProduct($product->id);
                            }
                        }
                    }
                } elseif (!empty($product->attachment_id)) {
                    $attach_ids = explode($this->multivalueSeparator, $product->attachment_id);
                    foreach ($attach_ids as $key => $a_id) {
                        try {
                            $attachment = new Attachment((int) $a_id);
                            $attachment->attachProduct($product->id);
                        } catch (Exception $e) {
                            // Error means the product has this attachment, so nothing intimidating, go on.
                            continue;
                        }
                    }
                }
            }

            // Customizations
            if (!$validateOnly) {
                if (!empty($product->customization_fields)) {
                    $custom_fields = explode($this->multivalueSeparator, $product->customization_fields);
                    $ids = [];
                    foreach ($custom_fields as $key => $field) {
                        $field = explode(':', $field);
                        foreach ($this->shops as $id_shop) {
                            $existing = Db::getInstance()->getRow('
                                SELECT cf.* FROM ' . _DB_PREFIX_ . 'customization_field cf
                                LEFT JOIN `' . _DB_PREFIX_ . 'customization_field_lang` cfl 
                                    ON (cf.`id_customization_field` = cfl.`id_customization_field` AND `id_lang` = ' . (int) $id_lang . ')
                                                    WHERE cf.id_product = ' . (int) $product->id . ' AND cfl.`name` = "' . trim($field[0]) . '"');

                            if (!empty($existing['id_customization_field'])) {
                                $cf = new CustomizationField((int) $existing['id_customization_field'], null, $id_shop);
                                foreach (Language::getIDs(false) as $id_l) {
                                    if (empty($cf->name[$id_l])) {
                                        $cf->name[$id_l] = trim($field[0]);
                                    }
                                }
                            } else {
                                $cf = new CustomizationField(null, null, $id_shop);
                                $cf->name = self::createMultiLangField(trim($field[0]));
                            }
                            $cf->id_shop_list = [$id_shop];

                            $cf->id_product = (int) $product->id;

                            $cf->type = isset($field[1]) ? (int) $field[1] : 1;

                            $cf->required = isset($field[2]) ? (int) $field[2] : 0;

                            if ($cf->save()) {
                                $ids[] = $cf->id;
                            }
                        }
                    }
                    if (!empty($ids)) {
                        Db::getInstance()->execute('
                            DELETE cf, cfl FROM ' . _DB_PREFIX_ . 'customization_field cf
                            LEFT JOIN `' . _DB_PREFIX_ . 'customization_field_lang` cfl 
                                ON (cf.`id_customization_field` = cfl.`id_customization_field` AND `id_lang` = ' . (int) $id_lang . ')
                                                    WHERE cf.id_product = ' . (int) $product->id .
                                                        ' AND cfl.`id_shop` IN (' . implode(',', $this->shops) . ')' .
                                                        ' AND cf.`id_customization_field` NOT IN (' . implode(',', $ids) . ')');
                    }

                    Configuration::updateGlobalValue('PS_CUSTOMIZATION_FEATURE_ACTIVE', '1');
                } elseif (isset($product->customization_fields) && !self::$keepOldValue) {
                    Db::getInstance()->execute('
                                                    DELETE cf, cfl FROM ' . _DB_PREFIX_ . 'customization_field cf
                            LEFT JOIN `' . _DB_PREFIX_ . 'customization_field_lang` cfl 
                                ON (cf.`id_customization_field` = cfl.`id_customization_field` AND `id_lang` = ' . (int) $id_lang . ')
                                                    WHERE cf.id_product = ' . (int) $product->id .
                                                    ' AND cfl.`id_shop` IN (' . implode(',', $this->shops) . ')');
                }
            }

            // Carriers
            if (!$validateOnly) {
                if (!empty($product->carriers)) {
                    $carriers = explode($this->multivalueSeparator, $product->carriers);
                    $ids = [];
                    foreach ($carriers as $carrier) {
                        $id_ref = Db::getInstance()->getValue('
						SELECT id_reference FROM ' . _DB_PREFIX_ . 'carrier
						WHERE `name` = "' . pSQL($carrier) . '" AND `deleted` = 0');
                        if (!empty($id_ref)) {
                            $ids[] = $id_ref;
                        } else {
                            $this->warnings[] = sprintf(
                                    $this->module->l('There is no carrier with name "%s"', 'EIImport'),
                                    Tools::htmlentitiesUTF8($carrier)
                            );
                        }
                    }
                    if (!empty($ids)) {
                        Db::getInstance()->execute('
						DELETE FROM ' . _DB_PREFIX_ . 'product_carrier
						WHERE id_product = ' . (int) $product->id
                                . ' AND id_shop IN (' . implode(',', $this->shops) . ')');

                        $data = [];
                        foreach ($ids as $id) {
                            foreach ($this->shops as $id_shop) {
                                $data[] = array(
                                    'id_product' => (int) $product->id,
                                    'id_carrier_reference' => (int) $id,
                                    'id_shop' => (int) $id_shop,
                                );
                            }
                        }
                        if ($data) {
                            Db::getInstance()->insert('product_carrier', $data);
                        }
                    }
                    // If we want to empty the product carriers. e.g. The carriers cell is empty and the "keep_old_value" input is disabled
                } elseif (isset($product->carriers)) {
                    Db::getInstance()->execute('
						DELETE FROM ' . _DB_PREFIX_ . 'product_carrier
						WHERE id_product = ' . (int) $product->id
                            . ' AND id_shop IN (' . implode(',', $this->shops) . ')');
                }
            }

            // Image
            // Delete existing images if "delete_existing_images" is set to 1
            if (!$validateOnly && isset($product->delete_existing_images)) {
                if ((bool) $product->delete_existing_images) {
                    $product->deleteImages();
                }
            }

            // Delete images only from table
//            if (!$validateOnly) {
//                $this->deleteProductImages($product->id);
//            }

            $id_image = [];
            // Cover Image
            if (!$validateOnly && isset($product->cover_image)) {
                $product->cover_image = trim($product->cover_image);
                $error = false;
                if (!empty($product->cover_image)) {
//                    $product->cover_image = str_replace(' ', '%20', $product->cover_image);
                    $product_images = Image::getImages($this->context->language->id, $product->id);
                    $id_img = 0;
                    foreach ($product_images as $img) {
                        if ($product->cover_image == $this->getImageLink($img['id_image'])) {
                            $id_img = $img['id_image'];
                            break;
                        }
                    }
                    $tmpfile1 = _IP_TMP_IMG_DIR_ . uniqid() . '.jpg';
                    $tmpfile2 = _IP_TMP_IMG_DIR_ . uniqid() . '_ip.jpg';
                    $context = stream_context_create(array('http' => array('user_agent' => 'custom user agent string')));
                    if (self::$imageDownloadType === self::DOWNLOAD_IMG_WITHOUT_CONTEXT) {
                        file_put_contents($tmpfile1, Tools::file_get_contents($product->cover_image, false, $context, 5, true));
                    } else {
                        $arrContextOptions = array(
                            "ssl" => array(
                                "verify_peer" => false,
                                "verify_peer_name" => false,
                            ),
                        );
                        file_put_contents($tmpfile1, file_get_contents($product->cover_image, false, stream_context_create($arrContextOptions)));
                    }
                    if (filesize($tmpfile1) == 0) {
                        $this->warnings[] = sprintf($this->module->l('Error copying image: %1$s', 'EIImport'), $product->cover_image);
                        @unlink($tmpfile1);
                    } else {
                        $tgt_width = $tgt_height = 0;
                        $src_width = $src_height = 0;
                        $err = 0;
                        ImageManager::resize($tmpfile1, $tmpfile2, null, null, 'jpg', false, $err, $tgt_width, $tgt_height, 5, $src_width, $src_height);
                        if (!$id_img) {
                            foreach ($product_images as $img) {
                                $existing_image = realpath(_PS_PROD_IMG_DIR_ . Image::getImgFolderStatic($img['id_image']) . $img['id_image'] . '.jpg');
                                if (md5(file_get_contents($tmpfile2)) == md5(file_get_contents($existing_image))) {
                                    $id_img = $img['id_image'];
                                    break;
                                }
                            }
                        }
                        @unlink($tmpfile1);

                        if ($id_img) {
                            $image = new Image((int) $id_img);
                            $image->id_product = (int) $product->id;
                        } else {
                            $image = new Image();
                            $image->id_product = (int) $product->id;
                            $image->position = Image::getHighestPosition($product->id) + 1;
                        }
                        $image->cover = true;

                        $index = array_search($product->cover_image, $product->image ? $product->image : []);
                        if ($index !== false && isset($product->image_alt[$index]) && Tools::strlen($product->image_alt[$index]) > 0) {
                            $image->legend = self::createMultiLangField($product->image_alt[$index]);
                        }
                        // file_exists doesn't work with HTTP protocol
                        if (($field_error = $image->validateFields(false, true)) === true &&
                                ($lang_field_error = $image->validateFieldsLang(false, true)) === true
                        ) {
                            Db::getInstance()->execute('UPDATE `' . _DB_PREFIX_ . 'image` SET `cover` = NULL
                            WHERE id_product = ' . (int) $product->id . ' AND `cover` = 1');
                            Db::getInstance()->execute('UPDATE `' . _DB_PREFIX_ . 'image_shop` SET `cover` = NULL
                            WHERE id_product = ' . (int) $product->id . ' AND `cover` = 1 AND id_shop IN (' . implode(',', $shops) . ')');
                            if (isset($image->id) && ObjectModel::existsInDatabase((int) $image->id, 'image')) {
                                $image->update();
                                $exists = true;
                            } else {
                                $image->add();
                                $exists = false;
                            }
                            // associate image to selected shops
                            $image->associateTo($shops);
                            if (!self::copyImg($product->id, $image->id, $product->cover_image, 'products', $regenerate, $exists, $tmpfile2)) {
                                $image->delete();
                                $this->warnings[] = sprintf($this->module->l('Error copying image: %1$s', 'EIImport'), $product->cover_image);
                            } else {
                                $id_image[] = $image->id;
                            }
                        } else {
                            $error = true;
                        }
                    }
                }

                if ($error) {
                    $this->warnings[] = sprintf(
                            $this->module->l('Product #%1$d: the cover picture (%2$s) cannot be saved.', 'EIImport'),
                            Tools::htmlentitiesUTF8(isset($image) ? $image->id_product : ''),
                            Tools::htmlentitiesUTF8($product->cover_image)
                    );

                    if ($field_error !== true || isset($lang_field_error) && $lang_field_error !== true) {
                        $this->warnings[] = ($field_error !== true ? $field_error : '') . (isset($lang_field_error) && $lang_field_error !== true ? $lang_field_error : '');
                    }
                }
            }

            // Other images
            if (!$validateOnly && isset($product->image) && is_array($product->image) && count($product->image)) {
                $product_images = Image::getImages($this->context->language->id, $product->id);
                $product_has_images = !empty($product->cover_image) || (bool) $product_images;
                foreach ($product->image as $key => $url) {
                    $url = trim($url);
                    if ($url == $product->cover_image) {
                        continue;
                    }

                    $error = false;
                    if (!empty($url)) {
//                        $url = str_replace(' ', '%20', $url);
                        $id_img = 0;
                        foreach ($product_images as $img) {
                            if ($url == $this->getImageLink($img['id_image'])) {
                                $id_img = $img['id_image'];
                                break;
                            }
                        }
                        $tmpfile1 = _IP_TMP_IMG_DIR_ . uniqid() . '.jpg';
                        $tmpfile2 = _IP_TMP_IMG_DIR_ . uniqid() . '_ip.jpg';
                        $context = stream_context_create(array('http' => array('user_agent' => 'custom user agent string')));
                        if (self::$imageDownloadType === self::DOWNLOAD_IMG_WITHOUT_CONTEXT) {
                            file_put_contents($tmpfile1, Tools::file_get_contents($url, false, $context, 5, true));
                        } else {
                            $arrContextOptions = array(
                                "ssl" => array(
                                    "verify_peer" => false,
                                    "verify_peer_name" => false,
                                ),
                            );
                            file_put_contents($tmpfile1, file_get_contents($url, false, stream_context_create($arrContextOptions)));
                        }
                        if (filesize($tmpfile1) == 0) {
                            $this->warnings[] = sprintf($this->module->l('Error copying image: %1$s', 'EIImport'), $url);
                            @unlink($tmpfile1);
                        } else {
                            $tgt_width = $tgt_height = 0;
                            $src_width = $src_height = 0;
                            $err = 0;
                            ImageManager::resize($tmpfile1, $tmpfile2, null, null, 'jpg', false, $err, $tgt_width, $tgt_height, 5, $src_width, $src_height);
                            if (!$id_img) {
                                foreach ($product_images as $img) {
                                    $existing_image = realpath(_PS_PROD_IMG_DIR_ . Image::getImgFolderStatic($img['id_image']) . $img['id_image'] . '.jpg');
                                    if (md5(file_get_contents($tmpfile2)) == md5(file_get_contents($existing_image))) {
                                        $id_img = $img['id_image'];
                                        break;
                                    }
                                }
                            }
                            @unlink($tmpfile1);

                            if ($id_img) {
                                $image = new Image((int) $id_img);
                                $image->id_product = (int) $product->id;
                            } else {
                                $image = new Image();
                                $image->id_product = (int) $product->id;
                                $image->position = Image::getHighestPosition($product->id) + 1;
                                $image->cover = !$product_has_images ? true : false;
                            }

                            if (isset($product->image_alt[$key]) && Tools::strlen($product->image_alt[$key]) > 0) {
                                $image->legend = self::createMultiLangField($product->image_alt[$key]);
                            }
                            // file_exists doesn't work with HTTP protocol
                            if (($field_error = $image->validateFields(false, true)) === true &&
                                    ($lang_field_error = $image->validateFieldsLang(false, true)) === true
                            ) {
                                if (isset($image->id) && ObjectModel::existsInDatabase((int) $image->id, 'image')) {
                                    $image->update();
                                    $exists = true;
                                } else {
                                    if ($image->cover) {
                                        Db::getInstance()->execute('UPDATE `' . _DB_PREFIX_ . 'image` SET `cover` = NULL
                                        WHERE id_product = ' . (int) $product->id . ' AND `cover` = 1');
                                        Db::getInstance()->execute('UPDATE `' . _DB_PREFIX_ . 'image_shop` SET `cover` = NULL
                                        WHERE id_product = ' . (int) $product->id . ' AND `cover` = 1 AND id_shop IN (' . implode(',', $shops) . ')');
                                    }
                                    $image->add();
                                    $exists = false;
                                    $product_has_images = true;
                                }
                                // associate image to selected shops
                                $image->associateTo($shops);
                                if (!self::copyImg($product->id, $image->id, $url, 'products', $regenerate, $exists, $tmpfile2)) {
                                    $image->delete();
                                    $this->warnings[] = sprintf($this->module->l('Error copying image: %1$s', 'EIImport'), $url);
                                } else {
                                    $id_image[] = $image->id;
                                }
                            } else {
                                $error = true;
                            }
                        }
                    } else {
                        $error = true;
                    }

                    if ($error) {
                        $this->warnings[] = sprintf(
                                $this->module->l('Product #%1$d: the picture (%2$s) cannot be saved.', 'EIImport'),
                                Tools::htmlentitiesUTF8(isset($image) ? $image->id_product : ''),
                                Tools::htmlentitiesUTF8($url)
                        );

                        if ($field_error !== true || isset($lang_field_error) && $lang_field_error !== true) {
                            $this->warnings[] = ($field_error !== true ? $field_error : '') . (isset($lang_field_error) && $lang_field_error !== true ? $lang_field_error : '');
                        }
                    }
                }
            }

            // Delete existing non-imported images
            if (!$validateOnly && Tools::getValue('delete_images')) {
                foreach (Image::getImages($this->context->language->id, $product->id) as $img) {
                    if (!in_array($img['id_image'], $id_image)) {
                        $image = new Image($img['id_image']);
                        $image->delete();
                    }
                }
            }

            if (!$validateOnly && isset($product->id_category) && is_array($product->id_category)) {
                if (!in_array($product->id_category_default, $product->id_category)) {
                    $product->id_category[] = $product->id_category_default;
                }
                $product->updateCategories(array_map('intval', $product->id_category));
            }

            $product->checkDefaultAttributes();
            if (!$validateOnly && !$product->cache_default_attribute) {
                Product::updateDefaultAttribute($product->id);
            }

            // Features import
            $features = get_object_vars($product);

            if (!$validateOnly && isset($features['features'])) {
                $product->deleteProductFeatures();
                if (!empty($features['features'])) {
                    foreach (explode($this->multivalueSeparator, $features['features']) as $single_feature) {
                        if (empty($single_feature)) {
                            continue;
                        }
                        $tab_feature = explode('~', $single_feature);
                        $feature_name = isset($tab_feature[0]) ? trim($tab_feature[0]) : '';
                        $feature_value = isset($tab_feature[1]) ? trim($tab_feature[1]) : '';
                        $position = false; //isset($tab_feature[2]) ? (int) $tab_feature[2] - 1 : false;
                        $custom = isset($tab_feature[2]) ? (int) $tab_feature[2] : false;
                        if (!empty($feature_name) && !empty($feature_value)) {
                            $id_feature = (int) Feature::addFeatureImport($feature_name, $position);
                            $id_product = null;
                            if ($force_id || $match_ref) {
                                $id_product = (int) $product->id;
                            }
                            $id_feature_value = (int) FeatureValue::addFeatureValueImport($id_feature, $feature_value, null, $id_lang, $custom);
                            Product::addFeatureProductImport($product->id, $id_feature, $id_feature_value);
                        }
                    }
                }
            }
            // clean feature positions to avoid conflict
            Feature::cleanPositions();

            // set advanced stock management
            if (!$validateOnly && isset($product->advanced_stock_management)) {
                if ($product->advanced_stock_management != 1 && $product->advanced_stock_management != 0) {
                    $this->warnings[] = sprintf(
                            $this->module->l('Advanced stock management has incorrect value. Not set for product %1$s', 'EIImport'),
                            Tools::htmlentitiesUTF8($product->name[$default_language_id])
                    );
                } elseif ($update_advanced_stock_management_value) {
                    $product->setAdvancedStockManagement($product->advanced_stock_management);
                }
            }

            // Check if warehouse exists
            if (isset($product->warehouse) && $product->warehouse) {
                if (!$validateOnly) {
                    foreach (explode($this->multivalueSeparator, $product->warehouse) as $wh) {
                        $warehouse = explode(':', $wh);
                        $id_warehouse = Db::getInstance()->getValue('
					SELECT id_warehouse
					FROM `' . _DB_PREFIX_ . 'warehouse`
					WHERE `reference` = "' . pSQL(trim($warehouse[0])) . '"'
                                . (!empty($warehouse[1]) ? ' AND `name` = "' . pSQL(trim($warehouse[1])) . '"' : ''), false);
                        if ($id_warehouse) {
                            // Get already associated warehouses
                            $associated_warehouses_collection = WarehouseProductLocation::getCollection($product->id);
                            // Delete any entry in warehouse for this product
                            foreach ($associated_warehouses_collection as $awc) {
                                $awc->delete();
                            }
                            $warehouse_location_entity = new WarehouseProductLocation();
                            $warehouse_location_entity->id_product = $product->id;
                            $warehouse_location_entity->id_product_attribute = 0;
                            $warehouse_location_entity->id_warehouse = $id_warehouse;
                            if (!empty($warehouse[2])) {
                                $warehouse_location_entity->location = $warehouse[2];
                            }
                            if (WarehouseProductLocation::getProductLocation($product->id, 0, $id_warehouse) !== false) {
                                $warehouse_location_entity->update();
                            } else {
                                $warehouse_location_entity->save();
                            }
                            StockAvailable::synchronize($product->id);
                        } else {
                            $this->warnings[] = sprintf(
                                    $this->module->l('Warehouse did not exist, cannot set on product %1$s.', 'EIImport'),
                                    Tools::htmlentitiesUTF8($product->name[$default_language_id])
                            );
                        }
                    }
                }
            }

            // stock available
            if (isset($product->depends_on_stock)) {
                if ($product->depends_on_stock != 0 && $product->depends_on_stock != 1) {
                    $this->warnings[] = sprintf(
                            $this->module->l('Incorrect value for "Depends on stock" for product %1$s', 'EIImport'),
                            Tools::htmlentitiesUTF8($product->name[$default_language_id])
                    );
                } elseif (!$validateOnly) {
                    StockAvailable::setProductDependsOnStock($product->id, $product->depends_on_stock);
                }

                // This code allows us to set qty and disable depends on stock
                if (!$validateOnly && (isset($product->quantity) || $finalAction === 'insert')) {
                    // if depends on stock and quantity, add quantity to stock
                    if ($product->depends_on_stock == 1) {
                        $stock_manager = StockManagerFactory::getManager();
                        $price = str_replace(',', '.', $product->wholesale_price);
                        if ($price == '0') {
                            $price = 0.000001;
                        }
                        $price = round((float) $price, 6);
                        $warehouse = new Warehouse($product->warehouse);
                        if ($stock_manager->addProduct((int) $product->id, 0, $warehouse, (int) $product->quantity, 1, $price, true)) {
                            StockAvailable::synchronize((int) $product->id);
                        }
                    } else {
                        if ($shop_is_feature_active) {
                            foreach ($shops as $shop) {
                                StockAvailable::setQuantity((int) $product->id, 0, (int) $product->quantity, (int) $shop);
                                if (method_exists('StockAvailable', 'setLocation')) {
                                    StockAvailable::setLocation((int) $product->id, (string) $product->location, (int) $shop, 0);
                                }
                            }
                        } else {
                            StockAvailable::setQuantity((int) $product->id, 0, (int) $product->quantity, (int) $this->context->shop->id);
                            if (method_exists('StockAvailable', 'setLocation')) {
                                StockAvailable::setLocation((int) $product->id, (string) $product->location, (int) $this->context->shop->id, 0);
                            }
                        }
                    }
                }
            } elseif (!$validateOnly && (isset($product->quantity) || $finalAction === 'insert')) {
                // if not depends_on_stock set, use normal qty
                if ($shop_is_feature_active) {
                    foreach ($shops as $shop) {
                        StockAvailable::setQuantity((int) $product->id, 0, (int) $product->quantity, (int) $shop);
                        if (method_exists('StockAvailable', 'setLocation')) {
                            StockAvailable::setLocation((int) $product->id, (string) $product->location, (int) $shop, 0);
                        }
                    }
                } else {
                    StockAvailable::setQuantity((int) $product->id, 0, (int) $product->quantity, (int) $this->context->shop->id);
                    if (method_exists('StockAvailable', 'setLocation')) {
                        StockAvailable::setLocation((int) $product->id, (string) $product->location, (int) $this->context->shop->id, 0);
                    }
                }
            }

            if (!$validateOnly) {
                // Add the product to search index
                if (in_array($product->visibility, array('both', 'search')) && Configuration::get('PS_SEARCH_INDEXATION')) {
                    Search::indexation(false, $product->id);
                }
            }

            // Own accessory linkage
//            if (isset($product->accessories) && !$validateOnly && is_array($product->accessories) && count($product->accessories)) {
//                $data = [];
//                foreach (array_unique($product->accessories) as $id_product_2) {
//                    $data[] = [
//                        'id_product_1' => (int) $product->id,
//                        'id_product_2' => (int) $id_product_2,
//                    ];
//                }
//                Db::getInstance()->delete('accessory', 'id_product_1 = ' . (int) $product->id);
//                Db::getInstance()->insert('accessory', $data);
//            }
            // Accessories linkage
            if (!$validateOnly) {
//                if (isset($product->id_accessories) && is_array($product->id_accessories)) {
//                    $accessories[$product->id] = $product->id_accessories;
//                }
                $this->setAccessoriesAndRedirects($product);
            }
        }
    }

    private function setAccessoriesAndRedirects($product)
    {
        // Set accessories
        if (isset($product->id_accessories) && is_array($product->id_accessories) || isset($product->accessories) && is_array($product->accessories)) {
            $_SESSION['ip_import']['accessories'][$product->id] = [
                'id' => $product->id_accessories,
                'name' => $product->accessories,
            ];
        }

        // Set redirects
        if (isset($product->redirect_type2) && $product->redirect_type2 != '404') {
            $_SESSION['ip_import']['redirects'][$product->id] = [
                'type' => $product->redirect_type2,
                'id' => $product->id_type_redirected2,
                'name' => $product->type_redirected_name,
            ];
        }
    }

    public function productImportCreateCat($default_language_id, $category_name, $id_category = null, $id_parent_category = null)
    {
        $category_to_create = new Category();
        $shop_is_feature_active = Shop::isFeatureActive();
        if (!$shop_is_feature_active) {
            // $category_to_create->id_shop_default = 1;
            $category_to_create->id_shop_default = $this->context->shop->id;
        } else {
            $category_to_create->id_shop_default = (int) Context::getContext()->shop->id;
        }
        $category_to_create->name = self::createMultiLangField(trim($category_name));
        if ((int) $id_category && !ObjectModel::existsInDatabase((int) $id_category, 'category')) {
            $category_to_create->id = (int) $id_category;
            $category_to_create->force_id = true;
        }
        $category_to_create->active = true;
        $category_to_create->id_parent = (int) $id_parent_category ? (int) $id_parent_category : (int) Configuration::get('PS_HOME_CATEGORY'); // Default parent is home for unknown category to create
        $category_link_rewrite = Tools::link_rewrite($category_to_create->name[$default_language_id]);
        $category_to_create->link_rewrite = self::createMultiLangField($category_link_rewrite);

        if (($field_error = $category_to_create->validateFields(false, true)) !== true ||
                ($lang_field_error = $category_to_create->validateFieldsLang(false, true)) !== true ||
                !$category_to_create->add()
        ) {
            $this->errors[] = sprintf(
                            $this->module->l('%1$s (ID: %2$s) cannot be saved.', 'EIImport'),
                            Tools::htmlentitiesUTF8($category_to_create->name[$default_language_id]),
                            !empty($category_to_create->id) ? Tools::htmlentitiesUTF8($category_to_create->id) : 'null'
                    )
                    . '<br> ' . $this->module->l('Location', 'EIImport') . ': ' . __CLASS__ . ':' . __LINE__;
            if ($field_error !== true || isset($lang_field_error) && $lang_field_error !== true) {
                $this->errors[] = ($field_error !== true ? $field_error : '') . (isset($lang_field_error) && $lang_field_error !== true ? $lang_field_error : '') .
                        Db::getInstance()->getMsgError()
                        . '<br> ' . $this->module->l('Location', 'EIImport') . ': ' . __CLASS__ . ':' . __LINE__;
            }
        }
    }

    private function deleteProductImages($id)
    {
        return Db::getInstance()->execute('DELETE i, il FROM `' . _DB_PREFIX_ . 'image` i
                LEFT JOIN `' . _DB_PREFIX_ . 'image_lang` il ON i.id_image = il.id_image
                WHERE `id_product` = ' . (int) $id) && Db::getInstance()->execute('DELETE FROM `' . _DB_PREFIX_ . 'image_shop`
                    WHERE `id_product` = ' . (int) $id);
    }

    public function packImport($offset = false, $limit = false, &$crossStepsVariables = false, $validateOnly = false)
    {
        $default_language = Configuration::get('PS_LANG_DEFAULT');
        $id_lang = Tools::getValue('language');
        $this->deleteOtherProducts = Tools::getValue('delete_others');
        if (!Validate::isUnsignedId($id_lang)) {
            $id_lang = $default_language;
        }

        $this->receiveColumns();
        $handle = $this->openCsvFile($offset);
        if (!$handle) {
            return false;
        }

        self::setLocale();

        $force_id = Tools::getValue('keep_id');
        $shop_is_feature_active = Shop::isFeatureActive();

        $line_count = 0;
        for ($current_line = 0; ($line = fgetcsv($handle, MAX_LINE_SIZE, $this->separator, $this->enclosure)) && (!$limit || $current_line < $limit); $current_line++) {
            $line_count++;
            $this->row = $offset + $line_count + 1;
            if ($this->convert) {
                $line = $this->utf8EncodeArray($line);
            }

            $isEmpty = true;
            foreach ($line as $ln) {
                if ($ln) {
                    $isEmpty = false;
                    break;
                }
            }

            if ($isEmpty) {
                $this->warnings[] = $this->module->l('There is an empty row in the file that won\'t be imported.', 'EIImport');

                continue;
            }

            $info = self::getMaskedRow($line);
            $info = array_map('trim', $info);

            $this->packImportOne(
                    $info,
                    $default_language,
                    $id_lang,
                    $force_id,
                    $shop_is_feature_active,
                    $crossStepsVariables,
                    $validateOnly
            );
        }
        $this->closeCsvFile($handle);

        return $line_count;
    }

    private function getPackUpdateOrInsertion(&$info, $id_lang, $id_shop_list)
    {
        $id_pack = $id_product = null;
        $id_product_attribute = 0;

        // Get Pack ID
//        if (!empty($info['id'])) {
//            $id_pack = Db::getInstance()->getValue('
//                        SELECT p.id_product_pack
//                        FROM `' . _DB_PREFIX_ . 'pack` p
//                        WHERE `id_product_pack` = ' . (int) $info['id'], false);
//        }
//        if (!empty($info['reference']) && !$id_pack) {
//            if (!$id_pack = Db::getInstance()->getValue('
//                        SELECT p.id_product_pack
//                        FROM `' . _DB_PREFIX_ . 'pack` p
//                        LEFT JOIN `' . _DB_PREFIX_ . 'product` pr ON p.id_product_pack = pr.id_product
//                        LEFT JOIN `' . _DB_PREFIX_ . 'product_lang` pl
//                            ON (pr.`id_product` = pl.`id_product`
//                            AND `id_lang` = ' . (int) $id_lang . Shop::addSqlRestrictionOnLang('pl') . ')
//                        WHERE `reference` = \'' . pSQL($info['reference']) . '\''
//                    . (!empty($info['name']) ? ' AND `name` = \'' . pSQL($info['name']) . '\'' : ''), false)) {
//                $id_pack = Db::getInstance()->getValue('
//                        SELECT p.id_product_pack
//                        FROM `' . _DB_PREFIX_ . 'pack` p
//                        LEFT JOIN `' . _DB_PREFIX_ . 'product` pr ON p.id_product_pack = pr.id_product
//                        WHERE `reference` = \'' . pSQL($info['reference']) . '\'', false);
//            }
//        }
//        if (!empty($info['name']) && !$id_pack) {
//            if (!$id_pack = Db::getInstance()->getValue('
//                        SELECT p.id_product_pack
//                        FROM `' . _DB_PREFIX_ . 'pack` p
//                        LEFT JOIN `' . _DB_PREFIX_ . 'product` pr ON p.id_product_pack = pr.id_product
//                        LEFT JOIN `' . _DB_PREFIX_ . 'product_lang` pl
//                            ON (pr.`id_product` = pl.`id_product`
//                            AND `id_lang` = ' . (int) $id_lang . Shop::addSqlRestrictionOnLang('pl') . ')
//                        WHERE `name` = \'' . pSQL($info['name']) . '\''
//                    . (!empty($info['reference']) ? ' AND `reference` = \'' . pSQL($info['reference']) . '\'' : ''), false)) {
//                $id_pack = Db::getInstance()->getValue('
//                        SELECT p.id_product_pack
//                        FROM `' . _DB_PREFIX_ . 'pack` p
//                        LEFT JOIN `' . _DB_PREFIX_ . 'product_lang` pl
//                            ON (p.`id_product_pack` = pl.`id_product`
//                            AND `id_lang` = ' . (int) $id_lang . Shop::addSqlRestrictionOnLang('pl') . ')
//                        WHERE `name` = \'' . pSQL($info['name']) . '\'', false);
//            }
//        }
        if (!empty($info['id'])) {
            $id_pack = Db::getInstance()->getValue('
                        SELECT p.id_product
                        FROM `' . _DB_PREFIX_ . 'product` p
                        WHERE `id_product` = ' . (int) $info['id'], false);
        }
        if (!empty($info['reference']) && !$id_pack) {
            if (!$id_pack = Db::getInstance()->getValue('
                        SELECT p.id_product
                        FROM `' . _DB_PREFIX_ . 'product` p
                        LEFT JOIN `' . _DB_PREFIX_ . 'product_lang` pl
                            ON (p.`id_product` = pl.`id_product`
                            AND `id_lang` = ' . (int) $id_lang . Shop::addSqlRestrictionOnLang('pl') . ')
                        WHERE `reference` = \'' . pSQL($info['reference']) . '\''
                    . (!empty($info['name']) ? ' AND `name` = \'' . pSQL($info['name']) . '\'' : ''), false)) {
                $id_pack = Db::getInstance()->getValue('
                        SELECT p.id_product
                        FROM `' . _DB_PREFIX_ . 'product` p
                        WHERE `reference` = \'' . pSQL($info['reference']) . '\'', false);
            }
        }
        if (!empty($info['name']) && !$id_pack) {
            if (!$id_pack = Db::getInstance()->getValue('
                        SELECT p.id_product
                        FROM `' . _DB_PREFIX_ . 'product` p
                        LEFT JOIN `' . _DB_PREFIX_ . 'product_lang` pl
                            ON (p.`id_product` = pl.`id_product`
                            AND `id_lang` = ' . (int) $id_lang . Shop::addSqlRestrictionOnLang('pl') . ')
                        WHERE `name` = \'' . pSQL($info['name']) . '\''
                    . (!empty($info['reference']) ? ' AND `reference` = \'' . pSQL($info['reference']) . '\'' : ''), false)) {
                $id_pack = Db::getInstance()->getValue('
                        SELECT p.id_product
                        FROM `' . _DB_PREFIX_ . 'product` p
                        LEFT JOIN `' . _DB_PREFIX_ . 'product_lang` pl
                            ON (p.`id_product` = pl.`id_product`
                            AND `id_lang` = ' . (int) $id_lang . Shop::addSqlRestrictionOnLang('pl') . ')
                        WHERE `name` = \'' . pSQL($info['name']) . '\'', false);
            }
        }

        if ($id_pack) {
            // Get product ID
            if (!empty($info['id_product'])) {
                $id_product = $info['id_product'];
            } elseif (!empty($info['product_name']) || !empty($info['product_reference'])) {
                if ($data = Db::getInstance()->executeS('
                            SELECT p.*
                            FROM `' . _DB_PREFIX_ . 'product` p
                            LEFT JOIN `' . _DB_PREFIX_ . 'product_lang` pl
                                ON (p.`id_product` = pl.`id_product`
                                AND `id_lang` = ' . (int) $id_lang . Shop::addSqlRestrictionOnLang('pl') . ')
                            WHERE 1' . (!empty($info['product_name']) ? ' AND `name` = \'' . pSQL($info['product_name']) . '\'' : '')
                        . (!empty($info['product_reference']) ? ' AND `reference` = \'' . pSQL($info['product_reference']) . '\'' : ''))) {
                    
                } elseif (!empty($info['product_reference']) && $data = Db::getInstance()->executeS('
                            SELECT p.*
                            FROM `' . _DB_PREFIX_ . 'product` p
                            WHERE `reference` = \'' . pSQL($info['product_reference']) . '\'')) {
                    
                } elseif (!empty($info['product_name']) && $data = Db::getInstance()->executeS('
                            SELECT p.*
                            FROM `' . _DB_PREFIX_ . 'product` p
                            LEFT JOIN `' . _DB_PREFIX_ . 'product_lang` pl
                                ON (p.`id_product` = pl.`id_product`
                                AND `id_lang` = ' . (int) $id_lang . Shop::addSqlRestrictionOnLang('pl') . ')
                            WHERE `name` = \'' . pSQL($info['product_name']) . '\'')) {
                    
                }
                if ($data) {
                    $found = false;
                    foreach ($data as $d) {
                        if ($d['id_product'] == $info['id_product']) {
                            $data = $d;
                            $found = true;
                            break;
                        }
                    }
                    if (!$found) {
                        $data = $data[0];
                    }
                }
                if (!empty($data['id_product'])) {
                    $id_product = $data['id_product'];
                }
            }

            // Get combination ID
            if ($id_product) {
                if (!empty($info['id_product_attribute'])) {
                    $id_product_attribute = $info['id_product_attribute'];
                } elseif (!empty($info['attribute']) || !empty($info['product_attribute_reference'])) {
                    $id_join_shop = count($id_shop_list) == 1 ? $id_shop_list[0] : $this->context->shop->id;
                    if (!empty($info['attribute'])) {
                        if (!empty($info['product_attribute_reference'])) {
                            $id_product_attribute = Db::getInstance()->getValue('SELECT id_product_attribute FROM (
                                    SELECT
                                        pac.id_product_attribute,
                                        GROUP_CONCAT(CONCAT_WS(":", agl.name, al.name) ORDER BY agl.name, al.name SEPARATOR "' . $this->multivalueSeparator . '") `values`
                                    FROM ' . _DB_PREFIX_ . 'product_attribute_combination pac
                                    JOIN ' . _DB_PREFIX_ . 'product_attribute pa ON pac.id_product_attribute = pa.id_product_attribute
                                    JOIN ' . _DB_PREFIX_ . 'attribute a ON pac.id_attribute = a.id_attribute
                                    JOIN ' . _DB_PREFIX_ . 'attribute_shop ash ON a.id_attribute = ash.id_attribute AND ash.id_shop = ' . $id_join_shop . '
                                    JOIN ' . _DB_PREFIX_ . 'attribute_lang al ON a.id_attribute = al.id_attribute AND al.id_lang = ' . $id_lang . '
                                    JOIN ' . _DB_PREFIX_ . 'attribute_group ag ON a.id_attribute_group = ag.id_attribute_group
                                    JOIN ' . _DB_PREFIX_ . 'attribute_group_shop agsh ON a.id_attribute_group = agsh.id_attribute_group AND agsh.id_shop = ' . $id_join_shop . '
                                    JOIN ' . _DB_PREFIX_ . 'attribute_group_lang agl ON a.id_attribute_group = agl.id_attribute_group AND agl.id_lang = ' . $id_lang . '
                                    WHERE pa.`id_product` = ' . (int) $id_product . ' AND pa.`reference` = \'' . (int) $info['product_attribute_reference'] . '\'' . '
                                    GROUP BY pac.id_product_attribute
                                HAVING `values` = "' . str_replace('"', '\"', $info['attribute']) . '") tmp', false);
                        }
                        if (!$id_product_attribute) {
                            $id_product_attribute = Db::getInstance()->getValue('SELECT id_product_attribute FROM (
                                    SELECT
                                        pac.id_product_attribute,
                                        GROUP_CONCAT(CONCAT_WS(":", agl.name, al.name) ORDER BY agl.name, al.name SEPARATOR "' . $this->multivalueSeparator . '") `values`
                                    FROM ' . _DB_PREFIX_ . 'product_attribute_combination pac
                                    JOIN ' . _DB_PREFIX_ . 'product_attribute pa ON pac.id_product_attribute = pa.id_product_attribute
                                    JOIN ' . _DB_PREFIX_ . 'attribute a ON pac.id_attribute = a.id_attribute
                                    JOIN ' . _DB_PREFIX_ . 'attribute_shop ash ON a.id_attribute = ash.id_attribute AND ash.id_shop = ' . $id_join_shop . '
                                    JOIN ' . _DB_PREFIX_ . 'attribute_lang al ON a.id_attribute = al.id_attribute AND al.id_lang = ' . $id_lang . '
                                    JOIN ' . _DB_PREFIX_ . 'attribute_group ag ON a.id_attribute_group = ag.id_attribute_group
                                    JOIN ' . _DB_PREFIX_ . 'attribute_group_shop agsh ON a.id_attribute_group = agsh.id_attribute_group AND agsh.id_shop = ' . $id_join_shop . '
                                    JOIN ' . _DB_PREFIX_ . 'attribute_group_lang agl ON a.id_attribute_group = agl.id_attribute_group AND agl.id_lang = ' . $id_lang . '
                                    WHERE pa.`id_product` = ' . (int) $id_product . '
                                    GROUP BY pac.id_product_attribute
                                    HAVING `values` = "' . str_replace('"', '\"', $info['attribute']) . '") tmp', false);
                        }
                    }
                    if (!$id_product_attribute) {
                        $id_product_attribute = Db::getInstance()->getValue('
                                    SELECT pa.id_product_attribute
                                    FROM `' . _DB_PREFIX_ . 'product_attribute` pa
                                    WHERE `id_product` = ' . (int) $id_product . ' AND `reference` = \'' . pSQL($info['product_attribute_reference']) . '\'', false);
                    }
                }
            }
        }

        $info['id'] = $id_pack;
        $info['id_product'] = $id_product;
        $info['id_product_attribute'] = $id_product_attribute;
        return array(
            'id' => $id_pack,
            'id_product' => $id_product,
            'id_product_attribute' => $id_product_attribute
        );
    }

    protected function packImportOne($info, $default_language, $id_lang, $force_id, $shop_is_feature_active, &$crossStepsVariables, $validateOnly = false)
    {
        if (!$shop_is_feature_active) {
            //            $info['shop'] = 1;
            $info['shop'] = (int) Configuration::get('PS_SHOP_DEFAULT');
        } elseif (empty($info['shop'])) {
            //            $info['shop'] = implode($this->multivalueSeparator, Shop::getContextListShopID());
            $info['shop'] = implode($this->multivalueSeparator, $this->shops);
        }

        // Get shops for each attributes
        $info['shop'] = explode($this->multivalueSeparator, $info['shop']);

        $id_shop_list = [];
        if (is_array($info['shop']) && count($info['shop'])) {
            foreach ($info['shop'] as $shop) {
                if (!empty($shop) && !is_numeric($shop)) {
                    $id_shop_list[] = Shop::getIdByName($shop);
                } elseif (!empty($shop)) {
                    $id_shop_list[] = $shop;
                }
            }
        }

        $id_product = null;
        $id_product_attribute = 0;
        $pack = $this->getPackUpdateOrInsertion($info, $id_lang, $id_shop_list);
        if ($this->targetAction == 'insert') {
            if ((!$id_pack = $pack['id']) || (!$id_product = $pack['id_product']) || ($id_product_attribute = $pack['id_product_attribute']) === false) {
                $this->warnings[] = $this->module->l('No pack found with these parameters to insert', 'EIImport')
                        . ':<br> <pre>' . print_r($info, true);
                return false;
            }
            if (!$validateOnly) {
                if ($this->deleteOtherProducts) {
                    if (!isset($crossStepsVariables['deleted_packs'])) {
                        $crossStepsVariables['deleted_packs'] = [];
                    }
                    if (!in_array($id_pack, $crossStepsVariables['deleted_packs'])) {
                        Db::getInstance()->execute('
                            DELETE FROM `' . _DB_PREFIX_ . 'pack`
                            WHERE id_product_pack = ' . (int) $id_pack);
                        $crossStepsVariables['deleted_packs'][] = $id_pack;
                    }
                }
                Db::getInstance()->execute('
                    INSERT IGNORE INTO 
                        `' . _DB_PREFIX_ . 'pack`
                    VALUES ('
                        . (int) $id_pack . ', '
                        . (int) $id_product . ', '
                        . (int) $id_product_attribute . ', '
                        . (int) $info['quantity']
                        . ')');
                Db::getInstance()->execute('
                    UPDATE `' . _DB_PREFIX_ . 'product`
                    SET `cache_is_pack` = 1 
                    WHERE id_product = ' . (int) $id_pack);
            }
        } elseif ($this->targetAction == 'update') {
            if ((!$id_pack = $pack['id']) || (!$id_product = $pack['id_product']) || ($id_product_attribute = $pack['id_product_attribute']) === false) {
                $this->warnings[] = $this->module->l('No pack found with these parameters to update', 'EIImport')
                        . ':<br> <pre>' . print_r($info, true);
                return false;
            }
            if (!$validateOnly) {
                if (isset($info['quantity'])) {
                    if (trim($info['quantity']) != '' || !self::$keepOldValue) {
                        Db::getInstance()->execute('
                                UPDATE `' . _DB_PREFIX_ . 'pack`
                                SET quantity = ' . (int) $info['quantity'] . '
                                WHERE id_product_pack = ' . (int) $id_pack
                                . ' AND id_product_item = ' . (int) $id_product
                                . ' AND id_product_attribute_item = ' . (int) $id_product_attribute);
                    }
                }
            }
        } else {
            if ((!$id_pack = $pack['id']) || (!$id_product = $pack['id_product']) || ($id_product_attribute = $pack['id_product_attribute']) === false) {
                $this->warnings[] = $this->module->l('No pack found with these parameters to update/insert', 'EIImport')
                        . ':<br> <pre>' . print_r($info, true);
                return false;
            }
            if (!$validateOnly) {
                // Update
                if (isset($info['quantity'])) {
                    if (trim($info['quantity']) != '' || !self::$keepOldValue) {
                        Db::getInstance()->execute('
                                UPDATE `' . _DB_PREFIX_ . 'pack`
                                SET quantity = ' . (int) $info['quantity'] . '
                                WHERE id_product_pack = ' . (int) $id_pack
                                . ' AND id_product_item = ' . (int) $id_product
                                . ' AND id_product_attribute_item = ' . (int) $id_product_attribute);
                    }
                }
                // Insert
                if ($this->deleteOtherProducts) {
                    if (!isset($crossStepsVariables['deleted_packs'])) {
                        $crossStepsVariables['deleted_packs'] = [];
                    }
                    if (!in_array($id_pack, $crossStepsVariables['deleted_packs'])) {
                        Db::getInstance()->execute('
                            DELETE FROM `' . _DB_PREFIX_ . 'pack`
                            WHERE id_product_pack = ' . (int) $id_pack);
                        $crossStepsVariables['deleted_packs'][] = $id_pack;
                    }
                }
                Db::getInstance()->execute('
                    INSERT IGNORE INTO 
                        `' . _DB_PREFIX_ . 'pack`
                    VALUES ('
                        . (int) $id_pack . ', '
                        . (int) $id_product . ', '
                        . (int) $id_product_attribute . ', '
                        . (int) $info['quantity']
                        . ')');
                Db::getInstance()->execute('
                    UPDATE `' . _DB_PREFIX_ . 'product`
                    SET `cache_is_pack` = 1 
                    WHERE id_product = ' . (int) $id_pack);
            }
        }
    }

    public function combinationImport($offset = false, $limit = false, &$crossStepsVariables = false, $validateOnly = false)
    {
        $default_language = Configuration::get('PS_LANG_DEFAULT');
        $id_lang = Tools::getValue('language');
        if (!Validate::isUnsignedId($id_lang)) {
            $id_lang = $default_language;
        }

        $groups = [];
        if ($crossStepsVariables !== false && array_key_exists('groups', $crossStepsVariables)) {
            $groups = $crossStepsVariables['groups'];
        }
        foreach (AttributeGroup::getAttributesGroups($id_lang) as $group) {
            $groups[$group['name']] = (int) $group['id_attribute_group'];
        }

        $attributes = [];
        if ($crossStepsVariables !== false && array_key_exists('attributes', $crossStepsVariables)) {
            $attributes = $crossStepsVariables['attributes'];
        }
        foreach (EIAttribute::getAttributes($id_lang) as $attribute) {
            $attributes[$attribute['attribute_group'] . '_' . $attribute['name']] = (int) $attribute['id_attribute'];
        }

        $this->receiveColumns();
        $handle = $this->openCsvFile($offset);
        if (!$handle) {
            return false;
        }

        self::setLocale();

        $force_id = Tools::getValue('keep_id');
        $regenerate = Tools::getValue('regenerate');
        $shop_is_feature_active = Shop::isFeatureActive();

        $line_count = 0;
        for ($current_line = 0; ($line = fgetcsv($handle, MAX_LINE_SIZE, $this->separator, $this->enclosure)) && (!$limit || $current_line < $limit); $current_line++) {
            $line_count++;
            $this->row = $offset + $line_count + 1;
            if ($this->convert) {
                $line = $this->utf8EncodeArray($line);
            }

            $isEmpty = true;
            foreach ($line as $ln) {
                if ($ln) {
                    $isEmpty = false;
                    break;
                }
            }

            if ($isEmpty) {
                $this->warnings[] = $this->module->l('There is an empty row in the file that won\'t be imported.', 'EIImport');

                continue;
            }

            $info = self::getMaskedRow($line);
            $info = array_map('trim', $info);

            $this->combinationImportOne(
                    $info,
                    $default_language,
                    $id_lang,
                    $force_id,
                    $groups, // by ref
                    $attributes, // by ref
                    $regenerate,
                    $shop_is_feature_active,
                    $validateOnly
            );
        }
        $this->closeCsvFile($handle);

        if ($crossStepsVariables !== false) {
            $crossStepsVariables['groups'] = $groups;
            $crossStepsVariables['attributes'] = $attributes;
        }

        return $line_count;
    }

    private function getCombinationUpdate(&$info, $id_lang, $id_shop_list)
    {
        $id_product_attribute = $id_product = null;
        if ($this->updateBy == 'id' && !empty($info['id'])) {
            $comb = Db::getInstance()->getRow('
                        SELECT pa.id_product_attribute, pa.id_product
                        FROM `' . _DB_PREFIX_ . 'product_attribute` pa
                        WHERE `id_product_attribute` = ' . (int) $info['id'], false);
            if ($comb) {
                $id_product_attribute = $comb['id_product_attribute'];
                if (!empty($info['id_product'])) {
                    $id_product = $info['id_product'];
                } elseif (!empty($info['product_name']) || !empty($info['product_reference'])) {
                    if ($data = Db::getInstance()->executeS('
                            SELECT p.*
                            FROM `' . _DB_PREFIX_ . 'product` p
                            LEFT JOIN `' . _DB_PREFIX_ . 'product_lang` pl
                                ON (p.`id_product` = pl.`id_product`
                                AND `id_lang` = ' . (int) $id_lang . Shop::addSqlRestrictionOnLang('pl') . ')
                            WHERE 1' . (!empty($info['product_name']) ? ' AND `name` = \'' . pSQL($info['product_name']) . '\'' : '')
                            . (!empty($info['product_reference']) ? ' AND `reference` = \'' . pSQL($info['product_reference']) . '\'' : ''))) {
                        
                    } elseif (!empty($info['product_reference']) && $data = Db::getInstance()->executeS('
                            SELECT p.*
                            FROM `' . _DB_PREFIX_ . 'product` p
                            WHERE `reference` = \'' . pSQL($info['product_reference']) . '\'')) {
                        
                    } elseif (!empty($info['product_name']) && $data = Db::getInstance()->executeS('
                            SELECT p.*
                            FROM `' . _DB_PREFIX_ . 'product` p
                            LEFT JOIN `' . _DB_PREFIX_ . 'product_lang` pl
                                ON (p.`id_product` = pl.`id_product`
                                AND `id_lang` = ' . (int) $id_lang . Shop::addSqlRestrictionOnLang('pl') . ')
                            WHERE `name` = \'' . pSQL($info['product_name']) . '\'')) {
                        
                    }
                    if ($data) {
                        $found = false;
                        foreach ($data as $d) {
                            if ($d['id_product'] == $info['id_product']) {
                                $data = $d;
                                $found = true;
                                break;
                            }
                        }
                        if (!$found) {
                            $data = $data[0];
                        }
                    }
                    if (!empty($data['id_product'])) {
                        $id_product = $data['id_product'];
                    }
                } else {
                    $id_product = $comb['id_product'];
                }
            }
        } else {
            if (!empty($info['product_name']) || !empty($info['product_reference'])) {
                if ($data = Db::getInstance()->executeS('
                            SELECT p.*
                            FROM `' . _DB_PREFIX_ . 'product` p
                            LEFT JOIN `' . _DB_PREFIX_ . 'product_lang` pl
                                ON (p.`id_product` = pl.`id_product`
                                AND `id_lang` = ' . (int) $id_lang . Shop::addSqlRestrictionOnLang('pl') . ')
                            WHERE 1' . (!empty($info['product_name']) ? ' AND `name` = \'' . pSQL($info['product_name']) . '\'' : '')
                        . (!empty($info['product_reference']) ? ' AND `reference` = \'' . pSQL($info['product_reference']) . '\'' : ''))) {
                    
                } elseif (!empty($info['product_reference']) && $data = Db::getInstance()->executeS('
                            SELECT p.*
                            FROM `' . _DB_PREFIX_ . 'product` p
                            WHERE `reference` = \'' . pSQL($info['product_reference']) . '\'')) {
                    
                } elseif (!empty($info['product_name']) && $data = Db::getInstance()->executeS('
                            SELECT p.*
                            FROM `' . _DB_PREFIX_ . 'product` p
                            LEFT JOIN `' . _DB_PREFIX_ . 'product_lang` pl
                                ON (p.`id_product` = pl.`id_product`
                                AND `id_lang` = ' . (int) $id_lang . Shop::addSqlRestrictionOnLang('pl') . ')
                            WHERE `name` = \'' . pSQL($info['product_name']) . '\'')) {
                    
                }
                if ($data) {
                    $found = false;
                    foreach ($data as $d) {
                        if ($d['id_product'] == $info['id_product']) {
                            $data = $d;
                            $found = true;
                            break;
                        }
                    }
                    if (!$found) {
                        $data = $data[0];
                    }
                }
                if (!empty($data['id_product'])) {
                    $id_product = $data['id_product'];
                } elseif (!empty($info['id_product'])) {
                    $id_product = $info['id_product'];
                }
            } elseif (!empty($info['id_product'])) {
                $id_product = $info['id_product'];
            }

            if ($this->updateBy == 'reference' && !empty($info['reference'])) {
                $comb = Db::getInstance()->getRow('
                        SELECT pa.id_product_attribute, pa.id_product
                        FROM `' . _DB_PREFIX_ . 'product_attribute` pa
                        WHERE `reference` = \'' . pSQL($info['reference']) . '\''
                        . (!empty($id_product) ? ' AND `id_product` = \'' . (int) $id_product . '\'' : ''), false);
                $id_product_attribute = $comb['id_product_attribute'];
                if (!$id_product) {
                    $id_product = $comb['id_product'];
                }
                if (!$id_product_attribute) {
                    $comb = Db::getInstance()->getRow('
                        SELECT pa.id_product_attribute, pa.id_product
                        FROM `' . _DB_PREFIX_ . 'product_attribute` pa
                        WHERE `reference` = \'' . pSQL($info['reference']) . '\'', false);
                    if ($comb) {
                        $id_product_attribute = $comb['id_product_attribute'];
                        if (!$id_product) {
                            $id_product = $comb['id_product'];
                        }
                    }
                }
            } elseif ($this->updateBy == 'attribute' && !empty($info['attribute'])) {
                $id_join_shop = count($id_shop_list) == 1 ? $id_shop_list[0] : $this->context->shop->id;
                $comb = Db::getInstance()->getRow('SELECT id_product_attribute FROM (
                                SELECT
                                    pac.id_product_attribute, pa.id_product,
                                    GROUP_CONCAT(IF(a.color = "", al.name, CONCAT_WS(":", al.name, a.color)) ORDER BY agl.name, al.name SEPARATOR "' . $this->multivalueSeparator . '") `values`
                                FROM ' . _DB_PREFIX_ . 'product_attribute_combination pac
                                JOIN ' . _DB_PREFIX_ . 'product_attribute pa ON pac.id_product_attribute = pa.id_product_attribute
                                JOIN ' . _DB_PREFIX_ . 'attribute a ON pac.id_attribute = a.id_attribute
                                JOIN ' . _DB_PREFIX_ . 'attribute_shop ash ON a.id_attribute = ash.id_attribute AND ash.id_shop = ' . $id_join_shop . '
                                JOIN ' . _DB_PREFIX_ . 'attribute_lang al ON a.id_attribute = al.id_attribute AND al.id_lang = ' . $id_lang . '
                                JOIN ' . _DB_PREFIX_ . 'attribute_group ag ON a.id_attribute_group = ag.id_attribute_group
                                JOIN ' . _DB_PREFIX_ . 'attribute_group_shop agsh ON a.id_attribute_group = agsh.id_attribute_group AND agsh.id_shop = ' . $id_join_shop . '
                                JOIN ' . _DB_PREFIX_ . 'attribute_group_lang agl ON a.id_attribute_group = agl.id_attribute_group AND agl.id_lang = ' . $id_lang . '
                                WHERE 1' . (!empty($id_product) ? ' AND pa.`id_product` = ' . (int) $id_product : '') . '
                                GROUP BY pac.id_product_attribute
                                HAVING `values` = "' . str_replace('"', '\"', $info['attribute']) . '") tmp', false);
                $id_product_attribute = $comb['id_product_attribute'];
                if (!$id_product) {
                    $id_product = $comb['id_product'];
                }
                if (!$id_product_attribute) {
                    $comb = Db::getInstance()->getRow('SELECT
                                    pac.id_product_attribute, pa.id_product,
                                    GROUP_CONCAT(IF(a.color = "", al.name, CONCAT_WS(":", al.name, a.color)) ORDER BY agl.name, al.name SEPARATOR "' . $this->multivalueSeparator . '") `values`
                                FROM ' . _DB_PREFIX_ . 'product_attribute_combination pac
                                JOIN ' . _DB_PREFIX_ . 'product_attribute pa ON pac.id_product_attribute = pa.id_product_attribute
                                JOIN ' . _DB_PREFIX_ . 'attribute a ON pac.id_attribute = a.id_attribute
                                JOIN ' . _DB_PREFIX_ . 'attribute_shop ash ON a.id_attribute = ash.id_attribute AND ash.id_shop = ' . $id_join_shop . '
                                JOIN ' . _DB_PREFIX_ . 'attribute_lang al ON a.id_attribute = al.id_attribute AND al.id_lang = ' . $id_lang . '
                                JOIN ' . _DB_PREFIX_ . 'attribute_group ag ON a.id_attribute_group = ag.id_attribute_group
                                JOIN ' . _DB_PREFIX_ . 'attribute_group_shop agsh ON a.id_attribute_group = agsh.id_attribute_group AND agsh.id_shop = ' . $id_join_shop . '
                                JOIN ' . _DB_PREFIX_ . 'attribute_group_lang agl ON a.id_attribute_group = agl.id_attribute_group AND agl.id_lang = ' . $id_lang . '
                                GROUP BY pac.id_product_attribute
                                HAVING `values` = "' . str_replace('"', '\"', $info['attribute']) . '"', false);
                    if ($comb) {
                        $id_product_attribute = $comb['id_product_attribute'];
                        if (!$id_product) {
                            $id_product = $comb['id_product'];
                        }
                    }
                }
            }
        }

        return array(
            'id_product_attribute' => $id_product_attribute,
            'id_product' => $id_product
        );
    }

    private function getCombinationInsertion(&$info, $id_lang, $force_id, $id_shop_list)
    {
        if ($force_id && !empty($info['id']) && ObjectModel::existsInDatabase((int) $info['id'], 'product_attribute')) {
            return false;
        }

        // Get product ID
        $id_product = null;
        if (!empty($info['id_product'])) {
            $id_product = $info['id_product'];
        } elseif (!empty($info['product_name']) || !empty($info['product_reference'])) {
            if ($data = Db::getInstance()->executeS('
                    SELECT p.*
                    FROM `' . _DB_PREFIX_ . 'product` p
                    LEFT JOIN `' . _DB_PREFIX_ . 'product_lang` pl
                        ON (p.`id_product` = pl.`id_product`
                        AND `id_lang` = ' . (int) $id_lang . Shop::addSqlRestrictionOnLang('pl') . ')
                    WHERE 1' . (!empty($info['product_name']) ? ' AND `name` = \'' . pSQL($info['product_name']) . '\'' : '')
                    . (!empty($info['product_reference']) ? ' AND `reference` = \'' . pSQL($info['product_reference']) . '\'' : ''))) {
                
            } elseif (!empty($info['product_reference']) && $data = Db::getInstance()->executeS('
                    SELECT p.*
                    FROM `' . _DB_PREFIX_ . 'product` p
                    WHERE `reference` = \'' . pSQL($info['product_reference']) . '\'')) {
                
            } elseif (!empty($info['product_name']) && $data = Db::getInstance()->executeS('
                    SELECT p.*
                    FROM `' . _DB_PREFIX_ . 'product` p
                    LEFT JOIN `' . _DB_PREFIX_ . 'product_lang` pl
                        ON (p.`id_product` = pl.`id_product`
                        AND `id_lang` = ' . (int) $id_lang . Shop::addSqlRestrictionOnLang('pl') . ')
                    WHERE `name` = \'' . pSQL($info['product_name']) . '\'')) {
                
            }
            if ($data) {
                $found = false;
                foreach ($data as $d) {
                    if ($d['id_product'] == $info['id_product']) {
                        $data = $d;
                        $found = true;
                        break;
                    }
                }
                if (!$found) {
                    $data = $data[0];
                }
            }
            if (!empty($data['id_product'])) {
                $id_product = $data['id_product'];
            } else {
                $this->warnings[] = sprintf(
                        $this->module->l('No product (Name: %1$s, Ref: %2$s) found for combination %3$s.', 'EIImport'),
                        Tools::htmlentitiesUTF8(isset($info['product_name']) ? $info['product_name'] : ''),
                        Tools::htmlentitiesUTF8(isset($info['product_reference']) ? $info['product_reference'] : ''),
                        Tools::htmlentitiesUTF8(isset($info['group_name']) ? $info['group_name'] : '')
                );
                return false;
            }
        } else {
            return false;
        }

        $id_join_shop = count($id_shop_list) == 1 ? $id_shop_list[0] : $this->context->shop->id;
        if ($this->nameExists == 'ignore' || $this->nameExists == 'insert_if_attribute_exists') {
            if (!empty($info['reference']) && Db::getInstance()->getRow('
                        SELECT pa.*
                        FROM `' . _DB_PREFIX_ . 'product_attribute` pa
                        WHERE id_product = ' . (int) $id_product . ' AND `reference` = \'' . pSQL($info['reference']) . '\'')) {
                return false;
            }
        }
        if ($this->nameExists == 'ignore' || $this->nameExists == 'insert_if_reference_exists') {
            if (!empty($info['attribute']) && Db::getInstance()->getRow('SELECT
                                    pac.id_product_attribute,
                                    GROUP_CONCAT(IF(a.color = "", al.name, CONCAT_WS(":", al.name, a.color)) ORDER BY agl.name, al.name SEPARATOR "' . $this->multivalueSeparator . '") `values`
                                FROM ' . _DB_PREFIX_ . 'product_attribute_combination pac
                                JOIN ' . _DB_PREFIX_ . 'product_attribute pa ON pac.id_product_attribute = pa.id_product_attribute
                                JOIN ' . _DB_PREFIX_ . 'attribute a ON pac.id_attribute = a.id_attribute
                                JOIN ' . _DB_PREFIX_ . 'attribute_shop ash ON a.id_attribute = ash.id_attribute AND ash.id_shop = ' . $id_join_shop . '
                                JOIN ' . _DB_PREFIX_ . 'attribute_lang al ON a.id_attribute = al.id_attribute AND al.id_lang = ' . $id_lang . '
                                JOIN ' . _DB_PREFIX_ . 'attribute_group ag ON a.id_attribute_group = ag.id_attribute_group
                                JOIN ' . _DB_PREFIX_ . 'attribute_group_shop agsh ON a.id_attribute_group = agsh.id_attribute_group AND agsh.id_shop = ' . $id_join_shop . '
                                JOIN ' . _DB_PREFIX_ . 'attribute_group_lang agl ON a.id_attribute_group = agl.id_attribute_group AND agl.id_lang = ' . $id_lang . '
                                WHERE pa.id_product = ' . (int) $id_product . '
                                GROUP BY pac.id_product_attribute
                                HAVING `values` = "' . str_replace('"', '\"', $info['attribute']) . '"')) {
                return false;
            }
        }

        if (!$force_id) {
            unset($info['id']);
        }

        if ((int) $id_product && !ObjectModel::existsInDatabase((int) $id_product, 'product')) {
            return false;
        }

        return $id_product;
    }

    protected function combinationImportOne($info, $default_language, $id_lang, $force_id, &$groups, &$attributes, $regenerate, $shop_is_feature_active, $validateOnly = false)
    {
        if (!$shop_is_feature_active) {
            //            $info['shop'] = 1;
            $info['shop'] = (int) Configuration::get('PS_SHOP_DEFAULT');
        } elseif (empty($info['shop'])) {
            //            $info['shop'] = implode($this->multivalueSeparator, Shop::getContextListShopID());
            $info['shop'] = implode($this->multivalueSeparator, $this->shops);
        }

        // Get shops for each attributes
        $shops = explode($this->multivalueSeparator, $info['shop']);

        $id_shop_list = [];
        if (is_array($shops)) {
            foreach ($shops as $shop) {
                if (!empty($shop) && !is_numeric($shop)) {
                    $id_shop_list[] = Shop::getIdByName($shop);
                } elseif (!empty($shop)) {
                    $id_shop_list[] = $shop;
                }
            }
        }

        $id_product = $id_product_attribute = null;
        $combination = null;
        if ($this->targetAction == 'insert') {
            if ($id_product = $this->getCombinationInsertion($info, $id_lang, $force_id, $id_shop_list)) {
                $product = new Product((int) $id_product, false, $default_language);
                $combination = new Combination();
                $finalAction = 'insert';
                self::setDefaultValues($info);
            } else {
                return false;
            }
        } elseif ($this->targetAction == 'update') {
            $comb = $this->getCombinationUpdate($info, $id_lang, $id_shop_list);
            if (($id_product_attribute = $comb['id_product_attribute']) && $comb['id_product']) {
                $product = new Product((int) $comb['id_product'], false, $default_language);
                $combination = new Combination((int) $id_product_attribute);
                $finalAction = 'update';
            } else {
                return false;
            }
        } else {
            $comb = $this->getCombinationUpdate($info, $id_lang, $id_shop_list);
            if (($id_product_attribute = $comb['id_product_attribute']) && $comb['id_product']) {
                $product = new Product((int) $comb['id_product'], false, $default_language);
                $combination = new Combination((int) $id_product_attribute);
                $finalAction = 'update';
            } else {
                if ($id_product = $this->getCombinationInsertion($info, $id_lang, $force_id, $id_shop_list)) {
                    $product = new Product((int) $id_product, false, $default_language);
                    $combination = new Combination();
                    $finalAction = 'insert';
                    self::setDefaultValues($info);
                } else {
                    return false;
                }
            }
        }

        // Correct date formats
        if (!empty($info['available_date'])) {
            $info['available_date'] = date("Y-m-d", strtotime($info['available_date']));
            if ($info['available_date'] === '1969-12-31' || Tools::substr($info['available_date'], 0, 1) === '-') {
                $info['available_date'] = '0000-00-00';
            }
        }

        self::arrayWalk($info, [self::class, 'fillInfo'], $combination);

        // Delete existing combination
//        if (!$validateOnly) {
//            $product->deleteProductAttributes();
//        }

        $id_image = [];
        if (isset($info['image_url']) && $info['image_url']) {
            $info['image_url'] = explode($this->multivalueSeparator, $info['image_url']);

            if (is_array($info['image_url']) && count($info['image_url'])) {
                $product_images = Image::getImages($this->context->language->id, $product->id);
                $product_has_images = (bool) $product_images;
                foreach ($info['image_url'] as $key => $url) {
                    $url = trim($url);

                    $id_img = 0;
                    if (!$validateOnly) {
                        foreach ($product_images as $img) {
                            if ($url == $this->getImageLink($img['id_image'])) {
                                $id_img = $img['id_image'];
                                break;
                            }
                        }
                        $tmpfile1 = _IP_TMP_IMG_DIR_ . uniqid() . '.jpg';
                        $tmpfile2 = _IP_TMP_IMG_DIR_ . uniqid() . '_ip.jpg';
                        $context = stream_context_create(array('http' => array('user_agent' => 'custom user agent string')));
                        if (self::$imageDownloadType === self::DOWNLOAD_IMG_WITHOUT_CONTEXT) {
                            file_put_contents($tmpfile1, Tools::file_get_contents($url, false, $context, 5, true));
                        } else {
                            $arrContextOptions = array(
                                "ssl" => array(
                                    "verify_peer" => false,
                                    "verify_peer_name" => false,
                                ),
                            );
                            file_put_contents($tmpfile1, file_get_contents($url, false, stream_context_create($arrContextOptions)));
                        }
                        if (filesize($tmpfile1) == 0) {
                            $this->warnings[] = sprintf($this->module->l('Error copying image: %1$s', 'EIImport'), $url);
                            @unlink($tmpfile1);
                        } else {
                            $tgt_width = $tgt_height = 0;
                            $src_width = $src_height = 0;
                            $err = 0;
                            ImageManager::resize($tmpfile1, $tmpfile2, null, null, 'jpg', false, $err, $tgt_width, $tgt_height, 5, $src_width, $src_height);
                            if (!$id_img) {
                                foreach ($product_images as $img) {
                                    $existing_image = realpath(_PS_PROD_IMG_DIR_ . Image::getImgFolderStatic($img['id_image']) . $img['id_image'] . '.jpg');
                                    if (md5(file_get_contents($tmpfile2)) == md5(file_get_contents($existing_image))) {
                                        $id_img = $img['id_image'];
                                        break;
                                    }
                                }
                            }
                            @unlink($tmpfile1);

                            if ($id_img) {
                                $image = new Image((int) $id_img);
                                $image->id_product = (int) $product->id;
                            } else {
                                $image = new Image();
                                $image->id_product = (int) $product->id;
                                $image->position = Image::getHighestPosition($product->id) + 1;
                                $image->cover = !$product_has_images ? true : false;
                            }

                            if (isset($info['image_alt'])) {
                                $alt = self::split($info['image_alt']);
                                if (isset($alt[$key]) && Tools::strlen($alt[$key]) > 0) {
                                    $alt = self::createMultiLangField($alt[$key]);
                                    $image->legend = $alt;
                                }
                            }

                            $field_error = $image->validateFields(false, true);
                            $lang_field_error = $image->validateFieldsLang(false, true);

                            if ($field_error === true && $lang_field_error === true && !$validateOnly) {
                                if (isset($image->id) && ObjectModel::existsInDatabase((int) $image->id, 'image')) {
                                    $image->update();
                                    $exists = true;
                                } else {
                                    $image->add();
                                    $exists = false;
                                    $product_has_images = true;
                                }
                                $image->associateTo($id_shop_list);
                                // FIXME: 2s/image !
                                if (!self::copyImg($product->id, $image->id, $url, 'products', $regenerate, $exists, $tmpfile2)) {
                                    $this->warnings[] = sprintf(
                                            $this->module->l('Error copying image: %1$s', 'EIImport'),
                                            Tools::htmlentitiesUTF8($url)
                                    );
                                    $image->delete();
                                } else {
                                    $id_image[] = (int) $image->id;
                                }
                                // until here
                            } else {
                                if (!$validateOnly) {
                                    $this->warnings[] = sprintf(
                                            $this->module->l('%1$s cannot be saved.', 'EIImport'),
                                            isset($image->id_product) ? ' (' . Tools::htmlentitiesUTF8($image->id_product) . ')' : ''
                                    );
                                }
                                if ($field_error !== true || $lang_field_error !== true) {
                                    $this->errors[] = ($field_error !== true ? $field_error : '')
                                            . ($lang_field_error !== true ? $lang_field_error : '')
                                            . '<br> ' . $this->module->l('Location', 'EIImport') . ': ' . __CLASS__ . ':' . __LINE__ . '.'
                                            . '<br> ' . $this->module->l('Row number in file', 'EIImport') . ": {$this->row}. " . $this->module->l('Problematic row', 'EIImport') . ': <pre>' . print_r($info, true);
                                }
                            }
                        }
                    }
                }
            }
        } elseif (isset($info['image_position']) && $info['image_position']) {
            $info['image_position'] = explode($this->multivalueSeparator, $info['image_position']);

            if (is_array($info['image_position']) && count($info['image_position'])) {
                foreach ($info['image_position'] as $position) {
                    // choose images from product by position
                    $images = $product->getImages($default_language);

                    if ($images) {
                        foreach ($images as $row) {
                            if ($row['position'] == (int) $position) {
                                $id_image[] = (int) $row['id_image'];

                                break;
                            }
                        }
                    }
                    if (empty($id_image)) {
                        $this->warnings[] = sprintf(
                                $this->module->l('No image was found for combination with id_product = %s and image position = %s.', 'EIImport'),
                                Tools::htmlentitiesUTF8($product->id),
                                (int) $position
                        );
                    }
                }
            }
        }

        $id_attribute_group = 0;
        // groups
        $groups_attributes = [];
        if (isset($info['group_name'])) {
            foreach (explode($this->multivalueSeparator, $info['group_name']) as $key => $group) {
                if (empty($group)) {
                    continue;
                }
                // Now no need to split, because the group name and the group type are imported in separate columns
//                $tab_group = preg_split('~:(?=[^:]*$)~', $group);

                $group = trim($group);

                if (!empty($info['group_public_name']) && trim($info['group_public_name'])) {
                    $public_name = explode($this->multivalueSeparator, trim($info['group_public_name']));
                    if (isset($public_name[$key])) {
                        $public_name = $public_name[$key];
                    } else {
                        $public_name = $group;
                    }
                } else {
                    $public_name = $group;
                }

                if (!empty($info['group_type']) && trim($info['group_type'])) {
                    $type = explode($this->multivalueSeparator, trim($info['group_type']));
                    if (isset($type[$key])) {
                        $type = $type[$key];
                    } else {
                        $type = 'select';
                    }
                } else {
                    $type = 'select';
                }

                // sets group
                $groups_attributes[$key]['group'] = $group;

                // if position is filled
                if (isset($info['group_position'])) {
                    $position = trim($info['group_position']);
                } else {
                    $position = false;
                }

                if (!isset($groups[$group])) {
                    $obj = new AttributeGroup();
                    $obj->group_type = pSQL($type);
                    $obj->is_color_group = $obj->group_type == 'color';
                    $obj->name[$default_language] = $group;
                    $obj->public_name[$default_language] = $public_name;
                    $obj->position = !$position ? AttributeGroup::getHigherPosition() + 1 : $position;

                    if (($field_error = $obj->validateFields(false, true)) === true &&
                            ($lang_field_error = $obj->validateFieldsLang(false, true)) === true
                    ) {
                        // here, cannot avoid attributeGroup insertion to avoid an error during validation step.
                        //if (!$validateOnly) {
                        $obj->add();
                        $obj->associateTo($id_shop_list);
                        $groups[$group] = $obj->id;
                        //}
                    } else {
                        $this->errors[] = ($field_error !== true ? $field_error : '') . (isset($lang_field_error) && $lang_field_error !== true ? $lang_field_error : '')
                                . '<br> ' . $this->module->l('Location', 'EIImport') . ': ' . __CLASS__ . ':' . __LINE__ . '.'
                                . '<br> ' . $this->module->l('Row number in file', 'EIImport') . ": {$this->row}. " . $this->module->l('Problematic row', 'EIImport') . ': <pre>' . print_r($info, true);
                    }

                    // fills groups attributes
                    $id_attribute_group = $obj->id;
                    $groups_attributes[$key]['id'] = $id_attribute_group;
                } else {
                    // already exists

                    $id_attribute_group = $groups[$group];
                    $groups_attributes[$key]['id'] = $id_attribute_group;
                }
            }
        }

        // inits attribute
        $attributes_to_add = [];

        // for each attribute
        if (isset($info['attribute'])) {
            foreach (explode($this->multivalueSeparator, $info['attribute']) as $key => $attribute) {
                if (empty($attribute)) {
                    continue;
                }
                // Split by the last colon. Only two parts will be received
                $tab_attribute = preg_split('~:(?=[^:]*$)~', $attribute);
                $attribute = trim($tab_attribute[0]);
                // if position is filled
                if (isset($info['attribute_position'])) {
                    $position = trim($info['attribute_position']);
                } else {
                    $position = false;
                }

                if (isset($groups_attributes[$key])) {
                    $group = $groups_attributes[$key]['group'];
                    if (!isset($attributes[$group . '_' . $attribute]) && count($groups_attributes[$key]) == 2) {
                        $id_attribute_group = $groups_attributes[$key]['id'];
                        $obj = new EIAttribute();
                        // sets the proper id (corresponding to the right key)
                        $obj->id_attribute_group = $groups_attributes[$key]['id'];
                        $obj->name[$default_language] = str_replace('\n', '', str_replace('\r', '', $attribute));
                        $obj->color = $tab_attribute[1];
                        $obj->position = !$position && isset($groups[$group]) ? EIAttribute::getHigherPosition($groups[$group]) + 1 : $position;

                        if (($field_error = $obj->validateFields(false, true)) === true &&
                                ($lang_field_error = $obj->validateFieldsLang(false, true)) === true
                        ) {
                            if (!$validateOnly) {
                                $obj->add();
                                $obj->associateTo($id_shop_list);
                                $attributes[$group . '_' . $attribute] = $obj->id;
                            }
                        } else {
                            $this->errors[] = ($field_error !== true ? $field_error : '') . (isset($lang_field_error) && $lang_field_error !== true ? $lang_field_error : '')
                                    . '<br> ' . $this->module->l('Location', 'EIImport') . ': ' . __CLASS__ . ':' . __LINE__ . '.'
                                    . '<br> ' . $this->module->l('Row number in file', 'EIImport') . ": {$this->row}. " . $this->module->l('Problematic row', 'EIImport') . ': <pre>' . print_r($info, true);
                        }
                    }

                    // fills our attributes array, in order to add the attributes to the product_attribute afterwards
                    if (isset($attributes[$group . '_' . $attribute])) {
                        $attributes_to_add[] = (int) $attributes[$group . '_' . $attribute];
                    }

                    // after insertion, we clean attribute position and group attribute position
                    if (!$validateOnly) {
                        $obj = new EIAttribute();
                        $obj->cleanPositions((int) $id_attribute_group, false);
                        AttributeGroup::cleanPositions();
                    }
                }
            }
        }

//        $id_product_attribute = 0;
        $id_product_attribute_update = false;

        $combination->minimal_quantity = isset($combination->minimal_quantity) && $combination->minimal_quantity ? (int) $combination->minimal_quantity : 1;
        $combination->low_stock_threshold = empty($combination->low_stock_threshold) && '0' != $combination->low_stock_threshold ? null : (int) $combination->low_stock_threshold;
        $combination->low_stock_alert = !empty($combination->low_stock_alert);

        // Convert comma into dot for all floating values
        foreach (Combination::$definition['fields'] as $key => $array) {
            if ($array['type'] == Combination::TYPE_FLOAT && isset($combination->{$key})) {
                $combination->{$key} = str_replace(',', '.', $combination->{$key});
            }
        }

        $combination->available_date = Validate::isDate($combination->available_date) ? $combination->available_date : null;

        if ($combination->ean13 && !Validate::isEan13($combination->ean13)) {
            $this->warnings[] = sprintf(
                    $this->module->l('EAN13 "%1$s" has incorrect value for product with id %2$d.', 'EIImport'),
                    Tools::htmlentitiesUTF8($combination->ean13),
                    Tools::htmlentitiesUTF8($product->id)
            );
            $combination->ean13 = '';
        }
        if ($combination->upc && method_exists('Validate', 'isUpc') && !Validate::isUpc($combination->upc)) {
            $this->warnings[] = sprintf(
                    $this->module->l('UPC "%1$s" has incorrect value for product with id %2$d.', 'EIImport'),
                    Tools::htmlentitiesUTF8($combination->upc),
                    Tools::htmlentitiesUTF8($product->id)
            );
            $combination->upc = '';
        }
        if ($combination->isbn && method_exists('Validate', 'isIsbn') && !Validate::isIsbn($combination->isbn)) {
            $this->warnings[] = sprintf(
                    $this->module->l('ISBN "%1$s" has incorrect value for product with id %2$d.', 'EIImport'),
                    Tools::htmlentitiesUTF8($combination->isbn),
                    Tools::htmlentitiesUTF8($product->id)
            );
            $combination->isbn = '';
        }
        if ($combination->mpn && method_exists('Validate', 'isMpn') && !Validate::isMpn($combination->mpn)) {
            $this->warnings[] = sprintf(
                    $this->module->l('MPN "%1$s" has incorrect value for product with id %2$d.', 'EIImport'),
                    Tools::htmlentitiesUTF8($combination->mpn),
                    Tools::htmlentitiesUTF8($product->id)
            );
            $combination->mpn = '';
        }

        if ($combination->default_on && !$validateOnly) {
            $product->deleteDefaultAttributes();
        }

        // if a reference is specified for this product, get the associate id_product_attribute to UPDATE
        if (!$validateOnly) {
            if ($id_product_attribute) {
                if ($this->targetAction != 'insert') {
                    Db::getInstance()->update(
                            'product_attribute',
                            array(
                                'id_product' => $product->id,
                                'default_on' => null,
                            ),
                            'id_product_attribute = ' . $id_product_attribute,
                            0,
                            true
                    );
                    Db::getInstance()->update(
                            'product_attribute_shop',
                            array(
                                'id_product' => $product->id,
                                'default_on' => null,
                            ),
                            'id_product_attribute = ' . $id_product_attribute . ' AND id_shop IN (' . implode(', ', $shops) . ')',
                            0,
                            true
                    );
                }
                // gets all the combinations of this product
                $attribute_combinations = $product->getAttributeCombinations($default_language);
                foreach ($attribute_combinations as $attribute_combination) {
                    if (in_array($id_product_attribute, $attribute_combination)) {
                        // FIXME: ~3s/declinaison
                        $product->updateAttribute(
                                $id_product_attribute,
                                (float) $combination->wholesale_price,
                                (float) $combination->price,
                                (float) $combination->weight,
                                (float) $combination->unit_price_impact,
                                (Configuration::get('PS_USE_ECOTAX') ? (float) $combination->ecotax : 0),
                                $id_image,
                                (string) $combination->reference,
                                (string) $combination->ean13,
                                ((int) $combination->default_on ? (int) $combination->default_on : null),
                                (string) $combination->location,
                                (string) $combination->upc,
                                (int) $combination->minimal_quantity,
                                $combination->available_date,
                                null,
                                $id_shop_list,
                                !empty($combination->isbn) ? $combination->isbn : '',
                                !empty($combination->low_stock_threshold) ? $combination->low_stock_threshold : null,
                                !empty($combination->low_stock_alert) ? $combination->low_stock_alert : false,
                                !empty($combination->mpn) ? $combination->mpn : null
                        );
                        $id_product_attribute_update = true;
                        if (!empty($info['supplier_reference'])) {
                            Db::getInstance()->update(
                                    'product_attribute',
                                    array('supplier_reference' => $info['supplier_reference']),
                                    'id_product_attribute = ' . $id_product_attribute
                            );
                            $product->addSupplierReference($product->id_supplier, $id_product_attribute, $info['supplier_reference']);
                        }
                        // until here
                    }
                }
            } else {
                $id_product_attribute = $product->addCombinationEntity(
                        (float) $combination->wholesale_price,
                        (float) $combination->price,
                        (float) $combination->weight,
                        (float) $combination->unit_price_impact,
                        (Configuration::get('PS_USE_ECOTAX') ? (float) $combination->ecotax : 0),
                        (int) $combination->quantity,
                        $id_image,
                        (string) $combination->reference,
                        0,
                        (string) $combination->ean13,
                        ((int) $combination->default_on ? (int) $combination->default_on : null),
                        (string) $combination->location,
                        (string) $combination->upc,
                        (int) $combination->minimal_quantity,
                        $id_shop_list,
                        $combination->available_date,
                        !empty($combination->isbn) ? $combination->isbn : '',
                        !empty($combination->low_stock_threshold) ? $combination->low_stock_threshold : null,
                        !empty($combination->low_stock_alert) ? $combination->low_stock_alert : false,
                        !empty($combination->mpn) ? $combination->mpn : null
                );

                if (isset($info['id']) && (int) $info['id'] && $force_id) {
                    Db::getInstance()->update(
                            'product_attribute',
                            array('id_product_attribute' => $info['id']),
                            'id_product_attribute = ' . $id_product_attribute
                    );
                    // Fixes the error "when Keep IDs" is enabled and the primary keys is like 1, 4, 6 (not in natural order), which causes 'duplicate primary key error'
                    Db::getInstance()->execute('ALTER TABLE ' . _DB_PREFIX_ . 'product_attribute AUTO_INCREMENT = ' . ((int) $id_product_attribute + 1));
                    
                    Db::getInstance()->update(
                            'product_attribute_shop',
                            array('id_product_attribute' => $info['id']),
                            'id_product_attribute = ' . $id_product_attribute . ' AND id_shop IN (' . implode(', ', $shops) . ')'
                    );
                    Db::getInstance()->update(
                        'product_attribute_image',
                        array('id_product_attribute' => $info['id']),
                        'id_product_attribute = ' . $id_product_attribute
                    );
                    $id_product_attribute = $info['id'];
                }

                if (!empty($info['supplier_reference'])) {
                    Db::getInstance()->update(
                            'product_attribute',
                            array('supplier_reference' => $info['supplier_reference']),
                            'id_product_attribute = ' . $id_product_attribute
                    );
                    $product->addSupplierReference($product->id_supplier, $id_product_attribute, $info['supplier_reference']);
                }
            }
        }
        
        if ((int) $id_product_attribute) {
            if (!$validateOnly) {
                // now adds the attributes in the attribute_combination table
                if ($id_product_attribute_update && $attributes_to_add) {
                    Db::getInstance()->execute('
						DELETE FROM ' . _DB_PREFIX_ . 'product_attribute_combination
						WHERE id_product_attribute = ' . (int) $id_product_attribute);
                }

                foreach ($attributes_to_add as $attribute_to_add) {
                    Db::getInstance()->execute('
						INSERT IGNORE INTO ' . _DB_PREFIX_ . 'product_attribute_combination (id_attribute, id_product_attribute)
						VALUES (' . (int) $attribute_to_add . ',' . (int) $id_product_attribute . ')', false);
                }
            }

            // set advanced stock management
            if (isset($info['advanced_stock_management'])) {
                if ($info['advanced_stock_management'] != 1 && $info['advanced_stock_management'] != 0) {
                    $this->warnings[] = sprintf(
                            $this->module->l('Advanced stock management has incorrect value. Not set for product with id %d.', 'EIImport'),
                            Tools::htmlentitiesUTF8($product->id)
                    );
                } elseif (!$validateOnly) {
                    $product->setAdvancedStockManagement($info['advanced_stock_management']);
                }
            }

            // Check if warehouse exists
            if (isset($combination->warehouse)) {
                if (!$validateOnly) {
                    Db::getInstance()->execute('
					DELETE
					FROM `' . _DB_PREFIX_ . 'warehouse_product_location`
					WHERE id_product_attribute = ' . (int) $id_product_attribute, false);
                    if ($combination->warehouse) {
                        foreach (explode($this->multivalueSeparator, $combination->warehouse) as $wh) {
                            $warehouse = explode(':', $wh);
                            $id_warehouse = Db::getInstance()->getValue('
					SELECT id_warehouse
					FROM `' . _DB_PREFIX_ . 'warehouse`
					WHERE `reference` = "' . pSQL(trim($warehouse[0])) . '"'
                                    . (!empty($warehouse[1]) ? ' AND `name` = "' . pSQL(trim($warehouse[1])) . '"' : ''), false);
                            if ($id_warehouse) {
                                $warehouse_location_entity = new WarehouseProductLocation();
                                $warehouse_location_entity->id_product = $product->id;
                                $warehouse_location_entity->id_product_attribute = $id_product_attribute;
                                $warehouse_location_entity->id_warehouse = $id_warehouse;
                                if (!empty($warehouse[2])) {
                                    $warehouse_location_entity->location = $warehouse[2];
                                }
                                if (WarehouseProductLocation::getProductLocation($product->id, $id_product_attribute, $id_warehouse) !== false) {
                                    $warehouse_location_entity->update();
                                } else {
                                    $warehouse_location_entity->save();
                                }
                                StockAvailable::synchronize($product->id);
                            } else {
                                $this->warnings[] = sprintf(
                                        $this->module->l('Warehouse did not exist, cannot set on product %s.', 'EIImport'),
                                        Tools::htmlentitiesUTF8($product->name[$default_language])
                                );
                            }
                        }
                    }
                }
            }

            // stock available
            if (isset($info['depends_on_stock'])) {
                if ($info['depends_on_stock'] != 0 && $info['depends_on_stock'] != 1) {
                    $this->warnings[] = sprintf(
                            $this->module->l('Incorrect value for "Depends on stock" for product %s', 'EIImport'),
                            Tools::htmlentitiesUTF8($product->name[$default_language])
                    );
                } elseif (!$validateOnly) {
                    StockAvailable::setProductDependsOnStock($product->id, $info['depends_on_stock'], null, $id_product_attribute);
                }

                // This code allows us to set qty and disable depends on stock
                if (isset($info['quantity']) || $finalAction === 'insert') {
                    // if depends on stock and quantity, add quantity to stock
                    if ($info['depends_on_stock'] == 1) {
                        $stock_manager = StockManagerFactory::getManager();
                        $price = str_replace(',', '.', $info['wholesale_price']);
                        if ($price == '0') {
                            $price = 0.000001;
                        }
                        $price = round((float) $price, 6);
                        $warehouse = new Warehouse($info['warehouse']);
                        if (!$validateOnly && $stock_manager->addProduct((int) $product->id, $id_product_attribute, $warehouse, (int) $info['quantity'], 1, $price, true)) {
                            StockAvailable::synchronize((int) $product->id);
                        }
                    } elseif (!$validateOnly) {
                        if ($shop_is_feature_active) {
                            foreach ($id_shop_list as $shop) {
                                StockAvailable::setQuantity((int) $product->id, $id_product_attribute, (int) $info['quantity'], (int) $shop);
                                if (method_exists('StockAvailable', 'setLocation')) {
                                    StockAvailable::setLocation((int) $product->id, (string) $info['location'], (int) $shop, $id_product_attribute);
                                }
                            }
                        } else {
                            StockAvailable::setQuantity((int) $product->id, $id_product_attribute, (int) $info['quantity'], $this->context->shop->id);
                            if (method_exists('StockAvailable', 'setLocation')) {
                                StockAvailable::setLocation((int) $product->id, (string) $info['location'], $this->context->shop->id, $id_product_attribute);
                            }
                        }
                    }
                }
            } elseif (!$validateOnly && (isset($info['quantity']) || $finalAction === 'insert')) { // if not depends_on_stock set, use normal qty
                if ($shop_is_feature_active) {
                    foreach ($id_shop_list as $shop) {
                        StockAvailable::setQuantity((int) $product->id, $id_product_attribute, (int) $info['quantity'], (int) $shop);
                        if (method_exists('StockAvailable', 'setLocation')) {
                            StockAvailable::setLocation((int) $product->id, (string) $info['location'], (int) $shop, $id_product_attribute);
                        }
                    }
                } else {
                    StockAvailable::setQuantity((int) $product->id, $id_product_attribute, (int) $info['quantity'], $this->context->shop->id);
                    if (method_exists('StockAvailable', 'setLocation')) {
                        StockAvailable::setLocation((int) $product->id, (string) $info['location'], $this->context->shop->id, $id_product_attribute);
                    }
                }
            }
        }
        
        $product->checkDefaultAttributes();
        if (!$validateOnly) {
            // Updates the cache_default_attribute field. It is used by the facetedsearch module.
            Product::updateDefaultAttribute($product->id);
        }
    }

    public function categoryImport($offset = false, $limit = false, &$crossStepsVariables = false, $validateOnly = false)
    {
        $this->receiveColumns();
        $handle = $this->openCsvFile($offset);
        if (!$handle) {
            return false;
        }

        $default_language_id = (int) Configuration::get('PS_LANG_DEFAULT');
        $id_lang = Tools::getValue('language');
        if (!Validate::isUnsignedId($id_lang)) {
            $id_lang = $default_language_id;
        }
        self::setLocale();

        $force_id = Tools::getValue('keep_id');
        $regenerate = Tools::getValue('regenerate');
        $shop_is_feature_active = Shop::isFeatureActive();

        $cat_moved = [];
        if ($crossStepsVariables !== false && array_key_exists('cat_moved', $crossStepsVariables)) {
            $cat_moved = $crossStepsVariables['cat_moved'];
        }

        $line_count = 0;
        for ($current_line = 0; ($line = fgetcsv($handle, MAX_LINE_SIZE, $this->separator, $this->enclosure)) && (!$limit || $current_line < $limit); $current_line++) {
            $line_count++;
            $this->row = $offset + $line_count + 1;
            if ($this->convert) {
                $line = $this->utf8EncodeArray($line);
            }

            $isEmpty = true;
            foreach ($line as $ln) {
                if ($ln) {
                    $isEmpty = false;
                    break;
                }
            }

            if ($isEmpty) {
                $this->warnings[] = $this->module->l('There is an empty row in the file that won\'t be imported.', 'EIImport');

                continue;
            }

            $info = self::getMaskedRow($line);
            try {
                $this->categoryImportOne(
                        $info,
                        $default_language_id,
                        $id_lang,
                        $force_id,
                        $regenerate,
                        $shop_is_feature_active,
                        $cat_moved, // by ref
                        $validateOnly
                );
            } catch (Exception $exc) {
                $this->errors[] = $exc->getMessage()
                        . '<br> ' . $this->module->l('Location', 'EIImport') . ': ' . __CLASS__ . ':' . __LINE__ . '.'
                        . '<br> ' . $this->module->l('Row number in file', 'EIImport') . ": {$this->row}. " . $this->module->l('Problematic row', 'EIImport') . ': <pre>' . print_r($info, true);
            }
        }

        $this->closeCsvFile($handle);

        if ($crossStepsVariables !== false) {
            $crossStepsVariables['cat_moved'] = $cat_moved;
        }

        return $line_count;
    }

    private function getCategoryUpdate(&$info, $id_lang)
    {
        $id_category = null;
        if ($this->updateBy == 'id' && !empty($info['id'])) {
            $id_category = (int) Db::getInstance()->getValue('
                        SELECT c.id_category
                        FROM `' . _DB_PREFIX_ . 'category` c
                        WHERE `id_category` = ' . (int) $info['id'], false);
        } elseif ($this->updateBy == 'name' && !empty($info['name'])) {
            $id_category = (int) Db::getInstance()->getValue('
                        SELECT c.id_category
                        FROM `' . _DB_PREFIX_ . 'category` c
                        LEFT JOIN `' . _DB_PREFIX_ . 'category_lang` cl
                            ON (c.`id_category` = cl.`id_category`
                            AND `id_lang` = ' . (int) $id_lang . Shop::addSqlRestrictionOnLang('cl') . ')
                        WHERE `name` = \'' . pSQL($info['name']) . '\'', false);
        }
        if ($id_category && $this->targetAction != 'insert') {
            $info['id'] = $id_category;
        }
        return $id_category;
    }

    private function getCategoryInsertion(&$info, $id_lang, $force_id)
    {
        if ($force_id && !empty($info['id']) && ObjectModel::existsInDatabase((int) $info['id'], 'category')) {
            return false;
        }
        if ($this->nameExists == 'ignore' && !empty($info['name']) &&
                Db::getInstance()->getValue('
                        SELECT c.id_category
                        FROM `' . _DB_PREFIX_ . 'category` c
                        LEFT JOIN `' . _DB_PREFIX_ . 'category_lang` cl
                            ON (c.`id_category` = cl.`id_category`
                            AND `id_lang` = ' . (int) $id_lang . Shop::addSqlRestrictionOnLang('cl') . ')
                        WHERE `name` = \'' . pSQL($info['name']) . '\'', false)) {
            return false;
        }
        if (!$force_id) {
            unset($info['id']);
        }
        return true;
    }

    protected function categoryImportOne($info, $default_language_id, $id_lang, $force_id, $regenerate, $shop_is_feature_active, &$cat_moved, $validateOnly = false)
    {
        $id_category = false;
        if ($this->targetAction == 'insert') {
//            if (!$this->getCategoryInsertion($info, $id_lang, $force_id)) {
//                return false;
//            }
        } elseif ($this->targetAction == 'update') {
            if (!$id_category = $this->getCategoryUpdate($info, $id_lang)) {
                return false;
            }
        }
//        else {
//            if (!$id_category = $this->getCategoryUpdate($info, $id_lang)) {
//                return false;
//            }
//        }

        $categories_home_root = [Configuration::get('PS_HOME_CATEGORY'), Configuration::get('PS_ROOT_CATEGORY')];
        if (isset($info['id'])) {
            if ((int) $info['id'] == $categories_home_root[0]) {
                $this->warnings[] = $this->module->l('Home category was ignored for import.', 'EIImport');
                return;
            } elseif ((int) $info['id'] == $categories_home_root[1]) {
                $this->warnings[] = $this->module->l('Root category was ignored for import.', 'EIImport');
                return;
            }
        } elseif (isset($info['name']) && Db::getInstance(_PS_USE_SQL_SLAVE_)->getRow('
                SELECT c.*
                FROM `' . _DB_PREFIX_ . 'category` c
                LEFT JOIN `' . _DB_PREFIX_ . 'category_lang` cl
                    ON (c.`id_category` = cl.`id_category`
                    AND `id_lang` = ' . (int) $id_lang . Shop::addSqlRestrictionOnLang('cl') . ')
                WHERE `name` = \'' . pSQL($info['name']) . '\'
                    AND c.`id_category` IN (' . implode(',', $categories_home_root) . ')')) {
            $this->warnings[] = $this->module->l('Root or Home category was ignored for import.', 'EIImport');
            return;
        }
        // Before the setDefaultValues(), because it will set the parent category to HOME if no parent.
//        if (empty($info['parent'])) {
//            $info['id_parent'] = Configuration::get('PS_HOME_CATEGORY');
//        }

        self::setDefaultValues($info);

        // Correct some formats
        if (!empty($info['date_add'])) {
            $info['date_add'] = date("Y-m-d H:i:s", strtotime($info['date_add']));
            if (Tools::substr($info['date_add'], 0, 10) === '1969-12-31' || Tools::substr($info['date_add'], 0, 1) === '-') {
                $info['date_add'] = date("Y-m-d H:i:s");
            }
        }

        if (isset($info['id']) && (int) $info['id'] && ($force_id || ObjectModel::existsInDatabase((int) $info['id'], 'category'))) {
            $category = new Category((int) $info['id']);
            //            $category_groups = $category->getGroups();
        } else {
            $category = new Category();
        }

        self::arrayWalk($info, [self::class, 'fillInfo'], $category);

        // Parent category
        if (!empty($category->id_parent) && (int) $category->id_parent || !empty($category->parent)) {
            // Validation for parenting itself
            if ($validateOnly && ($category->id_parent == $category->id) || (isset($info['id']) && $category->id_parent == (int) $info['id'])) {
                $this->errors[] = sprintf(
                                $this->module->l('The category ID must be unique. It can\'t be the same as the one for the parent category (ID: %1$s).', 'EIImport'),
                                !empty($info['id']) ? Tools::htmlentitiesUTF8($info['id']) : 'null'
                        )
                        . '<br> ' . $this->module->l('Location', 'EIImport') . ': ' . __CLASS__ . ':' . __LINE__ . '.'
                        . '<br> ' . $this->module->l('Row number in file', 'EIImport') . ": {$this->row}. " . $this->module->l('Problematic row', 'EIImport') . ': <pre>' . print_r($info, true);

                return;
            }
            if (!ObjectModel::existsInDatabase((int) $category->id_parent, 'category')) {
                if (!empty($category->parent)) {
                    $category_parent = $this->searchCategoryByName($id_lang, $category->parent, $force_id ? $category->id_parent : false);
                    if ($category_parent) {
                        $found = false;
                        foreach ($category_parent as $cp) {
                            if ($cp['id_category'] == $category->id_parent) {
                                $category_parent = $cp;
                                $found = true;
                                break;
                            }
                        }
                        if (!$found) {
                            $category_parent = $category_parent[0];
                        }
                        // Validation for parenting itself
                        if ($validateOnly && isset($category->name) && ($category->parent == $category->name)) {
                            $this->errors[] = sprintf(
                                            $this->module->l('A category can\'t be its own parent. You should rename it (current name: %1$s).', 'EIImport'),
                                            Tools::htmlentitiesUTF8($category->parent)
                                    )
                                    . '<br> ' . $this->module->l('Location', 'EIImport') . ': ' . __CLASS__ . ':' . __LINE__ . '.'
                                    . '<br> ' . $this->module->l('Row number in file', 'EIImport') . ": {$this->row}. " . $this->module->l('Problematic row', 'EIImport') . ': <pre>' . print_r($info, true);

                            return;
                        }
                        $category->id_parent = $category_parent['id_category'];
//                        $category->level_depth = (int) $category_parent['level_depth'] + 1;
                    } else {
                        $category_to_create = new Category();
                        $category_to_create->id = (int) $category->id_parent ?: null;
                        $category_to_create->force_id = true;
                        $category_to_create->name = self::createMultiLangField($category->parent);
                        $category_to_create->active = 1;
                        $category_link_rewrite = Tools::link_rewrite($category_to_create->name[$id_lang]);
                        $category_to_create->link_rewrite = self::createMultiLangField($category_link_rewrite);
                        $category_to_create->id_parent = Configuration::get('PS_HOME_CATEGORY'); // Default parent is home for unknown category to create

                        if (($field_error = $category_to_create->validateFields(false, true)) === true &&
                                ($lang_field_error = $category_to_create->validateFieldsLang(false, true)) === true &&
                                !$validateOnly && // Do not move the position of this test. Only ->add() should not be triggered is !validateOnly. Previous tests should be always run.
                                $category_to_create->add()
                        ) {
                            $category->id_parent = $category_to_create->id;
                        } else {
                            if (!$validateOnly) {
                                $this->errors[] = sprintf(
                                                $this->module->l('%1$s (ID: %2$s) cannot be saved.', 'EIImport'),
                                                Tools::htmlentitiesUTF8($category_to_create->name[$id_lang]),
                                                !empty($category_to_create->id) ? Tools::htmlentitiesUTF8($category_to_create->id) : 'null'
                                        )
                                        . '<br> ' . $this->module->l('Location', 'EIImport') . ': ' . __CLASS__ . ':' . __LINE__ . '.'
                                        . '<br> ' . $this->module->l('Row number in file', 'EIImport') . ": {$this->row}. " . $this->module->l('Problematic row', 'EIImport') . ': <pre>' . print_r($info, true);
                            }
                            if ($field_error !== true || isset($lang_field_error) && $lang_field_error !== true) {
                                $this->errors[] = ($field_error !== true ? $field_error : '') . (isset($lang_field_error) && $lang_field_error !== true ? $lang_field_error : '') .
                                        Db::getInstance()->getMsgError()
                                        . '<br> ' . $this->module->l('Location', 'EIImport') . ': ' . __CLASS__ . ':' . __LINE__ . '.'
                                        . '<br> ' . $this->module->l('Row number in file', 'EIImport') . ": {$this->row}. " . $this->module->l('Problematic row', 'EIImport') . ': <pre>' . print_r($info, true);
                            }
                        }
                    }
                } else {
                    $category->id_parent = Configuration::get('PS_HOME_CATEGORY');
                }
            }
        } elseif ((int) $category->id && $category->id == $category->id_parent) {
            $this->errors[] = sprintf(
                            $this->module->l('The category ID must be unique. It can\'t be the same as the one for the parent category (ID: %1$s).', 'EIImport'),
                            !empty($info['id']) ? Tools::htmlentitiesUTF8($info['id']) : 'null'
                    )
                    . '<br> ' . $this->module->l('Location', 'EIImport') . ': ' . __CLASS__ . ':' . __LINE__ . '.'
                    . '<br> ' . $this->module->l('Row number in file', 'EIImport') . ": {$this->row}. " . $this->module->l('Problematic row', 'EIImport') . ': <pre>' . print_r($info, true);

            return;
        }

        // Group Importation
        $category_groups = [];
        if (!empty($category->groups)) {
            foreach (explode($this->multivalueSeparator, $category->groups) as $group) {
                $group = trim($group);
                if (empty($group)) {
                    continue;
                }
                $id_group = false;
                if (is_numeric($group) && $group) {
                    $my_group = new Group((int) $group);
                    if (Validate::isLoadedObject($my_group)) {
                        $category_groups[] = (int) $group;
                    }

                    continue;
                }
                $my_group = Group::searchByName($group);
                if (isset($my_group['id_group']) && $my_group['id_group']) {
                    $id_group = (int) $my_group['id_group'];
                }
                if (!$id_group) {
                    $my_group = new Group();
                    $my_group->name = [$id_lang => $group];
                    if ($id_lang != $default_language_id) {
                        $my_group->name = $my_group->name + [$default_language_id => $group];
                    }
                    $my_group->price_display_method = 1;
                    if (!$validateOnly) {
                        $my_group->add();
                        if (Validate::isLoadedObject($my_group)) {
                            $id_group = (int) $my_group->id;
                        }
                    }
                }
                if ($id_group) {
                    $category_groups[] = (int) $id_group;
                }
            }
        } else {
            $validGroupExists = (int) Db::getInstance()->getValue('
			SELECT COUNT(*)
                        FROM `' . _DB_PREFIX_ . 'group` g
                        LEFT JOIN ' . _DB_PREFIX_ . 'category_group cg ON g.id_group = cg.id_group
                        WHERE cg.id_category = ' . (int) $category->id);
            if (!$validGroupExists) {
                $category_groups = [
                    Configuration::get('PS_CUSTOMER_GROUP'),
                    Configuration::get('PS_GUEST_GROUP'),
                    Configuration::get('PS_UNIDENTIFIED_GROUP')
                ];
            }
        }
        $category_groups = array_flip(array_flip($category_groups));

        $reduction_groups = [];
        if (!empty($category->reductions)) {
            foreach (explode($this->multivalueSeparator, $category->reductions) as $group) {
                $reductions = explode(':', $group);
                $group = trim($reductions[0]);
                if (isset($reductions[1])) {
                    $reduction = (float) str_replace(',', '.', $reductions[1]);
                }
                if (empty($group) || empty($reduction)) {
                    continue;
                }
                $id_group = false;
                if (is_numeric($group) && $group) {
                    $my_group = new Group((int) $group);
                    if (Validate::isLoadedObject($my_group)) {
                        $reduction_groups[] = [
                            'id_group' => (int) $group,
                            'reduction' => $reduction
                        ];
                    }

                    continue;
                }
                $my_group = Group::searchByName($group);
                if (isset($my_group['id_group']) && $my_group['id_group']) {
                    $id_group = (int) $my_group['id_group'];
                }
                if (!$id_group) {
                    $my_group = new Group();
                    $my_group->name = [$id_lang => $group];
                    if ($id_lang != $default_language_id) {
                        $my_group->name = $my_group->name + [$default_language_id => $group];
                    }
                    $my_group->price_display_method = 1;
                    if (!$validateOnly) {
                        $my_group->add();
                        if (Validate::isLoadedObject($my_group)) {
                            $id_group = (int) $my_group->id;
                        }
                    }
                }
                if ($id_group) {
                    $reduction_groups[] = [
                        'id_group' => (int) $id_group,
                        'reduction' => $reduction
                    ];
                }
            }
        }

        if (isset($category->link_rewrite) && !empty($category->link_rewrite[$default_language_id])) {
            $valid_link = Validate::isLinkRewrite($category->link_rewrite[$default_language_id]);
        } else {
            $valid_link = false;
        }

        if (!$shop_is_feature_active) {
            $category->id_shop_default = (int) Configuration::get('PS_SHOP_DEFAULT');
        } else {
            $category->id_shop_default = (int) $this->context->shop->id;
        }

        $bak = $category->link_rewrite[$default_language_id];
        if ((isset($category->link_rewrite) && empty($category->link_rewrite[$default_language_id])) || !$valid_link) {
            $category->link_rewrite = Tools::link_rewrite($category->name[$default_language_id]);
            if ($category->link_rewrite == '') {
                $category->link_rewrite = 'friendly-url-autogeneration-failed';
                $this->warnings[] = sprintf(
                        $this->module->l('URL rewriting failed to auto-generate a friendly URL for: %1$s', 'EIImport'),
                        Tools::htmlentitiesUTF8($category->name[$default_language_id])
                );
            }
            $category->link_rewrite = self::createMultiLangField($category->link_rewrite);
        }

        if (!$valid_link && !$category->id) {
            $this->informations[] = sprintf(
                    $this->module->l('Rewrite link for %1$s (ID %2$s): re-written as %3$s.', 'EIImport'),
                    Tools::htmlentitiesUTF8($bak),
                    !empty($info['id']) ? Tools::htmlentitiesUTF8($info['id']) : 'null',
                    Tools::htmlentitiesUTF8($category->link_rewrite[$default_language_id])
            );
        }
        $res = false;
        if (($field_error = $category->validateFields(false, true)) === true &&
                ($lang_field_error = $category->validateFieldsLang(false, true)) === true && empty($this->errors)
        ) {
            if ($category->id && $category->id == $category->id_parent) {
                $this->errors[] = sprintf(
                                $this->module->l('A category cannot be its own parent. The parent category ID is either missing or unknown (ID: %1$s).', 'EIImport'),
                                !empty($info['id']) ? Tools::htmlentitiesUTF8($info['id']) : 'null'
                        )
                        . '<br> ' . $this->module->l('Location', 'EIImport') . ': ' . __CLASS__ . ':' . __LINE__ . '.'
                        . '<br> ' . $this->module->l('Row number in file', 'EIImport') . ": {$this->row}. " . $this->module->l('Problematic row', 'EIImport') . ': <pre>' . print_r($info, true);

                return;
            }

            /* No automatic nTree regeneration for import */
            $category->doNotRegenerateNTree = true;

            // If id category AND id category already in base, trying to update
            if ($category->id && $category->categoryExists($category->id) && !in_array($category->id, $categories_home_root) && !$validateOnly) {
                $res = $category->update();
                if ($res) {
                    if (isset($category_groups)) {
                        $category->updateGroup($category_groups);
                    }
                    if (isset($category->reductions)) {
                        $this->updateCategoryReductions((int) $category->id, $reduction_groups);
                    }
                }
            }
            if ($category->id == Configuration::get('PS_ROOT_CATEGORY')) {
                $this->errors[] = $this->module->l('The root category cannot be modified.', 'EIImport')
                        . '<br> ' . $this->module->l('Location', 'EIImport') . ': ' . __CLASS__ . ':' . __LINE__ . '.'
                        . '<br> ' . $this->module->l('Row number in file', 'EIImport') . ": {$this->row}. " . $this->module->l('Problematic row', 'EIImport') . ': <pre>' . print_r($info, true);
            }
            // If no id_category or update failed
            $category->force_id = (bool) $force_id;
            if (!$res && !$validateOnly) {
                $res = $category->add();
                if ($res) {
                    if (isset($category_groups)) {
                        $category->updateGroup($category_groups);
                    }
                    if (isset($category->reductions)) {
                        $this->updateCategoryReductions((int) $category->id, $reduction_groups);
                    }
                }
                if (isset($info['id']) && $category->id != $info['id']) {
                    $cat_moved[$info['id']] = $category->id;
                }
            }
        }

        // ValidateOnly mode : stops here
        if ($validateOnly) {
            return;
        }

        //copying images of categories
        if (isset($category->image) && !empty($category->image)) {
            if (!(self::copyImg($category->id, null, $category->image, 'categories', $regenerate))) {
                $this->warnings[] = $category->image . ' ' . $this->module->l('cannot be copied.', 'EIImport');
            }
        }
        // If both failed, mysql error
        if (!$res) {
            $this->errors[] = sprintf(
                            $this->module->l('%1$s (ID: %2$s) cannot be %3$s', 'EIImport'),
                            !empty($info['name']) ? Tools::safeOutput($info['name']) : 'No Name',
                            !empty($info['id']) ? Tools::safeOutput($info['id']) : 'No ID',
                            ($validateOnly ? 'validated' : 'saved')
                    )
                    . '<br> ' . $this->module->l('Location', 'EIImport') . ': ' . __CLASS__ . ':' . __LINE__ . '.'
                    . '<br> ' . $this->module->l('Row number in file', 'EIImport') . ": {$this->row}. " . $this->module->l('Problematic row', 'EIImport') . ': <pre>' . print_r($info, true);
            $error_tmp = ($field_error !== true ? $field_error : '') . (isset($lang_field_error) && $lang_field_error !== true ? $lang_field_error : '') . Db::getInstance()->getMsgError();
            if ($error_tmp != '') {
                $this->errors[] = $error_tmp
                        . '<br> ' . $this->module->l('Location', 'EIImport') . ': ' . __CLASS__ . ':' . __LINE__ . '.'
                        . '<br> ' . $this->module->l('Row number in file', 'EIImport') . ": {$this->row}. " . $this->module->l('Problematic row', 'EIImport') . ': <pre>' . print_r($info, true);
            }
        } else {
            // Associate category to shop
            $data = array();
            foreach ($this->shops as $id_shop) {
                if (!empty($info['position']) || $info['position'] == '0') {
                    $data[] = array(
                        Category::$definition['primary'] => (int) $category->id,
                        'id_shop' => (int) $id_shop,
                        'position' => (int) $category->position
                    );
                } else {
                    Category::cleanPositions($category->id_parent);
                }
            }
            if ($data) {
                return Db::getInstance()->insert(Category::$definition['table'] . '_shop', $data, false, true, Db::ON_DUPLICATE_KEY);
            }
        }
    }

    private function searchCategoryByName($id_lang, $name, $id = false)
    {
        $sql = "SELECT c.*, cl.* FROM " . _DB_PREFIX_ . "category c
                LEFT JOIN " . _DB_PREFIX_ . "category_lang cl ON c.id_category = cl.id_category
                    AND cl.id_lang = $id_lang
                WHERE cl.`name` = '" . pSQL($name) . "'" . ($id ? ' AND c.id_category = ' . (int) $id : '');
        return Db::getInstance()->executeS($sql);
    }

    protected function updateCategoryReductions($id_cat, $reductions)
    {
        Db::getInstance()->execute('
					DELETE FROM ' . _DB_PREFIX_ . 'group_reduction
					WHERE id_category = ' . $id_cat);
        $data = array();
        foreach ($reductions as $red) {
            $data[] = array(
                'id_category' => $id_cat,
                'id_group' => $red['id_group'],
                'reduction' => $red['reduction']
            );
        }
        if ($data) {
            return Db::getInstance()->insert('group_reduction', $data);
        }
    }

    public function manufacturerImport($offset = false, $limit = false, $validateOnly = false)
    {
        // Fill self::$column_mask array
        $this->receiveColumns();
        // Open the file
        $handle = $this->openCsvFile($offset);
        if (!$handle) {
            return false;
        }

        self::setLocale();

        $shop_is_feature_active = Shop::isFeatureActive();
        $regenerate = Tools::getValue('regenerate');
        $force_id = Tools::getValue('keep_id');

        $line_count = 0;
        for ($current_line = 0; ($line = fgetcsv($handle, MAX_LINE_SIZE, $this->separator, $this->enclosure)) && (!$limit || $current_line < $limit); $current_line++) {
            $line_count++;
            $this->row = $offset + $line_count + 1;
            if ($this->convert) {
                $line = $this->utf8EncodeArray($line);
            }

            $isEmpty = true;
            foreach ($line as $ln) {
                if ($ln) {
                    $isEmpty = false;
                    break;
                }
            }

            if ($isEmpty) {
                $this->warnings[] = $this->module->l('There is an empty row in the file that won\'t be imported.', 'EIImport');
                continue;
            }

            $info = self::getMaskedRow($line);

            $this->manufacturerImportOne(
                    $info,
                    $shop_is_feature_active,
                    $regenerate,
                    $force_id,
                    $validateOnly
            );
        }
        $this->closeCsvFile($handle);

        return $line_count;
    }

    protected function manufacturerImportOne($info, $shop_is_feature_active, $regenerate, $force_id, $validateOnly = false)
    {
        if ($this->targetAction == 'insert') {
            if (!$this->getManufacturerInsertion($info, $force_id)) {
                return false;
            }
        } elseif ($this->targetAction == 'update') {
            if (!$this->getManufacturerUpdate($info)) {
                return false;
            }
        } else {
            if (!$this->getManufacturerUpdate($info) && !$this->getManufacturerInsertion($info, $force_id)) {
                return false;
            }
        }

        if (isset($info['id']) && (int) $info['id'] && ($force_id || ObjectModel::existsInDatabase((int) $info['id'], 'manufacturer'))) {
            $manufacturer = new Manufacturer((int) $info['id']);
        } else {
            $manufacturer = new Manufacturer();
            self::setDefaultValues($info);
        }

        // Correct some formats
        if (!empty($info['date_add'])) {
            $info['date_add'] = date("Y-m-d H:i:s", strtotime($info['date_add']));
            if (Tools::substr($info['date_add'], 0, 10) === '1969-12-31' || Tools::substr($info['date_add'], 0, 1) === '-') {
                $info['date_add'] = date("Y-m-d H:i:s");
            }
        }

        self::arrayWalk($info, [self::class, 'fillInfo'], $manufacturer);

        $res = false;
        if (($field_error = $manufacturer->validateFields(false, true)) === true &&
                ($lang_field_error = $manufacturer->validateFieldsLang(false, true)) === true) {
            if ($manufacturer->id && $manufacturer->manufacturerExists($manufacturer->id)) {
                $res = ($validateOnly || $manufacturer->update());
            }
            $manufacturer->force_id = (bool) $force_id;
            if (!$res) {
                $res = ($validateOnly || $manufacturer->add());
            }

            //copying images of manufacturer
            if (!$validateOnly && !empty($manufacturer->image)) {
                if (!self::copyImg($manufacturer->id, null, $manufacturer->image, 'manufacturers', $regenerate)) {
                    $this->warnings[] = $manufacturer->image . ' ' . $this->module->l('cannot be copied.', 'EIImport');
                }
            }

            if (!$validateOnly && $res) {
                // Associate manufacturer to group shop
                if ($shop_is_feature_active) {
                    $data = array();
                    foreach ($this->shops as $id_shop) {
                        $data[] = array(
                            Manufacturer::$definition['primary'] => (int) $manufacturer->id,
                            'id_shop' => (int) $id_shop,
                        );
                    }
                    if ($data) {
                        Db::getInstance()->insert(Manufacturer::$definition['table'] . '_shop', $data, false, true, Db::INSERT_IGNORE);
                    }
                }
            }
        }

        if (!$res) {
            if (!$validateOnly) {
                $this->errors[] = Db::getInstance()->getMsgError() . ' ' . sprintf(
                                $this->module->l('%1$s (ID: %2$s) cannot be saved.', 'EIImport'),
                                !empty($info['name']) ? Tools::safeOutput($info['name']) : 'No Name',
                                !empty($info['id']) ? Tools::safeOutput($info['id']) : 'No ID'
                        )
                        . '<br> ' . $this->module->l('Location', 'EIImport') . ': ' . __CLASS__ . ':' . __LINE__ . '.'
                        . '<br> ' . $this->module->l('Row number in file', 'EIImport') . ": {$this->row}. " . $this->module->l('Problematic row', 'EIImport') . ': <pre>' . print_r($info, true);
            }
            if ($field_error !== true || isset($lang_field_error) && $lang_field_error !== true) {
                $this->errors[] = ($field_error !== true ? $field_error : '') . (isset($lang_field_error) && $lang_field_error !== true ? $lang_field_error : '') .
                        Db::getInstance()->getMsgError()
                        . '<br> ' . $this->module->l('Location', 'EIImport') . ': ' . __CLASS__ . ':' . __LINE__ . '.'
                        . '<br> ' . $this->module->l('Row number in file', 'EIImport') . ": {$this->row}. " . $this->module->l('Problematic row', 'EIImport') . ': <pre>' . print_r($info, true);
            }
        }
    }

    private function getManufacturerUpdate(&$info)
    {
        $id_manufacturer = null;
        if ($this->updateBy == 'id' && !empty($info['id'])) {
            $id_manufacturer = (int) Db::getInstance()->getValue('
                        SELECT id_manufacturer
                        FROM `' . _DB_PREFIX_ . 'manufacturer`
                        WHERE `id_manufacturer` = ' . (int) $info['id'], false);
        } elseif ($this->updateBy == 'name' && !empty($info['name'])) {
            $id_manufacturer = (int) Db::getInstance()->getValue('
                        SELECT id_manufacturer
                        FROM `' . _DB_PREFIX_ . 'manufacturer`
                        WHERE `name` = \'' . pSQL($info['name']) . '\'', false);
        }
        if ($id_manufacturer && $this->targetAction != 'insert') {
            $info['id'] = $id_manufacturer;
        }
        return $id_manufacturer;
    }

    private function getManufacturerInsertion(&$info, $force_id)
    {
        if ($force_id && !empty($info['id']) && ObjectModel::existsInDatabase((int) $info['id'], 'manufacturer')) {
            return false;
        }
        if ($this->nameExists == 'ignore' && !empty($info['name']) &&
                Db::getInstance()->getRow('
                        SELECT *
                        FROM `' . _DB_PREFIX_ . 'manufacturer`
                        WHERE `name` = \'' . pSQL($info['name']) . '\'', false)) {
            return false;
        }

        if (!$force_id) {
            unset($info['id']);
        }
        return true;
    }

    public function supplierImport($offset = false, $limit = false, $validateOnly = false)
    {
        // Fill self::$column_mask array
        $this->receiveColumns();
        // Open the file
        $handle = $this->openCsvFile($offset);
        if (!$handle) {
            return false;
        }

        self::setLocale();

        $shop_is_feature_active = Shop::isFeatureActive();
        $regenerate = Tools::getValue('regenerate');
        $force_id = Tools::getValue('keep_id');

        $line_count = 0;
        for ($current_line = 0; ($line = fgetcsv($handle, MAX_LINE_SIZE, $this->separator, $this->enclosure)) && (!$limit || $current_line < $limit); $current_line++) {
            $line_count++;
            $this->row = $offset + $line_count + 1;
            if ($this->convert) {
                $line = $this->utf8EncodeArray($line);
            }

            $isEmpty = true;
            foreach ($line as $ln) {
                if ($ln) {
                    $isEmpty = false;
                    break;
                }
            }

            if ($isEmpty) {
                $this->warnings[] = $this->module->l('There is an empty row in the file that won\'t be imported.', 'EIImport');
                continue;
            }

            $info = self::getMaskedRow($line);

            $this->supplierImportOne(
                    $info,
                    $shop_is_feature_active,
                    $regenerate,
                    $force_id,
                    $validateOnly
            );
        }
        $this->closeCsvFile($handle);

        return $line_count;
    }

    protected function supplierImportOne($info, $shop_is_feature_active, $regenerate, $force_id, $validateOnly = false)
    {
        if ($this->targetAction == 'insert') {
            if (!$this->getSupplierInsertion($info, $force_id)) {
                return false;
            }
        } elseif ($this->targetAction == 'update') {
            if (!$this->getSupplierUpdate($info)) {
                return false;
            }
        } else {
            if (!$this->getSupplierUpdate($info) && !$this->getSupplierInsertion($info, $force_id)) {
                return false;
            }
        }

        if (isset($info['id']) && (int) $info['id'] && ($force_id || ObjectModel::existsInDatabase((int) $info['id'], 'supplier'))) {
            $supplier = new Supplier((int) $info['id']);
        } else {
            $supplier = new Supplier();
            self::setDefaultValues($info);
        }

        // Correct some formats
        if (!empty($info['date_add'])) {
            $info['date_add'] = date("Y-m-d H:i:s", strtotime($info['date_add']));
            if (Tools::substr($info['date_add'], 0, 10) === '1969-12-31' || Tools::substr($info['date_add'], 0, 1) === '-') {
                $info['date_add'] = date("Y-m-d H:i:s");
            }
        }

        self::arrayWalk($info, [self::class, 'fillInfo'], $supplier);

        $res = false;
        if (($field_error = $supplier->validateFields(false, true)) === true &&
                ($lang_field_error = $supplier->validateFieldsLang(false, true)) === true) {
            if ($supplier->id && $supplier->supplierExists($supplier->id)) {
                $res = ($validateOnly || $supplier->update());
            }
            $supplier->force_id = (bool) $force_id;
            if (!$res) {
                $res = ($validateOnly || $supplier->add());
            }

            // copying images of supplier
            if (!$validateOnly && !empty($supplier->image)) {
                if (!self::copyImg($supplier->id, null, $supplier->image, 'suppliers', $regenerate)) {
                    $this->warnings[] = $supplier->image . ' ' . $this->module->l('cannot be copied.', 'EIImport');
                }
            }

            if (!$validateOnly && $res) {
                // Associate supplier to group shop
                if ($shop_is_feature_active) {
                    $data = array();
                    foreach ($this->shops as $id_shop) {
                        $data[] = array(
                            Supplier::$definition['primary'] => (int) $supplier->id,
                            'id_shop' => (int) $id_shop,
                        );
                    }
                    if ($data) {
                        Db::getInstance()->insert(Supplier::$definition['table'] . '_shop', $data, false, true, Db::INSERT_IGNORE);
                    }
                }
            }
        }

        if (!$res) {
            if (!$validateOnly) {
                $this->errors[] = Db::getInstance()->getMsgError() . ' ' . sprintf(
                                $this->module->l('%1$s (ID: %2$s) cannot be saved.', 'EIImport'),
                                !empty($info['name']) ? Tools::safeOutput($info['name']) : 'No Name',
                                !empty($info['id']) ? Tools::safeOutput($info['id']) : 'No ID'
                        )
                        . '<br> ' . $this->module->l('Location', 'EIImport') . ': ' . __CLASS__ . ':' . __LINE__ . '.'
                        . '<br> ' . $this->module->l('Row number in file', 'EIImport') . ": {$this->row}. " . $this->module->l('Problematic row', 'EIImport') . ': <pre>' . print_r($info, true);
            }
            if ($field_error !== true || isset($lang_field_error) && $lang_field_error !== true) {
                $this->errors[] = ($field_error !== true ? $field_error : '') . (isset($lang_field_error) && $lang_field_error !== true ? $lang_field_error : '') .
                        Db::getInstance()->getMsgError()
                        . '<br> ' . $this->module->l('Location', 'EIImport') . ': ' . __CLASS__ . ':' . __LINE__ . '.'
                        . '<br> ' . $this->module->l('Row number in file', 'EIImport') . ": {$this->row}. " . $this->module->l('Problematic row', 'EIImport') . ': <pre>' . print_r($info, true);
            }
        }
    }

    private function getSupplierUpdate(&$info)
    {
        $id_supplier = null;
        if ($this->updateBy == 'id' && !empty($info['id'])) {
            $id_supplier = (int) Db::getInstance()->getValue('
                        SELECT id_supplier
                        FROM `' . _DB_PREFIX_ . 'supplier`
                        WHERE `id_supplier` = ' . (int) $info['id'], false);
        } elseif ($this->updateBy == 'name' && !empty($info['name'])) {
            $id_supplier = (int) Db::getInstance()->getValue('
                        SELECT id_supplier
                        FROM `' . _DB_PREFIX_ . 'supplier`
                        WHERE `name` = \'' . pSQL($info['name']) . '\'', false);
        }
        if ($id_supplier && $this->targetAction != 'insert') {
            $info['id'] = $id_supplier;
        }
        return $id_supplier;
    }

    private function getSupplierInsertion(&$info, $force_id)
    {
        if ($force_id && !empty($info['id']) && ObjectModel::existsInDatabase((int) $info['id'], 'supplier')) {
            return false;
        }
        if ($this->nameExists == 'ignore' && !empty($info['name']) &&
                Db::getInstance()->getRow('
                        SELECT *
                        FROM `' . _DB_PREFIX_ . 'supplier`
                        WHERE `name` = \'' . pSQL($info['name']) . '\'', false)) {
            return false;
        }

        if (!$force_id) {
            unset($info['id']);
        }
        return true;
    }

    public function discountImport($offset = false, $limit = false, $validateOnly = false)
    {
        // Fill self::$column_mask array
        $this->receiveColumns();
        // Open the file
        $handle = $this->openCsvFile($offset);
        if (!$handle) {
            return false;
        }

        self::setLocale();

        $shop_is_feature_active = Shop::isFeatureActive();
        $default_language_id = Configuration::get('PS_LANG_DEFAULT');
        $id_lang = Tools::getValue('language');
        // For discounts only one shop at a time. id_shop is inside the specific_price(_rule) table
        $id_shop = Tools::getValue('shop');
        $force_id = Tools::getValue('keep_id');
        //        if (!Validate::isUnsignedId($id_lang)) {
        //            $id_lang = $default_language;
        //        }

        $line_count = 0;
        for ($current_line = 0; ($line = fgetcsv($handle, MAX_LINE_SIZE, $this->separator, $this->enclosure)) && (!$limit || $current_line < $limit); $current_line++) {
            $line_count++;
            $this->row = $offset + $line_count + 1;
            if ($this->convert) {
                $line = $this->utf8EncodeArray($line);
            }

            $isEmpty = true;
            foreach ($line as $ln) {
                if ($ln) {
                    $isEmpty = false;
                    break;
                }
            }

            if ($isEmpty) {
                $this->warnings[] = $this->module->l('There is an empty row in the file that won\'t be imported.', 'EIImport');
                continue;
            }

            $info = self::getMaskedRow($line);

            if (isset($info['id_country']) && is_numeric($info['id_country'])) {
                if (Country::getNameById($default_language_id, (int) $info['id_country'])) {
                    $info['id_country'] = (int) $info['id_country'];
                }
            } elseif (isset($info['id_country']) && is_string($info['id_country']) && !empty($info['id_country'])) {
                if ($id_country = Country::getIdByName(null, $info['id_country'])) {
                    $info['id_country'] = (int) $id_country;
                }
            }
            if (empty($info['id_country'])) {
                $info['id_country'] = 0;
            }

            $this->discountImportOne(
                    $info,
                    $default_language_id,
                    $id_lang,
                    $shop_is_feature_active,
                    $id_shop,
                    $force_id,
                    $validateOnly
            );
        }
        $this->closeCsvFile($handle);

        return $line_count;
    }

    private function getDiscountInsertUpdate(&$info, $id_lang)
    {
        $id_product = null;
        $id_product_attribute = 0;
        $id_sp = isset($info['id_specific_price']) ? (int) $info['id_specific_price'] : null;
        $id_spr = isset($info['id_specific_price_rule']) && (int) $info['id_specific_price_rule'] ? (int) $info['id_specific_price_rule'] : null;
        $id_spr_old = $id_spr;

        if ($this->targetAction !== 'insert' && $this->updateBy == 'name' && isset($info['name']) && is_string($info['name'])) {
            $id_spr = (int) Db::getInstance()->getValue('SELECT id_specific_price_rule FROM '
                            . _DB_PREFIX_ . 'specific_price_rule WHERE id_shop = ' . (int) $info['id_shop'] . ' AND `name` = "' . $info['name'] . '"', false);
        }

        // Get Product ID
        if (!empty($info['id_product'])) {
            $id_product = Db::getInstance()->getValue('
                        SELECT p.id_product
                        FROM `' . _DB_PREFIX_ . 'product` p
                        WHERE `id_product` = ' . (int) $info['id_product'], false);
        }
        if (!$id_product && !empty($info['product_reference'])) {
            if (!$id_product = Db::getInstance()->getValue('
                        SELECT p.id_product
                        FROM `' . _DB_PREFIX_ . 'product` p
                        LEFT JOIN `' . _DB_PREFIX_ . 'product_lang` pl
                            ON (p.`id_product` = pl.`id_product`
                            AND `id_lang` = ' . (int) $id_lang . Shop::addSqlRestrictionOnLang('pl') . ')
                        WHERE `reference` = \'' . pSQL($info['product_reference']) . '\''
                    . (!empty($info['product_name']) ? ' AND `name` = \'' . pSQL($info['product_name']) . '\'' : ''), false)) {
                $id_product = Db::getInstance()->getValue('
                        SELECT p.id_product
                        FROM `' . _DB_PREFIX_ . 'product` p
                        WHERE `reference` = \'' . pSQL($info['product_reference']) . '\'', false);
            }
        }
        if (!$id_product && !empty($info['product_name'])) {
            if (!$id_product = Db::getInstance()->getValue('
                        SELECT p.id_product
                        FROM `' . _DB_PREFIX_ . 'product` p
                        LEFT JOIN `' . _DB_PREFIX_ . 'product_lang` pl
                            ON (p.`id_product` = pl.`id_product`
                            AND `id_lang` = ' . (int) $id_lang . Shop::addSqlRestrictionOnLang('pl') . ')
                        WHERE `name` = \'' . pSQL($info['product_name']) . '\''
                    . (!empty($info['product_reference']) ? ' AND `reference` = \'' . pSQL($info['product_reference']) . '\'' : ''), false)) {
                $id_product = Db::getInstance()->getValue('
                        SELECT p.id_product
                        FROM `' . _DB_PREFIX_ . 'product` p
                        LEFT JOIN `' . _DB_PREFIX_ . 'product_lang` pl
                            ON (p.`id_product` = pl.`id_product`
                            AND `id_lang` = ' . (int) $id_lang . Shop::addSqlRestrictionOnLang('pl') . ')
                        WHERE `name` = \'' . pSQL($info['product_name']) . '\'', false);
            }
        }

        // Get combination ID
        if ($id_product) {
            if (!empty($info['id_product_attribute'])) {
                $id_product_attribute = $info['id_product_attribute'];
            } elseif (!empty($info['attribute']) || !empty($info['product_attribute_reference'])) {
                $id_join_shop = $info['id_shop'];
                if (!empty($info['attribute'])) {
                    if (!empty($info['product_attribute_reference'])) {
                        $id_product_attribute = Db::getInstance()->getValue('SELECT id_product_attribute FROM (
                                SELECT
                                    pac.id_product_attribute,
                                    GROUP_CONCAT(CONCAT_WS(":", agl.name, al.name) ORDER BY agl.name, al.name SEPARATOR "' . $this->multivalueSeparator . '") `values`
                                FROM ' . _DB_PREFIX_ . 'product_attribute_combination pac
                                JOIN ' . _DB_PREFIX_ . 'product_attribute pa ON pac.id_product_attribute = pa.id_product_attribute
                                JOIN ' . _DB_PREFIX_ . 'attribute a ON pac.id_attribute = a.id_attribute
                                JOIN ' . _DB_PREFIX_ . 'attribute_shop ash ON a.id_attribute = ash.id_attribute AND ash.id_shop = ' . $id_join_shop . '
                                JOIN ' . _DB_PREFIX_ . 'attribute_lang al ON a.id_attribute = al.id_attribute AND al.id_lang = ' . $id_lang . '
                                JOIN ' . _DB_PREFIX_ . 'attribute_group ag ON a.id_attribute_group = ag.id_attribute_group
                                JOIN ' . _DB_PREFIX_ . 'attribute_group_shop agsh ON a.id_attribute_group = agsh.id_attribute_group AND agsh.id_shop = ' . $id_join_shop . '
                                JOIN ' . _DB_PREFIX_ . 'attribute_group_lang agl ON a.id_attribute_group = agl.id_attribute_group AND agl.id_lang = ' . $id_lang . '
                                WHERE pa.`id_product` = ' . (int) $id_product . ' AND pa.`reference` = \'' . (int) $info['product_attribute_reference'] . '\'' . '
                                GROUP BY pac.id_product_attribute
                                HAVING `values` = "' . str_replace('"', '\"', $info['attribute']) . '") tmp', false);
                    }
                    if (!$id_product_attribute) {
                        $id_product_attribute = Db::getInstance()->getValue('SELECT id_product_attribute FROM (
                                    SELECT
                                        pac.id_product_attribute,
                                        GROUP_CONCAT(CONCAT_WS(":", agl.name, al.name) ORDER BY agl.name, al.name SEPARATOR "' . $this->multivalueSeparator . '") `values`
                                    FROM ' . _DB_PREFIX_ . 'product_attribute_combination pac
                                    JOIN ' . _DB_PREFIX_ . 'product_attribute pa ON pac.id_product_attribute = pa.id_product_attribute
                                    JOIN ' . _DB_PREFIX_ . 'attribute a ON pac.id_attribute = a.id_attribute
                                    JOIN ' . _DB_PREFIX_ . 'attribute_shop ash ON a.id_attribute = ash.id_attribute AND ash.id_shop = ' . $id_join_shop . '
                                    JOIN ' . _DB_PREFIX_ . 'attribute_lang al ON a.id_attribute = al.id_attribute AND al.id_lang = ' . $id_lang . '
                                    JOIN ' . _DB_PREFIX_ . 'attribute_group ag ON a.id_attribute_group = ag.id_attribute_group
                                    JOIN ' . _DB_PREFIX_ . 'attribute_group_shop agsh ON a.id_attribute_group = agsh.id_attribute_group AND agsh.id_shop = ' . $id_join_shop . '
                                    JOIN ' . _DB_PREFIX_ . 'attribute_group_lang agl ON a.id_attribute_group = agl.id_attribute_group AND agl.id_lang = ' . $id_lang . '
                                    WHERE pa.`id_product` = ' . (int) $id_product . '
                                    GROUP BY pac.id_product_attribute
                                    HAVING `values` = "' . str_replace('"', '\"', $info['attribute']) . '") tmp', false);
                    }
                }
                if (!$id_product_attribute) {
                    $id_product_attribute = Db::getInstance()->getValue('
                                    SELECT pa.id_product_attribute
                                    FROM `' . _DB_PREFIX_ . 'product_attribute` pa
                                    WHERE `id_product` = ' . (int) $id_product . ' AND `reference` = \'' . pSQL($info['product_attribute_reference']) . '\'', false);
                }
            }
        }

        $info['id_product'] = (int) $id_product;
        $info['id_product_attribute'] = (int) $id_product_attribute;
        $info['id_specific_price'] = (int) $id_sp;
        $info['id_specific_price_rule'] = (int) $id_spr;
        $info['id_specific_price_rule_old'] = (int) $id_spr_old;
    }

    protected function discountImportOne($info, $default_language_id, $id_lang, $id_shop, $shop_is_feature_active, $force_id, $validateOnly = false)
    {
        if (!$shop_is_feature_active) {
            //                $info['shop'] = 1;
            $info['id_shop'] = (int) Configuration::get('PS_SHOP_DEFAULT');
        } elseif (!empty($id_shop)) {
            $info['id_shop'] = $id_shop;
        }

        $this->getDiscountInsertUpdate($info, $id_lang);

        $spr = $sp = null;
        if ($this->targetAction == 'insert') {
            if ($force_id && (int) $info['id_specific_price_rule']) {
                if (!ObjectModel::existsInDatabase((int) $info['id_specific_price_rule'], 'specific_price_rule')) {
                    $spr = new SpecificPriceRule();
                    $spr->id = (int) $info['id_specific_price_rule'];
                    $spr->force_id = true;
                }
            } elseif (!empty($info['name'])) {
//                unset($info['id_specific_price_rule']);
                $spr = new SpecificPriceRule();
            }

            if ($this->nameExists == 'ignore' && !empty($info['name']) && (int) Db::getInstance()->getValue('SELECT id_specific_price_rule FROM '
                            . _DB_PREFIX_ . 'specific_price_rule WHERE `name` = "' . $info['name'] . '"', false)) {
                $spr = null;
            }

            if ($force_id && (int) $info['id_specific_price']) {
                if (!ObjectModel::existsInDatabase((int) $info['id_specific_price'], 'specific_price')) {
                    $sp = new SpecificPrice();
                    $sp->id = (int) $info['id_specific_price'];
                    $sp->force_id = true;
                }
            } elseif ($info['id_product']) {
//                unset($info['id_specific_price']);
                $sp = new SpecificPrice();
            }
        } elseif ($this->targetAction == 'update') {
            if ((int) $info['id_specific_price_rule'] && ObjectModel::existsInDatabase((int) $info['id_specific_price_rule'], 'specific_price_rule')) {
                $spr = new SpecificPriceRule((int) $info['id_specific_price_rule']);
                $spr->force_id = true;

                // Use the existing shop ID
                if (!$shop_is_feature_active) {
                    unset($info['id_shop']);
                }
            }
            if ((int) $info['id_specific_price'] && ObjectModel::existsInDatabase((int) $info['id_specific_price'], 'specific_price')) {
                $sp = new SpecificPrice((int) $info['id_specific_price']);
                $sp->force_id = true;

                // Use the existing shop ID
                if (!$shop_is_feature_active) {
                    unset($info['id_shop']);
                }
            }
        } else {
            if ((int) $info['id_specific_price_rule'] && ObjectModel::existsInDatabase((int) $info['id_specific_price_rule'], 'specific_price_rule')) {
                $spr = new SpecificPriceRule((int) $info['id_specific_price_rule']);
                $spr->force_id = true;

                // Use the existing shop ID
                if (!$shop_is_feature_active) {
                    unset($info['id_shop']);
                }
            }
            if ((int) $info['id_specific_price'] && ObjectModel::existsInDatabase((int) $info['id_specific_price'], 'specific_price')) {
                $sp = new SpecificPrice((int) $info['id_specific_price']);
                $sp->force_id = true;

                // Use the existing shop ID
                if (!$shop_is_feature_active) {
                    unset($info['id_shop']);
                }
            }

            if (!$spr) {
                $info['id_specific_price_rule'] = $info['id_specific_price_rule_old'];
                if ($force_id && (int) $info['id_specific_price_rule']) {
                    if (!ObjectModel::existsInDatabase((int) $info['id_specific_price_rule'], 'specific_price_rule')) {
                        $spr = new SpecificPriceRule();
                        $spr->id = (int) $info['id_specific_price_rule'];
                        $spr->force_id = true;
                    }
                } elseif (!empty($info['name'])) {
//                    unset($info['id_specific_price_rule']);
                    $spr = new SpecificPriceRule();
                }

                if ($this->nameExists == 'ignore' && !empty($info['name']) && (int) Db::getInstance()->getValue('SELECT id_specific_price_rule FROM '
                                . _DB_PREFIX_ . 'specific_price_rule WHERE `name` = "' . $info['name'] . '"', false)) {
                    $spr = null;
                }
            }

            if (!$sp) {
                if ($force_id && (int) $info['id_specific_price']) {
                    if (!ObjectModel::existsInDatabase((int) $info['id_specific_price'], 'specific_price')) {
                        $sp = new SpecificPrice();
                        $sp->id = (int) $info['id_specific_price'];
                        $sp->force_id = true;
                    }
                } elseif ($info['id_product']) {
//                    unset($info['id_specific_price']);
                    $sp = new SpecificPrice();
                }
            }
        }

        if (isset($info['is_rule']) && $info['is_rule']) {
            $sp = null;
        }

        self::setDefaultValues($info);

        // Correct some formats
        if (!empty($info['price'])) {
            $info['price'] = str_replace(',', '.', $info['price']);
        }
        if (!empty($info['reduction'])) {
            $info['reduction'] = str_replace(',', '.', $info['reduction']);
        }
        if (!empty($info['from'])) {
            $info['from'] = date("Y-m-d H:i:s", strtotime($info['from']));
            if (Tools::substr($info['from'], 0, 10) === '1969-12-31' || Tools::substr($info['from'], 0, 1) === '-') {
                $info['from'] = '0000-00-00 00:00:00';
            }
        } else {
            $info['from'] = '0000-00-00 00:00:00';
        }
        if (!empty($info['to'])) {
            $info['to'] = date("Y-m-d H:i:s", strtotime($info['to']));
            if (Tools::substr($info['to'], 0, 10) === '1969-12-31' || Tools::substr($info['to'], 0, 1) === '-') {
                $info['to'] = '0000-00-00 00:00:00';
            }
        } else {
            $info['to'] = '0000-00-00 00:00:00';
        }

//        $succ1 = $succ2 = false;

        if ($spr) {
            self::arrayWalk($info, [self::class, 'fillInfo'], $spr);
            $spr->name = $info['name']; // because this is not a multi-lingual field

            if (is_numeric($info['id_currency'])) {
                $my_group = new Currency((int) $info['id_currency']);
                if (Validate::isLoadedObject($my_group)) {
                    $spr->id_currency = (int) $info['id_currency'];
                } else {
                    $spr->id_currency = 0;
                }
            } elseif (($res = (int) Currency::getIdByIsoCode($info['id_currency']))) {
                $spr->id_currency = $res;
            } else {
                $spr->id_currency = 0;
            }

            if (is_numeric($info['id_group'])) {
                $my_group = new Group((int) $info['id_group']);
                if (Validate::isLoadedObject($my_group)) {
                    $spr->id_group = (int) $info['id_group'];
                } else {
                    $spr->id_group = 0;
                }
            } elseif ($info['id_group'] && ($res = Group::searchByName($info['id_group']))) {
                $spr->id_group = (int) $res['id_group'];
            } else {
                $spr->id_group = 0;
            }

            if (($field_error = $spr->validateFields(false, true)) !== true) {
                $this->errors[] = $field_error . Db::getInstance()->getMsgError()
                        . '<br> ' . $this->module->l('Location', 'EIImport') . ': ' . __CLASS__ . ':' . __LINE__ . '.'
                        . '<br> ' . $this->module->l('Row number in file', 'EIImport') . ": {$this->row}. " . $this->module->l('Problematic row', 'EIImport') . ': <pre>' . print_r($info, true);
                return false;
            }
            if (!$validateOnly) {
                if (!$spr->id) {
                    $succ1 = $spr->add();
                } elseif (!ObjectModel::existsInDatabase((int) $info['id_specific_price_rule'], 'specific_price_rule')) {
                    $succ1 = $spr->add();
                } else {
                    $succ1 = $spr->update();
                }

                if ($succ1 && !empty($info['conds'])) {
                    Db::getInstance()->execute('DELETE sprcg, sprc FROM ' . _DB_PREFIX_ . 'specific_price_rule_condition_group sprcg
					JOIN ' . _DB_PREFIX_ . 'specific_price_rule_condition sprc ON sprcg.id_specific_price_rule_condition_group = sprc.id_specific_price_rule_condition_group
					WHERE sprcg.id_specific_price_rule = ' . $spr->id);
                    foreach (explode('|', $info['conds']) as $or) {
                        $or = trim($or);
                        $id_sprcg = (int) Db::getInstance()->getValue('SELECT COALESCE(MAX(id_specific_price_rule_condition_group), 0) + 1 FROM '
                                        . _DB_PREFIX_ . 'specific_price_rule_condition_group');
                        Db::getInstance()->insert('specific_price_rule_condition_group', [
                            'id_specific_price_rule_condition_group' => $id_sprcg,
                            'id_specific_price_rule' => (int) $spr->id
                                ], false, true, Db::INSERT_IGNORE);
                        foreach (explode('&', $or) as $and) {
                            $and = trim($and);
                            $type = explode(':', $and, 2);
                            if (trim($type[0]) === 'category') {
                                $row = $this->getCategoryByName($id_lang, trim($type[1]));
                                if ($row) {
                                    Db::getInstance()->insert('specific_price_rule_condition', [
                                        'id_specific_price_rule_condition_group' => $id_sprcg,
                                        'type' => 'category',
                                        'value' => (int) $row['id_category']
                                    ]);
                                } else {
                                    $this->warnings[] = sprintf(
                                            $this->module->l('Category "%s" does not exist. Import them first, then the discounts.', 'EIImport'),
                                            Tools::safeOutput(trim($type[1]))
                                    );
                                }
                            } elseif (trim($type[0]) === 'manufacturer') {
                                $row = Manufacturer::getIdByName(trim($type[1]));
                                if ($row) {
                                    Db::getInstance()->insert('specific_price_rule_condition', [
                                        'id_specific_price_rule_condition_group' => $id_sprcg,
                                        'type' => 'manufacturer',
                                        'value' => (int) $row['id_manufacturer']
                                    ]);
                                } else {
                                    $this->warnings[] = sprintf(
                                            $this->module->l('Manufacturer "%s" does not exist. Import them first, then the discounts.', 'EIImport'),
                                            Tools::safeOutput(trim($type[1]))
                                    );
                                }
                            } elseif (trim($type[0]) === 'supplier') {
                                $row = Supplier::getIdByName(trim($type[1]));
                                if ($row) {
                                    Db::getInstance()->insert('specific_price_rule_condition', [
                                        'id_specific_price_rule_condition_group' => $id_sprcg,
                                        'type' => 'supplier',
                                        'value' => (int) $row['id_supplier']
                                    ]);
                                } else {
                                    $this->warnings[] = sprintf(
                                            $this->module->l('Supplier "%s" does not exist. Import them first, then the discounts.', 'EIImport'),
                                            Tools::safeOutput(trim($type[1]))
                                    );
                                }
                            } elseif (trim($type[0]) === 'attribute') {
                                $attr = explode(' ^ ', trim($type[1]));
                                $row = $this->getAttributeGroupByName($id_lang, trim($attr[0]));
                                if (!$row) {
                                    $obj = new AttributeGroup();
                                    $obj->group_type = pSQL(trim($attr[2]));
                                    $obj->is_color_group = $obj->group_type === 'color';
                                    $obj->position = AttributeGroup::getHigherPosition() + 1;
                                    $obj->name = self::createMultiLangField(trim($attr[0]));
                                    $obj->public_name = trim($attr[1]);
                                    $obj->add();
                                    $row['id_attribute_group'] = $obj->id;
                                    AttributeGroup::cleanPositions();
                                }
                                $row2 = $this->getAttributeByName($id_lang, $row['id_attribute_group'], trim($attr[3]));
                                if (!$row2) {
                                    $obj = new EIAttribute();
                                    $obj->id_attribute_group = $row['id_attribute_group'];
                                    $obj->position = EIAttribute::getHigherPosition($row['id_attribute_group']) + 1;
                                    $obj->color = trim($attr[4]);
                                    $obj->name = self::createMultiLangField(trim($attr[3]));
                                    $obj->add();
                                    $row2['id_attribute'] = $obj->id;
                                    $obj->cleanPositions((int) $row['id_attribute_group'], false);
                                }
                                Db::getInstance()->insert('specific_price_rule_condition', [
                                    'id_specific_price_rule_condition_group' => $id_sprcg,
                                    'type' => 'attribute',
                                    'value' => (int) $row2['id_attribute']
                                ]);
                            } elseif (trim($type[0]) === 'feature') {
                                $attr = explode(' ^ ', trim($type[1]));
                                $row = $this->getFeatureByName($id_lang, trim($attr[0]));
                                if (!$row) {
                                    $row['id_feature'] = (int) Feature::addFeatureImport(trim($attr[0]));
                                    // clean feature positions to avoid conflict
                                    Feature::cleanPositions();
                                }
                                $row2 = $this->getFeatureValueByName($id_lang, $row['id_feature'], trim($attr[1]));
                                if (!$row2) {
                                    $row2['id_feature_value'] = (int) FeatureValue::addFeatureValueImport($row['id_feature'], trim($attr[1]), null, null, (int) $attr[2]);
                                }
                                Db::getInstance()->insert('specific_price_rule_condition', [
                                    'id_specific_price_rule_condition_group' => $id_sprcg,
                                    'type' => 'feature',
                                    'value' => (int) $row2['id_feature_value']
                                ]);
                            }
                        }
                    }
                }
                $spr->apply();
            }
        }
        if ($sp && empty($info['is_rule'])) {
            self::arrayWalk($info, [self::class, 'fillInfo'], $sp);
            $sp->id_specific_price_rule = isset($spr->id) ? $spr->id : (!empty($info['id_specific_price_rule']) ? (int) $info['id_specific_price_rule'] : 0);
            $sp->id_shop_group = Shop::getGroupFromShop($sp->id_shop);
            $sp->id_cart = 0;

            if (is_numeric($info['id_currency'])) {
                $my_group = new Currency((int) $info['id_currency']);
                if (Validate::isLoadedObject($my_group)) {
                    $sp->id_currency = (int) $info['id_currency'];
                } else {
                    $sp->id_currency = 0;
                }
            } elseif (($res = (int) Currency::getIdByIsoCode($info['id_currency']))) {
                $sp->id_currency = $res;
            } else {
                $sp->id_currency = 0;
            }

            if (is_numeric($info['id_group'])) {
                $my_group = new Group((int) $info['id_group']);
                if (Validate::isLoadedObject($my_group)) {
                    $sp->id_group = (int) $info['id_group'];
                } else {
                    $sp->id_group = 0;
                }
            } elseif ($info['id_group'] && ($res = Group::searchByName($info['id_group']))) {
                $sp->id_group = (int) $res['id_group'];
            } else {
                $sp->id_group = 0;
            }
            if (is_numeric($info['id_customer'])) {
                $sp->id_customer = (int) $info['id_customer'];
            } elseif ($info['id_customer'] && Validate::isEmail($info['id_customer'])) {
                $sp->id_customer = (int) Customer::customerExists(trim($info['id_customer']), true);
            } else {
                $sp->id_customer = 0;
            }

            if (($field_error = $sp->validateFields(false, true)) !== true) {
                $this->errors[] = $field_error . Db::getInstance()->getMsgError()
                        . '<br> ' . $this->module->l('Location', 'EIImport') . ': ' . __CLASS__ . ':' . __LINE__ . '.'
                        . '<br> ' . $this->module->l('Row number in file', 'EIImport') . ": {$this->row}. " . $this->module->l('Problematic row', 'EIImport') . ': <pre>' . print_r($info, true);
                return false;
            }

            if (!$validateOnly) {
                $specific_price = Db::getInstance()->getRow('SELECT *
                        FROM `' . _DB_PREFIX_ . 'specific_price`
                        WHERE `id_product` = ' . (int) $sp->id_product . '
                            AND `id_product_attribute` = ' . (int) $sp->id_product_attribute . '
                            AND `id_customer` = ' . (int) $sp->id_customer . '
                            AND `id_cart` = 0
                            AND `from` = "' . $sp->from . '"' . '
                            AND `to` = "' . $sp->to . '"' . '
                            AND `id_shop` = ' . (int) $sp->id_shop . '
                            AND `id_shop_group` = ' . (int) $sp->id_shop_group . '
                            AND `id_currency` = ' . (int) $sp->id_currency . '
                            AND `id_country` = ' . (int) $sp->id_country . '
                            AND `id_group` = ' . (int) $sp->id_group . '
                            AND `from_quantity` = ' . (int) $sp->from_quantity . '
                            AND `id_specific_price_rule` = ' . (int) $sp->id_specific_price_rule, false);

                if (!$sp->id) {
                    if (!$specific_price) {
                        $succ2 = $sp->add();
                    }
                } elseif (!ObjectModel::existsInDatabase((int) $info['id_specific_price'], 'specific_price')) {
                    if (!$specific_price) {
                        $succ2 = $sp->add();
                    }
                } elseif ($this->targetAction != 'insert') {
                    $succ2 = $sp->update();
                }
            }
        }
//        if (!empty($info['name']) && !$succ1) {
//            $this->errors[] = Db::getInstance()->getMsgError() . ' ' . sprintf(
//                            $this->module->l('Catalog Price Rule %1$s cannot be saved.', 'EIImport'),
//                            Tools::safeOutput($info['name'])
//                    )
//                    . '<br> ' . $this->module->l('Location', 'EIImport') . ': ' . __CLASS__ . ':' . __LINE__ . '.'
//                    . '<br> ' . $this->module->l('Row number in file', 'EIImport') . ": {$this->row}. " . $this->module->l('Problematic row', 'EIImport') . ': <pre>' . print_r($info, true);
//        }
//        if (empty($info['is_rule']) && !$succ2) {
//            $this->errors[] = Db::getInstance()->getMsgError() . ' ' . $this->module->l('Specific Price Rule cannot be saved.', 'EIImport')
//                    . '<br> ' . $this->module->l('Location', 'EIImport') . ': ' . __CLASS__ . ':' . __LINE__ . '.'
//                    . '<br> ' . $this->module->l('Row number in file', 'EIImport') . ": {$this->row}. " . $this->module->l('Problematic row', 'EIImport') . ': <pre>' . print_r($info, true);
//        }
    }

    public function carrierImport($offset = false, $limit = false, $validateOnly = false)
    {
        // Fill self::$column_mask array
        $this->receiveColumns();
        // Open the file
        $handle = $this->openCsvFile($offset);
        if (!$handle) {
            return false;
        }

        $default_language_id = (int) Configuration::get('PS_LANG_DEFAULT');
        $id_lang = Tools::getValue('language');
        if (!Validate::isUnsignedId($id_lang)) {
            $id_lang = $default_language_id;
        }

        self::setLocale();

        $shop_is_feature_active = Shop::isFeatureActive();
        //        $regenerate = Tools::getValue('regenerate');
        $force_id = Tools::getValue('keep_id');

        $line_count = 0;
        for ($current_line = 0; ($line = fgetcsv($handle, MAX_LINE_SIZE, $this->separator, $this->enclosure)) && (!$limit || $current_line < $limit); $current_line++) {
            $line_count++;
            $this->row = $offset + $line_count + 1;
            if ($this->convert) {
                $line = $this->utf8EncodeArray($line);
            }

            $isEmpty = true;
            foreach ($line as $ln) {
                if ($ln) {
                    $isEmpty = false;
                    break;
                }
            }

            if ($isEmpty) {
                $this->warnings[] = $this->module->l('There is an empty row in the file that won\'t be imported.', 'EIImport');
                continue;
            }

            $info = self::getMaskedRow($line);

            // Set carrier tax rules group
            if (!empty($info['id_tax_rules_group']) && !is_numeric($info['id_tax_rules_group'])) {
                if (($id_tax_rules_group = TaxRulesGroup::getIdByName(trim($info['id_tax_rules_group'])))) {
                    $info['id_tax_rules_group'] = (int) $id_tax_rules_group;
                }
            }

            $this->carrierImportOne(
                    $info,
                    $default_language_id,
                    $id_lang,
                    $shop_is_feature_active,
                    // $regenerate,
                    $force_id,
                    $validateOnly
            );
        }
        $this->closeCsvFile($handle);

        return $line_count;
    }

    public function customerImport($offset = false, $limit = false, $validateOnly = false)
    {
        // Fill self::$column_mask array
        $this->receiveColumns();
        $handle = $this->openCsvFile($offset);
        if (!$handle) {
            return false;
        }

        $default_language_id = (int) Configuration::get('PS_LANG_DEFAULT');
        $id_lang = Tools::getValue('language');
        if (!Validate::isUnsignedId($id_lang)) {
            $id_lang = $default_language_id;
        }

        self::setLocale();

        $shop_is_feature_active = Shop::isFeatureActive();
        $force_id = Tools::getValue('keep_id');

        $line_count = 0;
        for ($current_line = 0; ($line = fgetcsv($handle, MAX_LINE_SIZE, $this->separator, $this->enclosure)) && (!$limit || $current_line < $limit); $current_line++) {
            $line_count++;
            $this->row = $offset + $line_count + 1;
            if ($this->convert) {
                $line = $this->utf8EncodeArray($line);
            }

            $isEmpty = true;
            foreach ($line as $ln) {
                if ($ln) {
                    $isEmpty = false;
                    break;
                }
            }

            if ($isEmpty) {
                $this->warnings[] = $this->module->l('There is an empty row in the file that won\'t be imported.', 'EIImport');

                continue;
            }

            $info = self::getMaskedRow($line);

            // Set customer lang
            if (!empty($info['id_lang']) && !is_numeric($info['id_lang'])) {
                if (($id_lang = Language::getIdByIso(trim($info['id_lang'])))) {
                    $info['id_lang'] = (int) $id_lang;
                }
            }

            // Set customer password
            if (!empty($info['new_passwd'])) {
                $info['passwd'] = $info['new_passwd'];
                $info['encypt_passwd'] = true;
            } else {
                $info['encypt_passwd'] = false;
            }

            $this->customerImportOne(
                    $info,
                    $default_language_id,
                    $id_lang,
                    $shop_is_feature_active,
                    $force_id,
                    $validateOnly
            );
        }
        $this->closeCsvFile($handle);

        return $line_count;
    }

    public function featureImport($offset = false, $limit = false, $validateOnly = false)
    {
        // Fill self::$column_mask array
        $this->receiveColumns();
        $handle = $this->openCsvFile($offset);
        if (!$handle) {
            return false;
        }

        $default_language_id = (int) Configuration::get('PS_LANG_DEFAULT');
        $id_lang = Tools::getValue('language');
        if (!Validate::isUnsignedId($id_lang)) {
            $id_lang = $default_language_id;
        }

        self::setLocale();

        $shop_is_feature_active = Shop::isFeatureActive();
        $force_id = Tools::getValue('keep_id');

        $line_count = 0;
        for ($current_line = 0; ($line = fgetcsv($handle, MAX_LINE_SIZE, $this->separator, $this->enclosure)) && (!$limit || $current_line < $limit); $current_line++) {
            $line_count++;
            $this->row = $offset + $line_count + 1;
            if ($this->convert) {
                $line = $this->utf8EncodeArray($line);
            }

            $isEmpty = true;
            foreach ($line as $ln) {
                if ($ln) {
                    $isEmpty = false;
                    break;
                }
            }

            if ($isEmpty) {
                $this->warnings[] = $this->module->l('There is an empty row in the file that won\'t be imported.', 'EIImport');

                continue;
            }

            $info = self::getMaskedRow($line);

            $this->featureImportOne(
                    $info,
                    $id_lang,
                    $shop_is_feature_active,
                    $force_id,
                    $validateOnly
            );
        }
        $this->closeCsvFile($handle);

        return $line_count;
    }
    
    protected function featureImportOne($info, $id_lang, $shop_is_feature_active, $force_id, $validateOnly = false)
    {
        $ignoreFeature = false;
        if ($this->targetAction == 'insert') {
            if (!$this->getFeatureInsertion($info, $id_lang, $force_id)) {
                $ignoreFeature = true;
            }
        } elseif ($this->targetAction == 'update') {
            if (!$this->getFeatureUpdate($info, $id_lang)) {
                $ignoreFeature = true;
            }
        } else {
            if (!$this->getFeatureUpdate($info, $id_lang) && !$this->getFeatureInsertion($info, $id_lang, $force_id)) {
                $ignoreFeature = true;
            }
        }


        if (isset($info['id']) && (int) $info['id'] && ($force_id || ObjectModel::existsInDatabase((int) $info['id'], 'feature'))) {
            $feature = new Feature((int) $info['id']);
        } else {
            $feature = new Feature();
            self::setDefaultValues($info);
        }

        self::arrayWalk($info, [self::class, 'fillInfo'], $feature);
        $feature->id_shop_list = $this->shops;

        $res = false;
        if (($field_error = $feature->validateFields(false, true)) === true &&
                ($lang_field_error = $feature->validateFieldsLang(false, true)) === true) {
            if ($feature->id && ObjectModel::existsInDatabase((int) $info['id'], 'feature')) {
                $res = ($validateOnly || $ignoreFeature || $feature->update());
            }
            $feature->force_id = (bool) $force_id;
            if (!$res) {
                $res = ($validateOnly || $ignoreFeature || $feature->add());
            }

            if (!$validateOnly && $res) {
                // Associate feature to group shop
                // maybe id_shop_list can do this
//                if ($shop_is_feature_active) {
//                    $data = array();
//                    foreach ($this->shops as $id_shop) {
//                        $data[] = array(
//                            Feature::$definition['primary'] => (int) $feature->id,
//                            'id_shop' => (int) $id_shop,
//                        );
//                    }
//                    if ($data) {
//                        Db::getInstance()->insert(Feature::$definition['table'] . '_shop', $data, false, true, Db::INSERT_IGNORE);
//                    }
//                }

                // Feature Value Import
                if (!empty($info['feature_value'])) {
                    if ($this->targetAction != 'insert' && Tools::getValue('value_truncate')) {
                        Db::getInstance()->execute('DELETE fv, fvl FROM ' . _DB_PREFIX_ . 'feature_value fv
                                           LEFT JOIN ' . _DB_PREFIX_ . 'feature_value_lang fvl ON fv.id_feature_value = fvl.id_feature_value# AND fvl.id_lang = ' . $id_lang . '
                                           WHERE fv.id_feature = ' . (int) $feature->id);
                    }

                    if ($this->targetAction == 'insert') {
                        if (!$this->getFeatureValueInsertion($info, $id_lang, $force_id)) {
                            return false;
                        }
                    } elseif ($this->targetAction == 'update') {
                        if (!$this->getFeatureValueUpdate($info, $id_lang)) {
                            return false;
                        }
                    } else {
                        if (!$this->getFeatureValueUpdate($info, $id_lang) && !$this->getFeatureValueInsertion($info, $id_lang, $force_id)) {
                            return false;
                        }
                    }
                    if (isset($info['feature_value_id']) && (int) $info['feature_value_id'] && ($force_id || ObjectModel::existsInDatabase((int) $info['feature_value_id'], 'feature_value'))) {
                        $featureValue = new FeatureValue((int) $info['feature_value_id']);
                        $featureValue->id = (int) $info['feature_value_id'];
                    } else {
                        $featureValue = new FeatureValue();
                    }

                    $featureValue->force_id = (bool) $force_id;
                    $featureValue->id_feature = $feature->id;
                    $featureValue->custom = isset($info['feature_value_custom']) ? (int) $info['feature_value_custom'] : 0;
                    $featureValue->value[$id_lang] = trim($info['feature_value']);
                    if (isset($featureValue->id) && ObjectModel::existsInDatabase((int) $featureValue->id, 'feature_value')) {
                        $res = $featureValue->update();
                    } else {
                        $res = $featureValue->add();
                    }
                }
            }
        }

        if (!$res) {
            if (!$validateOnly) {
                $this->errors[] = Db::getInstance()->getMsgError() . ' ' . sprintf(
                                $this->module->l('%1$s (ID: %2$s) cannot be saved.', 'EIImport'),
                                !empty($info['name']) ? Tools::safeOutput($info['name']) : 'No Name',
                                !empty($info['id']) ? Tools::safeOutput($info['id']) : 'No ID'
                        )
                        . '<br> ' . $this->module->l('Location', 'EIImport') . ': ' . __CLASS__ . ':' . __LINE__ . '.'
                        . '<br> ' . $this->module->l('Row number in file', 'EIImport') . ": {$this->row}. " . $this->module->l('Problematic row', 'EIImport') . ': <pre>' . print_r($info, true);
            }
            if ($field_error !== true || isset($lang_field_error) && $lang_field_error !== true) {
                $this->errors[] = ($field_error !== true ? $field_error : '') . (isset($lang_field_error) && $lang_field_error !== true ? $lang_field_error : '') .
                        Db::getInstance()->getMsgError()
                        . '<br> ' . $this->module->l('Location', 'EIImport') . ': ' . __CLASS__ . ':' . __LINE__ . '.'
                        . '<br> ' . $this->module->l('Row number in file', 'EIImport') . ": {$this->row}. " . $this->module->l('Problematic row', 'EIImport') . ': <pre>' . print_r($info, true);
            }
        }
    }
    
    private function getFeatureUpdate(&$info, $id_lang)
    {
        $id_feature = null;
        if ($this->updateBy == 'id' && !empty($info['id'])) {
            $id_feature = (int) Db::getInstance()->getValue('
                        SELECT id_feature
                        FROM `' . _DB_PREFIX_ . 'feature`
                        WHERE `id_feature` = ' . (int) $info['id'], false);
        } elseif ($this->updateBy == 'name' && !empty($info['name'])) {
            $id_feature = (int) Db::getInstance()->getValue('
                        SELECT f.id_feature
                        FROM `' . _DB_PREFIX_ . 'feature` f
                        LEFT JOIN `' . _DB_PREFIX_ . 'feature_lang` fl ON f.id_feature = fl.id_feature AND fl.id_lang = ' . (int) $id_lang . '
                        WHERE fl.`name` = \'' . pSQL($info['name']) . '\'', false);
        }
        if ($id_feature && $this->targetAction != 'insert') {
            $info['id'] = $id_feature;
        }
        return $id_feature;
    }

    private function getFeatureInsertion(&$info, $id_lang, $force_id)
    {
        if ($force_id && !empty($info['id']) && ObjectModel::existsInDatabase((int) $info['id'], 'feature')) {
            return false;
        }
        if ($this->nameExists == 'ignore' && !empty($info['name']) &&
                Db::getInstance()->getRow('
                        SELECT *
                        FROM `' . _DB_PREFIX_ . 'feature` f
                        LEFT JOIN `' . _DB_PREFIX_ . 'feature_lang` fl ON f.id_feature = fl.id_feature AND fl.id_lang = ' . (int) $id_lang . '
                        WHERE fl.`name` = \'' . pSQL($info['name']) . '\'', false)) {
            return false;
        }

        if (!$force_id) {
            unset($info['id']);
        }
        return true;
    }
    
    private function getFeatureValueUpdate(&$info, $id_lang)
    {
        $id_feature_value = null;
        if ($this->updateBy == 'id' && !empty($info['feature_value_id'])) {
            $id_feature_value = (int) Db::getInstance()->getValue('
                        SELECT id_feature_value
                        FROM `' . _DB_PREFIX_ . 'feature_value`
                        WHERE `id_feature_value` = ' . (int) $info['feature_value_id'], false);
        } elseif ($this->updateBy == 'name' && !empty($info['feature_value'])) {
            $id_feature_value = (int) Db::getInstance()->getValue('
                        SELECT fv.id_feature_value
                        FROM `' . _DB_PREFIX_ . 'feature_value` fv
                        LEFT JOIN `' . _DB_PREFIX_ . 'feature_value_lang` fvl ON fv.id_feature_value = fvl.id_feature_value AND fvl.id_lang = ' . (int) $id_lang . '
                        WHERE fvl.`value` = \'' . pSQL($info['feature_value']) . '\'', false);
        }
        if ($id_feature_value && $this->targetAction != 'insert') {
            $info['feature_value_id'] = $id_feature_value;
        }
        return $id_feature_value;
    }

    private function getFeatureValueInsertion(&$info, $id_lang, $force_id)
    {
        if ($force_id && !empty($info['feature_value_id']) && ObjectModel::existsInDatabase((int) $info['feature_value_id'], 'feature_value')) {
            return false;
        }
        if ($this->nameExists == 'ignore' && !empty($info['feature_value']) &&
                Db::getInstance()->getRow('
                        SELECT *
                        FROM `' . _DB_PREFIX_ . 'feature_value` fv
                        LEFT JOIN `' . _DB_PREFIX_ . 'feature_value_lang` fvl ON fv.id_feature_value = fvl.id_feature_value AND fvl.id_lang = ' . (int) $id_lang . '
                        WHERE fvl.`value` = \'' . pSQL($info['feature_value']) . '\'', false)) {
            return false;
        }

        if (!$force_id) {
            unset($info['feature_value_id']);
        }
        return true;
    }

    public function attributeImport($offset = false, $limit = false, $validateOnly = false)
    {
        // Fill self::$column_mask array
        $this->receiveColumns();
        $handle = $this->openCsvFile($offset);
        if (!$handle) {
            return false;
        }

        $default_language_id = (int) Configuration::get('PS_LANG_DEFAULT');
        $id_lang = Tools::getValue('language');
        if (!Validate::isUnsignedId($id_lang)) {
            $id_lang = $default_language_id;
        }

        self::setLocale();

        $shop_is_feature_active = Shop::isFeatureActive();
        $force_id = Tools::getValue('keep_id');

        $line_count = 0;
        for ($current_line = 0; ($line = fgetcsv($handle, MAX_LINE_SIZE, $this->separator, $this->enclosure)) && (!$limit || $current_line < $limit); $current_line++) {
            $line_count++;
            $this->row = $offset + $line_count + 1;
            if ($this->convert) {
                $line = $this->utf8EncodeArray($line);
            }

            $isEmpty = true;
            foreach ($line as $ln) {
                if ($ln) {
                    $isEmpty = false;
                    break;
                }
            }

            if ($isEmpty) {
                $this->warnings[] = $this->module->l('There is an empty row in the file that won\'t be imported.', 'EIImport');

                continue;
            }

            $info = self::getMaskedRow($line);

            $this->attributeImportOne(
                    $info,
                    $id_lang,
                    $shop_is_feature_active,
                    $force_id,
                    $validateOnly
            );
        }
        $this->closeCsvFile($handle);

        return $line_count;
    }
    
    protected function attributeImportOne($info, $id_lang, $shop_is_feature_active, $force_id, $validateOnly = false)
    {
        $ignoreAg = false;
        if ($this->targetAction == 'insert') {
            if (!$this->getAttributeInsertion($info, $id_lang, $force_id)) {
                $ignoreAg = true;
            }
        } elseif ($this->targetAction == 'update') {
            if (!$this->getAttributeUpdate($info, $id_lang)) {
                $ignoreAg = true;
            }
        } else {
            if (!$this->getAttributeUpdate($info, $id_lang) && !$this->getAttributeInsertion($info, $id_lang, $force_id)) {
                $ignoreAg = true;
            }
        }


        if (isset($info['id']) && (int) $info['id'] && ($force_id || ObjectModel::existsInDatabase((int) $info['id'], 'attribute_group'))) {
            $attributeGroup = new AttributeGroup((int) $info['id']);
        } else {
            $attributeGroup = new AttributeGroup();
            self::setDefaultValues($info);
        }
        $attributeGroup->is_color_group = $attributeGroup->group_type == 'color' ? 1 : 0;

        self::arrayWalk($info, [self::class, 'fillInfo'], $attributeGroup);
        $attributeGroup->id_shop_list = $this->shops;

        $res = false;
        if (($field_error = $attributeGroup->validateFields(false, true)) === true &&
                ($lang_field_error = $attributeGroup->validateFieldsLang(false, true)) === true) {
            if ($attributeGroup->id && ObjectModel::existsInDatabase((int) $info['id'], 'attribute_group')) {
                $res = ($validateOnly || $ignoreAg || $attributeGroup->update());
            }
            $attributeGroup->force_id = (bool) $force_id;
            if (!$res) {
                $res = ($validateOnly || $ignoreAg || $attributeGroup->add());
            }

            if (!$validateOnly && $res) {
                // Associate AttributeGroup to group shop
                // maybe id_shop_list can do this
//                if ($shop_is_feature_active) {
//                    $data = array();
//                    foreach ($this->shops as $id_shop) {
//                        $data[] = array(
//                            AttributeGroup::$definition['primary'] => (int) $attributeGroup->id,
//                            'id_shop' => (int) $id_shop,
//                        );
//                    }
//                    if ($data) {
//                        Db::getInstance()->insert(AttributeGroup::$definition['table'] . '_shop', $data, false, true, Db::INSERT_IGNORE);
//                    }
//                }

                // Attribute Import
                if (!empty($info['attribute_name'])) {
                    if ($this->targetAction != 'insert' && Tools::getValue('value_truncate')) {
                        Db::getInstance()->execute('DELETE a, al, ash FROM ' . _DB_PREFIX_ . 'attribute a
                                           LEFT JOIN ' . _DB_PREFIX_ . 'attribute_lang al ON a.id_attribute = al.id_attribute# AND al.id_lang = ' . $id_lang . '
                                           LEFT JOIN ' . _DB_PREFIX_ . 'attribute_shop ash ON a.id_attribute = ash.id_attribute
                                           WHERE a.id_attribute_group = ' . (int) $attributeGroup->id . ' AND ash.id_shop IN (' . implode(',', $this->shops) . ')');
                    }
                    
                    if ($this->targetAction == 'insert') {
                        if (!$this->getAttributeValueInsertion($info, $id_lang, $force_id)) {
                            return false;
                        }
                    } elseif ($this->targetAction == 'update') {
                        if (!$this->getAttributeValueUpdate($info, $id_lang)) {
                            return false;
                        }
                    } else {
                        if (!$this->getAttributeValueUpdate($info, $id_lang) && !$this->getAttributeValueInsertion($info, $id_lang, $force_id)) {
                            return false;
                        }
                    }
                    
                    if (isset($info['attribute_id']) && (int) $info['attribute_id'] && ($force_id || ObjectModel::existsInDatabase((int) $info['attribute_id'], 'attribute'))) {
                        $attribute = new EIAttribute((int) $info['attribute_id']);
                        $attribute->id = (int) $info['attribute_id'];
                    } else {
                        $attribute = new EIAttribute();
                    }

                    $attribute->force_id = (bool) $force_id;
                    $attribute->id_attribute_group = $attributeGroup->id;
                    $attribute->color = isset($info['attribute_color']) ? trim($info['attribute_color']) : '';
                    $attribute->position = isset($info['attribute_position']) ? (int) $info['attribute_position'] : 0;
                    $attribute->name[$id_lang] = trim($info['attribute_name']);
                    $attribute->id_shop_list = $this->shops;
                    if (isset($attribute->id) && ObjectModel::existsInDatabase((int) $attribute->id, 'attribute')) {
                        $res = $attribute->update();
                    } else {
                        $res = $attribute->add();
                    }

                    // Associate AttributeGroup to group shop
                    // maybe id_shop_list can do this
//                    if ($res && $shop_is_feature_active) {
//                        $data = array();
//                        foreach ($this->shops as $id_shop) {
//                            $data[] = array(
//                                Attribute::$definition['primary'] => (int) $attribute->id,
//                                'id_shop' => (int) $id_shop,
//                            );
//                        }
//                        if ($data) {
//                            Db::getInstance()->insert(Attribute::$definition['table'] . '_shop', $data, false, true, Db::INSERT_IGNORE);
//                        }
//                    }
                }
            }
        }

        if (!$res) {
            if (!$validateOnly) {
                $this->errors[] = Db::getInstance()->getMsgError() . ' ' . sprintf(
                                $this->module->l('%1$s (ID: %2$s) cannot be saved.', 'EIImport'),
                                !empty($info['name']) ? Tools::safeOutput($info['name']) : 'No Name',
                                !empty($info['id']) ? Tools::safeOutput($info['id']) : 'No ID'
                        )
                        . '<br> ' . $this->module->l('Location', 'EIImport') . ': ' . __CLASS__ . ':' . __LINE__ . '.'
                        . '<br> ' . $this->module->l('Row number in file', 'EIImport') . ": {$this->row}. " . $this->module->l('Problematic row', 'EIImport') . ': <pre>' . print_r($info, true);
            }
            if ($field_error !== true || isset($lang_field_error) && $lang_field_error !== true) {
                $this->errors[] = ($field_error !== true ? $field_error : '') . (isset($lang_field_error) && $lang_field_error !== true ? $lang_field_error : '') .
                        Db::getInstance()->getMsgError()
                        . '<br> ' . $this->module->l('Location', 'EIImport') . ': ' . __CLASS__ . ':' . __LINE__ . '.'
                        . '<br> ' . $this->module->l('Row number in file', 'EIImport') . ": {$this->row}. " . $this->module->l('Problematic row', 'EIImport') . ': <pre>' . print_r($info, true);
            }
        }
    }
    
    private function getAttributeUpdate(&$info, $id_lang)
    {
        $id_attribute_group = null;
        if ($this->updateBy == 'id' && !empty($info['id'])) {
            $id_attribute_group = (int) Db::getInstance()->getValue('
                        SELECT id_attribute_group
                        FROM `' . _DB_PREFIX_ . 'attribute_group`
                        WHERE `id_attribute_group` = ' . (int) $info['id'], false);
        } elseif ($this->updateBy == 'name' && !empty($info['name'])) {
            $id_attribute_group = (int) Db::getInstance()->getValue('
                        SELECT ag.id_attribute_group
                        FROM `' . _DB_PREFIX_ . 'attribute_group` ag
                        LEFT JOIN `' . _DB_PREFIX_ . 'attribute_group_lang` agl ON ag.id_attribute_group = agl.id_attribute_group AND agl.id_lang = ' . (int) $id_lang . '
                        WHERE agl.`name` = \'' . pSQL($info['name']) . '\'', false);
        }
        if ($id_attribute_group && $this->targetAction != 'insert') {
            $info['id'] = $id_attribute_group;
        }
        return $id_attribute_group;
    }

    private function getAttributeInsertion(&$info, $id_lang, $force_id)
    {
        if ($force_id && !empty($info['id']) && ObjectModel::existsInDatabase((int) $info['id'], 'attribute_group')) {
            return false;
        }
        if ($this->nameExists == 'ignore' && !empty($info['name']) &&
                Db::getInstance()->getRow('
                        SELECT *
                        FROM `' . _DB_PREFIX_ . 'attribute_group` ag
                        LEFT JOIN `' . _DB_PREFIX_ . 'attribute_group_lang` agl ON ag.id_attribute_group = agl.id_attribute_group AND agl.id_lang = ' . (int) $id_lang . '
                        WHERE agl.`name` = \'' . pSQL($info['name']) . '\'', false)) {
            return false;
        }

        if (!$force_id) {
            unset($info['id']);
        }
        return true;
    }
    
    private function getAttributeValueUpdate(&$info, $id_lang)
    {
        $id_attribute = null;
        if ($this->updateBy == 'id' && !empty($info['attribute_id'])) {
            $id_attribute = (int) Db::getInstance()->getValue('
                        SELECT id_attribute
                        FROM `' . _DB_PREFIX_ . 'attribute`
                        WHERE `id_attribute` = ' . (int) $info['attribute_id'], false);
        } elseif ($this->updateBy == 'name' && !empty($info['attribute_name'])) {
            $id_attribute = (int) Db::getInstance()->getValue('
                        SELECT a.id_attribute
                        FROM `' . _DB_PREFIX_ . 'attribute` a
                        LEFT JOIN `' . _DB_PREFIX_ . 'attribute_lang` al ON a.id_attribute = al.id_attribute AND al.id_lang = ' . (int) $id_lang . '
                        WHERE al.`name` = \'' . pSQL($info['attribute_name']) . '\'', false);
        }
        if ($id_attribute && $this->targetAction != 'insert') {
            $info['attribute_id'] = $id_attribute;
        }
        return $id_attribute;
    }

    private function getAttributeValueInsertion(&$info, $id_lang, $force_id)
    {
        if ($force_id && !empty($info['attribute_id']) && ObjectModel::existsInDatabase((int) $info['attribute_id'], 'attribute')) {
            return false;
        }
        if ($this->nameExists == 'ignore' && !empty($info['attribute_name']) &&
                Db::getInstance()->getRow('
                        SELECT *
                        FROM `' . _DB_PREFIX_ . 'attribute` a
                        LEFT JOIN `' . _DB_PREFIX_ . 'attribute_lang` al ON a.id_attribute = al.id_attribute AND al.id_lang = ' . (int) $id_lang . '
                        WHERE al.`name` = \'' . pSQL($info['attribute_name']) . '\'', false)) {
            return false;
        }

        if (!$force_id) {
            unset($info['attribute_id']);
        }
        return true;
    }

    protected function customerImportOne($info, $default_language_id, $id_lang, $shop_is_feature_active, $force_id, $validateOnly = false)
    {
        if ($this->targetAction == 'insert') {
            if (!$this->getCustomerInsertion($info, $force_id)) {
                return false;
            }
        } elseif ($this->targetAction == 'update') {
            if (!$this->getCustomerUpdate($info)) {
                return false;
            }
        } else {
            if (!$this->getCustomerUpdate($info) && !$this->getCustomerInsertion($info, $force_id)) {
                return false;
            }
        }

        if (isset($info['id']) && (int) $info['id'] && ($force_id || Customer::customerIdExistsStatic((int) $info['id']))) {
            $customer = new Customer((int) $info['id']);
        } else {
            $customer = new Customer();
            self::setDefaultValues($info);
        }

        // Correct some formats
        if (!empty($info['last_passwd_gen'])) {
            $info['last_passwd_gen'] = date("Y-m-d H:i:s", strtotime($info['last_passwd_gen']));
            if (Tools::substr($info['last_passwd_gen'], 0, 10) === '1969-12-31' || Tools::substr($info['last_passwd_gen'], 0, 1) === '-') {
                $info['last_passwd_gen'] = date("Y-m-d H:i:s");
            }
        }
        if (!empty($info['newsletter_date_add'])) {
            $info['newsletter_date_add'] = date("Y-m-d H:i:s", strtotime($info['newsletter_date_add']));
            if (Tools::substr($info['newsletter_date_add'], 0, 10) === '1969-12-31' || Tools::substr($info['newsletter_date_add'], 0, 1) === '-') {
                $info['newsletter_date_add'] = '0000-00-00 00:00:00';
            }
        }
        if (!empty($info['date_add'])) {
            $info['date_add'] = date("Y-m-d H:i:s", strtotime($info['date_add']));
            if (Tools::substr($info['date_add'], 0, 10) === '1969-12-31' || Tools::substr($info['date_add'], 0, 1) === '-') {
                $info['date_add'] = date("Y-m-d H:i:s");
            }
        }
        if (!empty($info['birthday'])) {
            $info['birthday'] = date("Y-m-d", strtotime($info['birthday']));
            if (Tools::substr($info['birthday'], 0, 10) === '1969-12-31' || Tools::substr($info['birthday'], 0, 1) === '-') {
                $info['birthday'] = '0000-00-00';
            }
        }

        $customer_exist = false;
        $autodate = true;

        $customer_groups = [];
        if (isset($info['id']) && (int) $info['id'] && Customer::customerIdExistsStatic((int) $info['id']) && Validate::isLoadedObject($customer)) {
            $current_id_customer = (int) $customer->id;
            $current_id_shop = (int) $customer->id_shop;
            $current_id_shop_group = (int) $customer->id_shop_group;
            $customer_exist = true;
            //            $customer_groups = $customer->getGroups();
            $addresses = $customer->getAddresses($default_language_id);
        }

        // Group Import
        if (!empty($info['groups'])) {
            foreach (explode($this->multivalueSeparator, $info['groups']) as $group) {
                $group = trim($group);
                if (empty($group)) {
                    continue;
                }
                $id_group = false;
                if (is_numeric($group) && $group) {
                    $my_group = new Group((int) $group);
                    if (Validate::isLoadedObject($my_group)) {
                        $customer_groups[] = (int) $group;
                    }

                    continue;
                }
                $my_group = Group::searchByName($group);
                if (isset($my_group['id_group']) && $my_group['id_group']) {
                    $id_group = (int) $my_group['id_group'];
                }
                if (!$id_group) {
                    $my_group = new Group();
                    $my_group->name = [$id_lang => $group];
                    if ($id_lang != $default_language_id) {
                        $my_group->name = $my_group->name + [$default_language_id => $group];
                    }
                    $my_group->price_display_method = 1;
                    if (!$validateOnly) {
                        $my_group->add();
                        if (Validate::isLoadedObject($my_group)) {
                            $id_group = (int) $my_group->id;
                        }
                    }
                }
                if ($id_group) {
                    $customer_groups[] = (int) $id_group;
                }
            }
        } elseif (empty($info['groups']) && isset($customer->id) && $customer->id) {
            $customer_groups = [Configuration::get('PS_CUSTOMER_GROUP')];
        }

        if (!empty($info['date_add'])) {
            $autodate = false;
        }

        self::arrayWalk($info, [self::class, 'fillInfo'], $customer);

        if ($customer->passwd && $customer->encypt_passwd) {
            if (version_compare(_PS_VERSION_, '1.7', '>=')) {
                $crypto = new \PrestaShop\PrestaShop\Core\Crypto\Hashing();
                $customer->passwd = $crypto->hash($customer->passwd, _COOKIE_KEY_);
            } else {
                $customer->passwd = Tools::encrypt($customer->passwd);
            }
        }

        $customers_shop = [];
        $customers_shop['shared'] = [];
        $default_shop = new Shop((int) Configuration::get('PS_SHOP_DEFAULT'));
        if ($shop_is_feature_active && $this->shops) {
            foreach ($this->shops as $id_shop) {
                if (empty($id_shop)) {
                    continue;
                }
                $shop = new Shop((int) $id_shop);
                $group_shop = $shop->getGroup();
                if ($group_shop->share_customer) {
                    if (!in_array($group_shop->id, $customers_shop['shared'])) {
                        $customers_shop['shared'][(int) $id_shop] = $group_shop->id;
                    }
                } else {
                    $customers_shop[(int) $id_shop] = $group_shop->id;
                }
            }
        } else {
            $customers_shop[$default_shop->id] = $default_shop->getGroup()->id;
        }

        //        // Set customer lang
        //        if (isset($info['id_lang']) && !empty($info['id_lang']) && !is_numeric($info['id_lang'])) {
        //            if (($id_lang = Language::getIdByIso(trim($info['id_lang'])))) {
        //                $customer->id_lang = (int) $id_lang;
        //            }
        //        }
        // Set temporary for validate field
        $customer->id_shop = $default_shop->id;
        $customer->id_shop_group = $default_shop->getGroup()->id;
        if (isset($info['id_default_group']) && !empty($info['id_default_group']) && !is_numeric($info['id_default_group'])) {
            $info['id_default_group'] = trim($info['id_default_group']);
            $my_group = Group::searchByName($info['id_default_group']);
            if (isset($my_group['id_group']) && $my_group['id_group']) {
                $customer->id_default_group = $info['id_default_group'] = (int) $my_group['id_group'];
            }
        }
        $my_group = new Group($customer->id_default_group);
        if (!Validate::isLoadedObject($my_group)) {
            $customer->id_default_group = (int) Configuration::get('PS_CUSTOMER_GROUP');
        }
        $customer_groups[] = (int) $customer->id_default_group;
        $customer_groups = array_flip(array_flip($customer_groups));

        // Bug when updating existing user that were csv-imported before...
        if (!isset($customer->date_upd) || $customer->date_upd == '0000-00-00 00:00:00') {
            $customer->date_upd = date('Y-m-d H:i:s');
        }

        $res = false;
        if (($field_error = $customer->validateFields(false, true)) === true &&
                ($lang_field_error = $customer->validateFieldsLang(false, true)) === true) {
            $res = true;
            foreach ($customers_shop as $id_shop => $id_group) {
                $customer->force_id = (bool) $force_id;
                if ($id_shop == 'shared') {
                    foreach ($id_group as $key => $id) {
                        $customer->id_shop = (int) $key;
                        $customer->id_shop_group = (int) $id;
                        if ($customer_exist && ((int) $current_id_shop_group == (int) $id || in_array($current_id_shop, ShopGroup::getShopsFromGroup($id)))) {
                            $customer->id = (int) $current_id_customer;
                            $res &= ($validateOnly || $customer->update());
                        } else {
                            $res &= ($validateOnly || $customer->add($autodate));
                            if (isset($addresses)) {
                                foreach ($addresses as $address) {
                                    $address['id_customer'] = $customer->id;
                                    unset($address['country'], $address['state'], $address['state_iso'], $address['id_address']);
                                    Db::getInstance()->insert('address', $address, false, false);
                                }
                            }
                        }
                        if ($res && !$validateOnly && !empty($customer_groups)) {
                            $customer->updateGroup($customer_groups);
                        }
                    }
                } else {
                    $customer->id_shop = $id_shop;
                    $customer->id_shop_group = $id_group;
                    if ($customer_exist && (int) $id_shop == (int) $current_id_shop) {
                        $customer->id = (int) $current_id_customer;
                        $res &= ($validateOnly || $customer->update());
                    } else {
                        $res &= ($validateOnly || $customer->add($autodate));
                        if (!$validateOnly && isset($addresses)) {
                            foreach ($addresses as $address) {
                                $address['id_customer'] = $customer->id;
                                unset($address['country'], $address['state'], $address['state_iso'], $address['id_address']);
                                Db::getInstance()->insert('address', $address, false, false);
                            }
                        }
                    }
                    if ($res && !$validateOnly && !empty($customer_groups)) {
                        $customer->updateGroup($customer_groups);
                    }
                }
            }
        }


        if (!empty($customer_groups)) {
            unset($customer_groups);
        }
        if (isset($current_id_customer)) {
            unset($current_id_customer);
        }
        if (isset($current_id_shop)) {
            unset($current_id_shop);
        }
        if (isset($current_id_shop_group)) {
            unset($current_id_shop_group);
        }
        if (isset($addresses)) {
            unset($addresses);
        }

        if (!$res) {
            if ($validateOnly) {
                $this->errors[] = sprintf(
                        $this->module->l('Email address %1$s (ID: %2$s) cannot be validated.', 'EIImport'),
                        Tools::htmlentitiesUTF8($info['email']),
                        !empty($info['id']) ? Tools::htmlentitiesUTF8($info['id']) : 'null'
                );
            } else {
                $this->errors[] = sprintf(
                        $this->module->l('Email address %1$s (ID: %2$s) cannot be saved.', 'EIImport'),
                        Tools::htmlentitiesUTF8($info['email']),
                        !empty($info['id']) ? Tools::htmlentitiesUTF8($info['id']) : 'null'
                );
            }

            $this->errors[] = ($field_error !== true ? $field_error : '') . (isset($lang_field_error) && $lang_field_error !== true ? $lang_field_error : '')
                    . Db::getInstance()->getMsgError()
                    . '<br> ' . $this->module->l('Location', 'EIImport') . ': ' . __CLASS__ . ':' . __LINE__ . '.'
                    . '<br> ' . $this->module->l('Row number in file', 'EIImport') . ": {$this->row}. " . $this->module->l('Problematic row', 'EIImport') . ': <pre>' . print_r($info, true);
        }
    }

    private function getCustomerUpdate(&$info)
    {
        $id_customer = null;
        if ($this->updateBy == 'id' && !empty($info['id'])) {
            $id_customer = (int) Db::getInstance()->getValue('
                        SELECT id_customer
                        FROM `' . _DB_PREFIX_ . 'customer`
                        WHERE `id_customer` = ' . (int) $info['id'], false);
        } elseif ($this->updateBy == 'email' && !empty($info['email'])) {
            $id_customer = (int) Db::getInstance()->getValue('
                        SELECT id_customer
                        FROM `' . _DB_PREFIX_ . 'customer`
                        WHERE `email` = \'' . pSQL($info['email']) . '\'', false);
        }
        if ($id_customer && $this->targetAction != 'insert') {
            $info['id'] = $id_customer;
        }
        return $id_customer;
    }

    private function getCustomerInsertion(&$info, $force_id)
    {
        if ($force_id && !empty($info['id']) && ObjectModel::existsInDatabase((int) $info['id'], 'customer')) {
            return false;
        }
        if ($this->nameExists == 'ignore' && !empty($info['email']) &&
                Db::getInstance()->getRow('
                        SELECT *
                        FROM `' . _DB_PREFIX_ . 'customer`
                        WHERE `email` = \'' . pSQL($info['email']) . '\'', false)) {
            return false;
        }

        if (!$force_id) {
            unset($info['id']);
        }
        return true;
    }

    public function groupImport($offset = false, $limit = false, $validateOnly = false)
    {
        // Fill self::$column_mask array
        $this->receiveColumns();
        // Open the file
        $handle = $this->openCsvFile($offset);
        if (!$handle) {
            return false;
        }

        $id_lang = Tools::getValue('language');

        self::setLocale();

        $shop_is_feature_active = Shop::isFeatureActive();
        $force_id = Tools::getValue('keep_id');

        $line_count = 0;
        for ($current_line = 0; ($line = fgetcsv($handle, MAX_LINE_SIZE, $this->separator, $this->enclosure)) && (!$limit || $current_line < $limit); $current_line++) {
            $line_count++;
            $this->row = $offset + $line_count + 1;
            if ($this->convert) {
                $line = $this->utf8EncodeArray($line);
            }

            $isEmpty = true;
            foreach ($line as $ln) {
                if ($ln) {
                    $isEmpty = false;
                    break;
                }
            }

            if ($isEmpty) {
                $this->warnings[] = $this->module->l('There is an empty row in the file that won\'t be imported.', 'EIImport');
                continue;
            }

            $info = self::getMaskedRow($line);

            $this->groupImportOne(
                    $info,
                    $id_lang,
                    $shop_is_feature_active,
                    $force_id,
                    $validateOnly
            );
        }
        $this->closeCsvFile($handle);

        return $line_count;
    }

    protected function groupImportOne($info, $id_lang, $shop_is_feature_active, $force_id, $validateOnly = false)
    {
        if ($this->targetAction == 'insert') {
            if (!$this->getGroupInsertion($info, $id_lang, $force_id)) {
                return false;
            }
        } elseif ($this->targetAction == 'update') {
            if (!$this->getGroupUpdate($info, $id_lang)) {
                return false;
            }
        } else {
            if (!$this->getGroupUpdate($info, $id_lang) && !$this->getGroupInsertion($info, $id_lang, $force_id)) {
                return false;
            }
        }


        if (isset($info['id']) && (int) $info['id'] && ($force_id || ObjectModel::existsInDatabase((int) $info['id'], 'group'))) {
            $group = new Group((int) $info['id']);
        } else {
            $group = new Group();
            self::setDefaultValues($info);
        }

        // Correct some formats
        if (!empty($info['date_add'])) {
            $info['date_add'] = date("Y-m-d H:i:s", strtotime($info['date_add']));
            if (Tools::substr($info['date_add'], 0, 10) === '1969-12-31' || Tools::substr($info['date_add'], 0, 1) === '-') {
                $info['date_add'] = date("Y-m-d H:i:s");
            }
        }
        if (!empty($info['reduction'])) {
            $info['reduction'] = str_replace(',', '.', $info['reduction']);
        }

        self::arrayWalk($info, [self::class, 'fillInfo'], $group);

        $res = false;
        if (($field_error = $group->validateFields(false, true)) === true &&
                ($lang_field_error = $group->validateFieldsLang(false, true)) === true) {
            if ($group->id && ObjectModel::existsInDatabase((int) $info['id'], 'group')) {
                $res = ($validateOnly || $group->update());
            }
            $group->force_id = (bool) $force_id;
            if (!$res) {
                $res = ($validateOnly || $group->add());
            }

            if (!$validateOnly && $res) {
                // Associate group to group shop
                if ($shop_is_feature_active) {
                    $data = array();
                    foreach ($this->shops as $id_shop) {
                        $data[] = array(
                            Group::$definition['primary'] => (int) $group->id,
                            'id_shop' => (int) $id_shop,
                        );
                    }
                    if ($data) {
                        Db::getInstance()->insert(Group::$definition['table'] . '_shop', $data, false, true, Db::INSERT_IGNORE);
                    }
                }
            }
        }

        if (!$res) {
            if (!$validateOnly) {
                $this->errors[] = Db::getInstance()->getMsgError() . ' ' . sprintf(
                                $this->module->l('%1$s (ID: %2$s) cannot be saved.', 'EIImport'),
                                !empty($info['name']) ? Tools::safeOutput($info['name']) : 'No Name',
                                !empty($info['id']) ? Tools::safeOutput($info['id']) : 'No ID'
                        )
                        . '<br> ' . $this->module->l('Location', 'EIImport') . ': ' . __CLASS__ . ':' . __LINE__ . '.'
                        . '<br> ' . $this->module->l('Row number in file', 'EIImport') . ": {$this->row}. " . $this->module->l('Problematic row', 'EIImport') . ': <pre>' . print_r($info, true);
            }
            if ($field_error !== true || isset($lang_field_error) && $lang_field_error !== true) {
                $this->errors[] = ($field_error !== true ? $field_error : '') . (isset($lang_field_error) && $lang_field_error !== true ? $lang_field_error : '') .
                        Db::getInstance()->getMsgError()
                        . '<br> ' . $this->module->l('Location', 'EIImport') . ': ' . __CLASS__ . ':' . __LINE__ . '.'
                        . '<br> ' . $this->module->l('Row number in file', 'EIImport') . ": {$this->row}. " . $this->module->l('Problematic row', 'EIImport') . ': <pre>' . print_r($info, true);
            }
        }
    }

    private function getGroupUpdate(&$info, $id_lang)
    {
        $id_group = null;
        if ($this->updateBy == 'id' && !empty($info['id'])) {
            $id_group = (int) Db::getInstance()->getValue('
                        SELECT id_group
                        FROM `' . _DB_PREFIX_ . 'group`
                        WHERE `id_group` = ' . (int) $info['id'], false);
        } elseif ($this->updateBy == 'name' && !empty($info['name'])) {
            $id_group = (int) Db::getInstance()->getValue('
                        SELECT g.id_group
                        FROM `' . _DB_PREFIX_ . 'group` g
                        LEFT JOIN `' . _DB_PREFIX_ . 'group_lang` gl ON g.`id_group` = gl.`id_group` AND `id_lang` = ' . (int) $id_lang . '
                        WHERE `name` = \'' . pSQL($info['name']) . '\'', false);
        }
        if ($id_group && $this->targetAction != 'insert') {
            $info['id'] = $id_group;
        }
        return $id_group;
    }

    private function getGroupInsertion(&$info, $id_lang, $force_id)
    {
        if ($force_id && !empty($info['id']) && ObjectModel::existsInDatabase((int) $info['id'], 'group')) {
            return false;
        }
        if ($this->nameExists == 'ignore' && !empty($info['name']) &&
                Db::getInstance()->getRow('
                        SELECT g.*
                        FROM `' . _DB_PREFIX_ . 'group` g
                        LEFT JOIN `' . _DB_PREFIX_ . 'group_lang` gl ON g.`id_group` = gl.`id_group` AND `id_lang` = ' . (int) $id_lang . '
                        WHERE `name` = \'' . pSQL($info['name']) . '\'', false)) {
            return false;
        }

        if (!$force_id) {
            unset($info['id']);
        }
        return true;
    }

    public function warehouseImport($offset = false, $limit = false, $validateOnly = false)
    {
        // Fill self::$column_mask array
        $this->receiveColumns();
        // Open the file
        $handle = $this->openCsvFile($offset);
        if (!$handle) {
            return false;
        }

        self::setLocale();

        $shop_is_feature_active = Shop::isFeatureActive();
        $force_id = Tools::getValue('keep_id');

        $line_count = 0;
        for ($current_line = 0; ($line = fgetcsv($handle, MAX_LINE_SIZE, $this->separator, $this->enclosure)) && (!$limit || $current_line < $limit); $current_line++) {
            $line_count++;
            $this->row = $offset + $line_count + 1;
            if ($this->convert) {
                $line = $this->utf8EncodeArray($line);
            }

            $isEmpty = true;
            foreach ($line as $ln) {
                if ($ln) {
                    $isEmpty = false;
                    break;
                }
            }

            if ($isEmpty) {
                $this->warnings[] = $this->module->l('There is an empty row in the file that won\'t be imported.', 'EIImport');
                continue;
            }

            $info = self::getMaskedRow($line);

            $this->warehouseImportOne(
                    $info,
                    $shop_is_feature_active,
                    $force_id,
                    $validateOnly
            );
        }
        $this->closeCsvFile($handle);

        return $line_count;
    }

    private function getWarehouseUpdate(&$info)
    {
        $id_warehouse = null;
        if ($this->updateBy == 'id' && !empty($info['id'])) {
            $id_warehouse = (int) Db::getInstance()->getValue('
                        SELECT id_warehouse
                        FROM `' . _DB_PREFIX_ . 'warehouse`
                        WHERE `id_warehouse` = ' . (int) $info['id'], false);
        } elseif ($this->updateBy == 'name' && !empty($info['name'])) {
            $id_warehouse = (int) Db::getInstance()->getValue('
                        SELECT id_warehouse
                        FROM `' . _DB_PREFIX_ . 'warehouse`
                        WHERE `name` = \'' . pSQL($info['name']) . '\'', false);
        }
        if ($id_warehouse && $this->targetAction != 'insert') {
            $info['id'] = $id_warehouse;
        }
        return $id_warehouse;
    }

    private function getWarehouseInsertion(&$info, $force_id)
    {
        if ($force_id && !empty($info['id']) && ObjectModel::existsInDatabase((int) $info['id'], 'warehouse')) {
            return false;
        }
        if ($this->nameExists == 'ignore' && !empty($info['name']) &&
                Db::getInstance()->getRow('
                        SELECT *
                        FROM `' . _DB_PREFIX_ . 'warehouse`
                        WHERE `name` = \'' . pSQL($info['name']) . '\'', false)) {
            return false;
        }

        if (!$force_id) {
            unset($info['id']);
        }
        return true;
    }

    protected function warehouseImportOne($info, $shop_is_feature_active, $force_id, $validateOnly = false)
    {
        if ($this->targetAction == 'insert') {
            if (!$this->getWarehouseInsertion($info, $force_id)) {
                return false;
            }
        } elseif ($this->targetAction == 'update') {
            if (!$this->getWarehouseUpdate($info)) {
                return false;
            }
        } else {
            if (!$this->getWarehouseUpdate($info) && !$this->getWarehouseInsertion($info, $force_id)) {
                return false;
            }
        }

        $info['id_employee'] = (int) /* Employee::employeeExists($row['id_employee']) ?: */ $this->context->employee->id;
        $info['id_address'] = 0;
        $info['id_currency'] = (int) Currency::getIdByIsoCode($info['id_currency']);

        if (isset($info['id']) && (int) $info['id'] && ($force_id || Warehouse::exists((int) $info['id']))) {
            $warehouse = new Warehouse((int) $info['id']);
        } else {
            $warehouse = new Warehouse();
            self::setDefaultValues($info);
        }

        self::arrayWalk($info, [self::class, 'fillInfo'], $warehouse);

        if (isset($warehouse->country) && is_numeric($warehouse->country)) {
            if (Country::getNameById(Configuration::get('PS_LANG_DEFAULT'), (int) $warehouse->country)) {
                $warehouse->id_country = (int) $warehouse->country;
            }
        } elseif (isset($warehouse->country) && is_string($warehouse->country) && !empty($warehouse->country)) {
            if ($id_country = Country::getIdByName(null, $warehouse->country)) {
                $warehouse->id_country = (int) $id_country;
            } else {
                $country = new Country();
                $country->active = 1;
                $country->name = self::createMultiLangField($warehouse->country);
                $country->id_zone = 0; // Default zone for country to create
                $country->iso_code = Tools::strtoupper(Tools::substr($warehouse->country, 0, 2)); // Default iso for country to create
                $country->contains_states = 0; // Default value for country to create
                $lang_field_error = $country->validateFieldsLang(false, true);
                if (($field_error = $country->validateFields(false, true)) === true &&
                        ($lang_field_error = $country->validateFieldsLang(false, true)) === true &&
                        !$validateOnly && // Do not move this condition: previous tests should be played always, but next ->add() test should not be played in validateOnly mode
                        $country->add()
                ) {
                    $warehouse->id_country = (int) $country->id;
                } else {
                    $default_language_id = (int) Configuration::get('PS_LANG_DEFAULT');
                    $this->errors[] = sprintf(
                                    $this->module->l('%s cannot be saved.', 'EIImport'),
                                    Tools::htmlentitiesUTF8($country->name[$default_language_id])
                            )
                            . '<br> ' . $this->module->l('Location', 'EIImport') . ': ' . __CLASS__ . ':' . __LINE__ . '.'
                            . '<br> ' . $this->module->l('Row number in file', 'EIImport') . ": {$this->row}. " . $this->module->l('Problematic row', 'EIImport') . ': <pre>' . print_r($info, true);
                    if ($field_error !== true || isset($lang_field_error) && $lang_field_error !== true) {
                        $this->errors[] = ($field_error !== true ? $field_error : '') . (isset($lang_field_error) && $lang_field_error !== true ? $lang_field_error : '') .
                                Db::getInstance()->getMsgError()
                                . '<br> ' . $this->module->l('Location', 'EIImport') . ': ' . __CLASS__ . ':' . __LINE__ . '.'
                                . '<br> ' . $this->module->l('Row number in file', 'EIImport') . ": {$this->row}. " . $this->module->l('Problematic row', 'EIImport') . ': <pre>' . print_r($info, true);
                    }
                }
            }
        }

        if (isset($warehouse->state) && is_numeric($warehouse->state)) {
            if (State::getNameById((int) $warehouse->state)) {
                $warehouse->id_state = (int) $warehouse->state;
            }
        } elseif (isset($warehouse->state) && is_string($warehouse->state) && !empty($warehouse->state)) {
            if ($id_state = State::getIdByName($warehouse->state)) {
                $warehouse->id_state = (int) $id_state;
            } else {
                $state = new State();
                $state->active = 1;
                $state->name = $warehouse->state;
                $state->id_country = isset($country->id) ? (int) $country->id : 0;
                $state->id_zone = 0; // Default zone for state to create
                $state->iso_code = Tools::strtoupper(Tools::substr($warehouse->state, 0, 2)); // Default iso for state to create
                $state->tax_behavior = 0;
                if (($field_error = $state->validateFields(false, true)) === true &&
                        ($lang_field_error = $state->validateFieldsLang(false, true)) === true &&
                        !$validateOnly && // Do not move this condition: previous tests should be played always, but next ->add() test should not be played in validateOnly mode
                        $state->add()
                ) {
                    $warehouse->id_state = (int) $state->id;
                } else {
                    $this->errors[] = sprintf(
                                    $this->module->l('%s cannot be saved.', 'EIImport'),
                                    Tools::htmlentitiesUTF8($state->name)
                            )
                            . '<br> ' . $this->module->l('Location', 'EIImport') . ': ' . __CLASS__ . ':' . __LINE__ . '.'
                            . '<br> ' . $this->module->l('Row number in file', 'EIImport') . ": {$this->row}. " . $this->module->l('Problematic row', 'EIImport') . ': <pre>' . print_r($info, true);
                    if ($field_error !== true || isset($lang_field_error) && $lang_field_error !== true) {
                        $this->errors[] = ($field_error !== true ? $field_error : '') . (isset($lang_field_error) && $lang_field_error !== true ? $lang_field_error : '') .
                                Db::getInstance()->getMsgError()
                                . '<br> ' . $this->module->l('Location', 'EIImport') . ': ' . __CLASS__ . ':' . __LINE__ . '.'
                                . '<br> ' . $this->module->l('Row number in file', 'EIImport') . ": {$this->row}. " . $this->module->l('Problematic row', 'EIImport') . ': <pre>' . print_r($info, true);
                    }
                }
            }
        }

        $warehouse->deleted = 0;

        $id_address = Db::getInstance()->getValue('
					SELECT id_address
					FROM `' . _DB_PREFIX_ . 'address`
					WHERE `address1` = "' . pSQL($info['address1']) . '"
                        AND `address2` = "' . pSQL($info['address2']) . '"
                        AND `postcode` = "' . pSQL($info['postcode']) . '"
                        AND `city` = "' . pSQL($info['city']) . '"
                        AND `id_country` = "' . (int) $warehouse->id_country . '"
                        AND `id_state` = "' . (int) $warehouse->id_state . '"
                        AND `phone` = "' . pSQL($info['phone']) . '"
                        AND `phone_mobile` = "' . pSQL($info['phone_mobile']) . '"', false);

        if ($id_address) {
            $warehouse->id_address = (int) $id_address;
        } else {
            $address = new Address();
            self::arrayWalk($info, [self::class, 'fillInfo'], $address);
            $address->alias = $info['reference'];
            $address->lastname = 'warehouse';
            $address->firstname = 'warehouse';
            $address->id_country = $warehouse->id_country;
            $address->id_state = $warehouse->id_state;
            if (!$validateOnly) {
                if ($address->add()) {
                    $warehouse->id_address = (int) $address->id;
                } else {
                    $warehouse->id_address = 0;
                    $this->warnings[] = sprintf(
                            $this->module->l('Could not create address "%s".', 'EIImport'),
                            Tools::safeOutput($info['address1'])
                    );
                }
            }
        }

        if (($field_error = $warehouse->validateFields(false, true)) !== true) {
            $this->errors[] = $field_error . Db::getInstance()->getMsgError()
                    . '<br> ' . $this->module->l('Location', 'EIImport') . ': ' . __CLASS__ . ':' . __LINE__ . '.'
                    . '<br> ' . $this->module->l('Row number in file', 'EIImport') . ": {$this->row}. " . $this->module->l('Problematic row', 'EIImport') . ': <pre>' . print_r($info, true);
            return false;
        }

        $res = false;
        if ($warehouse->id && Warehouse::exists((int) $info['id'])) {
            $res = $validateOnly || $warehouse->update();
            if (isset($address)) {
                $address->id_warehouse = (int) $warehouse->id;
                if (!$validateOnly && $res) {
                    $address->update();
                }
            }
        }
        $warehouse->force_id = (bool) $force_id;
        if (!$res) {
            $res = $validateOnly || $warehouse->add();
            if (isset($address)) {
                $address->id_warehouse = (int) $warehouse->id;
                if (!$validateOnly && $res) {
                    $address->update();
                }
            }
        }

        // Carriers
        if (!$validateOnly && $res) {
            if (!empty($warehouse->carriers)) {
                $carriers = explode($this->multivalueSeparator, $warehouse->carriers);
                $ids = [];
                foreach ($carriers as $carrier) {
                    $id_carr = Db::getInstance()->getValue('
						SELECT id_carrier FROM ' . _DB_PREFIX_ . 'carrier
						WHERE `name` = "' . pSQL($carrier) . '" AND `deleted` = 0');
                    if (!empty($id_carr)) {
                        $ids[] = $id_carr;
                    } else {
                        $this->warnings[] = sprintf(
                                $this->module->l('There is no carrier with name "%s"', 'EIImport'),
                                Tools::htmlentitiesUTF8($carrier)
                        );
                    }
                }
                if (!empty($ids)) {
                    Db::getInstance()->execute('DELETE FROM ' . _DB_PREFIX_ . 'warehouse_carrier
                                            WHERE id_warehouse = ' . (int) $warehouse->id);

                    $data = [];
                    foreach ($ids as $id) {
                        $data[] = array(
                            'id_warehouse' => (int) $warehouse->id,
                            'id_carrier' => (int) $id,
                        );
                    }
                    if ($data) {
                        Db::getInstance()->insert('warehouse_carrier', $data);
                    }
                }
                // If we want to empty the warehouse carriers. e.g. The carriers cell is empty and the "keep_old_value" input is disabled
            } elseif (isset($warehouse->carriers)) {
                Db::getInstance()->execute('DELETE FROM ' . _DB_PREFIX_ . 'warehouse_carrier
                                            WHERE id_warehouse = ' . (int) $warehouse->id);
            }
        }

        if ($res) {
            if (!$validateOnly) {
                // Associate group to group shop
                if ($shop_is_feature_active) {
                    $data = array();
                    foreach ($this->shops as $id_shop) {
                        $data[] = array(
                            Warehouse::$definition['primary'] => (int) $warehouse->id,
                            'id_shop' => (int) $id_shop,
                        );
                    }
                    if ($data) {
                        return Db::getInstance()->insert(Warehouse::$definition['table'] . '_shop', $data, false, true, Db::INSERT_IGNORE);
                    }
                }
            }
        } else {
            $this->errors[] = Db::getInstance()->getMsgError() . ' ' . sprintf(
                            $this->module->l('%1$s (ID: %2$s) cannot be saved.', 'EIImport'),
                            !empty($info['name']) ? Tools::safeOutput($info['name']) : 'No Name',
                            !empty($info['id']) ? Tools::safeOutput($info['id']) : 'No ID'
                    )
                    . '<br> ' . $this->module->l('Location', 'EIImport') . ': ' . __CLASS__ . ':' . __LINE__ . '.'
                    . '<br> ' . $this->module->l('Row number in file', 'EIImport') . ": {$this->row}. " . $this->module->l('Problematic row', 'EIImport') . ': <pre>' . print_r($info, true);
        }
    }

    public function storeContactImport($offset = false, $limit = false, $validateOnly = false)
    {
        $this->receiveColumns();
        $handle = $this->openCsvFile($offset);
        if (!$handle) {
            return false;
        }

        $force_id = Tools::getValue('keep_id');
        $regenerate = Tools::getValue('regenerate');

        $line_count = 0;
        for ($current_line = 0; ($line = fgetcsv($handle, MAX_LINE_SIZE, $this->separator, $this->enclosure)) && (!$limit || $current_line < $limit); $current_line++) {
            $line_count++;
            $this->row = $offset + $line_count + 1;
            if ($this->convert) {
                $line = $this->utf8EncodeArray($line);
            }

            $isEmpty = true;
            foreach ($line as $ln) {
                if ($ln) {
                    $isEmpty = false;
                    break;
                }
            }

            if ($isEmpty) {
                $this->warnings[] = $this->module->l('There is an empty row in the file that won\'t be imported.', 'EIImport');

                continue;
            }

            $info = self::getMaskedRow($line);

            $this->storeContactImportOne(
                    $info,
                    Shop::isFeatureActive(),
                    $regenerate,
                    $force_id,
                    $validateOnly
            );
        }
        $this->closeCsvFile($handle);

        return $line_count;
    }

    private function getStoreUpdate(&$info)
    {
        $id_store = null;
        if ($this->updateBy == 'id' && !empty($info['id'])) {
            $id_store = (int) Db::getInstance()->getValue('
                        SELECT id_store
                        FROM `' . _DB_PREFIX_ . 'store`
                        WHERE `id_store` = ' . (int) $info['id'], false);
        } elseif ($this->updateBy == 'name' && !empty($info['name'])) {
            $id_store = (int) Db::getInstance()->getValue('
                        SELECT id_store
                        FROM `' . _DB_PREFIX_ . 'store`
                        WHERE `name` = \'' . pSQL($info['name']) . '\'', false);
        }
        if ($id_store && $this->targetAction != 'insert') {
            $info['id'] = $id_store;
        }
        return $id_store;
    }

    private function getStoreInsertion(&$info, $force_id)
    {
        if ($force_id && !empty($info['id']) && ObjectModel::existsInDatabase((int) $info['id'], 'store')) {
            return false;
        }
        if ($this->nameExists == 'ignore' && !empty($info['name'])) {
            if (Db::getInstance()->getRow('
                        SELECT *
                        FROM `' . _DB_PREFIX_ . 'store' . ($this->storeLangTableExists ? '_lang' : '') . '`
                        WHERE `name` = \'' . pSQL($info['name']) . '\'', false)) {
                return false;
            }
        }

        if (!$force_id) {
            unset($info['id']);
        }
        return true;
    }

    public function storeContactImportOne($info, $shop_is_feature_active, $regenerate, $force_id, $validateOnly = false)
    {
        if ($this->targetAction == 'insert') {
            if (!$this->getStoreInsertion($info, $force_id)) {
                return false;
            }
        } elseif ($this->targetAction == 'update') {
            if (!$this->getStoreUpdate($info)) {
                return false;
            }
        } else {
            if (!$this->getStoreUpdate($info) && !$this->getStoreInsertion($info, $force_id)) {
                return false;
            }
        }


        if ($force_id && isset($info['id']) && (int) $info['id']) {
            $store = new Store((int) $info['id']);
        } else {
            if (array_key_exists('id', $info) && (int) $info['id'] && ObjectModel::existsInDatabase((int) $info['id'], 'store')) {
                $store = new Store((int) $info['id']);
            } else {
                $store = new Store();
                self::setDefaultValues($info);
            }
        }

        // Correct some formats
        if (isset($info['latitude'])) {
            $info['latitude'] = str_replace(',', '.', $info['latitude']);
        }
        if (isset($info['longitude'])) {
            $info['longitude'] = str_replace(',', '.', $info['longitude']);
        }
        if (!empty($info['date_add'])) {
            $info['date_add'] = date("Y-m-d H:i:s", strtotime($info['date_add']));
            if (Tools::substr($info['date_add'], 0, 10) === '1969-12-31' || Tools::substr($info['date_add'], 0, 1) === '-') {
                $info['date_add'] = date("Y-m-d H:i:s");
            }
        }

        self::arrayWalk($info, [self::class, 'fillInfo'], $store);

        if (!$validateOnly && !empty($store->image)) {
            if (!(self::copyImg($store->id, null, $store->image, 'stores', $regenerate))) {
                $this->warnings[] = $store->image . ' ' . $this->module->l('cannot be copied.', 'EIImport');
            }
        }

        if (isset($store->hours)) {
            if (substr($store->hours, 0, 2) === '[[' && !$this->storeLangTableExists) {
                $store->hours = json_decode($store->hours, true);
                $store->hours = serialize($store->hours);
            } elseif(substr($store->hours, 0, 2) !== '[[' && $this->storeLangTableExists) {
                $store->hours = unserialize($store->hours);
                $store->hours = json_encode($store->hours);
            }
        }

        if (isset($store->country) && is_numeric($store->country)) {
            if (Country::getNameById(Configuration::get('PS_LANG_DEFAULT'), (int) $store->country)) {
                $store->id_country = (int) $store->country;
            }
        } elseif (isset($store->country) && is_string($store->country) && !empty($store->country)) {
            if ($id_country = Country::getIdByName(null, $store->country)) {
                $store->id_country = (int) $id_country;
            } else {
                $country = new Country();
                $country->active = 1;
                $country->name = self::createMultiLangField($store->country);
                $country->id_zone = 0; // Default zone for country to create
                $country->iso_code = Tools::strtoupper(Tools::substr($store->country, 0, 2)); // Default iso for country to create
                $country->contains_states = 0; // Default value for country to create
                $lang_field_error = $country->validateFieldsLang(false, true);
                if (($field_error = $country->validateFields(false, true)) === true &&
                        ($lang_field_error = $country->validateFieldsLang(false, true)) === true &&
                        !$validateOnly && // Do not move this condition: previous tests should be played always, but next ->add() test should not be played in validateOnly mode
                        $country->add()
                ) {
                    $store->id_country = (int) $country->id;
                } else {
                    if (!$validateOnly) {
                        $default_language_id = (int) Configuration::get('PS_LANG_DEFAULT');
                        $this->errors[] = sprintf(
                                        $this->module->l('%s cannot be saved.', 'EIImport'),
                                        Tools::htmlentitiesUTF8($country->name[$default_language_id])
                                )
                                . '<br> ' . $this->module->l('Location', 'EIImport') . ': ' . __CLASS__ . ':' . __LINE__ . '.'
                                . '<br> ' . $this->module->l('Row number in file', 'EIImport') . ": {$this->row}. " . $this->module->l('Problematic row', 'EIImport') . ': <pre>' . print_r($info, true);
                    }
                    if ($field_error !== true || isset($lang_field_error) && $lang_field_error !== true) {
                        $this->errors[] = ($field_error !== true ? $field_error : '') . (isset($lang_field_error) && $lang_field_error !== true ? $lang_field_error : '') .
                                Db::getInstance()->getMsgError()
                                . '<br> ' . $this->module->l('Location', 'EIImport') . ': ' . __CLASS__ . ':' . __LINE__ . '.'
                                . '<br> ' . $this->module->l('Row number in file', 'EIImport') . ": {$this->row}. " . $this->module->l('Problematic row', 'EIImport') . ': <pre>' . print_r($info, true);
                    }
                }
            }
        }

        if (isset($store->state) && is_numeric($store->state)) {
            if (State::getNameById((int) $store->state)) {
                $store->id_state = (int) $store->state;
            }
        } elseif (isset($store->state) && is_string($store->state) && !empty($store->state)) {
            if ($id_state = State::getIdByName($store->state)) {
                $store->id_state = (int) $id_state;
            } else {
                $state = new State();
                $state->active = 1;
                $state->name = $store->state;
                $state->id_country = isset($country->id) ? (int) $country->id : 0;
                $state->id_zone = 0; // Default zone for state to create
                $state->iso_code = Tools::strtoupper(Tools::substr($store->state, 0, 2)); // Default iso for state to create
                $state->tax_behavior = 0;
                if (($field_error = $state->validateFields(false, true)) === true &&
                        ($lang_field_error = $state->validateFieldsLang(false, true)) === true &&
                        !$validateOnly && // Do not move this condition: previous tests should be played always, but next ->add() test should not be played in validateOnly mode
                        $state->add()
                ) {
                    $store->id_state = (int) $state->id;
                } else {
                    if (!$validateOnly) {
                        $this->errors[] = sprintf(
                                        $this->module->l('%s cannot be saved.', 'EIImport'),
                                        Tools::htmlentitiesUTF8($state->name)
                                )
                                . '<br> ' . $this->module->l('Location', 'EIImport') . ': ' . __CLASS__ . ':' . __LINE__ . '.'
                                . '<br> ' . $this->module->l('Row number in file', 'EIImport') . ": {$this->row}. " . $this->module->l('Problematic row', 'EIImport') . ': <pre>' . print_r($info, true);
                    }
                    if ($field_error !== true || isset($lang_field_error) && $lang_field_error !== true) {
                        $this->errors[] = ($field_error !== true ? $field_error : '') . (isset($lang_field_error) && $lang_field_error !== true ? $lang_field_error : '') .
                                Db::getInstance()->getMsgError()
                                . '<br> ' . $this->module->l('Location', 'EIImport') . ': ' . __CLASS__ . ':' . __LINE__ . '.'
                                . '<br> ' . $this->module->l('Row number in file', 'EIImport') . ": {$this->row}. " . $this->module->l('Problematic row', 'EIImport') . ': <pre>' . print_r($info, true);
                    }
                }
            }
        }

        $res = false;
        if (($field_error = $store->validateFields(false, true)) === true &&
                ($lang_field_error = $store->validateFieldsLang(false, true)) === true
        ) {
            if ($store->id && ObjectModel::existsInDatabase((int) $store->id, 'store')) {
                $res = $validateOnly ? $validateOnly : $store->update();
            }
            $store->force_id = (bool) $force_id;
            if (!$res) {
                $res = $validateOnly ? $validateOnly : $store->add();
            }

            // Insert shops
            if ($shop_is_feature_active) {
                $data = array();
                foreach ($this->shops as $id_shop) {
                    $data[] = array(
                        Store::$definition['primary'] => (int) $store->id,
                        'id_shop' => (int) $id_shop,
                    );
                }
                if ($data) {
                    Db::getInstance()->insert(Store::$definition['table'] . '_shop', $data, false, true, Db::INSERT_IGNORE);
                }
            }

            if (!$res) {
                $this->errors[] = Db::getInstance()->getMsgError() . ' ' . sprintf(
                                $this->module->l('%1$s (ID: %2$s) cannot be saved.', 'EIImport'),
                                Tools::htmlentitiesUTF8($info['name']),
                                (isset($info['id']) ? Tools::htmlentitiesUTF8($info['id']) : 'null')
                        )
                        . '<br> ' . $this->module->l('Location', 'EIImport') . ': ' . __CLASS__ . ':' . __LINE__ . '.'
                        . '<br> ' . $this->module->l('Row number in file', 'EIImport') . ": {$this->row}. " . $this->module->l('Problematic row', 'EIImport') . ': <pre>' . print_r($info, true);
            }
        } else {
            $id_lang = Tools::getValue('language');
            $this->errors[] = $this->module->l('Store is invalid', 'EIImport') . ' (' . Tools::htmlentitiesUTF8($store->name[$id_lang]) . ')';
            $this->errors[] = ($field_error !== true ? $field_error : '') . (isset($lang_field_error) && $lang_field_error !== true ? $lang_field_error : '')
                    . '<br> ' . $this->module->l('Location', 'EIImport') . ': ' . __CLASS__ . ':' . __LINE__ . '.'
                    . '<br> ' . $this->module->l('Row number in file', 'EIImport') . ": {$this->row}. " . $this->module->l('Problematic row', 'EIImport') . ': <pre>' . print_r($info, true);
        }
    }

    public function aliasImport($offset = false, $limit = false, $validateOnly = false)
    {
        $this->receiveColumns();
        $handle = $this->openCsvFile($offset);
        if (!$handle) {
            return false;
        }

        self::setLocale();

        $force_id = Tools::getValue('keep_id');

        $line_count = 0;
        for ($current_line = 0; ($line = fgetcsv($handle, MAX_LINE_SIZE, $this->separator, $this->enclosure)) && (!$limit || $current_line < $limit); $current_line++) {
            $line_count++;
            $this->row = $offset + $line_count + 1;
            if ($this->convert) {
                $line = $this->utf8EncodeArray($line);
            }

            $isEmpty = true;
            foreach ($line as $ln) {
                if ($ln) {
                    $isEmpty = false;
                    break;
                }
            }

            if ($isEmpty) {
                $this->warnings[] = $this->module->l('There is an empty row in the file that won\'t be imported.', 'EIImport');

                continue;
            }

            $info = self::getMaskedRow($line);

            $this->aliasImportOne(
                    $info,
                    $force_id,
                    $validateOnly
            );
        }
        $this->closeCsvFile($handle);

        return $line_count;
    }

    private function getAliasUpdate(&$info)
    {
        $id_alias = null;
        if ($this->updateBy == 'id' && !empty($info['id'])) {
            $id_alias = (int) Db::getInstance()->getValue('
                        SELECT id_alias
                        FROM `' . _DB_PREFIX_ . 'alias`
                        WHERE `id_alias` = ' . (int) $info['id'], false);
        } elseif ($this->updateBy == 'name' && !empty($info['alias'])) {
            $id_alias = (int) Db::getInstance()->getValue('
                        SELECT id_alias
                        FROM `' . _DB_PREFIX_ . 'alias`
                        WHERE `alias` = \'' . pSQL($info['alias']) . '\'', false);
        }
        if ($id_alias && $this->targetAction != 'insert') {
            $info['id'] = $id_alias;
        }
        return $id_alias;
    }

    private function getAliasInsertion(&$info, $force_id)
    {
        if ($force_id && !empty($info['id']) && ObjectModel::existsInDatabase((int) $info['id'], 'alias')) {
            return false;
        }
        if ($this->nameExists == 'ignore' && !empty($info['alias']) &&
                Db::getInstance()->getRow('
                        SELECT *
                        FROM `' . _DB_PREFIX_ . 'alias`
                        WHERE `alias` = \'' . pSQL($info['alias']) . '\'', false)) {
            return false;
        }

        if (!$force_id) {
            unset($info['id']);
        }
        return true;
    }

    protected function aliasImportOne($info, $force_id, $validateOnly = false)
    {
        if ($this->targetAction == 'insert') {
            if (!$this->getAliasInsertion($info, $force_id)) {
                return false;
            }
        } elseif ($this->targetAction == 'update') {
            if (!$this->getAliasUpdate($info)) {
                return false;
            }
        } else {
            if (!$this->getAliasUpdate($info) && !$this->getAliasInsertion($info, $force_id)) {
                return false;
            }
        }


        if (isset($info['id']) && (int) $info['id'] && ($force_id || ObjectModel::existsInDatabase((int) $info['id'], 'alias'))) {
            $alias = new Alias((int) $info['id']);
        } else {
            $alias = new Alias();
            self::setDefaultValues($info);
        }

        self::arrayWalk($info, [self::class, 'fillInfo'], $alias);

        $res = false;
        if (($field_error = $alias->validateFields(false, true)) === true &&
                ($lang_field_error = $alias->validateFieldsLang(false, true)) === true
        ) {
            if ($alias->id && $alias->aliasExists($alias->id)) {
                $res = ($validateOnly || $alias->update());
            }
            $alias->force_id = (bool) $force_id;
            if (!$res) {
                $res = ($validateOnly || Db::getInstance()->getValue('
                    SELECT `id_alias`
                    FROM ' . _DB_PREFIX_ . 'alias a
                    WHERE a.`alias` = "' . $alias->alias . '"') || $alias->add());
            }

            if (!$res) {
                $this->errors[] = Db::getInstance()->getMsgError() . ' ' . sprintf(
                                $this->module->l('%1$s (ID: %2$s) cannot be saved.', 'EIImport'),
                                Tools::htmlentitiesUTF8($info['name']),
                                (isset($info['id']) ? Tools::htmlentitiesUTF8($info['id']) : 'null')
                        )
                        . '<br> ' . $this->module->l('Location', 'EIImport') . ': ' . __CLASS__ . ':' . __LINE__ . '.'
                        . '<br> ' . $this->module->l('Row number in file', 'EIImport') . ": {$this->row}. " . $this->module->l('Problematic row', 'EIImport') . ': <pre>' . print_r($info, true);
            }
        } else {
            $this->errors[] = $this->module->l('Alias is invalid', 'EIImport') . ' (' . Tools::htmlentitiesUTF8($alias->name) . ')';
            $this->errors[] = ($field_error !== true ? $field_error : '') . (isset($lang_field_error) && $lang_field_error !== true ? $lang_field_error : '')
                    . '<br> ' . $this->module->l('Location', 'EIImport') . ': ' . __CLASS__ . ':' . __LINE__ . '.'
                    . '<br> ' . $this->module->l('Row number in file', 'EIImport') . ": {$this->row}. " . $this->module->l('Problematic row', 'EIImport') . ': <pre>' . print_r($info, true);
        }
    }

    public function addressImport($offset = false, $limit = false, $validateOnly = false)
    {
        $this->receiveColumns();
        $handle = $this->openCsvFile($offset);
        if (!$handle) {
            return false;
        }

        self::setLocale();

        $force_id = Tools::getValue('keep_id');

        $line_count = 0;
        for ($current_line = 0; ($line = fgetcsv($handle, MAX_LINE_SIZE, $this->separator, $this->enclosure)) && (!$limit || $current_line < $limit); $current_line++) {
            $line_count++;
            $this->row = $offset + $line_count + 1;
            if ($this->convert) {
                $line = $this->utf8EncodeArray($line);
            }

            $isEmpty = true;
            foreach ($line as $ln) {
                if ($ln) {
                    $isEmpty = false;
                    break;
                }
            }

            if ($isEmpty) {
                $this->warnings[] = $this->module->l('There is an empty row in the file that won\'t be imported.', 'EIImport');

                continue;
            }

            $info = self::getMaskedRow($line);

            $this->addressImportOne(
                    $info,
                    $force_id,
                    $validateOnly
            );
        }
        $this->closeCsvFile($handle);

        return $line_count;
    }

    private function getAddressUpdate(&$info)
    {
        $id_address = null;
        if ($this->updateBy == 'id' && !empty($info['id'])) {
            $id_address = (int) Db::getInstance()->getValue('
                        SELECT id_address
                        FROM `' . _DB_PREFIX_ . 'address`
                        WHERE `id_address` = ' . (int) $info['id'], false);
        }
        if ($id_address && $this->targetAction != 'insert') {
            $info['id'] = $id_address;
        }
        return $id_address;
    }

    private function getAddressInsertion(&$info, $force_id)
    {
        if ($force_id && !empty($info['id']) && ObjectModel::existsInDatabase((int) $info['id'], 'address')) {
            return false;
        }

        if (!$force_id) {
            unset($info['id']);
        }
        return true;
    }

    protected function addressImportOne($info, $force_id, $validateOnly = false)
    {
        if ($this->targetAction == 'insert') {
            if (!$this->getAddressInsertion($info, $force_id)) {
                return false;
            }
        } elseif ($this->targetAction == 'update') {
            if (!$this->getAddressUpdate($info)) {
                return false;
            }
        } else {
            if (!$this->getAddressUpdate($info) && !$this->getAddressInsertion($info, $force_id)) {
                return false;
            }
        }


        if (isset($info['id']) && (int) $info['id'] && ($force_id || Address::addressExists((int) $info['id']))) {
            $address = new Address((int) $info['id']);
        } else {
            $address = new Address();
            self::setDefaultValues($info);
        }

        // Correct some formats
        if (!empty($info['date_add'])) {
            $info['date_add'] = date("Y-m-d H:i:s", strtotime($info['date_add']));
            if (Tools::substr($info['date_add'], 0, 10) === '1969-12-31' || Tools::substr($info['date_add'], 0, 1) === '-') {
                $info['date_add'] = date("Y-m-d H:i:s");
            }
        }

        self::arrayWalk($info, [self::class, 'fillInfo'], $address);

        $default_language_id = (int) Configuration::get('PS_LANG_DEFAULT');
        if (isset($address->country) && is_numeric($address->country)) {
            if (Country::getNameById($default_language_id, (int) $address->country)) {
                $address->id_country = (int) $address->country;
            }
        } elseif (isset($address->country) && is_string($address->country) && !empty($address->country)) {
            if (($id_country = Country::getIdByName(null, $address->country))) {
                $address->id_country = (int) $id_country;
            } else {
                $country = new Country();
                $country->active = 1;
                $country->name = self::createMultiLangField($address->country);
                $country->id_zone = 0; // Default zone for country to create
                $country->iso_code = Tools::strtoupper(Tools::substr($address->country, 0, 2)); // Default iso for country to create
                $country->contains_states = 0; // Default value for country to create
                $lang_field_error = $country->validateFieldsLang(false, true);
                if (($field_error = $country->validateFields(false, true)) === true &&
                        ($lang_field_error = $country->validateFieldsLang(false, true)) === true &&
                        !$validateOnly && // Do not move this condition: previous tests should be played always, but next ->add() test should not be played in validateOnly mode
                        $country->add()
                ) {
                    $address->id_country = (int) $country->id;
                } else {
                    if (!$validateOnly) {
                        $this->errors[] = sprintf(
                                        $this->module->l('%1$s cannot be saved.', 'EIImport'),
                                        Tools::htmlentitiesUTF8($country->name[$default_language_id])
                                )
                                . '<br> ' . $this->module->l('Location', 'EIImport') . ': ' . __CLASS__ . ':' . __LINE__ . '.'
                                . '<br> ' . $this->module->l('Row number in file', 'EIImport') . ": {$this->row}. " . $this->module->l('Problematic row', 'EIImport') . ': <pre>' . print_r($info, true);
                    }
                    if ($field_error !== true || isset($lang_field_error) && $lang_field_error !== true) {
                        $this->errors[] = ($field_error !== true ? $field_error : '') . (isset($lang_field_error) && $lang_field_error !== true ? $lang_field_error : '') .
                                Db::getInstance()->getMsgError()
                                . '<br> ' . $this->module->l('Location', 'EIImport') . ': ' . __CLASS__ . ':' . __LINE__ . '.'
                                . '<br> ' . $this->module->l('Row number in file', 'EIImport') . ": {$this->row}. " . $this->module->l('Problematic row', 'EIImport') . ': <pre>' . print_r($info, true);
                    }
                }
            }
        }

        if (isset($address->state) && is_numeric($address->state)) {
            if (State::getNameById((int) $address->state)) {
                $address->id_state = (int) $address->state;
            }
        } elseif (isset($address->state) && is_string($address->state) && !empty($address->state)) {
            if (($id_state = State::getIdByName($address->state))) {
                $address->id_state = (int) $id_state;
            } else {
                $state = new State();
                $state->active = 1;
                $state->name = $address->state;
                $state->id_country = isset($country->id) ? (int) $country->id : 0;
                $state->id_zone = 0; // Default zone for state to create
                $state->iso_code = Tools::strtoupper(Tools::substr($address->state, 0, 2)); // Default iso for state to create
                $state->tax_behavior = 0;
                if (($field_error = $state->validateFields(false, true)) === true &&
                        ($lang_field_error = $state->validateFieldsLang(false, true)) === true &&
                        !$validateOnly && // Do not move this condition: previous tests should be played always, but next ->add() test should not be played in validateOnly mode
                        $state->add()
                ) {
                    $address->id_state = (int) $state->id;
                } else {
                    if (!$validateOnly) {
                        $this->errors[] = sprintf(
                                        $this->module->l('%1$s cannot be saved.', 'EIImport'),
                                        Tools::htmlentitiesUTF8($state->name)
                                )
                                . '<br> ' . $this->module->l('Location', 'EIImport') . ': ' . __CLASS__ . ':' . __LINE__ . '.'
                                . '<br> ' . $this->module->l('Row number in file', 'EIImport') . ": {$this->row}. " . $this->module->l('Problematic row', 'EIImport') . ': <pre>' . print_r($info, true);
                    }
                    if ($field_error !== true || isset($lang_field_error) && $lang_field_error !== true) {
                        $this->errors[] = ($field_error !== true ? $field_error : '') . (isset($lang_field_error) && $lang_field_error !== true ? $lang_field_error : '') .
                                Db::getInstance()->getMsgError()
                                . '<br> ' . $this->module->l('Location', 'EIImport') . ': ' . __CLASS__ . ':' . __LINE__ . '.'
                                . '<br> ' . $this->module->l('Row number in file', 'EIImport') . ": {$this->row}. " . $this->module->l('Problematic row', 'EIImport') . ': <pre>' . print_r($info, true);
                    }
                }
            }
        } else {
            $address->id_state = 0;
        }

        if (isset($address->id_customer) && !empty($address->id_customer)) {
            if (Customer::customerIdExistsStatic((int) $address->id_customer)) {
                $customer = new Customer((int) $address->id_customer);

                // a customer could exists in different shop
                $customer_list = Customer::getCustomersByEmail($customer->email);

                if (count($customer_list) == 0) {
                    if ($validateOnly) {
                        $this->errors[] = sprintf(
                                        $this->module->l('%1$s does not exist in database %2$s (ID: %3$s), and therefore cannot be validated', 'EIImport'),
                                        Tools::htmlentitiesUTF8($customer->email),
                                        Tools::htmlentitiesUTF8(Db::getInstance()->getMsgError()),
                                        (int) $address->id_customer
                                )
                                . '<br> ' . $this->module->l('Location', 'EIImport') . ': ' . __CLASS__ . ':' . __LINE__ . '.'
                                . '<br> ' . $this->module->l('Row number in file', 'EIImport') . ": {$this->row}. " . $this->module->l('Problematic row', 'EIImport') . ': <pre>' . print_r($info, true);
                    } else {
                        $this->errors[] = sprintf(
                                        $this->module->l('%1$s does not exist in database %2$s (ID: %3$s), and therefore cannot be saved.', 'EIImport'),
                                        Tools::htmlentitiesUTF8($customer->email),
                                        Tools::htmlentitiesUTF8(Db::getInstance()->getMsgError()),
                                        (int) $address->id_customer
                                )
                                . '<br> ' . $this->module->l('Location', 'EIImport') . ': ' . __CLASS__ . ':' . __LINE__ . '.'
                                . '<br> ' . $this->module->l('Row number in file', 'EIImport') . ": {$this->row}. " . $this->module->l('Problematic row', 'EIImport') . ': <pre>' . print_r($info, true);
                    }
                }
            } else {
                if ($validateOnly) {
                    $this->errors[] = sprintf(
                                    $this->module->l('The customer ID #%d does not exist in the database, and therefore cannot be validated.', 'EIImport'),
                                    Tools::htmlentitiesUTF8($address->id_customer)
                            )
                            . '<br> ' . $this->module->l('Location', 'EIImport') . ': ' . __CLASS__ . ':' . __LINE__ . '.'
                            . '<br> ' . $this->module->l('Row number in file', 'EIImport') . ": {$this->row}. " . $this->module->l('Problematic row', 'EIImport') . ': <pre>' . print_r($info, true);
                } else {
                    $this->errors[] = sprintf(
                                    $this->module->l('The customer ID #%d does not exist in the database, and therefore cannot be saved.', 'EIImport'),
                                    Tools::htmlentitiesUTF8($address->id_customer)
                            )
                            . '<br> ' . $this->module->l('Location', 'EIImport') . ': ' . __CLASS__ . ':' . __LINE__ . '.'
                            . '<br> ' . $this->module->l('Row number in file', 'EIImport') . ": {$this->row}. " . $this->module->l('Problematic row', 'EIImport') . ': <pre>' . print_r($info, true);
                }
            }
        } elseif (isset($address->customer_email) && !empty($address->customer_email)) {
            if (Validate::isEmail($address->customer_email)) {
                // a customer could exists in different shop
                $customer_list = Customer::getCustomersByEmail($address->customer_email);

                if (count($customer_list) == 0) {
                    if ($validateOnly) {
                        $this->errors[] = sprintf(
                                        $this->module->l('%1$s does not exist in database %2$s (ID: %3$s), and therefore cannot be validated', 'EIImport'),
                                        Tools::htmlentitiesUTF8($address->customer_email),
                                        Tools::htmlentitiesUTF8(Db::getInstance()->getMsgError()),
                                        !empty($info['id']) ? Tools::htmlentitiesUTF8($info['id']) : 'null'
                                )
                                . '<br> ' . $this->module->l('Location', 'EIImport') . ': ' . __CLASS__ . ':' . __LINE__ . '.'
                                . '<br> ' . $this->module->l('Row number in file', 'EIImport') . ": {$this->row}. " . $this->module->l('Problematic row', 'EIImport') . ': <pre>' . print_r($info, true);
                    } else {
                        $this->errors[] = sprintf(
                                        $this->module->l('%1$s does not exist in database %2$s (ID: %3$s), and therefore cannot be saved.', 'EIImport'),
                                        Tools::htmlentitiesUTF8($address->customer_email),
                                        Tools::htmlentitiesUTF8(Db::getInstance()->getMsgError()),
                                        !empty($info['id']) ? Tools::htmlentitiesUTF8($info['id']) : 'null'
                                )
                                . '<br> ' . $this->module->l('Location', 'EIImport') . ': ' . __CLASS__ . ':' . __LINE__ . '.'
                                . '<br> ' . $this->module->l('Row number in file', 'EIImport') . ": {$this->row}. " . $this->module->l('Problematic row', 'EIImport') . ': <pre>' . print_r($info, true);
                    }
                } else {
                    $address->id_customer = (int) end($customer_list)['id_customer'];
                }
            } else {
                $this->errors[] = sprintf(
                                $this->module->l('"%1$s" is not a valid email address.', 'EIImport'),
                                Tools::htmlentitiesUTF8($address->customer_email)
                        )
                        . '<br> ' . $this->module->l('Location', 'EIImport') . ': ' . __CLASS__ . ':' . __LINE__ . '.'
                        . '<br> ' . $this->module->l('Row number in file', 'EIImport') . ": {$this->row}. " . $this->module->l('Problematic row', 'EIImport') . ': <pre>' . print_r($info, true);

                return;
            }
        } else {
            $customer_list = [];
            $address->id_customer = 0;
        }

        if (isset($address->manufacturer) && is_numeric($address->manufacturer) && Manufacturer::manufacturerExists((int) $address->manufacturer)) {
            $address->id_manufacturer = (int) $address->manufacturer;
        } elseif (isset($address->manufacturer) && is_string($address->manufacturer) && !empty($address->manufacturer)) {
            if (($manufacturerId = Manufacturer::getIdByName($address->manufacturer))) {
                $address->id_manufacturer = $manufacturerId;
            } else {
                $manufacturer = new Manufacturer();
                $manufacturer->name = $address->manufacturer;
                if (($field_error = $manufacturer->validateFields(false, true)) === true &&
                        ($lang_field_error = $manufacturer->validateFieldsLang(false, true)) === true &&
                        !$validateOnly && // Do not move this condition: previous tests should be played always, but next ->add() test should not be played in validateOnly mode
                        $manufacturer->add()
                ) {
                    $address->id_manufacturer = (int) $manufacturer->id;
                } else {
                    if (!$validateOnly) {
                        $this->errors[] = Db::getInstance()->getMsgError() . ' ' . sprintf(
                                        $this->module->l('%1$s (ID: %2$s) cannot be saved.', 'EIImport'),
                                        Tools::htmlentitiesUTF8($manufacturer->name),
                                        !empty($manufacturer->id) ? Tools::htmlentitiesUTF8($manufacturer->id) : 'null'
                                )
                                . '<br> ' . $this->module->l('Location', 'EIImport') . ': ' . __CLASS__ . ':' . __LINE__ . '.'
                                . '<br> ' . $this->module->l('Row number in file', 'EIImport') . ": {$this->row}. " . $this->module->l('Problematic row', 'EIImport') . ': <pre>' . print_r($info, true);
                    }
                    if ($field_error !== true || isset($lang_field_error) && $lang_field_error !== true) {
                        $this->errors[] = ($field_error !== true ? $field_error : '') . (isset($lang_field_error) && $lang_field_error !== true ? $lang_field_error : '') .
                                Db::getInstance()->getMsgError()
                                . '<br> ' . $this->module->l('Location', 'EIImport') . ': ' . __CLASS__ . ':' . __LINE__ . '.'
                                . '<br> ' . $this->module->l('Row number in file', 'EIImport') . ": {$this->row}. " . $this->module->l('Problematic row', 'EIImport') . ': <pre>' . print_r($info, true);
                    }
                }
            }
        }

        if (isset($address->supplier) && is_numeric($address->supplier) && Supplier::supplierExists((int) $address->supplier)) {
            $address->id_supplier = (int) $address->supplier;
        } elseif (isset($address->supplier) && is_string($address->supplier) && !empty($address->supplier)) {
            if (($supplierId = Supplier::getIdByName($address->supplier))) {
                $address->id_supplier = $supplierId;
            } else {
                $supplier = new Supplier();
                $supplier->name = $address->supplier;
                if (($field_error = $supplier->validateFields(false, true)) === true &&
                        ($lang_field_error = $supplier->validateFieldsLang(false, true)) === true &&
                        !$validateOnly && // Do not move this condition: previous tests should be played always, but next ->add() test should not be played in validateOnly mode
                        $supplier->add()
                ) {
                    $address->id_supplier = (int) $supplier->id;
                } else {
                    if (!$validateOnly) {
                        $this->errors[] = Db::getInstance()->getMsgError() . ' ' . sprintf(
                                        $this->module->l('%1$s (ID: %2$s) cannot be saved.', 'EIImport'),
                                        Tools::htmlentitiesUTF8($supplier->name),
                                        !empty($supplier->id) ? Tools::htmlentitiesUTF8($supplier->id) : 'null'
                                )
                                . '<br> ' . $this->module->l('Location', 'EIImport') . ': ' . __CLASS__ . ':' . __LINE__ . '.'
                                . '<br> ' . $this->module->l('Row number in file', 'EIImport') . ": {$this->row}. " . $this->module->l('Problematic row', 'EIImport') . ': <pre>' . print_r($info, true);
                    }
                    if ($field_error !== true || isset($lang_field_error) && $lang_field_error !== true) {
                        $this->errors[] = ($field_error !== true ? $field_error : '') . (isset($lang_field_error) && $lang_field_error !== true ? $lang_field_error : '') .
                                Db::getInstance()->getMsgError()
                                . '<br> ' . $this->module->l('Location', 'EIImport') . ': ' . __CLASS__ . ':' . __LINE__ . '.'
                                . '<br> ' . $this->module->l('Row number in file', 'EIImport') . ": {$this->row}. " . $this->module->l('Problematic row', 'EIImport') . ': <pre>' . print_r($info, true);
                    }
                }
            }
        }

        $res = false;
        if (($field_error = $address->validateFields(false, true)) === true &&
                ($lang_field_error = $address->validateFieldsLang(false, true)) === true
        ) {
            $address->force_id = (bool) $force_id;

            if ($address->id && $address->addressExists($address->id)) {
                $res = ($validateOnly || $address->update());
            }
            if (!$res) {
                $res = ($validateOnly || $address->add(!$address->date_add));
            }
        }
        if (!$res) {
            if (!$validateOnly) {
                $this->errors[] = sprintf(
                                $this->module->l('%1$s (ID: %2$s) cannot be saved.', 'EIImport'),
                                Tools::htmlentitiesUTF8($info['alias']),
                                !empty($info['id']) ? Tools::htmlentitiesUTF8($info['id']) : 'null'
                        )
                        . '<br> ' . $this->module->l('Location', 'EIImport') . ': ' . __CLASS__ . ':' . __LINE__ . '.'
                        . '<br> ' . $this->module->l('Row number in file', 'EIImport') . ": {$this->row}. " . $this->module->l('Problematic row', 'EIImport') . ': <pre>' . print_r($info, true);
            }
            if ($field_error !== true || isset($lang_field_error) && $lang_field_error !== true) {
                $this->errors[] = ($field_error !== true ? $field_error : '') . (isset($lang_field_error) && $lang_field_error !== true ? $lang_field_error : '') .
                        Db::getInstance()->getMsgError()
                        . '<br> ' . $this->module->l('Location', 'EIImport') . ': ' . __CLASS__ . ':' . __LINE__ . '.'
                        . '<br> ' . $this->module->l('Row number in file', 'EIImport') . ": {$this->row}. " . $this->module->l('Problematic row', 'EIImport') . ': <pre>' . print_r($info, true);
            }
        }
    }

    public function validateFields($row, $entity)
    {
        static $ps_allow_html_iframe = null;
        if ($ps_allow_html_iframe === null) {
            $ps_allow_html_iframe = (int) Configuration::get('PS_ALLOW_HTML_IFRAME');
        }

        foreach ($entity::$definition['fields'] as $field => $data) {
            // Check if field is required
            if (!empty($data['required'])) {
                if (Tools::isEmpty($row[$field])) {
                    return 'Property ' . $entity . '->' . $field . ' is empty';
                }
            }

            // Check field values
            if (!empty($data['values']) && is_array($data['values']) && !in_array($row[$field], $data['values'])) {
                return 'Property ' . $entity . '->' . $field . ' has bad value (allowed values are: ' . implode(', ', $data['values']) . ')';
            }

            // Check field size
            if (!empty($data['size'])) {
                $size = $data['size'];
                if (!is_array($data['size'])) {
                    $size = array('min' => 0, 'max' => $data['size']);
                }

                $length = Tools::strlen($row[$field]);
                if ($length < $size['min'] || $length > $size['max']) {
                    return 'Property ' . $entity . '->' . $field . ' length (' . $length . ') must be between ' . $size['min'] . ' and ' . $size['max'];
                }
            }
            // Check field validator
            if (!empty($data['validate'])) {
                if (!method_exists('Validate', $data['validate'])) {
                    throw new PrestaShopException('Validation function not found. ' . $data['validate']);
                }

                if (!empty($row[$field])) {
                    $res = true;
                    if (Tools::strtolower($data['validate']) == 'iscleanhtml') {
                        if (!call_user_func(array('Validate', $data['validate']), $row[$field], $ps_allow_html_iframe)) {
                            $res = false;
                        }
                    } else {
                        if (!call_user_func(array('Validate', $data['validate']), $row[$field])) {
                            $res = false;
                        }
                    }
                    if (!$res) {
                        return 'Property ' . $entity . '->' . $field . ' is not valid';
                    }
                }
            }
        }
        return true;
    }

    private function getCarrierUpdate(&$info)
    {
        $id_carrier = null;
        if ($this->updateBy == 'id' && !empty($info['id'])) {
            $id_carrier = (int) Db::getInstance()->getValue('
                        SELECT id_carrier
                        FROM `' . _DB_PREFIX_ . 'carrier`
                        WHERE `id_carrier` = ' . (int) $info['id'], false);
        } elseif ($this->updateBy == 'id_reference' && !empty($info['id_reference'])) {
            $id_carrier = (int) Db::getInstance()->getValue('
                        SELECT id_carrier
                        FROM `' . _DB_PREFIX_ . 'carrier`
                        WHERE `deleted` = 0 AND `id_reference` = ' . (int) $info['id_reference'], false);
        } elseif ($this->updateBy == 'name' && !empty($info['name'])) {
            $id_carrier = (int) Db::getInstance()->getValue('
                        SELECT id_carrier
                        FROM `' . _DB_PREFIX_ . 'carrier`
                        WHERE `deleted` = 0 AND `name` = \'' . pSQL($info['name']) . '\'', false);
        }

        if ($id_carrier && $this->targetAction != 'insert') {
            $info['id'] = $id_carrier;
        }
        return $id_carrier;
    }

    private function getCarrierInsertion(&$info, $force_id)
    {
        if ($force_id && !empty($info['id']) && ObjectModel::existsInDatabase((int) $info['id'], 'carrier')) {
            return false;
        }
        if ($this->nameExists == 'ignore' && !empty($info['name']) &&
                Db::getInstance()->getRow('
                        SELECT *
                        FROM `' . _DB_PREFIX_ . 'carrier`
                        WHERE `deleted` = 0 AND `name` = \'' . pSQL($info['name']) . '\'', false)) {
            return false;
        }

        if (!$force_id) {
            unset($info['id']);
        }
        return true;
    }

    protected function carrierImportOne($info, $default_language_id, $id_lang, $shop_is_feature_active, $force_id, $validateOnly = false)
    {
        if ($this->targetAction == 'insert') {
            if (!$this->getCarrierInsertion($info, $force_id)) {
                return false;
            }
        } elseif ($this->targetAction == 'update') {
            if (!$this->getCarrierUpdate($info)) {
                return false;
            }
        } else {
            if (!$this->getCarrierUpdate($info) && !$this->getCarrierInsertion($info, $force_id)) {
                return false;
            }
        }


        // Correct some formats
        $info['max_weight'] = str_replace(',', '.', $info['max_weight']);

        if (isset($info['id']) && (int) $info['id'] && ($force_id || ObjectModel::existsInDatabase((int) $info['id'], 'carrier'))) {
            $carrier = new Carrier((int) $info['id']);
        } else {
            $carrier = new Carrier();
            self::setDefaultValues($info);
        }

        // Group Import
        $carrier_groups = [];
        if (!empty($info['groups'])) {
            foreach (explode($this->multivalueSeparator, $info['groups']) as $group) {
                $group = trim($group);
                if (empty($group)) {
                    continue;
                }
                $id_group = false;
                if (is_numeric($group) && $group) {
                    $my_group = new Group((int) $group);
                    if (Validate::isLoadedObject($my_group)) {
                        $carrier_groups[] = (int) $group;
                    }

                    continue;
                }
                $my_group = Group::searchByName($group);
                if (isset($my_group['id_group']) && $my_group['id_group']) {
                    $id_group = (int) $my_group['id_group'];
                }
                if (!$id_group) {
                    $my_group = new Group();
                    $my_group->name = [$id_lang => $group];
                    if ($id_lang != $default_language_id) {
                        $my_group->name = $my_group->name + [$default_language_id => $group];
                    }
                    $my_group->price_display_method = 1;
                    if (!$validateOnly) {
                        $my_group->add();
                        if (Validate::isLoadedObject($my_group)) {
                            $id_group = (int) $my_group->id;
                        }
                    }
                }
                if ($id_group) {
                    $carrier_groups[] = (int) $id_group;
                }
            }
        } else {
            $carrier_groups = [
                Configuration::get('PS_UNIDENTIFIED_GROUP'),
                Configuration::get('PS_GUEST_GROUP'),
                Configuration::get('PS_CUSTOMER_GROUP')
            ];
        }

        $carrier_groups = array_flip(array_flip($carrier_groups));

        // Zone Import
        $carrier_zones = [];
        if (!empty($info['zones'])) {
            foreach (explode($this->multivalueSeparator, $info['zones']) as $zone) {
                $zone_active = explode(':', $zone);
                $zone = trim($zone_active[0]);
                $active = $zone_active[1];
                if (!is_numeric($active)) {
                    $active = 1;
                }
                if (empty($zone)) {
                    continue;
                }

                if (is_numeric($zone) && $zone) {
                    $my_zone = new Zone((int) $zone);
                    if (Validate::isLoadedObject($my_zone)) {
                        $carrier_zones[] = (int) $zone;
                    }

                    continue;
                }
                $id_zone = (int) Zone::getIdByName($zone);
                if (!$id_zone) {
                    $my_zone = new Zone();
                    $my_zone->name = $zone;
                    $my_zone->active = $active;
                    if (!$validateOnly) {
                        $my_zone->add();
                        if (Validate::isLoadedObject($my_zone)) {
                            $id_zone = (int) $my_zone->id;
                        }
                    }
                }
                if ($id_zone) {
                    $carrier_zones[] = (int) $id_zone;
                }
            }
        }

        $carrier_zones = array_flip(array_flip($carrier_zones));

        self::arrayWalk($info, [self::class, 'fillInfo'], $carrier);

        if (($field_error = $carrier->validateFields(false, true)) !== true ||
                ($lang_field_error = $carrier->validateFieldsLang(false, true)) !== true) {
            $this->errors[] = ($field_error !== true ? $field_error : '') . (isset($lang_field_error) && $lang_field_error !== true ? $lang_field_error : '')
                    . Db::getInstance()->getMsgError()
                    . '<br> ' . $this->module->l('Location', 'EIImport') . ': ' . __CLASS__ . ':' . __LINE__ . '.'
                    . '<br> ' . $this->module->l('Row number in file', 'EIImport') . ": {$this->row}. " . $this->module->l('Problematic row', 'EIImport') . ': <pre>' . print_r($info, true);
            return false;
        }

        if (!$validateOnly) {
            $res = false;
            if ($carrier->id && ObjectModel::existsInDatabase((int) $carrier->id, 'carrier')) {
                if (($res = $carrier->update())) {
                    $carrier->setGroups($carrier_groups);
                    if ($carrier_zones) {
                        $this->updateCarrierZones((int) $carrier->id, $carrier_zones);
                    }
                }
            }
            $carrier->force_id = (bool) $force_id;
            if (!$res) {
                if (($res = $carrier->add())) {
                    $carrier->setGroups($carrier_groups);
                    if ($carrier_zones) {
                        $this->updateCarrierZones((int) $carrier->id, $carrier_zones);
                    }
                }
            }

            // set price ranges
            if (!$validateOnly) {
                if (!empty($info['price_range'])) {
                    $ranges = explode($this->multivalueSeparator, $info['price_range']);
                    foreach ($ranges as $key => $range) {
                        $dels = explode('-', $range);
                        $del1 = trim($dels[0]);
                        $del2 = trim(isset($dels[1]) ? $dels[1] : 0);

                        $sql = 'INSERT INTO `' . _DB_PREFIX_ . 'range_price` (`id_carrier`, `delimiter1`, `delimiter2`) 
                        VALUES (' . (int) $carrier->id . ', ' . (float) $del1 . ', ' . (float) $del2 . ') 
                        ON DUPLICATE KEY UPDATE `delimiter2` = delimiter2';

                        Db::getInstance()->execute($sql);
                    }
                } elseif (isset($info['price_range']) && !self::$keepOldValue) {
                    Db::getInstance()->delete('range_price', '`id_carrier` = ' . (int) $carrier->id, false);
                }

                if (!empty($info['price_range_price'])) {
                    $zone_prices = explode($this->multivalueSeparator, $info['price_range_price']);
                    Db::getInstance()->delete('delivery', '`id_carrier` = ' . (int) $carrier->id . ' AND id_shop IS NULL AND id_shop_group IS NULL AND id_range_price > 0', false);
                    foreach ($zone_prices as $zone_price) {
                        $exp = explode('>', $zone_price);
                        if (!isset($exp[1]) || !trim($exp[1])) {
                            continue;
                        }

                        $id_range = null;
                        if (($range = trim($exp[0]))) {
                            $dels = explode('-', $range);
                            $del1 = trim($dels[0]);
                            $del2 = trim(isset($dels[1]) ? $dels[1] : 0);

                            $id_range = Db::getInstance()->getValue('
                            SELECT id_range_price
                            FROM `' . _DB_PREFIX_ . 'range_price`
                            WHERE `id_carrier` = ' . (int) $carrier->id . ' AND delimiter1 = ' . (float) $del1 . ' AND delimiter2 = ' . (float) $del2, false);
                        }

                        $zps = explode('!', $exp[1]);
                        foreach ($zps as $zp) {
                            $p = explode(':', $zp);
                            $id_zone = (int) Zone::getIdByName(trim($p[0]));
                            $price = isset($p[1]) ? trim($p[1]) : 0;

                            $sql = 'INSERT INTO `' . _DB_PREFIX_ . 'delivery`
                            VALUES (NULL, NULL, NULL, ' . (int) $carrier->id . ', ' . $id_range . ', NULL, ' . $id_zone . ', ' . (float) $price . ')';
                            Db::getInstance()->execute($sql);
                        }
                    }
                } elseif (isset($info['price_range_price']) && !self::$keepOldValue) {
                    Db::getInstance()->delete('delivery', '`id_carrier` = ' . (int) $carrier->id . ' AND id_shop IS NULL AND id_shop_group IS NULL AND id_range_price > 0', false);
                }

                // set weight ranges
                if (!empty($info['weight_range'])) {
                    $ranges = explode($this->multivalueSeparator, $info['weight_range']);
                    foreach ($ranges as $key => $range) {
                        $dels = explode('-', $range);
                        $del1 = trim($dels[0]);
                        $del2 = trim(isset($dels[1]) ? $dels[1] : 0);

                        $sql = 'INSERT INTO `' . _DB_PREFIX_ . 'range_weight` (`id_carrier`, `delimiter1`, `delimiter2`) 
                        VALUES (' . (int) $carrier->id . ', ' . (float) $del1 . ', ' . (float) $del2 . ') 
                        ON DUPLICATE KEY UPDATE `delimiter2` = delimiter2';

                        Db::getInstance()->execute($sql);
                    }
                } elseif (isset($info['weight_range']) && !self::$keepOldValue) {
                    Db::getInstance()->delete('range_weight', '`id_carrier` = ' . (int) $carrier->id, false);
                }

                if (!empty($info['weight_range_price'])) {
                    $zone_weights = explode($this->multivalueSeparator, $info['weight_range_price']);
                    Db::getInstance()->delete('delivery', '`id_carrier` = ' . (int) $carrier->id . ' AND id_shop IS NULL AND id_shop_group IS NULL AND id_range_weight > 0', false);
                    foreach ($zone_weights as $zone_weight) {
                        $exp = explode('>', $zone_weight);
                        if (!isset($exp[1]) || !trim($exp[1])) {
                            continue;
                        }

                        $id_range = null;
                        if (($range = trim($exp[0]))) {
                            $dels = explode('-', $range);
                            $del1 = trim($dels[0]);
                            $del2 = trim(isset($dels[1]) ? $dels[1] : 0);

                            $id_range = Db::getInstance()->getValue('
                            SELECT id_range_weight
                            FROM `' . _DB_PREFIX_ . 'range_weight`
                            WHERE `id_carrier` = ' . (int) $carrier->id . ' AND delimiter1 = ' . (float) $del1 . ' AND delimiter2 = ' . (float) $del2, false);
                        }

                        $zps = explode('!', $exp[1]);
                        foreach ($zps as $zp) {
                            $p = explode(':', $zp);
                            $id_zone = (int) Zone::getIdByName(trim($p[0]));
                            $price = isset($p[1]) ? trim($p[1]) : 0;

                            $sql = 'INSERT INTO `' . _DB_PREFIX_ . 'delivery`
                            VALUES (NULL, NULL, NULL, ' . (int) $carrier->id . ', NULL, ' . $id_range . ', ' . $id_zone . ', ' . (float) $price . ')';
                            Db::getInstance()->execute($sql);
                        }
                    }
                } elseif (isset($info['weight_range_price']) && !self::$keepOldValue) {
                    Db::getInstance()->delete('delivery', '`id_carrier` = ' . (int) $carrier->id . ' AND id_shop IS NULL AND id_shop_group IS NULL AND id_range_weight > 0', false);
                }
            }

            // copying images of carrier
            if (!empty($carrier->logo_url)) {
                if (!self::copyImg($carrier->id, null, $carrier->logo_url, 'carriers', false)) {
                    $this->warnings[] = $carrier->logo_url . ' ' . $this->module->l('cannot be copied.', 'EIImport');
                }
            }

            if ($res) {
                // Associate carrier to group shop
                if ($shop_is_feature_active) {
                    $data = array();
                    foreach ($this->shops as $id_shop) {
                        $data[] = array(
                            Carrier::$definition['primary'] => (int) $carrier->id,
                            'id_shop' => (int) $id_shop,
                        );
                    }
                    if ($data) {
                        $res = Db::getInstance()->insert(Carrier::$definition['table'] . '_shop', $data, false, true, Db::INSERT_IGNORE);
                    }
                }
                // Associate tax rule
                Db::getInstance()->execute('
					DELETE FROM ' . _DB_PREFIX_ . 'carrier_tax_rules_group_shop
					WHERE id_carrier = ' . (int) $carrier->id);
                $data = array();
                foreach (Shop::getCompleteListOfShopsID() as $id_shop) {
                    $data[] = array(
                        Carrier::$definition['primary'] => (int) $carrier->id,
                        'id_tax_rules_group' => (int) $carrier->id_tax_rules_group,
                        'id_shop' => (int) $id_shop,
                    );
                }
                if ($data) {
                    return $res && Db::getInstance()->insert('carrier_tax_rules_group_shop', $data, false, true, Db::INSERT_IGNORE);
                }
            } else {
                $this->errors[] = Db::getInstance()->getMsgError() . ' ' . sprintf(
                                $this->module->l('%1$s (ID: %2$s) cannot be saved.', 'EIImport'),
                                !empty($info['name']) ? Tools::safeOutput($info['name']) : 'No Name',
                                !empty($info['id']) ? Tools::safeOutput($info['id']) : 'No ID'
                        )
                        . '<br> ' . $this->module->l('Location', 'EIImport') . ': ' . __CLASS__ . ':' . __LINE__ . '.'
                        . '<br> ' . $this->module->l('Row number in file', 'EIImport') . ": {$this->row}. " . $this->module->l('Problematic row', 'EIImport') . ': <pre>' . print_r($info, true);
            }
        }
    }

    protected function updateCarrierZones($id_carr, $zones)
    {
        Db::getInstance()->execute('
					DELETE FROM ' . _DB_PREFIX_ . 'carrier_zone
					WHERE id_carrier = ' . $id_carr);
        $data = array();
        foreach ($zones as $zone) {
            $data[] = array(
                'id_carrier' => $id_carr,
                'id_zone' => $zone,
            );
        }
        if ($data) {
            return Db::getInstance()->insert('carrier_zone', $data);
        }
    }

    /**
     * copyImg copy an image located in $url and save it in a path
     * according to $entity->$id_entity .
     * $id_image is used if we need to add a watermark.
     *
     * @param int $id_entity id of product or category (set in entity)
     * @param int $id_image (default null) id of the image if watermark enabled
     * @param string $url path or url to use
     * @param string $entity 'products' or 'categories'
     * @param bool $regenerate
     *
     * @return bool
     */
    protected static function copyImg($id_entity, $id_image = null, $url = '', $entity = 'products', $regenerate = true, $exists = false, $tmp_path = null)
    {
        $tmpfile = tempnam(_PS_TMP_IMG_DIR_, 'ip_import');
        $watermark_types = explode(',', Configuration::get('WATERMARK_TYPES'));

        switch ($entity) {
            default:
            case 'products':
                $image_obj = new Image($id_image);
                $path = $image_obj->getPathForCreation();

                break;
            case 'categories':
                $path = _PS_CAT_IMG_DIR_ . (int) $id_entity;

                break;
            case 'manufacturers':
                $path = _PS_MANU_IMG_DIR_ . (int) $id_entity;

                break;
            case 'suppliers':
                $path = _PS_SUPP_IMG_DIR_ . (int) $id_entity;

                break;
            case 'carriers':
                $path = _PS_SHIP_IMG_DIR_ . (int) $id_entity;

                break;
            case 'stores':
                $path = _PS_STORE_IMG_DIR_ . (int) $id_entity;

                break;
        }

        $url = urldecode(trim($url));
//        $parsed_url = parse_url($url);
//
//        if (isset($parsed_url['path'])) {
//            $uri = ltrim($parsed_url['path'], '/');
//            $parts = explode('/', $uri);
//            foreach ($parts as &$part) {
//                $part = rawurlencode($part);
//            }
//            unset($part);
//            $parsed_url['path'] = '/' . implode('/', $parts);
//        }
//
//        if (isset($parsed_url['query'])) {
//            $query_parts = [];
//            parse_str($parsed_url['query'], $query_parts);
//            $parsed_url['query'] = http_build_query($query_parts);
//        }
//
//        if (!function_exists('http_build_url')) {
//            require_once _PS_TOOL_DIR_ . 'http_build_url/http_build_url.php';
//        }
//
//        $url = http_build_url('', $parsed_url);

        $orig_tmpfile = $tmpfile;

        //        Tools::copy($url, $tmpfile);
        if (self::$imageDownloadType === self::DOWNLOAD_IMG_WITHOUT_CONTEXT) {
            $resp = file_put_contents($tmpfile, Tools::file_get_contents($url, false, null, 5, true));
        } else {
            $arrContextOptions = array(
                "ssl" => array(
                    "verify_peer" => false,
                    "verify_peer_name" => false,
                ),
            );
            $resp = file_put_contents($tmpfile, file_get_contents($url, false, stream_context_create($arrContextOptions)));
        }
        if ($resp) {
            // Evaluate the memory required to resize the image: if it's too much, you can't resize it.
            if (!ImageManager::checkImageMemoryLimit($tmpfile)) {
                @unlink($tmpfile);

                return false;
            }

            $tgt_width = $tgt_height = 0;
            $src_width = $src_height = 0;
            $error = 0;

            if (!$exists) {
                if ($tmp_path) {
                    rename($tmp_path, $path . '.jpg');
                } else {
                    ImageManager::resize($tmpfile, $path . '.jpg', null, null, 'jpg', false, $error, $tgt_width, $tgt_height, 5, $src_width, $src_height);
                }
            }

            if (file_exists($tmp_path)) {
                @unlink($tmp_path);
            }

            if ($regenerate) {
                $images_types = ImageType::getImagesTypes($entity, true);
                $path_infos = [];
                $path_infos[] = [$tgt_width, $tgt_height, $path . '.jpg'];
                foreach ($images_types as $image_type) {
                    if (!$exists) {
                        $tmpfile = self::getBestPath($image_type['width'], $image_type['height'], $path_infos);

                        if (ImageManager::resize(
                                        $tmpfile,
                                        $path . '-' . Tools::stripslashes($image_type['name']) . '.jpg',
                                        $image_type['width'],
                                        $image_type['height'],
                                        'jpg',
                                        false,
                                        $error,
                                        $tgt_width,
                                        $tgt_height,
                                        5,
                                        $src_width,
                                        $src_height
                                )) {
                            // the last image should not be added in the candidate list if it's bigger than the original image
                            if ($tgt_width <= $src_width && $tgt_height <= $src_height) {
                                $path_infos[] = [$tgt_width, $tgt_height, $path . '-' . Tools::stripslashes($image_type['name']) . '.jpg'];
                            }
                            if ($entity == 'products') {
                                if (is_file(_PS_TMP_IMG_DIR_ . 'product_mini_' . (int) $id_entity . '.jpg')) {
                                    unlink(_PS_TMP_IMG_DIR_ . 'product_mini_' . (int) $id_entity . '.jpg');
                                }
                                $context = Context::getContext();
                                if (is_file(_PS_TMP_IMG_DIR_ . 'product_mini_' . (int) $id_entity . '_' . (int) $context->shop->id . '.jpg')) {
                                    unlink(_PS_TMP_IMG_DIR_ . 'product_mini_' . (int) $id_entity . '_' . (int) $context->shop->id . '.jpg');
                                }
                            }
                        }
                    }
                    if (in_array($image_type['id_image_type'], $watermark_types)) {
                        Hook::exec('actionWatermark', ['id_image' => $id_image, 'id_product' => $id_entity]);
                    }
                }
            }
        } else {
            @unlink($orig_tmpfile);
            @unlink($tmp_path);

            return false;
        }
        unlink($orig_tmpfile);

        return true;
    }

    private function getImageLink($id_image)
    {
        $theme = Shop::isFeatureActive() && file_exists(_PS_PROD_IMG_DIR_ . Image::getImgFolderStatic($id_image) . $id_image . '-' . (int) $this->context->shop->id_theme . '.jpg') ? '-' . $this->context->shop->id_theme : '';
        $uri_path = _THEME_PROD_DIR_ . Image::getImgFolderStatic($id_image) . $id_image . $theme . '.jpg';
        return $this->context->link->protocol_content . Tools::getMediaServer($uri_path) . $uri_path;
    }

    protected static function getBestPath($tgt_width, $tgt_height, $path_infos)
    {
        $path_infos = array_reverse($path_infos);
        $path = '';
        foreach ($path_infos as $path_info) {
            list($width, $height, $path) = $path_info;
            if ($width >= $tgt_width && $height >= $tgt_height) {
                return $path;
            }
        }

        return $path;
    }

    public static function arrayWalk(&$array, $funcname, &$user_data = false)
    {
        if (!is_callable($funcname)) {
            return false;
        }

        foreach ($array as $k => $row) {
            if (!call_user_func_array($funcname, [$row, $k, &$user_data])) {
                return false;
            }
        }

        return true;
    }

    protected static function fillInfo($info, $key, &$entity)
    {
        $info = trim($info);
        if (isset(self::$validators[$key][1]) && self::$validators[$key][1] == 'createMultiLangField' && $id_lang = Tools::getValue('language')) {
            $tmp = call_user_func(self::$validators[$key], $info);
            foreach ($tmp as $id_lang_tmp => $value) {
                if (empty($entity->{$key}[$id_lang_tmp]) || $id_lang_tmp == $id_lang) {
                    $entity->{$key}[$id_lang_tmp] = $value;
                }
            }
        } elseif (!empty($info) || $info == '0' || !self::$keepOldValue) { // ($infos == '0') => if you want to disable a product by using "0" in active because empty('0') return true
            $entity->{$key} = isset(self::$validators[$key]) ? call_user_func(self::$validators[$key], $info) : $info;
        }

        return true;
    }

    protected static function getBoolean($field)
    {
        return (bool) $field;
    }

    protected static function getPrice($field)
    {
        $field = ((float) str_replace(',', '.', $field));
        $field = ((float) str_replace('%', '', $field));

        return $field;
    }

    protected static function split($field)
    {
        if (empty($field)) {
            return [];
        }

        $separator = Tools::getValue('multivalue_separator');
        if (null === $separator || trim($separator) == '') {
            $separator = ',';
        }

        $tab = explode($separator, $field);

        if (empty($tab) || !is_array($tab)) {
            return [];
        }

        return $tab;
    }

    protected static function createMultiLangField($field)
    {
        $res = [];
        foreach (Language::getIDs(false) as $id_lang) {
            $res[$id_lang] = $field;
        }

        return $res;
    }

    protected static function setDefaultValues(&$info)
    {
        foreach (self::$default_values as $k => $v) {
            if (!isset($info[$k]) || $info[$k] == '') {
                $info[$k] = $v;
            }
        }
    }

    protected static function setEntityDefaultValues(&$entity)
    {
        $members = get_object_vars($entity);
        foreach (self::$default_values as $k => $v) {
            if (array_key_exists($k, $members) && $entity->$k === null || !array_key_exists($k, $members)) {
                $entity->$k = $v;
            }
        }
    }

    public static function getMaskedRow($row)
    {
        $res = [];
        if (is_array(self::$column_mask)) {
            foreach (self::$column_mask as $type => $nb) {
                $res[$type] = isset($row[$nb]) ? trim($row[$nb]) : null;
            }
        }

        return $res;
    }

    protected function receiveColumns()
    {
        $type_value = Tools::getValue('type_value') ? Tools::getValue('type_value') : [];
        foreach ($type_value as $nb => $type) {
            if ($type != 'no') {
                self::$column_mask[$type] = $nb;
            }
        }
    }

    protected function truncateTables($entity)
    {
        switch ($entity) {
            case 'categories':
                Db::getInstance()->execute('
					DELETE FROM `' . _DB_PREFIX_ . 'category`
					WHERE id_category NOT IN (' . (int) Configuration::get('PS_HOME_CATEGORY') .
                        ', ' . (int) Configuration::get('PS_ROOT_CATEGORY') . ')');
                Db::getInstance()->execute('
					DELETE FROM `' . _DB_PREFIX_ . 'category_lang`
					WHERE id_category NOT IN (' . (int) Configuration::get('PS_HOME_CATEGORY') .
                        ', ' . (int) Configuration::get('PS_ROOT_CATEGORY') . ')');
                Db::getInstance()->execute('
					DELETE FROM `' . _DB_PREFIX_ . 'category_shop`
					WHERE `id_category` NOT IN (' . (int) Configuration::get('PS_HOME_CATEGORY') .
                        ', ' . (int) Configuration::get('PS_ROOT_CATEGORY') . ')');
                Db::getInstance()->execute('ALTER TABLE `' . _DB_PREFIX_ . 'category` AUTO_INCREMENT = 3');
                Db::getInstance()->execute('
					DELETE FROM `' . _DB_PREFIX_ . 'category_group`
					WHERE id_category NOT IN (' . (int) Configuration::get('PS_HOME_CATEGORY') .
                        ', ' . (int) Configuration::get('PS_ROOT_CATEGORY') . ')');
                Db::getInstance()->execute('
					DELETE FROM `' . _DB_PREFIX_ . 'group_reduction`
					WHERE id_category NOT IN (' . (int) Configuration::get('PS_HOME_CATEGORY') .
                        ', ' . (int) Configuration::get('PS_ROOT_CATEGORY') . ')');
                foreach (scandir(_PS_CAT_IMG_DIR_, SCANDIR_SORT_NONE) as $d) {
                    if (preg_match('/^[0-9]+(\-(.*))?\.jpg$/', $d)) {
                        unlink(_PS_CAT_IMG_DIR_ . $d);
                    }
                }

                break;
            case 'products':
                Db::getInstance()->execute('TRUNCATE TABLE `' . _DB_PREFIX_ . 'product`');
                Db::getInstance()->execute('TRUNCATE TABLE `' . _DB_PREFIX_ . 'product_shop`');
                Db::getInstance()->execute('TRUNCATE TABLE `' . _DB_PREFIX_ . 'feature_product`');
                Db::getInstance()->execute('TRUNCATE TABLE `' . _DB_PREFIX_ . 'product_lang`');
                Db::getInstance()->execute('TRUNCATE TABLE `' . _DB_PREFIX_ . 'category_product`');
                Db::getInstance()->execute('TRUNCATE TABLE `' . _DB_PREFIX_ . 'product_tag`');
                Db::getInstance()->execute('TRUNCATE TABLE `' . _DB_PREFIX_ . 'image`');
                Db::getInstance()->execute('TRUNCATE TABLE `' . _DB_PREFIX_ . 'image_lang`');
                Db::getInstance()->execute('TRUNCATE TABLE `' . _DB_PREFIX_ . 'image_shop`');
                Db::getInstance()->execute('TRUNCATE TABLE `' . _DB_PREFIX_ . 'specific_price`');
                Db::getInstance()->execute('TRUNCATE TABLE `' . _DB_PREFIX_ . 'specific_price_priority`');
                Db::getInstance()->execute('TRUNCATE TABLE `' . _DB_PREFIX_ . 'product_carrier`');
                Db::getInstance()->execute('TRUNCATE TABLE `' . _DB_PREFIX_ . 'cart_product`');
                if (count(Db::getInstance()->executeS('SHOW TABLES LIKE \'' . _DB_PREFIX_ . 'favorite_product\''))) { //check if table exist
                    Db::getInstance()->execute('TRUNCATE TABLE `' . _DB_PREFIX_ . 'favorite_product`');
                }
                Db::getInstance()->execute('TRUNCATE TABLE `' . _DB_PREFIX_ . 'product_attachment`');
                Db::getInstance()->execute('TRUNCATE TABLE `' . _DB_PREFIX_ . 'product_country_tax`');
                Db::getInstance()->execute('TRUNCATE TABLE `' . _DB_PREFIX_ . 'product_download`');
                Db::getInstance()->execute('TRUNCATE TABLE `' . _DB_PREFIX_ . 'product_group_reduction_cache`');
                Db::getInstance()->execute('TRUNCATE TABLE `' . _DB_PREFIX_ . 'product_sale`');
                Db::getInstance()->execute('TRUNCATE TABLE `' . _DB_PREFIX_ . 'product_supplier`');
                Db::getInstance()->execute('TRUNCATE TABLE `' . _DB_PREFIX_ . 'warehouse_product_location`');
                Db::getInstance()->execute('TRUNCATE TABLE `' . _DB_PREFIX_ . 'stock`');
                Db::getInstance()->execute('TRUNCATE TABLE `' . _DB_PREFIX_ . 'stock_available`');
                Db::getInstance()->execute('TRUNCATE TABLE `' . _DB_PREFIX_ . 'stock_mvt`');
                Db::getInstance()->execute('TRUNCATE TABLE `' . _DB_PREFIX_ . 'customization`');
                Db::getInstance()->execute('TRUNCATE TABLE `' . _DB_PREFIX_ . 'customization_field`');
                Db::getInstance()->execute('TRUNCATE TABLE `' . _DB_PREFIX_ . 'customization_field_lang`');
                Db::getInstance()->execute('TRUNCATE TABLE `' . _DB_PREFIX_ . 'supply_order_detail`');
                if (count(Db::getInstance()->executeS('SHOW TABLES LIKE \'' . _DB_PREFIX_ . 'attribute_impact\''))) { //check if table exist
                    Db::getInstance()->execute('TRUNCATE TABLE `' . _DB_PREFIX_ . 'attribute_impact`');
                }
                Db::getInstance()->execute('TRUNCATE TABLE `' . _DB_PREFIX_ . 'product_attribute`');
                Db::getInstance()->execute('TRUNCATE TABLE `' . _DB_PREFIX_ . 'product_attribute_shop`');
                Db::getInstance()->execute('TRUNCATE TABLE `' . _DB_PREFIX_ . 'product_attribute_combination`');
                Db::getInstance()->execute('TRUNCATE TABLE `' . _DB_PREFIX_ . 'product_attribute_image`');
                Db::getInstance()->execute('TRUNCATE TABLE `' . _DB_PREFIX_ . 'pack`');
                //                Image::deleteAllImages(_PS_PROD_IMG_DIR_);
                //                if (!file_exists(_PS_PROD_IMG_DIR_)) {
                //                    mkdir(_PS_PROD_IMG_DIR_);
                //                }

                break;
            case 'combinations':
                Db::getInstance()->execute('TRUNCATE TABLE `' . _DB_PREFIX_ . 'attribute`');
                if (count(Db::getInstance()->executeS('SHOW TABLES LIKE \'' . _DB_PREFIX_ . 'attribute_impact\''))) { //check if table exist
                    Db::getInstance()->execute('TRUNCATE TABLE `' . _DB_PREFIX_ . 'attribute_impact`');
                }
                Db::getInstance()->execute('TRUNCATE TABLE `' . _DB_PREFIX_ . 'attribute_lang`');
                Db::getInstance()->execute('TRUNCATE TABLE `' . _DB_PREFIX_ . 'attribute_group`');
                Db::getInstance()->execute('TRUNCATE TABLE `' . _DB_PREFIX_ . 'attribute_group_lang`');
                Db::getInstance()->execute('TRUNCATE TABLE `' . _DB_PREFIX_ . 'attribute_group_shop`');
                Db::getInstance()->execute('TRUNCATE TABLE `' . _DB_PREFIX_ . 'attribute_shop`');
                Db::getInstance()->execute('TRUNCATE TABLE `' . _DB_PREFIX_ . 'product_attribute`');
                Db::getInstance()->execute('TRUNCATE TABLE `' . _DB_PREFIX_ . 'product_attribute_shop`');
                if (count(Db::getInstance()->executeS('SHOW TABLES LIKE \'' . _DB_PREFIX_ . 'product_attribute_lang\''))) { //check if table exist
                    Db::getInstance()->execute('TRUNCATE TABLE `' . _DB_PREFIX_ . 'product_attribute_lang`');
                }
                Db::getInstance()->execute('TRUNCATE TABLE `' . _DB_PREFIX_ . 'product_attribute_combination`');
                Db::getInstance()->execute('TRUNCATE TABLE `' . _DB_PREFIX_ . 'product_attribute_image`');
                Db::getInstance()->execute('DELETE FROM `' . _DB_PREFIX_ . 'stock_available` WHERE id_product_attribute != 0');
                Db::getInstance()->execute('DELETE FROM `' . _DB_PREFIX_ . 'warehouse_product_location` WHERE id_product_attribute != 0');

                break;
            case 'packs':
                Db::getInstance()->execute('TRUNCATE TABLE `' . _DB_PREFIX_ . 'pack`');

                break;
            case 'features':
                Db::getInstance()->execute('TRUNCATE TABLE `' . _DB_PREFIX_ . 'feature`');
                Db::getInstance()->execute('TRUNCATE TABLE `' . _DB_PREFIX_ . 'feature_lang`');
                Db::getInstance()->execute('TRUNCATE TABLE `' . _DB_PREFIX_ . 'feature_shop`');
                Db::getInstance()->execute('TRUNCATE TABLE `' . _DB_PREFIX_ . 'feature_value`');
                Db::getInstance()->execute('TRUNCATE TABLE `' . _DB_PREFIX_ . 'feature_value_lang`');

                break;
            case 'attributes':
                Db::getInstance()->execute('TRUNCATE TABLE `' . _DB_PREFIX_ . 'attribute_group`');
                Db::getInstance()->execute('TRUNCATE TABLE `' . _DB_PREFIX_ . 'attribute_group_lang`');
                Db::getInstance()->execute('TRUNCATE TABLE `' . _DB_PREFIX_ . 'attribute_group_shop`');
                Db::getInstance()->execute('TRUNCATE TABLE `' . _DB_PREFIX_ . 'attribute`');
                Db::getInstance()->execute('TRUNCATE TABLE `' . _DB_PREFIX_ . 'attribute_lang`');
                Db::getInstance()->execute('TRUNCATE TABLE `' . _DB_PREFIX_ . 'attribute_shop`');

                break;
            case 'customers':
                Db::getInstance()->execute('TRUNCATE TABLE `' . _DB_PREFIX_ . 'customer`');
                Db::getInstance()->execute('TRUNCATE TABLE `' . _DB_PREFIX_ . 'customer_group`');

                break;
            case 'addresses':
                Db::getInstance()->execute('TRUNCATE TABLE `' . _DB_PREFIX_ . 'address`');

                break;
            case 'brands':
                Db::getInstance()->execute('TRUNCATE TABLE `' . _DB_PREFIX_ . 'manufacturer`');
                Db::getInstance()->execute('TRUNCATE TABLE `' . _DB_PREFIX_ . 'manufacturer_lang`');
                Db::getInstance()->execute('TRUNCATE TABLE `' . _DB_PREFIX_ . 'manufacturer_shop`');
                foreach (scandir(_PS_MANU_IMG_DIR_, SCANDIR_SORT_NONE) as $d) {
                    if (preg_match('/^[0-9]+(\-(.*))?\.jpg$/', $d)) {
                        unlink(_PS_MANU_IMG_DIR_ . $d);
                    }
                }

                break;
            case 'suppliers':
                Db::getInstance()->execute('TRUNCATE TABLE `' . _DB_PREFIX_ . 'supplier`');
                Db::getInstance()->execute('TRUNCATE TABLE `' . _DB_PREFIX_ . 'supplier_lang`');
                Db::getInstance()->execute('TRUNCATE TABLE `' . _DB_PREFIX_ . 'supplier_shop`');
                foreach (scandir(_PS_SUPP_IMG_DIR_, SCANDIR_SORT_NONE) as $d) {
                    if (preg_match('/^[0-9]+(\-(.*))?\.jpg$/', $d)) {
                        unlink(_PS_SUPP_IMG_DIR_ . $d);
                    }
                }

                break;
            case 'aliases':
                Db::getInstance()->execute('TRUNCATE TABLE `' . _DB_PREFIX_ . 'alias`');

                break;
            case 'groups':
                Db::getInstance()->execute('TRUNCATE TABLE `' . _DB_PREFIX_ . 'group`');
                Db::getInstance()->execute('TRUNCATE TABLE `' . _DB_PREFIX_ . 'group_lang`');
                Db::getInstance()->execute('TRUNCATE TABLE `' . _DB_PREFIX_ . 'group_shop`');

                break;
            case 'stores':
                Db::getInstance()->execute('TRUNCATE TABLE `' . _DB_PREFIX_ . 'store`');
                Db::getInstance()->execute('TRUNCATE TABLE `' . _DB_PREFIX_ . 'store_shop`');
                if ($this->storeLangTableExists) {
                    Db::getInstance()->execute('TRUNCATE TABLE `' . _DB_PREFIX_ . 'store_lang`');
                }
                foreach (scandir(_PS_STORE_IMG_DIR_, SCANDIR_SORT_NONE) as $d) {
                    if (preg_match('/^[0-9]+(\-(.*))?\.jpg$/', $d)) {
                        unlink(_PS_STORE_IMG_DIR_ . $d);
                    }
                }

                break;
            case 'warehouses':
                Db::getInstance()->execute('TRUNCATE TABLE `' . _DB_PREFIX_ . 'warehouse`');
                Db::getInstance()->execute('TRUNCATE TABLE `' . _DB_PREFIX_ . 'warehouse_shop`');
                Db::getInstance()->execute('TRUNCATE TABLE `' . _DB_PREFIX_ . 'warehouse_carrier`');

                break;
            case 'carriers':
                Db::getInstance()->execute('TRUNCATE TABLE `' . _DB_PREFIX_ . 'carrier`');
                Db::getInstance()->execute('TRUNCATE TABLE `' . _DB_PREFIX_ . 'carrier_shop`');
                Db::getInstance()->execute('TRUNCATE TABLE `' . _DB_PREFIX_ . 'carrier_lang`');
                Db::getInstance()->execute('TRUNCATE TABLE `' . _DB_PREFIX_ . 'carrier_group`');
                Db::getInstance()->execute('TRUNCATE TABLE `' . _DB_PREFIX_ . 'carrier_zone`');
                foreach (scandir(_PS_SHIP_IMG_DIR_, SCANDIR_SORT_NONE) as $d) {
                    if (preg_match('/^[0-9]+(\-(.*))?\.jpg$/', $d)) {
                        unlink(_PS_SHIP_IMG_DIR_ . $d);
                    }
                }

                break;
            case 'discounts':
                Db::getInstance()->execute('TRUNCATE TABLE `' . _DB_PREFIX_ . 'specific_price`');
                Db::getInstance()->execute('TRUNCATE TABLE `' . _DB_PREFIX_ . 'specific_price_rule`');
                Db::getInstance()->execute('TRUNCATE TABLE `' . _DB_PREFIX_ . 'specific_price_rule_condition`');
                Db::getInstance()->execute('TRUNCATE TABLE `' . _DB_PREFIX_ . 'specific_price_rule_condition_group`');

                break;
        }
        Image::clearTmpDir();

        return true;
    }

    public function getConfigs()
    {
        $confs = DB::getInstance()->executeS('SELECT 
                                                    `id_ipcatalogimport`, 
                                                    `name`,
                                                    `configuration`,
                                                    "' . $this->module->l('Delete', 'EIImport') . '" `title`
                                                FROM `' . _DB_PREFIX_ . 'ipcatalogimport`
                                                ORDER BY `id_ipcatalogimport` DESC;');
        foreach ($confs as &$conf) {
            $conf['configuration'] = str_replace("'", '&apos;', $conf['configuration']);
        }
        return $confs;
    }

    protected function openCsvFile($offset = false)
    {
        $handle = false;
        if (is_file($this->file) && is_readable($this->file)) {
            //            if (!mb_check_encoding(file_get_contents($this->file), 'UTF-8')) {
            //                $this->convert = true;
            //            }
            $handle = fopen($this->file, 'rb');
        }

        if (!$handle) {
            $this->errors[] = Tools::displayError('Cannot read the file');
        }

        self::rewindBomAware($handle);

        $toSkip = (int) Tools::getValue('skip');
        if ($offset && $offset > 0) {
            $toSkip += $offset;
        }

        for ($i = 0; $i < $toSkip; $i++) {
            $line = fgetcsv($handle, MAX_LINE_SIZE, $this->separator, $this->enclosure);
            if ($line === false) {
                return false; // reached end of file
            }
        }
        return $handle;
    }

    protected function closeCsvFile($handle)
    {
        fclose($handle);
    }

    private function copyDir($src, $dst)
    {
        // open the source directory
        $dir = opendir($src);

        // Make the destination directory if not exist
        @mkdir($dst);

        // Loop through the files in source directory
        while ($file = readdir($dir)) {
            if (($file != '.') && ($file != '..')) {
                if (is_dir($src . '/' . $file)) {
                    // Recursively calling custom copy function
                    // for sub directory
                    $this->copyDir($src . '/' . $file, $dst . '/' . $file);
                } else {
                    copy($src . '/' . $file, $dst . '/' . $file);
                }
            }
        }

        closedir($dir);
    }

    public function utf8EncodeArray($array)
    {
        return (is_array($array) ? array_map('utf8_encode', $array) : utf8_encode($array));
    }

    protected static function rewindBomAware($handle)
    {
        // A rewind wrapper that skips BOM signature wrongly
        if (!is_resource($handle)) {
            return false;
        }
        rewind($handle);
        if (fread($handle, 3) != "\xEF\xBB\xBF") {
            rewind($handle);
        }
    }

    public static function setLocale()
    {
        $iso_lang = Language::getIsoById(Tools::getValue('language'));
        setlocale(LC_COLLATE, Tools::strtolower($iso_lang) . '_' . Tools::strtoupper($iso_lang) . '.UTF-8');
        setlocale(LC_CTYPE, Tools::strtolower($iso_lang) . '_' . Tools::strtoupper($iso_lang) . '.UTF-8');
    }

    protected function addProductWarning($product_name, $product_id = null, $message = '')
    {
        $this->warnings[] = Tools::htmlentitiesUTF8($product_name . (isset($product_id) ? ' (ID ' . $product_id . ')' : '') . ' ' . $message);
    }

}
