<?php
/**
 * PrestaChamps
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
/**
 * Class AdminMailchimpProCartsController
 *
 * @property Mailchimppro $module
 */
class AdminMailchimpProStoresController extends \PrestaChamps\MailchimpPro\Controllers\BaseMCObjectController
{
    public $entityPlural   = 'stores';
    public $entitySingular = 'store';

    protected function getListApiEndpointUrl()
    {
        return '/ecommerce/stores';
    }

    protected function deleteEntity($id)
    {
        $this->mailchimp->delete("/ecommerce/stores/{$id}");

        if ($this->mailchimp->success()) {
            if ($id == $this->getShopId()) {
                \MailchimpProConfig::saveValue(\MailchimpProConfig::MAILCHIMP_STORE_SYNCED, false);
            }
            return true;
        }
        return false;
    }
}
