<?php
/**
 * MailChimp
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Commercial License
 * you can't distribute, modify or sell this code
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file
 * If you need help please contact leo@prestachamps.com
 *
 * @author    Mailchimp
 * @copyright Mailchimp
 * @license   commercial
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

function upgrade_module_3_0_17(Mailchimppro $object)
{

    $pf = _DB_PREFIX_ . "{$object->name}_";

    return Db::getInstance()->execute("
                CREATE TABLE IF NOT EXISTS `{$pf}api_log` (
                    `id` INT NOT NULL AUTO_INCREMENT,
                    `request_type` VARCHAR(50) NOT NULL,
                    `end_point` VARCHAR(255) NOT NULL,
                    `back_trace` LONGTEXT NULL,
                    `is_success` BOOLEAN,
                    `created_at` DATETIME NULL,
                    PRIMARY KEY (`id`)
                ) ENGINE = InnoDB;
            ") &&
            $object->unregisterHook('actionFrontControllerSetMedia');
}
