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
use \PrestaChamps\MailchimpPro\Formatters\StoreFormatter;

/**
 * Class StoreSyncCommand
 *
 * @package PrestaChamps\MailchimpPro\Commands
 */
class StoreSyncCommand extends BaseApiCommand
{
    protected $context;
    protected $stores;
    protected $mailchimp;
    protected $batch;
    protected $batchPrefix = '';
    protected $commands = [];

    /**
     * StoreSyncService constructor.
     *
     * @param \Context  $context
     * @param MailChimpAPI $mailchimp
     * @param array     $storeIds
     */
    public function __construct(\Context $context, MailChimpAPI $mailchimp, $storeIds = [])
    {
        $this->context = $context;
        $this->mailchimp = $mailchimp;
        $this->batchPrefix = uniqid("STORE_SYNC_", true);
        $this->batch = $this->mailchimp->new_batch($this->batchPrefix);
        $this->stores = $storeIds;
    }

    /**
     * @return array|false
     * @throws \PrestaShopDatabaseException
     * @throws \PrestaShopException
     */
    public function execute()
    {
        $this->responses = [];

        $this->buildStores();

        if ($this->syncMode === self::SYNC_MODE_BATCH) {
            $this->responses['batch'] = $this->batch->execute();
        }

        $allRequestsSuccess = true;
        $requestErrors = [];
        if ($this->syncMode === self::SYNC_MODE_REGULAR) {
            $method = \Tools::strtolower($this->method);
            foreach ($this->commands as $entityId => $params) {
                try {
                    //$this->responses[$entityId] = $this->mailchimp->$method($params['route'], $params['data']);
                    $this->mailchimp->$method($params['route'], $params['data']);
                } catch (\Exception $exception) {
                    //$this->responses[$entityId] = $this->mailchimp->getLastResponse();
                    //\PrestaShopLogger::addLog("[MAILCHIMP]: {$exception->getMessage()}");
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
    protected function buildStores()
    {
        foreach ($this->stores as $storeId) {
            $shop = new \Shop($storeId);
            $storeFormatter = new StoreFormatter($shop, $this->context);
            $formattedId = \Mailchimppro::shopIdTransformer($shop);

            if ($this->method === self::SYNC_METHOD_POST) {
                if ($this->syncMode == self::SYNC_MODE_BATCH) {
                    $this->batch->post(
                        "{$this->batchPrefix}_{$formattedId}",
                        "/ecommerce/stores",
                        $storeFormatter->format()
                    );
                } elseif ($this->syncMode === self::SYNC_MODE_REGULAR) {
                    $this->commands[$storeId] = [
                        'route' => "/ecommerce/stores",
                        'data' => $storeFormatter->format(),
                    ];
                }
            } elseif ($this->method === self::SYNC_METHOD_PATCH) {
                $data = $storeFormatter->format();
                // MC does not support changing the list id, so it must be unset
                unset($data['list_id']);

                if ($this->syncMode == self::SYNC_MODE_BATCH) {
                    $this->batch->patch(
                        "{$this->batchPrefix}_{$formattedId}",
                        "/ecommerce/stores/{$formattedId}",
                        $data
                    );
                } elseif ($this->syncMode === self::SYNC_MODE_REGULAR) {
                    $this->commands[$storeId] = [
                        'route' => "/ecommerce/stores/{$formattedId}",
                        'data' => $data,
                    ];
                }
            } elseif ($this->method === self::SYNC_METHOD_DELETE) {
                if ($this->syncMode == self::SYNC_MODE_BATCH) {
                    $this->batch->delete(
                        "{$this->batchPrefix}_{$formattedId}",
                        "/ecommerce/stores/{$formattedId}"
                    );
                } elseif ($this->syncMode === self::SYNC_MODE_REGULAR) {
                    $this->commands[$storeId] = [
                        'route' => "/ecommerce/stores/{$formattedId}",
                        'data' => [],
                    ];
                }
            }
        }
    }

    /**
     * @param string $shopId
     * @param bool   $returnFields
     *
     * @return bool|array
     */
    public function getStoreExists($shopId = null, $returnFields = null)
    {
        if (!$shopId) {
            $shopId = $this->getShopId();
        }
        $result = $this->mailchimp->get(
            "/ecommerce/stores/{$shopId}"
        );

        if ($this->mailchimp->success()) {
            if ($returnFields) {
                return $result;
            }
            return true;
        }

        return false;
    }
}
