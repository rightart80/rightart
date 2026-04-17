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
/**
 * @param Ets_megamenu $module
 * @return bool
 */
function upgrade_module_2_2_9($module)
{
    if(!is_dir(_PS_ETS_MM_IMG_DIR_))
    {
        @mkdir(_PS_ETS_MM_IMG_DIR_,0755,true);
    }
    $module->copy_directory(dirname(__FILE__).'/../views/img/upload',_PS_ETS_MM_IMG_DIR_);
    $module->rrmdir(dirname(__FILE__).'/../views/img/upload');
    return true;
}