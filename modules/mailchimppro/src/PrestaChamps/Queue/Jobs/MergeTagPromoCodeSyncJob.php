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
use PrestaChamps\MailchimpPro\Commands\PromoMergeTagSyncCommand;
use PrestaChamps\Queue\JobInterface;
use PrestaChamps\MailchimpPro\Models\Campaign;
use PrestaChamps\MailchimpPro\Models\PromoCode;

class MergeTagPromoCodeSyncJob implements JobInterface
{
    public $promoCodeId;

    protected $promoCampaignId;
    protected $promoCode;
    protected $promoEmail;
    protected $mergeTagPromoCode;
    protected $syncMode;
    protected $method;

    const JOB_TYPE = "mergeTagPromoCode";

    protected $idStore;

    public function execute($idStore = null)
    {
        if($idStore){
            $this->idStore = $idStore;
        }else{
            $this->idStore = Context::getContext()->shop->id;
        }

        // dump(new Campaign($this->promoCampaignId));
        // die();

        $command = new PromoMergeTagSyncCommand(
            Context::getContext(),
            Module::getInstanceByName('mailchimppro')->getApiClient($this->idStore),
            new Campaign($this->promoCampaignId),
            [$this->promoCodeId],
            $this->idStore
        );

        if (isset($this->syncMode) && $this->syncMode) {
            $command->setSyncMode($this->syncMode);
        }
        else {
            $command->setSyncMode($command::SYNC_MODE_REGULAR);
        }

        $command->setMethod($command::SYNC_METHOD_PATCH);

        return $command->execute();
        
    }

    /**
     * Set sync mode
     *
     * @param string $syncMode
     */
    public function setSyncMode($syncMode = PromoMergeTagSyncCommand::SYNC_MODE_REGULAR)
    {
        $this->syncMode = $syncMode;
    }

    /**
     * Set sync method
     *
     * @param string $method
     */
    public function setMethod($method = PromoMergeTagSyncCommand::SYNC_METHOD_PATCH)
    {
        $this->method = $method;
    }

    public function setCampaignId($campaign_id)
    {
        $this->promoCampaignId = $campaign_id;
    }
    
    public function setPromoEmail($email)
    {
        $this->promoEmail = $email;
    }
    
    public function setPromoCode($promo_code)
    {
        $this->promoCode = $promo_code;
    }

    public function setMergeTagPromoCode($merge_tag)
    {
        $this->mergeTagPromoCode = $merge_tag;
    }

    public function getJobType()
    {
        return self::JOB_TYPE;
    }

    public function getJobId()
    {
        return $this->promoCodeId;
    }

    public function convertToArrayJson()
    {
        $arrayProperties = array(
            "promoCodeId" => $this->promoCodeId,
            "promoCampaignId" => $this->promoCampaignId,
            "promoCode" => $this->promoCode,
            "mergeTagPromoCode" => $this->mergeTagPromoCode,  /// promo kode tablabol kivenni? vagy jobhoz hozzaadni? 
            "promoEmail" => $this->promoEmail,
            "syncMode" => $this->syncMode,
            "method" => $this->method,
            "idStore" => is_null($this->idStore) ? Context::getContext()->shop->id : $this->idStore,
            
        );

        return json_encode($arrayProperties);
    }

    public function __construct($arrayPropertiesJson = null)
    {
        if($arrayPropertiesJson){
            $arrayProperties = json_decode($arrayPropertiesJson);

            $this->promoCodeId = $arrayProperties->promoCodeId;
            $this->promoCampaignId = $arrayProperties->promoCampaignId;
            $this->promoCode = $arrayProperties->promoCode;
            $this->mergeTagPromoCode = $arrayProperties->mergeTagPromoCode;
            $this->promoEmail = $arrayProperties->promoEmail;
            $this->syncMode = $arrayProperties->syncMode;
            $this->method = $arrayProperties->method;
            $this->idStore = $arrayProperties->idStore;
        }
    }
}
