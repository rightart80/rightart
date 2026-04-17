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

namespace PrestaChamps\MailchimpPro\Hooks\Action;
if (!defined('_PS_VERSION_')) {
    exit;
}
use Context;
use PrestaChamps\MailChimpAPI;
use PrestaChamps\MailchimpPro\Commands\CartSyncCommand;
use PrestaChamps\Queue\Jobs\CartSyncJob;
use PrestaChamps\Queue\Queue;

/**
 * Class CartSave
 * @package PrestaChamps\MailchimpPro\Hooks\Action
 */
class CartSave
{
    private $cart;
    private $context;
    /**
     * @var MailChimp
     */
    private $mailchimp;
    private $configuration;

    private function __construct(Context $context, MailChimpAPI $mailChimp)
    {
        $this->context = $context;
        $this->mailchimp = $mailChimp;
        $this->configuration = \MailchimpProConfig::getConfigurationValues();
    }

    public static function run(Context $context, MailChimpAPI $mailChimp)
    {
        (new CartSave($context, $mailChimp))->syncCart();
    }

    protected function syncCart()
    {
        if (\Configuration::get(\MailchimpProConfig::SYNC_CARTS)) {
            $cartId = isset($this->context->cart->id) ? $this->context->cart->id : false;
            $customerId = isset($this->context->customer->id) ? $this->context->customer->id : false;
            if ($cartId && $customerId) {
                $customer = new \Customer($customerId);
                // check filter customers to sync
                if(in_array($customer->active, $this->configuration[\MailchimpProConfig::CUSTOMER_SYNC_FILTER_ENABLED]) && 
                    in_array($customer->newsletter, $this->configuration[\MailchimpProConfig::CUSTOMER_SYNC_FILTER_NEWSLETTER])){

                    if (!\Configuration::get(\MailchimpProConfig::CRONJOB_BASED_SYNC)) {
                        $command = new CartSyncCommand($this->context, $this->mailchimp, [$cartId]);
                        $command->setSyncMode($command::SYNC_MODE_REGULAR);
                        //if ($command->getCartExists($cartId)) {
                            $command->setMethod($command::SYNC_METHOD_DELETE);
                            $command->execute();
                        //}
                        if ($this->context->cart->nbProducts()) {
                            $command->setMethod($command::SYNC_METHOD_POST);
                            $command->execute();
                        }
                    }
                    else {
                        $job = new CartSyncJob();
                        $job->cartId = $cartId;
                        if (isset($_COOKIE['mc_cid']) && !empty($_COOKIE['mc_cid']) && !is_a($this->context->controller, 'AdminController') && !is_subclass_of($this->context->controller, 'AdminController')) {
                            $job->setCampaignId($_COOKIE['mc_cid']);
                        }
                        $job->setSyncMode(CartSyncCommand::SYNC_MODE_REGULAR);
                        $queue = new Queue();
                        $queue->push($job, 'hook-cart-save', $this->context->shop->id);
                    }
                }
            }
        }
    }
}
