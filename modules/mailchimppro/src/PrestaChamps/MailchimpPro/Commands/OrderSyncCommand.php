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
use PrestaChamps\MailchimpPro\Exceptions\MailChimpException;
use PrestaChamps\MailchimpPro\Formatters\OrderFormatter;
use PrestaChamps\PrestaShop\Traits\ShopIdTrait;

/**
 * Class OrderSyncService
 *
 * @package PrestaChamps\MailchimpPro\Services
 */
class OrderSyncCommand extends BaseApiCommand
{
    use ShopIdTrait;

    protected $context;
    protected $orders;
    protected $mailchimp;
    protected $batch;
    protected $batchPrefix = '';
    protected $commands = [];
    protected $requestErrors = [];
    protected $campaignId = null;
    protected $idOrderStates = [];

    protected $idStore;

    protected $configuration;

    /**
     * OrderSyncService constructor.
     *
     * @param \Context $context
     * @param MailChimpAPI $mailchimp
     * @param array $orderIds
     *
     * @throws \PrestaShopDatabaseException
     * @throws \PrestaShopException
     */
    public function __construct(Context $context, MailChimpAPI $mailchimp, $orderIds = [], $idStore = null)
    {
        $this->context = $context;
        $this->mailchimp = $mailchimp;
        $this->batchPrefix = uniqid('ORDERS_SYNC_', true);
        $this->batch = $this->mailchimp->new_batch($this->batchPrefix);
        $this->orders = $orderIds;
        $this->requestErrors = [];
        $this->configuration = \MailchimpProConfig::getConfigurationValues();
        $this->idStore = $idStore ? $idStore : $this->context->shop->id;
    }

    /**
     * Set campaignId feature
     *
     * @param string $campaign_id
     */
    public function setCampaignId($campaign_id = null)
    {
        $this->campaignId = $campaign_id;
    }

    /**
     * Set idOrderStates
     *
     * @param array $id_order_states
     */
    public function setIdOrderStates($id_order_states = [])
    {
        $this->idOrderStates = $id_order_states;
    }

    /**
     * @return array|false
     * @throws \PrestaShopDatabaseException
     * @throws \PrestaShopException
     */
    public function execute()
    {
        if (\Configuration::get(MailchimpProConfig::SYNC_ORDERS) || $this->method === self::SYNC_METHOD_DELETE) {
            $this->responses = [];

            $this->buildOrders();

            if ($this->syncMode === self::SYNC_MODE_BATCH) {
                $this->responses['batch'] = $this->batch->execute();
            }

            $allRequestsSuccess = true;
            /* $requestErrors = []; */
            if ($this->syncMode === self::SYNC_MODE_REGULAR) {
                $method = \Tools::strtolower($this->method);
                foreach ($this->commands as $entityId => $params) {
                    try {
                        //$this->responses[$entityId] = $this->mailchimp->$method($params['route'], $params['data']);
                        $this->mailchimp->$method($params['route'], $params['data']);
                        /* \Configuration::updateValue(MailchimpProConfig::LAST_SYNCED_ORDER_ID, $entityId); */
                    } catch (\Exception $exception) {
                        //$this->responses[$entityId] = $this->mailchimp->getLastResponse();
                        //\PrestaShopLogger::addLog("[MAILCHIMP]: {$exception->getMessage()}");
                        continue;
                    }

                    if (!$this->mailchimp->success()) {
                        $allRequestsSuccess = false;
                        /* $requestErrors[$entityId] = $this->mailchimp->getLastError(); */
                        $this->requestErrors[$entityId] = $this->mailchimp->getLastError();
                    }
                    else {
                        \Configuration::updateValue(MailchimpProConfig::LAST_SYNCED_ORDER_ID, $entityId);
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
                /* $this->responses['requestLastErrors'] = $this->syncMode === self::SYNC_MODE_REGULAR ? $requestErrors : $this->mailchimp->getLastError(); */
                $this->responses['requestLastErrors'] = ($this->syncMode === self::SYNC_MODE_REGULAR || !empty($this->requestErrors)) ? $this->requestErrors : $this->mailchimp->getLastError();
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
     * @todo Implement batch functionality
     */
    protected function buildOrders()
    {
        foreach ($this->orders as $key => $orderId) {
            $order = new \Order($orderId, $this->context->language->id);
            $shippingAddress = new \Address($order->id_address_delivery, $this->context->language->id);
            $billingAddress = new \Address($order->id_address_invoice, $this->context->language->id);

            if(!\Validate::isLoadedObject($order) || !\Validate::isLoadedObject($shippingAddress) || !\Validate::isLoadedObject($billingAddress)){
                continue;
            }

            $orderFormatter = new OrderFormatter($order, $order->getCustomer(), $billingAddress, $shippingAddress, $this->context, $this->campaignId, $this->idStore);

            if ($this->idOrderStates) {
                $orderFormatter->setIdOrderStates($this->idOrderStates);
            }

            // check filter customers to sync
            if(!in_array($order->getCustomer()->active, $this->configuration[\MailchimpProConfig::CUSTOMER_SYNC_FILTER_ENABLED])){
                continue;
            }

            if(!in_array($order->getCustomer()->newsletter, $this->configuration[\MailchimpProConfig::CUSTOMER_SYNC_FILTER_NEWSLETTER])){
                continue;
            }

            if ($this->syncCustomer($order->getCustomer())) {
                if ($this->method === self::SYNC_METHOD_POST) {
                    if ($this->syncMode == self::SYNC_MODE_BATCH) {
                        $this->batch->post(
                            "{$this->batchPrefix}_{$key}",
                            "/ecommerce/stores/{$this->getShopId($this->idStore)}/orders",
                            $orderFormatter->format()
                        );
                    } elseif ($this->syncMode === self::SYNC_MODE_REGULAR) {
                        $this->commands[$orderId] = [
                            'route' => "/ecommerce/stores/{$this->getShopId($this->idStore)}/orders",
                            'data' => $orderFormatter->format(),
                        ];
                    }
                } elseif ($this->method === self::SYNC_METHOD_PATCH) {
                    if ($this->syncMode == self::SYNC_MODE_BATCH) {
                        $this->batch->patch(
                            "{$this->batchPrefix}_{$key}",
                            "/ecommerce/stores/{$this->getShopId($this->idStore)}/orders/{$orderId}",
                            $orderFormatter->format()
                        );
                    } elseif ($this->syncMode === self::SYNC_MODE_REGULAR) {
                        $this->commands[$orderId] = [
                            'route' => "/ecommerce/stores/{$this->getShopId($this->idStore)}/orders/{$orderId}",
                            'data' => $orderFormatter->format(),
                        ];
                    }
                }
            }
            else {
                $this->responses['entities'][$orderId]['requestSuccess'] = $this->mailchimp->success();
                $this->responses['entities'][$orderId]['requestLastResponse'] = $this->mailchimp->getLastResponse();
                $this->responses['entities'][$orderId]['requestLastError'] = $this->mailchimp->getLastError();
                $this->responses['requestSuccess'] = $this->mailchimp->success(); // false
                $this->requestErrors[$orderId] = $this->mailchimp->getLastError();
                //\PrestaShopLogger::addLog("[MAILCHIMP]: {$this->mailchimp->getLastResponse()}");
            }
        }
    }

    /**
     * @param \Customer $customer
     *
     * @throws \PrestaShopDatabaseException
     * @throws MailChimpException
     */
    protected function syncCustomer(\Customer $customer)
    {
        $command = new CustomerSyncCommand(
            $this->context,
            $this->mailchimp,
            [$customer->id],
            $this->idStore
        );
        //if (!$this->getCustomerExists($customer)) {
        if (!$command->getCustomerExists($customer->id)) {
            $command->setMethod($command::SYNC_METHOD_PUT);
            $command->triggerDoubleOptIn(true);
            $command->execute();

            if (!$this->mailchimp->success()) {
                //throw new MailChimpException($this->mailchimp->getLastResponse());
                return false;
            }

            //\Configuration::updateValue(MailchimpProConfig::LAST_SYNCED_CUSTOMER_ID, $customer->id);
            return true;
        }

        return true;
    }

    /**
     * @param int    $orderId
     * @param string $shopId
     * @param bool   $returnFields
     *
     * @return bool|array
     */
    public function getOrderExists($orderId, $shopId = null, $returnFields = null)
    {
        if (!$shopId) {
            $shopId = $this->getShopId($this->idStore);
        }
        $result = $this->mailchimp->get(
            "/ecommerce/stores/{$shopId}/orders/{$orderId}",
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
}
