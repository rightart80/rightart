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

function upgrade_module_3_0_0(Mailchimppro $object)
{
    $menus = [
        [
            'is_root' => false,
            'name' => 'Mailchimp Configuration',
            'class_name' => 'AdminMailchimpProConfiguration',
            'visible' => true,
            'parent_class_name' => 'mailchimppro',
        ],
        [
            'is_root' => false,
            'name' => 'Queue Work',
            'class_name' => 'AdminMailchimpProQueue',
            'visible' => true,
            'parent_class_name' => 'mailchimppro',
        ],
        [
            'is_root' => false,
            'name' => 'Email templates',
            'class_name' => 'AdminMailchimpProEmailTemplates',
            'visible' => false,
            'parent_class_name' => 'mailchimppro',
        ],
        [
            'is_root' => false,
            'name' => 'Tags',
            'class_name' => 'AdminMailchimpProTags',
            'visible' => true,
            'parent_class_name' => 'mailchimppro',
        ],
        [
            'is_root' => false,
            'name' => 'Segments',
            'class_name' => 'AdminMailchimpProSegments',
            'visible' => false,
            'parent_class_name' => 'mailchimppro',
        ],
        [
            'is_root' => false,
            'name' => 'Campaigns',
            'class_name' => 'AdminMailchimpProCampaigns',
            'visible' => false,
            'parent_class_name' => 'mailchimppro',
        ],
        [
            'is_root' => false,
            'name' => 'Statistics',
            'class_name' => 'AdminMailchimpProReports',
            'visible' => false,
            'parent_class_name' => 'mailchimppro',
        ],
    ];
    
    $tabRepository = new \PrestaChamps\PrestaShop\Tab\TabRepository($menus, 'mailchimppro');
    $tabRepository->install();
    
    $activeTabs = ['mailchimppro', 'AdminMailchimpProConfiguration', 'AdminMailchimpProQueue', 'AdminMailchimpProTags'];
    $tabs = Tab::getCollectionFromModule($object->name);
    if (!empty($tabs)) {
        foreach ($tabs as $tab) {
            $change = false;
            if (in_array($tab->class_name, $activeTabs)) {
                if (!$tab->active) {
                    $tab->active = 1;
                    $change = true;
                }
            }
            else {
                if ($tab->active) {
                    $tab->active = 0;
                    $change = true;
                }
            }
            if ($change) {
                $tab->save();
            }
        }
    }
    
    $pf = _DB_PREFIX_ . "{$object->name}_";
    return $object->registerHook('actionObjectAddAfter') &&
        $object->registerHook('actionObjectAddressAddAfter') &&
        Db::getInstance()->execute("
            CREATE TABLE IF NOT EXISTS `{$pf}jobs` (
                `id_job` INT NOT NULL AUTO_INCREMENT,
                `id_entity` INT NULL,
                `type` VARCHAR(50)  NOT NULL,
                `channel` TINYTEXT  NOT NULL,
                `status` TINYINT NOT NULL DEFAULT 0,
                `attempts` SMALLINT DEFAULT 0,
                `body` MEDIUMTEXT NOT NULL,
                `error` VARCHAR(255) NULL,
                `created_at` DATETIME NULL,
                `locked_at` DATETIME NULL,
                `priority` SMALLINT DEFAULT 1,
                `id_shop` INT NOT NULL DEFAULT 1,
                PRIMARY KEY (`id_job`),
                CONSTRAINT entity_type_shop UNIQUE (id_entity,type,id_shop)
                ) ENGINE = InnoDB;
        ") && 
        Db::getInstance()->execute("
            CREATE TABLE IF NOT EXISTS `{$pf}specific_price` (
                `id_specific_price` INT NOT NULL,
                `id_product` INT NOT NULL,
                `start_date` DATETIME NOT NULL,
                `end_date` DATETIME NOT NULL,
                `needToRun` SMALLINT NOT NULL DEFAULT 0,
                `id_shop` INT NOT NULL DEFAULT 1,
                PRIMARY KEY (`id_specific_price`)
                ) ENGINE = InnoDB;
        ");
}
