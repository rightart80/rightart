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

namespace PrestaChamps\MailchimpPro\Models;

if (!defined('_PS_VERSION_')) {
    exit;
}

use ObjectModel;

abstract class BaseModel extends ObjectModel
{
    /**
     * Get table name with the prefix.
     *
     * @return string
     */
    public static function getTableName()
    {
        return _DB_PREFIX_ . static::$definition['table'];
    }

    /**
     * Get the primary key field for the table.
     *
     * @return string
     */
    public static function getPrimaryKey()
    {
        return static::$definition['primary'];
    }
}
