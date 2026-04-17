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
use PrestaChamps\MailchimpPro\Formatters\ProductFormatter;
use PrestaChamps\PrestaShop\Traits\ShopIdTrait;

/**
 * Class ProductSyncService
 *
 * @package PrestaChamps\MailchimpPro\Exceptions
 */
class ProductSyncCommand extends BaseApiCommand
{
    use ShopIdTrait;

    protected $context;
    protected $productIds;
    protected $mailchimp;
    protected $batch;
    protected $batchPrefix = '';
    protected $method = 'POST';
    protected $commands = [];

    protected $idStore;

    /**
     * @var \Category[]
     */
    protected $categoryCache = [];

    /**
     * ProductSyncService constructor.
     *
     * @param \Context $context
     * @param MailChimpAPI $mailchimp
     * @param           $productIds
     */
    public function __construct(Context $context, MailChimpAPI $mailchimp, $productIds, $idStore = null)
    {
        $this->context = $context;
        $this->mailchimp = $mailchimp;
        $this->batchPrefix = uniqid("PRODUCT_SYNC_{$this->method}_", true);
        $this->batch = $this->mailchimp->new_batch($this->batchPrefix);
        $this->productIds = $productIds;
        $this->idStore = $idStore ? $idStore : $this->context->shop->id;
    }

    /**
     * @return array|false
     * @throws \PrestaShopDatabaseException
     * @throws \PrestaShopException
     */
    public function execute()
    {
        if (\Configuration::get(MailchimpProConfig::SYNC_PRODUCTS) || $this->method === self::SYNC_METHOD_DELETE) {
            $this->responses = [];

            $this->batchPrefix = uniqid("PRODUCT_SYNC_{$this->method}_", true);

            $this->buildProducts();

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
                        /* \Configuration::updateValue(MailchimpProConfig::LAST_SYNCED_PRODUCT_ID, $entityId); */
                    } catch (\Exception $exception) {
                        //$this->responses[$entityId] = $this->mailchimp->getLastResponse();
                        //\PrestaShopLogger::addLog("[MAILCHIMP]: {$exception->getMessage()}");
                        continue;
                    }

                    if (!$this->mailchimp->success()) {
                        $allRequestsSuccess = false;
                        $requestErrors[$entityId] = $this->mailchimp->getLastError();
                    }
                    else {
                        \Configuration::updateValue(MailchimpProConfig::LAST_SYNCED_PRODUCT_ID, $entityId);
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
        return ['requestSuccess' => true];
    }

    /**
     * @throws \PrestaShopDatabaseException
     * @throws \PrestaShopException
     */
    protected function buildProducts()
    {
        foreach ($this->productIds as $key => $productID) {
            $productFormatter = null;
            $product = new \Product($productID, false, $this->context->language->id, $this->idStore);
            if(\Validate::isLoadedObject($product)){
                $category = $this->getCategory($product->getDefaultCategory());
                $productFormatter = new ProductFormatter(
                    $product,
                    $category,
                    $this->context,
                    $this->idStore
                );
            }else{
                $this->method = self::SYNC_METHOD_DELETE;
            }
            if ($this->method === self::SYNC_METHOD_POST) {
                if ($this->syncMode == self::SYNC_MODE_BATCH) {
                    $this->batch->post(
                        "{$this->batchPrefix}_{$key}",
                        "/ecommerce/stores/{$this->getShopId($this->idStore)}/products",
                        $productFormatter->format()
                    );
                } elseif ($this->syncMode === self::SYNC_MODE_REGULAR) {
                    $this->commands[$product->id] = [
                        'route' => "/ecommerce/stores/{$this->getShopId($this->idStore)}/products",
                        'data' => $productFormatter->format(),
                    ];
                }
            } elseif ($this->method === self::SYNC_METHOD_PATCH) {
                if ($this->syncMode == self::SYNC_MODE_BATCH) {
                    $this->batch->patch(
                        "{$this->batchPrefix}_{$key}",
                        "/ecommerce/stores/{$this->getShopId($this->idStore)}/products/{$product->id}",
                        $productFormatter->format()
                    );
                } elseif ($this->syncMode === self::SYNC_MODE_REGULAR) {
                    $this->commands[$product->id] = [
                        'route' => "/ecommerce/stores/{$this->getShopId($this->idStore)}/products/{$product->id}",
                        'data' => $productFormatter->format(),
                    ];
                }
            } elseif ($this->method === self::SYNC_METHOD_PUT) {
                if ($this->syncMode == self::SYNC_MODE_BATCH) {
                    $this->batch->put(
                        "{$this->batchPrefix}_{$key}",
                        "/ecommerce/stores/{$this->getShopId($this->idStore)}/products/{$product->id}",
                        $productFormatter->format()
                    );
                } elseif ($this->syncMode === self::SYNC_MODE_REGULAR) {
                    $this->commands[$product->id] = [
                        'route' => "/ecommerce/stores/{$this->getShopId($this->idStore)}/products/{$product->id}",
                        'data' => $productFormatter->format(),
                    ];
                }            
            } elseif ($this->method === self::SYNC_METHOD_DELETE) {
                if ($this->syncMode == self::SYNC_MODE_BATCH) {
                    $this->batch->delete(
                        "{$this->batchPrefix}_{$key}",
                        "/ecommerce/stores/{$this->getShopId($this->idStore)}/products/{$productID}"
                    );
                } elseif ($this->syncMode === self::SYNC_MODE_REGULAR) {
                    $this->commands[$product->id] = [
                        'route' => "/ecommerce/stores/{$this->getShopId($this->idStore)}/products/{$productID}",
                        'data' => [],
                    ];
                }
            }
        }
    }

    /**
     * @param int    $productId
     * @param string $shopId
     * @param bool   $returnFields
     *
     * @return bool|array
     */
    public function getProductExists($productId, $shopId = null, $returnFields = null)
    {
        if (!$shopId) {
            $shopId = $this->getShopId($this->idStore);
        }
        $result = $this->mailchimp->get(
            "/ecommerce/stores/{$shopId}/products/{$productId}",
            ['fields' => ['id']]
        );

        if ($this->mailchimp->success()) {
            if ($returnFields) {
                return $result;
            }
            return true;
        }

        return false;
    }

    /**
     * It's a good idea to store categories in a cache to prevent multiple and unnecessary DB calls
     *
     * @param $categoryId
     *
     * @return \Category
     */
    protected function getCategory($categoryId)
    {
        // Because PrestaShop, that's why
        if (!is_scalar($categoryId)) {
            $categoryId = $categoryId['id_category_default'];
        }

        if (isset($this->categoryCache[$categoryId])) {
            return $this->categoryCache[$categoryId];
        }
        $this->categoryCache[$categoryId] = new \Category(
            $categoryId,
            $this->context->language->id,
            $this->context->shop->id
        );

        return $this->categoryCache[$categoryId];
    }

    public function getBatchId()
    {
        return $this->batchPrefix;
    }
}
