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
 * Class AdminMailchimpProReportsController
 *
 * @property Mailchimppro $module
 */
class AdminMailchimpProReportsController extends \PrestaChamps\MailchimpPro\Controllers\BaseMCObjectController
{
	public $entityPlural   = 'reports';
    public $entitySingular = 'report';
	
	protected function getListApiEndpointUrl()
    {
        return '/reports';
    }

    protected function getSingleApiEndpointUrl($entityId)
    {
        return "/reports/{$entityId}";
    }
}