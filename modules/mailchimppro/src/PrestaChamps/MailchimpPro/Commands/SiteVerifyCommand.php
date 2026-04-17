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

namespace PrestaChamps\MailchimpPro\Commands;
if (!defined('_PS_VERSION_')) {
    exit;
}
use PrestaChamps\MailChimpAPI;
use PrestaChamps\MailchimpPro\Exceptions\MailChimpException;

/**
 * Verify that the connected sites script has been installed
 *
 * @package PrestaChamps\MailchimpPro\Services
 */
class SiteVerifyCommand extends BaseApiCommand
{
    public $mailchimp;
    public $siteId;

    public function __construct(MailChimpAPI $mailchimp, $siteId)
    {
        $this->mailchimp = $mailchimp;
        $this->siteId = $siteId;
    }

    /**
     * @return bool
     * @throws MailChimpException
     */
    public function execute()
    {
        $formattedId = \Mailchimppro::shopIdTransformer($this->siteId);
        
        $this->mailchimp->post("connected-sites/{$formattedId}/actions/verify-script-installation");

        if ($this->mailchimp->success()) {
            return true;
        }

        throw new MailChimpException($this->mailchimp->getLastError());
    }
}