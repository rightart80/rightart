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
require_once(dirname(__FILE__).'/../classes/MM_Obj.php');
require_once(dirname(__FILE__).'/../classes/MM_Menu.php');
require_once(dirname(__FILE__).'/../classes/MM_Column.php');
require_once(dirname(__FILE__).'/../classes/MM_Block.php');
require_once(dirname(__FILE__).'/../classes/MM_Config.php');
require_once(dirname(__FILE__).'/../classes/MM_Tab.php');
function upgrade_module_2_0_1($object)
{
    $languages = Language::getLanguages(false);
    $mm_config = new MM_Config();
    $formField = $mm_config->getFormField();
    if($formField['configs'])
    {
        foreach($formField['configs'] as $key=> $config)
        {
            if(!Configuration::get($key))
            {
                if(isset($config['lang']) && $config['lang'])
                {
                    $values = array();
                    foreach($languages as $lang)
                    {
                        $values[$lang['id_lang']] = isset($config['default']) ? $config['default'] : '';
                    }
                    Configuration::updateValue($key, $values,true);
                }
                else
                    Configuration::updateValue($key, isset($config['default']) ? $config['default'] : '',true);
            }
            
        }
    }
    Db::getInstance()->execute("
        CREATE TABLE IF NOT EXISTS `"._DB_PREFIX_."ets_mm_menu_shop` (
          `id_menu` int(10) unsigned NOT NULL,
          `id_shop` int(11) DEFAULT NULL
        )
    ");
    Db::getInstance()->execute("
                CREATE TABLE IF NOT EXISTS `"._DB_PREFIX_."ets_mm_tab` (
                  `id_tab` int(10) unsigned NOT NULL AUTO_INCREMENT,
                  `id_menu` INT(11) NOT NULL,
                  `enabled` INT(11) NOT NULL,
                  `tab_img_link` text,
                  `tab_sub_width` text,
                  `tab_sub_content_pos` INT(11) NOT NULL,
                  `tab_icon` varchar(22),
                  `bubble_text_color` varchar(50) DEFAULT NULL,
                  `bubble_background_color` varchar(50) DEFAULT NULL,
                  `sort_order` int(11) DEFAULT NULL,
                  `background_image` varchar(200) DEFAULT NULL,
                  `position_background` varchar(50) DEFAULT NULL,
                  PRIMARY KEY (`id_tab`)
                )
    ");
    Db::getInstance()->execute("
        CREATE TABLE IF NOT EXISTS `"._DB_PREFIX_."ets_mm_tab_lang` (
          `id_tab` int(10) UNSIGNED NOT NULL,
          `id_lang` int(10) UNSIGNED NOT NULL,
          `bubble_text` varchar(500) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
          `title` varchar(500) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL
        )
    ");
    Db::getInstance()->execute('ALTER TABLE `'._DB_PREFIX_.'ets_mm_menu` CHANGE `sub_menu_max_width` `sub_menu_max_width` VARCHAR(500) NOT NULL');
    Db::getInstance()->execute(
        Ets_megamenu_defines::alterSQL('ets_mm_menu','menu_open_new_tab', 'text NOT NULL  AFTER `custom_class`').
        Ets_megamenu_defines::alterSQL('ets_mm_menu','id_supplier', 'text NOT NULL  AFTER `custom_class`').
        Ets_megamenu_defines::alterSQL('ets_mm_menu','menu_img_link', 'text NOT NULL  AFTER `custom_class`').
        Ets_megamenu_defines::alterSQL('ets_mm_menu','menu_icon', 'varchar(222) NOT NULL  AFTER `custom_class`').
        Ets_megamenu_defines::alterSQL('ets_mm_block','id_suppliers', 'varchar(222) NOT NULL  AFTER `id_categories`').
        Ets_megamenu_defines::alterSQL('ets_mm_block','order_by_category', 'varchar(222) NOT NULL  AFTER `id_categories`').
        Ets_megamenu_defines::alterSQL('ets_mm_block','order_by_manufacturers', 'varchar(222) NOT NULL  AFTER `id_manufacturers`').
        Ets_megamenu_defines::alterSQL('ets_mm_block','order_by_suppliers', 'varchar(222) NOT NULL  AFTER `id_categories`').
        Ets_megamenu_defines::alterSQL('ets_mm_menu','display_tabs_in_full_width', 'varchar(50) NOT NULL').
        Ets_megamenu_defines::alterSQL('ets_mm_block','show_description', 'INT(1) NOT NULL').
        Ets_megamenu_defines::alterSQL('ets_mm_block','show_clock', 'INT(1) NOT NULL ').
        Ets_megamenu_defines::alterSQL('ets_mm_block','hidden_mobile', 'INT(1) NOT NULL ').
        Ets_megamenu_defines::alterSQL('ets_mm_block','display_mnu_img', 'INT(1) NOT NULL ').
        Ets_megamenu_defines::alterSQL('ets_mm_block','display_mnu_name', 'INT(1) NOT NULL ').
        Ets_megamenu_defines::alterSQL('ets_mm_block','display_mnu_inline', 'INT(1) NOT NULL ').
        Ets_megamenu_defines::alterSQL('ets_mm_block','display_suppliers_img', 'INT(1) NOT NULL ').
        Ets_megamenu_defines::alterSQL('ets_mm_block','display_suppliers_name', 'INT(1) NOT NULL ').
        Ets_megamenu_defines::alterSQL('ets_mm_block','display_suppliers_inline', 'INT(1) NOT NULL ').
        Ets_megamenu_defines::alterSQL('ets_mm_menu','enabled_vertical', 'INT(1) NOT NULL ').
        Ets_megamenu_defines::alterSQL('ets_mm_menu','tab_item_width', 'varchar(50) NOT NULL ').
        Ets_megamenu_defines::alterSQL('ets_mm_menu','menu_item_width', 'varchar(50) NOT NULL ').
        Ets_megamenu_defines::alterSQL('ets_mm_menu','background_image', 'varchar(50) NOT NULL ').
        Ets_megamenu_defines::alterSQL('ets_mm_menu','position_background', 'varchar(50) NOT NULL ').
        Ets_megamenu_defines::alterSQL('ets_mm_menu','menu_ver_text_color', 'varchar(50) NOT NULL ').
        Ets_megamenu_defines::alterSQL('ets_mm_menu','menu_ver_background_color', 'varchar(50) NOT NULL ').
        Ets_megamenu_defines::alterSQL('ets_mm_menu','menu_ver_hidden_border', 'varchar(50) NOT NULL ').
        Ets_megamenu_defines::alterSQL('ets_mm_column','id_tab', 'INT(11) NOT NULL')
    );
    $object->registerHook('displayCustomMenu');
    $object->registerHook('displayCustomerInforTop');
    $object->registerHook('displaySearch');
    $object->registerHook('displayCartTop');
    $object->registerHook('displayMMItemTab');
    return true;
}