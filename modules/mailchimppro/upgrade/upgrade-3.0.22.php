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

function upgrade_module_3_0_22(Mailchimppro $object)
{
    // Configuration::updateGlobalValue(MailchimpProConfig::SELECTED_PRESET, 'custom');
    Configuration::updateGlobalValue('module-mailchimpproconfig-selected-preset', 'custom');

    return $object->registerHook('dashboardZoneOne') &&
           $object->updatePosition(Hook::getIdByName('dashboardZoneOne'), 0, 1);
}
