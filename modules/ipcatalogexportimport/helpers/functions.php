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

if (!function_exists('ddd')) {

    function ddd($d, $var_dump = false)
    {
        echo '<pre>';
        if ($var_dump) {
            var_dump($d);
        } else {
            print_r($d);
        }
        die;
    }

}
