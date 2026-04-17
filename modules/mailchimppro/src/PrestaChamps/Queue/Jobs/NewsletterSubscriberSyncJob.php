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

namespace PrestaChamps\Queue\Jobs;
if (!defined('_PS_VERSION_')) {
    exit;
}
use Context;
use Module;
use Tools;
use PrestaChamps\MailchimpPro\Commands\NewsletterSubscriberSyncCommand;
use PrestaChamps\Queue\JobInterface;

class NewsletterSubscriberSyncJob implements JobInterface
{
    public $newsletterSubscriber;
    protected $triggerDoubleOptIn;
    protected $updateSubscriptionStatus;
    protected $syncMode;
    protected $method;

    const JOB_TYPE = "newsletterSubscriber";

    protected $idStore;

    public function execute($idStore = null)
    {
        if($idStore){
            $this->idStore = $idStore;
        }else{
            $this->idStore = Context::getContext()->shop->id;
        }
        
        $newsletterSubscribers = [$this->newsletterSubscriber];
        $command = new NewsletterSubscriberSyncCommand(
            Context::getContext(),
            Module::getInstanceByName('mailchimppro')->getApiClient($this->idStore),
            $newsletterSubscribers,
            $this->idStore
        );

        if (isset($this->updateSubscriptionStatus)) {
            $command->setUpdateSubscriptionStatus($this->updateSubscriptionStatus);
        }

        if (isset($this->syncMode) && $this->syncMode) {
            $command->setSyncMode($this->syncMode);
        }
        else {
            $command->setSyncMode($command::SYNC_MODE_REGULAR);
        }

        if (isset($this->method) && $this->method) {
            $command->setMethod($this->method);
        }
        else {
            $command->setMethod($command::SYNC_METHOD_PUT);
        }

        return $command->execute();
    }

    /**
     * Set sync mode
     *
     * @param string $syncMode
     */
    public function setSyncMode($syncMode = NewsletterSubscriberSyncCommand::SYNC_MODE_REGULAR)
    {
        $this->syncMode = $syncMode;
    }

    /**
     * Trigger DoubleOptIn feature
     *
     * @param bool $trigger
     */
    public function triggerDoubleOptIn($trigger = true)
    {
        $this->triggerDoubleOptIn = (bool)$trigger;
    }

    /**
     * Set update subscription status
     *
     * @param bool $update
     */
    public function setUpdateSubscriptionStatus($update = true)
    {
        $this->updateSubscriptionStatus = (bool)$update;
    }

    /**
     * Set sync method
     *
     * @param string $method
     */
    public function setMethod($method = NewsletterSubscriberSyncCommand::SYNC_METHOD_PUT)
    {
        $this->method = $method;
    }

    public function getJobType()
    {
        return self::JOB_TYPE;
    }

    public function getJobId()
    {
        return isset($this->newsletterSubscriber['id']) ? $this->newsletterSubscriber['id'] : crc32($this->newsletterSubscriber['email']);
    }

    public function convertToArrayJson()
    {
        $arrayProperties = array(
            // "jobType" => $this->getJobType(),
            "newsletterSubscriber" => $this->newsletterSubscriber,
            // "triggerDoubleOptIn" => $this->triggerDoubleOptIn,
            "updateSubscriptionStatus" => $this->updateSubscriptionStatus,
            "syncMode" => $this->syncMode,
            "method" => $this->method,
            "idStore" => is_null($this->idStore) ? Context::getContext()->shop->id : $this->idStore
        );

        return json_encode($arrayProperties);
    }

    public function __construct($arrayPropertiesJson = null)
    {
        if($arrayPropertiesJson){
            $arrayProperties = json_decode($arrayPropertiesJson);

            if(isset($arrayProperties->newsletterSubscriber->id)){
                $this->newsletterSubscriber['id'] = $arrayProperties->newsletterSubscriber->id;
            }
            if(isset($arrayProperties->newsletterSubscriber->email)){
                $this->newsletterSubscriber['email'] = $arrayProperties->newsletterSubscriber->email;
            }
            if(isset($arrayProperties->newsletterSubscriber->id_lang)){
                $this->newsletterSubscriber['id_lang'] = $arrayProperties->newsletterSubscriber->id_lang;
            }
            // $this->triggerDoubleOptIn = $arrayProperties->triggerDoubleOptIn;
            $this->updateSubscriptionStatus = $arrayProperties->updateSubscriptionStatus;
            $this->syncMode = $arrayProperties->syncMode;
            $this->method = $arrayProperties->method;
            $this->idStore = $arrayProperties->idStore;
        }
    }
}
