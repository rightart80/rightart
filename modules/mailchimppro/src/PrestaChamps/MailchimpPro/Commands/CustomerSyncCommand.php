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
use Context;
use Customer;
use CustomerCore;
use PrestaChamps\MailChimpAPI;
use MailchimpProConfig;
use PrestaChamps\MailchimpPro\Formatters\ListMemberTagFormatter;
use PrestaShop\PrestaShop\Adapter\Entity\Group;
use PrestaShop\PrestaShop\Adapter\Entity\Validate;
use PrestaShop\PrestaShop\Adapter\Entity\Gender;
use PrestaChamps\MailchimpPro\Formatters\CustomerFormatter;
use PrestaChamps\MailchimpPro\Formatters\ListMemberFormatter;
use PrestaChamps\PrestaShop\Traits\ShopIdTrait;
use PrestaShopDatabaseException;
use Tools;

/**
 * Class CustomerSyncCommand
 *
 * @package PrestaChamps\MailchimpPro\Commands
 */
class CustomerSyncCommand extends BaseApiCommand
{
    use ShopIdTrait;

    protected $context;
    protected $customerIds;
    protected $mailchimp;
    protected $batch;
    protected $batchPrefix = '';
    protected $triggerDoubleOptIn = true;
    protected $commands = [];
    protected $updateSubscriptionStatus = true;

    protected $idStore;

    protected $configuration;

    /**
     * CustomerSyncService constructor.
     *
     * @param Context $context
     * @param MailChimpAPI $mailchimp
     * @param array $customerIds
     */
    public function __construct(Context $context, MailChimpAPI $mailchimp, $customerIds = [], $idStore = null)
    {
        $this->context = $context;
        $this->mailchimp = $mailchimp;
        $this->batchPrefix = uniqid('CUSTOMER_SYNC_', true);
        $this->batch = $this->mailchimp->new_batch($this->batchPrefix);
        $this->customerIds = $customerIds;
        $this->configuration = \MailchimpProConfig::getConfigurationValues();
        $this->idStore = $idStore ? $idStore : $this->context->shop->id;
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
     * Trigger DoubleOptIn feature
     *
     * @param bool $trigger
     */
    public function triggerDoubleOptIn($trigger = true)
    {
        $this->triggerDoubleOptIn = (bool)$trigger;
    }

    /**
     * @return array|false
     * @throws \PrestaShopDatabaseException
     * @throws \PrestaShopException
     */
    public function execute()
    {
        if (\Configuration::get(MailchimpProConfig::SYNC_CUSTOMERS) || \Configuration::get(MailchimpProConfig::SYNC_ORDERS) || $this->method === self::SYNC_METHOD_DELETE) {
            $this->responses = [];

            $this->buildCustomers();

            if ($this->syncMode === self::SYNC_MODE_BATCH) {
                $this->responses['batch'] = $this->batch->execute();
            }

            $allRequestsSuccess = true;
            $requestErrors = [];
            if ($this->syncMode === self::SYNC_MODE_REGULAR) {
                $method = \Tools::strtolower($this->method);
                foreach ($this->commands as $entityId => $params) {
                    foreach ($params as $key => $param) {
                        try {
                            //$this->responses[$entityId] = $this->mailchimp->$method($params['route'], $params['data']);
                            if ($this->method === self::SYNC_METHOD_POST || $this->method === self::SYNC_METHOD_PUT || $this->method === self::SYNC_METHOD_PATCH) {
                                if ($key == 'tags') {
                                    $this->mailchimp->post($param['route'], $param['data']);
                                }
                                else {
                                    $this->mailchimp->put($param['route'], $param['data']);
                                }
                            }
                            else {
                                $this->mailchimp->$method($param['route'], $param['data']);
                            }
                            /* \Configuration::updateValue(MailchimpProConfig::LAST_SYNCED_CUSTOMER_ID, $entityId); */
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
                            \Configuration::updateValue(MailchimpProConfig::LAST_SYNCED_CUSTOMER_ID, $entityId);
                        }

                        $this->responses['entities'][$entityId][$key]['requestSuccess'] = $this->mailchimp->success();
                        $this->responses['entities'][$entityId][$key]['requestLastResponse'] = $this->mailchimp->getLastResponse();
                        $this->responses['entities'][$entityId][$key]['requestLastError'] = $this->mailchimp->getLastError();
                    }
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
    protected function buildCustomers()
    {
        $listId = $this->getListIdFromStore($this->idStore);
        $listRequiresDoi = $this->getListRequiresDOI($listId);

        if ($this->method === self::SYNC_METHOD_POST && !$this->updateSubscriptionStatus) {
            $this->updateSubscriptionStatus = true;
        }

        foreach ($this->customerIds as $customerId) {
            $customer = new Customer($customerId);
            // check for customer Mailchimp subscription status and only update transactional statuses
            $memberStatus = null;
            //$customerOptInStatus = null;

            // check filter customers to sync
            if(!in_array($customer->active, $this->configuration[\MailchimpProConfig::CUSTOMER_SYNC_FILTER_ENABLED])){
                continue;
            }

            if(!in_array($customer->newsletter, $this->configuration[\MailchimpProConfig::CUSTOMER_SYNC_FILTER_NEWSLETTER])){
                continue;
            }

            //if (($this->method === self::SYNC_METHOD_PUT || $this->method === self::SYNC_METHOD_PATCH) && $this->updateSubscriptionStatus) { || guest
            if (($this->method === self::SYNC_METHOD_PUT || $this->method === self::SYNC_METHOD_PATCH || $customer->isGuest()) && $this->updateSubscriptionStatus) {
                if ($memberExists = self::getMemberExists($customer->email, $listId, true)) {
                    if (isset($memberExists['status'])) {
                        if ($memberExists['status'] !== ListMemberFormatter::STATUS_TRANSACTIONAL) {
                            $this->updateSubscriptionStatus = false;
                            $memberStatus = $memberExists['status'];
                            $customerOptInStatus = true;
                        }
                        elseif (!(bool)$customer->newsletter) {
                            $this->updateSubscriptionStatus = false;
                            $customerOptInStatus = false;
                        }
                    }
                }
            }
            $customerFormatter = new CustomerFormatter($customer, $this->context, null, $this->updateSubscriptionStatus, $this->idStore);
            $data = $customerFormatter->format();
            /* if ($this->updateSubscriptionStatus) {
                $data['opt_in_status'] = (bool)$customer->newsletter; // ($customer->optin || $customer->newsletter) ? true : false;
            } */
            if (isset($customerOptInStatus)) {
                $data['opt_in_status'] = (bool)$customerOptInStatus;
            }

            $listMemberFormatter = new ListMemberFormatter(
                $customer,
                $this->context,
                //$memberStatus ? $memberStatus : $this->getMemberNewsletterStatus($customer, $listRequiresDoi),
                $this->getMemberNewsletterStatus($customer, $listRequiresDoi),
                ListMemberFormatter::EMAIL_TYPE_HTML,
                null,
                $this->updateSubscriptionStatus,
                $this->idStore
            );

            $hash = $this->mailchimp->subscriberHash($customer->email);

            if ($this->method === self::SYNC_METHOD_POST || $this->method === self::SYNC_METHOD_PUT || $this->method === self::SYNC_METHOD_PATCH) {
                if ($this->syncMode == self::SYNC_MODE_BATCH) {
                    $this->batch->put(
                        "{$this->batchPrefix}_{$customerId}_customers",
                        "/ecommerce/stores/{$this->getShopId($this->idStore)}/customers/{$customerId}",
                        $data
                    );
                    $this->batch->put(
                        "{$this->batchPrefix}_{$customerId}_members",
                        "/lists/{$listId}/members/{$hash}",
                        $listMemberFormatter->format()
                    );
                } elseif ($this->syncMode === self::SYNC_MODE_REGULAR) {
                    $this->commands[$customerId]['customers'] = [
                        'route' => "/ecommerce/stores/{$this->getShopId($this->idStore)}/customers/{$customerId}",
                        'data' => $data,
                    ];
                    $this->commands[$customerId]['members'] = [
                        'route' => "/lists/{$listId}/members/{$hash}" . "?skip_merge_validation=true",
                        'data' => $listMemberFormatter->format(),
                    ];
                    if (in_array(\Configuration::get(\MailchimpProConfig::CUSTOMER_SYNC_TAG_DEFAULT_GROUP), ["all", "default"]) || 
                        \Configuration::get(\MailchimpProConfig::CUSTOMER_SYNC_TAG_GENDER) || 
                        \Configuration::get(\MailchimpProConfig::CUSTOMER_SYNC_TAG_LANGUAGE)) {
                        $tags = [];
                        if (in_array(\Configuration::get(\MailchimpProConfig::CUSTOMER_SYNC_TAG_DEFAULT_GROUP), ["all", "default"])) {
                            //$customerAssignedGroups = $customer->getGroups();
                            $customerAssignedGroups = array_column($customer->getWsGroups(), 'id');
                            $customerGroups = Group::getGroups($this->context->language->id);
                            foreach ($customerGroups as $customerGroup) {
                                $tag = [];
                                //$customerGroup = new Group(intval($customerGroup));
                                //$customerGroup = new Group(intval($customer->id_default_group));

                                //if (Validate::isLoadedObject($customerGroup)) {
                                if($customerGroup['name'] != null){
                                    $tag['name'] = $customerGroup['name'];
                                    if (\Configuration::get(\MailchimpProConfig::CUSTOMER_SYNC_TAG_DEFAULT_GROUP) === 'all') {
                                        $tag['status'] = in_array($customerGroup['id_group'], $customerAssignedGroups) ? (ListMemberTagFormatter::STATUS_ACTIVE) : (ListMemberTagFormatter::STATUS_INACTIVE);
                                    }
                                    else {
                                        $tag['status'] = $customer->id_default_group === $customerGroup['id_group'] ? (ListMemberTagFormatter::STATUS_ACTIVE) : (ListMemberTagFormatter::STATUS_INACTIVE);
                                    }
                                //}
                                    $tags[] = $tag;
                                }
                            }
                        }
                        if (\Configuration::get(\MailchimpProConfig::CUSTOMER_SYNC_TAG_GENDER)) {
                            if ($customerGender = $this->getCustomerGenderName($customerId)) {
                                $genders = Gender::getGenders($this->context->language->id);
                                foreach ($genders as $gender) {
                                    $tag = [];
                                    $tag['name'] = $gender->name;
                                    $tag['status'] = $customerGender === $gender->name ? (ListMemberTagFormatter::STATUS_ACTIVE) : (ListMemberTagFormatter::STATUS_INACTIVE);
                                    $tags[] = $tag;
                                }
                            }
                        }
                        
                        // Add language tag
                        if (\Configuration::get(\MailchimpProConfig::CUSTOMER_SYNC_TAG_LANGUAGE) && $customer->id_lang) {
                            $language = new \Language($customer->id_lang);
                            if (\Validate::isLoadedObject($language)) {
                                $languageIsoCode = \Mailchimppro::getCustomerLanguageIsoCode($language->iso_code);
                                $tag = [];
                                $tag['name'] = 'Language: ' . $languageIsoCode;
                                $tag['status'] = ListMemberTagFormatter::STATUS_ACTIVE;
                                $tags[] = $tag;
                            }
                        }

                        $listMemberTagFormatter = new ListMemberTagFormatter($tags);

                        $this->commands[$customerId]['tags'] = [
                            'route' => "/lists/{$listId}/members/{$hash}/tags",
                            'data' => $listMemberTagFormatter->format(),
                        ];
                    }
                }
            } elseif ($this->method === self::SYNC_METHOD_DELETE) {
                if ($this->syncMode == self::SYNC_MODE_BATCH) {
                    $this->batch->delete(
                        "{$this->batchPrefix}_{$customerId}",
                        "/ecommerce/stores/{$this->getShopId($this->idStore)}/customers/{$customerId}"
                    );
                } elseif ($this->syncMode === self::SYNC_MODE_REGULAR) {
                    $this->commands[$customerId]['customers'] = [
                        'route' => "/ecommerce/stores/{$this->getShopId($this->idStore)}/customers/{$customerId}",
                        'data' => [],
                    ];
                }
            }
        }
    }

    /**
     * @param Customer $customer
     * @param bool $listRequiresDoi
     * @return string
     */
    public function getMemberNewsletterStatus(Customer $customer, $listRequiresDoi)
    {
        if (!$customer->newsletter) { // if (!$customer->newsletter && !$customer->optin) {
            return ListMemberFormatter::STATUS_TRANSACTIONAL;
        }
        if ($listRequiresDoi && $customer->newsletter) { // if ($listRequiresDoi && ($customer->newsletter || $customer->optin)) {
            return ListMemberFormatter::STATUS_PENDING;
        }
        if (!$listRequiresDoi && $customer->newsletter) { // if (!$listRequiresDoi && ($customer->newsletter || $customer->optin)) {
            return ListMemberFormatter::STATUS_SUBSCRIBED;
        }

        return ListMemberFormatter::STATUS_TRANSACTIONAL;
    }

    /**
     * @param int    $customerId
     * @param string $shopId
     * @param bool   $returnFields
     *
     * @return bool|array
     */
    public function getCustomerExists($customerId, $shopId = null, $returnFields = null)
    {
        if (!$shopId) {
            $shopId = $this->getShopId($this->idStore);
        }
        $result = $this->mailchimp->get(
            "/ecommerce/stores/{$shopId}/customers/{$customerId}",
            ['fields' => ['opt_in_status']]
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
     * @param string  $customerEmail
     * @param string  $listId
     * @param bool    $returnFields
     *
     * @return bool|array
     */
    public function getMemberExists($customerEmail, $listId = null, $returnFields = null)
    {
        if (!$listId) {
            $listId = $listId = $this->getListIdFromStore($this->idStore);
        }

        $hash = $this->mailchimp->subscriberHash($customerEmail);
        
        $result = $this->mailchimp->get(
            "/lists/{$listId}/members/{$hash}",
            ['fields' => ['opt_in_status']]
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
     * @param int $customerId
     *
     * @return string
     */
    public function getCustomerGenderName($customerId) {
        $dbquery = new \DbQuery();
        $dbquery->select('gl.`name` AS `gender`');
        $dbquery->from('customer', 'c');
        $dbquery->leftJoin('gender', 'g', 'g.id_gender = c.id_gender');
        $dbquery->leftJoin('gender_lang', 'gl', 'g.id_gender = gl.id_gender AND gl.id_lang = ' . (int) $this->context->language->id);
        $dbquery->where('c.`id_customer` = ' . (int)$customerId);

        return \Db::getInstance()->getValue($dbquery->build());
    }
}
