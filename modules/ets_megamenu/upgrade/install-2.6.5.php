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
function upgrade_module_2_6_5()
{
    $sqls = array();
    if(!Ets_megamenu_defines::checkCreatedColumn('ets_mm_block','display_content_mobile'))
    {
        $sqls[] = 'ALTER TABLE `'._DB_PREFIX_.'ets_mm_block` ADD `display_content_mobile` tinyint(1) NOT NULL';
    }
    if(!Ets_megamenu_defines::checkCreatedColumn('ets_mm_block','customer_groups'))
    {
        $sqls[] = 'ALTER TABLE `'._DB_PREFIX_.'ets_mm_block` ADD `customer_groups` varchar(255) DEFAULT NULL';
    }
    if(!Ets_megamenu_defines::checkCreatedColumn('ets_mm_block','product_with_category'))
    {
        $sqls[] = 'ALTER TABLE `'._DB_PREFIX_.'ets_mm_block` ADD `product_with_category` tinyint(1) DEFAULT 0';
    }
    if($sqls)
    {
        foreach($sqls as $sql)
            Db::getInstance()->execute($sql);
    }
    return true;
}