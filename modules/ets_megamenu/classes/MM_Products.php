<?php
/**
 * Copyright ETS Software Technology Co., Ltd
 *
 * NOTICE OF LICENSE
 *
 * This file is not open source! Each license that you purchased is only available for 1 website only.
 * If you want to use this file on more websites (or projects), you need to purchase additional licenses.
 * You are not allowed to redistribute, resell, lease, license, sub-license or offer our resources to any third party.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future.
 *
 * @author ETS Software Technology Co., Ltd
 * @copyright  ETS Software Technology Co., Ltd
 * @license    Valid for 1 website (or project) for each purchase of license
 */

if (!defined('_PS_VERSION_')) { exit; }

class MM_Products
{
    private static $is17 = false;
    private $nProducts = 1;
    private $id_category = 2;
    private $Page = 1;
    private $orderBy = null;
    private $orderWay = null;
    private $randSeed = 1;
    private $context;

    public function __construct()
    {
        self::$is17 = version_compare(_PS_VERSION_, '1.7', '>=');
    }
    public function setRandSeed($randSeed)
    {
        $this->randSeed = $randSeed;
        return $this;
    }

    public function setIdCategory($id_category)
    {
        $this->id_category = $id_category;
        return $this;
    }

    public function setPage($Page)
    {
        $this->Page = $Page;
        return $this;
    }

    public function setPerPage($nProducts)
    {
        $this->nProducts = $nProducts;
        return $this;
    }

    public function setOrderBy($orderBy)
    {
        $this->orderBy = $orderBy;
        return $this;
    }

    public function setOrderWay($orderWay)
    {
        $this->orderWay = $orderWay;
        return $this;
    }

    public function getPages($methods)
    {
        if (!$methods)
            return 1;
        $nbTotal = (int)$this->{$methods}(true);
        return ceil($nbTotal/$this->nProducts);
    }

    public function getBestSellers($context, $listIdCategory = null, $count = false)
    {
        if ($count)
            return ProductSale::getNbSales();
        if (($bestSales = $this->_getBestSales($context, (int)$context->language->id, $this->Page, $this->nProducts,$listIdCategory, $this->orderBy, $this->orderWay)))
        {
            if (!self::$is17) {
                $currency = new Currency((int)$context->currency->id);
                $use_tax = (Product::getTaxCalculationMethod((isset($context->customer->id) && $context->customer->id? (int)$context->customer->id : null)) != PS_TAX_EXC);
                foreach ($bestSales as &$product){
                    $product['price'] = Tools::displayPriceSmarty(['price'=>Product::getPriceStatic((int)$product['id_product'], $use_tax),'currency'=> $currency->id], $context->smarty);
                }
            }
        }
        return $bestSales;
    }
    /**
     * Get required informations on best sales products.
     *
     * @param int $idLang Language id
     * @param int $pageNumber Start from (optional)
     * @param int $nbProducts Number of products to return (optional)
     *
     * @return array|bool from Product::getProductProperties
     *                    `false` if failure
     */
    public static function _getBestSales($context, $idLang, $pageNumber = 0, $nbProducts = 10, $listIdCategory = null, $orderBy = null, $orderWay = null)
    {
        if ($pageNumber < 1) {
            $pageNumber = 1;
        }
        if ($nbProducts < 1) {
            $nbProducts = 10;
        }
        $finalOrderBy = $orderBy;
        $orderTable = '';

        $invalidOrderBy = !Validate::isOrderBy($orderBy);
        if ($invalidOrderBy || null === $orderBy) {
            $orderBy = 'quantity';
            $orderTable = 'ps';
        }

        if ($orderBy == 'date_add' || $orderBy == 'date_upd') {
            $orderTable = 'product_shop';
        }

        $invalidOrderWay = !Validate::isOrderWay($orderWay);
        if ($invalidOrderWay || null === $orderWay || $orderBy == 'sales') {
            $orderWay = 'DESC';
        }

        $interval = Validate::isUnsignedInt(Configuration::get('PS_NB_DAYS_NEW_PRODUCT')) ? Configuration::get('PS_NB_DAYS_NEW_PRODUCT') : 20;

        // Thêm điều kiện lọc theo category
        $sql_category_filter = '';
        if ($listIdCategory) {
            $categoryIds = is_array($listIdCategory) ? implode(',', array_map('intval', $listIdCategory)) : $listIdCategory;
            $sql_category_filter = ' AND EXISTS(SELECT 1 FROM `'._DB_PREFIX_.'category_product` cp 
                              WHERE cp.`id_product` = p.`id_product` 
                              AND cp.`id_category` IN ('.pSQL($categoryIds).'))';
        }

        // no group by needed : there's only one attribute with default_on=1 for a given id_product + shop
        // same for image with cover=1
        $sql = 'SELECT p.*, product_shop.*, stock.out_of_stock, IFNULL(stock.quantity, 0) as quantity,
            ' . (Combination::isFeatureActive() ? 'product_attribute_shop.minimal_quantity AS product_attribute_minimal_quantity,IFNULL(product_attribute_shop.id_product_attribute,0) id_product_attribute,' : '') . '
            pl.`description`, pl.`description_short`, pl.`link_rewrite`, pl.`meta_description`,
            pl.`meta_title`, pl.`name`, pl.`available_now`, pl.`available_later`,
            m.`name` AS manufacturer_name, p.`id_manufacturer` as id_manufacturer,
            image_shop.`id_image` id_image, il.`legend`,
            ps.`quantity` AS sales, t.`rate`,  pl.`meta_title`, pl.`meta_description`,
            DATEDIFF(p.`date_add`, DATE_SUB("' . date('Y-m-d') . ' 00:00:00",
            INTERVAL ' . (int) $interval . ' DAY)) > 0 AS new'
            . ' FROM `' . _DB_PREFIX_ . 'product_sale` ps
            LEFT JOIN `' . _DB_PREFIX_ . 'product` p ON ps.`id_product` = p.`id_product`
            ' . Shop::addSqlAssociation('product', 'p', false);

        if (Combination::isFeatureActive()) {
            $sql .= ' LEFT JOIN `' . _DB_PREFIX_ . 'product_attribute_shop` product_attribute_shop
                    ON (p.`id_product` = product_attribute_shop.`id_product` AND product_attribute_shop.`default_on` = 1 AND product_attribute_shop.id_shop=' . (int) $context->shop->id . ')';
        }

        $sql .= ' LEFT JOIN `' . _DB_PREFIX_ . 'product_lang` pl
                ON p.`id_product` = pl.`id_product`
                AND pl.`id_lang` = ' . (int) $idLang . Shop::addSqlRestrictionOnLang('pl') . '
            LEFT JOIN `' . _DB_PREFIX_ . 'image_shop` image_shop
                ON (image_shop.`id_product` = p.`id_product` AND image_shop.cover=1 AND image_shop.id_shop=' . (int) $context->shop->id . ')
            LEFT JOIN `' . _DB_PREFIX_ . 'image_lang` il ON (image_shop.`id_image` = il.`id_image` AND il.`id_lang` = ' . (int) $idLang . ')
            LEFT JOIN `' . _DB_PREFIX_ . 'manufacturer` m ON (m.`id_manufacturer` = p.`id_manufacturer`)
            LEFT JOIN `' . _DB_PREFIX_ . 'tax_rule` tr ON (product_shop.`id_tax_rules_group` = tr.`id_tax_rules_group`)
                AND tr.`id_country` = ' . (int) $context->country->id . '
                AND tr.`id_state` = 0
            LEFT JOIN `' . _DB_PREFIX_ . 'tax` t ON (t.`id_tax` = tr.`id_tax`)
            ' . Product::sqlStock('p', 0);

        $sql .= '
            WHERE product_shop.`active` = 1
                AND product_shop.`visibility` != \'none\'
            ' . $sql_category_filter;

        if (Group::isFeatureActive()) {
            $groups = FrontController::getCurrentCustomerGroups();
            $sql .= ' AND EXISTS(SELECT 1 FROM `' . _DB_PREFIX_ . 'category_product` cp
        JOIN `' . _DB_PREFIX_ . 'category_group` cg ON (cp.id_category = cg.id_category AND cg.`id_group` ' . (count($groups) ? 'IN (' . implode(',', $groups) . ')' : '=' . (int) Group::getCurrent()->id) . ')
        WHERE cp.`id_product` = p.`id_product`)';
        }

        if ($finalOrderBy != 'price') {
            $sql .= '
                ORDER BY ' . (!empty($orderTable) ? '`' . pSQL($orderTable) . '`.' : '') . '`' . pSQL($orderBy) . '` ' . pSQL($orderWay) . '
                LIMIT ' . (int) (($pageNumber - 1) * $nbProducts) . ', ' . (int) $nbProducts;
        }

        $result = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($sql);

        if ($finalOrderBy == 'price') {
            Tools::orderbyPrice($result, $orderWay);
            $result = array_slice($result, (int) (($pageNumber - 1) * $nbProducts), (int) $nbProducts);
        }
        if (!$result) {
            return false;
        }

        return Product::getProductsProperties($idLang, $result);
    }

    public function getHomeFeatured($context, $listIdCategory = null, $count = false)
    {
        if (!$this->id_category) {
            return array();
        }

        // Lấy id của danh mục chính (home category)
        $id_home_category = Configuration::get('PS_HOME_CATEGORY');

        // Nếu có danh sách category được chọn
        if ($listIdCategory) {
            $categoryIds = is_array($listIdCategory) ? $listIdCategory : explode(',', $listIdCategory);
            $categoryIds = array_map('intval', $categoryIds);

            // Kiểm tra xem danh sách có chứa category home không
            $hasHomeCategory = in_array($id_home_category, $categoryIds);

            // Gọi hàm _getFeaturedProducts với điều kiện lọc
            return $this->_getFeaturedProducts(
                $context,
                (int)$context->language->id,
                $count ? 0 : $this->Page,
                $count ? 0 : $this->nProducts,
                $this->orderBy,
                $this->orderWay,
                $count,
                true,
                ($this->orderBy == 'rand'),
                ($this->orderBy == 'rand' ? $this->nProducts : 1),
                ($context->controller->controller_type != 'admin'),
                $categoryIds,
                $hasHomeCategory ? null : $id_home_category // Truyền id_home_category nếu không có trong danh sách
            );
        }

        // Phần còn lại giữ nguyên nếu không có listIdCategory
        $category = new Category((int)$this->id_category, (int)$context->language->id);
        if (!$category->active) {
            return false;
        }

        if ($count) {
            return $category->getProducts((int)$context->language->id, 0, 0, null, null, false, true, ($context->controller->controller_type != 'admin' ? true : false), $context);
        }

        $products = $category->getProducts(
            (int)$context->language->id,
            $this->Page,
            $this->nProducts,
            $this->orderBy,
            $this->orderWay,
            false,
            true,
            ($this->orderBy != 'rand' ? false : true),
            ($this->orderBy != 'rand' ? 1 : $this->nProducts),
            ($context->controller->controller_type != 'admin' ? true : false),
            $context
        );

        return Product::getProductsProperties((int)$context->language->id, $products);
    }

    /**
     * Returns featured products with optional category filtering.
     */
    public function _getFeaturedProducts(
        $context,
        $idLang,
        $pageNumber,
        $productPerPage,
        $orderBy = null,
        $orderWay = null,
        $getTotal = false,
        $active = true,
        $random = false,
        $randomNumberProducts = 1,
        $checkAccess = true,
        $categoryIds = null,
        $id_home_category = null
    ) {
        if(!$context)
            return [];
        $front = in_array($context->controller->controller_type, ['front', 'modulefront']);
        $idSupplier = (int) Tools::getValue('id_supplier');

        // Điều kiện lọc theo category
        $categoryFilter = '';
        $joinCategoryProduct = ''; // Thêm biến để quản lý JOIN bảng category_product

        if ($categoryIds && is_array($categoryIds)) {
            // Đầu tiên lọc sản phẩm thuộc ít nhất một trong các category được chọn
            $categoryFilter = ' AND EXISTS(SELECT 1 FROM `'._DB_PREFIX_.'category_product` cp 
                      WHERE cp.`id_product` = p.`id_product` 
                      AND cp.`id_category` IN (' . implode(',', $categoryIds) . '))';

            // Nếu có yêu cầu kiểm tra category home (khi nó không nằm trong danh sách category được chọn)
            if ($id_home_category) {
                $categoryFilter .= ' AND EXISTS(SELECT 1 FROM `'._DB_PREFIX_.'category_product` cp 
                          WHERE cp.`id_product` = p.`id_product` 
                          AND cp.`id_category` = '.(int)$id_home_category.')';
            }
        }

        // JOIN bảng category_product nếu cần sắp xếp theo position hoặc có categoryIds
        if ($orderBy === 'position' || ($categoryIds && is_array($categoryIds))) {
            // Sử dụng category đầu tiên trong danh sách hoặc category mặc định
            $categoryForPosition = 1; // Default category
            if ($categoryIds && is_array($categoryIds)) {
                $categoryForPosition = (int)$categoryIds[0];
            } elseif (isset($this->id_category)) {
                $categoryForPosition = (int)$this->id_category;
            }

            $joinCategoryProduct = ' LEFT JOIN `'._DB_PREFIX_.'category_product` cp ON (
                p.`id_product` = cp.`id_product` 
                AND cp.`id_category` = '.$categoryForPosition.'
            )';
        }

        /* Return only the number of products */
        if ($getTotal) {
            $sql = 'SELECT COUNT(DISTINCT p.`id_product`) AS total
            FROM `' . _DB_PREFIX_ . 'product` p
            ' . Shop::addSqlAssociation('product', 'p') . '
            WHERE 1=1 ' . $categoryFilter .
                ($front ? ' AND product_shop.`visibility` IN ("both", "catalog")' : '') .
                ($active ? ' AND product_shop.`active` = 1' : '') .
                ($idSupplier ? ' AND p.id_supplier = ' . (int) $idSupplier : '');

            return (int) Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue($sql);
        }

        if ($pageNumber < 1) {
            $pageNumber = 1;
        }

        /** Tools::strtolower is a fix for all modules which are now using lowercase values for 'orderBy' parameter */
        $orderBy = Validate::isOrderBy($orderBy) ? Tools::strtolower($orderBy) : 'position';
        $orderWay = Validate::isOrderWay($orderWay) ? Tools::strtoupper($orderWay) : 'ASC';

        $orderByPrefix = false;
        if ($orderBy === 'id_product' || $orderBy === 'date_add' || $orderBy === 'date_upd') {
            $orderByPrefix = 'p';
        } elseif ($orderBy === 'name') {
            $orderByPrefix = 'pl';
        } elseif ($orderBy === 'manufacturer' || $orderBy === 'manufacturer_name') {
            $orderByPrefix = 'm';
            $orderBy = 'name';
        } elseif ($orderBy === 'position') {
            $orderByPrefix = 'cp';
        }

        if ($orderBy === 'price') {
            $orderBy = 'orderprice';
        }

        $nbDaysNewProduct = Configuration::get('PS_NB_DAYS_NEW_PRODUCT');
        if (!Validate::isUnsignedInt($nbDaysNewProduct)) {
            $nbDaysNewProduct = 20;
        }

        $sql = 'SELECT DISTINCT p.*, product_shop.*, stock.out_of_stock, IFNULL(stock.quantity, 0) AS quantity' . (Combination::isFeatureActive() ? ', IFNULL(product_attribute_shop.id_product_attribute, 0) AS id_product_attribute,
          product_attribute_shop.minimal_quantity AS product_attribute_minimal_quantity' : '') . ', pl.`description`, pl.`description_short`, pl.`available_now`,
          pl.`available_later`, pl.`link_rewrite`, pl.`meta_description`,  pl.`meta_title`, pl.`name`, image_shop.`id_image` id_image,
          il.`legend` as legend, m.`name` AS manufacturer_name, cl.`name` AS category_default,
          DATEDIFF(product_shop.`date_add`, DATE_SUB("' . date('Y-m-d') . ' 00:00:00",
          INTERVAL ' . (int) $nbDaysNewProduct . ' DAY)) > 0 AS new, product_shop.price AS orderprice
        FROM `' . _DB_PREFIX_ . 'product` p
        ' . Shop::addSqlAssociation('product', 'p') .
            (Combination::isFeatureActive() ? ' LEFT JOIN `' . _DB_PREFIX_ . 'product_attribute_shop` product_attribute_shop
        ON (p.`id_product` = product_attribute_shop.`id_product` AND product_attribute_shop.`default_on` = 1 AND product_attribute_shop.id_shop=' . (int) $context->shop->id . ')' : '') . '
        ' . Product::sqlStock('p', 0) . '
        ' . $joinCategoryProduct . ' 
        LEFT JOIN `' . _DB_PREFIX_ . 'category_lang` cl
          ON (product_shop.`id_category_default` = cl.`id_category`
          AND cl.`id_lang` = ' . (int) $idLang . Shop::addSqlRestrictionOnLang('cl') . ')
        LEFT JOIN `' . _DB_PREFIX_ . 'product_lang` pl
          ON (p.`id_product` = pl.`id_product`
          AND pl.`id_lang` = ' . (int) $idLang . Shop::addSqlRestrictionOnLang('pl') . ')
        LEFT JOIN `' . _DB_PREFIX_ . 'image_shop` image_shop
          ON (image_shop.`id_product` = p.`id_product` AND image_shop.cover=1 AND image_shop.id_shop=' . (int) $context->shop->id . ')
        LEFT JOIN `' . _DB_PREFIX_ . 'image_lang` il
          ON (image_shop.`id_image` = il.`id_image`
          AND il.`id_lang` = ' . (int) $idLang . ')
        LEFT JOIN `' . _DB_PREFIX_ . 'manufacturer` m
          ON m.`id_manufacturer` = p.`id_manufacturer`
        WHERE product_shop.`id_shop` = ' . (int) $context->shop->id .
            $categoryFilter .
            ($active ? ' AND product_shop.`active` = 1' : '') .
            ($front ? ' AND product_shop.`visibility` IN ("both", "catalog")' : '') .
            ($idSupplier ? ' AND p.id_supplier = ' . (int) $idSupplier : '');

        if ($random === true) {
            $sql .= ' ORDER BY RAND() LIMIT ' . (int) $randomNumberProducts;
        } elseif ($orderBy !== 'orderprice') {
            // Kiểm tra nếu orderBy là position và không có JOIN category_product thì fallback
            if ($orderBy === 'position' && strpos($sql, 'category_product` cp') === false) {
                $sql .= ' ORDER BY p.`id_product` ' . pSQL($orderWay) . '
            LIMIT ' . (((int) $pageNumber - 1) * (int) $productPerPage) . ',' . (int) $productPerPage;
            } else {
                $sql .= ' ORDER BY ' . (!empty($orderByPrefix) ? $orderByPrefix . '.' : '') . '`' . bqSQL($orderBy) . '` ' . pSQL($orderWay) . '
            LIMIT ' . (((int) $pageNumber - 1) * (int) $productPerPage) . ',' . (int) $productPerPage;
            }
        }

        $result = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($sql, true, false);

        if (!$result) {
            return [];
        }

        if ($orderBy === 'orderprice') {
            Tools::orderbyPrice($result, $orderWay);
            $result = array_slice($result, (int) (($pageNumber - 1) * $productPerPage), (int) $productPerPage);
        }

        // Modify SQL result
        return Product::getProductsProperties($idLang, $result);
    }

    public function getNewProducts($context, $idcategories = null, $count = false)
    {

        if ($count)
            return $this->_getNewProducts($context, 0, 0,$idcategories, true);
        $newProducts = false;
        if (Configuration::get('PS_NB_DAYS_NEW_PRODUCT')) {
            $newProducts = $this->_getNewProducts($context, $this->Page, $this->nProducts, false, $this->orderBy, $this->orderWay,$idcategories);
        }
        return $newProducts;
    }
    public static function _getNewProducts($context, $page_number = 0, $nb_products = 10, $count = false, $order_by = null, $order_way = null, $listIdCategory = null)
    {
        $now = date('Y-m-d') . ' 00:00:00';
        $front = true;
        if (!in_array($context->controller->controller_type, ['front', 'modulefront'])) {
            $front = false;
        }

        if ($page_number < 1) {
            $page_number = 1;
        }
        if ($nb_products < 1) {
            $nb_products = 10;
        }
        if (empty($order_by) || $order_by == 'position') {
            $order_by = 'date_add';
        }
        if (empty($order_way)) {
            $order_way = 'DESC';
        }
        if ($order_by == 'id_product' || $order_by == 'price' || $order_by == 'date_add' || $order_by == 'date_upd') {
            $order_by_prefix = 'product_shop';
        } elseif ($order_by == 'name') {
            $order_by_prefix = 'pl';
        }
        if (!Validate::isOrderBy($order_by) || !Validate::isOrderWay($order_way)) {
            die(Tools::displayError('Invalid sorting parameters provided.'));
        }

        $sql_groups = '';
        if (Group::isFeatureActive()) {
            $groups = FrontController::getCurrentCustomerGroups();
            $sql_groups = ' AND EXISTS(SELECT 1 FROM `' . _DB_PREFIX_ . 'category_product` cp
        JOIN `' . _DB_PREFIX_ . 'category_group` cg ON (cp.id_category = cg.id_category AND cg.`id_group` ' . (count($groups) ? 'IN (' . implode(',', $groups) . ')' : '=' . (int) Group::getCurrent()->id) . ')
        WHERE cp.`id_product` = p.`id_product`)';
        }

        if (strpos($order_by, '.') > 0) {
            $order_by = explode('.', $order_by);
            $order_by_prefix = $order_by[0];
            $order_by = $order_by[1];
        }

        $nb_days_new_product = (int) Configuration::get('PS_NB_DAYS_NEW_PRODUCT');

        // Xử lý điều kiện lọc theo category
        $sql_category_filter = '';
        if ($listIdCategory) {
            // Làm sạch chuỗi category, chỉ giữ lại số và dấu phẩy
            $listIdCategory = preg_replace('/[^0-9,]/', '', $listIdCategory);
            $sql_category_filter = ' AND EXISTS(SELECT 1 FROM `' . _DB_PREFIX_ . 'category_product` cp 
                              WHERE cp.`id_product` = p.`id_product` 
                              AND cp.`id_category` IN (' . $listIdCategory . '))';
        }

        if ($count) {
            $sql = 'SELECT COUNT(p.`id_product`) AS nb
                FROM `' . _DB_PREFIX_ . 'product` p
                ' . Shop::addSqlAssociation('product', 'p') . '
                WHERE product_shop.`active` = 1
                AND DATEDIFF(product_shop.`date_add`, DATE_SUB("' . $now . '", INTERVAL ' . $nb_days_new_product . ' DAY)) > 0
                ' . ($front ? ' AND product_shop.`visibility` IN ("both", "catalog")' : '') . '
                ' . $sql_groups . $sql_category_filter;
            return (int) Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue($sql);
        }

        $sql = new DbQuery();
        $sql->select(
            'p.*, product_shop.*, stock.out_of_stock, IFNULL(stock.quantity, 0) as quantity, pl.`description`, pl.`description_short`, pl.`link_rewrite`, pl.`meta_description`,
         pl.`meta_title`, pl.`name`, pl.`available_now`, pl.`available_later`, image_shop.`id_image` id_image, il.`legend`, m.`name` AS manufacturer_name,
        (DATEDIFF(product_shop.`date_add`,
            DATE_SUB(
                "' . $now . '",
                INTERVAL ' . $nb_days_new_product . ' DAY
            )
        ) > 0) as new'
        );

        $sql->from('product', 'p');
        $sql->join(Shop::addSqlAssociation('product', 'p'));
        $sql->leftJoin(
            'product_lang',
            'pl',
            'p.`id_product` = pl.`id_product`
        AND pl.`id_lang` = ' . (int) $context->language->id . Shop::addSqlRestrictionOnLang('pl')
        );
        $sql->leftJoin('image_shop', 'image_shop', 'image_shop.`id_product` = p.`id_product` AND image_shop.cover=1 AND image_shop.id_shop=' . (int) $context->shop->id);
        $sql->leftJoin('image_lang', 'il', 'image_shop.`id_image` = il.`id_image` AND il.`id_lang` = ' . (int) $context->language->id);
        $sql->leftJoin('manufacturer', 'm', 'm.`id_manufacturer` = p.`id_manufacturer`');

        $sql->where('product_shop.`active` = 1');
        if ($front) {
            $sql->where('product_shop.`visibility` IN ("both", "catalog")');
        }
        $sql->where('DATEDIFF(product_shop.`date_add`,
        DATE_SUB(
            "' . $now . '",
            INTERVAL ' . $nb_days_new_product . ' DAY
        )
    ) > 0');

        // Áp dụng điều kiện lọc category
        if ($listIdCategory) {
            $sql->where('EXISTS(SELECT 1 FROM `' . _DB_PREFIX_ . 'category_product` cp 
                      WHERE cp.`id_product` = p.`id_product` 
                      AND cp.`id_category` IN (' . $listIdCategory . '))');
        }

        if (Group::isFeatureActive()) {
            $groups = FrontController::getCurrentCustomerGroups();
            $sql->where('EXISTS(SELECT 1 FROM `' . _DB_PREFIX_ . 'category_product` cp
        JOIN `' . _DB_PREFIX_ . 'category_group` cg ON (cp.id_category = cg.id_category AND cg.`id_group` ' . (count($groups) ? 'IN (' . implode(',', $groups) . ')' : '=' . (int) Group::getCurrent()->id) . ')
        WHERE cp.`id_product` = p.`id_product`)');
        }

        // Sắp xếp và giới hạn số lượng sản phẩm
        if ($order_by !== 'price') {
            $sql->orderBy((isset($order_by_prefix) ? pSQL($order_by_prefix) . '.' : '') . '`' . pSQL($order_by) . '` ' . pSQL($order_way));
            $sql->limit($nb_products, (int) (($page_number - 1) * $nb_products));
        }

        if (Combination::isFeatureActive()) {
            $sql->select('product_attribute_shop.minimal_quantity AS product_attribute_minimal_quantity, IFNULL(product_attribute_shop.id_product_attribute,0) id_product_attribute');
            $sql->leftJoin('product_attribute_shop', 'product_attribute_shop', 'p.`id_product` = product_attribute_shop.`id_product` AND product_attribute_shop.`default_on` = 1 AND product_attribute_shop.id_shop=' . (int) $context->shop->id);
        }
        $sql->join(Product::sqlStock('p', 0));

        $result = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($sql);

        if (!$result) {
            return false;
        }

        if ($order_by === 'price') {
            Tools::orderbyPrice($result, $order_way);
            $result = array_slice($result, (int) (($page_number - 1) * $nb_products), (int) $nb_products);
        }

        $products_ids = [];
        foreach ($result as $row) {
            $products_ids[] = $row['id_product'];
        }

        Product::cacheFrontFeatures($products_ids, $context->language->id);
        return Product::getProductsProperties((int) $context->language->id, $result);
    }

    public function getSpecialProducts($context, $listIdCategory = null, $count = false)
    {
        if ($count)
            return self::getPricesDrop($context, 0, 0, true);
        $pricesDrops = self::getPricesDrop($context, $this->Page, $this->nProducts,$listIdCategory, false, $this->orderBy, $this->orderWay, false, false);
        return $pricesDrops;
    }

    public static function getPricesDrop(
        $context,
        $page_number = 0,
        $nb_products = 10,
        $listIdCategory = null,
        $count = false,
        $order_by = null,
        $order_way = null,
        $beginning = false,
        $ending = false) {
        $id_lang = $context->language->id;
        if ($page_number < 1) {
            $page_number = 1;
        }
        if ($nb_products < 1) {
            $nb_products = 10;
        }
        if (empty($order_by) || $order_by == 'position') {
            $order_by = 'price';
        }
        if (empty($order_way)) {
            $order_way = 'DESC';
        }
        if ($order_by == 'id_product' || $order_by == 'price' || $order_by == 'date_add' || $order_by == 'date_upd') {
            $order_by_prefix = 'product_shop';
        } elseif ($order_by == 'name') {
            $order_by_prefix = 'pl';
        }

        if (!Validate::isOrderBy($order_by) || !Validate::isOrderWay($order_way)) {
            die(Tools::displayError());
        }
        $current_date = date('Y-m-d H:i:00');
        $ids_product = self::_getProductIdByDate((!$beginning ? $current_date : $beginning), (!$ending ? $current_date : $ending), $context);
        $tab_id_product = array();
        foreach ($ids_product as $product) {
            if (is_array($product)) {
                $tab_id_product[] = (int)$product['id_product'];
            } else {
                $tab_id_product[] = (int)$product;
            }
        }
        $front = false;
        if ($context->controller->controller_type != 'admin') {
            $front = true;
        }
        $sql_groups = '';
        if (Group::isFeatureActive()) {
            $groups = FrontController::getCurrentCustomerGroups();
            $sql_groups = ' AND EXISTS(SELECT 1 FROM `'._DB_PREFIX_.'category_product` cp JOIN `'._DB_PREFIX_.'category_group` cg ON (cp.id_category = cg.id_category AND cg.`id_group` '.(count($groups) ? 'IN ('.implode(',', $groups).')' : '= 1').') WHERE cp.`id_product` = p.`id_product`)';
        }
        $sql_category_filter = '';
        if ($listIdCategory) {
            $categoryIds = is_array($listIdCategory) ? implode(',', array_map('intval', $listIdCategory)) : $listIdCategory;
            $sql_category_filter = ' AND EXISTS(SELECT 1 FROM `'._DB_PREFIX_.'category_product` cp 
                                WHERE cp.`id_product` = p.`id_product` 
                                AND cp.`id_category` IN ('.pSQL($categoryIds).'))';
        }
        if ($count) {
            return Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue('
        SELECT COUNT(DISTINCT p.`id_product`)
        FROM `'._DB_PREFIX_.'product` p
        '.Shop::addSqlAssociation('product', 'p').'
        WHERE product_shop.`active` = 1
        AND product_shop.`show_price` = 1
        '.($front ? ' AND product_shop.`visibility` IN ("both", "catalog")' : '').'
        '.((!$beginning && !$ending) ? 'AND p.`id_product` IN('.((is_array($tab_id_product) && count($tab_id_product)) ? implode(', ', $tab_id_product) : 0).')' : '').'
        '.$sql_groups
                .$sql_category_filter);
        }

        if (strpos($order_by, '.') > 0) {
            $order_by = explode('.', $order_by);
            $order_by = pSQL($order_by[0]).'.`'.pSQL($order_by[1]).'`';
        }
        $prev_version = version_compare(_PS_VERSION_, '1.6.1.0', '<');
        $sql = '
    SELECT
        p.*, product_shop.*, stock.out_of_stock, IFNULL(stock.quantity, 0) as quantity, pl.`description`, pl.`description_short`, pl.`available_now`, pl.`available_later`,
        '.($prev_version? ' MAX(product_attribute_shop.id_product_attribute)' : ' IFNULL(product_attribute_shop.id_product_attribute, 0)').' `id_product_attribute`,
        pl.`link_rewrite`, pl.`meta_description`, pl.`meta_title`,
        pl.`name`, '.($prev_version? 'MAX(image_shop.`id_image`)' : 'image_shop.`id_image`').' `id_image`, il.`legend`, m.`name` AS manufacturer_name,
        DATEDIFF(
            p.`date_add`,
            DATE_SUB(
                "'.date('Y-m-d').' 00:00:00",
                INTERVAL '.(Validate::isUnsignedInt(Configuration::get('PS_NB_DAYS_NEW_PRODUCT')) ? Configuration::get('PS_NB_DAYS_NEW_PRODUCT') : 20).' DAY
            )
        ) > 0 AS new
    FROM `'._DB_PREFIX_.'product` p' .Shop::addSqlAssociation('product', 'p')
            .($prev_version? 'LEFT JOIN `'._DB_PREFIX_.'product_attribute` pa ON (pa.id_product = p.id_product)'.Shop::addSqlAssociation('product_attribute', 'pa', false, 'product_attribute_shop.default_on=1').'':'LEFT JOIN `'._DB_PREFIX_.'product_attribute_shop` product_attribute_shop ON (p.`id_product` = product_attribute_shop.`id_product` AND product_attribute_shop.`default_on` = 1 AND product_attribute_shop.id_shop='.(int)$context->shop->id.')')
            .Product::sqlStock('p', 0, false, $context->shop).'
    LEFT JOIN `'._DB_PREFIX_.'product_lang` pl ON (p.`id_product` = pl.`id_product`AND pl.`id_lang` = '.(int)$id_lang.Shop::addSqlRestrictionOnLang('pl').')'
            .($prev_version? 'LEFT JOIN `'._DB_PREFIX_.'image` i ON (i.`id_product` = p.`id_product`)'. Shop::addSqlAssociation('image', 'i', false, 'image_shop.cover=1') : 'LEFT JOIN `' . _DB_PREFIX_ . 'image_shop` image_shop ON (image_shop.`id_product` = p.`id_product` AND image_shop.id_shop=' . (int)$context->shop->id . ' AND image_shop.cover=1)').'
    LEFT JOIN `'._DB_PREFIX_.'image_lang` il ON ('.($prev_version? 'i' : 'image_shop').'.`id_image` = il.`id_image` AND il.`id_lang` = '.(int)$id_lang.')
    LEFT JOIN `'._DB_PREFIX_.'manufacturer` m ON (m.`id_manufacturer` = p.`id_manufacturer`)
    WHERE product_shop.`active` = 1
    AND product_shop.`show_price` = 1
    '.($front ? ' AND product_shop.`visibility` IN ("both", "catalog")' : '').'
    '.((!$beginning && !$ending) ? ' AND p.`id_product` IN ('.((is_array($tab_id_product) && count($tab_id_product)) ? implode(', ', $tab_id_product) : 0).')' : '').'
    '.pSQL($sql_groups)
            .$sql_category_filter;

        $sql .= '
    ORDER BY '.(isset($order_by_prefix) ? pSQL($order_by_prefix).'.' : '').pSQL($order_by).' '.pSQL($order_way);

        // Áp dụng LIMIT sau khi đã lọc và sắp xếp
        $sql .= ' LIMIT '.(int)(($page_number-1) * $nb_products).', '.(int)$nb_products;

        $result = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($sql);
        if (!$result) {
            return false;
        }

        if ($order_by == 'price') {
            Tools::orderbyPrice($result, $order_way);
        }

        return Product::getProductsProperties($id_lang, $result);
    }

    protected static function _getProductIdByDate($beginning, $ending, $context, $with_combination = false)
    {
        $id_country = (int)Configuration::get('PS_COUNTRY_DEFAULT');
        if ($context->controller->controller_type != 'admin')
        {
            $id_address = $context->cart->{Configuration::get('PS_TAX_ADDRESS_TYPE')};
            $ids = Address::getCountryAndState($id_address);
            if (!empty($ids['id_country']))
                $id_country = $ids['id_country'];
        }
        return SpecificPrice::getProductIdByDate(
            $context->shop->id,
            $context->currency->id,
            $id_country,
            (int)Configuration::get('PS_CUSTOMER_GROUP'),
            $beginning,
            $ending,
            0,
            $with_combination
        );
    }
}