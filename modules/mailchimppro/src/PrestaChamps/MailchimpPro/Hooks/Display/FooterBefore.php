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

namespace PrestaChamps\MailchimpPro\Hooks\Display;
if (!defined('_PS_VERSION_')) {
    exit;
}
use Context;
use PrestaChamps\MailChimpAPI;
use Module;
use Tools;
use PrestaChamps\Queue\Queue;
use PrestaChamps\Queue\Jobs\NewsletterSubscriberSyncJob;
use PrestaChamps\MailchimpPro\Commands\NewsletterSubscriberSyncCommand;

/**
 * Class FooterBefore
 * @package PrestaChamps\MailchimpPro\Hooks\Display
 */
class FooterBefore
{
    /**
     * @var array
     */
    private $_params;

    /**
     * @var MailChimpAPI
     */
    private $_mailChimp;

    /**
     * @var Context
     */
    private $_context;

    protected function __construct($params, MailChimpAPI $mailChimp, Context $context)
    {
        $this->_params = $params;
        $this->_mailChimp = $mailChimp;
        $this->_context = $context;
    }

    public static function run($params, MailChimpAPI $mailchimp, Context $context)
    {
        return new FooterBefore($params, $mailchimp, $context);
    }

    public function newsletterBlockRegistration()
    {
        if (\Configuration::get(\MailchimpProConfig::SYNC_NEWSLETTER_SUBSCRIBERS)) {
            $subscriptionIsEnabled = Module::isEnabled('Ps_Emailsubscription')
                || Module::isEnabled('blocknewsletter');
            if (Tools::isSubmit('submitNewsletter') && $subscriptionIsEnabled) {
                $newsletterSubscriber['email'] = Tools::getValue('email');
                if (isset($this->_context->language->id) && $this->_context->language->id) {
                    $newsletterSubscriber['id_lang'] = $this->_context->language->id;
                }
                if (!\Configuration::get(\MailchimpProConfig::CRONJOB_BASED_SYNC)) {
                    $command = new NewsletterSubscriberSyncCommand(
                        Context::getContext(),
                        Module::getInstanceByName('mailchimppro')->getApiClient(),
                        [$newsletterSubscriber]
                    );
                    $command->setSyncMode($command::SYNC_MODE_REGULAR);
                    $command->setMethod($command::SYNC_METHOD_PUT);
                    return $command->execute();
                } else {
                    $job = new NewsletterSubscriberSyncJob();
                    $job->newsletterSubscriber = $newsletterSubscriber;
                    $job->setSyncMode(NewsletterSubscriberSyncCommand::SYNC_MODE_REGULAR);
                    $job->setMethod(NewsletterSubscriberSyncCommand::SYNC_METHOD_PUT);
                    $queue = new Queue();
                    $queue->push($job, 'hook-footer-before', $this->_context->shop->id);
                    return true;
                }
            }
        }
        return false;
    }
}
