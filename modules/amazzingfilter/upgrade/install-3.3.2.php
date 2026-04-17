<?php
/**
 *  @author    Amazzing <mail@mirindevo.com>
 *  @copyright Amazzing
 *  @license   https://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 */
if (!defined('_PS_VERSION_')) {
    exit;
}

function upgrade_module_3_3_2($module_obj)
{
    Media::clearCache();
    $module_obj->cache('clear', '');
    $module_obj->bo()->autoFillSettings('general'); // save compact_external
    if ($module_obj->is_modern) {
        $module_obj->registerHook('actionAfterUpdateProductFormHandler');
    }
    $to_unlink = [
        '/views/css/specific/classic-17.css',
        '/views/css/specific/modez-17.css',
        '/views/css/specific/warehouse-17.css',
        '/views/js/specific/ZOneTheme-17.js',
        '/views/js/specific/akira-17.js',
        '/views/js/specific/alysum-17.js',
        '/views/js/specific/at_classico-17.js',
        '/views/js/specific/at_decor-17.js',
        '/views/js/specific/at_movic-17.js',
        '/views/js/specific/at_oreo-17.js',
        '/views/js/specific/classic-rocket-17.js',
        '/views/js/specific/classic-17.js',
        '/views/js/specific/modez-17.js',
        '/views/js/specific/panda-17.js',
        '/views/js/specific/transformer-17.js',
        '/views/js/specific/venedor-17.js',
        '/views/js/specific/warehouse-17.js',
        '/views/templates/front/basic-layout-17.tpl',
    ];
    $deleted_num = 0;
    foreach ($to_unlink as $relative_path) {
        $path = _PS_MODULE_DIR_ . $module_obj->name . $relative_path;
        if (is_file($path) && unlink($path)) {
            ++$deleted_num;
        }
    }
    if ($deleted_num) {
        $module_obj->log('add', $deleted_num . ' unused files deleted');
    }
    $module_obj->log('add', 'auto-upgrade applied for v3.3.2');

    return true;
}
