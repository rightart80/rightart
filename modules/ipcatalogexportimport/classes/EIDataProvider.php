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

class EIDataProvider
{

    public static $getAll = false;
    public static $setInitial = false;

    public static function getAllInitialData($module, $type = null)
    {
        $methods = [
            'addresses' => 'getAddresses',
            'attributes' => 'getAttributes',
            'carriers' => 'getCarriers',
            'carriers2' => 'getCarriers',
            'customers' => 'getCustomers',
            'discounts' => 'getDiscounts',
            'features' => 'getFeatures',
            'featuresForFeatures' => 'getFeaturesForFeatures',
            'featureValues' => 'getFeatureValues',
            'attributeGroups' => 'getAttributeGroups',
            'attributeValues' => 'getAttributeValues',
            'groups' => 'getGroups',
            'groups2' => 'getGroups',
            'manufacturers' => 'getManufacturers',
            'suppliers' => 'getSuppliers',
            'suppliers2' => 'getSuppliers',
            'brands' => 'getManufacturers',
            'products' => 'getProducts',
            'stores' => 'getStores',
            'aliases' => 'getAliases',
            'warehouses' => 'getWarehouses',
            'packs' => 'getPacks',
            'combinations' => 'getCombinations',
        ];
        self::$getAll = true;
        $array = [];
        if ($type === 'ajax') {
            $id = (int) Tools::getValue('id');
            $datatables = DB::getInstance()->getValue('SELECT `datatables` FROM `' . _DB_PREFIX_ . 'ipcatalogexport`
                                                WHERE id_ipcatalogexport = ' . $id);
            $datatables = json_decode($datatables, true);

            foreach ($datatables as $entity => $value) {
                $_GET['extra_search_type'] = $value['showWhich'];
                $_GET['extra_search_params'] = ['type' => $value['type'], 'data' => $value['data']];
                $_GET['columns'] = $value['columns'];
                $_GET['search'] = ['value' => $value['search']];
                $_GET['order'] = [];
                foreach ($value['order'] as $order) {
                    $_GET['order'][] = ['column' => $order[0], 'dir' => $order[1]];
                }
                $_GET['start'] = $value['pageInfo']['start'];
                $_GET['length'] = $value['pageInfo']['length'];
                $array[$entity] = self::{$methods[$entity]}() + [
                    'type' => $value['type'],
                    'checkboxes' => $value['data'],
                    'search' => $value['search'],
                    'order' => $value['order'],
                    'pageInfo' => $value['pageInfo'],
                ];
            }
            die(json_encode(['message' => 'success', 'entities' => $array]));
        } else {
            self::$setInitial = true;
            foreach ($methods as $key => $method) {
                $array[$key] = self::$method();
            }
            $array['emails'] = self::getScheduleEmails($module);
            $array['ftps'] = self::getScheduleFTPs($module);
            return json_encode($array);
        }
    }

    public static function getDiscounts()
    {
        $context = Context::getContext();
        $langId = $context->language->id;
        $shopId = $context->shop->id;

        $sql = 'SELECT SQL_CALC_FOUND_ROWS
                    CONCAT(IFNULL(id_specific_price, ""), "_", IFNULL(id_specific_price_rule, "")) id,
                    spr_name,
                    #conds,
                    #`prod_reference`,
                    `prod_name`,
                    IF (reduction_type = "percentage", CONCAT(ROUND(100 * reduction, 2), "%"), CONCAT("' . $context->currency->sign . '", ROUND(reduction, 2))) reduction,
                    CONCAT(DATE_FORMAT(`from`, "%Y-%m-%d"), " - ", DATE_FORMAT(`to`, "%Y-%m-%d")) from_to,
                    IF (price = -1, -1, CONCAT("' . $context->currency->sign . '", ROUND(price, 2))) price,
                    from_quantity,
                    #email,
                    group_name,
                    #country_name,
                    #currency_iso_code,
                    is_rule
                    ';
        if (self::$setInitial) {
            $sql .= ', 1 AS initial ';
        }
        if (pSQL(Tools::getValue('extra_search_type')) === 'selected') {
            if (isset(Tools::getValue('extra_search_params')['data'])) {
                $ids_sp = [];
                $ids_spr = [];
                foreach (Tools::getValue('extra_search_params')['data'] as $val) {
                    $val = explode('_', $val);
                    if ($val[0]) {
                        $ids_sp[] = $val[0];
                    }
                    if ($val[1]) {
                        $ids_spr[] = $val[1];
                    }
                }
                $sps = implode(',', $ids_sp);
                $sprs = implode(',', $ids_spr);
                if (Tools::getValue('extra_search_params')['type'] === 'selected') {
                    if ($sps) {
                        $sps = ' AND sp.id_specific_price IN (' . $sps . ')';
                    }
                    if ($sprs) {
                        $sprs = ' AND spr.id_specific_price_rule IN (' . $sprs . ')';
                    }
                } else {
                    if ($sps) {
                        $sps = ' AND sp.id_specific_price NOT IN (' . $sps . ')';
                    }
                    if ($sprs) {
                        $sprs = ' AND spr.id_specific_price_rule NOT IN (' . $sprs . ')';
                    }
                }
            } elseif (Tools::getValue('extra_search_params')['type'] === 'selected') {
                $sps = $sprs = " AND 0";
            }
        } elseif (pSQL(Tools::getValue('extra_search_type')) === 'not-selected') {
            if (isset(Tools::getValue('extra_search_params')['data'])) {
                $ids_sp = [];
                $ids_spr = [];
                foreach (Tools::getValue('extra_search_params')['data'] as $val) {
                    $val = explode('_', $val);
                    if ($val[0]) {
                        $ids_sp[] = $val[0];
                    }
                    if ($val[1]) {
                        $ids_spr[] = $val[1];
                    }
                }
                $sps = implode(',', $ids_sp);
                $sprs = implode(',', $ids_spr);
                if (Tools::getValue('extra_search_params')['type'] === 'selected') {
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
            } elseif (Tools::getValue('extra_search_params')['type'] === 'unselected') {
                $sps = $sprs = " AND 0";
            }
        }

        $table = ' FROM (
            SELECT 
                sp.id_specific_price,
                sp.id_specific_price_rule id_specific_price_rule,
                spr.`name` spr_name,
                sprcg.conds,
                sp.id_product,
                p.`reference` prod_reference,
                pl.`name` prod_name,
                sp.reduction reduction,
                sp.reduction_type reduction_type,
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
                (SELECT sprcg.id_specific_price_rule, CAST(GROUP_CONCAT(cond SEPARATOR " | ") AS CHAR) conds
                    FROM ' . _DB_PREFIX_ . 'specific_price_rule_condition_group sprcg
                    LEFT JOIN (
                    SELECT id_specific_price_rule_condition_group, GROUP_CONCAT(CONCAT(sprc.`type`, ":", CASE
                        WHEN sprc.`type` = "category" THEN cl.`name`
                        WHEN sprc.`type` = "manufacturer" THEN m.`name`
                        WHEN sprc.`type` = "supplier" THEN s.`name`
                        WHEN sprc.`type` = "attribute" THEN CONCAT(agl.`name`, " ^ ", agl.public_name, " ^ ", ag.group_type, " ^ ", al.`name`, " ^ ", a.color)
                        WHEN sprc.`type` = "feature" THEN CONCAT(fl.`name`, " ^ ", fvl.`value`, " ^ ", fv.custom)
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
                WHERE 1 ' . (isset($sps) ? $sps : '') . '

                UNION

            SELECT
                NULL `id_specific_price`,
                spr.`id_specific_price_rule`,
                spr.`name` spr_name,
                sprcg.`conds`,
                NULL `id_product`,
                NULL `prod_reference`,
                NULL `prod_name`,
                spr.`reduction`,
                spr.`reduction_type`,
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
            (SELECT sprcg.id_specific_price_rule, CAST(GROUP_CONCAT(cond SEPARATOR " | ") AS CHAR) conds
                FROM ' . _DB_PREFIX_ . 'specific_price_rule_condition_group sprcg
                LEFT JOIN (
                SELECT id_specific_price_rule_condition_group, GROUP_CONCAT(CONCAT(sprc.`type`, ":", CASE
                    WHEN sprc.`type` = "category" THEN cl.`name`
                    WHEN sprc.`type` = "manufacturer" THEN m.`name`
                    WHEN sprc.`type` = "supplier" THEN s.`name`
                    WHEN sprc.`type` = "attribute" THEN CONCAT(agl.`name`, " ^ ", agl.public_name, " ^ ", ag.group_type, " ^ ", al.`name`, " ^ ", a.color)
                    WHEN sprc.`type` = "feature" THEN CONCAT(fl.`name`, " ^ ", fvl.`value`, " ^ ", fv.custom)
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
            FROM ' . _DB_PREFIX_ . 'specific_price) ' . (isset($sprs) ? $sprs : '') . '
        ) discount WHERE 1 ';

        $cond = '';

        $search = Tools::getValue('search')['value'];
        if ($search || $search === '0') {
            $cond .= " AND (spr_name LIKE '%" . $search . "%' OR ";
            $cond .= "prod_name LIKE '%" . $search . "%' OR ";
            $cond .= "reduction LIKE '%" . $search . "%' OR ";
            $cond .= "group_name LIKE '%" . $search . "%' OR ";
            $cond .= "price LIKE '%" . $search . "%' OR ";
            $cond .= "from_quantity LIKE '%" . $search . "%' OR ";
            $cond .= "from_to LIKE '%" . $search . "%')";
        }

        $ord = ' ORDER BY ';

        if (self::$getAll) {
            $ord .= 'id';
            $offset = 0;
            $limit = 10;
        } else {
            foreach (Tools::getValue('order') as $order) {
                $ord .= Tools::getValue('columns')[$order['column']]['data'] . ' ' . $order['dir'] . ', ';
            }
            $offset = pSQL(Tools::getValue('start'));
            $limit = pSQL(Tools::getValue('length'));
        }

        $ord = rtrim($ord, ', ');

        $sql .= $table . $cond . $ord . ' LIMIT ' . $offset . ', ' . $limit;
//                d($sql);
        $data = DB::getInstance()->executeS($sql);

        $filtered = DB::getInstance()->getValue('SELECT FOUND_ROWS()');

        $total = DB::getInstance()->getValue('SELECT COUNT(*) count ' . $table);

        if (self::$getAll) {
            return [
                'recordsTotal' => $total,
                'recordsFiltered' => $filtered,
                'data' => $data
            ];
        }

        $response = array(
            'draw' => pSQL(Tools::getValue('draw')),
            'recordsTotal' => $total,
            'recordsFiltered' => $filtered,
            'data' => $data
        );
        die(json_encode($response));
    }

    public static function getGroups()
    {
        $sql = 'SELECT SQL_CALC_FOUND_ROWS
                    g.id_group id, 
                    gl.`name`, 
                    g.reduction,
                    IFNULL(gc.members, 0) members,
                    g.show_prices, 
                    DATE(g.`date_add`) `date_add`
                    ';
        if (self::$setInitial) {
            $sql .= ', 1 AS initial ';
        }
        $table = ' FROM `' . _DB_PREFIX_ . 'group` g
                        LEFT JOIN `' . _DB_PREFIX_ . 'group_lang` AS gl
                            ON g.id_group = gl.id_group AND gl.id_lang = ' . Context::getContext()->language->id . '
                        LEFT JOIN (
                            SELECT id_group, COUNT(id_customer) members FROM `' . _DB_PREFIX_ . 'customer_group`
                            GROUP BY id_group
                            ) gc ON g.id_group = gc.id_group
                        WHERE 1';

        $cond = '';
        if (pSQL(Tools::getValue('extra_search_type')) === 'selected') {
            if (isset(Tools::getValue('extra_search_params')['data'])) {
                if (Tools::getValue('extra_search_params')['type'] === 'selected') {
                    $cond .= " AND g.id_group IN (" . implode(', ', Tools::getValue('extra_search_params')['data']) . ")";
                } else {
                    $cond .= " AND g.id_group NOT IN (" . implode(', ', Tools::getValue('extra_search_params')['data']) . ")";
                }
            } elseif (Tools::getValue('extra_search_params')['type'] === 'selected') {
                $cond .= " AND 0";
            }
        } elseif (pSQL(Tools::getValue('extra_search_type')) === 'not-selected') {
            if (isset(Tools::getValue('extra_search_params')['data'])) {
                if (Tools::getValue('extra_search_params')['type'] === 'selected') {
                    $cond .= " AND g.id_group NOT IN (" . implode(', ', Tools::getValue('extra_search_params')['data']) . ")";
                } else {
                    $cond .= " AND g.id_group IN (" . implode(', ', Tools::getValue('extra_search_params')['data']) . ")";
                }
            } elseif (Tools::getValue('extra_search_params')['type'] === 'unselected') {
                $cond .= " AND 0";
            }
        }

        $search = Tools::getValue('search')['value'];
        if ($search || $search === '0') {
            $cond .= " AND (gl.`name` LIKE '%" . $search . "%' OR ";
            $cond .= "DATE(g.`date_add`) LIKE '%" . $search . "%' OR ";
            $cond .= "g.id_group LIKE '%" . $search . "%' OR ";
            $cond .= "g.`reduction` LIKE '%" . $search . "%' OR ";
            $cond .= "gc.members LIKE '%" . $search . "%')";
        }

        $ord = ' ORDER BY ';

        if (self::$getAll) {
            $ord .= 'id';
            $offset = 0;
            $limit = 10;
        } else {
            foreach (Tools::getValue('order') as $order) {
                $ord .= Tools::getValue('columns')[$order['column']]['data'] . ' ' . $order['dir'] . ', ';
            }
            $offset = pSQL(Tools::getValue('start'));
            $limit = pSQL(Tools::getValue('length'));
        }

        $ord = rtrim($ord, ', ');

        $sql .= $table . $cond . $ord . ' LIMIT ' . $offset . ', ' . $limit;
//                d($sql);
        $data = DB::getInstance()->executeS($sql);

        $filtered = DB::getInstance()->getValue('SELECT FOUND_ROWS()');

        $total = DB::getInstance()->getValue('SELECT COUNT(id_group) count FROM `'
            . _DB_PREFIX_ . 'group`');

        if (self::$getAll) {
            return [
                'recordsTotal' => $total,
                'recordsFiltered' => $filtered,
                'data' => $data
            ];
        }

        $response = array(
            'draw' => pSQL(Tools::getValue('draw')),
            'recordsTotal' => $total,
            'recordsFiltered' => $filtered,
            'data' => $data
        );
        die(json_encode($response));
    }

    public static function getCustomers()
    {
        $id_lang = Context::getContext()->language->id;
        $sql = 'SELECT SQL_CALC_FOUND_ROWS
                    c.id_customer id, 
                    c.firstname, 
                    c.lastname, 
                    c.email, 
                    gel.`name` gender, 
                    active enabled,
                    gl.`name` `group`,
                    c.newsletter,
                    c.deleted
                     ';
        if (self::$setInitial) {
            $sql .= ', 1 AS initial ';
        }
        $table = ' FROM `' . _DB_PREFIX_ . 'customer` c 
                        LEFT JOIN `' . _DB_PREFIX_ . 'group_lang` AS gl
                            ON c.id_default_group = gl.id_group AND gl.id_lang = ' . $id_lang . '
                        LEFT JOIN `' . _DB_PREFIX_ . 'gender_lang` gel
                            ON c.id_gender = gel.id_gender AND gel.id_lang= ' . $id_lang . '
                        WHERE c.deleted = 0 ';

        $cond = '';
        if (pSQL(Tools::getValue('extra_search_type')) === 'selected') {
            if (isset(Tools::getValue('extra_search_params')['data'])) {
                if (Tools::getValue('extra_search_params')['type'] === 'selected') {
                    $cond .= " AND id_customer IN (" . implode(', ', Tools::getValue('extra_search_params')['data']) . ")";
                } else {
                    $cond .= " AND id_customer NOT IN (" . implode(', ', Tools::getValue('extra_search_params')['data']) . ")";
                }
            } elseif (Tools::getValue('extra_search_params')['type'] === 'selected') {
                $cond .= " AND 0";
            }
        } elseif (pSQL(Tools::getValue('extra_search_type')) === 'not-selected') {
            if (isset(Tools::getValue('extra_search_params')['data'])) {
                if (Tools::getValue('extra_search_params')['type'] === 'selected') {
                    $cond .= " AND id_customer NOT IN (" . implode(', ', Tools::getValue('extra_search_params')['data']) . ")";
                } else {
                    $cond .= " AND id_customer IN (" . implode(', ', Tools::getValue('extra_search_params')['data']) . ")";
                }
            } elseif (Tools::getValue('extra_search_params')['type'] === 'unselected') {
                $cond .= " AND 0";
            }
        }

        $search = Tools::getValue('search')['value'];
        if ($search || $search === '0') {
            $cond .= " AND (firstname LIKE '%" . $search . "%' OR ";
            $cond .= "lastname LIKE '%" . $search . "%' OR ";
            $cond .= "id_customer LIKE '%" . $search . "%' OR ";
            $cond .= "gel.`name` LIKE '%" . $search . "%' OR ";
            $cond .= "gl.`name` LIKE '%" . $search . "%' OR ";
            $cond .= "email LIKE '%" . $search . "%')";
        }

        $ord = ' ORDER BY ';

        if (self::$getAll) {
            $ord .= 'id';
            $offset = 0;
            $limit = 10;
        } else {
            foreach (Tools::getValue('order') as $order) {
                $ord .= Tools::getValue('columns')[$order['column']]['data'] . ' ' . $order['dir'] . ', ';
            }
            $offset = pSQL(Tools::getValue('start'));
            $limit = pSQL(Tools::getValue('length'));
        }

        $ord = rtrim($ord, ', ');

        $sql .= $table . $cond . $ord . ' LIMIT ' . $offset . ', ' . $limit;
        //        d($sql);
        $data = DB::getInstance()->executeS($sql);

        $filtered = DB::getInstance()->getValue('SELECT FOUND_ROWS()');

        $total = DB::getInstance()->getValue('SELECT COUNT(id_customer) count FROM '
            . _DB_PREFIX_ . 'customer WHERE deleted = 0');

        if (self::$getAll) {
            return [
                'recordsTotal' => $total,
                'recordsFiltered' => $filtered,
                'data' => $data
            ];
        }

        $response = array(
            'draw' => pSQL(Tools::getValue('draw')),
            'recordsTotal' => $total,
            'recordsFiltered' => $filtered,
            'data' => $data
        );
        die(json_encode($response));
    }

    public static function getWarehouses()
    {
        $sql = 'SELECT SQL_CALC_FOUND_ROWS
                    warehouse.id_warehouse id, 
                    warehouse.reference, 
                    warehouse.name, 
                    warehouse.management_type, 
                    currency.iso_code, 
                    address.address1
                    ';
        if (self::$setInitial) {
            $sql .= ', 1 AS initial ';
        }
        $table = ' FROM ' . _DB_PREFIX_ . 'warehouse warehouse
                LEFT JOIN
            ' . _DB_PREFIX_ . 'employee employee ON warehouse.id_employee = employee.id_employee
                LEFT JOIN
                ' . _DB_PREFIX_ . 'currency currency ON warehouse.id_currency = currency.id_currency
                LEFT JOIN
                ' . _DB_PREFIX_ . 'address address ON warehouse.id_address = address.id_address
                WHERE warehouse.deleted = 0
                    ';

        $cond = '';
        if (pSQL(Tools::getValue('extra_search_type')) === 'selected') {
            if (isset(Tools::getValue('extra_search_params')['data'])) {
                if (Tools::getValue('extra_search_params')['type'] === 'selected') {
                    $cond .= " AND id_warehouse IN (" . implode(', ', Tools::getValue('extra_search_params')['data']) . ")";
                } else {
                    $cond .= " AND id_warehouse NOT IN (" . implode(', ', Tools::getValue('extra_search_params')['data']) . ")";
                }
            } elseif (Tools::getValue('extra_search_params')['type'] === 'selected') {
                $cond .= " AND 0";
            }
        } elseif (pSQL(Tools::getValue('extra_search_type')) === 'not-selected') {
            if (isset(Tools::getValue('extra_search_params')['data'])) {
                if (Tools::getValue('extra_search_params')['type'] === 'selected') {
                    $cond .= " AND id_warehouse NOT IN (" . implode(', ', Tools::getValue('extra_search_params')['data']) . ")";
                } else {
                    $cond .= " AND id_warehouse IN (" . implode(', ', Tools::getValue('extra_search_params')['data']) . ")";
                }
            } elseif (Tools::getValue('extra_search_params')['type'] === 'unselected') {
                $cond .= " AND 0";
            }
        }

        $search = Tools::getValue('search')['value'];
        if ($search || $search === '0') {
            $cond .= " AND (warehouse.reference LIKE '%" . $search . "%' OR ";
            $cond .= "warehouse.name LIKE '%" . $search . "%' OR ";
            $cond .= "warehouse.id_warehouse LIKE '%" . $search . "%' OR ";
            $cond .= "currency.iso_code LIKE '%" . $search . "%' OR ";
            $cond .= "warehouse.management_type LIKE '%" . $search . "%' OR ";
            $cond .= "address.address1 LIKE '%" . $search . "%')";
        }

        $ord = ' ORDER BY ';

        if (self::$getAll) {
            $ord .= 'id';
            $offset = 0;
            $limit = 10;
        } else {
            foreach (Tools::getValue('order') as $order) {
                $ord .= Tools::getValue('columns')[$order['column']]['data'] . ' ' . $order['dir'] . ', ';
            }
            $offset = pSQL(Tools::getValue('start'));
            $limit = pSQL(Tools::getValue('length'));
        }

        $ord = rtrim($ord, ', ');

        $sql .= $table . $cond . $ord . ' LIMIT ' . $offset . ', ' . $limit;
        //        d($sql);
        $data = DB::getInstance()->executeS($sql);

        $filtered = DB::getInstance()->getValue('SELECT FOUND_ROWS()');

        $total = DB::getInstance()->getValue('SELECT COUNT(id_warehouse) count FROM '
            . _DB_PREFIX_ . 'warehouse WHERE deleted = 0');

        if (self::$getAll) {
            return [
                'recordsTotal' => $total,
                'recordsFiltered' => $filtered,
                'data' => $data
            ];
        }

        $response = array(
            'draw' => pSQL(Tools::getValue('draw')),
            'recordsTotal' => $total,
            'recordsFiltered' => $filtered,
            'data' => $data
        );
        die(json_encode($response));
    }

    public static function getAliases()
    {
        $sql = 'SELECT SQL_CALC_FOUND_ROWS
                    alias.id_alias id, 
                    alias.alias, 
                    alias.search, 
                    alias.active enabled
                    ';
        if (self::$setInitial) {
            $sql .= ', 1 AS initial ';
        }
        $table = ' FROM ' . _DB_PREFIX_ . 'alias alias
                WHERE 1
                    ';

        $cond = '';
        if (pSQL(Tools::getValue('extra_search_type')) === 'selected') {
            if (isset(Tools::getValue('extra_search_params')['data'])) {
                if (Tools::getValue('extra_search_params')['type'] === 'selected') {
                    $cond .= " AND id_alias IN (" . implode(', ', Tools::getValue('extra_search_params')['data']) . ")";
                } else {
                    $cond .= " AND id_alias NOT IN (" . implode(', ', Tools::getValue('extra_search_params')['data']) . ")";
                }
            } elseif (Tools::getValue('extra_search_params')['type'] === 'selected') {
                $cond .= " AND 0";
            }
        } elseif (pSQL(Tools::getValue('extra_search_type')) === 'not-selected') {
            if (isset(Tools::getValue('extra_search_params')['data'])) {
                if (Tools::getValue('extra_search_params')['type'] === 'selected') {
                    $cond .= " AND id_alias NOT IN (" . implode(', ', Tools::getValue('extra_search_params')['data']) . ")";
                } else {
                    $cond .= " AND id_alias IN (" . implode(', ', Tools::getValue('extra_search_params')['data']) . ")";
                }
            } elseif (Tools::getValue('extra_search_params')['type'] === 'unselected') {
                $cond .= " AND 0";
            }
        }

        $search = Tools::getValue('search')['value'];
        if ($search || $search === '0') {
            $cond .= " AND (alias.id_alias LIKE '%" . $search . "%' OR ";
            $cond .= "alias.alias LIKE '%" . $search . "%' OR ";
            $cond .= "alias.search LIKE '%" . $search . "%')";
        }

        $ord = ' ORDER BY ';

        if (self::$getAll) {
            $ord .= 'id';
            $offset = 0;
            $limit = 10;
        } else {
            foreach (Tools::getValue('order') as $order) {
                $ord .= Tools::getValue('columns')[$order['column']]['data'] . ' ' . $order['dir'] . ', ';
            }
            $offset = pSQL(Tools::getValue('start'));
            $limit = pSQL(Tools::getValue('length'));
        }

        $ord = rtrim($ord, ', ');

        $sql .= $table . $cond . $ord . ' LIMIT ' . $offset . ', ' . $limit;
        //        d($sql);
        $data = DB::getInstance()->executeS($sql);

        $filtered = DB::getInstance()->getValue('SELECT FOUND_ROWS()');

        $total = DB::getInstance()->getValue('SELECT COUNT(id_alias) count FROM '
            . _DB_PREFIX_ . 'alias');

        if (self::$getAll) {
            return [
                'recordsTotal' => $total,
                'recordsFiltered' => $filtered,
                'data' => $data
            ];
        }

        $response = array(
            'draw' => pSQL(Tools::getValue('draw')),
            'recordsTotal' => $total,
            'recordsFiltered' => $filtered,
            'data' => $data
        );
        die(json_encode($response));
    }

    public static function getAddresses()
    {
        $sql = 'SELECT SQL_CALC_FOUND_ROWS
                    address.id_address id, 
                    address.address1, 
                    country_lang.name country_name, 
                    address.city,
                    address.firstname,
                    address.lastname,
                    customer.email,
                    manufacturer.name brand_name,
                    supplier.name supplier_name
                    ';
        if (self::$setInitial) {
            $sql .= ', 1 AS initial ';
        }
        $table = ' FROM ' . _DB_PREFIX_ . 'address address
                        LEFT JOIN
                    ' . _DB_PREFIX_ . 'country_lang country_lang ON address.id_country = country_lang.id_country
                            AND country_lang.id_lang = ' . Context::getContext()->language->id . '
                        LEFT JOIN
                    ' . _DB_PREFIX_ . 'state state ON address.id_state = state.id_state
                        LEFT JOIN
                    ' . _DB_PREFIX_ . 'customer customer ON address.id_customer = customer.id_customer
                        LEFT JOIN
                    ' . _DB_PREFIX_ . 'manufacturer manufacturer ON address.id_manufacturer = manufacturer.id_manufacturer
                        LEFT JOIN
                    ' . _DB_PREFIX_ . 'supplier supplier ON address.id_supplier = supplier.id_supplier
                        WHERE address.deleted = 0 ';

        $cond = '';
        if (pSQL(Tools::getValue('extra_search_type')) === 'selected') {
            if (isset(Tools::getValue('extra_search_params')['data'])) {
                if (Tools::getValue('extra_search_params')['type'] === 'selected') {
                    $cond .= " AND id_address IN (" . implode(', ', Tools::getValue('extra_search_params')['data']) . ")";
                } else {
                    $cond .= " AND id_address NOT IN (" . implode(', ', Tools::getValue('extra_search_params')['data']) . ")";
                }
            } elseif (Tools::getValue('extra_search_params')['type'] === 'selected') {
                $cond .= " AND 0";
            }
        } elseif (pSQL(Tools::getValue('extra_search_type')) === 'not-selected') {
            if (isset(Tools::getValue('extra_search_params')['data'])) {
                if (Tools::getValue('extra_search_params')['type'] === 'selected') {
                    $cond .= " AND id_address NOT IN (" . implode(', ', Tools::getValue('extra_search_params')['data']) . ")";
                } else {
                    $cond .= " AND id_address IN (" . implode(', ', Tools::getValue('extra_search_params')['data']) . ")";
                }
            } elseif (Tools::getValue('extra_search_params')['type'] === 'unselected') {
                $cond .= " AND 0";
            }
        }

        $search = Tools::getValue('search')['value'];
        if ($search || $search === '0') {
            $cond .= " AND (address.id_address LIKE '%" . $search . "%' OR ";
            $cond .= "address.address1 LIKE '%" . $search . "%' OR ";
            $cond .= "country_lang.name LIKE '%" . $search . "%' OR ";
            $cond .= "address.city LIKE '%" . $search . "%' OR ";
            $cond .= "address.firstname LIKE '%" . $search . "%' OR ";
            $cond .= "address.lastname LIKE '%" . $search . "%' OR ";
            $cond .= "manufacturer.name LIKE '%" . $search . "%' OR ";
            $cond .= "supplier.name LIKE '%" . $search . "%' OR ";
            $cond .= "customer.email LIKE '%" . $search . "%')";
        }

        $ord = ' ORDER BY ';

        if (self::$getAll) {
            $ord .= 'id';
            $offset = 0;
            $limit = 10;
        } else {
            foreach (Tools::getValue('order') as $order) {
                $ord .= Tools::getValue('columns')[$order['column']]['data'] . ' ' . $order['dir'] . ', ';
            }
            $offset = pSQL(Tools::getValue('start'));
            $limit = pSQL(Tools::getValue('length'));
        }

        $ord = rtrim($ord, ', ');

        $sql .= $table . $cond . $ord . ' LIMIT ' . $offset . ', ' . $limit;
//        d($sql);
        $data = DB::getInstance()->executeS($sql);

        $filtered = DB::getInstance()->getValue('SELECT FOUND_ROWS()');

        $total = DB::getInstance()->getValue('SELECT COUNT(id_address) count FROM '
            . _DB_PREFIX_ . 'address WHERE deleted = 0');

        if (self::$getAll) {
            return [
                'recordsTotal' => $total,
                'recordsFiltered' => $filtered,
                'data' => $data
            ];
        }

        $response = array(
            'draw' => pSQL(Tools::getValue('draw')),
            'recordsTotal' => $total,
            'recordsFiltered' => $filtered,
            'data' => $data
        );
        die(json_encode($response));
    }

    public static function getCarriers()
    {
        $sql = 'SELECT SQL_CALC_FOUND_ROWS
                    *  
                FROM (SELECT 
                    c.id_carrier id,
                    c.id_reference reference,
                    delay,
                    active enabled,
                    is_free,
                    IF(`name` = "0", (SELECT value FROM '
            . _DB_PREFIX_ . 'configuration WHERE `name` = "PS_SHOP_NAME" LIMIT 1), `name`) `name`';
        if (self::$setInitial) {
            $sql .= ', 1 AS initial ';
        }
        $sql .= ' FROM `'
            . _DB_PREFIX_ . 'carrier` c
                LEFT JOIN `' . _DB_PREFIX_ . 'carrier_lang` cl ON  c.id_carrier = cl.id_carrier
                    AND cl.id_lang = ' . Context::getContext()->language->id . '
                    AND cl.id_shop = ' . Context::getContext()->shop->id . '
                WHERE c.deleted = 0 ';

        $cond = '';
        if (pSQL(Tools::getValue('extra_search_type')) === 'selected') {
            if (isset(Tools::getValue('extra_search_params')['data'])) {
                if (Tools::getValue('extra_search_params')['type'] === 'selected') {
                    $cond .= " AND c.id_reference IN (" . implode(', ', Tools::getValue('extra_search_params')['data']) . ")";
                } else {
                    $cond .= " AND c.id_reference NOT IN (" . implode(', ', Tools::getValue('extra_search_params')['data']) . ")";
                }
            } elseif (Tools::getValue('extra_search_params')['type'] === 'selected') {
                $cond .= " AND 0";
            }
        } elseif (pSQL(Tools::getValue('extra_search_type')) === 'not-selected') {
            if (isset(Tools::getValue('extra_search_params')['data'])) {
                if (Tools::getValue('extra_search_params')['type'] === 'selected') {
                    $cond .= " AND c.id_reference NOT IN (" . implode(', ', Tools::getValue('extra_search_params')['data']) . ")";
                } else {
                    $cond .= " AND c.id_reference IN (" . implode(', ', Tools::getValue('extra_search_params')['data']) . ")";
                }
            } elseif (Tools::getValue('extra_search_params')['type'] === 'unselected') {
                $cond .= " AND 0";
            }
        }

        $cond .= ') tmp WHERE 1';

        $search = Tools::getValue('search')['value'];
        if ($search || $search === '0') {
            $cond .= " AND (id LIKE '%" . $search . "%' OR ";
            $cond .= "reference LIKE '%" . $search . "%' OR ";
            $cond .= "delay LIKE '%" . $search . "%' OR ";
            $cond .= "`name` LIKE '%" . $search . "%')";
        }

        $ord = ' ORDER BY ';

        if (self::$getAll) {
            $ord .= 'id';
            $offset = 0;
            $limit = 10;
        } else {
            foreach (Tools::getValue('order') as $order) {
                $ord .= Tools::getValue('columns')[$order['column']]['data'] . ' ' . $order['dir'] . ', ';
            }
            $offset = pSQL(Tools::getValue('start'));
            $limit = pSQL(Tools::getValue('length'));
        }

        $ord = rtrim($ord, ', ');

        $sql .= $cond . $ord . ' LIMIT ' . $offset . ', ' . $limit;
        //        d($sql);
        $data = DB::getInstance()->executeS($sql);

        $filtered = DB::getInstance()->getValue('SELECT FOUND_ROWS()');

        foreach ($data as &$val) {
            $val['logo'] = ImageManager::thumbnail(_PS_SHIP_IMG_DIR_ . $val['id'] . '.jpg', 'carrier_mini_'
                    . $val['id'] . '_' . Context::getContext()->shop->id . '.jpg', 45, 'jpg');
        }

        $total = DB::getInstance()->getValue('SELECT COUNT(id_carrier) count FROM '
            . _DB_PREFIX_ . 'carrier WHERE deleted = 0');

        if (self::$getAll) {
            return [
                'recordsTotal' => $total,
                'recordsFiltered' => $filtered,
                'data' => $data
            ];
        }

        $response = array(
            'draw' => pSQL(Tools::getValue('draw')),
            'recordsTotal' => $total,
            'recordsFiltered' => $filtered,
            'data' => $data
        );
        die(json_encode($response));
    }

    public static function getPacks()
    {
        $langId = Context::getContext()->language->id;
        $shopId = Context::getContext()->shop->id;
        
        $sql = 'SELECT SQL_CALC_FOUND_ROWS
                    p.id_product_pack id,
                    CONCAT_WS("-", p.id_product_pack, p.id_product_item, p.id_product_attribute_item) unique_id,
                    p.id_product_item product_id,
                    pl.`name` `product_name`,
                    pack_name.`name` `pack_name`,
                    pr.`reference` `product_reference`,
                    pa.id_product_attribute combination_id,
                    pa.reference combination_reference,
                    attributes.name_values,
                    p.quantity';
        if (self::$setInitial) {
            $sql .= ', 1 AS initial ';
        }
        $sql .= ' FROM ' . _DB_PREFIX_ . 'pack p
                JOIN ' . _DB_PREFIX_ . 'product_lang pack_name ON p.id_product_pack = pack_name.id_product
                    AND pack_name.id_shop = ' . $shopId . '
                    AND pack_name.id_lang = ' . $langId . '
                JOIN ' . _DB_PREFIX_ . 'product pr ON p.id_product_item = pr.id_product
                JOIN ' . _DB_PREFIX_ . 'product_lang pl ON pr.id_product = pl.id_product
                    AND pl.id_shop = ' . $shopId . '
                    AND pl.id_lang = ' . $langId . '
                LEFT JOIN ' . _DB_PREFIX_ . 'product_attribute pa ON p.id_product_attribute_item = pa.id_product_attribute
                LEFT JOIN ' . _DB_PREFIX_ . 'product_attribute_shop pas ON pa.id_product_attribute = pas.id_product_attribute
                    AND pas.id_shop = ' . $shopId . '
                LEFT JOIN (
                    SELECT                        
                        pac.id_product_attribute,
                        GROUP_CONCAT(CONCAT_WS(":", IFNULL(agl.name, ""), al.name) ORDER BY agl.name, al.name SEPARATOR ", ") name_values,
                        GROUP_CONCAT(CONCAT_WS(":", agl.name, agl.public_name, ag.group_type) ORDER BY agl.name, al.name SEPARATOR ", ") `groups`,
                        GROUP_CONCAT(al.name ORDER BY agl.name, al.name SEPARATOR ", ") `values`
                    FROM ' . _DB_PREFIX_ . 'product_attribute_combination pac
                    JOIN ' . _DB_PREFIX_ . 'attribute a ON pac.id_attribute = a.id_attribute
                    JOIN ' . _DB_PREFIX_ . 'attribute_shop ash ON a.id_attribute = ash.id_attribute AND ash.id_shop = ' . $shopId . '
                    JOIN ' . _DB_PREFIX_ . 'attribute_lang al ON a.id_attribute = al.id_attribute AND al.id_lang = ' . $langId . '
                    JOIN ' . _DB_PREFIX_ . 'attribute_group ag ON a.id_attribute_group = ag.id_attribute_group
                    JOIN ' . _DB_PREFIX_ . 'attribute_group_shop agsh ON a.id_attribute_group = agsh.id_attribute_group AND agsh.id_shop = ' . $shopId . '
                    JOIN ' . _DB_PREFIX_ . 'attribute_group_lang agl ON a.id_attribute_group = agl.id_attribute_group AND agl.id_lang = ' . $langId . '
                    GROUP BY pac.id_product_attribute
                ) attributes ON pa.id_product_attribute = attributes.id_product_attribute
            ';

        $cond = '';
        if (pSQL(Tools::getValue('extra_search_type')) === 'selected') {
            if (isset(Tools::getValue('extra_search_params')['data'])) {
                if (Tools::getValue('extra_search_params')['type'] === 'selected') {
                    $cond .= " AND p.id_product_pack IN (" . implode(', ', Tools::getValue('extra_search_params')['data']) . ")";
                } else {
                    $cond .= " AND p.id_product_pack NOT IN (" . implode(', ', Tools::getValue('extra_search_params')['data']) . ")";
                }
            } elseif (Tools::getValue('extra_search_params')['type'] === 'selected') {
                $cond .= " AND 0";
            }
        } elseif (pSQL(Tools::getValue('extra_search_type')) === 'not-selected') {
            if (isset(Tools::getValue('extra_search_params')['data'])) {
                if (Tools::getValue('extra_search_params')['type'] === 'selected') {
                    $cond .= " AND p.id_product_pack NOT IN (" . implode(', ', Tools::getValue('extra_search_params')['data']) . ")";
                } else {
                    $cond .= " AND p.id_product_pack IN (" . implode(', ', Tools::getValue('extra_search_params')['data']) . ")";
                }
            } elseif (Tools::getValue('extra_search_params')['type'] === 'unselected') {
                $cond .= " AND 0";
            }
        }

        $search = Tools::getValue('search')['value'];
        if ($search || $search === '0') {
            $cond .= " AND (p.id_product_pack LIKE '%" . $search . "%' OR ";
            $cond .= "p.id_product_item product_id LIKE '%" . $search . "%' OR ";
            $cond .= "pl.`name` LIKE '%" . $search . "%' OR ";
            $cond .= "p.id_product_attribute_item LIKE '%" . $search . "%' OR ";
            $cond .= "attributes.name_values LIKE '%" . $search . "%' OR ";
            $cond .= "p.quantity LIKE '%" . $search . "%')";
        }

        $ord = ' ORDER BY ';

        if (self::$getAll) {
            $ord .= 'id';
            $offset = 0;
            $limit = 10;
        } else {
            foreach (Tools::getValue('order') as $order) {
                $ord .= Tools::getValue('columns')[$order['column']]['data'] . ' ' . $order['dir'] . ', ';
            }
            $offset = pSQL(Tools::getValue('start'));
            $limit = pSQL(Tools::getValue('length'));
        }

        $ord = rtrim($ord, ', ');

        $sql .= $cond . $ord . ' LIMIT ' . $offset . ', ' . $limit;
        //        d($sql);
        $data = DB::getInstance()->executeS($sql);

        $filtered = DB::getInstance()->getValue('SELECT FOUND_ROWS()');

        $total = DB::getInstance()->getValue('SELECT COUNT(id_product_pack) `count` FROM '
            . _DB_PREFIX_ . 'pack');

        if (self::$getAll) {
            return [
                'recordsTotal' => $total,
                'recordsFiltered' => $filtered,
                'data' => $data
            ];
        }

        $response = array(
            'draw' => pSQL(Tools::getValue('draw')),
            'recordsTotal' => $total,
            'recordsFiltered' => $filtered,
            'data' => $data
        );
        die(json_encode($response));
    }
    
    public static function getManufacturers()
    {
        $sql = 'SELECT SQL_CALC_FOUND_ROWS
                    m.id_manufacturer id, 
                    `name`, 
                    active enabled, 
                    mp.prod_count, 
                    ma.address_count';
        if (self::$setInitial) {
            $sql .= ', 1 AS initial ';
        }
        $sql .= ' FROM 
                ' . _DB_PREFIX_ . 'manufacturer m ';

        $join = 'LEFT JOIN (
                SELECT id_manufacturer, COUNT(id_product) prod_count
                FROM ' . _DB_PREFIX_ . 'product
                GROUP BY id_manufacturer) mp ON m.id_manufacturer = mp.id_manufacturer
                LEFT JOIN (SELECT id_manufacturer, COUNT(id_address) address_count
                FROM ' . _DB_PREFIX_ . 'address
                GROUP BY id_manufacturer) ma ON m.id_manufacturer = ma.id_manufacturer WHERE 1';

        $cond = '';
        if (pSQL(Tools::getValue('extra_search_type')) === 'selected') {
            if (isset(Tools::getValue('extra_search_params')['data'])) {
                if (Tools::getValue('extra_search_params')['type'] === 'selected') {
                    $cond .= " AND m.id_manufacturer IN (" . implode(', ', Tools::getValue('extra_search_params')['data']) . ")";
                } else {
                    $cond .= " AND m.id_manufacturer NOT IN (" . implode(', ', Tools::getValue('extra_search_params')['data']) . ")";
                }
            } elseif (Tools::getValue('extra_search_params')['type'] === 'selected') {
                $cond .= " AND 0";
            }
        } elseif (pSQL(Tools::getValue('extra_search_type')) === 'not-selected') {
            if (isset(Tools::getValue('extra_search_params')['data'])) {
                if (Tools::getValue('extra_search_params')['type'] === 'selected') {
                    $cond .= " AND m.id_manufacturer NOT IN (" . implode(', ', Tools::getValue('extra_search_params')['data']) . ")";
                } else {
                    $cond .= " AND m.id_manufacturer IN (" . implode(', ', Tools::getValue('extra_search_params')['data']) . ")";
                }
            } elseif (Tools::getValue('extra_search_params')['type'] === 'unselected') {
                $cond .= " AND 0";
            }
        }

        $search = Tools::getValue('search')['value'];
        if ($search || $search === '0') {
            $cond .= " AND (m.id_manufacturer LIKE '%" . $search . "%' OR ";
            $cond .= "prod_count LIKE '%" . $search . "%' OR ";
            $cond .= "address_count LIKE '%" . $search . "%' OR ";
            $cond .= "`name` LIKE '%" . $search . "%')";
        }

        $ord = ' ORDER BY ';

        if (self::$getAll) {
            $ord .= 'id';
            $offset = 0;
            $limit = 10;
        } else {
            foreach (Tools::getValue('order') as $order) {
                $ord .= Tools::getValue('columns')[$order['column']]['data'] . ' ' . $order['dir'] . ', ';
            }
            $offset = pSQL(Tools::getValue('start'));
            $limit = pSQL(Tools::getValue('length'));
        }

        $ord = rtrim($ord, ', ');

        $sql .= $join . $cond . $ord . ' LIMIT ' . $offset . ', ' . $limit;
        //        d($sql);
        $data = DB::getInstance()->executeS($sql);

        $filtered = DB::getInstance()->getValue('SELECT FOUND_ROWS()');

        foreach ($data as &$val) {
            $val['logo'] = ImageManager::thumbnail(_PS_MANU_IMG_DIR_ . $val['id'] . '.jpg', 'manufacturer_mini_'
                    . $val['id'] . '_' . Context::getContext()->shop->id . '.jpg', 45, 'jpg');
        }

        $total = DB::getInstance()->getValue('SELECT COUNT(id_manufacturer) `count` FROM '
            . _DB_PREFIX_ . 'manufacturer');

        if (self::$getAll) {
            return [
                'recordsTotal' => $total,
                'recordsFiltered' => $filtered,
                'data' => $data
            ];
        }

        $response = array(
            'draw' => pSQL(Tools::getValue('draw')),
            'recordsTotal' => $total,
            'recordsFiltered' => $filtered,
            'data' => $data
        );
        die(json_encode($response));
    }

    public static function getSuppliers()
    {
        $sql = 'SELECT SQL_CALC_FOUND_ROWS
                    s.id_supplier id, 
                    `name`, 
                    active enabled, 
                    prod_count';
        if (self::$setInitial) {
            $sql .= ', 1 AS initial ';
        }
        $sql .= ' FROM ' . _DB_PREFIX_ . 'supplier s ';
        $join = ' LEFT JOIN (
                SELECT id_supplier, COUNT(id_product) prod_count
                FROM ' . _DB_PREFIX_ . 'product
                GROUP BY id_supplier) sp ON s.id_supplier = sp.id_supplier
                WHERE 1 ';

        $cond = '';
        if (pSQL(Tools::getValue('extra_search_type')) === 'selected') {
            if (isset(Tools::getValue('extra_search_params')['data'])) {
                if (Tools::getValue('extra_search_params')['type'] === 'selected') {
                    $cond .= " AND s.id_supplier IN (" . implode(', ', Tools::getValue('extra_search_params')['data']) . ")";
                } else {
                    $cond .= " AND s.id_supplier NOT IN (" . implode(', ', Tools::getValue('extra_search_params')['data']) . ")";
                }
            } elseif (Tools::getValue('extra_search_params')['type'] === 'selected') {
                $cond .= " AND 0";
            }
        } elseif (pSQL(Tools::getValue('extra_search_type')) === 'not-selected') {
            if (isset(Tools::getValue('extra_search_params')['data'])) {
                if (Tools::getValue('extra_search_params')['type'] === 'selected') {
                    $cond .= " AND s.id_supplier NOT IN (" . implode(', ', Tools::getValue('extra_search_params')['data']) . ")";
                } else {
                    $cond .= " AND s.id_supplier IN (" . implode(', ', Tools::getValue('extra_search_params')['data']) . ")";
                }
            } elseif (Tools::getValue('extra_search_params')['type'] === 'unselected') {
                $cond .= " AND 0";
            }
        }

        $search = Tools::getValue('search')['value'];
        if ($search || $search === '0') {
            $cond .= " AND (s.id_supplier LIKE '%" . $search . "%' OR ";
            $cond .= "prod_count LIKE '%" . $search . "%' OR ";
            $cond .= "`name` LIKE '%" . $search . "%')";
        }

        $ord = ' ORDER BY ';

        if (self::$getAll) {
            $ord .= 'id';
            $offset = 0;
            $limit = 10;
        } else {
            foreach (Tools::getValue('order') as $order) {
                $ord .= Tools::getValue('columns')[$order['column']]['data'] . ' ' . $order['dir'] . ', ';
            }
            $offset = pSQL(Tools::getValue('start'));
            $limit = pSQL(Tools::getValue('length'));
        }

        $ord = rtrim($ord, ', ');

        $sql .= $join . $cond . $ord . ' LIMIT ' . $offset . ', ' . $limit;

        $data = DB::getInstance()->executeS($sql);

        $filtered = DB::getInstance()->getValue('SELECT FOUND_ROWS()');

        foreach ($data as &$val) {
            $val['logo'] = ImageManager::thumbnail(_PS_SUPP_IMG_DIR_ . $val['id'] . '.jpg', 'supplier_mini_'
                    . $val['id'] . '_' . Context::getContext()->shop->id . '.jpg', 45, 'jpg');
        }

        $total = DB::getInstance()->getValue('SELECT COUNT(id_supplier) count FROM '
            . _DB_PREFIX_ . 'supplier');

        if (self::$getAll) {
            return [
                'recordsTotal' => $total,
                'recordsFiltered' => $filtered,
                'data' => $data
            ];
        }

        $response = array(
            'draw' => pSQL(Tools::getValue('draw')),
            'recordsTotal' => $total,
            'recordsFiltered' => $filtered,
            'data' => $data
        );
        die(json_encode($response));
    }

    public static function getStores()
    {
        $langId = Context::getContext()->language->id;
        if (!empty(Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS("SHOW TABLES LIKE '" . _DB_PREFIX_ . "store_lang'"))) {
            $select = '
                sl.name,
                sl.address1,
                ';
            $join = '
                LEFT JOIN
            ' . _DB_PREFIX_ . 'store_lang sl ON s.id_store = sl.id_store
                    AND sl.id_lang = ' . $langId . '
                WHERE 1 ';
        } else {
            $select = '
                s.name,
                s.address1,
                ';
            $join = '
                WHERE 1 ';
        }

        if (self::$setInitial) {
            $select .= '
            1 AS initial, ';
        }

        $sql = 'SELECT SQL_CALC_FOUND_ROWS
                    s.id_store id,
                    ' . $select . '
                    s.city,
                    s.postcode,
                    st.name state_name,
                    cl.name country_name,
                    s.phone,
                    s.`active` enabled
                FROM ' . _DB_PREFIX_ . 'store s 
                LEFT JOIN
                ' . _DB_PREFIX_ . 'country_lang cl ON s.id_country = cl.id_country AND cl.id_lang = ' . $langId . '
                LEFT JOIN
                ' . _DB_PREFIX_ . 'state st ON s.id_state = st.id_state ' . $join;

        $cond = '';
        if (pSQL(Tools::getValue('extra_search_type')) === 'selected') {
            if (isset(Tools::getValue('extra_search_params')['data'])) {
                if (Tools::getValue('extra_search_params')['type'] === 'selected') {
                    $cond .= " AND s.id_store IN (" . implode(', ', Tools::getValue('extra_search_params')['data']) . ")";
                } else {
                    $cond .= " AND s.id_store NOT IN (" . implode(', ', Tools::getValue('extra_search_params')['data']) . ")";
                }
            } elseif (Tools::getValue('extra_search_params')['type'] === 'selected') {
                $cond .= " AND 0";
            }
        } elseif (pSQL(Tools::getValue('extra_search_type')) === 'not-selected') {
            if (isset(Tools::getValue('extra_search_params')['data'])) {
                if (Tools::getValue('extra_search_params')['type'] === 'selected') {
                    $cond .= " AND s.id_store NOT IN (" . implode(', ', Tools::getValue('extra_search_params')['data']) . ")";
                } else {
                    $cond .= " AND s.id_store IN (" . implode(', ', Tools::getValue('extra_search_params')['data']) . ")";
                }
            } elseif (Tools::getValue('extra_search_params')['type'] === 'unselected') {
                $cond .= " AND 0";
            }
        }

        $search = Tools::getValue('search')['value'];
        if ($search || $search === '0') {
            $cond .= " AND (s.id_store LIKE '%" . $search . "%' OR ";
            $cond .= "s.`name` LIKE '%" . $search . "%' OR ";
            $cond .= "s.address1 LIKE '%" . $search . "%' OR ";
            $cond .= "s.city LIKE '%" . $search . "%' OR ";
            $cond .= "s.postcode LIKE '%" . $search . "%' OR ";
            $cond .= "s.phone LIKE '%" . $search . "%' OR ";
            $cond .= "cl.`name` LIKE '%" . $search . "%' OR ";
            $cond .= "st.`name` LIKE '%" . $search . "%')";
        }

        $ord = ' ORDER BY ';

        if (self::$getAll) {
            $ord .= 'id';
            $offset = 0;
            $limit = 10;
        } else {
            foreach (Tools::getValue('order') as $order) {
                $ord .= Tools::getValue('columns')[$order['column']]['data'] . ' ' . $order['dir'] . ', ';
            }
            $offset = pSQL(Tools::getValue('start'));
            $limit = pSQL(Tools::getValue('length'));
        }

        $ord = rtrim($ord, ', ');

        $sql .= $cond . $ord . ' LIMIT ' . $offset . ', ' . $limit;

        $data = DB::getInstance()->executeS($sql);

        $filtered = DB::getInstance()->getValue('SELECT FOUND_ROWS()');

        foreach ($data as &$val) {
            $val['logo'] = ImageManager::thumbnail(_PS_STORE_IMG_DIR_ . $val['id'] . '.jpg', 'store_mini_'
                    . $val['id'] . '_' . Context::getContext()->shop->id . '.jpg', 45, 'jpg');
        }

        $total = DB::getInstance()->getValue('SELECT COUNT(id_store) count FROM '
            . _DB_PREFIX_ . 'store');

        if (self::$getAll) {
            return [
                'recordsTotal' => $total,
                'recordsFiltered' => $filtered,
                'data' => $data
            ];
        }

        $response = array(
            'draw' => pSQL(Tools::getValue('draw')),
            'recordsTotal' => $total,
            'recordsFiltered' => $filtered,
            'data' => $data
        );
        die(json_encode($response));
    }

    public static function getProducts()
    {
        $context = Context::getContext();
        $sql = 'SELECT SQL_CALC_FOUND_ROWS * FROM (SELECT
                        p.id_product id,
                        pl.`name`,
                        p.reference,
                        cl.`name` category,
                        ROUND(p.price, 2) base_price,
                        ROUND(p.price * (1 + rate / 100) ,2) final_price,
                        sa.quantity,
                        active enabled ';
        if (self::$setInitial) {
            $sql .= ', 1 AS initial ';
        }
        $lang = $context->language->id;
        $shop = $context->shop->id;
        $table = '
                FROM ' . _DB_PREFIX_ . 'product p
                LEFT JOIN ' . _DB_PREFIX_ . 'product_lang pl ON p.id_product = pl.id_product AND pl.id_lang = ' . $lang . '
                LEFT JOIN ' . _DB_PREFIX_ . 'category_lang cl ON p.id_category_default = cl.id_category AND cl.id_lang = ' . $lang . '
                LEFT JOIN ' . _DB_PREFIX_ . 'stock_available sa ON p.id_product = sa.id_product AND sa.id_product_attribute = 0 AND sa.id_shop = ' . $shop . '
                LEFT JOIN (
                        SELECT rate, tr.id_tax_rules_group
                        FROM ' . _DB_PREFIX_ . 'tax t
                        LEFT JOIN ' . _DB_PREFIX_ . 'tax_rule tr ON t.id_tax = tr.id_tax GROUP BY tr.id_tax_rules_group) tmp
                    ON p.id_tax_rules_group = tmp.id_tax_rules_group
                WHERE 1 ';

        $cond = '';
        if (pSQL(Tools::getValue('extra_search_type')) === 'selected') {
            if (isset(Tools::getValue('extra_search_params')['data'])) {
                if (Tools::getValue('extra_search_params')['type'] === 'selected') {
                    $cond .= " AND p.id_product IN (" . implode(', ', Tools::getValue('extra_search_params')['data']) . ")";
                } else {
                    $cond .= " AND p.id_product NOT IN (" . implode(', ', Tools::getValue('extra_search_params')['data']) . ")";
                }
            } elseif (Tools::getValue('extra_search_params')['type'] === 'selected') {
                $cond .= " AND 0";
            }
        } elseif (pSQL(Tools::getValue('extra_search_type')) === 'not-selected') {
            if (isset(Tools::getValue('extra_search_params')['data'])) {
                if (Tools::getValue('extra_search_params')['type'] === 'selected') {
                    $cond .= " AND p.id_product NOT IN (" . implode(', ', Tools::getValue('extra_search_params')['data']) . ")";
                } else {
                    $cond .= " AND p.id_product IN (" . implode(', ', Tools::getValue('extra_search_params')['data']) . ")";
                }
            } elseif (Tools::getValue('extra_search_params')['type'] === 'unselected') {
                $cond .= " AND 0";
            }
        }
        $table .= $cond . ' GROUP BY p.id_product) tmp WHERE 1 ';
        $search = Tools::getValue('search')['value'];
        $cond2 = '';
        if ($search || $search === '0') {
            $cond2 .= " AND (`name` LIKE '%" . $search . "%' OR ";
            $cond2 .= "category LIKE '%" . $search . "%' OR ";
            $cond2 .= "reference LIKE '%" . $search . "%' OR ";
            $cond2 .= "base_price LIKE '%" . $search . "%' OR ";
            $cond2 .= "final_price LIKE '%" . $search . "%' OR ";
            $cond2 .= "quantity LIKE '%" . $search . "%' OR ";
            $cond2 .= "id LIKE '%" . $search . "%')";
        }

        $ord = ' ORDER BY ';

        if (self::$getAll) {
            $ord .= 'id';
            $offset = 0;
            $limit = 10;
        } else {
            foreach (Tools::getValue('order') as $order) {
                $ord .= Tools::getValue('columns')[$order['column']]['data'] . ' ' . $order['dir'] . ', ';
            }
            $offset = pSQL(Tools::getValue('start'));
            $limit = pSQL(Tools::getValue('length'));
        }

        $ord = rtrim($ord, ', ');

        $sql .= $table . $cond2 . $ord . ' LIMIT ' . $offset . ', ' . $limit;

        $data = DB::getInstance()->executeS($sql);

        $filtered = DB::getInstance()->getValue('SELECT FOUND_ROWS()');

        $total = DB::getInstance()->getValue('SELECT COUNT(id_product) `count` FROM `'
            . _DB_PREFIX_ . 'product`');

        $currency_symbol = $context->currency->sign;

        foreach ($data as &$val) {
            $cover = Product::getCover($val['id']);
            $join = Image::getImgFolderStatic($cover['id_image']) . (int) $cover['id_image'];
            $val['image'] = ImageManager::thumbnail(_PS_PROD_IMG_DIR_ . $join . '.jpg', 'product_mini_'
                    . $val['id'] . '_' . $shop . '.jpg', 45, 'jpg');

            $val['base_price'] = $val['base_price'] ? $currency_symbol . $val['base_price'] : '';
            $val['final_price'] = $val['final_price'] ? $currency_symbol . $val['final_price'] : '';
        }

        if (self::$getAll) {
            return [
                'recordsTotal' => $total,
                'recordsFiltered' => $filtered,
                'data' => $data
            ];
        }

        $response = array(
            'draw' => pSQL(Tools::getValue('draw')),
            'recordsTotal' => $total,
            'recordsFiltered' => $filtered,
            'data' => $data
        );
        die(json_encode($response));
    }

    public static function getCombinations()
    {
        $context = Context::getContext();
        $sql = 'SELECT SQL_CALC_FOUND_ROWS * FROM (SELECT
                        pa.id_product_attribute id,
                        p.id_product product_id,
                        pl.`name` product_name,
                        p.reference product_reference,
                        pa.reference combination_reference,
                        attributes.name_values combination,
                        sa.quantity combination_quantity';
        if (self::$setInitial) {
            $sql .= ', 1 AS initial ';
        }
        $lang = $context->language->id;
        $shop = $context->shop->id;
        $table = '
                FROM ' . _DB_PREFIX_ . 'product_attribute pa
                LEFT JOIN ' . _DB_PREFIX_ . 'product p ON pa.id_product = p.id_product
                LEFT JOIN ' . _DB_PREFIX_ . 'product_lang pl ON p.id_product = pl.id_product AND pl.id_lang = ' . $lang . ' AND pl.id_shop = ' . $shop . '
                LEFT JOIN ' . _DB_PREFIX_ . 'stock_available sa ON p.id_product = sa.id_product
                    AND sa.id_product_attribute = pa.id_product_attribute
                    AND sa.id_shop = ' . $shop . '
                LEFT JOIN (
                    SELECT
                        pac.id_product_attribute,
                        GROUP_CONCAT(CONCAT_WS(":", IFNULL(agl.name, ""), al.name) ORDER BY agl.name, al.name SEPARATOR ", ") name_values
                    FROM ' . _DB_PREFIX_ . 'product_attribute_combination pac
                    JOIN ' . _DB_PREFIX_ . 'attribute a ON pac.id_attribute = a.id_attribute
                    JOIN ' . _DB_PREFIX_ . 'attribute_lang al ON a.id_attribute = al.id_attribute AND al.id_lang = ' . $lang . '
                    JOIN ' . _DB_PREFIX_ . 'attribute_group ag ON a.id_attribute_group = ag.id_attribute_group
                    JOIN ' . _DB_PREFIX_ . 'attribute_group_lang agl ON a.id_attribute_group = agl.id_attribute_group AND agl.id_lang = ' . $lang . '
                    GROUP BY pac.id_product_attribute
                ) attributes ON pa.id_product_attribute = attributes.id_product_attribute
                WHERE 1 ';

        $cond = '';
        if (pSQL(Tools::getValue('extra_search_type')) === 'selected') {
            if (isset(Tools::getValue('extra_search_params')['data'])) {
                if (Tools::getValue('extra_search_params')['type'] === 'selected') {
                    $cond .= " AND pa.id_product_attribute IN (" . implode(', ', Tools::getValue('extra_search_params')['data']) . ")";
                } else {
                    $cond .= " AND pa.id_product_attribute NOT IN (" . implode(', ', Tools::getValue('extra_search_params')['data']) . ")";
                }
            } elseif (Tools::getValue('extra_search_params')['type'] === 'selected') {
                $cond .= " AND 0";
            }
        } elseif (pSQL(Tools::getValue('extra_search_type')) === 'not-selected') {
            if (isset(Tools::getValue('extra_search_params')['data'])) {
                if (Tools::getValue('extra_search_params')['type'] === 'selected') {
                    $cond .= " AND pa.id_product_attribute NOT IN (" . implode(', ', Tools::getValue('extra_search_params')['data']) . ")";
                } else {
                    $cond .= " AND pa.id_product_attribute IN (" . implode(', ', Tools::getValue('extra_search_params')['data']) . ")";
                }
            } elseif (Tools::getValue('extra_search_params')['type'] === 'unselected') {
                $cond .= " AND 0";
            }
        }
        $table .= $cond . ') tmp WHERE 1 ';
        $search = Tools::getValue('search')['value'];
        $cond2 = '';
        if ($search || $search === '0') {
            $cond2 .= " AND (product_name LIKE '%" . $search . "%' OR ";
            $cond2 .= "product_id LIKE '%" . $search . "%' OR ";
            $cond2 .= "product_reference LIKE '%" . $search . "%' OR ";
            $cond2 .= "combination LIKE '%" . $search . "%' OR ";
            $cond2 .= "combination_reference LIKE '%" . $search . "%' OR ";
            $cond2 .= "combination_quantity LIKE '%" . $search . "%' OR ";
            $cond2 .= "id LIKE '%" . $search . "%')";
        }

        $ord = ' ORDER BY ';

        if (self::$getAll) {
            $ord .= 'product_id, id';
            $offset = 0;
            $limit = 10;
        } else {
            foreach (Tools::getValue('order') as $order) {
                $ord .= Tools::getValue('columns')[$order['column']]['data'] . ' ' . $order['dir'] . ', ';
            }
            $offset = pSQL(Tools::getValue('start'));
            $limit = pSQL(Tools::getValue('length'));
        }

        $ord = rtrim($ord, ', ');

        $sql .= $table . $cond2 . $ord . ' LIMIT ' . $offset . ', ' . $limit;

        $data = DB::getInstance()->executeS($sql);

        $filtered = DB::getInstance()->getValue('SELECT FOUND_ROWS()');

        $total = DB::getInstance()->getValue('SELECT COUNT(id_product_attribute) `count` FROM `'
            . _DB_PREFIX_ . 'product_attribute`');

        foreach ($data as &$val) {
            $cover = Product::getCover($val['product_id']);
            $join = Image::getImgFolderStatic($cover['id_image']) . (int) $cover['id_image'];
            $val['product_image'] = ImageManager::thumbnail(_PS_PROD_IMG_DIR_ . $join . '.jpg', 'product_mini_'
                    . $val['product_id'] . '_' . $shop . '.jpg', 45, 'jpg');
        }

        if (self::$getAll) {
            return [
                'recordsTotal' => $total,
                'recordsFiltered' => $filtered,
                'data' => $data
            ];
        }

        $response = array(
            'draw' => pSQL(Tools::getValue('draw')),
            'recordsTotal' => $total,
            'recordsFiltered' => $filtered,
            'data' => $data
        );
        die(json_encode($response));
    }

    public static function getCategories()
    {
        $catIds = array();
        $categories = Category::getCategories(Context::getContext()->language->id, false, false);
        foreach ($categories as $cat) {
            $catIds[] = $cat['id_category'];
        }

        $tree = new HelperTreeCategories('data_export_orders_categories_tree', 'Filter by Category');
        $tree->setRootCategory((int) Category::getRootCategory()->id)
            ->setUseCheckBox(true)
            ->setUseSearch(true)
            ->setInputName('products_categories')
            ->setSelectedCategories($catIds);

        die($tree->render());
    }

    public static function getFeatures()
    {
        $lang = Context::getContext()->language->id;
        $sql = 'SELECT SQL_CALC_FOUND_ROWS * FROM (SELECT
                    CONCAT(IFNULL(fl.name, ""), "_#&_", IFNULL(fvl.value, ""), "_#&_", fv.custom) id,
                    fl.name feature_name,
                    fvl.value feature_value,
                    fv.custom';
        if (self::$setInitial) {
            $sql .= ', 1 AS initial ';
        }
        $sql .= '
                FROM ' . _DB_PREFIX_ . 'feature_value fv
                LEFT JOIN ' . _DB_PREFIX_ . 'feature_value_lang fvl ON fv.id_feature_value = fvl.id_feature_value AND fvl.id_lang = ' . $lang . '
                LEFT JOIN ' . _DB_PREFIX_ . 'feature f ON f.id_feature = fv.id_feature
                LEFT JOIN ' . _DB_PREFIX_ . 'feature_lang fl ON f.id_feature = fl.id_feature AND fl.id_lang = ' . $lang . '
                GROUP BY fl.name, fvl.value, fv.custom) features
                WHERE 1 ';

        $cond = '';
        if (pSQL(Tools::getValue('extra_search_type')) === 'selected') {
            if (isset(Tools::getValue('extra_search_params')['data'])) {
                if (Tools::getValue('extra_search_params')['type'] === 'selected') {
                    $cond .= " AND id IN (\"" . implode('", "', Tools::getValue('extra_search_params')['data']) . "\")";
                } else {
                    $cond .= " AND id NOT IN (\"" . implode('", "', Tools::getValue('extra_search_params')['data']) . "\")";
                }
            } elseif (Tools::getValue('extra_search_params')['type'] === 'selected') {
                $cond .= " AND 0";
            }
        } elseif (pSQL(Tools::getValue('extra_search_type')) === 'not-selected') {
            if (isset(Tools::getValue('extra_search_params')['data'])) {
                if (Tools::getValue('extra_search_params')['type'] === 'selected') {
                    $cond .= " AND id NOT IN (\"" . implode('", "', Tools::getValue('extra_search_params')['data']) . "\")";
                } else {
                    $cond .= " AND id IN (\"" . implode('", "', Tools::getValue('extra_search_params')['data']) . "\")";
                }
            } elseif (Tools::getValue('extra_search_params')['type'] === 'unselected') {
                $cond .= " AND 0";
            }
        }

        $search = Tools::getValue('search')['value'];
        if ($search || $search === '0') {
            $cond .= " AND (feature_name LIKE '%" . $search . "%' OR ";
            $cond .= "feature_value LIKE '%" . $search . "%')";
        }

        $ord = ' ORDER BY ';

        if (self::$getAll) {
            $ord .= 'id';
            $offset = 0;
            $limit = 10;
        } else {
            foreach (Tools::getValue('order') as $order) {
                $ord .= Tools::getValue('columns')[$order['column']]['data'] . ' ' . $order['dir'] . ', ';
            }
            $offset = pSQL(Tools::getValue('start'));
            $limit = pSQL(Tools::getValue('length'));
        }

        $ord = rtrim($ord, ', ');

        $sql .= $cond . $ord . ' LIMIT ' . $offset . ', ' . $limit;

        $data = DB::getInstance()->executeS($sql);

        $filtered = DB::getInstance()->getValue('SELECT FOUND_ROWS()');

        $total = DB::getInstance()->getValue('SELECT COUNT(feature_value) count FROM (
                SELECT
                    fvl.value feature_value
                FROM ' . _DB_PREFIX_ . 'feature_value fv
                LEFT JOIN ' . _DB_PREFIX_ . 'feature_value_lang fvl ON fv.id_feature_value = fvl.id_feature_value AND fvl.id_lang = ' . $lang . '
                LEFT JOIN ' . _DB_PREFIX_ . 'feature f ON f.id_feature = fv.id_feature
                LEFT JOIN ' . _DB_PREFIX_ . 'feature_lang fl ON f.id_feature = fl.id_feature AND fl.id_lang = ' . $lang . '
                GROUP BY fl.name, fvl.value, fv.custom) features');

        if (self::$getAll) {
            return [
                'recordsTotal' => $total,
                'recordsFiltered' => $filtered,
                'data' => $data
            ];
        }

        $response = array(
            'draw' => pSQL(Tools::getValue('draw')),
            'recordsTotal' => $total,
            'recordsFiltered' => $filtered,
            'data' => $data
        );
        die(json_encode($response));
    }
    
    public static function getFeaturesForFeatures()
    {
        $lang = Context::getContext()->language->id;
        $sql = 'SELECT SQL_CALC_FOUND_ROWS
                    f.id_feature as id,
                    fl.name,
                    f.position';
        if (self::$setInitial) {
            $sql .= ', 1 AS initial ';
        }
        $sql .= '
                FROM ' . _DB_PREFIX_ . 'feature f
                LEFT JOIN ' . _DB_PREFIX_ . 'feature_lang fl ON f.id_feature = fl.id_feature AND fl.id_lang = ' . $lang . '
                WHERE 1 ';

        $cond = '';
        if (pSQL(Tools::getValue('extra_search_type')) === 'selected') {
            if (isset(Tools::getValue('extra_search_params')['data'])) {
                if (Tools::getValue('extra_search_params')['type'] === 'selected') {
                    $cond .= " AND f.id_feature IN (" . implode(', ', Tools::getValue('extra_search_params')['data']) . ")";
                } else {
                    $cond .= " AND f.id_feature NOT IN (" . implode(', ', Tools::getValue('extra_search_params')['data']) . ")";
                }
            } elseif (Tools::getValue('extra_search_params')['type'] === 'selected') {
                $cond .= " AND 0";
            }
        } elseif (pSQL(Tools::getValue('extra_search_type')) === 'not-selected') {
            if (isset(Tools::getValue('extra_search_params')['data'])) {
                if (Tools::getValue('extra_search_params')['type'] === 'selected') {
                    $cond .= " AND f.id_feature NOT IN (" . implode(', ', Tools::getValue('extra_search_params')['data']) . ")";
                } else {
                    $cond .= " AND f.id_feature IN (" . implode(', ', Tools::getValue('extra_search_params')['data']) . ")";
                }
            } elseif (Tools::getValue('extra_search_params')['type'] === 'unselected') {
                $cond .= " AND 0";
            }
        }

        $search = Tools::getValue('search')['value'];
        if ($search || $search === '0') {
            $cond .= " AND (f.id_feature LIKE '%" . $search . "%' OR ";
            $cond .= "fl.name LIKE '%" . $search . "%' OR ";
            $cond .= "f.position LIKE '%" . $search . "%')";
        }

        $ord = ' ORDER BY ';

        if (self::$getAll) {
            $ord .= 'id';
            $offset = 0;
            $limit = 10;
        } else {
            foreach (Tools::getValue('order') as $order) {
                $ord .= Tools::getValue('columns')[$order['column']]['data'] . ' ' . $order['dir'] . ', ';
            }
            $offset = pSQL(Tools::getValue('start'));
            $limit = pSQL(Tools::getValue('length'));
        }

        $ord = rtrim($ord, ', ');

        $sql .= $cond . $ord . ' LIMIT ' . $offset . ', ' . $limit;

        $data = DB::getInstance()->executeS($sql);

        $filtered = DB::getInstance()->getValue('SELECT FOUND_ROWS()');

        $total = DB::getInstance()->getValue('SELECT COUNT(id_feature) count FROM ' . _DB_PREFIX_ . 'feature');

        if (self::$getAll) {
            return [
                'recordsTotal' => $total,
                'recordsFiltered' => $filtered,
                'data' => $data
            ];
        }

        $response = array(
            'draw' => pSQL(Tools::getValue('draw')),
            'recordsTotal' => $total,
            'recordsFiltered' => $filtered,
            'data' => $data
        );
        die(json_encode($response));
    }

    public static function getFeatureValues()
    {
        $lang = Context::getContext()->language->id;
        $sql = 'SELECT SQL_CALC_FOUND_ROWS
                    fv.id_feature_value as id,
                    fvl.`value`,
                    fv.custom,
                    fv.id_feature,
                    fl.name feature_name';
        if (self::$setInitial) {
            $sql .= ', 1 AS initial ';
        }
        $sql .= '
                FROM ' . _DB_PREFIX_ . 'feature_value fv
                LEFT JOIN ' . _DB_PREFIX_ . 'feature_value_lang fvl ON fv.id_feature_value = fvl.id_feature_value AND fvl.id_lang = ' . $lang . '
                LEFT JOIN ' . _DB_PREFIX_ . 'feature_lang fl ON fv.id_feature = fl.id_feature AND fl.id_lang = ' . $lang . '
                WHERE 1 ';

        $cond = '';
        if (pSQL(Tools::getValue('extra_search_type')) === 'selected') {
            if (isset(Tools::getValue('extra_search_params')['data'])) {
                if (Tools::getValue('extra_search_params')['type'] === 'selected') {
                    $cond .= " AND fv.id_feature_value IN (" . implode(', ', Tools::getValue('extra_search_params')['data']) . ")";
                } else {
                    $cond .= " AND fv.id_feature_value NOT IN (" . implode(', ', Tools::getValue('extra_search_params')['data']) . ")";
                }
            } elseif (Tools::getValue('extra_search_params')['type'] === 'selected') {
                $cond .= " AND 0";
            }
        } elseif (pSQL(Tools::getValue('extra_search_type')) === 'not-selected') {
            if (isset(Tools::getValue('extra_search_params')['data'])) {
                if (Tools::getValue('extra_search_params')['type'] === 'selected') {
                    $cond .= " AND fv.id_feature_value NOT IN (" . implode(', ', Tools::getValue('extra_search_params')['data']) . ")";
                } else {
                    $cond .= " AND fv.id_feature_value IN (" . implode(', ', Tools::getValue('extra_search_params')['data']) . ")";
                }
            } elseif (Tools::getValue('extra_search_params')['type'] === 'unselected') {
                $cond .= " AND 0";
            }
        }

        $search = Tools::getValue('search')['value'];
        if ($search || $search === '0') {
            $cond .= " AND (fv.id_feature_value LIKE '%" . $search . "%' OR ";
            $cond .= "fvl.`value` LIKE '%" . $search . "%' OR ";
            $cond .= "fv.custom LIKE '%" . $search . "%')";
        }

        $ord = ' ORDER BY ';

        if (self::$getAll) {
            $ord .= 'id';
            $offset = 0;
            $limit = 10;
        } else {
            foreach (Tools::getValue('order') as $order) {
                $ord .= Tools::getValue('columns')[$order['column']]['data'] . ' ' . $order['dir'] . ', ';
            }
            $offset = pSQL(Tools::getValue('start'));
            $limit = pSQL(Tools::getValue('length'));
        }

        $ord = rtrim($ord, ', ');

        $sql .= $cond . $ord . ' LIMIT ' . $offset . ', ' . $limit;

        $data = DB::getInstance()->executeS($sql);

        $filtered = DB::getInstance()->getValue('SELECT FOUND_ROWS()');

        $total = DB::getInstance()->getValue('SELECT COUNT(id_feature_value) count FROM ' . _DB_PREFIX_ . 'feature_value');

        if (self::$getAll) {
            return [
                'recordsTotal' => $total,
                'recordsFiltered' => $filtered,
                'data' => $data
            ];
        }

        $response = array(
            'draw' => pSQL(Tools::getValue('draw')),
            'recordsTotal' => $total,
            'recordsFiltered' => $filtered,
            'data' => $data
        );
        die(json_encode($response));
    }
    
    public static function getAttributeGroups()
    {
        $lang = Context::getContext()->language->id;
        $sql = 'SELECT SQL_CALC_FOUND_ROWS
                    ag.id_attribute_group as id,
                    agl.name,
                    agl.public_name,
                    ag.group_type,
                    ag.position';
        if (self::$setInitial) {
            $sql .= ', 1 AS initial ';
        }
        $sql .= '
                FROM ' . _DB_PREFIX_ . 'attribute_group ag
                LEFT JOIN ' . _DB_PREFIX_ . 'attribute_group_lang agl ON ag.id_attribute_group = agl.id_attribute_group AND agl.id_lang = ' . $lang . '
                WHERE 1 ';

        $cond = '';
        if (pSQL(Tools::getValue('extra_search_type')) === 'selected') {
            if (isset(Tools::getValue('extra_search_params')['data'])) {
                if (Tools::getValue('extra_search_params')['type'] === 'selected') {
                    $cond .= " AND ag.id_attribute_group IN (" . implode(', ', Tools::getValue('extra_search_params')['data']) . ")";
                } else {
                    $cond .= " AND ag.id_attribute_group NOT IN (" . implode(', ', Tools::getValue('extra_search_params')['data']) . ")";
                }
            } elseif (Tools::getValue('extra_search_params')['type'] === 'selected') {
                $cond .= " AND 0";
            }
        } elseif (pSQL(Tools::getValue('extra_search_type')) === 'not-selected') {
            if (isset(Tools::getValue('extra_search_params')['data'])) {
                if (Tools::getValue('extra_search_params')['type'] === 'selected') {
                    $cond .= " AND ag.id_attribute_group NOT IN (" . implode(', ', Tools::getValue('extra_search_params')['data']) . ")";
                } else {
                    $cond .= " AND ag.id_attribute_group IN (" . implode(', ', Tools::getValue('extra_search_params')['data']) . ")";
                }
            } elseif (Tools::getValue('extra_search_params')['type'] === 'unselected') {
                $cond .= " AND 0";
            }
        }

        $search = Tools::getValue('search')['value'];
        if ($search || $search === '0') {
            $cond .= " AND (ag.id_attribute_group LIKE '%" . $search . "%' OR ";
            $cond .= "agl.`name` LIKE '%" . $search . "%' OR ";
            $cond .= "agl.public_name LIKE '%" . $search . "%' OR ";
            $cond .= "ag.group_type LIKE '%" . $search . "%' OR ";
            $cond .= "ag.position LIKE '%" . $search . "%')";
        }

        $ord = ' ORDER BY ';

        if (self::$getAll) {
            $ord .= 'id';
            $offset = 0;
            $limit = 10;
        } else {
            foreach (Tools::getValue('order') as $order) {
                $ord .= Tools::getValue('columns')[$order['column']]['data'] . ' ' . $order['dir'] . ', ';
            }
            $offset = pSQL(Tools::getValue('start'));
            $limit = pSQL(Tools::getValue('length'));
        }

        $ord = rtrim($ord, ', ');

        $sql .= $cond . $ord . ' LIMIT ' . $offset . ', ' . $limit;

        $data = DB::getInstance()->executeS($sql);

        $filtered = DB::getInstance()->getValue('SELECT FOUND_ROWS()');

        $total = DB::getInstance()->getValue('SELECT COUNT(id_attribute_group) count FROM ' . _DB_PREFIX_ . 'attribute_group');

        if (self::$getAll) {
            return [
                'recordsTotal' => $total,
                'recordsFiltered' => $filtered,
                'data' => $data
            ];
        }

        $response = array(
            'draw' => pSQL(Tools::getValue('draw')),
            'recordsTotal' => $total,
            'recordsFiltered' => $filtered,
            'data' => $data
        );
        die(json_encode($response));
    }

    public static function getAttributeValues()
    {
        $lang = Context::getContext()->language->id;
        $sql = 'SELECT SQL_CALC_FOUND_ROWS
                    a.id_attribute as id,
                    al.`name`,
                    a.color,
                    a.position,
                    a.id_attribute_group,
                    agl.name attribute_group_name';
        if (self::$setInitial) {
            $sql .= ', 1 AS initial ';
        }
        $sql .= '
                FROM ' . _DB_PREFIX_ . 'attribute a
                LEFT JOIN ' . _DB_PREFIX_ . 'attribute_lang al ON a.id_attribute = al.id_attribute AND al.id_lang = ' . $lang . '
                LEFT JOIN ' . _DB_PREFIX_ . 'attribute_group_lang agl ON a.id_attribute_group = agl.id_attribute_group AND agl.id_lang = ' . $lang . '
                WHERE 1 ';

        $cond = '';
        if (pSQL(Tools::getValue('extra_search_type')) === 'selected') {
            if (isset(Tools::getValue('extra_search_params')['data'])) {
                if (Tools::getValue('extra_search_params')['type'] === 'selected') {
                    $cond .= " AND a.id_attribute IN (" . implode(', ', Tools::getValue('extra_search_params')['data']) . ")";
                } else {
                    $cond .= " AND a.id_attribute NOT IN (" . implode(', ', Tools::getValue('extra_search_params')['data']) . ")";
                }
            } elseif (Tools::getValue('extra_search_params')['type'] === 'selected') {
                $cond .= " AND 0";
            }
        } elseif (pSQL(Tools::getValue('extra_search_type')) === 'not-selected') {
            if (isset(Tools::getValue('extra_search_params')['data'])) {
                if (Tools::getValue('extra_search_params')['type'] === 'selected') {
                    $cond .= " AND a.id_attribute NOT IN (" . implode(', ', Tools::getValue('extra_search_params')['data']) . ")";
                } else {
                    $cond .= " AND a.id_attribute IN (" . implode(', ', Tools::getValue('extra_search_params')['data']) . ")";
                }
            } elseif (Tools::getValue('extra_search_params')['type'] === 'unselected') {
                $cond .= " AND 0";
            }
        }

        $search = Tools::getValue('search')['value'];
        if ($search || $search === '0') {
            $cond .= " AND (a.id_attribute LIKE '%" . $search . "%' OR ";
            $cond .= "al.`name` LIKE '%" . $search . "%' OR ";
            $cond .= "a.`color` LIKE '%" . $search . "%' OR ";
            $cond .= "a.id_attribute_group LIKE '%" . $search . "%' OR ";
            $cond .= "agl.name LIKE '%" . $search . "%' OR ";
            $cond .= "a.position LIKE '%" . $search . "%')";
        }

        $ord = ' ORDER BY ';

        if (self::$getAll) {
            $ord .= 'id';
            $offset = 0;
            $limit = 10;
        } else {
            foreach (Tools::getValue('order') as $order) {
                $ord .= Tools::getValue('columns')[$order['column']]['data'] . ' ' . $order['dir'] . ', ';
            }
            $offset = pSQL(Tools::getValue('start'));
            $limit = pSQL(Tools::getValue('length'));
        }

        $ord = rtrim($ord, ', ');

        $sql .= $cond . $ord . ' LIMIT ' . $offset . ', ' . $limit;

        $data = DB::getInstance()->executeS($sql);

        $filtered = DB::getInstance()->getValue('SELECT FOUND_ROWS()');

        $total = DB::getInstance()->getValue('SELECT COUNT(id_attribute) count FROM ' . _DB_PREFIX_ . 'attribute');

        if (self::$getAll) {
            return [
                'recordsTotal' => $total,
                'recordsFiltered' => $filtered,
                'data' => $data
            ];
        }

        $response = array(
            'draw' => pSQL(Tools::getValue('draw')),
            'recordsTotal' => $total,
            'recordsFiltered' => $filtered,
            'data' => $data
        );
        die(json_encode($response));
    }

    public static function getAttributes()
    {
        $lang = Context::getContext()->language->id;
        $sql = '
            SELECT SQL_CALC_FOUND_ROWS
                    a.id_attribute id,
                    ag.id_attribute_group group_id,
                    ag.group_type,
                    agl.name group_name,
                    al.name attribute_name ';
        if (self::$setInitial) {
            $sql .= ', 1 AS initial ';
        }
        $sql .= '
            FROM ' . _DB_PREFIX_ . 'attribute a
            LEFT JOIN ' . _DB_PREFIX_ . 'attribute_lang al ON a.id_attribute = al.id_attribute AND al.id_lang = ' . $lang . '
            LEFT JOIN ' . _DB_PREFIX_ . 'attribute_group ag ON a.id_attribute_group = ag.id_attribute_group
            LEFT JOIN ' . _DB_PREFIX_ . 'attribute_group_lang agl ON ag.id_attribute_group = agl.id_attribute_group AND agl.id_lang = ' . $lang . ' 
            WHERE 1 ';
        $cond = '';
        if (pSQL(Tools::getValue('extra_search_type')) === 'selected') {
            if (isset(Tools::getValue('extra_search_params')['data'])) {
                if (Tools::getValue('extra_search_params')['type'] === 'selected') {
                    $cond .= " AND a.id_attribute IN (" . implode(', ', Tools::getValue('extra_search_params')['data']) . ")";
                } else {
                    $cond .= " AND a.id_attribute NOT IN (" . implode(', ', Tools::getValue('extra_search_params')['data']) . ")";
                }
            } elseif (Tools::getValue('extra_search_params')['type'] === 'selected') {
                $cond .= " AND 0";
            }
        } elseif (pSQL(Tools::getValue('extra_search_type')) === 'not-selected') {
            if (isset(Tools::getValue('extra_search_params')['data'])) {
                if (Tools::getValue('extra_search_params')['type'] === 'selected') {
                    $cond .= " AND a.id_attribute NOT IN (" . implode(', ', Tools::getValue('extra_search_params')['data']) . ")";
                } else {
                    $cond .= " AND a.id_attribute IN (" . implode(', ', Tools::getValue('extra_search_params')['data']) . ")";
                }
            } elseif (Tools::getValue('extra_search_params')['type'] === 'unselected') {
                $cond .= " AND 0";
            }
        }

        $search = Tools::getValue('search')['value'];
        if ($search || $search === '0') {
            $cond .= " AND (ag.group_type LIKE '%" . $search . "%' OR ";
            $cond .= "agl.name LIKE '%" . $search . "%' OR ";
            $cond .= "a.id_attribute LIKE '%" . $search . "%' OR ";
            $cond .= "ag.id_attribute_group LIKE '%" . $search . "%' OR ";
            $cond .= "al.name LIKE '%" . $search . "%')";
        }

        $ord = ' ORDER BY ';

        if (self::$getAll) {
            $ord .= 'id';
            $offset = 0;
            $limit = 10;
        } else {
            foreach (Tools::getValue('order') as $order) {
                $ord .= Tools::getValue('columns')[$order['column']]['data'] . ' ' . $order['dir'] . ', ';
            }
            $offset = pSQL(Tools::getValue('start'));
            $limit = pSQL(Tools::getValue('length'));
        }

        $ord = rtrim($ord, ', ');

        $sql .= $cond . $ord . ' LIMIT ' . $offset . ', ' . $limit;

        $data = DB::getInstance()->executeS($sql);

        $filtered = DB::getInstance()->getValue('SELECT FOUND_ROWS()');

        $total = DB::getInstance()->getValue('SELECT COUNT(id_attribute) count FROM `'
            . _DB_PREFIX_ . 'attribute`');

        if (self::$getAll) {
            return [
                'recordsTotal' => $total,
                'recordsFiltered' => $filtered,
                'data' => $data
            ];
        }

        $response = array(
            'draw' => pSQL(Tools::getValue('draw')),
            'recordsTotal' => $total,
            'recordsFiltered' => $filtered,
            'data' => $data
        );
        die(json_encode($response));
    }

    public static function getScheduleEmails($module)
    {
        $sql = "
            SELECT SQL_CALC_FOUND_ROWS
                id_ipcatalogexport_email id,
                address,
                timestamp,
                template,
                active,
                entities,
                IF(b.`name` = 'catalog_default', '-- " . $module->l('Default', EIDataProvider::class) . " --', b.`name`) template_name";
        if (self::$setInitial) {
            $sql .= ', 1 AS initial ';
        }
        $sql .= " FROM " . _DB_PREFIX_ . "ipcatalogexport_email a
            LEFT JOIN " . _DB_PREFIX_ . "ipcatalogexport b ON a.template = b.`name`
            WHERE 1 ";

        $cond = '';
        $search = Tools::getValue('search')['value'];
        if ($search || $search === '0') {
            $cond .= " AND (`address` LIKE '%" . $search . "%' OR ";
            $cond .= "template LIKE '%" . $search . "%' OR ";
            $cond .= "id_ipcatalogexport_email LIKE '%" . $search . "%')";
        }

        $ord = ' ORDER BY ';

        if (self::$getAll) {
            $ord .= 'id';
            $offset = 0;
            $limit = 10;
        } else {
            foreach (Tools::getValue('order') as $order) {
                $ord .= Tools::getValue('columns')[$order['column']]['data'] . ' ' . $order['dir'] . ', ';
            }
            $offset = pSQL(Tools::getValue('start'));
            $limit = pSQL(Tools::getValue('length'));
        }

        $ord = rtrim($ord, ', ');

        $sql .= $cond . $ord . ' LIMIT ' . $offset . ', ' . $limit;

        $data = DB::getInstance()->executeS($sql);

        $filtered = DB::getInstance()->getValue('SELECT FOUND_ROWS()');

        $total = DB::getInstance()->getValue('SELECT COUNT(id_ipcatalogexport_email) cc
            FROM ' . _DB_PREFIX_ . 'ipcatalogexport_email');

        if (self::$getAll) {
            return [
                'recordsTotal' => $total,
                'recordsFiltered' => $filtered,
                'data' => $data
            ];
        }

        $response = array(
            'draw' => pSQL(Tools::getValue('draw')),
            'recordsTotal' => $total,
            'recordsFiltered' => $filtered,
            'data' => $data
        );

        die(json_encode($response));
    }

    public static function getScheduleFTPs($module)
    {
        $sql = "
            SELECT SQL_CALC_FOUND_ROWS
                id_ipcatalogexport_ftp id,
                type,
                IF(port = '' OR port IS NULL, url, CONCAT(url, ':', port)) url,
                username,
                password,
                timestamp,
                template,
                active,
                entities,
                IF(b.`name` = 'catalog_default', '-- " . $module->l('Default', EIDataProvider::class) . " --', b.`name`) template_name";
        if (self::$setInitial) {
            $sql .= ', 1 AS initial ';
        }
        $sql .= " FROM " . _DB_PREFIX_ . "ipcatalogexport_ftp a
            LEFT JOIN " . _DB_PREFIX_ . "ipcatalogexport b ON a.template = b.`name`
            WHERE 1 ";

        $cond = '';
        $search = Tools::getValue('search')['value'];
        if ($search || $search === '0') {
            $cond .= " AND (`url` LIKE '%" . $search . "%' OR ";
            $cond .= "username LIKE '%" . $search . "%' OR ";
            $cond .= "password LIKE '%" . $search . "%' OR ";
            $cond .= "template LIKE '%" . $search . "%' OR ";
            $cond .= "id_ipcatalogexport_ftp LIKE '%" . $search . "%')";
        }

        $ord = ' ORDER BY ';

        if (self::$getAll) {
            $ord .= 'id';
            $offset = 0;
            $limit = 10;
        } else {
            foreach (Tools::getValue('order') as $order) {
                $ord .= Tools::getValue('columns')[$order['column']]['data'] . ' ' . $order['dir'] . ', ';
            }
            $offset = pSQL(Tools::getValue('start'));
            $limit = pSQL(Tools::getValue('length'));
        }

        $ord = rtrim($ord, ', ');

        $sql .= $cond . $ord . ' LIMIT ' . $offset . ', ' . $limit;

        $data = DB::getInstance()->executeS($sql);

        $filtered = DB::getInstance()->getValue('SELECT FOUND_ROWS()');

        $total = DB::getInstance()->getValue('SELECT COUNT(id_ipcatalogexport_ftp) cc
            FROM ' . _DB_PREFIX_ . 'ipcatalogexport_ftp');

        if (self::$getAll) {
            return [
                'recordsTotal' => $total,
                'recordsFiltered' => $filtered,
                'data' => $data
            ];
        }

        $response = array(
            'draw' => pSQL(Tools::getValue('draw')),
            'recordsTotal' => $total,
            'recordsFiltered' => $filtered,
            'data' => $data
        );

        die(json_encode($response));
    }

    public static function getImportScheduleURLs($module, $initial = false)
    {
        $sql = "
            SELECT SQL_CALC_FOUND_ROWS
                id_ipcatalogimport_url id,
                url,
                template,
                active,
                entity,
                IF(b.`name` = 'catalog_default', '-- " . $module->l('Default', EIDataProvider::class) . " --', b.`name`) template_name";
        if ($initial) {
            $sql .= ', 1 AS initial ';
        }
        $sql .= " FROM " . _DB_PREFIX_ . "ipcatalogimport_url a
            LEFT JOIN " . _DB_PREFIX_ . "ipcatalogimport b ON a.template = b.`name`
            WHERE 1 ";

        $cond = '';
        $search = Tools::getValue('search')['value'];
        if ($search || $search === '0') {
            $cond .= " AND (`url` LIKE '%" . $search . "%' OR ";
            $cond .= "entity LIKE '%" . $search . "%' OR ";
            $cond .= "template LIKE '%" . $search . "%' OR ";
            $cond .= "id_ipcatalogimport_url LIKE '%" . $search . "%')";
        }

        $ord = ' ORDER BY ';

        if ($initial) {
            $ord .= 'id';
            $offset = 0;
            $limit = 10;
        } else {
            foreach (Tools::getValue('order') as $order) {
                $ord .= Tools::getValue('columns')[$order['column']]['data'] . ' ' . $order['dir'] . ', ';
            }
            $offset = pSQL(Tools::getValue('start'));
            $limit = pSQL(Tools::getValue('length'));
        }

        $ord = rtrim($ord, ', ');

        $sql .= $cond . $ord . ' LIMIT ' . $offset . ', ' . $limit;

        $data = DB::getInstance()->executeS($sql);
        $entities = EIHelper::allEntitiesUnordered($module);
        foreach ($data as &$d) {
            $d['translated_entity'] = $entities[$d['entity']]['plural'];
        }

        $filtered = DB::getInstance()->getValue('SELECT FOUND_ROWS()');

        $total = DB::getInstance()->getValue('SELECT COUNT(id_ipcatalogimport_url) cc
            FROM ' . _DB_PREFIX_ . 'ipcatalogimport_url');

        if ($initial) {
            return [
                'recordsTotal' => $total,
                'recordsFiltered' => $filtered,
                'data' => $data
            ];
        }

        $response = array(
            'draw' => pSQL(Tools::getValue('draw')),
            'recordsTotal' => $total,
            'recordsFiltered' => $filtered,
            'data' => $data
        );

        die(json_encode($response));
    }

    public static function getImportScheduleFTPs($module, $initial = false)
    {
        $sql = "
            SELECT SQL_CALC_FOUND_ROWS
                id_ipcatalogimport_ftp id,
                type,
                IF(port = '' OR port IS NULL, url, CONCAT(url, ':', port)) url,
                username,
                password,
                template,
                active,
                entities,
                IF(b.`name` = 'catalog_default', '-- " . $module->l('Default', EIDataProvider::class) . " --', b.`name`) template_name";
        if ($initial) {
            $sql .= ', 1 AS initial ';
        }
        $sql .= " FROM " . _DB_PREFIX_ . "ipcatalogimport_ftp a
            LEFT JOIN " . _DB_PREFIX_ . "ipcatalogimport b ON a.template = b.`name`
            WHERE 1 ";

        $cond = '';
        $search = Tools::getValue('search')['value'];
        if ($search || $search === '0') {
            $cond .= " AND (`url` LIKE '%" . $search . "%' OR ";
            $cond .= "username LIKE '%" . $search . "%' OR ";
            $cond .= "password LIKE '%" . $search . "%' OR ";
            $cond .= "template LIKE '%" . $search . "%' OR ";
            $cond .= "id_ipcatalogimport_ftp LIKE '%" . $search . "%')";
        }

        $ord = ' ORDER BY ';

        if ($initial) {
            $ord .= 'id';
            $offset = 0;
            $limit = 10;
        } else {
            foreach (Tools::getValue('order') as $order) {
                $ord .= Tools::getValue('columns')[$order['column']]['data'] . ' ' . $order['dir'] . ', ';
            }
            $offset = pSQL(Tools::getValue('start'));
            $limit = pSQL(Tools::getValue('length'));
        }

        $ord = rtrim($ord, ', ');

        $sql .= $cond . $ord . ' LIMIT ' . $offset . ', ' . $limit;

        $data = DB::getInstance()->executeS($sql);

        $filtered = DB::getInstance()->getValue('SELECT FOUND_ROWS()');

        $total = DB::getInstance()->getValue('SELECT COUNT(id_ipcatalogimport_ftp) cc
            FROM ' . _DB_PREFIX_ . 'ipcatalogimport_ftp');

        if ($initial) {
            return [
                'recordsTotal' => $total,
                'recordsFiltered' => $filtered,
                'data' => $data
            ];
        }

        $response = array(
            'draw' => pSQL(Tools::getValue('draw')),
            'recordsTotal' => $total,
            'recordsFiltered' => $filtered,
            'data' => $data
        );

        die(json_encode($response));
    }

    public static function getUploadedFiles($module)
    {
        $entity = Tools::getValue('entity');
        $files = [];
        foreach (new DirectoryIterator(dirname(__FILE__) . '/../import/' . $entity) as $fileInfo) {
            if (in_array($fileName = $fileInfo->getFilename(), ['.', '..', 'index.php'])) {
                continue;
            }
            $files[] = ['file' => $fileName, 'size' => self::formatSizeUnits($fileInfo->getSize())];
        }
//        $files = array_reverse($files);

        $filtered = $total = count($files);

        $response = array(
            'draw' => pSQL(Tools::getValue('draw')),
            'recordsTotal' => $total,
            'recordsFiltered' => $filtered,
            'data' => $files
        );

        die(json_encode($response));
    }

    public static function formatSizeUnits($bytes)
    {
        if ($bytes >= 1073741824) {
            $bytes = number_format($bytes / 1073741824, 2) . ' GB';
        } elseif ($bytes >= 1048576) {
            $bytes = number_format($bytes / 1048576, 2) . ' MB';
        } elseif ($bytes >= 1024) {
            $bytes = number_format($bytes / 1024, 2) . ' KB';
        } elseif ($bytes > 1) {
            $bytes = $bytes . ' bytes';
        } elseif ($bytes == 1) {
            $bytes = $bytes . ' byte';
        } else {
            $bytes = '0 bytes';
        }

        return $bytes;
    }

    public function getExportSetting($id = false)
    {
        if ((int)$id) {
            return DB::getInstance()->executeS('SELECT ice.name, ice.configuration,ice.datatables FROM ' . _DB_PREFIX_ . 'ipcatalogexport ice
                                                WHERE ice.id_ipcatalogexport=' . (int)$id);
        } else {
            return DB::getInstance()->executeS('SELECT ice.name, ice.configuration,ice.datatables FROM ' . _DB_PREFIX_ . 'ipcatalogexport ice');
        }
    }

    public function getImportSetting($id = false)
    {
        if ((int)$id) {
            return DB::getInstance()->executeS('SELECT ici.name, ici.configuration,ici.datatables FROM ' . _DB_PREFIX_ . 'ipcatalogimport ici
                                                WHERE ici.id_ipcatalogimport=' . (int)$id);
        } else {
            return DB::getInstance()->executeS('SELECT ici.name, ici.configuration,ici.datatables FROM ' . _DB_PREFIX_ . 'ipcatalogimport ici');
        }
    }
}
