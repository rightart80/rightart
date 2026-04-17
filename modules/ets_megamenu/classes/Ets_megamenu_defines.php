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
class Ets_megamenu_defines
{
    protected static $instance;
    public function l($string)
    {
        return Translate::getModuleTranslation('ets_marketplace', $string, pathinfo(__FILE__, PATHINFO_FILENAME));
    }

    public static function getInstance()
    {
        if (!isset(self::$instance)) {
            self::$instance = new Ets_megamenu_defines();
        }
        return self::$instance;
    }
    public static function searchProduct($context, $query,$excludedProductIds=array(),$excludeVirtuals=false,$exclude_packs=false,$excludeIds=false,$imageType='cart')
    {
        if (version_compare(_PS_VERSION_, '1.6.1.0', '<')) {
            $imgLeftJoin = ' LEFT JOIN `' . _DB_PREFIX_ . 'image` i ON (i.`id_product` = p.`id_product`) ' . Shop::addSqlAssociation('image', 'i', false, 'image_shop.cover = 1');
        } else {
            $imgLeftJoin = ' LEFT JOIN `' . _DB_PREFIX_ . 'image_shop` image_shop ON (image_shop.`id_product` = p.`id_product` AND image_shop.id_shop=' . (int)$context->shop->id . ') ';
        }
        $sql = 'SELECT p.`id_product`, pl.`link_rewrite`, p.`reference`, pl.`name`, image_shop.`id_image` id_image, il.`legend`, p.`cache_default_attribute`
            		FROM `' . _DB_PREFIX_ . 'product` p
            		' . Shop::addSqlAssociation('product', 'p') . '
                    LEFT JOIN `' . _DB_PREFIX_ . 'product_lang` pl ON (pl.id_product = p.id_product AND pl.id_lang = ' . (int)$context->language->id . Shop::addSqlRestrictionOnLang('pl') . ')
            		' . pSQL($imgLeftJoin) . ' 
            		LEFT JOIN `' . _DB_PREFIX_ . 'image_lang` il ON (image_shop.`id_image` = il.`id_image` AND il.`id_lang` = ' . (int)$context->language->id . ')
            		LEFT JOIN `' . _DB_PREFIX_ . 'product_shop` ps ON (p.`id_product` = ps.`id_product`) 
            		WHERE ' . ($excludedProductIds ? 'p.`id_product` NOT IN(' . implode(',', array_map('intval',$excludedProductIds)) . ') AND ' : '') . ' (pl.name LIKE \'%' . pSQL($query) . '%\' OR p.reference LIKE \'%' . pSQL($query) . '%\' OR p.id_product = ' . (int)$query . ') AND ps.`active` = 1 AND ps.`id_shop` = ' . (int)$context->shop->id .
            ($excludeVirtuals ? ' AND NOT EXISTS (SELECT 1 FROM `' . _DB_PREFIX_ . 'product_download` pd WHERE (pd.id_product = p.id_product))' : '') .
            ($exclude_packs ? ' AND (p.cache_is_pack IS NULL OR p.cache_is_pack = 0)' : '') .
            ' AND image_shop.cover = 1  GROUP BY p.id_product';

        if (($items = Db::getInstance()->executeS($sql))) {
            $results = array();
            foreach ($items as $item) {
                if (Combination::isFeatureActive() && (int)$item['cache_default_attribute']) {
                    $sql = 'SELECT pa.`id_product_attribute`, pa.`reference`, ag.`id_attribute_group`, pai.`id_image`, agl.`name` AS group_name, al.`name` AS attribute_name, NULL as `attribute`, a.`id_attribute`
            					FROM `' . _DB_PREFIX_ . 'product_attribute` pa
            					' . Shop::addSqlAssociation('product_attribute', 'pa') . '
            					LEFT JOIN `' . _DB_PREFIX_ . 'product_attribute_combination` pac ON pac.`id_product_attribute` = pa.`id_product_attribute`
            					LEFT JOIN `' . _DB_PREFIX_ . 'attribute` a ON a.`id_attribute` = pac.`id_attribute`
            					LEFT JOIN `' . _DB_PREFIX_ . 'attribute_group` ag ON ag.`id_attribute_group` = a.`id_attribute_group`
            					LEFT JOIN `' . _DB_PREFIX_ . 'attribute_lang` al ON (a.`id_attribute` = al.`id_attribute` AND al.`id_lang` = ' . (int)$context->language->id . ')
            					LEFT JOIN `' . _DB_PREFIX_ . 'attribute_group_lang` agl ON (ag.`id_attribute_group` = agl.`id_attribute_group` AND agl.`id_lang` = ' . (int)$context->language->id . ')
            					LEFT JOIN `' . _DB_PREFIX_ . 'product_attribute_image` pai ON pai.`id_product_attribute` = pa.`id_product_attribute`
            					WHERE pa.`id_product` = ' . (int)$item['id_product'] . ($excludeIds ? ' AND NOT FIND_IN_SET(CONCAT(pa.`id_product`,"-", IF(pa.`id_product_attribute` IS NULL,0,pa.`id_product_attribute`)), "' . pSQL($excludeIds) . '")' : '') . '
            					GROUP BY pa.`id_product_attribute`, ag.`id_attribute_group`
            					ORDER BY pa.`id_product_attribute`';
                    if (($combinations = Db::getInstance()->executeS($sql))) {
                        foreach ($combinations as $combination) {
                            $results[$combination['id_product_attribute']]['id_product'] = $item['id_product'];
                            $results[$combination['id_product_attribute']]['id_product_attribute'] = $combination['id_product_attribute'];
                            $results[$combination['id_product_attribute']]['name'] = $item['name'];
                            !empty($results[$combination['id_product_attribute']]['attribute']) ? $results[$combination['id_product_attribute']]['attribute'] .= ' ' . $combination['group_name'] . '-' . $combination['attribute_name']
                                : $results[$combination['id_product_attribute']]['attribute'] = $item['attribute'] . ' ' . $combination['group_name'] . '-' . $combination['attribute_name'];

                            if (!empty($combination['reference'])) {
                                $results[$combination['id_product_attribute']]['ref'] = $combination['reference'];
                            } else {
                                $results[$combination['id_product_attribute']]['ref'] = !empty($item['reference']) ? $item['reference'] : '';
                            }

                            if (empty($results[$combination['id_product_attribute']]['image'])) {
                                $results[$combination['id_product_attribute']]['image'] = str_replace('http://', Tools::getShopProtocol(), $context->link->getImageLink($item['link_rewrite'], (!empty($combination['id_image']) ? (int)$combination['id_image'] : (int)$item['id_image']), $imageType));
                            }
                        }
                    }
                } else {
                    $results[] = array(
                        'id_product' => (int)($item['id_product']),
                        'id_product_attribute' => 0,
                        'name' => $item['name'],
                        'attribute' => '',
                        'ref' => (!empty($item['reference']) ? $item['reference'] : ''),
                        'image' => str_replace('http://', Tools::getShopProtocol(), $context->link->getImageLink($item['link_rewrite'], $item['id_image'], $imageType)),
                    );
                }
            }
            if ($results) {
                foreach ($results as &$item)
                    echo trim($item['id_product'] . '|' . (int)($item['id_product_attribute']) . '|' . Tools::ucfirst($item['name']) . '|' . $item['attribute'] . '|' . $item['ref'] . '|' . $item['image']) . "\n";
            }
        }
        die;
    }
    public static function getCombinationImageById($id_product_attribute, $id_lang)
    {
        if (version_compare(_PS_VERSION_, '1.6.1.0', '>=')) {
            return Product::getCombinationImageById($id_product_attribute, $id_lang);
        } else {
            if (!Combination::isFeatureActive() || !$id_product_attribute) {
                return false;
            }
            $result = Db::getInstance()->executeS('
                SELECT pai.`id_image`, pai.`id_product_attribute`, il.`legend`
                FROM `' . _DB_PREFIX_ . 'product_attribute_image` pai
                LEFT JOIN `' . _DB_PREFIX_ . 'image_lang` il ON (il.`id_image` = pai.`id_image`)
                LEFT JOIN `' . _DB_PREFIX_ . 'image` i ON (i.`id_image` = pai.`id_image`)
                WHERE pai.`id_product_attribute` = ' . (int)$id_product_attribute . ' AND il.`id_lang` = ' . (int)$id_lang . ' ORDER by i.`position` LIMIT 1'
            );
            if (!$result) {
                return false;
            }
            return $result[0];
        }
    }
    protected static $childs = array();
    public static function getChildCategories($context, $id_parent, $order_by = 'cl.name ASC')
    {
        if(!isset(self::$childs[$id_parent]))
        {
            self::$childs[$id_parent] = Db::getInstance()->executeS("
                SELECT c.*, cl.name,cl.link_rewrite
                FROM `" . _DB_PREFIX_ . "category` c
                INNER JOIN `" . _DB_PREFIX_ . "category_shop` cs ON (c.id_category =cs.id_category AND cs.id_shop=" . (int)$context->shop->id . ")
                LEFT JOIN `" . _DB_PREFIX_ . "category_lang` cl ON c.id_category=cl.id_category AND cl.id_lang=" . (int)$context->language->id . " AND cl.id_shop=".(int)$context->shop->id."
                WHERE c.active = 1 AND  c.id_parent=" . (int)$id_parent . " AND c.id_category!=" . (int)$id_parent . "
                " . ($order_by ? " ORDER BY " . pSQL($order_by) : "") . " 
            ");
        }
        return self::$childs[$id_parent];
    }
    public static function getCategoryById($context, $id_category, $order_by = 'cl.name ASC')
    {
        $frontEnd = false;
        if (!defined('_PS_ADMIN_DIR_')) {
            $frontEnd = true;
            $id_customer = ($context->customer->id) ? (int)($context->customer->id) : 0;
            $id_group = null;
            if ($id_customer) {
                $id_group = Customer::getGroupsStatic((int)$id_customer);
            }
            if (!$id_group) {
                $id_group = array((int)Group::getCurrent()->id);
            }
        }
        $sql = "
            SELECT c.*, cl.name,cl.link_rewrite
            FROM `" . _DB_PREFIX_ . "category` c
            INNER JOIN `" . _DB_PREFIX_ . "category_shop` cs ON (c.id_category=cs.id_category AND cs.id_shop=" . (int)$context->shop->id . ")
            LEFT JOIN `" . _DB_PREFIX_ . "category_lang` cl ON c.id_category=cl.id_category AND cl.id_lang=" . (int)$context->language->id . "
            " . ($frontEnd && Group::isFeatureActive() ? " INNER JOIN `" . _DB_PREFIX_ . "category_group` cg ON c.`id_category` = cg.`id_category` AND cg.id_group IN (".implode(',',array_map('intval',$id_group)).") " : "") . "
            WHERE c.active=1 
            AND  c.id_category " . (is_array($id_category) ? "IN(" . implode(',', array_map('intval', $id_category)) . ")" : "=" . (int)$id_category) . "
            GROUP BY c.id_category
            " . ($order_by ? "ORDER BY " . pSQL($order_by) : "") . " 
        ";
        return $id_category ? (is_array($id_category) ? Db::getInstance()->executeS($sql) : Db::getInstance()->getRow($sql)) : false;
    }
    public static function createDb()
    {
        return Db::getInstance()->execute("
                CREATE TABLE IF NOT EXISTS `" . _DB_PREFIX_ . "ets_mm_block` (
                  `id_block` int(10) unsigned NOT NULL AUTO_INCREMENT,
                  `id_column` int(11) DEFAULT NULL,
                  `block_type` varchar(20) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT 'HTML',
                  `sort_order` int(11) NOT NULL DEFAULT '1',
                  `enabled` tinyint(1) NOT NULL DEFAULT '1',
                  `id_categories` varchar(500) DEFAULT NULL,
                  `order_by_category` varchar(500) DEFAULT NULL,
                  `id_manufacturers` varchar(500) DEFAULT NULL,
                  `order_by_manufacturers` varchar(500) DEFAULT NULL,
                  `display_mnu_img` tinyint(1) NOT NULL DEFAULT '1',
                  `display_mnu_name` tinyint(1) NOT NULL DEFAULT '1',
                  `display_mnu_inline` varchar(500) DEFAULT NULL,
                  `id_suppliers` varchar(500) DEFAULT NULL,
                  `order_by_suppliers` varchar(500) DEFAULT NULL,
                  `display_suppliers_img` tinyint(1) NOT NULL DEFAULT '1',
                  `display_suppliers_name` tinyint(1) NOT NULL DEFAULT '1',
                  `display_suppliers_inline` varchar(500) DEFAULT NULL,
                  `product_type` varchar(50) NOT NULL,
                  `id_products` varchar(500) NOT NULL,
                  `product_count` int(11) NOT NULL,
                  `id_cmss` varchar(500) DEFAULT NULL,
                  `display_title` tinyint(1) NOT NULL DEFAULT '1',
                  `customer_groups` varchar(50) DEFAULT NULL,
                  `display_content_mobile` tinyint(1) NOT NULL DEFAULT '1',
                  `show_description` tinyint(1) NOT NULL DEFAULT '0',
                  `product_with_category` tinyint(1) NOT NULL DEFAULT '0',
                  `show_clock` tinyint(1) NOT NULL DEFAULT '0',
                  `hidden_mobile` tinyint(1) NOT NULL DEFAULT '0',
                  PRIMARY KEY (`id_block`),
                  INDEX (`id_column`), INDEX(`sort_order`), INDEX(`enabled`), INDEX(`display_mnu_img`), INDEX(`display_mnu_name`), INDEX(`display_suppliers_img`), INDEX(`display_suppliers_name`),INDEX(`product_count`),INDEX(`display_title`), INDEX(`show_description`), INDEX(`show_clock`) 
                )
            ")
            && Db::getInstance()->execute("
                CREATE TABLE IF NOT EXISTS `" . _DB_PREFIX_ . "ets_mm_block_lang` (
                  `id_block` int(11) NOT NULL,
                  `id_lang` int(11) NOT NULL,
                  `title` varchar(500) CHARACTER SET utf8 COLLATE utf8_bin DEFAULT NULL,
                  `content` text CHARACTER SET utf8 COLLATE utf8_bin,
                  `title_link` varchar(500) CHARACTER SET utf8 COLLATE utf8_bin DEFAULT NULL,
                  `image_link` varchar(500) CHARACTER SET utf8 COLLATE utf8_bin DEFAULT NULL,
                  `image` varchar(500) NOT NULL,
                  PRIMARY KEY (`id_block`, `id_lang`)
                )
            ")
            && Db::getInstance()->execute("
                CREATE TABLE IF NOT EXISTS `" . _DB_PREFIX_ . "ets_mm_column` (
                  `id_column` int(10) unsigned NOT NULL AUTO_INCREMENT,
                  `id_menu` int(11) DEFAULT NULL,
                  `id_tab` int(11) DEFAULT NULL,
                  `is_breaker` tinyint(1) NOT NULL DEFAULT '0',
                  `column_size` varchar(20) CHARACTER SET utf8 COLLATE utf8_bin DEFAULT NULL,
                  `sort_order` int(11) NOT NULL DEFAULT '1',
                  PRIMARY KEY (`id_column`),
                  INDEX (`id_menu`), INDEX(`id_tab`), INDEX(`is_breaker`), INDEX(`sort_order`)
                )
            ")
            && Db::getInstance()->execute("
                CREATE TABLE IF NOT EXISTS `" . _DB_PREFIX_ . "ets_mm_menu_shop` (
                  `id_menu` int(10) unsigned NOT NULL,
                  `id_shop` int(11) NOT NULL,
                  PRIMARY KEY (`id_menu`,`id_shop`)
                )
            ")
            && Db::getInstance()->execute("
                CREATE TABLE IF NOT EXISTS `" . _DB_PREFIX_ . "ets_mm_menu` (
                  `id_menu` int(10) unsigned NOT NULL AUTO_INCREMENT,
                  `sort_order` int(11) NOT NULL DEFAULT '1',
                  `enabled` tinyint(1) UNSIGNED NOT NULL DEFAULT '1',
                  `enabled_vertical` int(1) NOT NULL DEFAULT '1',
                  `menu_open_new_tab` tinyint(1) UNSIGNED NOT NULL DEFAULT '1',
                  `id_cms` int(11) DEFAULT NULL,
                  `id_manufacturer` int(11) DEFAULT NULL,
                  `id_supplier` int(11) DEFAULT NULL,
                  `id_category` int(11) DEFAULT NULL,
                  `link_type` varchar(20) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT 'FULL',
                  `sub_menu_type` varchar(20) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT 'FULL',
                  `sub_menu_max_width` varchar(50) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
                  `custom_class` varchar(50) CHARACTER SET utf8 COLLATE utf8_bin DEFAULT NULL,
                  `menu_icon` varchar(50) CHARACTER SET utf8 COLLATE utf8_bin DEFAULT NULL,
                  `menu_img_link` varchar(50) CHARACTER SET utf8 COLLATE utf8_bin DEFAULT NULL,
                  `bubble_text_color` varchar(50) DEFAULT NULL,
                  `menu_item_width` varchar(50)  DEFAULT NULL,
                  `tab_item_width` varchar(50)  DEFAULT NULL,
                  `bubble_background_color` varchar(50) DEFAULT NULL,
                  `menu_ver_text_color` varchar(50) DEFAULT NULL,
                  `menu_ver_background_color` varchar(50) DEFAULT NULL,
                  `background_image` varchar(200) DEFAULT NULL,
                  `position_background` varchar(50) DEFAULT NULL,
                  `menu_ver_alway_show` tinyint(1) UNSIGNED NOT NULL DEFAULT '0',
                  `menu_ver_alway_open_first` tinyint(1) UNSIGNED NOT NULL DEFAULT '1',
                  `menu_ver_hidden_border` tinyint(1) UNSIGNED NOT NULL DEFAULT '0',
                  `display_tabs_in_full_width` INT(1) NOT NULL,
                  PRIMARY KEY (`id_menu`),
                  INDEX(`sort_order`), INDEX(`enabled`), INDEX(`enabled_vertical`), INDEX(`menu_open_new_tab`), INDEX(`id_cms`), INDEX(`id_manufacturer`), INDEX(`id_supplier`), INDEX(`id_category`), INDEX(`menu_ver_alway_show`), INDEX(`menu_ver_alway_open_first`), INDEX(`menu_ver_hidden_border`), INDEX(`display_tabs_in_full_width`)
                )
            ")
            && Db::getInstance()->execute("
                CREATE TABLE IF NOT EXISTS `" . _DB_PREFIX_ . "ets_mm_menu_lang` (
                  `id_menu` int(10) UNSIGNED NOT NULL,
                  `id_lang` int(10) UNSIGNED NOT NULL,
                  `title` varchar(500) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
                  `link` varchar(500) CHARACTER SET utf8 COLLATE utf8_bin DEFAULT NULL,
                  `bubble_text` varchar(50) CHARACTER SET utf8 COLLATE utf8_bin DEFAULT NULL,
                  PRIMARY KEY (`id_menu`, `id_lang`)
                )
            ") && Db::getInstance()->execute("
                CREATE TABLE IF NOT EXISTS `" . _DB_PREFIX_ . "ets_mm_tab` (
                  `id_tab` int(10) unsigned NOT NULL AUTO_INCREMENT,
                  `id_menu` INT(11) NOT NULL,
                  `enabled` INT(11) NOT NULL,
                  `link_type` VARCHAR(32),
                  `id_category` INT(11) NOT NULL,
                  `id_manufacturer` INT(11) NOT NULL,
                  `id_supplier` INT(11) NOT NULL,
                  `id_cms` INT(11) NOT NULL,
                  `tab_img_link` text,
                  `tab_sub_width` text,
                  `tab_sub_content_pos` INT(11) NOT NULL,
                  `tab_icon` varchar(22),
                  `bubble_text_color` varchar(50) DEFAULT NULL,
                  `bubble_background_color` varchar(50) DEFAULT NULL,
                  `sort_order` int(11) DEFAULT NULL,
                  `background_image` varchar(200) DEFAULT NULL,
                  `position_background` varchar(50) DEFAULT NULL,
                  PRIMARY KEY (`id_tab`),
                  INDEX(`id_menu`), INDEX(`enabled`), INDEX(`tab_sub_content_pos`), INDEX(`id_category`), INDEX(`id_cms`), INDEX(`id_manufacturer`),INDEX( `id_supplier`), INDEX(`sort_order`)
                )
            ") && Db::getInstance()->execute("
                CREATE TABLE IF NOT EXISTS `" . _DB_PREFIX_ . "ets_mm_tab_lang` (
                  `id_tab` int(10) UNSIGNED NOT NULL,
                  `id_lang` int(10) UNSIGNED NOT NULL,
                  `title` varchar(500) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
                  `url` text,
                  `bubble_text` varchar(500) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
                  PRIMARY KEY (`id_tab`, `id_lang`)
                )
            ");
    }
    public static function deleteDb()
    {
        return
            Db::getInstance()->execute("DROP TABLE IF EXISTS " . _DB_PREFIX_ . "ets_mm_block_lang")
            && Db::getInstance()->execute("DROP TABLE IF EXISTS " . _DB_PREFIX_ . "ets_mm_block")
            && Db::getInstance()->execute("DROP TABLE IF EXISTS " . _DB_PREFIX_ . "ets_mm_column")
            && Db::getInstance()->execute("DROP TABLE IF EXISTS " . _DB_PREFIX_ . "ets_mm_menu_lang")
            && Db::getInstance()->execute("DROP TABLE IF EXISTS " . _DB_PREFIX_ . "ets_mm_menu")
            && Db::getInstance()->execute("DROP TABLE IF EXISTS " . _DB_PREFIX_ . "ets_mm_menu_shop")
            && Db::getInstance()->execute("DROP TABLE IF EXISTS " . _DB_PREFIX_ . "ets_mm_tab")
            && Db::getInstance()->execute("DROP TABLE IF EXISTS " . _DB_PREFIX_ . "ets_mm_tab_lang");
    }
    public static function createTableIndex()
    {
        $sqls = array();
        $sqls[] = 'ALTER TABLE `' . _DB_PREFIX_ . 'ets_mm_block` ADD INDEX (`id_column`), ADD INDEX (`sort_order`), ADD INDEX (`enabled`), ADD INDEX (`display_mnu_img`), ADD INDEX (`display_mnu_name`), ADD INDEX(`display_suppliers_img`), ADD INDEX (`display_suppliers_name`), ADD INDEX(`product_count`), ADD INDEX(`display_title`), ADD INDEX(`show_description`), ADD INDEX(`show_clock`), ADD INDEX(`hidden_mobile`)';
        $sqls[] = 'ALTER TABLE `' . _DB_PREFIX_ . 'ets_mm_block_lang` ADD PRIMARY KEY (`id_block`, `id_lang`)';
        $sqls[] = 'ALTER TABLE `' . _DB_PREFIX_ . 'ets_mm_column` ADD INDEX (`id_menu`), ADD INDEX (`id_tab`), ADD INDEX(`is_breaker`) , ADD INDEX(`sort_order`) ';
        $sqls[] = 'ALTER TABLE `' . _DB_PREFIX_ . 'ets_mm_menu` ADD INDEX (`sort_order`), ADD INDEX (`enabled`), ADD INDEX (`enabled_vertical`), ADD INDEX(`menu_open_new_tab`), ADD INDEX(`id_cms`), ADD INDEX(`id_manufacturer`), ADD INDEX (`id_supplier`), ADD INDEX(`id_category`), ADD INDEX(`menu_ver_alway_show`), ADD INDEX (`menu_ver_alway_open_first`), ADD INDEX (`menu_ver_hidden_border`), ADD INDEX (`display_tabs_in_full_width`)';
        $sqls[] = 'ALTER TABLE `' . _DB_PREFIX_ . 'ets_mm_menu_lang` ADD PRIMARY KEY (`id_menu`, `id_lang`)';
        $sqls[] = 'ALTER TABLE `' . _DB_PREFIX_ . 'ets_mm_menu_shop` ADD PRIMARY KEY (`id_menu`)';
        $sqls[] = 'ALTER TABLE `' . _DB_PREFIX_ . 'ets_mm_menu_shop` ADD INDEX (`id_shop`)';
        $sqls[] = 'ALTER TABLE `' . _DB_PREFIX_ . 'ets_mm_tab` ADD INDEX (`id_menu`), ADD INDEX (`enabled`), ADD INDEX(`tab_sub_content_pos`), ADD INDEX(`id_category`),ADD INDEX ( `id_cms`), ADD INDEX(`id_manufacturer`), ADD INDEX(`id_supplier`), ADD INDEX(`sort_order`)';
        $sqls[] = 'ALTER TABLE `' . _DB_PREFIX_ . 'ets_mm_tab_lang` ADD PRIMARY KEY (`id_tab`, `id_lang`)';
        foreach ($sqls as $sql)
            Db::getInstance()->execute($sql);
        return true;
    }
    public static function checkCreatedColumn($table,$column)
    {
        $fieldsCustomers = Db::getInstance()->ExecuteS('DESCRIBE '._DB_PREFIX_.pSQL($table));
        $check_add=false;
        foreach($fieldsCustomers as $field)
        {
            if($field['Field']==$column)
            {
                $check_add=true;
                break;
            }
        }
        return $check_add;
    }
    public static function duplicateRowsFromDefaultShopLang($tableName, $shopId,$identifier)
    {
        $tableName = _DB_PREFIX_.pSQL($tableName);
        $shopDefaultLangId = (int)Configuration::get('PS_LANG_DEFAULT');
        $fields = array();
        $shop_field_exists = $primary_key_exists = false;
        $columns = Db::getInstance()->executeS('SHOW COLUMNS FROM `' . pSQL($tableName) . '`');
        foreach ($columns as $column) {
            $fields[] = '`' . $column['Field'] . '`';
            if ($column['Field'] == 'id_shop') {
                $shop_field_exists = true;
            }
            if ($column['Field'] == $identifier) {
                $primary_key_exists = true;
            }
        }
        if (!$primary_key_exists) {
            return true;
        }

        $sql = 'INSERT IGNORE INTO `'.pSQL($tableName) . '` (' . implode(',', array_map('pSQL',$fields)) . ') (SELECT ';


        reset($columns);
        $selectQueries = array();
        foreach ($columns as $column) {
            if ($identifier != $column['Field'] && $column['Field'] != 'id_lang') {
                $selectQueries[] = '(
							SELECT `' . bqSQL($column['Field']) . '`
							FROM `' . bqSQL($tableName) . '` tl
							WHERE tl.`id_lang` = ' . (int) $shopDefaultLangId . '
							' . ($shop_field_exists ? ' AND tl.`id_shop` = ' . (int) $shopId : '') . '
							AND tl.`' . bqSQL($identifier) . '` = `' . bqSQL(str_replace('_lang', '', pSQL($tableName))) . '`.`' . bqSQL($identifier) . '`
						)';
            } else {
                $selectQueries[] = '`' . bqSQL($column['Field']) . '`';
            }
        }
        $sql .= implode(',', $selectQueries);
        $sql .= ' FROM `' . _DB_PREFIX_ . 'lang` CROSS JOIN `' . bqSQL(str_replace('_lang', '', pSQL($tableName))) . '` ';


        $sql .= ' WHERE `' . bqSQL($identifier) . '` IN (SELECT `' . bqSQL($identifier) . '` FROM `' . bqSQL($tableName) . '`) )';
        return Db::getInstance()->execute($sql);
    }
    public static function alterSQL($table, $column, $tableDef)
    {
        // Initialize the database instance
        $db = Db::getInstance();

        // Check if the column exists
        $query = new DbQuery();
        $query->select('COUNT(*)');
        $query->from('INFORMATION_SCHEMA.COLUMNS');
        $query->where('table_name = "' . pSQL(_DB_PREFIX_ . $table) . '"');
        $query->where('table_schema = DATABASE()');
        $query->where('column_name = "' . pSQL($column) . '"');

        $columnExists = (bool)$db->getValue($query);

        if (!$columnExists) {
            // If the column does not exist, prepare and execute the ALTER TABLE query
            $sql = 'ALTER TABLE `' . bqSQL(_DB_PREFIX_ . $table) . '` ADD `' . bqSQL($column) . '` ' . pSQL($tableDef);
            $db->execute($sql);
        }

        return $columnExists ? 'Column already exists' : 'Column added successfully';
    }
    public static function dropTable($table, $column)
    {
        $db = Db::getInstance();
        // Check if the column exists
        $query = new DbQuery();
        $query->select('COUNT(*)');
        $query->from('INFORMATION_SCHEMA.COLUMNS');
        $query->where('table_name = "' . pSQL(_DB_PREFIX_ . $table) . '"');
        $query->where('table_schema = DATABASE()');
        $query->where('column_name = "' . pSQL($column) . '"');

        $columnExists = (bool)$db->getValue($query);

        if ($columnExists) {
            // If the column exists, prepare and execute the ALTER TABLE query to drop the column
            $sql = 'ALTER TABLE `' . bqSQL(_DB_PREFIX_ . $table) . '` DROP COLUMN `' . bqSQL($column) . '`';
            $db->execute($sql);
            return 'Column dropped successfully';
        }

        return 'Column does not exist';
    }
    private static function isPathInAllowedDirectory($path, $allowed_directory)
    {
        $realPath = realpath($path);
        $realAllowedDir = realpath($allowed_directory);

        return $realPath !== false && $realAllowedDir !== false && strpos($realPath, $realAllowedDir) === 0;
    }
    public static function unlink($filePath, $baseDir = null)
    {
        if ($baseDir === null) {
            $baseDir = _PS_ROOT_DIR_;
        }
        if (preg_match('/^[a-zA-Z0-9_\-\s\/\.:\\\]+$/', $filePath) && self::isPathInAllowedDirectory($filePath, $baseDir) && file_exists(realpath($filePath))) {
            return unlink(realpath($filePath));
        }
        return false;
    }
    private static function sanitizePath($path)
    {
        // Remove special characters except alphanumeric, dash, underscore, dot, and slash
        $sanitizedPath = preg_replace('/[^\w\-\/\.:\\\\]/', '', $path);

        return $sanitizedPath === $path ? $sanitizedPath : false;
    }
    private function checkFileExtension($path, $allowedExtensions)
    {
        $fileExtension = pathinfo($path, PATHINFO_EXTENSION);
        if (!in_array($fileExtension, $allowedExtensions)) {
            throw new Exception($this->l('Unsupported file extension.'));
        }
    }
    public function filePutContents($path, $data, $allowed_directory = null, $allowedExtensions = [], $flags = 0, $context = null)
    {
        if ($allowed_directory === null) {
            $allowed_directory = _PS_ROOT_DIR_;
        }
        $sanitizedPath = self::sanitizePath($path);

        if (!$sanitizedPath || !self::isPathInAllowedDirectory($sanitizedPath, $allowed_directory)) {
            Tools::error_log(sprintf($this->l('Unauthorized access attempt or invalid path: %s'), $path));
            return false;
        }

        if (!empty($allowedExtensions)) {
            try {
                $this->checkFileExtension($sanitizedPath, $allowedExtensions);
            } catch (Exception $e) {
                Tools::error_log(sprintf($this->l('Unsupported file extension: %s'), $path).' Error: '.$e->getMessage());
                return false;
            }
        }

        try {
            $result = file_put_contents($sanitizedPath, $data, $flags, $context);
            if ($result === false) {
                Tools::error_log(sprintf($this->l('Failed to write to file: %s'), $sanitizedPath));
                return false;
            }
            return true;
        } catch (Exception $e) {
            Tools::error_log(sprintf($this->l('Error writing to file: %s'), $sanitizedPath).' Error: '.$e->getMessage());
            return false;
        }
    }
    public function readFile($filepath, $base_dir = null)
    {
        if ($base_dir === null) {
            $base_dir = _PS_ROOT_DIR_;
        }
        if (!self::isPathInAllowedDirectory($filepath,$base_dir)) {
            Tools::error_log($this->l('Invalid base directory or file path.'));
            return;
        }
        $normalized_path = realpath($filepath);
        $normalized_path = rtrim($normalized_path, DIRECTORY_SEPARATOR);

        if (!file_exists($normalized_path) || !is_readable($normalized_path)) {
            Tools::error_log($this->l('File does not exist or is not readable.'));
            return;
        }
        readfile($normalized_path);
    }
}