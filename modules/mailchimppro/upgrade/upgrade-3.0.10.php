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

function upgrade_module_3_0_10(Mailchimppro $object)
{
    $request = 'SELECT count(*) FROM `'. _DB_PREFIX_ . 'mailchimppro_jobs`';

    $jobs_count = Db::getInstance()->getValue($request);

    $result = Db::getInstance()->delete('mailchimppro_jobs');

    Configuration::updateGlobalValue(MailchimpProConfig::DELETED_JOBS_ON_UPGRADE_COUNT, $jobs_count);
    Configuration::updateGlobalValue(MailchimpProConfig::DELETED_JOBS_ON_UPGRADE_DATE, date('Y-m-d H:i:s'));
    Configuration::updateGlobalValue(MailchimpProConfig::DELETED_JOBS_ON_UPGRADE_ACCEPTED_MESSAGE_DATE, null);
    Configuration::updateGlobalValue(MailchimpProConfig::DELETED_JOBS_ON_UPGRADE_ACCEPTED_MESSAGE_EMPLOYEE, null);

    return true;
}
