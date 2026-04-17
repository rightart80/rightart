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

use Context;
use PrestaChamps\MailChimpAPI;
use MailchimpProConfig;
use PrestaChamps\MailchimpPro\Formatters\ListMemberPromoMergeTagFormatter;
use PrestaChamps\MailchimpPro\Models\Campaign;
use PrestaChamps\MailchimpPro\Models\PromoCode;
use PrestaShopDatabaseException;
use Tools;

/**
 * Class StoreSyncCommand
 *
 * @package PrestaChamps\MailchimpPro\Commands
 */
class PromoMergeTagSyncCommand extends BaseApiCommand
{
    protected $context;
    protected $promoCodes;
    protected $mailchimp;
    protected $batch;
    protected $batchPrefix = '';
    protected $commands = [];
    protected $campaign;

    protected $idStore;

    /**
     * StoreSyncService constructor.
     *
     * @param \Context  $context
     * @param MailChimpAPI $mailchimp
     * @param array     $storeIds
     */
    public function __construct(Context $context, MailChimpAPI $mailchimp, Campaign $campaign, $promoCodes = [], $idStore = null)
    {
        $this->context = $context;
        $this->mailchimp = $mailchimp;
        $this->batchPrefix = uniqid("PROMO_MERGE_SYNC_", true);
        $this->batch = $this->mailchimp->new_batch($this->batchPrefix);
        $this->promoCodes = $promoCodes;
        $this->campaign = $campaign;
        $this->idStore = $idStore ? $idStore : $this->context->shop->id;
    }

    /**
     * @return array|false
     * @throws \PrestaShopDatabaseException
     * @throws \PrestaShopException
     */
    public function execute()
    {
        $this->responses = [];

        $this->buildPromoMergeTags();

        if ($this->syncMode === self::SYNC_MODE_BATCH) {
            $this->responses['batch'] = $this->batch->execute();
        }

        $allRequestsSuccess = true;
        $requestErrors = [];
        if ($this->syncMode === self::SYNC_MODE_REGULAR) {
            $method = \Tools::strtolower($this->method);
            foreach ($this->commands as $entityId => $params) {
                try {
                    $this->mailchimp->$method($params['route'], $params['data']);
                } catch (\Exception $exception) {
                    continue;
                }

                if (!$this->mailchimp->success()) {
                    $allRequestsSuccess = false;
                    $requestErrors[$entityId] = $this->mailchimp->getLastError();
                }

                $this->responses['entities'][$entityId]['requestSuccess'] = $this->mailchimp->success();
                $this->responses['entities'][$entityId]['requestLastResponse'] = $this->mailchimp->getLastResponse();
                $this->responses['entities'][$entityId]['requestLastError'] = $this->mailchimp->getLastError();
            }
        }

        $this->responses['requestMethod'] = $this->method;
        if (empty($this->responses['requestSuccess'])) {
            $this->responses['requestSuccess'] = $this->syncMode === self::SYNC_MODE_REGULAR ? $allRequestsSuccess : $this->mailchimp->success();
        }
        if (!$this->responses['requestSuccess']) {
            $this->responses['requestLastErrors'] = $this->syncMode === self::SYNC_MODE_REGULAR ? $requestErrors : $this->mailchimp->getLastError();
        }
        $this->responses['requestLastResponse'] = $this->mailchimp->getLastResponse();
        $this->responses['requestSyncMode'] = $this->syncMode === self::SYNC_MODE_REGULAR ? 'regular' : 'batch';

        return $this->responses;
    }

    /**
     * @throws \PrestaShopDatabaseException
     * @throws \PrestaShopException
     */
    protected function buildPromoMergeTags()
    {
        $listId = $this->getListIdFromStore($this->idStore);
        $shop = new \Shop($this->idStore);

        foreach ($this->promoCodes as $promoCodeId) 
        {
            $promoCode = new PromoCode($promoCodeId);            
            $campaign = new Campaign($promoCode->id_promo_main);

            $promoCodeFormatter = new ListMemberPromoMergeTagFormatter($promoCode, $campaign, $this->context);

            $data = $promoCodeFormatter->format();
            $subscriberHash = $this->mailchimp->subscriberHash($promoCode->email);

            if ($this->method === self::SYNC_METHOD_PATCH) {
                if ($this->syncMode == self::SYNC_MODE_BATCH) {
                    $this->batch->patch(
                        "{$this->batchPrefix}_{$promoCodeId}",
                        "/lists/{$listId}/members/{$subscriberHash}",
                        $data
                    );
                } elseif ($this->syncMode === self::SYNC_MODE_REGULAR) {
                    $this->commands[$promoCodeId] = [
                        'route' => "/lists/{$listId}/members/{$subscriberHash}",
                        'data' => $data,
                    ];
                }
            }            
        }
    }

    public function getMergeFieldLimitExceeded($listId = null, $returnFields = null)
    {
        if (!$listId) {
            $listId = $listId = $this->getListIdFromStore($this->idStore);
        }

        $result = $this->mailchimp->get("lists/{$listId}/merge-fields");

        if ($this->mailchimp->success()) {
            if ($returnFields) {
                return $result;
            }

            if (isset($result["total_items"]) && (int)$result["total_items"] < 30) {
                return false;
            } else {
                return true;
            }
        }

        return false;
    }

    public function createMergeFieldAtMailchimp($listId = null)
    {
        $existing_tags = [];
        if (!$listId) {
            $listId = $listId = $this->getListIdFromStore($this->idStore);
        }

        if ($this->campaign->status && (is_null($this->campaign->id_merge_field_mc) || $this->campaign->id_merge_field_mc == 0)  && !$this->campaign->isExpired()) {

            $merge_field_tags = $this->mailchimp->get("lists/{$listId}/merge-fields", ['fields' => 'merge_fields.tag']);

            if($merge_field_tags){
                // Extract the 'tag' values into a flat array
                $existing_tags = array_map(function ($field) {
                    return $field['tag'];
                }, $merge_field_tags['merge_fields']);
            }

            // remove white spaces for tag name
            $new_tag = strtoupper(preg_replace('/\s+/', '', $this->campaign->name));

            // Get the first 10 characters of $new_tag
            $first_10_characters = substr($new_tag, 0, 10);

            $merge_field_data = [
                "name" => $this->campaign->name,
                "type" => "text",
            ];

            if (!in_array($first_10_characters, $existing_tags)) {
                $merge_field_data["tag"] = $new_tag;
            }

            $response = $this->mailchimp->post("lists/{$listId}/merge-fields", $merge_field_data);

            if ($this->mailchimp->success()) {
                // success synchronization - save ID and TAG from response

                $this->campaign->id_merge_field_mc = $response["merge_id"];
                $this->campaign->tag_merge_field_mc = $response["tag"];

                $this->campaign->update();

                return true;                
            } else {
                
                return false;
            }
            
        }

        return false;
    }

    public function deleteMergeFieldFromMailchimp($listId = null)
    {
        if (!$listId) {
            $listId = $listId = $this->getListIdFromStore($this->idStore);
        }

        // promo code Merge field was already sent and MC merge field ID saved
        if (!is_null($this->campaign->id_merge_field_mc) && $this->campaign->id_merge_field_mc != 0) {

            $response = $this->mailchimp->delete("lists/{$listId}/merge-fields/{$this->campaign->id_merge_field_mc}");

            if ($this->mailchimp->success()) {
                // success merge field deletion
                return true;                
            } else {
                // failed deletion
                return false;
            } 
        }

        return true;
    }
    
}
