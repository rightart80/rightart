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

namespace PrestaChamps\MailchimpPro\Hooks\Action\Customer;
if (!defined('_PS_VERSION_')) {
    exit;
}
use Context;
use Customer;
use PrestaChamps\MailChimpAPI;
use PrestaChamps\MailchimpPro\Commands\CartSyncCommand;
use PrestaChamps\MailchimpPro\Commands\CustomerSyncCommand;
use PrestaChamps\Queue\Jobs\CustomerSyncJob;
use PrestaChamps\Queue\Jobs\CartSyncJob;
use PrestaChamps\Queue\Queue;

/**
 * Invoked when a new customer creates an account successfully
 *
 * @package PrestaChamps\MailchimpPro\Hooks\Action\Customer
 */
class AccountAdd
{
    protected $context;
    protected $customer;
    protected $mailchimp;

    /**
     * AccountAdd constructor
     *
     * @param Customer $customer
     * @param MailChimpAPI $mailchimp
     * @param Context $context
     */
    protected function __construct(Customer $customer, MailChimpAPI $mailchimp, Context $context)
    {
        $this->context = $context;
        $this->customer = $customer;
        $this->mailchimp = $mailchimp;

        $this->handleGuestCheckoutAbandonedMail();
    }

    public static function run(Context $context, MailChimpAPI $mailchimp, Customer $customer)
    {
        new AccountAdd($customer, $mailchimp, $context);
    }

    protected function handleGuestCheckoutAbandonedMail()
    {
        $this->syncCustomer();
        $this->syncCart();
    }

    protected function syncCustomer()
    {
        if (\Configuration::get(\MailchimpProConfig::SYNC_CUSTOMERS)) {
            if (!\Configuration::get(\MailchimpProConfig::CRONJOB_BASED_SYNC)) {
                $command = new CustomerSyncCommand($this->context, $this->mailchimp, [$this->customer->id]);
                $command->triggerDoubleOptIn(true);
                $command->setSyncMode($command::SYNC_MODE_REGULAR);
                $command->setMethod($command::SYNC_METHOD_POST);
                return $command->execute();
            } else {
                $job = new CustomerSyncJob();
                $job->customerId = $this->customer->id;
                $job->triggerDoubleOptIn(true);
                $job->setSyncMode(CustomerSyncCommand::SYNC_MODE_REGULAR);
                $job->setMethod(CustomerSyncCommand::SYNC_METHOD_POST);
                $queue = new Queue();
                $queue->push($job, 'hook-account-add', $this->context->shop->id);
                return true;
            }
        }
    }

    protected function syncCart()
    {
        if (\Configuration::get(\MailchimpProConfig::SYNC_CARTS)) {
            $cartId = isset($this->context->cart->id) ? $this->context->cart->id : false;
            if ($cartId && $this->context->cart->nbProducts() && $this->customer->id) {
                if (!\Configuration::get(\MailchimpProConfig::CRONJOB_BASED_SYNC)) {
                    $command = new CartSyncCommand($this->context, $this->mailchimp, [$cartId]);
                    $command->setSyncMode($command::SYNC_MODE_REGULAR);
                    //if ($command->getCartExists($cartId)) {
                        $command->setMethod($command::SYNC_METHOD_DELETE);
                        $command->execute();
                    //}
                    $command->setMethod($command::SYNC_METHOD_POST);
                    return $command->execute();
                } else {
                    $job = new CartSyncJob();
                    $job->cartId = $cartId;
                    $job->setSyncMode(CartSyncCommand::SYNC_MODE_REGULAR);
                    if (isset($_COOKIE['mc_cid']) && !empty($_COOKIE['mc_cid']) && !is_a($this->context->controller, 'AdminController') && !is_subclass_of($this->context->controller, 'AdminController')) {
                        $job->setCampaignId($_COOKIE['mc_cid']);
                    }
                    $queue = new Queue();
                    $queue->push($job, 'hook-account-add', $this->context->shop->id);
                    return true;
                }
            }
        }
    }
}
