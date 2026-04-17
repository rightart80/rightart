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
use PrestaChamps\MailchimpPro\Commands\CartRuleSyncCommand;
use PrestaChamps\Queue\JobInterface;
use PrestaChamps\PrestaShop\Traits\ShopIdTrait;

class CartRuleSyncJob implements JobInterface
{
    use ShopIdTrait;
    
    public $cartRuleId;
    protected $syncMode;
    protected $method;

    const JOB_TYPE = "cartRule";

    protected $idStore;

    public function execute($idStore = null)
    {
        if($idStore){
            $this->idStore = $idStore;
        }else{
            $this->idStore = Context::getContext()->shop->id;
        }
        
        $objects = [];

        $object = new \CartRule($this->cartRuleId, Context::getContext()->language->id, $idStore);
        if (\Validate::isLoadedObject($object)) {
            $objects[] = $object;
        }
        $command = new CartRuleSyncCommand(
            Context::getContext(),
            Module::getInstanceByName('mailchimppro')->getApiClient($this->idStore),
            $objects,
            $this->idStore
        );

        if (isset($this->syncMode) && $this->syncMode) {
            $command->setSyncMode($this->syncMode);
        } else {
            $command->setSyncMode($command::SYNC_MODE_REGULAR);
        }

        if (isset($this->method) && $this->method) {
            $command->setMethod($this->method);
            $result = $command->execute();
        } else {
            // $command->setMethod(
            //     $command->getCartRuleExists($this->cartRuleId)
            //     ? $command::SYNC_METHOD_PATCH
            //     : $command::SYNC_METHOD_POST
            // );
            $command->setMethod($command::SYNC_METHOD_PATCH); 

            $result = $command->execute();

            if (isset($result["requestSuccess"]) && $result["requestSuccess"] == false) {
                $command->setMethod($command::SYNC_METHOD_POST);
                $result = $command->execute();                
            }
        }

        return $result;
    }

    /**
     * Set sync mode
     *
     * @param string $syncMode
     */
    public function setSyncMode($syncMode = CartRuleSyncCommand::SYNC_MODE_REGULAR)
    {
        $this->syncMode = $syncMode;
    }

    /**
     * Set sync method
     *
     * @param string $method
     */
    public function setMethod($method = CartRuleSyncCommand::SYNC_METHOD_PUT)
    {
        $this->method = $method;
    }

    public function getJobType()
    {
        return self::JOB_TYPE;
    }

    public function getJobId()
    {
        return $this->cartRuleId;
    }

    public function convertToArrayJson()
    {
        $arrayProperties = array(
            // "jobType" => $this->getJobType(),
            "cartRuleId" => $this->cartRuleId,
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

            $this->cartRuleId = $arrayProperties->cartRuleId;
            $this->syncMode = $arrayProperties->syncMode;
            $this->method = $arrayProperties->method;
            $this->idStore = $arrayProperties->idStore;
        }            
    }
}
