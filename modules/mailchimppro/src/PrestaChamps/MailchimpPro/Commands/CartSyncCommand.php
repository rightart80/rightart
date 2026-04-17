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

namespace PrestaChamps\MailchimpPro\Commands;
if (!defined('_PS_VERSION_')) {
    exit;
}
use Cart;
use Context;
use Customer;
use PrestaChamps\MailChimpAPI;
use MailchimpProConfig;
use PrestaChamps\MailchimpPro\Formatters\CartFormatter;
use PrestaChamps\PrestaShop\Traits\ShopIdTrait;

/**
 * Class CartSyncCommand
 *
 * @package PrestaChamps\MailchimpPro\Commands
 */
class CartSyncCommand extends BaseApiCommand
{
    use ShopIdTrait;

    protected $context;
    protected $cartIds;
    protected $mailchimp;
    protected $batch;
    protected $batchPrefix = '';
    protected $commands = [];
    protected $campaignId = null;

    protected $idStore;

    protected $requestErrors;


    public function __construct(Context $context, MailChimpAPI $mailchimp, $cartIds = [], $idStore = null)
    {
        $this->context = $context;
        $this->mailchimp = $mailchimp;
        $this->batchPrefix = uniqid('CART_SYNC_', true);
        $this->batch = $this->mailchimp->new_batch($this->batchPrefix);
        $this->cartIds = $cartIds;
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
     * @return array|false
     * @throws \PrestaShopDatabaseException
     * @throws \PrestaShopException
     */
    public function execute()
    {
        if (\Configuration::get(MailchimpProConfig::SYNC_CARTS) || $this->method === self::SYNC_METHOD_DELETE) {
            $this->responses = [];

            $this->buildCarts();

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
                        /* \Configuration::updateValue(MailchimpProConfig::LAST_SYNCED_CART_ID, $entityId); */
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
                        \Configuration::updateValue(MailchimpProConfig::LAST_SYNCED_CART_ID, $entityId);
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
    protected function buildCarts()
    {
        foreach ($this->cartIds as $key => $cartId) {
            $cart = new Cart($cartId);
            $customer = new Customer($cart->id_customer);
            $cartFormatter = new CartFormatter(
                $cart,
                $customer,
                $this->context,
                $this->campaignId,
                $this->idStore
            );
            // check filter customers to sync
            if (\Configuration::get(MailchimpProConfig::CRONJOB_BASED_SYNC)) {
                if (!in_array($customer->active, MailchimpProConfig::getConfigurationValues()[MailchimpProConfig::CUSTOMER_SYNC_FILTER_ENABLED])) {
                    continue;
                }

                if (!in_array($customer->newsletter, MailchimpProConfig::getConfigurationValues()[MailchimpProConfig::CUSTOMER_SYNC_FILTER_NEWSLETTER])) {
                    continue;
                }
            }
            if (!\Configuration::get(MailchimpProConfig::CRONJOB_BASED_SYNC) || (\Configuration::get(MailchimpProConfig::CRONJOB_BASED_SYNC) && $this->syncCustomer($customer))) {
                if ($this->method === self::SYNC_METHOD_POST) {
                    if ($this->syncMode == self::SYNC_MODE_BATCH) {
                        $this->batch->post(
                            "{$this->batchPrefix}_{$key}",
                            "/ecommerce/stores/{$this->getShopId($this->idStore)}/carts",
                            $cartFormatter->format()
                        );
                    } elseif ($this->syncMode === self::SYNC_MODE_REGULAR) {
                        $this->commands[$cart->id] = [
                            'route' => "/ecommerce/stores/{$this->getShopId($this->idStore)}/carts",
                            'data' => $cartFormatter->format(),
                        ];
                    }
                } elseif ($this->method === self::SYNC_METHOD_PATCH) {
                    if ($this->syncMode == self::SYNC_MODE_BATCH) {
                        $this->batch->patch(
                            "{$this->batchPrefix}_{$key}",
                            "/ecommerce/stores/{$this->getShopId($this->idStore)}/carts/{$cart->id}",
                            $cartFormatter->format()
                        );
                    } elseif ($this->syncMode === self::SYNC_MODE_REGULAR) {
                        $this->commands[$cart->id] = [
                            'route' => "/ecommerce/stores/{$this->getShopId($this->idStore)}/carts/{$cart->id}",
                            'data' => $cartFormatter->format(),
                        ];
                    }
                } elseif ($this->method === self::SYNC_METHOD_DELETE) {
                    if ($this->syncMode == self::SYNC_MODE_BATCH) {
                        $this->batch->delete(
                            "{$this->batchPrefix}_{$key}",
                            "/ecommerce/stores/{$this->getShopId($this->idStore)}/carts/{$cart->id}"
                        );
                    } elseif ($this->syncMode === self::SYNC_MODE_REGULAR) {
                        $this->commands[$cart->id] = [
                            'route' => "/ecommerce/stores/{$this->getShopId($this->idStore)}/carts/{$cart->id}",
                            'data' => [],
                        ];
                    }
                }
            } else {
                $this->responses['entities'][$cartId]['requestSuccess'] = $this->mailchimp->success();
                $this->responses['entities'][$cartId]['requestLastResponse'] = $this->mailchimp->getLastResponse();
                $this->responses['entities'][$cartId]['requestLastError'] = $this->mailchimp->getLastError();
                $this->responses['requestSuccess'] = $this->mailchimp->success(); // false
                $this->requestErrors[$cartId] = $this->mailchimp->getLastError();
                //\PrestaShopLogger::addLog("[MAILCHIMP]: {$this->mailchimp->getLastResponse()}");
            }
        }
    }

    /**
     * @param Customer $customer
     *
     */
    protected function syncCustomer(Customer $customer)
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
                return false;
            }

            //\Configuration::updateValue(MailchimpProConfig::LAST_SYNCED_CUSTOMER_ID, $customer->id);
            return true;
        }

        return true;
    }

    /**
     * @param int    $cartId
     * @param string $shopId
     * @param bool   $returnFields
     *
     * @return bool|array
     */
    public function getCartExists($cartId, $shopId = null, $returnFields = null)
    {
        if (!$shopId) {
            $shopId = $this->getShopId($this->idStore);
        }
        $result = $this->mailchimp->get(
            "/ecommerce/stores/{$shopId}/carts/{$cartId}",
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
