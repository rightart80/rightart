<?php
/**
 *
 * NOTICE OF LICENSE
 *
 *  @author    SmartPresta <tehran.alishov@gmail.com>
 *  @copyright 2023 SmartPresta
 *  @license   http://opensource.org/licenses/afl-3.0.php Commercial License!
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;

class EIDiscountsExport
{

    public $module;
    protected $path;
    private $langId;
    private $sql;
    private $selectedColumns;
    private $exportDir;
    private $auto;

    public function __construct($module)
    {
        $this->module = $module;
        $this->context = Context::getContext();
        $this->exportDir = dirname(__FILE__) . '/../../export/';
    }

    private function getDiscounts($getTotal = false)
    {
        $newColumns = [
            'discount.reduction_tax' => !empty(Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS("SHOW COLUMNS FROM `" . _DB_PREFIX_ . "specific_price` LIKE 'reduction_tax'"))
        ];

        if ($this->auto) {
            $discountsFromDate = $this->inputs['from_date'];
            $fromFromDate = pSQL($this->inputs['from_from_date']);
            $fromToDate = pSQL($this->inputs['from_to_date']);
            $discountsToDate = $this->inputs['to_date'];
            $toFromDate = pSQL($this->inputs['to_from_date']);
            $toToDate = pSQL($this->inputs['to_to_date']);
            $discountType = pSQL($this->inputs['discount_type']);

            $this->langId = $langId = (int) $this->inputs['language'];
            $shopId = (int) $this->inputs['shop'] ?: (int) Configuration::get('PS_SHOP_DEFAULT');
            $fromDate = pSQL($this->inputs['from_date']);
            $toDate = pSQL($this->inputs['to_date']);
            $this->sortWay = (int) $this->inputs['sort_way'] === 0 ? ' DESC' : ' ASC';
            $this->sort = pSQL($this->inputs['sort']);
            $this->multivalueSeparator = pSQL($this->inputs['multivalue_separator']) ?: ',';
        } else {
            $discountsFromDate = Tools::getValue('from_date');
            $fromFromDate = pSQL(Tools::getValue('from_from_date'));
            $fromToDate = pSQL(Tools::getValue('from_to_date'));
            $discountsToDate = Tools::getValue('to_date');
            $toFromDate = pSQL(Tools::getValue('to_from_date'));
            $toToDate = pSQL(Tools::getValue('to_to_date'));
            $discountType = pSQL(Tools::getValue('discount_type'));

            $this->langId = $langId = (int) Tools::getValue('language');
            $shopId = (int) Tools::getValue('shop') ?: (int) Configuration::get('PS_SHOP_DEFAULT');
            $this->sortWay = (int) Tools::getValue('sort_way') === 0 ? ' DESC' : ' ASC';
            $this->sort = pSQL(Tools::getValue('sort'));
            $this->multivalueSeparator = pSQL(Tools::getValue('multivalue_separator')) ?: ',';
        }

        $this->sql = '
            SELECT ';
        foreach ($this->selectedColumns as $k => $col) {
            if (array_key_exists($k, $newColumns) && !$newColumns[$k]) {
                $this->sql .= "
                    '' `$col`, ";
            } else {
                $this->sql .= "
                    $k `$col`, ";
            }
        }

        $this->sql = rtrim($this->sql, ', ');

        $sps = $sprs = '';
        if ($this->auto) {
            $ids_sp = [];
            $ids_spr = [];
            foreach ($this->datatables['discounts']['data'] as $val) {
                $val = explode('_', $val);
                if ($val[0]) {
                    $ids_sp[] = $val[0];
                }
                if ($val[1]) {
                    $ids_spr[] = $val[1];
                }
            }
            $sps = pSQL(implode(',', $ids_sp));
            $sprs = pSQL(implode(',', $ids_spr));
            $discounts_type = $this->datatables['discounts']['type'];
        } else {
            $ids_sp = [];
            $ids_spr = [];
            foreach (explode(',', Tools::getValue('discounts_data')) as $val) {
                $val = explode('_', $val);
                if ($val[0]) {
                    $ids_sp[] = $val[0];
                }
                if ($val[1]) {
                    $ids_spr[] = $val[1];
                }
            }
            $sps = pSQL(implode(',', $ids_sp));
            $sprs = pSQL(implode(',', $ids_spr));
            $discounts_type = Tools::getValue('discounts_type');
        }

        // Filter By Discount
        if ($discounts_type === 'unselected') {
            if ($sps) {
                $sps = ' AND sp.id_specific_price NOT IN (' . $sps . ')';
            }
            if ($sprs) {
                $sprs = ' AND spr.id_specific_price_rule NOT IN (' . $sprs . ')';
            }
        } else {
            if ($sps) {
                $sps = ' AND sp.id_specific_price IN (' . $sps . ')';
            }
            if ($sprs) {
                $sprs = ' AND spr.id_specific_price_rule IN (' . $sprs . ')';
            }
        }


        $this->sql .= '
        FROM (
            SELECT 
                sp.id_specific_price,
                sp.id_specific_price_rule id_specific_price_rule,
                IFNULL(spr.`name`, "") spr_name,
                CAST(sprcg.`conds` AS CHAR) conds,
                sp.id_product,
                p.`reference` prod_reference,
                pl.`name` prod_name,
                attributes.`id_product_attribute` id_product_attribute,
                attributes.`reference` comb_reference,
                attributes.`values` comb_values,
                sp.reduction reduction,
                sp.reduction_type reduction_type,
                ' . ($newColumns['discount.reduction_tax'] ? 'sp.reduction_tax reduction_tax,' : '') . '
                sp.`from` `from`,
                sp.`to` `to`,
                sp.price price,
                sp.from_quantity from_quantity,
                sp.id_customer id_customer,
                IFNULL(firstname, "") firstname,
                IFNULL(lastname, "") lastname,
                IFNULL(email, "") email,
                sp.id_group id_group,
                IFNULL(gl.`name`, "") group_name,
                sp.id_country id_country,
                IFNULL(cl.`name`, "") country_name,
                sp.id_currency id_currency,
                IFNULL(cu.iso_code, "") currency_iso_code,
                0 is_rule
            FROM
                ' . _DB_PREFIX_ . 'specific_price sp
                    LEFT JOIN
                ' . _DB_PREFIX_ . 'product p ON sp.id_product = p.id_product
                    LEFT JOIN
                ' . _DB_PREFIX_ . 'product_lang pl ON p.id_product = pl.id_product
                    AND pl.id_lang = ' . $langId . '
                    AND pl.id_shop = ' . $shopId . '
                    LEFT JOIN (
                        SELECT
                            pa.id_product_attribute,
                            pa.reference,
                            GROUP_CONCAT(CONCAT_WS(":", IFNULL(agl.`name`, ""), al.`name`) SEPARATOR "' . $this->multivalueSeparator . '") name_values,
                            GROUP_CONCAT(CONCAT_WS(":", agl.`name`, agl.public_name, ag.group_type) ORDER BY agl.`name`, al.`name` SEPARATOR "' . $this->multivalueSeparator . '") `groups`,
                            GROUP_CONCAT(CONCAT_WS(":", agl.`name`, al.`name`) ORDER BY agl.`name`, al.`name` SEPARATOR "' . $this->multivalueSeparator . '") `values`
                        FROM ' . _DB_PREFIX_ . 'product_attribute pa
                        JOIN ' . _DB_PREFIX_ . 'product_attribute_combination pac ON pa.id_product_attribute = pac.id_product_attribute
                        JOIN ' . _DB_PREFIX_ . 'attribute a ON pac.id_attribute = a.id_attribute
                        JOIN ' . _DB_PREFIX_ . 'attribute_shop ash ON a.id_attribute = ash.id_attribute AND ash.id_shop = ' . $shopId . '
                        JOIN ' . _DB_PREFIX_ . 'attribute_lang al ON a.id_attribute = al.id_attribute AND al.id_lang = ' . $langId . '
                        JOIN ' . _DB_PREFIX_ . 'attribute_group ag ON a.id_attribute_group = ag.id_attribute_group
                        JOIN ' . _DB_PREFIX_ . 'attribute_group_shop agsh ON a.id_attribute_group = agsh.id_attribute_group AND agsh.id_shop = ' . $shopId . '
                        JOIN ' . _DB_PREFIX_ . 'attribute_group_lang agl ON a.id_attribute_group = agl.id_attribute_group AND agl.id_lang = ' . $langId . '
                        GROUP BY pa.id_product_attribute
                    ) attributes ON sp.id_product_attribute = attributes.id_product_attribute
                    LEFT JOIN
                ' . _DB_PREFIX_ . 'customer c ON sp.id_country = c.id_customer
                    LEFT JOIN
                ' . _DB_PREFIX_ . 'group_lang gl ON sp.id_group = gl.id_group
                    AND gl.id_lang = ' . $langId . '
                    LEFT JOIN
                ' . _DB_PREFIX_ . 'currency cu ON sp.id_currency = cu.id_currency
                    LEFT JOIN
                ' . _DB_PREFIX_ . 'country_lang cl ON sp.id_country = cl.id_country
                    AND cl.id_lang = ' . $langId . '
                    LEFT JOIN
                ' . _DB_PREFIX_ . 'specific_price_rule spr ON sp.id_specific_price_rule = spr.id_specific_price_rule
                    LEFT JOIN
                (SELECT sprcg.id_specific_price_rule, GROUP_CONCAT(cond SEPARATOR " | ") conds
                    FROM ' . _DB_PREFIX_ . 'specific_price_rule_condition_group sprcg
                    LEFT JOIN (
                    SELECT id_specific_price_rule_condition_group, GROUP_CONCAT(CONCAT(sprc.`type`, ":", CASE
                        WHEN sprc.`type` = "category" THEN cl.`name`
                        WHEN sprc.`type` = "manufacturer" THEN m.`name`
                        WHEN sprc.`type` = "supplier" THEN s.`name`
                        WHEN sprc.`type` = "attribute" THEN CONCAT(agl.`name`, " ^ ", agl.public_name, " ^ ", ag.group_type, " ^ ", al.`name`, " ^ ", a.color)
                        WHEN sprc.`type` = "feature" THEN CONCAT(fl.`name`, " ^ ", fvl.`value`, " ^ ", fv.`custom`)
                        ELSE sprc.`value`
                    END) SEPARATOR " & ") cond
                    FROM ' . _DB_PREFIX_ . 'specific_price_rule_condition sprc
                    LEFT JOIN ' . _DB_PREFIX_ . 'category_lang cl ON sprc.`type` = "category" AND sprc.`value` = cl.id_category AND cl.id_shop = ' . $shopId . ' AND cl.id_lang = ' . $langId . '
                    LEFT JOIN ' . _DB_PREFIX_ . 'manufacturer m ON sprc.`type` = "manufacturer" AND sprc.`value` = m.id_manufacturer
                    LEFT JOIN ' . _DB_PREFIX_ . 'supplier s ON sprc.`type` = "supplier" AND sprc.`value` = s.id_supplier
                    LEFT JOIN ' . _DB_PREFIX_ . 'attribute a ON sprc.`type` = "attribute" AND sprc.`value` = a.id_attribute
                    LEFT JOIN ' . _DB_PREFIX_ . 'attribute_lang al ON a.id_attribute = al.id_attribute AND al.id_lang = ' . $langId . '
                    LEFT JOIN ' . _DB_PREFIX_ . 'attribute_group ag ON a.id_attribute_group = ag.id_attribute_group
                    LEFT JOIN ' . _DB_PREFIX_ . 'attribute_group_lang agl ON ag.id_attribute_group = agl.id_attribute_group AND agl.id_lang = ' . $langId . '
                    LEFT JOIN ' . _DB_PREFIX_ . 'feature_value fv ON sprc.`type` = "feature" AND sprc.`value` = fv.id_feature_value
                    LEFT JOIN ' . _DB_PREFIX_ . 'feature_value_lang fvl ON fv.id_feature_value = fvl.id_feature_value AND fvl.id_lang = ' . $langId . '
                    LEFT JOIN ' . _DB_PREFIX_ . 'feature f ON fv.id_feature = f.id_feature
                    LEFT JOIN ' . _DB_PREFIX_ . 'feature_lang fl ON f.id_feature = fl.id_feature AND fl.id_lang = ' . $langId . '
                    GROUP BY id_specific_price_rule_condition_group) sprc ON sprc.id_specific_price_rule_condition_group = sprcg.id_specific_price_rule_condition_group
                    GROUP BY sprcg.id_specific_price_rule) sprcg ON spr.id_specific_price_rule = sprcg.id_specific_price_rule
                WHERE 1 ' . $sps . '

                UNION

            SELECT
                NULL `id_specific_price`,
                spr.`id_specific_price_rule`,
                spr.`name` spr_name,
                CAST(sprcg.`conds` AS CHAR) conds,
                NULL `id_product`,
                NULL `prod_reference`,
                NULL `prod_name`,
                NULL `id_product_attribute`,
                NULL `comb_reference`,
                NULL `comb_values`,
                spr.`reduction`,
                spr.`reduction_type`,
                ' . ($newColumns['discount.reduction_tax'] ? 'spr.`reduction_tax`,' : '') . '
                spr.`from`,
                spr.`to`,
                spr.`price`,
                spr.from_quantity,
                NULL id_customer,
                NULL firstname,
                NULL lastname,
                NULL email,
                spr.id_group,
                gl.`name` group_name,
                spr.id_country,
                cl.`name`,
                spr.id_currency,
                c.iso_code,
                1 is_rule
            FROM ' . _DB_PREFIX_ . 'specific_price_rule spr 
            LEFT JOIN
            (SELECT sprcg.id_specific_price_rule, GROUP_CONCAT(cond SEPARATOR " | ") conds
                FROM ' . _DB_PREFIX_ . 'specific_price_rule_condition_group sprcg
                LEFT JOIN (
                SELECT id_specific_price_rule_condition_group, GROUP_CONCAT(CONCAT(sprc.`type`, ":", CASE
                    WHEN sprc.`type` = "category" THEN cl.`name`
                    WHEN sprc.`type` = "manufacturer" THEN m.`name`
                    WHEN sprc.`type` = "supplier" THEN s.`name`
                    WHEN sprc.`type` = "attribute" THEN CONCAT(agl.`name`, " ^ ", agl.public_name, " ^ ", ag.group_type, " ^ ", al.`name`, " ^ ", a.color)
                    WHEN sprc.`type` = "feature" THEN CONCAT(fl.`name`, " ^ ", fvl.`value`, " ^ ", fv.`custom`)
                    ELSE sprc.`value`
                END) SEPARATOR " & ") cond
                FROM ' . _DB_PREFIX_ . 'specific_price_rule_condition sprc
                LEFT JOIN ' . _DB_PREFIX_ . 'category_lang cl ON sprc.`type` = "category" AND sprc.`value` = cl.id_category AND cl.id_shop = ' . $shopId . ' AND cl.id_lang = ' . $langId . '
                LEFT JOIN ' . _DB_PREFIX_ . 'manufacturer m ON sprc.`type` = "manufacturer" AND sprc.`value` = m.id_manufacturer
                LEFT JOIN ' . _DB_PREFIX_ . 'supplier s ON sprc.`type` = "supplier" AND sprc.`value` = s.id_supplier
                LEFT JOIN ' . _DB_PREFIX_ . 'attribute a ON sprc.`type` = "attribute" AND sprc.`value` = a.id_attribute
                LEFT JOIN ' . _DB_PREFIX_ . 'attribute_lang al ON a.id_attribute = al.id_attribute AND al.id_lang = ' . $langId . '
                LEFT JOIN ' . _DB_PREFIX_ . 'attribute_group ag ON a.id_attribute_group = ag.id_attribute_group
                LEFT JOIN ' . _DB_PREFIX_ . 'attribute_group_lang agl ON ag.id_attribute_group = agl.id_attribute_group AND agl.id_lang = ' . $langId . '
                LEFT JOIN ' . _DB_PREFIX_ . 'feature_value fv ON sprc.`type` = "feature" AND sprc.`value` = fv.id_feature_value
                LEFT JOIN ' . _DB_PREFIX_ . 'feature_value_lang fvl ON fv.id_feature_value = fvl.id_feature_value AND fvl.id_lang = ' . $langId . '
                LEFT JOIN ' . _DB_PREFIX_ . 'feature f ON fv.id_feature = f.id_feature
                LEFT JOIN ' . _DB_PREFIX_ . 'feature_lang fl ON f.id_feature = fl.id_feature AND fl.id_lang = ' . $langId . '
                GROUP BY id_specific_price_rule_condition_group) sprc ON sprc.id_specific_price_rule_condition_group = sprcg.id_specific_price_rule_condition_group
                GROUP BY sprcg.id_specific_price_rule) sprcg ON spr.id_specific_price_rule = sprcg.id_specific_price_rule
            LEFT JOIN
                ' . _DB_PREFIX_ . 'group_lang gl ON spr.id_group = gl.id_group
                    AND gl.id_lang = ' . $langId . '
            LEFT JOIN
                ' . _DB_PREFIX_ . 'currency c ON spr.id_currency = c.id_currency
            LEFT JOIN
                ' . _DB_PREFIX_ . 'country_lang cl ON spr.id_country = cl.id_country
                    AND cl.id_lang = ' . $langId . '
            WHERE spr.id_specific_price_rule NOT IN (
            SELECT id_specific_price_rule
            FROM ' . _DB_PREFIX_ . 'specific_price) ' . $sprs . '
        ) discount WHERE 1 
            ';

        // Filter By Date
        $dateFrom = '`from`';
        if ($discountsFromDate === 'this_month') {
            $this->sql .= " 
                AND discount.$dateFrom >= '" . date("Y-m-d", strtotime("first day of this month")) . "'";
            $this->sql .= " 
                AND discount.$dateFrom < '" . date("Y-m-d", strtotime("first day of next month")) . "'";
        } elseif ($discountsFromDate === 'last_month') {
            $this->sql .= " 
                AND discount.$dateFrom >= '" . date("Y-m-d", strtotime("first day of previous month")) . "'";
            $this->sql .= " 
                AND discount.$dateFrom < '" . date("Y-m-d", strtotime("first day of this month")) . "'";
        } elseif ($discountsFromDate === 'this_week') {
            $start = date('N') == 1 ? date('Y-m-d') : date('Y-m-d', strtotime('last monday'));
            $end = date('Y-m-d', strtotime('this sunday'));
            $this->sql .= " 
                AND discount.$dateFrom >= '" . $start . "'";
            $this->sql .= " 
                AND discount.$dateFrom <= '" . $end . "'";
        } elseif ($discountsFromDate === 'last_week') {
            $start = date('N') == 1 ? date('Y-m-d', strtotime('last monday')) : date('Y-m-d', strtotime('last week monday'));
            $end = date('Y-m-d', strtotime('last sunday'));
            $this->sql .= " 
                AND discount.$dateFrom >= '" . $start . "'";
            $this->sql .= " 
                AND discount.$dateFrom <= '" . $end . "'";
        } elseif ($discountsFromDate === 'today') {
            $this->sql .= " 
                AND discount.$dateFrom >= '" . date("Y-m-d", strtotime("today")) . "'";
            $this->sql .= " 
                AND discount.$dateFrom < '" . date("Y-m-d", strtotime("tomorrow")) . "'";
        } elseif ($discountsFromDate === 'last_24_hours') {
            $this->sql .= " 
                AND discount.$dateFrom >= '" . date('Y-m-d H:i:s', strtotime("-1 day")) . "'";
            $this->sql .= " 
                AND discount.$dateFrom < '" . date('Y-m-d H:i:s') . "'";
        } elseif ($discountsFromDate === 'yesterday') {
            $this->sql .= " 
                AND discount.$dateFrom >= '" . date("Y-m-d", strtotime("yesterday")) . "'";
            $this->sql .= " 
                AND discount.$dateFrom < '" . date("Y-m-d", strtotime("today")) . "'";
        } elseif ($discountsFromDate === 'select_date') {
            if ($fromDate) {
                $this->sql .= " 
                    AND discount.$dateFrom >= '" . $fromFromDate . "'";
            }
            if ($toDate) {
                $this->sql .= " 
                    AND discount.$dateFrom < '" . $fromToDate . "'";
            }
        }

        $dateTo = '`to`';
        if ($discountsToDate === 'this_month') {
            $this->sql .= " 
                AND discount.$dateTo >= '" . date("Y-m-d", strtotime("first day of this month")) . "'";
            $this->sql .= " 
                AND discount.$dateTo < '" . date("Y-m-d", strtotime("first day of next month")) . "'";
        } elseif ($discountsToDate === 'last_month') {
            $this->sql .= " 
                AND discount.$dateTo >= '" . date("Y-m-d", strtotime("first day of previous month")) . "'";
            $this->sql .= " 
                AND discount.$dateTo < '" . date("Y-m-d", strtotime("first day of this month")) . "'";
        } elseif ($discountsToDate === 'this_week') {
            $start = date('N') == 1 ? date('Y-m-d') : date('Y-m-d', strtotime('last monday'));
            $end = date('Y-m-d', strtotime('this sunday'));
            $this->sql .= " 
                AND discount.$dateTo >= '" . $start . "'";
            $this->sql .= " 
                AND discount.$dateTo <= '" . $end . "'";
        } elseif ($discountsToDate === 'last_week') {
            $start = date('N') == 1 ? date('Y-m-d', strtotime('last monday')) : date('Y-m-d', strtotime('last week monday'));
            $end = date('Y-m-d', strtotime('last sunday'));
            $this->sql .= " 
                AND discount.$dateTo >= '" . $start . "'";
            $this->sql .= " 
                AND discount.$dateTo <= '" . $end . "'";
        } elseif ($discountsToDate === 'today') {
            $this->sql .= " 
                AND discount.$dateTo >= '" . date("Y-m-d", strtotime("today")) . "'";
            $this->sql .= " 
                AND discount.$dateTo < '" . date("Y-m-d", strtotime("tomorrow")) . "'";
        } elseif ($discountsToDate === 'last_24_hours') {
            $this->sql .= " 
                AND discount.$dateTo >= '" . date('Y-m-d H:i:s', strtotime("-1 day")) . "'";
            $this->sql .= " 
                AND discount.$dateTo < '" . date('Y-m-d H:i:s') . "'";
        } elseif ($discountsToDate === 'yesterday') {
            $this->sql .= " 
                AND discount.$dateTo >= '" . date("Y-m-d", strtotime("yesterday")) . "'";
            $this->sql .= " 
                AND discount.$dateTo < '" . date("Y-m-d", strtotime("today")) . "'";
        } elseif ($discountsToDate === 'select_date') {
            if ($fromDate) {
                $this->sql .= " 
                    AND discount.$dateTo >= '" . $toFromDate . "'";
            }
            if ($toDate) {
                $this->sql .= " 
                    AND discount.$dateTo < '" . $toToDate . "'";
            }
        }
        
        // Filter By Discount Type
        if ($discountType === 'specific') {
            $this->sql .= " 
                    AND discount.id_specific_price IS NOT NULL";
        } elseif ($discountType === 'catalog') {
            $this->sql .= " 
                    AND discount.id_specific_price_rule <> 0";
        }

        // Sort By ...
        $this->sql .= ' ORDER BY ' . $this->sort . $this->sortWay;
        if ($this->sort !== 'discount.id_specific_price') {
            $this->sql .= ', discount.id_specific_price ASC';
        }

        if (!$getTotal) {
            $this->sql .= ' LIMIT ' . $this->offset . ', ' . $this->limit;
        }

//        d($this->sql);
        return Db::getInstance()->executeS($this->sql);
    }

    public function run($auto = null)
    {
        $this->auto = $auto;
        $this->encl = '"';

        if ($auto) {
            $this->inputs = $auto['config']['inputs'];

            $this->fileType = $this->inputs['as'];
            $docName = $this->inputs['doc_name'] ?: $this->module->l('Discounts', 'EIDiscountsExport');

            $this->selectedColumns = $this->module->convertToUsableColumns($auto['config']['columns']);

            if ($this->fileType === 'csv') {
                $this->dlm = $this->inputs['csv_separator'];
//                $this->encl = $this->inputs['csv_enclosure'];

                if ($this->dlm === 't') {
                    $this->dlm = "\t";
                }

//                if ($this->encl === 'none') {
//                    $this->encl = '';
//                } elseif ($this->encl === 'quot') {
//                    $this->encl = '"';
//                }
            }

            $this->datatables = json_decode($auto['datatables'], true);

            $fileId = $auto['fileId'];
            $this->file = $this->exportDir . $fileId . '.' . $this->fileType;

            $this->offset = $auto['offset'];
            $this->limit = $auto['limit'];

            $this->discounts = $this->getDiscounts();

            if (!$this->discounts && Configuration::getGlobalValue('IPE_SCHDL_DNSEM')) {
                return;
            }
        } else {
            $this->fileType = Tools::getValue('as');
            $docName = Tools::getValue('doc_name') ?: $this->module->l('Discounts', 'EIDiscountsExport');

            $this->selectedColumns = json_decode(Tools::getValue('selectedColumns'), true);

            if ($this->fileType === 'csv') {
                $this->dlm = Tools::getValue('csv_separator');
//                $this->encl = Tools::getValue('csv_enclosure');

                if ($this->dlm === 't') {
                    $this->dlm = "\t";
                }

//                if ($this->encl === 'none') {
//                    $this->encl = '';
//                } elseif ($this->encl === 'quot') {
//                    $this->encl = '"';
//                }
            }

            $fileId = Tools::getValue('fileId') ?: mt_rand() . uniqid();

            $this->file = $this->exportDir . $fileId . '.' . $this->fileType;

            $this->offset = (int) Tools::getValue('offset');
            $this->limit = (int) Tools::getValue('limit');
            $this->moreStep = (int) Tools::getValue('moreStep');

            $this->discounts = $this->getDiscounts();
        }

        $results = ['fileId' => $fileId];
        $this->exportByGroups($results);

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

        if ($results['isFinished']) {
            $results['type'] = $this->fileType;
            $results['name'] = $docName;
        }
        die(json_encode($results));
    }

    public function exportByGroups(&$results)
    {
        $doneCount = 0;

        if ($this->fileType === 'csv') {
            $this->writeToCsv();
        } elseif ($this->fileType === 'xlsx') {
            $this->writeToExcel();
        }
        $doneCount += $this->discounts ? count($this->discounts) : 0;

        $results['isFinished'] = $doneCount < $this->limit;

        $results['doneCount'] = $this->offset + $doneCount;
        if ($this->offset === 0) {
            $total = $this->getDiscounts(true);
            $results['totalCount'] = $total ? count($total) : 0;
        }
        if (!$results['isFinished']) {
            // Since we'll have to POST this array from ajax for the next call, we should care about it size.
            $results['nextPostSize'] = 1024 * 64; // 64KB more for the rest of the POST query.
            $results['postSizeLimit'] = Tools::getMaxUploadSize();
        }
    }

    protected function openCsvFile()
    {
        $handle = false;
        if (file_exists($this->file)) {
            $handle = fopen($this->file, 'a');
        } else {
            $handle = fopen($this->file, 'w');
            fwrite($handle, "\xEF\xBB\xBF"); // NEW LINE
            if ($handle) {
                if ($this->discounts) {
                    fputcsv($handle, array_keys($this->discounts[0]), $this->dlm, $this->encl);
                } else {
                    fputcsv($handle, [$this->module->l('No Data', 'EIDiscountsExport')], $this->dlm, $this->encl);
                }
            }
        }

        if (!$handle) {
            $this->errors[] = Tools::displayError('Cannot read the file');
        }

        return $handle;
    }

    protected function closeCsvFile($handle)
    {
        fclose($handle);
    }

    protected function writeToCsv()
    {
        $handle = $this->openCsvFile();
        foreach ($this->discounts as $value) {
            fputcsv($handle, $value, $this->dlm, $this->encl);
        }
        $this->closeCsvFile($handle);
    }

    private function writeToExcel()
    {
        require_once dirname(__FILE__) . '/../../vendor/autoload.php';

        if (file_exists($this->file)) {
            $spreadsheet = IOFactory::load($this->file);
            $sheet = $spreadsheet->getActiveSheet();
            $excelColumns = EIHelper::createColumnsArray(count($this->discounts[0]));
        } else {
            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();

            // Set document properties
            $spreadsheet->getProperties()->setCreator('Tehran Alishov')
                ->setLastModifiedBy('Tehran Alishov')
                ->setTitle('Office 2007 XLSX Discounts Document')
                ->setSubject('Office 2007 XLSX Discounts Document')
                ->setDescription('Discounts document for Office 2007 XLSX, generated using PHP classes.')
                ->setKeywords('office 2007 openxml php')
                ->setCategory('Discounts result file');
            $spreadsheet->setActiveSheetIndex(0);

            if (empty($this->discounts)) {
                $sheet->setCellValue('A1', $this->module->l('No Data', 'EIDiscountsExport'));
            } else {
                $excelColumns = EIHelper::createColumnsArray(count($this->discounts[0]));
                $sheet->getDefaultColumnDimension()->setWidth(21);
                if (isset($this->selectedColumns['discount_logo'])) {
                    $sheet->getDefaultRowDimension()->setRowHeight(42);
                } else {
                    $sheet->getDefaultRowDimension()->setRowHeight(30);
                }

                $spreadsheet->getDefaultStyle()->getAlignment()
                    ->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER)
                    ->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);

                $sheet->getStyle('A1:' . end($excelColumns) . (count($this->discounts)))
                    ->getAlignment()->setWrapText(true);

                $sheet->getStyle('A1:' . end($excelColumns) . '1')
                    ->getFont()->setBold(true);
                $sheet->getStyle('A1:' . end($excelColumns) . '1')
                    ->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                    ->getStartColor()->setARGB('FFDCF0FF');
                $sheet->getStyle('A1:' . end($excelColumns) . '1')->getBorders()
                    ->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);

                // Rename worksheet
                $sheet->setTitle($this->module->l('Discounts', 'EIDiscountsExport'));

                $headers = array_keys($this->discounts[0]);
                foreach ($headers as $key => $header) {
                    $sheet->setCellValue($excelColumns[$key] . '1', $header);
                }
            }
        }

        foreach ($this->discounts as $key => $value) {
            $i = 0;
            foreach ($value as $val) {
                $sheet->setCellValue($excelColumns[$i++] . ($this->offset + $key + 2), $val);
            }
        }

        $sheet->setSelectedCell('A1');

        // Write to file
        $writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
        $writer->save($this->file);
    }
}
