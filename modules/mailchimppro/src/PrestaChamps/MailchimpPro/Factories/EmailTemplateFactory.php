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

namespace PrestaChamps\MailchimpPro\Factories;
if (!defined('_PS_VERSION_')) {
    exit;
}
use PrestaChamps\MailChimpAPI;

/**
 * Class EmailTemplateFactory
 *
 * @package PrestaChamps\MailchimpPro\Factories
 */
class EmailTemplateFactory
{
    /**
     * @param           $templateName
     * @param           $templateContent
     * @param           $listApiEndpointUrl
     * @param MailChimp $mailChimp
     * @param \Context  $context
     *
     * @return array|false
     * @throws \Exception
     */
    public static function make($templateName, $templateContent, $editing, $listApiEndpointUrl, MailChimpAPI $mailChimp, \Context $context)
    {
        $data = [
            'name' => $templateName,
            'html' => $templateContent,
        ];
        
        if ($editing) {
            $result = $mailChimp->patch($listApiEndpointUrl, $data);
        }
        else {
            $result = $mailChimp->post($listApiEndpointUrl, $data);
        }        
        if ($mailChimp->success()) {
            return $result;
        }

        throw new \Exception($mailChimp->getLastError() . $mailChimp->getLastResponse()['body']);
    }
}
