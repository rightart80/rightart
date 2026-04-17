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
 *
 * Class MailchimpProConfig
 */
if (!defined('_PS_VERSION_')) {
    exit;
}
class MailchimpProConfig
{
    const MAILCHIMP_API_KEY = 'module-mailchimpproconfig-mailchimp-api-key';
    const MAILCHIMP_SCRIPT_VERIFIED = 'module-mailchimpproconfig-script-verified';
    const MAILCHIMP_LIST_ID = 'module-mailchimpproconfig-list-id';
    const MAILCHIMP_LIST_NAME = 'module-mailchimpproconfig-list-name';
    const MAILCHIMP_STORE_SYNCED = 'module-mailchimpproconfig-store-synced';
    const SELECTED_PRESET = 'module-mailchimpproconfig-selected-preset';

    const STATUSES_FOR_PAID = 'module-mailchimpproconfig-statuses-for-paid';
    const STATUSES_FOR_PENDING = 'module-mailchimpproconfig-statuses-for-pending';
    const STATUSES_FOR_REFUNDED = 'module-mailchimpproconfig-statuses-for-refunded';
    const STATUSES_FOR_CANCELLED = 'module-mailchimpproconfig-statuses-for-cancelled';
    const STATUSES_FOR_SHIPPED = 'module-mailchimpproconfig-statuses-for-shipped';

    const PRODUCT_IMAGE_SIZE = 'module-mailchimpproconfig-product-image-size';
    const PRODUCT_DESCRIPTION_FIELD = 'module-mailchimpproconfig-product-description-field';
    const PRODUCT_SYNC_FILTER_ACTIVE = 'module-mailchimpproconfig-product-sync-filter-active';
    const PRODUCT_SYNC_FILTER_VISIBILITY = 'module-mailchimpproconfig-product-sync-filter-visibility';

    const CUSTOMER_SYNC_FILTER_ENABLED = 'module-mailchimpproconfig-customer-sync-filter-enabled';
    const CUSTOMER_SYNC_FILTER_NEWSLETTER = 'module-mailchimpproconfig-customer-sync-filter-newsletter';
    const CUSTOMER_SYNC_FILTER_PERIOD = 'module-mailchimpproconfig-customer-sync-filter-period';
    const CUSTOMER_SYNC_TAG_DEFAULT_GROUP = 'module-mailchimpproconfig-customer-sync-tag-default-group';
    const CUSTOMER_SYNC_TAG_GENDER = 'module-mailchimpproconfig-customer-sync-tag-gender';
    const CUSTOMER_SYNC_TAG_LANGUAGE = 'module-mailchimpproconfig-customer-sync-tag-language';
    
    const CART_RULE_SYNC_FILTER_STATUS = 'module-mailchimpproconfig-cart-rule-sync-filter-status';
    const CART_RULE_SYNC_FILTER_EXPIRATION = 'module-mailchimpproconfig-cart-rule-sync-filter-expiration';

    const EXISTING_ORDER_SYNC_STRATEGY = 'module-mailchimpproconfig-existing-order-sync-strategy';

    const NEWSLETTER_SUBSCRIBER_SYNC_FILTER_PERIOD = 'module-mailchimpproconfig-newsletter-subscriber-sync-filter-period';

    const MULTI_INSTANCE_MODE = 'module-mailchimpproconfig-multi-instance-mode';

    const SYNC_PRODUCTS = 'module-mailchimpproconfig-sync-products';
    const SYNC_CUSTOMERS = 'module-mailchimpproconfig-sync-customers';
    const SYNC_CART_RULES = 'module-mailchimpproconfig-sync-cart-rules';
    const SYNC_ORDERS = 'module-mailchimpproconfig-sync-orders';
    const SYNC_CARTS = 'module-mailchimpproconfig-sync-carts';
    const SYNC_CARTS_PASSW = 'module-mailchimpproconfig-sync-carts-passw';
    const SYNC_NEWSLETTER_SUBSCRIBERS = 'module-mailchimpproconfig-sync-newsletter-subscribers';

    const CRONJOB_SECURE_TOKEN = 'module-mailchimpproconfig-cronjob-secure-token';
    const LOG_QUEUE = 'module-mailchimpproconfig-log-queue';
    const QUEUE_STEP = 'module-mailchimpproconfig-queue-step';
    const QUEUE_ATTEMPT = 'module-mailchimpproconfig-queue-attempt';
    const LOG_CRONJOB = 'module-mailchimpproconfig-log-cronjob';
    const LAST_CRONJOB = 'module-mailchimpproconfig-last-cronjob';
    const LAST_CRONJOB_EXECUTION_TIME = 'module-mailchimpproconfig-last-cronjob-execution-time';
    const CRONJOB_BASED_SYNC = 'module-mailchimpproconfig-cronjob-based-sync';
    const CRONJOB_BASED_SYNC_FOR_MULTI_STORE = 'module-mailchimpproconfig-cronjob-based-sync-for-multi-store';

    const LAST_SYNCED_PRODUCT_ID = 'module-mailchimpproconfig-last-synced-product-id';
    const LAST_SYNCED_CUSTOMER_ID = 'module-mailchimpproconfig-last-synced-customer-id';
    const LAST_SYNCED_PROMO_ID = 'module-mailchimpproconfig-last-synced-promo-id';
    const LAST_SYNCED_ORDER_ID = 'module-mailchimpproconfig-last-synced-order-id';
    const LAST_SYNCED_CART_ID = 'module-mailchimpproconfig-last-synced-cart-id';
    const LAST_SYNCED_NEWSLETTER_SUBSCRIBER_ID = 'module-mailchimpproconfig-last-synced-newsletter-subscriber-id';

    const DELETED_JOBS_ON_UPGRADE_COUNT = 'module-mailchimpproconfig-deleted-jobs-on-upgrade-count';
    const DELETED_JOBS_ON_UPGRADE_DATE = 'module-mailchimpproconfig-deleted-jobs-on-upgrade-date';
    const DELETED_JOBS_ON_UPGRADE_ACCEPTED_MESSAGE_DATE = 'module-mailchimpproconfig-deleted-jobs-on-upgrade-accepted-message-date';
    const DELETED_JOBS_ON_UPGRADE_ACCEPTED_MESSAGE_EMPLOYEE = 'module-mailchimpproconfig-deleted-jobs-on-upgrade-accepted-message-employee';

    const MAILCHIMP_SCRIPT_CACHED = 'module-mailchimpproconfig-script-cached';
    const MAILCHIMP_SCRIPT_CACHED_DATE = 'module-mailchimpproconfig-script-cached-date';
    const MAILCHIMP_AUTO_AUDIENCE_SYNC = 'module-mailchimpproconfig-auto-audience-sync';
    const MAILCHIMP_AUTO_AUDIENCE_SYNC_ACCEPTED_MESSAGE_DATE = 'module-mailchimpproconfig-auto-audience-sync-accepted-message-date';
    const MAILCHIMP_AUTO_AUDIENCE_SYNC_ACCEPTED_MESSAGE_EMPLOYEE = 'module-mailchimpproconfig-auto-audience-sync-accepted-message-employee';

    const SHOW_DASHBOARD_STATS = 'module-mailchimpproconfig-show-dashboard-stats';
    const MAILCHIMP_REPORTS_CACHED = 'module-mailchimpproconfig-reports-cached';
    const MAILCHIMP_REPORTS_CACHED_DATE = 'module-mailchimpproconfig-reports-cached-date';
    
    const MAILCHIMP_CART_RULE_ID = 'module-mailchimpproconfig-cart-rule-id';

    const MAILCHIMP_ACCOUNT_ID_LOGGED_OUT = 'module-mailchimpproconfig-mailchimp-account-id-logged-out';

    const MAILCHIMP_PROMO_OVERRIDE_ENABLED = 'module-mailchimpproconfig-mailchimp-promo-override-enabled';
    const MAILCHIMP_PROMO_OVERRIDE_AUTO_INSTALLED = 'module-mailchimpproconfig-mailchimp-promo-override-auto-installed';
    const MAILCHIMP_PROMO_OVERRIDE_CONFLICTS = 'module-mailchimpproconfig-mailchimp-promo-override-conflicts';

    public static $jsonValues = [
        self::STATUSES_FOR_PAID,
        self::STATUSES_FOR_PENDING,
        self::STATUSES_FOR_REFUNDED,
        self::STATUSES_FOR_CANCELLED,
        self::STATUSES_FOR_SHIPPED,
        self::PRODUCT_SYNC_FILTER_ACTIVE,
        self::PRODUCT_SYNC_FILTER_VISIBILITY,
        self::CUSTOMER_SYNC_FILTER_ENABLED,
        self::CUSTOMER_SYNC_FILTER_NEWSLETTER,
        /* self::CUSTOMER_SYNC_TAG_DEFAULT_GROUP, */
        self::CUSTOMER_SYNC_TAG_GENDER,
        self::CUSTOMER_SYNC_TAG_LANGUAGE,
        self::CART_RULE_SYNC_FILTER_STATUS,
        self::CART_RULE_SYNC_FILTER_EXPIRATION,
        self::MAILCHIMP_PROMO_OVERRIDE_CONFLICTS,
    ];

    public static $defaultValues = [
        self::PRODUCT_SYNC_FILTER_ACTIVE => [1],
        self::PRODUCT_SYNC_FILTER_VISIBILITY => ['both', 'catalog', 'search', 'none'],
        self::CUSTOMER_SYNC_FILTER_ENABLED => [1],
        self::CUSTOMER_SYNC_FILTER_NEWSLETTER => [1],
        self::CUSTOMER_SYNC_FILTER_PERIOD => 'all',
        self::CUSTOMER_SYNC_TAG_DEFAULT_GROUP => 'default',
        self::CUSTOMER_SYNC_TAG_GENDER => true,
        self::CUSTOMER_SYNC_TAG_LANGUAGE => false,
        self::CART_RULE_SYNC_FILTER_STATUS => [1],
        self::CART_RULE_SYNC_FILTER_EXPIRATION => [1],
        self::NEWSLETTER_SUBSCRIBER_SYNC_FILTER_PERIOD => 'all',
        self::MULTI_INSTANCE_MODE => false,
        self::CRONJOB_BASED_SYNC => true,
        self::SYNC_PRODUCTS => true,
        self::SYNC_CUSTOMERS => true,
        self::SYNC_CART_RULES => true,
        self::SYNC_ORDERS => true,
        self::SYNC_CARTS => true,
        self::SYNC_CARTS_PASSW => false,
        self::SYNC_NEWSLETTER_SUBSCRIBERS => true,
        self::LOG_QUEUE => 0,
        self::LOG_CRONJOB => 1,
        self::QUEUE_STEP => 50,
        self::QUEUE_ATTEMPT => 2,
        self::PRODUCT_DESCRIPTION_FIELD => 'description',
        self::EXISTING_ORDER_SYNC_STRATEGY => '-1 year',
        self::STATUSES_FOR_PAID => [],
        self::STATUSES_FOR_PENDING => [],
        self::STATUSES_FOR_REFUNDED => [],
        self::STATUSES_FOR_CANCELLED => [],
        self::STATUSES_FOR_SHIPPED => [],
        self::SHOW_DASHBOARD_STATS => true,
        self::MAILCHIMP_PROMO_OVERRIDE_ENABLED => false,
    ];

    public static $keyMap = [
        'selectedPreset' => self::SELECTED_PRESET,
        'multiInstanceMode' => self::MULTI_INSTANCE_MODE,
        'cronjobBasedSync' => self::CRONJOB_BASED_SYNC,
        'syncProducts' => self::SYNC_PRODUCTS,
        'syncCustomers' => self::SYNC_CUSTOMERS,
        'syncCartRules' => self::SYNC_CART_RULES,
        'syncOrders' => self::SYNC_ORDERS,
        'syncCarts' => self::SYNC_CARTS,
        'syncCartsPassw' => self::SYNC_CARTS_PASSW,
        'syncNewsletterSubscribers' => self::SYNC_NEWSLETTER_SUBSCRIBERS,
        'statusForPending' => self::STATUSES_FOR_PENDING,
        'statusForRefunded' => self::STATUSES_FOR_REFUNDED,
        'statusForCancelled' => self::STATUSES_FOR_CANCELLED,
        'statusForShipped' => self::STATUSES_FOR_SHIPPED,
        'statusForPaid' => self::STATUSES_FOR_PAID,
        'productDescriptionField' => self::PRODUCT_DESCRIPTION_FIELD,
        'existingOrderSyncStrategy' => self::EXISTING_ORDER_SYNC_STRATEGY,
        'productSyncFilterActive' => self::PRODUCT_SYNC_FILTER_ACTIVE,
        'productSyncFilterVisibility' => self::PRODUCT_SYNC_FILTER_VISIBILITY,
        'customerSyncFilterEnabled' => self::CUSTOMER_SYNC_FILTER_ENABLED,
        'customerSyncFilterNewsletter' => self::CUSTOMER_SYNC_FILTER_NEWSLETTER,
        'customerSyncFilterPeriod' => self::CUSTOMER_SYNC_FILTER_PERIOD,
        'customerSyncTagDefaultGroup' => self::CUSTOMER_SYNC_TAG_DEFAULT_GROUP,
        'customerSyncTagGender' => self::CUSTOMER_SYNC_TAG_GENDER,
        'customerSyncTagLanguage' => self::CUSTOMER_SYNC_TAG_LANGUAGE,
        'cartRuleSyncFilterStatus' => self::CART_RULE_SYNC_FILTER_STATUS,
        'cartRuleSyncFilterExpiration' => self::CART_RULE_SYNC_FILTER_EXPIRATION,
        'newsletterSubscriberSyncFilterPeriod' => self::NEWSLETTER_SUBSCRIBER_SYNC_FILTER_PERIOD,
        'productImageSize' => self::PRODUCT_IMAGE_SIZE,
        'listId' => self::MAILCHIMP_LIST_ID,
        'storeSynced' => self::MAILCHIMP_STORE_SYNCED,
        'logQueue' => self::LOG_QUEUE,
        'queueStep' => self::QUEUE_STEP,
        'queueAttempt' => self::QUEUE_ATTEMPT,
        'logCronjob' => self::LOG_CRONJOB,
        'showDashboardStats' => self::SHOW_DASHBOARD_STATS,
        'promoOverridesEnabled' => self::MAILCHIMP_PROMO_OVERRIDE_ENABLED,
        'promoOverridesAutoInstalled' => self::MAILCHIMP_PROMO_OVERRIDE_AUTO_INSTALLED,
        'promoOverridesConflicts' => self::MAILCHIMP_PROMO_OVERRIDE_CONFLICTS,
    ];

    /** Required for PHP < 5.6 compatibility */
    public static $className = 'MailchimpProConfig';

    public static $multiLang = [
    ];

    /**
     * Save a config value
     *
     * @param $key
     * @param $value
     *
     * @return bool
     */
    public static function saveValue($key, $value)
    {
        return Configuration::updateValue($key, $value, true);
    }

    /**
     * Get configuration keys and values
     *
     * @return array
     */
    public static function getConfigurationValues()
    {
        try {
            $class = new ReflectionClass(static::$className);
            $values = [];
            foreach ($class->getConstants() as $constant) {
                if (is_string($constant)) {
                    if (in_array($constant, static::$multiLang, false)) {
                        static::getMultilangConfigValues($constant, $values);
                    } else {
                        $value = static::sanitizeDefault($constant, Configuration::get($constant, null, null, null, null));
                        $values[$constant] = $value;
                    }
                }
                if (in_array($constant, static::$jsonValues, false)) {
                    $jsonValue = static::sanitizeDefault($constant, Configuration::get($constant, null, null, null, null));
                    $values[$constant] = json_decode($jsonValue ?? '[]', true);
                }
            }
            return $values;
        } catch (Exception $exception) {
            return [];
        }
    }

    public static function sanitizeDefault($key, $value)
    {
        if ($value === null && isset(static::$defaultValues[$key])) {
            return is_scalar(static::$defaultValues[$key]) ? static::$defaultValues[$key] : json_encode(static::$defaultValues[$key]);
        }

        return $value;
    }

    /**
     * Get a multilang config key (mainly used with the HelperForm class)
     *
     * @param $key
     * @param $values
     */
    protected static function getMultilangConfigValues($key, &$values)
    {
        $languages = Language::getLanguages(false, false, false);
        $values[$key] = [];
        foreach ($languages as $language) {
            $values[$key][$language['id_lang']] = Configuration::get($key, $language['id_lang']);
        }
    }

    /**
     * Decide if a config key exists in the DB or not, doesn't really care about multilang
     *
     * @param null $configKey
     *
     * @return bool
     * @throws PrestaShopDatabaseException
     */
    public static function configExists($configKey = null)
    {
        $query = new \DbQuery();
        $query->select('count(*)');
        $query->from('configuration');
        $query->where("name = '" . pSQL($configKey) . "'");

        return (int)Db::getInstance()->executeS($query) > 0;
    }

    public static function isApiKeySet()
    {
        return false;
    }

    public static function resetConfigForNewAccount()
    {
        self::saveValue(self::MAILCHIMP_AUTO_AUDIENCE_SYNC, 1);
        self::saveValue(self::MAILCHIMP_AUTO_AUDIENCE_SYNC_ACCEPTED_MESSAGE_DATE, false);
        self::saveValue(self::MAILCHIMP_AUTO_AUDIENCE_SYNC_ACCEPTED_MESSAGE_EMPLOYEE, false);

        self::saveValue(self::SELECTED_PRESET, false);
        self::saveValue(self::MAILCHIMP_STORE_SYNCED, false);

        self::saveValue(self::MAILCHIMP_SCRIPT_CACHED, false);
        self::saveValue(self::MAILCHIMP_SCRIPT_CACHED_DATE, false);

        self::saveValue(self::LAST_SYNCED_PRODUCT_ID, false);
        self::saveValue(self::LAST_SYNCED_CUSTOMER_ID, false);
        self::saveValue(self::LAST_SYNCED_PROMO_ID, false);
        self::saveValue(self::LAST_SYNCED_ORDER_ID, false);
        self::saveValue(self::LAST_SYNCED_CART_ID, false);
        self::saveValue(self::LAST_SYNCED_NEWSLETTER_SUBSCRIBER_ID, false);

        self::saveValue(self::MAILCHIMP_REPORTS_CACHED, false);
        self::saveValue(self::MAILCHIMP_REPORTS_CACHED_DATE, false);

        self::saveValue(self::LAST_CRONJOB, false);
        self::saveValue(self::LAST_CRONJOB_EXECUTION_TIME, false);

        self::saveValue(self::MULTI_INSTANCE_MODE, false);
    }
}
