<?php
/**
* 2007-2021 PrestaShop
*
* NOTICE OF LICENSE
*
* This source file is subject to theCommercial License!
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/afl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to license@prestashop.com so we can send you a copy immediately.
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customize PrestaShop for your
* needs please refer to http://www.prestashop.com for more information.
*
*  @author    PrestaShop SA <contact@prestashop.com>
*  @copyright 2007-2021 PrestaShop SA
*  @license   http://opensource.org/licenses/afl-3.0.php Commercial License!
*  International Registered Trademark & Property of PrestaShop SA
*/

if (!defined('_PS_VERSION_')) {
    exit;
}

/**
 * This function updates your module from previous versions to the version 1.1,
 * usefull when you modify your database, or register a new hook ...
 * Don't forget to create one file per version.
 */
function upgrade_module_3_2_9($module)
{
    return Db::getInstance()->execute('CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'ipcatalogimport_url` (
                                        `id_ipcatalogimport_url` INT(11) NOT NULL AUTO_INCREMENT,
                                        `template` VARCHAR(100) NULL,
                                        `url` VARCHAR(250) NULL,
                                        `entity` VARCHAR(100) NULL,
                                        `active` TINYINT(1) NULL DEFAULT 1,
                                        PRIMARY KEY (`id_ipcatalogimport_url`)
                                    ) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8;');
}
