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
function upgrade_module_2_2_7()
{
    mmg_check_colum('ets_mm_tab', 'link_type', 'VARCHAR(32) NOT NULL AFTER `sort_order`');
    mmg_check_colum('ets_mm_tab', 'id_category', 'INT(11) NOT NULL AFTER `link_type`');
    mmg_check_colum('ets_mm_tab', 'id_manufacturer', 'INT(11) NOT NULL AFTER `link_type`');
    mmg_check_colum('ets_mm_tab', 'id_supplier', 'INT(11) NOT NULL AFTER `link_type`');
    mmg_check_colum('ets_mm_tab', 'id_cms', 'INT(11) NOT NULL AFTER `link_type`');
    return true;
}
if ( ! function_exists('mmg_check_colum')){
    function mmg_check_colum($table, $column, $suffix)
    {
        return Db::getInstance()->execute('
            SET @dbname = DATABASE();
            SET @tablename = "' . _DB_PREFIX_ . pSQL($table) . '";
            SET @columnname = "' . pSQL($column) . '";
            SET @suffix = "' . pSQL($suffix) . '";
            SET @preparedStatement = (SELECT IF(
            (
                SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
                WHERE
                  (table_name = @tablename)
                  AND (table_schema = @dbname)
                  AND (column_name = @columnname)
                ) > 0,
                "SELECT 1",
                CONCAT("ALTER TABLE ", @tablename, " ADD ", @columnname," ", @suffix)
            ));
            PREPARE alterIfNotExists FROM @preparedStatement;
            EXECUTE alterIfNotExists;
            DEALLOCATE PREPARE alterIfNotExists;
        ');
    }
}