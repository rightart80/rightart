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

class EIProductsExport
{

    public $module;
    protected $path;
    private $langId;
    private $fracPart;
    private $sql;
    private $selectedColumns;
    private $auto;

    public function __construct($module)
    {
        $this->module = $module;
        $this->context = Context::getContext();
        $this->exportDir = dirname(__FILE__) . '/../../export/';
    }

    private function getCategoryIdTree($id)
    {
        $sql = 'SELECT id_parent FROM ' . _DB_PREFIX_ . 'category WHERE id_category = ' . (int) $id;
        $parentCatId = Db::getInstance()->getValue($sql);
        if ($parentCatId != 1) {
            return $id . ' -> ' . $this->getCategoryIdTree($parentCatId);
        } else {
            return $id;
        }
    }

    private function getCategoryNameTree($id)
    {
        $sql = 'SELECT cl.name, c.id_parent FROM ' . _DB_PREFIX_ . 'category c
                LEFT JOIN ' . _DB_PREFIX_ . 'category_lang cl ON c.id_category = cl.id_category AND cl.id_lang = ' . $this->langId . '
                WHERE c.id_category = ' . (int) $id;
        $cat = Db::getInstance()->getRow($sql);

        if ($cat['id_parent'] != 1) {
            return $cat['name'] . ' -> ' . $this->getCategoryNameTree($cat['id_parent']);
        } else {
            return $cat['name'];
        }
    }

    private function getImageType($type)
    {
        if (method_exists('ImageType', 'getFormattedName')) {
            $iType = ImageType::getFormattedName($type);
        } else {
            $iType = ImageType::getFormatedName($type);
        }
        return $iType;
    }

    private function getImageLink($id_image)
    {
        $theme = ((Shop::isFeatureActive() && file_exists(_PS_PROD_IMG_DIR_ . Image::getImgFolderStatic($id_image) . $id_image . '-' . (int) Context::getContext()->shop->id_theme . '.jpg')) ? '-' . Context::getContext()->shop->id_theme : '');
        $uri_path = _THEME_PROD_DIR_ . Image::getImgFolderStatic($id_image) . $id_image . $theme . '.jpg';
        return $this->context->link->protocol_content . Tools::getMediaServer($uri_path) . $uri_path;
    }

    private function getProducts($getTotal = false)
    {
        Db::getInstance()->execute('SET SQL_BIG_SELECTS=1');
        Db::getInstance()->execute('SET SESSION group_concat_max_len = 1000000');
        
        // Columns that do not exist directly in the DB
        $absentColumns = array(
            'product_link' => 'product.id_product',
            'file_url' => 'product_download.filename',
            'cover_image' => 'image.cover_id',
            'cover_image_url' => 'image.cover_id',
            'image_urls' => 'image.ids',
            'urls_in_shop' => 'image.ids_in_shop',
            'target_type.name' => 'IF(product_shop.redirect_type LIKE "%-category", target_category.`name`, target_product.`name`)',
            'attachment_url' => 'attachment.id_attachment',
        );

        $del = !empty(Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS("SHOW COLUMNS FROM `" . _DB_PREFIX_ . "product_lang` LIKE 'delivery_in_stock'"));
        $low = !empty(Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS("SHOW COLUMNS FROM `" . _DB_PREFIX_ . "product_shop` LIKE 'low_stock_threshold'"));
        $newColumns = [
            'product.isbn' => !empty(Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS("SHOW COLUMNS FROM `" . _DB_PREFIX_ . "product` LIKE 'isbn'")),
            'product.mpn' => !empty(Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS("SHOW COLUMNS FROM `" . _DB_PREFIX_ . "product` LIKE 'mpn'")),
            'product_shop.show_condition' => !empty(Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS("SHOW COLUMNS FROM `" . _DB_PREFIX_ . "product_shop` LIKE 'show_condition'")),
            'product_lang.delivery_in_stock' => $del,
            'product_lang.delivery_out_stock' => $del,
            'product.additional_delivery_times' => !empty(Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS("SHOW COLUMNS FROM `" . _DB_PREFIX_ . "product` LIKE 'additional_delivery_times'")),
            'product_shop.low_stock_threshold' => $low,
            'product_shop.low_stock_alert' => $low,
        ];

        if ($this->fileType === 'csv' && isset($this->selectedColumns['cover_image'])) {
            unset($this->selectedColumns['cover_image']);
        }

        // Get the inputs and filters
        if ($this->auto) {
            $this->fracPart = (int) $this->inputs['decimals'] == -1 ? 6 : (int) $this->inputs['decimals'];
            $this->langId = $langId = (int) $this->inputs['language'];
            $shopId = (int) $this->inputs['shop'] ?: (int) Configuration::get('PS_SHOP_DEFAULT');
            $productsDate = $this->inputs['date'];
            $fromDate = pSQL($this->inputs['from_date']);
            $toDate = pSQL($this->inputs['to_date']);
            $this->sortWay = (int) $this->inputs['sort_way'] === 0 ? ' DESC' : ' ASC';
            $this->sort = pSQL($this->inputs['sort']);
            $this->multivalueSeparator = pSQL($this->inputs['multivalue_separator']) ?: ',';

            $categories = $this->inputs['categories'];
            $filterCategories = $this->inputs['category_whether_filter'];
            $categories_without = $this->inputs['category_without'];
            $availability = $this->inputs['availability'];
            $conditions = $this->inputs['conditions'];
            $visibility = $this->inputs['visibility'];
            $discount = $this->inputs['discount'];

            $features = pSQL(implode("','", $this->datatables['features']['data']));
            $featuresType = $this->datatables['features']['type'];
            $featuresWithout = $this->inputs['feature_without'];

            $carriers = pSQL(implode(',', $this->datatables['carriers']['data']));
            $carriersType = $this->datatables['carriers']['type'];
            $carriersWithout = $this->inputs['carrier_without'];

            $suppliers = pSQL(implode(',', $this->datatables['suppliers']['data']));
            $suppliersType = $this->datatables['suppliers']['type'];
            $suppliersWithout = $this->inputs['supplier_without'];

            $priceOperator = pSQL($this->inputs['price_operator']);
            $price = (float) $this->inputs['price'];

            $quantityOperator = pSQL($this->inputs['quantity_operator']);
            $quantity = (int) $this->inputs['quantity'];

            $products = pSQL(implode(',', $this->datatables['products']['data']));
            $productsType = $this->datatables['products']['type'];

            $manufacturerWithout = $this->inputs['manufacturer_without'];
            $manufacturers = pSQL(implode(',', $this->datatables['manufacturers']['data']));
            $manufacturersType = $this->datatables['manufacturers']['type'];
        } else {
            // Get the inputs and filters
            $this->fracPart = (int) Tools::getValue('decimals');
            $this->fracPart  = $this->fracPart == -1 ? 6 : $this->fracPart;
            $this->langId = $langId = (int) Tools::getValue('language');
            $shopId = (int) Tools::getValue('shop') ?: (int) Configuration::get('PS_SHOP_DEFAULT');
            $productsDate = Tools::getValue('date');
            $fromDate = pSQL(Tools::getValue('from_date'));
            $toDate = pSQL(Tools::getValue('to_date'));
            $this->sortWay = (int) Tools::getValue('sort_way') === 0 ? ' DESC' : ' ASC';
            $this->sort = pSQL(Tools::getValue('sort'));
            $this->multivalueSeparator = pSQL(Tools::getValue('multivalue_separator')) ?: ',';

            $categories = Tools::getValue('categories');
            $filterCategories = Tools::getValue('category_whether_filter');
            $categories_without = Tools::getValue('category_without');
            $availability = Tools::getValue('availability');
            $conditions = Tools::getValue('conditions');
            $visibility = Tools::getValue('visibility');
            $discount = Tools::getValue('discount');

            $features = implode("','", explode(',', pSQL(Tools::getValue('features_data'))));
            $featuresType = Tools::getValue('features_type');
            $featuresWithout = Tools::getValue('feature_without');

            $carriers = pSQL(Tools::getValue('carriers_data'));
            $carriersWithout = Tools::getValue('carrier_without');
            $carriersType = Tools::getValue('carriers_type');

            $suppliers = pSQL(Tools::getValue('suppliers_data'));
            $suppliersWithout = Tools::getValue('supplier_without');
            $suppliersType = Tools::getValue('suppliers_type');

            $priceOperator = pSQL(Tools::getValue('price_operator'));
            $price = (float) Tools::getValue('price');

            $quantityOperator = pSQL(Tools::getValue('quantity_operator'));
            $quantity = (int) Tools::getValue('quantity');

            $products = pSQL(Tools::getValue('products_data'));
            $productsType = Tools::getValue('products_type');

            $manufacturerWithout = Tools::getValue('manufacturer_without');
            $manufacturers = pSQL(Tools::getValue('manufacturers_data'));
            $manufacturersType = Tools::getValue('manufacturers_type');
        }
        
        // Build the SELECT SQL
        $this->sql = '
            SELECT ';
        foreach ($this->selectedColumns as $k => $col) {
            if (array_key_exists($k, $absentColumns)) {
                $this->sql .= "
                    {$absentColumns[$k]} `$col`, ";
            } elseif (array_key_exists($k, $newColumns) && !$newColumns[$k]) {
                $this->sql .= "
                    '' `$col`, ";
            } elseif ($k === 'product_price_tax_incl') {
                $this->sql .= "
                        ROUND(product.price * (1 + product_price_wt.rate / 100), " . $this->fracPart . ") `$col`, ";
            } elseif ($k === 'product_shop.unit_price') {
                $this->sql .= "
                        ROUND(IF(product.unit_price_ratio = 0, 0, product.price / product.unit_price_ratio), " . $this->fracPart . ") `$col`, ";
            } elseif ($k === 'product.cache_is_pack') {
                $this->sql .= "
                        IF(pack.id_product IS NULL, 0, 1) `$col`, ";
            } elseif ($this->fracPart !== -1 && (
                    strpos($k, 'ratio') !== false && $k != 'product_download.date_expiration' ||
                    strpos($k, 'price') !== false && $k != 'product_shop.show_price' ||
                    strpos($k, 'cost') !== false ||
                    strpos($k, 'width') !== false ||
                    strpos($k, 'weight') !== false ||
                    strpos($k, 'height') !== false ||
                    strpos($k, 'depth') !== false ||
                    strpos($k, 'ecotax') !== false)) {
                $this->sql .= "
                        ROUND($k, $this->fracPart) `$col`, ";
            } else {
                $this->sql .= "
                        $k `$col`, ";
            }
        }

        $this->sql = rtrim($this->sql, ', ');

        // Filter By Category
        $categoriesCond = $categoriesCond2 = '';
        if ($filterCategories === '1') {
            if ($categories) {
                $categoriesCond = ' WHERE id_category IN (' . implode(',', $categories) . ')';
                $categoriesCond2 .= 'category.id_product IS NOT NULL';
                if ($categories_without !== '0') {
                    $categoriesCond2 .= ' OR for_null_category.id_product IS NULL';
                }
                $categoriesCond2 = ' 
                    AND (' . $categoriesCond2 . ')';
            } else {
                if ($categories_without !== '0') {
                    $categoriesCond2 .= ' 
                        AND (for_null_category.id_product IS NULL)';
                }
            }
        }

        // Filter By Feature
        $featuresCond = $featuresCond2 = '';
        if ($features) {
            if ($featuresType === 'unselected') {
                $featuresCond = " WHERE CONCAT(fl.name, '_#&_', fvl.value, '_#&_', fv.custom) NOT IN ('" . $features . "')";
            } else {
                $featuresCond = " WHERE CONCAT(fl.name, '_#&_', fvl.value, '_#&_', fv.custom) IN ('" . $features . "')";
            }
            $featuresCond2 .= 'feature.id_product IS NOT NULL';
            if ($featuresWithout !== '0') {
                $featuresCond2 .= ' OR for_null_feature.id_product IS NULL';
            }
            $featuresCond2 = ' AND (' . $featuresCond2 . ')';
        } else {
            if ($featuresWithout !== '0' && $featuresType === 'selected') {
                $featuresCond2 .= ' AND (for_null_feature.id_product IS NULL)';
            } elseif ($featuresWithout === '0' && $featuresType === 'unselected') {
                $featuresCond2 .= ' AND (for_null_feature.id_product IS NOT NULL)';
            }
        }

        // Filter By Carrier
        $carriersCond = $carriersCond2 = '';
        if ($carriers) {
            if ($carriersType === 'unselected') {
                $carriersCond = ' AND id_carrier_reference NOT IN (' . $carriers . ')';
            } else {
                $carriersCond = ' AND id_carrier_reference IN (' . $carriers . ')';
            }
            $carriersCond2 .= 'carrier.id_product IS NOT NULL';
            if ($carriersWithout !== '0') {
                $carriersCond2 .= ' OR for_null_carrier.id_product IS NULL';
            }
            $carriersCond2 = ' 
                AND (' . $carriersCond2 . ')';
        } else {
            if ($carriersWithout !== '0' && $carriersType === 'selected') {
                $carriersCond2 .= ' 
                    AND (for_null_carrier.id_product IS NULL)';
            } elseif ($carriersWithout === '0' && $carriersType === 'unselected') {
                $carriersCond2 .= ' 
                    AND (for_null_carrier.id_product IS NOT NULL)';
            }
        }

        // Filter By Supplier
        $suppliersCond = $suppliersCond2 = '';
        if ($suppliers) {
            if ($suppliersType === 'unselected') {
                $suppliersCond = ' AND id_supplier NOT IN (' . $suppliers . ')';
            } else {
                $suppliersCond = ' AND id_supplier IN (' . $suppliers . ')';
            }
            $suppliersCond2 .= 'supplier.id_product IS NOT NULL';
            if ($suppliersWithout !== '0') {
                $suppliersCond2 .= ' OR for_null_supplier.id_product IS NULL';
            }
            $suppliersCond2 = ' 
                AND (' . $suppliersCond2 . ')';
        } else {
            if ($suppliersWithout !== '0' && $suppliersType === 'selected') {
                $suppliersCond2 .= ' 
                    AND (for_null_supplier.id_product IS NULL)';
            } elseif ($suppliersWithout === '0' && $suppliersType === 'unselected') {
                $suppliersCond2 .= ' 
                    AND (for_null_supplier.id_product IS NOT NULL)';
            }
        }

        $this->sql .= '
            FROM 
            ' . _DB_PREFIX_ . 'product product
                LEFT JOIN
            ' . _DB_PREFIX_ . 'product_shop product_shop ON product.id_product = product_shop.id_product
                    AND product_shop.id_shop = ' . $shopId . '
                LEFT JOIN
            ' . _DB_PREFIX_ . 'supplier default_supplier ON product.id_supplier = default_supplier.id_supplier
                LEFT JOIN
            ' . _DB_PREFIX_ . 'manufacturer manufacturer ON product.id_manufacturer = manufacturer.id_manufacturer
                LEFT JOIN
            ' . _DB_PREFIX_ . 'category_lang default_category ON product_shop.id_category_default = default_category.id_category
                    AND default_category.id_shop = ' . $shopId . '
                    AND default_category.id_lang = ' . $langId . '
                LEFT JOIN
            ' . _DB_PREFIX_ . 'product_lang product_lang ON product.id_product = product_lang.id_product
                    AND product_lang.id_shop = ' . $shopId . '
                    AND product_lang.id_lang = ' . $langId . '
                LEFT JOIN
            ' . _DB_PREFIX_ . 'shop shop ON product_shop.id_shop = shop.id_shop
                LEFT JOIN
            ' . _DB_PREFIX_ . 'shop_group shop_group ON shop.id_shop_group = shop_group.id_shop_group
                LEFT JOIN
            ' . _DB_PREFIX_ . 'product_sale product_sale ON product.id_product = product_sale.id_product
                LEFT JOIN
            ' . _DB_PREFIX_ . 'tax_rules_group tax_rules_group ON product_shop.id_tax_rules_group = tax_rules_group.id_tax_rules_group
                LEFT JOIN (
                    SELECT 
                        category_product.id_product,
                        GROUP_CONCAT(category_lang.id_category ORDER BY category_lang.id_category SEPARATOR "' . $this->multivalueSeparator . '") ids,
                        GROUP_CONCAT(category_lang.`name` ORDER BY category_lang.id_category SEPARATOR "' . $this->multivalueSeparator . '") names
                    FROM (SELECT DISTINCT id_product FROM ' . _DB_PREFIX_ . 'category_product' . $categoriesCond . ') cat_sub
                    JOIN ' . _DB_PREFIX_ . 'category_product category_product ON cat_sub.id_product = category_product.id_product
                    LEFT JOIN ' . _DB_PREFIX_ . 'category_lang category_lang ON category_product.id_category = category_lang.id_category
                        AND category_lang.id_shop = ' . $shopId . '
                        AND category_lang.id_lang = ' . $langId . '
                    GROUP BY category_product.id_product
                ) category ON product.id_product = category.id_product
                LEFT JOIN (
                    SELECT DISTINCT id_product FROM ' . _DB_PREFIX_ . 'category_product
                ) for_null_category ON product.id_product = for_null_category.id_product
                LEFT JOIN (
                    SELECT id_product_1, GROUP_CONCAT(id_product_2 SEPARATOR "' . $this->multivalueSeparator . '") ids, GROUP_CONCAT(pl.`name` SEPARATOR "' . $this->multivalueSeparator . '") `names`
                    FROM ' . _DB_PREFIX_ . 'accessory a
                    LEFT JOIN ' . _DB_PREFIX_ . 'product_lang pl ON a.id_product_2 = pl.id_product AND pl.id_shop = ' . $shopId . ' AND pl.id_lang = ' . $langId . '
                    GROUP BY id_product_1
                ) related_products ON product.id_product = related_products.id_product_1
                LEFT JOIN (
                    SELECT DISTINCT
                        fp.id_product,
                        GROUP_CONCAT(CONCAT(fl.`name`, "~", fvl.`value`, "~", fv.`custom`) ORDER BY f.position SEPARATOR "' . $this->multivalueSeparator . '") name_value_custom,
                        GROUP_CONCAT(CONCAT(fl.`name`, "~", fvl.`value`) ORDER BY f.position SEPARATOR "' . $this->multivalueSeparator . '") name_value,
                        GROUP_CONCAT(f.`position` ORDER BY f.position SEPARATOR "' . $this->multivalueSeparator . '") position,
                        GROUP_CONCAT(fv.`custom` ORDER BY f.position SEPARATOR "' . $this->multivalueSeparator . '") custom
                    FROM (
                        SELECT DISTINCT id_product 
                        FROM ' . _DB_PREFIX_ . 'feature_product fp
                        LEFT JOIN ' . _DB_PREFIX_ . 'feature_value fv ON fp.id_feature_value = fv.id_feature_value
                        LEFT JOIN ' . _DB_PREFIX_ . 'feature_lang fl ON fv.id_feature = fl.id_feature AND fl.id_lang = ' . $langId . '
                        LEFT JOIN ' . _DB_PREFIX_ . 'feature_value_lang fvl ON fv.id_feature_value = fvl.id_feature_value AND fvl.id_lang = ' . $langId
                        . $featuresCond . '
                    ) sub_feat
                    JOIN ' . _DB_PREFIX_ . 'feature_product fp ON fp.id_product = sub_feat.id_product
                    LEFT JOIN ' . _DB_PREFIX_ . 'feature f ON fp.id_feature = f.id_feature
                    LEFT JOIN ' . _DB_PREFIX_ . 'feature_lang fl ON fp.id_feature = fl.id_feature AND fl.id_lang = ' . $langId . '
                    LEFT JOIN ' . _DB_PREFIX_ . 'feature_value fv ON fp.id_feature_value = fv.id_feature_value
                    LEFT JOIN ' . _DB_PREFIX_ . 'feature_value_lang fvl ON fp.id_feature_value = fvl.id_feature_value AND fvl.id_lang = ' . $langId . '
                    GROUP BY fp.id_product
                ) feature ON product.id_product = feature.id_product
                LEFT JOIN (
                    SELECT DISTINCT id_product
                    FROM ' . _DB_PREFIX_ . 'feature_product
                ) for_null_feature ON product.id_product = for_null_feature.id_product
                LEFT JOIN ' . _DB_PREFIX_ . 'stock_available stock_available ON product.id_product = stock_available.id_product
                    AND stock_available.id_product_attribute = 0
                    ' . StockAvailable::addSqlShopRestriction(null, $shopId, 'stock_available') . '  
                LEFT JOIN (
                    SELECT 
                        prod_carr.id_product,
                        GROUP_CONCAT(carrier.id_carrier ORDER BY carrier.id_carrier SEPARATOR "' . $this->multivalueSeparator . '") ids,
                        GROUP_CONCAT(carrier.`name` ORDER BY carrier.id_carrier SEPARATOR "' . $this->multivalueSeparator . '") names
                    FROM (
                        SELECT DISTINCT id_product
                        FROM ' . _DB_PREFIX_ . 'product_carrier
                        WHERE id_shop = ' . $shopId . $carriersCond . '
                    ) sub_carr
                    JOIN ' . _DB_PREFIX_ . 'product_carrier prod_carr ON prod_carr.id_product = sub_carr.id_product
                    JOIN ' . _DB_PREFIX_ . 'carrier carrier ON prod_carr.id_carrier_reference = carrier.id_reference
                        AND carrier.deleted = 0
                        AND prod_carr.id_shop = ' . $shopId . '
                    GROUP BY prod_carr.id_product
                ) carrier ON product.id_product = carrier.id_product
                LEFT JOIN (
                    SELECT DISTINCT id_product
                    FROM ' . _DB_PREFIX_ . 'product_carrier
                    WHERE id_shop = ' . $shopId . '
                ) for_null_carrier ON product.id_product = for_null_carrier.id_product
                LEFT JOIN (
                    SELECT 
                        ps.id_product,
                        GROUP_CONCAT(ps.id_supplier SEPARATOR "' . $this->multivalueSeparator . '") id_supplier,
                        GROUP_CONCAT(IFNULL(s.`name`, "") SEPARATOR "' . $this->multivalueSeparator . '") `name`,
                        GROUP_CONCAT(ps.`product_supplier_reference` SEPARATOR "' . $this->multivalueSeparator . '") `product_supplier_reference`,
                        GROUP_CONCAT(ps.`product_supplier_price_te` SEPARATOR "' . $this->multivalueSeparator . '") `product_supplier_price_te`,
                        GROUP_CONCAT(ps.`id_currency` SEPARATOR "' . $this->multivalueSeparator . '") `id_currency`,
                        GROUP_CONCAT(IFNULL(c.`iso_code`, "") SEPARATOR "' . $this->multivalueSeparator . '") `currency_iso_code`,
                        GROUP_CONCAT(CONCAT(
                                    IFNULL(s.`name`, ""), ":", 
                                    ps.`product_supplier_reference`, ":", 
                                    ps.`product_supplier_price_te`, ":", 
                                    IFNULL(c.`iso_code`, "")) SEPARATOR "' . $this->multivalueSeparator . '") `name_ref_price_iso`
                    FROM (
                        SELECT DISTINCT id_product
                        FROM ' . _DB_PREFIX_ . 'product_supplier
                        WHERE id_product_attribute = 0' . $suppliersCond . '
                    ) sub_supp
                    JOIN ' . _DB_PREFIX_ . 'product_supplier ps ON sub_supp.id_product = ps.id_product
                    LEFT JOIN ' . _DB_PREFIX_ . 'supplier s ON ps.id_supplier = s.id_supplier
                    LEFT JOIN ' . _DB_PREFIX_ . 'currency c ON ps.id_currency = c.id_currency
                    WHERE ps.id_product_attribute = 0
                    GROUP BY id_product
                ) supplier ON supplier.id_product = product.id_product
                LEFT JOIN (
                    SELECT DISTINCT id_product
                    FROM ' . _DB_PREFIX_ . 'product_supplier
                    WHERE id_product_attribute = 0
                ) for_null_supplier ON product.id_product = for_null_supplier.id_product
                LEFT JOIN ' . _DB_PREFIX_ . 'specific_price_priority specific_price_priority ON specific_price_priority.id_product = product.id_product
                LEFT JOIN (
                    SELECT pt.id_product, GROUP_CONCAT(t.name SEPARATOR "' . $this->multivalueSeparator . '") tags
                    FROM ' . _DB_PREFIX_ . 'product_tag pt
                    JOIN ' . _DB_PREFIX_ . 'tag t ON pt.id_tag = t.id_tag AND t.id_lang = pt.id_lang
                    WHERE t.id_lang = ' . $langId . '
                    GROUP BY pt.id_product
                ) product_tag ON product.id_product = product_tag.id_product
                LEFT JOIN (
                    SELECT MAX(t.rate) rate, tr.id_tax_rules_group
                    FROM ' . _DB_PREFIX_ . 'tax t
                    JOIN ' . _DB_PREFIX_ . 'tax_rule tr ON t.id_tax = tr.id_tax
                    WHERE tr.id_country = ' . (int) $this->context->country->id . ' AND tr.id_state = 0
                    GROUP BY tr.id_tax_rules_group
                ) product_price_wt ON product.id_tax_rules_group = product_price_wt.id_tax_rules_group
                LEFT JOIN ' . _DB_PREFIX_ . 'product_download product_download ON product.id_product = product_download.id_product
                LEFT JOIN (
                    SELECT
                        p.id_product_pack id_product,
                        GROUP_CONCAT(p.id_product_item SEPARATOR "' . $this->multivalueSeparator . '") product_ids,
                        GROUP_CONCAT(pl.`name` SEPARATOR "' . $this->multivalueSeparator . '") product_names,
                        GROUP_CONCAT(p.quantity SEPARATOR "' . $this->multivalueSeparator . '") quantity
                    FROM ' . _DB_PREFIX_ . 'pack p
                    LEFT JOIN ' . _DB_PREFIX_ . 'product_lang pl ON p.id_product_item = pl.id_product
                        AND pl.id_shop = ' . $shopId . '
                        AND pl.id_lang = ' . $langId . '
                    WHERE p.id_product_attribute_item = 0
                    GROUP BY p.id_product_pack
                ) pack ON product.id_product = pack.id_product
                LEFT JOIN (
                    SELECT 
                        i.id_product,
                        GROUP_CONCAT(IF(i.id_image = 0, "", i.id_image) ORDER BY i.position SEPARATOR "' . $this->multivalueSeparator . '") ids,
                        GROUP_CONCAT(ish.id_image ORDER BY i.position SEPARATOR "' . $this->multivalueSeparator . '") ids_in_shop,
                        GROUP_CONCAT(i.`position` ORDER BY i.position SEPARATOR "' . $this->multivalueSeparator . '") `positions`,
                        GROUP_CONCAT(il.`legend` ORDER BY i.position SEPARATOR "' . $this->multivalueSeparator . '") `texts`,
                        GROUP_CONCAT(IF(i.cover = 1, i.id_image, NULL) ORDER BY i.position SEPARATOR "' . $this->multivalueSeparator . '") cover_id
                    FROM ' . _DB_PREFIX_ . 'image i
                    LEFT JOIN ' . _DB_PREFIX_ . 'image_shop ish ON ish.id_image = i.id_image AND ish.id_shop = ' . $shopId . '
                    LEFT JOIN ' . _DB_PREFIX_ . 'image_lang il ON i.id_image = il.id_image AND il.id_lang = ' . $langId . '
                    GROUP BY i.id_product
                ) image ON product.id_product = image.id_product
                LEFT JOIN (
                    SELECT
                        cf.id_product,
                        GROUP_CONCAT(CONCAT(`name`, ":", `type`, ":", required) SEPARATOR "' . $this->multivalueSeparator . '") name_type_req
                    FROM ' . _DB_PREFIX_ . 'customization_field cf
                    LEFT JOIN ' . _DB_PREFIX_ . 'customization_field_lang cfl ON cf.id_customization_field = cfl.id_customization_field 
                        AND cfl.id_lang =  ' . $langId . ' AND cfl.id_shop = ' . $shopId . '
                    GROUP BY cf.id_product
                ) customization_field ON product.id_product = customization_field.id_product
                LEFT JOIN (
                    SELECT
                        pa.id_product,
                        GROUP_CONCAT(pa.id_attachment SEPARATOR "' . $this->multivalueSeparator . '") id_attachment,
                        GROUP_CONCAT(a.`file` SEPARATOR "' . $this->multivalueSeparator . '") `file`,
                        GROUP_CONCAT(a.file_name SEPARATOR "' . $this->multivalueSeparator . '") file_name,
                        GROUP_CONCAT(a.file_size SEPARATOR "' . $this->multivalueSeparator . '") file_size,
                        GROUP_CONCAT(a.mime SEPARATOR "' . $this->multivalueSeparator . '") mime,
                        GROUP_CONCAT(al.`name` SEPARATOR "' . $this->multivalueSeparator . '") `name`,
                        GROUP_CONCAT(al.description SEPARATOR "' . $this->multivalueSeparator . '") description
                    FROM ' . _DB_PREFIX_ . 'product_attachment pa
                    LEFT JOIN ' . _DB_PREFIX_ . 'attachment a ON pa.id_attachment = a.id_attachment
                    LEFT JOIN ' . _DB_PREFIX_ . 'attachment_lang al ON a.id_attachment = al.id_attachment AND al.id_lang = ' . $langId . '
                    GROUP BY pa.id_product
                ) attachment ON product.id_product = attachment.id_product
                LEFT JOIN (
                    SELECT
                        wpl.id_product,
                        GROUP_CONCAT(CONCAT(w.`reference`, ":", w.`name`, ":", wpl.location) SEPARATOR "' . $this->multivalueSeparator . '") name_ref_loc
                    FROM ' . _DB_PREFIX_ . 'warehouse_product_location wpl
                    LEFT JOIN ' . _DB_PREFIX_ . 'warehouse w ON wpl.id_warehouse = w.id_warehouse 
                    WHERE wpl.id_product_attribute = 0
                    GROUP BY wpl.id_product
                ) warehouse ON product.id_product = warehouse.id_product
                ';

        if (isset($this->selectedColumns['target_type.name'])) {
            $this->sql .= '
                    LEFT JOIN ' . _DB_PREFIX_ . 'product_lang target_product ON product_shop.id_type_redirected = target_product.id_product
                        AND target_product.id_lang =  ' . $langId . ' AND target_product.id_shop = ' . $shopId . '
                    LEFT JOIN ' . _DB_PREFIX_ . 'category_lang target_category ON product_shop.id_type_redirected = target_category.id_category
                        AND target_category.id_lang =  ' . $langId . ' AND target_category.id_shop = ' . $shopId . '
            ';
        } elseif (isset($this->selectedColumns['target_product.name'])) {
            $this->sql .= '
                    LEFT JOIN ' . _DB_PREFIX_ . 'product_lang target_product ON product_shop.id_product_redirected = target_product.id_product
                        AND target_product.id_lang =  ' . $langId . ' AND target_product.id_shop = ' . $shopId . '
            ';
        }

        $this->sql .= '
                WHERE 1
            ';

        // Filter By Category
        $this->sql .= $categoriesCond2;

        // Filter By Feature
        $this->sql .= $featuresCond2;

        // Filter By Carrier
        $this->sql .= $carriersCond2;

        // Filter By Supplier
        $this->sql .= $suppliersCond2;

        // Filter By Date
        $date = 'date_add';
        if ($productsDate === 'this_month') {
            $this->sql .= " 
                AND product.$date >= '" . date("Y-m-d", strtotime("first day of this month")) . "'";
            $this->sql .= " 
                AND product.$date < '" . date("Y-m-d", strtotime("first day of next month")) . "'";
        } elseif ($productsDate === 'last_month') {
            $this->sql .= " 
                AND product.$date >= '" . date("Y-m-d", strtotime("first day of previous month")) . "'";
            $this->sql .= " 
                AND product.$date < '" . date("Y-m-d", strtotime("first day of this month")) . "'";
        } elseif ($productsDate === 'this_week') {
            $start = date('N') == 1 ? date('Y-m-d') : date('Y-m-d', strtotime('last monday'));
            $end = date('Y-m-d', strtotime('this sunday'));
            $this->sql .= " 
                AND product.$date >= '" . $start . "'";
            $this->sql .= " 
                AND product.$date <= '" . $end . "'";
        } elseif ($productsDate === 'last_week') {
            $start = date('N') == 1 ? date('Y-m-d', strtotime('last monday')) : date('Y-m-d', strtotime('last week monday'));
            $end = date('Y-m-d', strtotime('last sunday'));
            $this->sql .= " 
                AND product.$date >= '" . $start . "'";
            $this->sql .= " 
                AND product.$date <= '" . $end . "'";
        } elseif ($productsDate === 'today') {
            $this->sql .= " 
                AND product.$date >= '" . date("Y-m-d", strtotime("today")) . "'";
            $this->sql .= " 
                AND product.$date < '" . date("Y-m-d", strtotime("tomorrow")) . "'";
        } elseif ($productsDate === 'last_24_hours') {
            $this->sql .= " 
                AND product.$date >= '" . date('Y-m-d H:i:s', strtotime("-1 day")) . "'";
            $this->sql .= " 
                AND product.$date < '" . date('Y-m-d H:i:s') . "'";
        } elseif ($productsDate === 'yesterday') {
            $this->sql .= " 
                AND product.$date >= '" . date("Y-m-d", strtotime("yesterday")) . "'";
            $this->sql .= " 
                AND product.$date < '" . date("Y-m-d", strtotime("today")) . "'";
        } elseif ($productsDate === 'select_date') {
            if ($fromDate) {
                $this->sql .= " 
                    AND product.$date >= '" . $fromDate . "'";
            }
            if ($toDate) {
                $this->sql .= " 
                    AND product.$date < '" . $toDate . "'";
            }
        }

        // Filter By Availability
        $availabilityCond = '';
        if ($availability === 'active') {
            $availabilityCond = '
                AND product.active = 1 
                ';
        } elseif ($availability === 'inactive') {
            $availabilityCond = '
                AND product.active = 0 
                ';
        }
        $this->sql .= $availabilityCond;

        // Filter By Condition
        $conditionCond = '';
        foreach ($conditions as $key => $cond) {
            if ($cond === '1') {
                $conditionCond .= "'" . $key . "',";
            }
        }
        if ($conditionCond) {
            $conditionCond = ' 
                AND (product.condition IN (' . rtrim($conditionCond, ',') . '))';
        }
        $this->sql .= $conditionCond;

        // Filter By Visibility
        $visibilityCond = '';
        foreach ($visibility as $key => $vis) {
            if ($vis === '1') {
                $visibilityCond .= "'" . $key . "',";
            }
        }
        if ($visibilityCond) {
            $visibilityCond = ' 
                AND (product.visibility IN (' . rtrim($visibilityCond, ',') . '))';
            $this->sql .= $visibilityCond;
        }

        // Filter By Price
        if ($priceOperator !== 'none' && ($price || $price == '0')) {
            $this->sql .= '
                AND product_shop.price ' . EIHelper::getOperator($priceOperator) . $price;
        }

        // Filter By Quantity
        if ($quantityOperator !== 'none' && ($quantity || $quantity == '0')) {
            $this->sql .= '
                AND stock_available.quantity ' . EIHelper::getOperator($quantityOperator) . $quantity;
        }
        
        // Filter By Discount
        $discountCond = '';
        if ($discount === 'discounted') {
            $discountCond = '
                AND EXISTS (SELECT *
                    FROM ' . _DB_PREFIX_ . 'specific_price
                    WHERE id_product = product.id_product
                        AND id_product_attribute = 0
                        AND id_shop = ' . $shopId . '
                        AND (`from` < NOW() OR `from` = "0000-00-00 00:00:00") 
                        AND (`to` > NOW() OR `to` = "0000-00-00 00:00:00"))
                ';
        } elseif ($discount === 'nondiscounted') {
            $discountCond = '
                AND NOT EXISTS (SELECT *
                    FROM ' . _DB_PREFIX_ . 'specific_price
                    WHERE id_product = product.id_product
                        AND id_product_attribute = 0
                        AND id_shop = ' . $shopId . '
                        AND (`from` < NOW() OR `from` = "0000-00-00 00:00:00") 
                        AND (`to` > NOW() OR `to` = "0000-00-00 00:00:00"))
                ';
        }
        $this->sql .= $discountCond;

        // Filter By Product
        $products_cond = '';
        if ($products) {
            if ($productsType === 'unselected') {
                $products_cond = 'product.id_product NOT IN (' . $products . ')';
            } else {
                $products_cond = 'product.id_product IN (' . $products . ')';
            }
        }
        if ($products_cond) {
            $this->sql .= ' 
                            AND (' . $products_cond . ') ';
        }

        // Filter By Manufacturer
        $manufacturerCond = '';
        if ($manufacturers) {
            if ($manufacturersType === 'unselected') {
                $manufacturerCond = 'product.id_manufacturer NOT IN (' . $manufacturers . ')';
                if ($manufacturerWithout === '0') {
                    if ($manufacturerCond) {
                        $manufacturerCond .= ' AND ';
                    }
                    $manufacturerCond .= 'product.id_manufacturer IS NOT NULL AND product.id_manufacturer != 0';
                }
            } else {
                $manufacturerCond = 'product.id_manufacturer IN (' . $manufacturers . ')';
                if ($manufacturerWithout !== '0') {
                    if ($manufacturerCond) {
                        $manufacturerCond .= ' OR ';
                    }
                    $manufacturerCond .= 'product.id_manufacturer IS NULL OR product.id_manufacturer = 0';
                }
            }
        } elseif ($manufacturerWithout === '1' && $manufacturersType === 'selected') {
            $manufacturerCond .= 'product.id_manufacturer IS NULL OR product.id_manufacturer = 0';
        } elseif ($manufacturerWithout === '0' && $manufacturersType === 'unselected') {
            $manufacturerCond .= 'product.id_manufacturer IS NOT NULL AND product.id_manufacturer != 0';
        }

        if ($manufacturerCond) {
            $this->sql .= ' 
                        AND (' . $manufacturerCond . ') ';
        }

        // Sort By ...
        $this->sql .= ' ORDER BY ' . $this->sort . $this->sortWay;
        if ($this->sort !== 'product.id_product') {
            $this->sql .= ', product.id_product ASC';
        }

        if (!$getTotal) {
            $this->sql .= ' LIMIT ' . $this->offset . ', ' . $this->limit;
        }

//        die($this->sql);
        return Db::getInstance()->executeS($this->sql);
    }

    public function run($auto = null)
    {
        $this->auto = $auto;
        $this->encl = '"';

        if ($auto) {
            $this->inputs = $auto['config']['inputs'];

            $this->fileType = $this->inputs['as'];
            $docName = $this->inputs['doc_name'] ?: $this->module->l('Products', 'EIProductsExport');

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

            $this->products = $this->getProducts();

            if (!$this->products && Configuration::getGlobalValue('IPE_SCHDL_DNSEM')) {
                return;
            }
        } else {
            $this->fileType = Tools::getValue('as');
            $docName = Tools::getValue('doc_name') ?: $this->module->l('Products', 'EIProductsExport');

            if(!Tools::isSubmit('selectedColumns')) {
                throw new PrestaShopException($this->module->l('Not all inputs were accepted by your server. Please increase the \'max_input_vars\' variable in your PHP configuration.', 'EIProductsExport'));
            }
            
            $this->selectedColumns = json_decode(Tools::getValue('selectedColumns'), true);
            if (!$this->selectedColumns) {
                throw new PrestaShopException($this->module->l('Please select at least one column to export.', 'EIProductsExport'));
            }

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

            $this->products = $this->getProducts();
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
        $doneCount += $this->products ? count($this->products) : 0;
        
        $results['isFinished'] = $doneCount < $this->limit;

        $results['doneCount'] = $this->offset + $doneCount;
        if ($this->offset === 0) {
            $total = $this->getProducts(true);
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
                if ($this->products) {
                    fputcsv($handle, array_keys($this->products[0]), $this->dlm, $this->encl);
                } else {
                    fputcsv($handle, [$this->module->l('No Data', 'EIProductsExport')], $this->dlm, $this->encl);
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
        foreach ($this->products as $value) {
            if (!empty($value[$this->selectedColumns['product_link']])) {
                $value[$this->selectedColumns['product_link']] = $this->context->link->getProductLink(
                    $value[$this->selectedColumns['product_link']],
                    null,
                    null,
                    null,
                    $this->langId
                );
            }
            if (!empty($value[$this->selectedColumns['file_url']])) {
                $value[$this->selectedColumns['file_url']] = $this->context->link->getModuleLink(
                    'ipcatalogexportimport',
                    'getFile',
                    array('file' => $value[$this->selectedColumns['file_url']])
                );
            }
            if (!empty($value[$this->selectedColumns['attachment_url']])) {
                $link = '';
                foreach (explode($this->multivalueSeparator, $value[$this->selectedColumns['attachment_url']]) as $v) {
                    $link .= $this->context->link->getModuleLink(
                        'ipcatalogexportimport',
                        'getAttachment',
                        array('id_attachment' => $v)
                    ) . $this->multivalueSeparator;
                }
                $value[$this->selectedColumns['attachment_url']] = rtrim($link, $this->multivalueSeparator);
            }
            if (!empty($value[$this->selectedColumns['cover_image_url']])) {
                $value[$this->selectedColumns['cover_image_url']] = $this->getImageLink($value[$this->selectedColumns['cover_image_url']]);
            }
            if (!empty($value[$this->selectedColumns['image_urls']])) {
                $link = '';
                foreach (explode($this->multivalueSeparator, $value[$this->selectedColumns['image_urls']]) as $v) {
                    $link .= $this->getImageLink($v) . $this->multivalueSeparator;
                }
                $value[$this->selectedColumns['image_urls']] = rtrim($link, $this->multivalueSeparator);
            }
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
            $excelColumns = EIHelper::createColumnsArray(count($this->products[0]));
        } else {
            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();

            // Set document properties
            $spreadsheet->getProperties()->setCreator('Tehran Alishov')
                ->setLastModifiedBy('Tehran Alishov')
                ->setTitle('Office 2007 XLSX Products Document')
                ->setSubject('Office 2007 XLSX Products Document')
                ->setDescription('Products document for Office 2007 XLSX, generated using PHP classes.')
                ->setKeywords('office 2007 openxml php')
                ->setCategory('Products result file');
            $spreadsheet->setActiveSheetIndex(0);

            if (empty($this->products)) {
                $sheet->setCellValue('A1', $this->module->l('No Data', 'EIProductsExport'));
            } else {
                $excelColumns = EIHelper::createColumnsArray(count($this->products[0]));
                $sheet->getDefaultColumnDimension()->setWidth(21);
                if (isset($this->selectedColumns['cover_image'])) {
                    $sheet->getDefaultRowDimension()->setRowHeight(42);
                } else {
                    $sheet->getDefaultRowDimension()->setRowHeight(30);
                }

                $spreadsheet->getDefaultStyle()->getAlignment()
                    ->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER)
                    ->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);

                $sheet->getStyle('A1:' . end($excelColumns) . (count($this->products)))
                    ->getAlignment()->setWrapText(true);

                $sheet->getStyle('A1:' . end($excelColumns) . '1')
                    ->getFont()->setBold(true);
                $sheet->getStyle('A1:' . end($excelColumns) . '1')
                    ->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                    ->getStartColor()->setARGB('FFDCF0FF');
                $sheet->getStyle('A1:' . end($excelColumns) . '1')->getBorders()
                    ->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);

                // Rename worksheet
                $sheet->setTitle($this->module->l('Products', 'EIProductsExport'));

                $headers = array_keys($this->products[0]);
                foreach ($headers as $key => $header) {
                    $sheet->setCellValue($excelColumns[$key] . '1', $header);
                }
                
                if (isset($this->selectedColumns['cover_image'])) {
                    $sheet->getColumnDimension($excelColumns[array_search($this->selectedColumns['cover_image'], $headers)])->setWidth(9);
                }
                if (isset($this->selectedColumns['feature.name_value'])) {
                    $sheet->getColumnDimension($excelColumns[array_search($this->selectedColumns['feature.name_value'], $headers)])->setWidth(25);
                }
                if (isset($this->selectedColumns['product_link'])) {
                    $sheet->getColumnDimension($excelColumns[array_search($this->selectedColumns['product_link'], $headers)])->setWidth(30);
                }
                if (isset($this->selectedColumns['cover_image_url'])) {
                    $sheet->getColumnDimension($excelColumns[array_search($this->selectedColumns['cover_image_url'], $headers)])->setWidth(30);
                }
                if (isset($this->selectedColumns['image_urls'])) {
                    $sheet->getColumnDimension($excelColumns[array_search($this->selectedColumns['image_urls'], $headers)])->setWidth(40);
                }
                if (isset($this->selectedColumns['urls_in_shop'])) {
                    $sheet->getColumnDimension($excelColumns[array_search($this->selectedColumns['urls_in_shop'], $headers)])->setWidth(40);
                }
                if (isset($this->selectedColumns['product_lang.description_short'])) {
                    $sheet->getColumnDimension($excelColumns[array_search($this->selectedColumns['product_lang.description_short'], $headers)])->setWidth(40);
                }
                if (isset($this->selectedColumns['product_lang.description'])) {
                    $sheet->getColumnDimension($excelColumns[array_search($this->selectedColumns['product_lang.description'], $headers)])->setWidth(50);
                }
            }
        }

        $small_image = $this->getImageType('small');
        foreach ($this->products as $key => $value) {
            $i = 0;
            foreach ($value as $k => $val) {
                if ($k === $this->selectedColumns['product_link']) {
                    $link = $this->context->link->getProductLink($val, null, null, null, $this->langId);
                    $cell = $excelColumns[$i] . ($this->offset + $key + 2);
                    $sheet->setCellValue($cell, $link);
                    if ($link) {
                        $sheet->getCell($cell)->getHyperlink()->setUrl($link);
                        $sheet->getStyle($cell)->getFont()->getColor()->setARGB('FF0000FF');
                    }
                } elseif ($k === $this->selectedColumns['file_url'] && $val) {
                    $link = $this->context->link->getModuleLink('ipcatalogexportimport', 'getFile', array('file' => $val));
                    $cell = $excelColumns[$i] . ($this->offset + $key + 2);
                    $sheet->setCellValue($cell, $link);
                    if ($link) {
                        $sheet->getCell($cell)->getHyperlink()->setUrl($link);
                        $sheet->getStyle($cell)->getFont()->getColor()->setARGB('FF0000FF');
                    }
                } elseif ($k === $this->selectedColumns['attachment_url'] && $val) {
                    $link = '';
                    $val = explode($this->multivalueSeparator, $val);
                    foreach ($val as $v) {
                        $link .= $this->context->link->getModuleLink('ipcatalogexportimport', 'getAttachment', array('id_attachment' => $v)) . $this->multivalueSeparator;
                    }
                    $cell = $excelColumns[$i] . ($this->offset + $key + 2);
                    $link = rtrim($link, $this->multivalueSeparator);
                    $sheet->setCellValue($cell, $link);
                    if (count($val) === 1 && $link) {
                        $sheet->getCell($cell)->getHyperlink()->setUrl($link);
                        $sheet->getStyle($cell)->getFont()->getColor()->setARGB('FF0000FF');
                    }
                } elseif ($k === $this->selectedColumns['cover_image'] && $val) {
                    $img = new Image($val);
                    $image_path = realpath(_PS_PROD_IMG_DIR_ . $img->getExistingImgPath() . '-' . $small_image . '.jpg');
                    if (file_exists($image_path)) {
                        $drawing = new \PhpOffice\PhpSpreadsheet\Worksheet\Drawing();
                        // $drawing->setName('Product_' . $val);
                        $drawing->setPath($image_path);
                        $drawing->setResizeProportional(false);
                        $drawing->setWidth(62);
                        $drawing->setHeight(55);
                        $drawing->setCoordinates($excelColumns[$i] . ($this->offset + $key + 2));
                        $drawing->setWorksheet($sheet);
                        $drawing->getShadow()->setVisible(true);
                    }
                } elseif ($k === $this->selectedColumns['cover_image_url'] && $val) {
                    $link = $this->getImageLink($val);
                    $cell = $excelColumns[$i] . ($this->offset + $key + 2);
                    $sheet->setCellValue($cell, $link);
                    if ($link) {
                        $sheet->getCell($cell)->getHyperlink()->setUrl($link);
                        $sheet->getStyle($cell)->getFont()->getColor()->setARGB('FF0000FF');
                    }
                } elseif (($k === $this->selectedColumns['image_urls'] || $k === $this->selectedColumns['urls_in_shop']) && $val) {
                    $link = '';
                    $val = explode($this->multivalueSeparator, $val);
                    foreach ($val as $v) {
                        $link .= $this->getImageLink($v) . $this->multivalueSeparator;
                    }
                    $link = rtrim($link, $this->multivalueSeparator);
                    $cell = $excelColumns[$i] . ($this->offset + $key + 2);
                    $sheet->setCellValue($cell, $link);
                    if (count($val) === 1 && $link) {
                        $sheet->getCell($cell)->getHyperlink()->setUrl($link);
                        $sheet->getStyle($cell)->getFont()->getColor()->setARGB('FF0000FF');
                    }
                } elseif ($k === $this->selectedColumns['product.ean13'] && $val) {
                    $sheet->setCellValueExplicit($excelColumns[$i] . ($this->offset + $key + 2), $val, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
                } else {
                    $sheet->setCellValue($excelColumns[$i] . ($this->offset + $key + 2), $val);
                }
                $i++;
            }
        }

        $sheet->setSelectedCell('A1');

        // Write to file
        $writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
        $writer->save($this->file);
    }
}
