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
 *
 * Class AdminMailchimpProCustomersController
 */
if (!defined('_PS_VERSION_')) {
    exit;
}
class AdminMailchimpProCustomersController  extends \PrestaChamps\MailchimpPro\Controllers\BaseMCObjectController
{
    public $entityPlural   = 'customers';
    public $entitySingular = 'customer';

    public function initContent()
    {
        $this->addCSS($this->module->getLocalPath() . 'views/css/main.css');
        if (\Shop::getContext() !== \Shop::CONTEXT_SHOP) {
            $this->content = '';
            $this->warnings[] = $this->trans('Please select a shop', [], 'Modules.Mailchimppro.Adminmailchimpprocustomers');
        } else {
            parent::initContent();
        }
    }
}
