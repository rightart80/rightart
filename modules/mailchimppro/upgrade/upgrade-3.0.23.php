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

function upgrade_module_3_0_23(Mailchimppro $object)
{

    $menus = [
        [
            'is_root' => false,
            'name' => 'PromoStats',
            'class_name' => 'AdminMailchimpProStats',
            'visible' => false,
            'parent_class_name' => 'mailchimppro',
        ],
    ];
    
    $tabRepository = new \PrestaChamps\PrestaShop\Tab\TabRepository($menus, 'mailchimppro');
    $tabRepository->install();

    $pf = _DB_PREFIX_ . "{$object->name}_";


    return Db::getInstance()->execute("
                CREATE TABLE IF NOT EXISTS `{$pf}campaign` (
                    `id` int(11) NOT NULL AUTO_INCREMENT,
                    `name` varchar(255) NOT NULL,
                    `prefix` varchar(255) DEFAULT NULL,
                    `suffix` varchar(255) DEFAULT NULL,
                    `reduction` varchar(255) NOT NULL,
                    `reduction_type` int(11) NOT NULL,
                    `status` tinyint(4) NOT NULL,
                    `campaign_id` varchar(255) NOT NULL,
                    `id_merge_field_mc` int(11) DEFAULT NULL,
                    `tag_merge_field_mc` varchar(255) DEFAULT NULL,
                    `end_date` datetime NOT NULL,
                    `date_add` datetime DEFAULT NULL,
                    `date_upd` datetime DEFAULT NULL,
                    PRIMARY KEY (`id`)
                ) ENGINE = InnoDB;
            ") &&
            Db::getInstance()->execute("
                CREATE TABLE IF NOT EXISTS `{$pf}promocodes` (
                    `id` int(11) NOT NULL AUTO_INCREMENT,
                    `id_promo_main` int(11) NOT NULL,
                    `code` varchar(255) NOT NULL,
                    `status` tinyint(4) NOT NULL,
                    `sent_to_mc` tinyint(4) NOT NULL,
                    `id_cart` int(10) DEFAULT NULL,
                    `email` varchar(255) DEFAULT NULL,
                    `date_reddeem` datetime DEFAULT NULL,
                    `date_add` datetime DEFAULT NULL,
                    `date_upd` datetime DEFAULT NULL,
                    PRIMARY KEY (`id`)
                ) ENGINE = InnoDB;
            ");

}
