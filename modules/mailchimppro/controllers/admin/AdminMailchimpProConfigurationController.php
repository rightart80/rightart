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
if (!defined('_PS_VERSION_')) {
    exit;
}
use PrestaChamps\Queue\Jobs\CartRuleSyncJob;
use PrestaChamps\Queue\Jobs\CustomerSyncJob;
use PrestaChamps\Queue\Jobs\ProductSyncJob;
use PrestaChamps\Queue\Jobs\OrderSyncJob;
use PrestaChamps\Queue\Jobs\NewsletterSubscriberSyncJob;
use PrestaChamps\PrestaShop\Traits\ShopIdTrait;
use PrestaChamps\MailchimpPro\Commands\StoreSyncCommand;
use PrestaChamps\MailchimpPro\Commands\SiteVerifyCommand;
use PrestaChamps\MailchimpPro\Models\Campaign;
use PrestaChamps\MailchimpPro\Models\PromoCode;
use PrestaChamps\MailchimpPro\Commands\PromoMergeTagSyncCommand;
use PrestaChamps\Queue\Jobs\MergeTagPromoCodeSyncJob;

class AdminMailchimpProConfigurationController extends ModuleAdminController
{
    use ShopIdTrait;

    static $Account_Info = null;
    
    public $bootstrap = true;

    private function getApiLogs()
    {
        $query = new DbQuery();
        $query->select('*');
        $query->from('mailchimppro_api_log');
        $query->orderBy('id DESC');

        // Execute the query and fetch the results
        return Db::getInstance()->executeS($query);
    }

    public function initContent()
    {
        $logs = $this->getApiLogs();
        $promo = Campaign::getPromoCodes();
        $totalCodes = Campaign::getExpectedCodesCount();

        $multistore_on_store = false;

        if((\Shop::isFeatureActive() && \Shop::getContextShopID(true) != null) || !\Shop::isFeatureActive()){
            $multistore_on_store = true;
        }elseif(\Shop::getContextShopID(true) == null){
            $multistore_on_store = false;
        }

        $multistore_php_command = false;
        if (Configuration::get(MailchimpProConfig::CRONJOB_BASED_SYNC_FOR_MULTI_STORE) == true) {
            $multistore_php_command = true;
        }

        $jobs_deleted_message_show = true;
        $jobs_deleted_message_employee = Configuration::get(MailchimpProConfig::DELETED_JOBS_ON_UPGRADE_ACCEPTED_MESSAGE_EMPLOYEE);
        $jobs_deleted_message_date = Configuration::get(MailchimpProConfig::DELETED_JOBS_ON_UPGRADE_ACCEPTED_MESSAGE_DATE);

        $jobs_deleted_message_count = Configuration::hasKey(MailchimpProConfig::DELETED_JOBS_ON_UPGRADE_COUNT);


        if(($jobs_deleted_message_employee != null && $jobs_deleted_message_date != null) || empty($this->getAccountInfo()) || !$jobs_deleted_message_count){
            $jobs_deleted_message_show = false;
        }

        $syncAutoConfirm = false;
        $syncAutoConfirmListName = "";

        if(!Configuration::hasKey(MailchimpProConfig::MAILCHIMP_AUTO_AUDIENCE_SYNC) || 
            (Configuration::hasKey(MailchimpProConfig::MAILCHIMP_AUTO_AUDIENCE_SYNC) && (boolean)Configuration::get(MailchimpProConfig::MAILCHIMP_AUTO_AUDIENCE_SYNC))) {

            // automatic list_id selection and synchronization 
            if(!empty($this->getAccountInfo()) && (!Configuration::hasKey(MailchimpProConfig::MAILCHIMP_STORE_SYNCED) || Configuration::get(MailchimpProConfig::MAILCHIMP_STORE_SYNCED) == null)){
                try {

                    $lists = $this->module->getApiClient()->get('lists', ['fields' => 'lists.name,lists.id', 'count'=> 1000])['lists'];

                    if(count($lists) == 1){

                        $command = new StoreSyncCommand(
                            $this->context,
                            $this->module->getApiClient(),
                            [$this->context->shop->id]
                        );

                        $storeExists = $command->getStoreExists($this->getShopId(), true);

                        
                        $syncAutoConfirm = true;
                        $syncAutoConfirmListName = $lists[0]['name'];

                        if (isset($storeExists['domain']) && $storeExists['domain'] !== $this->context->shop->getBaseURL(true)) {
                            // dump("same shop ID other domain sync - NO multi instance");
                            MailchimpProConfig::saveValue(MailchimpProConfig::MULTI_INSTANCE_MODE, true);

                            $command = new StoreSyncCommand(
                                $this->context,
                                $this->module->getApiClient(),
                                [$this->context->shop->id]
                            );

                            $storeExists = $command->getStoreExists($this->getShopId(), true);

                            if (isset($storeExists['domain']) && $storeExists['domain'] === $this->context->shop->getBaseURL(true)) {
                                // dump("same domain sync");
                                $syncAutoConfirm = false;
                                MailchimpProConfig::saveValue(MailchimpProConfig::MAILCHIMP_STORE_SYNCED, true);
                            }else{
                                // dump("same shop ID other domain sync - multi instance");
                                MailchimpProConfig::saveValue(MailchimpProConfig::MAILCHIMP_LIST_ID, $lists[0]['id']);
                                MailchimpProConfig::saveValue(MailchimpProConfig::MAILCHIMP_LIST_NAME, $lists[0]['name']);

                                MailchimpProConfig::saveValue(MailchimpProConfig::MAILCHIMP_AUTO_AUDIENCE_SYNC, 1);

                                $resp = $this->autoSyncStore();
                            }

                        }elseif (isset($storeExists['domain']) && $storeExists['domain'] === $this->context->shop->getBaseURL(true)) {
                            // dump("same domain sync");
                            $syncAutoConfirm = false;
                            MailchimpProConfig::saveValue(MailchimpProConfig::MAILCHIMP_STORE_SYNCED, true);

                        }else{
                            // dump("no other domain, can be synched");

                            MailchimpProConfig::saveValue(MailchimpProConfig::MAILCHIMP_LIST_ID, $lists[0]['id']);
                            MailchimpProConfig::saveValue(MailchimpProConfig::MAILCHIMP_LIST_NAME, $lists[0]['name']);

                            MailchimpProConfig::saveValue(MailchimpProConfig::MAILCHIMP_AUTO_AUDIENCE_SYNC, 1);

                            $resp = $this->autoSyncStore();                        
                        }
                    }else{
                        MailchimpProConfig::saveValue(MailchimpProConfig::MAILCHIMP_AUTO_AUDIENCE_SYNC, 0);
                    }

                } catch (Exception $exception) {
                    $this->errors[] = $exception->getMessage();
                }
            }
            // END automatic list_id selection and auto synchronization
        }

        if(Configuration::hasKey(MailchimpProConfig::MAILCHIMP_AUTO_AUDIENCE_SYNC) && (boolean)Configuration::get(MailchimpProConfig::MAILCHIMP_AUTO_AUDIENCE_SYNC)) {
            $syncAutoConfirm = (boolean)Configuration::get(MailchimpProConfig::MAILCHIMP_AUTO_AUDIENCE_SYNC);
            $syncAutoConfirmListName = Configuration::get(MailchimpProConfig::MAILCHIMP_LIST_NAME);
        }

        $this->initMainConfigValues($multistore_on_store);

        $this->initDefaultOrderStatuses();

        if (!empty(Configuration::get(MailchimpProConfig::MAILCHIMP_API_KEY))) {
            $this->module->assignStatisticsVariables();
        }

        $currentDate = new DateTime();
        $currentDate->modify('+1 month');
        $defaultDate = $currentDate->format('Y-m-d H:i:s');

        $this->context->controller->addCSS($this->module->getLocalPath() . 'views/css/configuration.css');
        Media::addJsDef([
            'queueUrl' => $this->context->link->getAdminLink('AdminMailchimpProQueue'),
            'middlewareUrl' => Mailchimppro::MC_MIDDLEWARE,
            'middlewareUrlUpgraded' => Mailchimppro::MC_MIDDLEWARE_NEW_TO_CONNECT,
            'configurationUrl' => $this->context->link->getAdminLink($this->controller_name),
            'statsUrl' => $this->context->link->getAdminLink('AdminMailchimpProStats', true, [], ['ajax' => true]),
            'mailchimp' => $this->getConfigValues(),
            'cronjobSecureToken' => Configuration::get(MailchimpProConfig::CRONJOB_SECURE_TOKEN),
        ]);
        $this->context->smarty->assign([
            'multistore_on_store' => $multistore_on_store,
            'multistore_php_command' => $multistore_php_command,
            'cronjob_multiStore' => \Shop::isFeatureActive(),
            'jobs_deleted_message_show' => $jobs_deleted_message_show,
            'validApiKey' => !empty($this->getAccountInfo()),
            'mainJsPath' =>
                Media::getJSPath(
                    $this->module->getLocalPath() . 'views/js/configuration/main.js'
                ),
            'JsLybraryPath' =>
                Media::getJSPath(
                    $this->module->getLocalPath() . 'views/js/'
                ),
            'configurationUrl' => $this->context->link->getAdminLink($this->controller_name),
            'listId' => Configuration::get(MailchimpProConfig::MAILCHIMP_LIST_ID),
            'imageSizes' => $this->getImageSizes(),
            'cronjobLog' => $this->getCronjobLogContent(),
            'cronjobUrlLink' => $this->context->link->getModuleLink($this->module->name, 'cronjob') . '?secure=' . Configuration::get(MailchimpProConfig::CRONJOB_SECURE_TOKEN),
            'cronjobUrlLinkWget' => '* * * * * wget -O - ' . $this->context->link->getModuleLink($this->module->name, 'cronjob') . '?secure=' . Configuration::get(MailchimpProConfig::CRONJOB_SECURE_TOKEN),
            'cronjobUrlPath' => '* * * * * '.(defined('PHP_BINDIR') && PHP_BINDIR && is_string(PHP_BINDIR) ? PHP_BINDIR.'/' : '').'php ' . _PS_MODULE_DIR_ . $this->module->name . '/cronjob.php secure=' . Configuration::get(MailchimpProConfig::CRONJOB_SECURE_TOKEN),
            'lastCronjob' => Configuration::get(MailchimpProConfig::LAST_CRONJOB),
            'lastCronjobExecutionTime' => Configuration::get(MailchimpProConfig::LAST_CRONJOB_EXECUTION_TIME),
            'totalJobs' => $this->getNumberOfTotalJobs(),
            'autoSyncPopup' => $syncAutoConfirm,
            'autoSyncPopupListName' => $syncAutoConfirmListName,
            'logs' => $logs,
            'promos' => $promo,
            'totalCodes' => $totalCodes,
            'currency' => $this->context->currency,
            'defaultDate' => $defaultDate,
        ]);
        $this->content = $this->context->smarty->fetch(
            $this->module->getLocalPath() . 'views/templates/admin/configuration/main.tpl'
        );
        parent::initContent();
    }

    public function getDefinedPresets() {
        return [
            'advanced' => [
                'title' => $this->trans('Advanced', [], 'Modules.Mailchimppro.Adminmailchimpproconfiguration'),
                'description' => $this->trans('This preset automatically synchronizes data for both abandoned cart recovery and newsletter signups. It includes extensive data synchronization to ensure marketing and cart recovery campaigns have full context.', [], 'Modules.Mailchimppro.Adminmailchimpproconfiguration'),
                'sync-type' => $this->trans('Cronjob based', [], 'Modules.Mailchimppro.Adminmailchimpproconfiguration'),
                'data-sync' => [
                    $this->trans('All active products', [], 'Modules.Mailchimppro.Adminmailchimpproconfiguration'),
                    $this->trans('All active customers (last 6 months)', [], 'Modules.Mailchimppro.Adminmailchimpproconfiguration'),
                    $this->trans('All active cart rules', [], 'Modules.Mailchimppro.Adminmailchimpproconfiguration'),
                    $this->trans('All orders (last 6 months)', [], 'Modules.Mailchimppro.Adminmailchimpproconfiguration'),
                    $this->trans('Carts (from now on)', [], 'Modules.Mailchimppro.Adminmailchimpproconfiguration'),
                    $this->trans('Newsletter signups (lifetime)', [], 'Modules.Mailchimppro.Adminmailchimpproconfiguration'),
                ],
                'config-values' => [
                    'cronjobBasedSync' => true,
                    'syncProducts' => true,
                    'productSyncFilterActive' => [1], // [1] - Only active, [0, 1] - All
                    'productSyncFilterVisibility' => ['both', 'catalog', 'search', 'none'], // ['both', 'catalog', 'search', 'none'] - All, ['both'] - Both, ['catalog'] - Catalog, ['search'] - Search
                    'syncCustomers' => true,
                    'customerSyncFilterEnabled' => [1], // [1] - Only active, [0,1] - All
                    'customerSyncFilterNewsletter' => [0, 1], // [1] - Only opted-in, [0, 1] - All (transactional + opted-in)
                    'customerSyncFilterPeriod' => '-6 months',  // 'onlyNew', '-1 months', '-3 months', '-6 months', '-1 year', 'all'
                    'customerSyncTagLanguage' => false,
                    'syncCartRules' => true,
                    'cartRuleSyncFilterStatus' => [1], // [1] - Only active with available quantity, [0,1] - All
                    'cartRuleSyncFilterExpiration' => [1], // [1] - Only active, [0,1] - All
                    'syncOrders' => true,
                    'existingOrderSyncStrategy' => '-6 months',  // 'onlyNew', '-1 months', '-3 months', '-6 months', '-1 year', 'all'
                    'syncCarts' => true,
                    'syncNewsletterSubscribers' => true,
                    'newsletterSubscriberSyncFilterPeriod' => 'all', // 'onlyNew', '-1 months', '-3 months', '-6 months', '-1 year', 'all'
                ],
                'use-case' => $this->trans('Suitable for businesses wanting to ensure comprehensive data coverage, focusing on both email marketing and recovering abandoned carts.', [], 'Modules.Mailchimppro.Adminmailchimpproconfiguration'),
            ],
            'basic' => [
                'title' => $this->trans('Basic', [], 'Modules.Mailchimppro.Adminmailchimpproconfiguration'),
                'description' => $this->trans('This preset is designed to only manage basic email subscribers. It does not synchronize any cart, order, or cart rule data from your system.', [], 'Modules.Mailchimppro.Adminmailchimpproconfiguration'),
                'sync-type' => $this->trans('Cronjob based', [], 'Modules.Mailchimppro.Adminmailchimpproconfiguration'),
                'data-sync' => [
                    $this->trans('All active products', [], 'Modules.Mailchimppro.Adminmailchimpproconfiguration'),
                    $this->trans('Newsletter signups only (including subscribed customers)', [], 'Modules.Mailchimppro.Adminmailchimpproconfiguration'),
                ],
                'config-values' => [
                    'cronjobBasedSync' => true,
                    'syncProducts' => true,
                    'productSyncFilterActive' => [1], // [1] - Only active, [0, 1] - All
                    'productSyncFilterVisibility' => ['both', 'catalog', 'search', 'none'], // ['both', 'catalog', 'search', 'none'] - All, ['both'] - Both, ['catalog'] - Catalog, ['search'] - Search
                    'syncCustomers' => true,
                    'customerSyncFilterEnabled' => [1], // [1] - Only active, [0,1] - All
                    'customerSyncFilterNewsletter' => [1], // [1] - Only opted-in, [0, 1] - All (transactional + opted-in)
                    'customerSyncFilterPeriod' => 'all', // 'onlyNew', '-1 months', '-3 months', '-6 months', '-1 year', 'all'
                    'customerSyncTagLanguage' => false,
                    'syncCartRules' => false,
                    'syncOrders' => false,
                    'syncCarts' => false,
                    'syncNewsletterSubscribers' => true,
                    'newsletterSubscriberSyncFilterPeriod' => 'all', // 'onlyNew', '-1 months', '-3 months', '-6 months', '-1 year', 'all'
                ],
                'use-case' => $this->trans('Ideal for businesses that want to use Mailchimp solely for managing subscribers and sending newsletters, without integrating complex eCommerce data.', [], 'Modules.Mailchimppro.Adminmailchimpproconfiguration'),
            ],
            'free' => [
                'title' => $this->trans('Free (testing purposes)', [], 'Modules.Mailchimppro.Adminmailchimpproconfiguration'),
                'description' => $this->trans('This preset is designed to synchronize data to test the free Mailchimp plan for email sending. It syncs relevant information like customers, products, and orders. Allows the use of a single list.', [], 'Modules.Mailchimppro.Adminmailchimpproconfiguration'),
                'sync-type' => $this->trans('Cronjob based', [], 'Modules.Mailchimppro.Adminmailchimpproconfiguration'),
                'data-sync' => [
                    $this->trans('All active products', [], 'Modules.Mailchimppro.Adminmailchimpproconfiguration'),
                    $this->trans('Customers (from now on)', [], 'Modules.Mailchimppro.Adminmailchimpproconfiguration'),
                    $this->trans('All active cart rules', [], 'Modules.Mailchimppro.Adminmailchimpproconfiguration'),
                    $this->trans('Orders (from now on)', [], 'Modules.Mailchimppro.Adminmailchimpproconfiguration'),
                    $this->trans('Carts (from now on)', [], 'Modules.Mailchimppro.Adminmailchimpproconfiguration'),
                    $this->trans('Newsletter signups (from now on)', [], 'Modules.Mailchimppro.Adminmailchimpproconfiguration'),
                ],
                'config-values' => [
                    'cronjobBasedSync' => true,
                    'syncProducts' => true,
                    'productSyncFilterActive' => [1], // [1] - Only active, [0, 1] - All
                    'productSyncFilterVisibility' => ['both', 'catalog', 'search', 'none'], // ['both', 'catalog', 'search', 'none'] - All, ['both'] - Both, ['catalog'] - Catalog, ['search'] - Search
                    'syncCustomers' => true,
                    'customerSyncFilterEnabled' => [1], // [1] - Only active, [0,1] - All
                    'customerSyncFilterNewsletter' => [0, 1], // [1] - Only opted-in, [0, 1] - All (transactional + opted-in)
                    'customerSyncFilterPeriod' => 'onlyNew', // 'onlyNew', '-1 months', '-3 months', '-6 months', '-1 year', 'all'
                    'customerSyncTagLanguage' => false,
                    'syncCartRules' => true,
                    'cartRuleSyncFilterStatus' => [1], // [1] - Only active with available quantity, [0,1] - All
                    'cartRuleSyncFilterExpiration' => [1], // [1] - Only active, [0,1] - All
                    'syncOrders' => true,
                    'existingOrderSyncStrategy' => 'onlyNew', // 'onlyNew', '-1 months', '-3 months', '-6 months', '-1 year', 'all'
                    'syncCarts' => true,
                    'syncNewsletterSubscribers' => true,
                    'newsletterSubscriberSyncFilterPeriod' => 'onlyNew', // 'onlyNew', '-1 months', '-3 months', '-6 months', '-1 year', 'all'
                ],
                'use-case' => $this->trans('This is for new marketers with big ideas who want to get online, build an audience, and start growing on day one.', [], 'Modules.Mailchimppro.Adminmailchimpproconfiguration'),
                'list-member-limit' => 500,
            ],
            'abandoned-cart' => [
                'title' => $this->trans('Abandoned Cart', [], 'Modules.Mailchimppro.Adminmailchimpproconfiguration'),
                'description' => $this->trans('This preset is designed to synchronize data for abandoned cart emails. It syncs relevant information like customers, products, and orders.', [], 'Modules.Mailchimppro.Adminmailchimpproconfiguration'),
                'sync-type' => $this->trans('Cronjob based', [], 'Modules.Mailchimppro.Adminmailchimpproconfiguration'),
                'data-sync' => [
                    $this->trans('All active products', [], 'Modules.Mailchimppro.Adminmailchimpproconfiguration'),
                    $this->trans('Customers (from now on)', [], 'Modules.Mailchimppro.Adminmailchimpproconfiguration'),
                    $this->trans('All active cart rules', [], 'Modules.Mailchimppro.Adminmailchimpproconfiguration'),
                    $this->trans('Orders (from now on)', [], 'Modules.Mailchimppro.Adminmailchimpproconfiguration'),
                    $this->trans('Carts (from now on)', [], 'Modules.Mailchimppro.Adminmailchimpproconfiguration'),
                ],
                'config-values' => [
                    'cronjobBasedSync' => true,
                    'syncProducts' => true,
                    'productSyncFilterActive' => [1], // [1] - Only active, [0, 1] - All
                    'productSyncFilterVisibility' => ['both', 'catalog', 'search', 'none'], // ['both', 'catalog', 'search', 'none'] - All, ['both'] - Both, ['catalog'] - Catalog, ['search'] - Search
                    'syncCustomers' => true,
                    'customerSyncFilterEnabled' => [1], // [1] - Only active, [0,1] - All
                    'customerSyncFilterNewsletter' => [0, 1], // [1] - Only opted-in, [0, 1] - All (transactional + opted-in)
                    'customerSyncFilterPeriod' => 'onlyNew', // 'onlyNew', '-1 months', '-3 months', '-6 months', '-1 year', 'all'
                    'customerSyncTagLanguage' => false,
                    'syncCartRules' => true,
                    'cartRuleSyncFilterStatus' => [1], // [1] - Only active with available quantity, [0,1] - All
                    'cartRuleSyncFilterExpiration' => [1], // [1] - Only active, [0,1] - All
                    'syncOrders' => true,
                    'existingOrderSyncStrategy' => 'onlyNew', // 'onlyNew', '-1 months', '-3 months', '-6 months', '-1 year', 'all'
                    'syncCarts' => true,
                    'syncNewsletterSubscribers' => false,
                ],
                'use-case' => $this->trans('Suitable for stores focused on recovering abandoned carts and optimizing revenue through reminder emails within a 90-day window.', [], 'Modules.Mailchimppro.Adminmailchimpproconfiguration'),
            ],
            'custom' => [
                'title' => $this->trans('Custom', [], 'Modules.Mailchimppro.Adminmailchimpproconfiguration'),
                'description' => sprintf($this->trans('This preset offers all features available in the %s"Advanced Package"%s with some additional options, but it is manually operated from the back office and is triggered by specific events or hooks.', [], 'Modules.Mailchimppro.Adminmailchimpproconfiguration'), 
                                    '<b>',
                                    '</b>'
                                ),
                'sync-type' => $this->trans('Cronjob based', [], 'Modules.Mailchimppro.Adminmailchimpproconfiguration'),
                'data-sync' => [
                    $this->trans('All active products', [], 'Modules.Mailchimppro.Adminmailchimpproconfiguration'),
                    $this->trans('All active customers (last 12 months)', [], 'Modules.Mailchimppro.Adminmailchimpproconfiguration'),
                    $this->trans('All active cart rules', [], 'Modules.Mailchimppro.Adminmailchimpproconfiguration'),
                    $this->trans('All orders (last 12 months)', [], 'Modules.Mailchimppro.Adminmailchimpproconfiguration'),
                    $this->trans('Carts (from now on)', [], 'Modules.Mailchimppro.Adminmailchimpproconfiguration'),
                    $this->trans('Newsletter signups (lifetime)', [], 'Modules.Mailchimppro.Adminmailchimpproconfiguration'),
                ],
                'config-values' => [
                    'cronjobBasedSync' => true,
                    'syncProducts' => true,
                    'productSyncFilterActive' => [1], // [1] - Only active, [0, 1] - All
                    'productSyncFilterVisibility' => ['both', 'catalog', 'search', 'none'], // ['both', 'catalog', 'search', 'none'] - All, ['both'] - Both, ['catalog'] - Catalog, ['search'] - Search
                    'syncCustomers' => true,
                    'customerSyncFilterEnabled' => [1], // [1] - Only active, [0,1] - All
                    'customerSyncFilterNewsletter' => [0, 1], // [1] - Only opted-in, [0, 1] - All (transactional + opted-in)
                    'customerSyncFilterPeriod' => '-1 year', // 'onlyNew', '-1 months', '-3 months', '-6 months', '-1 year', 'all'
                    'customerSyncTagLanguage' => false,
                    'syncCartRules' => true,
                    'cartRuleSyncFilterStatus' => [1], // [1] - Only active with available quantity, [0,1] - All
                    'cartRuleSyncFilterExpiration' => [1], // [1] - Only active, [0,1] - All
                    'syncOrders' => true,
                    'existingOrderSyncStrategy' => '-1 year', // 'onlyNew', '-1 months', '-3 months', '-6 months', '-1 year', 'all'
                    'syncCarts' => true,
                    'syncNewsletterSubscribers' => true,
                    'newsletterSubscriberSyncFilterPeriod' => 'all', // 'onlyNew', '-1 months', '-3 months', '-6 months', '-1 year', 'all'
                ],
                'use-case' => $this->trans('Ideal for users who prefer manual control over data syncs, triggering updates via specific hooks or direct operations.', [], 'Modules.Mailchimppro.Adminmailchimpproconfiguration'),
            ],
            'full' => [
                'title' => $this->trans('Full', [], 'Modules.Mailchimppro.Adminmailchimpproconfiguration'),
                'description' => $this->trans('This preset synchronizes all available data. It includes everything from customer interactions to orders, products, carts, and newsletters without any time restriction.', [], 'Modules.Mailchimppro.Adminmailchimpproconfiguration'),
                'sync-type' => $this->trans('Cronjob based', [], 'Modules.Mailchimppro.Adminmailchimpproconfiguration'),
                'data-sync' => [
                    $this->trans('All active products', [], 'Modules.Mailchimppro.Adminmailchimpproconfiguration'),
                    $this->trans('All customers (no time restriction)', [], 'Modules.Mailchimppro.Adminmailchimpproconfiguration'),
                    $this->trans('All active cart rules', [], 'Modules.Mailchimppro.Adminmailchimpproconfiguration'),
                    $this->trans('All orders (no time restriction)', [], 'Modules.Mailchimppro.Adminmailchimpproconfiguration'),
                    $this->trans('All carts', [], 'Modules.Mailchimppro.Adminmailchimpproconfiguration'),
                    $this->trans('Newsletter signups (lifetime)', [], 'Modules.Mailchimppro.Adminmailchimpproconfiguration'),
                ],
                'config-values' => [
                    'cronjobBasedSync' => true,
                    'syncProducts' => true,
                    'productSyncFilterActive' => [1], // [1] - Only active, [0, 1] - All
                    'productSyncFilterVisibility' => ['both', 'catalog', 'search', 'none'], // ['both', 'catalog', 'search', 'none'] - All, ['both'] - Both, ['catalog'] - Catalog, ['search'] - Search
                    'syncCustomers' => true,
                    'customerSyncFilterEnabled' => [1], // [1] - Only active, [0,1] - All
                    'customerSyncFilterNewsletter' => [0, 1], // [1] - Only opted-in, [0, 1] - All (transactional + opted-in)
                    'customerSyncFilterPeriod' => 'all', // 'onlyNew', '-1 months', '-3 months', '-6 months', '-1 year', 'all'
                    'customerSyncTagLanguage' => true,
                    'syncCartRules' => true,
                    'cartRuleSyncFilterStatus' => [1], // [1] - Only active with available quantity, [0,1] - All
                    'cartRuleSyncFilterExpiration' => [1], // [1] - Only active, [0,1] - All
                    'syncOrders' => true,
                    'existingOrderSyncStrategy' => 'all', // 'onlyNew', '-1 months', '-3 months', '-6 months', '-1 year', 'all'
                    'syncCarts' => true,
                    'syncNewsletterSubscribers' => true,
                    'newsletterSubscriberSyncFilterPeriod' => 'all', // 'onlyNew', '-1 months', '-3 months', '-6 months', '-1 year', 'all'
                ],
                'use-case' => $this->trans('Ideal for businesses seeking full synchronization with Mailchimp, ensuring complete data availability for marketing and sales campaigns with no omissions.', [], 'Modules.Mailchimppro.Adminmailchimpproconfiguration'),
            ],
        ];
    }

    protected function initMainConfigValues($multistore_on_store)
    {
        if (!Configuration::get(MailchimpProConfig::CRONJOB_SECURE_TOKEN)) {
            MailchimpProConfig::saveValue(MailchimpProConfig::CRONJOB_SECURE_TOKEN, bin2hex(openssl_random_pseudo_bytes(32)));
        }

        if($multistore_on_store){
            if (!Configuration::get(MailchimpProConfig::MAILCHIMP_API_KEY)) {
                $this->informations[] = $this->trans('Please log in to Mailchimp...', [], 'Modules.Mailchimppro.Adminmailchimpproconfiguration');
            } elseif (!Configuration::get(MailchimpProConfig::MAILCHIMP_LIST_ID) || !Configuration::get(MailchimpProConfig::MAILCHIMP_STORE_SYNCED)) {
                try {
                    $command = new StoreSyncCommand(
                        $this->context,
                        $this->module->getApiClient(),
                        [$this->context->shop->id]
                    );
                    if ($storeExists = $command->getStoreExists($this->getShopId(), true)) {
                        if (isset($storeExists['list_id']) && $storeExists['list_id']) {
                            // MailchimpProConfig::saveValue(MailchimpProConfig::MAILCHIMP_LIST_ID, $storeExists['list_id']);
                            if (isset($storeExists['domain']) && $storeExists['domain'] === $this->context->shop->getBaseURL(true)) {
                                MailchimpProConfig::saveValue(MailchimpProConfig::MAILCHIMP_LIST_ID, $storeExists['list_id']);
                                MailchimpProConfig::saveValue(MailchimpProConfig::MAILCHIMP_STORE_SYNCED, true);
                                Media::addJsDef([
                                    'storeAlreadySynced' => true,
                                ]);
                            }
                            elseif (isset($storeExists['domain']) && $storeExists['domain'] !== $this->context->shop->getBaseURL(true)) {
                                MailchimpProConfig::saveValue(MailchimpProConfig::MULTI_INSTANCE_MODE, true);
                                $storeExists = $command->getStoreExists($this->getShopId(), true);
                                if (isset($storeExists['domain']) && $storeExists['domain'] === $this->context->shop->getBaseURL(true)) {
                                    MailchimpProConfig::saveValue(MailchimpProConfig::MAILCHIMP_LIST_ID, $storeExists['list_id']);
                                    MailchimpProConfig::saveValue(MailchimpProConfig::MAILCHIMP_STORE_SYNCED, true);
                                    Media::addJsDef([
                                        'storeAlreadySynced' => true,
                                    ]);
                                }                                
                            }
                        }
                    }
                } catch (Exception $exception) {
                    $this->errors[] = $exception->getMessage();
                }
            }
        }
    }

    protected function initDefaultOrderStatuses()
    {
        if (!Configuration::get(MailchimpProConfig::STATUSES_FOR_PENDING) || Configuration::get(MailchimpProConfig::STATUSES_FOR_PENDING) == '[]' ||
            !Configuration::get(MailchimpProConfig::STATUSES_FOR_REFUNDED) || Configuration::get(MailchimpProConfig::STATUSES_FOR_REFUNDED) == '[]' ||
            !Configuration::get(MailchimpProConfig::STATUSES_FOR_CANCELLED) || Configuration::get(MailchimpProConfig::STATUSES_FOR_CANCELLED) == '[]' ||
            !Configuration::get(MailchimpProConfig::STATUSES_FOR_SHIPPED) || Configuration::get(MailchimpProConfig::STATUSES_FOR_SHIPPED) == '[]' ||
            !Configuration::get(MailchimpProConfig::STATUSES_FOR_PAID) || Configuration::get(MailchimpProConfig::STATUSES_FOR_PAID) == '[]') {

            $orderStatuses = [];
            $orderStates = OrderState::getOrderStates($this->context->language->id);
            foreach ($orderStates as $orderState) {
                switch (true) {
                    case ($orderState['template'] == "bankwire" || $orderState['template'] == "cashondelivery" || $orderState['template'] == "cheque"):
                        $orderStatuses['pending'][] = $orderState['id_order_state'];
                        break;
                    case $orderState['template'] == "refund":
                        $orderStatuses['refunded'][] = $orderState['id_order_state'];
                        break;
                    case $orderState['template'] == "order_canceled":
                        $orderStatuses['cancelled'][] = $orderState['id_order_state'];
                        break;
                    case $orderState['template'] == "shipped":
                        $orderStatuses['shipped'][] = $orderState['id_order_state'];
                        break;
                    case $orderState['template'] == "payment":
                        $orderStatuses['paid'][] = $orderState['id_order_state'];
                        break;
                }
            }

            if (!Configuration::get(MailchimpProConfig::STATUSES_FOR_PENDING) || Configuration::get(MailchimpProConfig::STATUSES_FOR_PENDING) == '[]') {
                if (isset($orderStatuses['pending'])) {
                    MailchimpProConfig::saveValue(MailchimpProConfig::STATUSES_FOR_PENDING, json_encode($orderStatuses['pending']));
                }
            }
            if (!Configuration::get(MailchimpProConfig::STATUSES_FOR_REFUNDED) || Configuration::get(MailchimpProConfig::STATUSES_FOR_REFUNDED) == '[]') {
                if (isset($orderStatuses['refunded'])) {
                    MailchimpProConfig::saveValue(MailchimpProConfig::STATUSES_FOR_REFUNDED, json_encode($orderStatuses['refunded']));
                }
            }
            if (!Configuration::get(MailchimpProConfig::STATUSES_FOR_CANCELLED) || Configuration::get(MailchimpProConfig::STATUSES_FOR_CANCELLED) == '[]') {
                if (isset($orderStatuses['cancelled'])) {
                    MailchimpProConfig::saveValue(MailchimpProConfig::STATUSES_FOR_CANCELLED, json_encode($orderStatuses['cancelled']));
                }
            }
            if (!Configuration::get(MailchimpProConfig::STATUSES_FOR_SHIPPED) || Configuration::get(MailchimpProConfig::STATUSES_FOR_SHIPPED) == '[]') {
                if (isset($orderStatuses['shipped'])) {
                    MailchimpProConfig::saveValue(MailchimpProConfig::STATUSES_FOR_SHIPPED, json_encode($orderStatuses['shipped']));
                }
            }
            if (!Configuration::get(MailchimpProConfig::STATUSES_FOR_PAID) || Configuration::get(MailchimpProConfig::STATUSES_FOR_PAID) == '[]') {
                if (isset($orderStatuses['paid'])) {
                    MailchimpProConfig::saveValue(MailchimpProConfig::STATUSES_FOR_PAID, json_encode($orderStatuses['paid']));
                }
            }
        }
    }

    protected function getOrderStates()
    {
        $orderStates = [];
        foreach (OrderState::getOrderStates($this->context->language->id) as $orderState) {
            $orderStates[] = [
                'label' => $orderState['name'],
                'value' => $orderState['id_order_state'],
                'color' => $orderState['color'],
            ];
        }
        return $orderStates;
    }

    /**
     * Get the available image sizes
     *
     * @return array
     */
    private function getImageSizes()
    {
        $query = new DbQuery();
        $query->select('name, width, height');
        $query->from('image_type');
        $query->where('products = 1');

        try {
            $results = Db::getInstance()->executeS($query);
        } catch (Exception $exception) {
            $this->errors[] = $exception->getMessage();
            return [];
        }

        // init default product image size
        if (!Configuration::get(MailchimpProConfig::PRODUCT_IMAGE_SIZE) || Configuration::get(MailchimpProConfig::PRODUCT_IMAGE_SIZE) == 'null') {
            $resultNames = array_column($results, 'name');

            $large_name = '';
            if ((bool)version_compare(_PS_VERSION_, '1.7', '>=')) {
                $large_name = ImageType::getFormattedName('large'); // from PS 1.7
            }

            $key = array_search($large_name, $resultNames);
            if ($key !== false) {
                MailchimpProConfig::saveValue(MailchimpProConfig::PRODUCT_IMAGE_SIZE, $large_name);
            }
            else {
                if (!empty($resultNames)) {
                    MailchimpProConfig::saveValue(MailchimpProConfig::PRODUCT_IMAGE_SIZE, $resultNames[0]);
                }
            }
        }

        return $results;
    }

    /**
     * @return array
     */
    private function getAccountInfo()
    {
        try {
            if (!Configuration::get(MailchimpProConfig::MAILCHIMP_API_KEY)) {
                return [];
            }

            if(static::$Account_Info == null){
                $info = $this->module->getApiClient()->get('');
                if (!$this->module->getApiClient()->success()) {
                    return [];
                }
                static::$Account_Info = $info;

                return static::$Account_Info;
            }else{
                return static::$Account_Info;
            }
            
        } catch (\Exception $exception) {
            return [];
        }
    }

    /**
     * @param null $value
     * @param null $controller
     * @param null $method
     * @param int $statusCode
     */
    public function ajaxDie($value = null, $controller = null, $method = null, $statusCode = 200)
    {
        header('Content-Type: application/json');
        if (!is_scalar($value)) {
            $value = json_encode($value);
        }

        http_response_code($statusCode);
        if ((bool)version_compare(_PS_VERSION_, '1.7.5.0', '>=')) {
            parent::ajaxRender($value, $controller, $method); // from PS 1.7.5.0
            die();
        }else{
            parent::ajaxDie($value, $controller, $method);
        }

    }

    public function ajaxProcessDisconnect()
    {
        $accountInfo = $this->getAccountInfo();
        MailchimpProConfig::saveValue(MailchimpProConfig::MAILCHIMP_ACCOUNT_ID_LOGGED_OUT, $accountInfo['account_id']);

        MailchimpProConfig::saveValue(MailchimpProConfig::MAILCHIMP_API_KEY, false);
        MailchimpProConfig::saveValue(MailchimpProConfig::MAILCHIMP_LIST_ID, false);
        MailchimpProConfig::saveValue(MailchimpProConfig::MAILCHIMP_LIST_NAME, false);
        MailchimpProConfig::saveValue(MailchimpProConfig::MAILCHIMP_STORE_SYNCED, false);

        $accountInfo = $this->getAccountInfo();

        $this->ajaxDie([
            'accountInfo' => $accountInfo,
            'validApiKey' => !empty($accountInfo)
        ]);
    }

    public function ajaxProcessConnect()
    {
        MailchimpProConfig::saveValue(MailchimpProConfig::MAILCHIMP_API_KEY, $this->getJsonPayloadValue('token'));
        $accountInfo = $this->getAccountInfo();

        if(Configuration::hasKey(MailchimpProConfig::MAILCHIMP_ACCOUNT_ID_LOGGED_OUT) && Configuration::get(MailchimpProConfig::MAILCHIMP_ACCOUNT_ID_LOGGED_OUT) != ""){
            if(Configuration::get(MailchimpProConfig::MAILCHIMP_ACCOUNT_ID_LOGGED_OUT) !== $accountInfo['account_id']){
                // reset the config variables to start again the setup process
                MailchimpProConfig::resetConfigForNewAccount();                
            }
        }

        $this->ajaxDie([
            'accountInfo' => $accountInfo,
            'validApiKey' => !empty($accountInfo)
        ]);
    }

    protected function ajaxProcessExecuteCronjob()
    {
        $queue = new PrestaChamps\Queue\Queue();
        $queue->runCronjob();
    }

    protected function ajaxProcessClearCronjobLog()
    {
        $hasError = false;
        $errorResponse = '';

        // Define the expected log file path
        $logFilePath = _PS_MODULE_DIR_ . $this->module->name . '/logs/cronjob.log';

        // Resolve the real path to avoid path traversal issues
        $resolvedFilePath = realpath($logFilePath);

        // Ensure the resolved file path is valid and within the expected directory
        if ($resolvedFilePath && strpos($resolvedFilePath, _PS_MODULE_DIR_ . $this->module->name . '/logs/') === 0) {
            if (file_exists($resolvedFilePath)) {
                if (!unlink($resolvedFilePath)) {
                    $errorResponse = $this->trans('Cannot clear cronjob log. Please check the file permissions.', [], 'Modules.Mailchimppro.Adminmailchimpproconfiguration');
                    $hasError = true;
                }
            } else {
                $errorResponse = $this->trans('Cronjob log is already cleaned.', [], 'Modules.Mailchimppro.Adminmailchimpproconfiguration');
                $hasError = true;
            }
        } else {
            $errorResponse = $this->trans('Invalid file path.', [], 'Modules.Mailchimppro.Adminmailchimpproconfiguration');
            $hasError = true;
        }

        $this->ajaxDie([
            'hasError' => $hasError,
            'errorMessage' => $hasError ? $errorResponse : null,
            'successMessage' => $hasError ? null : $this->trans('Cleared cronjob log successfully.', [], 'Modules.Mailchimppro.Adminmailchimpproconfiguration'),
        ]);
    }

    public function getNumberOfTotalJobs()
    {
        $queue = new PrestaChamps\Queue\Queue();

        return $queue->getNumberOfTotalJobs();
    }

    public function getJsonPayloadValue($key, $defaultValue = null)
    {
        $body = json_decode(Tools::file_get_contents('php://input'), true);

        return isset($body[$key]) ? $body[$key] : $defaultValue;
    }

    public function getConfigValues()
    {
        $repository = new \PrestaChamps\MailchimpPro\EntitySyncRepository();
        $accountInfo = $this->getAccountInfo();
        $configValues = MailchimpProConfig::getConfigurationValues();
        $lists = [];
        try {
            if (!Configuration::get(MailchimpProConfig::MAILCHIMP_API_KEY)) {
                $lists = [];
            }
            else {

                if(!empty(Configuration::get(MailchimpProConfig::MAILCHIMP_STORE_SYNCED)) && !empty(Configuration::get(MailchimpProConfig::MAILCHIMP_LIST_ID)) && !empty(Configuration::get(MailchimpProConfig::MAILCHIMP_LIST_NAME))){

                    $lists[] = [
                        'label' => Configuration::get(MailchimpProConfig::MAILCHIMP_LIST_NAME),
                        'value' => Configuration::get(MailchimpProConfig::MAILCHIMP_LIST_ID)
                    ];                    
                    
                }else{
                    $lists = $this->module->getApiClient()->get('lists', ['fields' => 'lists.name,lists.id', 'count'=> 1000])['lists'];

                    if($lists){
                        $lists = array_map(function ($list) {
                            return [
                                'label' => $list['name'],
                                'value' => $list['id']
                            ];
                        }, $lists);
                    }
                    else{
                        $response = json_decode(($this->module->getApiClient()->getLastResponse())['body'], true);
                        $this->errors[] = $response['title'] . ': ' . $response['detail'];
                    }
                }
            }

        } catch (Exception $exception) {
            $this->errors[] = $exception->getMessage();
            $lists = [];
        }

        return [
            'definedPresets' => $this->getDefinedPresets(),
            'selectedPreset' => $configValues[MailchimpProConfig::SELECTED_PRESET], //null, //advanced, basic, abandoned-cart, custom, full
            'multiInstanceMode' => (bool)$configValues[MailchimpProConfig::MULTI_INSTANCE_MODE],
            'cronjobBasedSync' => (bool)$configValues[MailchimpProConfig::CRONJOB_BASED_SYNC],
            'syncProducts' => (bool)$configValues[MailchimpProConfig::SYNC_PRODUCTS],
            'syncCustomers' => (bool)$configValues[MailchimpProConfig::SYNC_CUSTOMERS],
            'syncCartRules' => (bool)$configValues[MailchimpProConfig::SYNC_CART_RULES],
            'syncOrders' => (bool)$configValues[MailchimpProConfig::SYNC_ORDERS],
            'syncCarts' => (bool)$configValues[MailchimpProConfig::SYNC_CARTS],
            'syncCartsPassw' => (bool)$configValues[MailchimpProConfig::SYNC_CARTS_PASSW],
            'syncNewsletterSubscribers' => (bool)$configValues[MailchimpProConfig::SYNC_NEWSLETTER_SUBSCRIBERS],
            'statusForPending' => $configValues[MailchimpProConfig::STATUSES_FOR_PENDING],
            'statusForRefunded' => $configValues[MailchimpProConfig::STATUSES_FOR_REFUNDED],
            'statusForCancelled' => $configValues[MailchimpProConfig::STATUSES_FOR_CANCELLED],
            'statusForShipped' => $configValues[MailchimpProConfig::STATUSES_FOR_SHIPPED],
            'statusForPaid' => $configValues[MailchimpProConfig::STATUSES_FOR_PAID],
            'orderStates' => $this->getOrderStates(),
            'productDescriptionField' => $configValues[MailchimpProConfig::PRODUCT_DESCRIPTION_FIELD],
            'existingOrderSyncStrategy' => $configValues[MailchimpProConfig::EXISTING_ORDER_SYNC_STRATEGY],
            'productSyncFilterActive' => $configValues[MailchimpProConfig::PRODUCT_SYNC_FILTER_ACTIVE],
            'productSyncFilterVisibility' => $configValues[MailchimpProConfig::PRODUCT_SYNC_FILTER_VISIBILITY],
            'customerSyncFilterEnabled' => $configValues[MailchimpProConfig::CUSTOMER_SYNC_FILTER_ENABLED],
            'customerSyncFilterNewsletter' => $configValues[MailchimpProConfig::CUSTOMER_SYNC_FILTER_NEWSLETTER],
            'customerSyncFilterPeriod' => $configValues[MailchimpProConfig::CUSTOMER_SYNC_FILTER_PERIOD],
            'customerSyncTagDefaultGroup' => $configValues[MailchimpProConfig::CUSTOMER_SYNC_TAG_DEFAULT_GROUP],
            'customerSyncTagGender' => (bool)$configValues[MailchimpProConfig::CUSTOMER_SYNC_TAG_GENDER],
            'customerSyncTagLanguage' => (bool)$configValues[MailchimpProConfig::CUSTOMER_SYNC_TAG_LANGUAGE],
            'cartRuleSyncFilterStatus' => $configValues[MailchimpProConfig::CART_RULE_SYNC_FILTER_STATUS],
            'cartRuleSyncFilterExpiration' => $configValues[MailchimpProConfig::CART_RULE_SYNC_FILTER_EXPIRATION],
            'newsletterSubscriberSyncFilterPeriod' => $configValues[MailchimpProConfig::NEWSLETTER_SUBSCRIBER_SYNC_FILTER_PERIOD],
            'productImageSize' => $configValues[MailchimpProConfig::PRODUCT_IMAGE_SIZE],
            'token' => $configValues[MailchimpProConfig::MAILCHIMP_API_KEY],
            'listId' => $configValues[MailchimpProConfig::MAILCHIMP_LIST_ID],
            'lists' => $lists,
            'storeSynced' => (bool)$configValues[MailchimpProConfig::MAILCHIMP_STORE_SYNCED],
            'validApiKey' => !empty($accountInfo),
            'accountInfo' => $accountInfo,
            'numberOfCartRulesToSync' => $repository->getCartRulesCount(),
            'numberOfCustomersToSync' => $repository->getCustomersCount(),
            'numberOfProductsToSync' => $repository->getProductsCount(),
            'numberOfOrdersToSync' => $repository->getOrdersCount(),
            'numberOfNewsletterSubscribersToSync' => $repository->getNewsletterSubscribersCount(),
            'logQueue' => $configValues[MailchimpProConfig::LOG_QUEUE],
            'queueStep' => $configValues[MailchimpProConfig::QUEUE_STEP],
            'queueAttempt' => $configValues[MailchimpProConfig::QUEUE_ATTEMPT],
            'logCronjob' => $configValues[MailchimpProConfig::LOG_CRONJOB],
            'cronjobLogContent' => $this->getCronjobLogContent(),
            'lastSyncedProductId' => $configValues[MailchimpProConfig::LAST_SYNCED_PRODUCT_ID],
            'lastSyncedCustomerId' => $configValues[MailchimpProConfig::LAST_SYNCED_CUSTOMER_ID],
            'lastSyncedCartRuleId' => $configValues[MailchimpProConfig::LAST_SYNCED_PROMO_ID],
            'lastSyncedOrderId' => $configValues[MailchimpProConfig::LAST_SYNCED_ORDER_ID],
            'lastSyncedCartId' => $configValues[MailchimpProConfig::LAST_SYNCED_CART_ID],
            'lastSyncedNewsletterSubscriberId' => $configValues[MailchimpProConfig::LAST_SYNCED_NEWSLETTER_SUBSCRIBER_ID],
            'lastCronjob' => $configValues[MailchimpProConfig::LAST_CRONJOB],
            'lastCronjobExecutionTime' => $configValues[MailchimpProConfig::LAST_CRONJOB_EXECUTION_TIME],
            'totalJobs' => $this->getNumberOfTotalJobs(),
            'showDashboardStats' => $configValues[MailchimpProConfig::SHOW_DASHBOARD_STATS],
            'promoOverridesEnabled' => (bool)$configValues[MailchimpProConfig::MAILCHIMP_PROMO_OVERRIDE_ENABLED],
            'promoOverridesAutoInstalled' => (bool)$configValues[MailchimpProConfig::MAILCHIMP_PROMO_OVERRIDE_AUTO_INSTALLED],
            'promoOverridesConflicts' => $configValues[MailchimpProConfig::MAILCHIMP_PROMO_OVERRIDE_CONFLICTS],
            'manuallyInstalledOverrides' => (bool)$configValues[MailchimpProConfig::MAILCHIMP_PROMO_OVERRIDE_ENABLED] && !(bool)($configValues[MailchimpProConfig::MAILCHIMP_PROMO_OVERRIDE_AUTO_INSTALLED]),
        ];
    }

    public function ajaxProcessSaveSettings()
    {
        foreach (MailchimpProConfig::$keyMap as $index => $item) {
            $value = $this->getJsonPayloadValue($index);
            $value = is_scalar($value) ? $value : json_encode($value);
            if (is_bool($value)) {
                $value = (int)$value;
            }
            MailchimpProConfig::saveValue($item, $value);
        }

        die();
    }

    public function ajaxProcessAddNewPromo()
    {
        $data = $this->getJsonPayloadValue('data');

        if (empty($data['promoName']) || empty($data['promoPrefix']) || empty($data['promoExpiration']) || empty($data['promoReduction'])) {
            $this->ajaxDie(['success' => false, 'message' => 'Please fill in all the required fields.']);
        }

        if (strtotime($data['promoExpiration']) === false) {
            $this->ajaxDie(['success' => false, 'message' => 'Invalid promo expiration date.']);
        }

        $promoName = pSQL($data['promoName']);
        $promoPrefix = pSQL($data['promoPrefix']);
        $promoSuffix = pSQL($data['promoSuffix']);
        $promoExpiration = (new DateTime($data['promoExpiration']))->format('Y-m-d H:i:s');
        $promoCampaign = !empty($data['promoCampaign']) ? pSQL($data['promoCampaign']) : null;
        $promoReduction = !empty($data['promoReduction']) ? pSQL($data['promoReduction']) : null;
        $promoStatus = !empty($data['promoStatus']) ? (int) $data['promoStatus'] : 0;
        $promoReductionType = !empty($data['promoReductionType']) ? (int) $data['promoReductionType'] : 0;
        // $promoStart = !empty($data['promoStart']) ? (new DateTime($data['promoStart']))->format('Y-m-d H:i:s') : date('Y-m-d H:i:s');

        $cartRule = new CartRule(PromoCode::getCartRuleId());
        // create new cart rule
        if (Validate::isLoadedObject($cartRule) == false) {
            $cartRule->name = [];
            $languages = Language::getLanguages(false);
            foreach ($languages as $lang) {
                $cartRule->name[$lang['id_lang']] = $this->trans('MC PromoCode', [], 'Modules.Mailchimppro.Adminmailchimpproconfiguration');
            }
            $cartRule->description = $this->module->name;
            $cartRule->code = 'MC' . Tools::passwdGen(15);
            $cartRule->quantity = 99999999;
            $cartRule->quantity_per_user = $cartRule->quantity;
            $cartRule->date_from = date('Y-m-d 00:00:00');
            $cartRule->date_to = date('Y-m-d 00:00:00', strtotime('+ 7 years'));
            $cartRule->active = true;

            if (!$cartRule->add()) {
                $this->ajaxDie([
                    'success' => false,
                    'message' => $this->trans('Failed to create the cart rule: ', [], 'Modules.Mailchimppro.Adminmailchimpproconfiguration') . Db::getInstance()->getMsgError()
                ]);
            }

            PromoCode::setCartRuleId($cartRule->id);
        }

        $campaign = new Campaign();
        $campaign->name = $promoName;
        $campaign->prefix = $promoPrefix;
        $campaign->suffix = $promoSuffix;
        $campaign->status = $promoStatus;
        $campaign->reduction = $promoReduction;
        $campaign->reduction_type = $promoReductionType;
        $campaign->campaign_id = $promoCampaign;
        $campaign->end_date = $promoExpiration;

        if (!$campaign->add()) {
            $this->ajaxDie(['success' => false, 'message' => $this->trans('Failed to add promo code.', [], 'Modules.Mailchimppro.Adminmailchimpproconfiguration')]);
        }

        /// auto creation on promo save
        $promoId = $campaign->id;
        if ($campaign->status) {
            $response = $this->syncPromoToMailchimp($promoId);
        }
        /// auto creation on promo save

        $this->ajaxDie(['success' => true, 'message' => $this->trans('Promo code added successfully!', [], 'Modules.Mailchimppro.Adminmailchimpproconfiguration'), 'promo' => $campaign]);
    }

    public function ajaxProcessGetPromoDetails()
    {
        $promoId = (int) $this->getJsonPayloadValue('id');
        if (!$promoId) {
            $this->ajaxDie(['success' => false, 'message' => $this->trans('Promo ID is missing.', [], 'Modules.Mailchimppro.Adminmailchimpproconfiguration')]);
        }

        $promo = Campaign::getById($promoId);

        if (!$promo) {
            $this->ajaxDie(['success' => false, 'message' => $this->trans('Promo not found.', [], 'Modules.Mailchimppro.Adminmailchimpproconfiguration')]);
        }
        // Check if the ps_emailsubscription table exists
        $tableExists = Db::getInstance()->getValue("
            SELECT COUNT(*) 
            FROM information_schema.tables 
            WHERE table_schema = DATABASE() 
            AND table_name = '" . _DB_PREFIX_ . "emailsubscription'
        ");

        $totalQuery = "
            SELECT COUNT(*) FROM (
                SELECT email FROM " . _DB_PREFIX_ . "customer WHERE email != ''
                " .
            ($tableExists ? "
                UNION
                SELECT email FROM " . _DB_PREFIX_ . "emailsubscription
                " : "")
            . "
            ) combined
            WHERE email IN (
                SELECT email FROM " . _DB_PREFIX_ . "mailchimppro_promocodes WHERE id_promo_main = $promoId
            )
        ";
        $total = Db::getInstance()->getValue($totalQuery);
        $totalAllQuery = "
            SELECT COUNT(*) FROM (
                SELECT email FROM " . _DB_PREFIX_ . "customer WHERE email != ''
                " .
            ($tableExists ? "
                UNION
                SELECT email FROM " . _DB_PREFIX_ . "emailsubscription
                " : "")
            . "
            ) combined
        ";
        $totalAll = Db::getInstance()->getValue($totalAllQuery);

        $remainingQuery = "
            SELECT COUNT(*) FROM (
                SELECT email FROM " . _DB_PREFIX_ . "customer WHERE email != ''
                " .
            ($tableExists ? "
                UNION
                SELECT email FROM " . _DB_PREFIX_ . "emailsubscription
                " : "")
            . "
            ) combined
            WHERE email NOT IN (
                SELECT email FROM " . _DB_PREFIX_ . "mailchimppro_promocodes WHERE id_promo_main = $promoId
            )
        ";
        $remaining = Db::getInstance()->getValue($remainingQuery);

        $this->ajaxDie(['success' => true, 'data' => $promo, 'count' => ['remain' => $remaining, 'total' => $total, 'totalAll' => $totalAll]]);
    }

    public function ajaxProcessUpdatePromo()
    {
        $data = $this->getJsonPayloadValue('data');

        if (empty($data['promoId']) || empty($data['promoName']) || empty($data['promoPrefix']) || empty($data['promoExpiration']) || empty($data['promoReduction'])) {
            $this->ajaxDie(['success' => false, 'message' => $this->trans('Please fill in all the required fields.', [], 'Modules.Mailchimppro.Adminmailchimpproconfiguration')]);
        }

        if (strtotime($data['promoExpiration']) === false) {
            $this->ajaxDie(['success' => false, 'message' => 'Invalid promo expiration date.']);
        }

        $promoId = (int) $data['promoId'];
        $promoName = pSQL($data['promoName']);
        $promoPrefix = pSQL($data['promoPrefix']);
        $promoSuffix = pSQL($data['promoSuffix']);
        $promoExpiration = (new DateTime($data['promoExpiration']))->format('Y-m-d H:i:s');
        $promoReduction = !empty($data['promoReduction']) ? pSQL($data['promoReduction']) : null;
        $promoCampaign = !empty($data['promoCampaign']) ? pSQL($data['promoCampaign']) : null;
        $promoStatus = !empty($data['promoStatus']) ? (int) $data['promoStatus'] : 0;
        $promoReductionType = !empty($data['promoReductionType']) ? (int) $data['promoReductionType'] : 0;

        $campaign = new Campaign($promoId);

        if (!Validate::isLoadedObject($campaign)) {
            $this->ajaxDie(['success' => false, 'message' => $this->trans('Promo not found.', [], 'Modules.Mailchimppro.Adminmailchimpproconfiguration')]);
        }

        // Update campaign properties
        $campaign->name = $promoName;
        $campaign->prefix = $promoPrefix;
        $campaign->suffix = $promoSuffix;
        $campaign->end_date = $promoExpiration;
        $campaign->status = $promoStatus;
        $campaign->reduction_type = $promoReductionType;
        $campaign->reduction = $promoReduction;
        $campaign->campaign_id = $promoCampaign;

        if (!$campaign->update()) {
            $this->ajaxDie(['success' => false, 'message' => $this->trans('Failed to update promo.', [], 'Modules.Mailchimppro.Adminmailchimpproconfiguration')]);
        }

       
        /// auto creation on promo save
        $promoId = $campaign->id;

        if ($campaign->id_merge_field_mc == null || $campaign->id_merge_field_mc == 0) {
            if ($campaign->status) {
                $response = $this->syncPromoToMailchimp($promoId);
            }
        }
        /// auto creation on promo save


        $this->ajaxDie(['success' => true, 'message' => $this->trans('Promo updated successfully!', [], 'Modules.Mailchimppro.Adminmailchimpproconfiguration'), 'promo' => $campaign]);
    }

    public function ajaxProcessDeletePromo()
    {
        $promoId = (int) $this->getJsonPayloadValue('id');
        if (!$promoId) {
            $this->ajaxDie(['success' => false, 'message' => $this->trans('Promo ID is missing.', [], 'Modules.Mailchimppro.Adminmailchimpproconfiguration')]);
        }

        $campaign = new Campaign($promoId);

        if($campaign->id_merge_field_mc > 0){

            $promoCommand = new PromoMergeTagSyncCommand(
                $this->context,
                $this->module->getApiClient(),
                $campaign,
                [],
                $this->context->shop->id
            );

            $delete_response = $promoCommand->deleteMergeFieldFromMailchimp();
            
        }

        if ($campaign->getCodesCount() > 0) {
            $campaign->deleteCodes();
        }

        if (!$campaign->delete()) {
            $this->ajaxDie(['success' => false, 'message' => $this->trans('Failed to delete promo.', [], 'Modules.Mailchimppro.Adminmailchimpproconfiguration')]);
        }
        $this->ajaxDie(['success' => true, 'message' => $this->trans('Promo deleted successfully!', [], 'Modules.Mailchimppro.Adminmailchimpproconfiguration')]);
    }

    public function ajaxProcessDeletePromocode()
    {
        $promoId = (int) $this->getJsonPayloadValue('id');
        if (!$promoId) {
            $this->ajaxDie(['success' => false, 'message' => $this->trans('Promocode ID is missing.', [], 'Modules.Mailchimppro.Adminmailchimpproconfiguration')]);
        }

        $promocode = new PromoCode($promoId);

        if (!$promocode->delete()) {
            $this->ajaxDie(['success' => false, 'message' => $this->trans('Failed to delete promo.', [], 'Modules.Mailchimppro.Adminmailchimpproconfiguration')]);
        }
        $this->ajaxDie(['success' => true, 'message' => $this->trans('Promo deleted successfully!', [], 'Modules.Mailchimppro.Adminmailchimpproconfiguration')]);
    }

    public function ajaxProcessDeleteBulkPromocodes()
    {
        $promoIds = $this->getJsonPayloadValue('ids');
        if (!is_array($promoIds) || empty($promoIds)) {
            $this->ajaxDie(['success' => false, 'message' => $this->trans('No promocodes selected.', [], 'Modules.Mailchimppro.Adminmailchimpproconfiguration')]);
        }

        foreach ($promoIds as $promoId) {
            $promocode = new PromoCode((int)$promoId);
            if (!$promocode->delete()) {
                $this->ajaxDie([
                    'success' => false,
                    'message' => $this->trans("Failed to delete promo with ID %promoid%.", ['%promoid%' => $promoId], 'Modules.Mailchimppro.Adminmailchimpproconfiguration'),
                ]);
            }
        }

        $this->ajaxDie(['success' => true, 'message' => $this->trans('Selected promocodes deleted successfully!', [], 'Modules.Mailchimppro.Adminmailchimpproconfiguration')]);
    }

    public function ajaxProcessGetPromoCodes()
    {
        $promoId = $this->getJsonPayloadValue('id');

        if (!$promoId) {
            $this->ajaxDie(['success' => false, 'message' => 'Invalid promo ID.']);
        }

        $promoCodes = PromoCode::getPromoCodes($promoId);

        if ($promoCodes) {
            $this->ajaxDie(['success' => true, 'codes' => $promoCodes]);
        } else {
            $this->ajaxDie(['success' => false, 'message' => 'No promo codes found for this promo ID.']);
        }
    }

    public function ajaxProcessGenerateCodes()
    {
        $id = (int)$this->getJsonPayloadValue('id');
        $batchSize = 50;
        $offset = (int)$this->getJsonPayloadValue('offset') ?: 0;

        if (!$id) {
            $this->ajaxDie(['success' => false, 'message' => $this->trans('Invalid promo ID.', [], 'Modules.Mailchimppro.Adminmailchimpproconfiguration')]);
        }

        $campaign = new Campaign($id);
        if (!$campaign->id) {
            $this->ajaxDie(['success' => false, 'message' => $this->trans('Campaign not found.', [], 'Modules.Mailchimppro.Adminmailchimpproconfiguration')]);
        }

        $prefix = $campaign->prefix;
        $suffix = $campaign->suffix;
        $date = date('Y-m-d H:i:s');

        // Check if emailsubscription table exists
        $tableExists = Db::getInstance()->getValue("
        SELECT COUNT(*) 
        FROM information_schema.tables 
        WHERE table_schema = DATABASE() 
        AND table_name = '" . _DB_PREFIX_ . "emailsubscription'
    ");

        // Get total emails count
        $totalQuery = "
        SELECT COUNT(*) FROM (
            SELECT email FROM " . _DB_PREFIX_ . "customer WHERE email != ''
            " . ($tableExists ? "UNION SELECT email FROM " . _DB_PREFIX_ . "emailsubscription" : "") . "
        ) combined
        WHERE email NOT IN (
            SELECT email FROM " . _DB_PREFIX_ . "mailchimppro_promocodes WHERE id_promo_main = $id
        )
    ";
        $total = Db::getInstance()->getValue($totalQuery);

        // Get batch of emails
        $emailQuery = "
        SELECT DISTINCT email FROM (
            SELECT email FROM " . _DB_PREFIX_ . "customer WHERE email != ''
            " . ($tableExists ? "UNION SELECT email FROM " . _DB_PREFIX_ . "emailsubscription" : "") . "
        ) combined
        WHERE email NOT IN (
            SELECT email FROM " . _DB_PREFIX_ . "mailchimppro_promocodes WHERE id_promo_main = $id
        )
        LIMIT 0, $batchSize
    ";
        $emails = Db::getInstance()->executeS($emailQuery);

        if (empty($emails)) {
            $this->ajaxDie([
                'success' => true,
                'message' => $this->trans('No more emails to process.', [], 'Modules.Mailchimppro.Adminmailchimpproconfiguration'),
                'remaining' => 0,
                'total' => $total
            ]);
        }

        $queue = new PrestaChamps\Queue\Queue();

        $generatedCount = 0;
        foreach ($emails as $emailData) {
            $email = $emailData['email'];
            $code = PromoCode::generateCode($prefix, $suffix);

            $data = [
                'id_promo_main' => (int)$campaign->id,
                'code' => pSQL($code),
                'status' => 0,
                'email' => pSQL($email),
                'date_add' => pSQL($date),
                'date_upd' => pSQL($date),
            ];

            if (Db::getInstance()->insert('mailchimppro_promocodes', $data)) {
                $lastInsertId = Db::getInstance()->Insert_ID();

                $job = new MergeTagPromoCodeSyncJob();
                $job->promoCodeId = $lastInsertId;

                $job->setCampaignId($campaign->id);            
                $job->setPromoEmail($email);
                $job->setPromoCode($code);
                $job->setMergeTagPromoCode($campaign->tag_merge_field_mc);
                $job->setMethod(PromoMergeTagSyncCommand::SYNC_METHOD_PATCH);

                $queue->push($job, 'setup-promoCode', $this->context->shop->id);    

                $generatedCount++;
            }
        }

        $remaining = $total - $batchSize;

        $this->ajaxDie([
            'success' => true,
            'message' => $this->trans('Batch processed successfully!', [], 'Modules.Mailchimppro.Adminmailchimpproconfiguration'),
            'generated_count' => $generatedCount,
            'remaining' => $remaining,
            'total' => $total,
            'next_offset' => $offset + $batchSize,
        ]);
    }

    // ajax call by manual sending 
    // public function ajaxProcessSyncPromo()
    // {
    //     $promoId = (int)$this->getJsonPayloadValue('promoId');

    //     if (!$promoId) {
    //         $this->ajaxDie(['success' => false, 'message' => $this->trans('Invalid promo ID.', [], 'Modules.Mailchimppro.Adminmailchimpproconfiguration')]);
    //     }

    //     $campaign = new Campaign($promoId);

    //     $response = $this->syncPromoToMailchimp($promoId);

    //     $resp = $this->addPromoCodesToQueue($campaign); 

    //     dump($response);
    //     die("tttt");

    // }

    public function syncPromoToMailchimp($promoId)
    {
        if (!$promoId) {
            $this->ajaxDie(['success' => false, 'message' => $this->trans('Invalid promo ID.', [], 'Modules.Mailchimppro.Adminmailchimpproconfiguration')]);
        }

        $campaign = new Campaign($promoId);

        if (!Validate::isLoadedObject($campaign)) {
            $this->ajaxDie(['success' => false, 'message' => $this->trans('Promo not found.', [], 'Modules.Mailchimppro.Adminmailchimpproconfiguration')]);
        }

        $promoCommand = new PromoMergeTagSyncCommand(
            $this->context,
            $this->module->getApiClient(),
            $campaign,
            [],
            $this->context->shop->id
        );

        if ($promoCommand->getMergeFieldLimitExceeded()) {
            $this->ajaxDie(['success' => false, 'message' => $this->trans('You already have the maximum 30 merge fields. Please remove a field in order to add a new one.', [], 'Modules.Mailchimppro.Adminmailchimpproconfiguration')]);
        }

        if ($promoCommand->createMergeFieldAtMailchimp()) {
            // $resp = $this->addPromoCodesToQueue($campaign);
            $this->ajaxDie(['success' => true, 'message' => $this->trans('Merge field successfully created at Mailchimp.', [], 'Modules.Mailchimppro.Adminmailchimpproconfiguration'), 'promo' => $campaign]);
        }
    }

    public function addPromoCodesToQueue($campaign)
    {
        // get all promo codes
        $allPromoCodes = PromoCode::getPromoCodes($campaign->id);

        $queue = new PrestaChamps\Queue\Queue();

        foreach($allPromoCodes as $promoCode){

            $job = new MergeTagPromoCodeSyncJob();
            $job->promoCodeId = $promoCode["id"];

            $job->setCampaignId($campaign->id);            
            $job->setPromoEmail($promoCode["email"]);
            $job->setPromoCode($promoCode["code"]);
            $job->setMergeTagPromoCode($campaign->tag_merge_field_mc);
            $job->setMethod(PromoMergeTagSyncCommand::SYNC_METHOD_PATCH);

            $queue->push($job, 'setup-promoCode', $this->context->shop->id);            
        }
    }

    public function ajaxProcessMarkReadJsonJobs()
    {
        Configuration::updateGlobalValue(MailchimpProConfig::DELETED_JOBS_ON_UPGRADE_ACCEPTED_MESSAGE_DATE, date('Y-m-d H:i:s'));
        Configuration::updateGlobalValue(MailchimpProConfig::DELETED_JOBS_ON_UPGRADE_ACCEPTED_MESSAGE_EMPLOYEE, Context::getContext()->employee->id);
        die();
    }

    public function ajaxProcessMarkReadAutoList()
    {
        MailchimpProConfig::saveValue(MailchimpProConfig::MAILCHIMP_AUTO_AUDIENCE_SYNC_ACCEPTED_MESSAGE_DATE, date('Y-m-d H:i:s'));
        MailchimpProConfig::saveValue(MailchimpProConfig::MAILCHIMP_AUTO_AUDIENCE_SYNC_ACCEPTED_MESSAGE_EMPLOYEE, Context::getContext()->employee->id);
        MailchimpProConfig::saveValue(MailchimpProConfig::MAILCHIMP_AUTO_AUDIENCE_SYNC, 0);
        die();
    }

    public function ajaxProcessGetEntityCount()
    {
        $repository = new \PrestaChamps\MailchimpPro\EntitySyncRepository();
        $this->ajaxDie([
            'products' => $repository->getProductsCount(),
            'orders' => $repository->getOrdersCount(),
            'customers' => $repository->getCustomersCount(),
            'cartRules' => $repository->getCartRulesCount(),
            'newsletterSubscribers' => $repository->getNewsletterSubscribersCount(),
            'totalJobs' => $this->getNumberOfTotalJobs(),
        ]);
    }

    public function ajaxProcessUpdateStaticContent()
    {
        $configValues = MailchimpProConfig::getConfigurationValues();
        $this->ajaxDie([
            'cronjobLogContent' => $this->getCronjobLogContent(),
            'lastSyncedProductId' => $configValues[MailchimpProConfig::LAST_SYNCED_PRODUCT_ID],
            'lastSyncedCustomerId' => $configValues[MailchimpProConfig::LAST_SYNCED_CUSTOMER_ID],
            'lastSyncedCartRuleId' => $configValues[MailchimpProConfig::LAST_SYNCED_PROMO_ID],
            'lastSyncedOrderId' => $configValues[MailchimpProConfig::LAST_SYNCED_ORDER_ID],
            'lastSyncedCartId' => $configValues[MailchimpProConfig::LAST_SYNCED_CART_ID],
            'lastSyncedNewsletterSubscriberId' => $configValues[MailchimpProConfig::LAST_SYNCED_NEWSLETTER_SUBSCRIBER_ID],
            'lastCronjob' => $configValues[MailchimpProConfig::LAST_CRONJOB],
            'lastCronjobExecutionTime' => $configValues[MailchimpProConfig::LAST_CRONJOB_EXECUTION_TIME],
            'totalJobs' => $this->getNumberOfTotalJobs(),
        ]);
    }

    public function getCronjobLogContent($maxLines = 500)
    {
        // Define the allowed directory
        $allowedDirectory = _PS_MODULE_DIR_ . $this->module->name . '/logs/';

        // Resolve the file path and sanitize it
        $filePath = realpath($allowedDirectory . 'cronjob.log');

        // Check if the file exists and is within the allowed directory
        if ($filePath && strpos($filePath, $allowedDirectory) === 0 && file_exists($filePath)) {
            // Only read the last X lines to prevent memory issues with large log files
            $file = new \SplFileObject($filePath);
            $file->seek(PHP_INT_MAX);
            $totalLines = $file->key();

            $startLine = max(0, $totalLines - $maxLines);
            $file->seek($startLine);

            $content = '';
            while (!$file->eof()) {
                $content .= $file->fgets();
            }

            if ($startLine > 0) {
                $content = "... (truncated, showing last {$maxLines} lines of {$totalLines} total) ...\n\n" . $content;
            }

            return $content;
        }

        return ''; // Fallback to an empty string if the file doesn't exist or is invalid
    }

    public function autoSyncStore(){
        try {
            $command = new StoreSyncCommand(
                $this->context,
                $this->module->getApiClient(),
                [$this->context->shop->id]
            );
            $command->setSyncMode($command::SYNC_MODE_REGULAR);
            $command->setMethod($command::SYNC_METHOD_POST);
            
            $response = $command->execute();

            if (isset($response['requestSuccess']) && $response['requestSuccess'] == true) {
                MailchimpProConfig::saveValue(MailchimpProConfig::MAILCHIMP_STORE_SYNCED, true);

                $resp = $this->scriptVerify();
            }

            return $response;
            
        } catch (Exception $exception) {
            //$this->responses[$entityId] = $this->mailchimp->getLastResponse();
            // \PrestaShopLogger::addLog("[MAILCHIMP]: {$exception->getMessage()}");
        }
    }

    public function scriptVerify(){
        try {
            // store script verified command
            if (!Configuration::get(MailchimpProConfig::MAILCHIMP_SCRIPT_VERIFIED)) {
               $command = new SiteVerifyCommand(
                    $this->module->getApiClient(),
                    $this->context->shop
                );

                $response = $command->execute();

                Configuration::updateValue(MailchimpProConfig::MAILCHIMP_SCRIPT_VERIFIED, true);
            }            
        } catch (Exception $exception) {
            //$this->responses[$entityId] = $this->mailchimp->getLastResponse();
            // \PrestaShopLogger::addLog("[MAILCHIMP]: {$exception->getMessage()}");
        }
    }

    public function ajaxProcessSyncStoresScript()
    {
        try {
            $command = new StoreSyncCommand(
                $this->context,
                $this->module->getApiClient(),
                [$this->context->shop->id]
            );

            $response = $command->getStoreExists($this->getShopId(), true);

            if (isset($response['connected_site'])) {
                $footer = $response['connected_site']['site_script']['fragment'];
                Configuration::updateValue(MailchimpProConfig::MAILCHIMP_SCRIPT_CACHED, $footer, true);
                Configuration::updateValue(MailchimpProConfig::MAILCHIMP_SCRIPT_CACHED_DATE, date('Y-m-d'));

                $resp = $this->scriptVerify();

                $this->ajaxDie([
                    'hasError' => false,
                    'errorMessage' => null,
                    'result' => $response,
                    'successMessage' => $this->trans('Store script has been fetched successfully!', [], 'Modules.Mailchimppro.Adminmailchimpproconfiguration'),
                ]);
            }
            else{
                $errorMessage = $this->trans('Error during syncing store script...', [], 'Modules.Mailchimppro.Adminmailchimpproconfiguration');
                
                $this->ajaxDie([
                    'hasError' => true,
                    'errorMessage' => $errorMessage,
                ]);
            }
        } catch (Exception $exception) {
            $this->ajaxDie([
                'hasError' => true,
                'errorMessage' => $this->trans('Error during syncing store script...', [], 'Modules.Mailchimppro.Adminmailchimpproconfiguration'),
            ]);
        }
    }

    public function ajaxProcessSyncStores()
    {
        try {
            $command = new StoreSyncCommand(
                $this->context,
                $this->module->getApiClient(),
                [$this->context->shop->id]
            );
            $command->setSyncMode($command::SYNC_MODE_REGULAR);
            $command->setMethod($command::SYNC_METHOD_POST);
            $response = $command->execute();

            if (isset($response['requestSuccess']) && $response['requestSuccess'] == true) {
                MailchimpProConfig::saveValue(MailchimpProConfig::MAILCHIMP_STORE_SYNCED, true);

                $resp = $this->scriptVerify();

                $this->ajaxDie([
                    'hasError' => false,
                    'errorMessage' => null,
                    'result' => $response,
                    'successMessage' => $this->trans('Store has been synced!', [], 'Modules.Mailchimppro.Adminmailchimpproconfiguration'),
                ]);
            }
            else {
                if (isset($response['requestLastErrors'])) {
                    if (is_array($response['requestLastErrors'])) {
                        $errorMessage = implode(";", array_values($response['requestLastErrors']));
                    }
                    else {
                        $errorMessage = $response['requestLastErrors'];
                    }
                }
                else {
                    $errorMessage = $this->trans('Error during syncing store...', [], 'Modules.Mailchimppro.Adminmailchimpproconfiguration');
                }
                $this->ajaxDie([
                    'hasError' => true,
                    'errorMessage' => $errorMessage,
                ]);
            }
        } catch (Exception $exception) {
            $this->ajaxDie([
                'hasError' => true,
                'errorMessage' => $this->trans('Error during syncing store...', [], 'Modules.Mailchimppro.Adminmailchimpproconfiguration'),
            ]);
        }
    }

    public function ajaxProcessAddProductsToQueue()
    {
        //$products = \ProductCore::getSimpleProducts(\Context::getContext()->language->id);
        $repository = new \PrestaChamps\MailchimpPro\EntitySyncRepository();
        $products = array_column($repository->getProducts(), 'id_product');
        $queue = new PrestaChamps\Queue\Queue();
        foreach ($products as $product) {
            $job = new ProductSyncJob();
            $job->productId = $product;
            // batch modifications
            $job->setSyncMode(\PrestaChamps\MailchimpPro\Commands\ProductSyncCommand::SYNC_MODE_BATCH);
            // $job->setMethod(\PrestaChamps\MailchimpPro\Commands\ProductSyncCommand::SYNC_METHOD_PUT);
            //end of batch modifications
            $queue->push($job, 'setup-wizard', $this->context->shop->id);
        }

        $this->ajaxDie(['ok']);
    }

    public function ajaxProcessInitializeSpecificPrices()
    {
        $repository = new \PrestaChamps\MailchimpPro\EntitySyncRepository();
        $specific_prices = $repository->getSpecificPrices();

        $current_date = new DateTime('now', new DateTimeZone(@date_default_timezone_get()));

        foreach($specific_prices as $specific_price){
            $from_date = new DateTime($specific_price['from'], new DateTimeZone(@date_default_timezone_get()));
            $to_date = new DateTime($specific_price['to'], new DateTimeZone(@date_default_timezone_get()));

            if($from_date > $current_date){

                // Insert the data using the insert method, with INSERT_IGNORE option
                Db::getInstance()->insert('mailchimppro_specific_price', [
                                        'id_specific_price' => (int)$specific_price['id_specific_price'],
                                        'id_product'        => (int)$specific_price['id_product'],
                                        'start_date'        => pSQL($specific_price['from']),
                                        'end_date'          => pSQL($specific_price['to']),
                                        'needToRun'         => 2, // Direct value since it's static
                                        'id_shop'           => (int)$specific_price['id_shop']
                                        ], 
                                        false, 
                                        true, 
                                        Db::INSERT_IGNORE
                                    );

            }elseif($to_date > $current_date){

                // Insert the data using the insert method, with INSERT_IGNORE option
                Db::getInstance()->insert('mailchimppro_specific_price', [
                                        'id_specific_price' => (int)$specific_price['id_specific_price'],
                                        'id_product'        => (int)$specific_price['id_product'],
                                        'start_date'        => pSQL($specific_price['from']),
                                        'end_date'          => pSQL($specific_price['to']),
                                        'needToRun'         => 1, // Direct value since it's static
                                        'id_shop'           => (int)$specific_price['id_shop']
                                        ], 
                                        false, 
                                        true, 
                                        Db::INSERT_IGNORE
                                    );
            }
        }

        $this->ajaxDie(['ok']);
    }

    public function ajaxProcessAddCustomersToQueue()
    {
        //$customers = array_column(Customer::getCustomers(true), 'id_customer');
        $repository = new \PrestaChamps\MailchimpPro\EntitySyncRepository();
        $customers = array_column($repository->getCustomers(), 'id_customer');
        $queue = new PrestaChamps\Queue\Queue();
        foreach ($customers as $customer) {
            $job = new CustomerSyncJob();
            $job->customerId = $customer;
            $queue->push($job, 'setup-wizard', $this->context->shop->id);
        }

        $this->ajaxDie(['ok']);
    }

    public function ajaxProcessAddOrdersToQueue()
    {
        //$orders = $this->getOrderIds();
        $repository = new \PrestaChamps\MailchimpPro\EntitySyncRepository();
        $orders = array_column($repository->getOrders(), 'id_order');
        $queue = new PrestaChamps\Queue\Queue();
        foreach ($orders as $order) {
            $job = new OrderSyncJob();
            $job->orderId = $order;
            $queue->push($job, 'setup-wizard', $this->context->shop->id);
        }

        $this->ajaxDie(['ok']);
    }

    public function ajaxProcessAddCartRulesToQueue()
    {
        //$cartRules = $this->getCartRules();
        $repository = new \PrestaChamps\MailchimpPro\EntitySyncRepository();
        $cartRules = array_column($repository->getCartRules(), 'id_cart_rule');
        $queue = new PrestaChamps\Queue\Queue();
        foreach ($cartRules as $cartRule) {
            $job = new CartRuleSyncJob();
            $job->cartRuleId = $cartRule;
            $queue->push($job, 'setup-wizard', $this->context->shop->id);
        }

        $this->ajaxDie(['ok']);
    }

    public function ajaxProcessAddNewsletterSubscribersToQueue()
    {
        if (\Module::isEnabled('Ps_Emailsubscription') || \Module::isEnabled('blocknewsletter')) {
            $repository = new \PrestaChamps\MailchimpPro\EntitySyncRepository();
            $newsletterSubscribers = $repository->getNewsletterSubscribers();
            $queue = new PrestaChamps\Queue\Queue();
            foreach ($newsletterSubscribers as $newsletterSubscriber) {
                $job = new NewsletterSubscriberSyncJob();
                $job->newsletterSubscriber = $newsletterSubscriber;
                $queue->push($job, 'setup-wizard', $this->context->shop->id);
            }
        }

        $this->ajaxDie(['ok']);
    }

    /* protected function getOrderIds()
    {
        $shopId = Shop::getContextShopID();
        $query = new DbQuery();
        $query->from('orders');
        $query->select('id_order');
        if ($shopId) {
            $query->where("id_shop = {$shopId}");
        }

        return array_column(Db::getInstance()->executeS($query), 'id_order');
    } */

    /* protected function getCartRules()
    {
        $query = new DbQuery();
        $query->from('cart_rule');
        $query->select('id_cart_rule');
        $query->where('shop_restriction = 0');
        $ids = array_column(Db::getInstance()->executeS($query), 'id_cart_rule');

        $query = new DbQuery();
        $query->from('cart_rule_shop');
        $query->select('id_cart_rule');
        $query->where('id_shop = ' . pSQL($this->context->shop->id));
        $result = array_column(Db::getInstance()->executeS($query), 'id_cart_rule');
        $result = array_unique(array_merge($ids, $result));
        sort($result, SORT_NUMERIC);

        return $result;
    } */

    public function ajaxProcessDeleteEcommerceData()
    {
        try {
            /* $shops = array_column(Shop::getShops(true), 'id_shop');
            $command = new StoreSyncCommand(
                $this->context,
                $this->module->getApiClient(),
                $shops
            ); */
            $command = new StoreSyncCommand(
                $this->context,
                $this->module->getApiClient(),
                [$this->context->shop->id]
            );
            $command->setSyncMode($command::SYNC_MODE_REGULAR);
            $command->setMethod($command::SYNC_METHOD_DELETE);
            $response = $command->execute();

            if (isset($response['requestSuccess']) && $response['requestSuccess'] == true) {
                MailchimpProConfig::saveValue(MailchimpProConfig::MAILCHIMP_STORE_SYNCED, false);
                MailchimpProConfig::saveValue(MailchimpProConfig::MAILCHIMP_AUTO_AUDIENCE_SYNC, 0);
                $this->ajaxDie([
                    'hasError' => false,
                    'errorMessage' => null,
                    'result' => $response,
                    'successMessage' => $this->trans('E-commerce data has been deleted', [], 'Modules.Mailchimppro.Adminmailchimpproconfiguration'),
                ]);
            }
            else {
                if (isset($response['requestLastErrors'])) {
                    if($response['requestLastResponse']['headers']['http_code'] == 404){
                        MailchimpProConfig::saveValue(MailchimpProConfig::MAILCHIMP_STORE_SYNCED, false);
                        MailchimpProConfig::saveValue(MailchimpProConfig::MAILCHIMP_AUTO_AUDIENCE_SYNC, 0);
                    }
                    if (is_array($response['requestLastErrors'])) {
                        $errorMessage = implode(";", array_values($response['requestLastErrors']));
                    }
                    else {
                        $errorMessage = $response['requestLastErrors'];
                    }
                }
                else {
                    $errorMessage = $this->trans('Error during deleting e-commerce data', [], 'Modules.Mailchimppro.Adminmailchimpproconfiguration');
                }
                $this->ajaxDie([
                    'hasError' => true,
                    'errorMessage' => $errorMessage,
                ]);
            }
        } catch (Exception $exception) {
            $this->ajaxDie([
                'hasError' => true,
                'errorMessage' => $this->trans('Error during deleting e-commerce data', [], 'Modules.Mailchimppro.Adminmailchimpproconfiguration'),
            ]);
        }
    }

    public function processSyncProduct()
    {
        if (Configuration::get(MailchimpProConfig::MAILCHIMP_API_KEY) && Configuration::get(MailchimpProConfig::MAILCHIMP_STORE_SYNCED) && Configuration::get(MailchimpProConfig::SYNC_PRODUCTS)) {
            try {
                if ($productId = Tools::getValue('productId')) {
                    if (!Configuration::get(MailchimpProConfig::CRONJOB_BASED_SYNC)) {
                        $command = new \PrestaChamps\MailchimpPro\Commands\ProductSyncCommand(
                            $this->context,
                            $this->module->getApiClient(),
                            [$productId]
                        );
                        $command->setSyncMode($command::SYNC_MODE_REGULAR);
                        $command->setMethod($command::SYNC_METHOD_PATCH);
                        $result = $command->execute();

                        if (isset($result["requestSuccess"]) && $result["requestSuccess"] == false) {
                            $command->setMethod($command::SYNC_METHOD_POST);
                            $result = $command->execute();                            
                        }

                        $this->ajaxDie([
                            'hasError' => false,
                            'error' => null,
                            'command_result' => $result,
                            'result' => $this->trans('Product synced', [], 'Modules.Mailchimppro.Adminmailchimpproconfiguration'),
                        ]);
                    } else {
                        $job = new ProductSyncJob();
                        $job->productId = $productId;
                        $job->setSyncMode(\PrestaChamps\MailchimpPro\Commands\ProductSyncCommand::SYNC_MODE_REGULAR);
                        $queue = new PrestaChamps\Queue\Queue();
                        $queue->push($job, 'hook-product-extra', $this->context->shop->id);
                        $this->ajaxDie([
                            'hasError' => false,
                            'error' => null,
                            'result' => $this->trans('Product job has been successfully added.', [], 'Modules.Mailchimppro.Adminmailchimpproconfiguration'),
                        ]);
                    }
                }
            } catch (Exception $exception) {
                $this->ajaxDie(
                    [
                        'hasError' => true,
                        'error' => $exception->getMessage(),
                    ],
                    null,
                    null,
                    400
                );
            }
        }
    }

    /**
     * Process the toggle of promoOverridesEnabled option
     * 
     * @return void
     */
    public function ajaxProcessTogglePromoOverrides()
    {
        // Validate and sanitize input
        $enabled = (bool)$this->getJsonPayloadValue('enabled');
        
        try {
            // Save the setting using PrestaShop's Configuration class
            Configuration::updateValue(MailchimpProConfig::MAILCHIMP_PROMO_OVERRIDE_ENABLED, (int)$enabled);
            
            // If enabling overrides, attempt to install them
            if ($enabled) {
                $autoInstalled = (bool)Configuration::get(MailchimpProConfig::MAILCHIMP_PROMO_OVERRIDE_AUTO_INSTALLED);
                
                if (!$autoInstalled) {
                    // Create an instance of the ConflictChecker
                    $conflictChecker = new \PrestaChamps\PrestaShop\Override\ConflictChecker($this->module);
                    
                    // Check for conflicts
                    $pendingOverrides = $conflictChecker->getPendingOverrides();
                    $conflicts = [];
                    
                    foreach ($pendingOverrides as $relativePath) {
                        $conflictResult = $conflictChecker->hasConflict($relativePath);
                        
                        if ($conflictResult['status'] === 'conflict') {
                            $conflicts[] = $conflictResult;
                        }
                    }
                    
                    if (!empty($conflicts)) {
                        // Save conflicts in configuration
                        Configuration::updateValue(MailchimpProConfig::MAILCHIMP_PROMO_OVERRIDE_CONFLICTS, json_encode($conflicts));
                        
                        // Return conflict information with detailed data
                        $this->ajaxDie([
                            'success' => true,
                            'overrideStatus' => 'conflicts',
                            'message' => $this->trans('Overrides could not be installed automatically due to conflicts. Manual installation is required.', [], 'Modules.Mailchimppro.Adminmailchimpproconfiguration'),
                            'conflicts' => $conflicts
                        ]);
                    } else {
                        // No conflicts, attempt to install overrides
                        try {
                            $overrideInstaller = new \PrestaChamps\PrestaShop\Override\OverrideInstaller($this->module);
                            $result = $overrideInstaller->install();
                            
                            if ($result) {
                                // Mark as auto-installed
                                Configuration::updateValue(MailchimpProConfig::MAILCHIMP_PROMO_OVERRIDE_AUTO_INSTALLED, 1);
                                // Clear any saved conflicts
                                Configuration::updateValue(MailchimpProConfig::MAILCHIMP_PROMO_OVERRIDE_CONFLICTS, '');
                                
                                $this->ajaxDie([
                                    'success' => true,
                                    'overrideStatus' => 'installed',
                                    'message' => $this->trans('Overrides installed successfully.', [], 'Modules.Mailchimppro.Adminmailchimpproconfiguration')
                                ]);
                            } else {
                                $this->ajaxDie([
                                    'success' => true,
                                    'overrideStatus' => 'failed',
                                    'message' => $this->trans('Failed to install overrides. Manual installation may be required.', [], 'Modules.Mailchimppro.Adminmailchimpproconfiguration')
                                ]);
                            }
                        } catch (Exception $e) {
                            // Save conflicts in configuration
                            Configuration::updateValue(MailchimpProConfig::MAILCHIMP_PROMO_OVERRIDE_CONFLICTS, json_encode($conflicts));
                            
                            $this->ajaxDie([
                                'success' => false,
                                'overrideStatus' => 'conflicts',
                                'message' => $this->trans('Override conflicts detected. Manual installation is required.', [], 'Modules.Mailchimppro.Adminmailchimpproconfiguration'),
                                'conflicts' => $e->getMessage(),
                                'requiresManualInstallation' => true
                            ]);
                        } catch (\Exception $e) {
                            // Log the error using PrestaShop's logger
                            PrestaShopLogger::addLog(
                                'Error during override installation: ' . $e->getMessage(),
                                3, // Error severity
                                null,
                                'Mailchimppro',
                                null,
                                true
                            );
                            
                            $this->ajaxDie([
                                'success' => false,
                                'overrideStatus' => 'error',
                                'message' => $this->trans('Error during override installation: ', [], 'Modules.Mailchimppro.Adminmailchimpproconfiguration') . $e->getMessage()
                            ]);
                        }
                    }
                } else {
                    // Already auto-installed
                    $this->ajaxDie([
                        'success' => true,
                        'overrideStatus' => 'auto_installed',
                        'message' => $this->trans('Overrides are already installed.', [], 'Modules.Mailchimppro.Adminmailchimpproconfiguration')
                    ]);
                }
            } else {
                // Overrides disabled
                $this->ajaxDie([
                    'success' => true,
                    'overrideStatus' => 'disabled',
                    'message' => $this->trans('Promo overrides have been disabled.', [], 'Modules.Mailchimppro.Adminmailchimpproconfiguration')
                ]);
            }
        } catch (\Exception $e) {
            // Log the error
            PrestaShopLogger::addLog(
                'Error toggling promo overrides: ' . $e->getMessage(),
                3, // Error severity
                null,
                'Mailchimppro',
                null,
                true
            );
            
            $this->ajaxDie([
                'success' => false,
                'message' => $this->trans('An error occurred: ', [], 'Modules.Mailchimppro.Adminmailchimpproconfiguration') . $e->getMessage(),
            ], null, null, 500);
        }
    }

    /**
     * Mark overrides as manually installed
     * 
     * @return void
     */
    public function ajaxProcessMarkOverridesInstalled()
    {
        try {
            // Update the configuration to mark overrides as manually installed
            Configuration::updateValue(MailchimpProConfig::MAILCHIMP_PROMO_OVERRIDE_AUTO_INSTALLED, 0);
            Configuration::updateValue(MailchimpProConfig::MAILCHIMP_PROMO_OVERRIDE_ENABLED, 1);
            // Clear any saved conflicts
            // Configuration::updateValue(MailchimpProConfig::MAILCHIMP_PROMO_OVERRIDE_CONFLICTS, '');
            
            $this->ajaxDie([
                'success' => true,
                'message' => $this->trans('Overrides marked as installed.', [], 'Modules.Mailchimppro.Adminmailchimpproconfiguration'),
            ]);
        } catch (\Exception $e) {
            // Log the error
            PrestaShopLogger::addLog(
                'Error marking overrides as installed: ' . $e->getMessage(),
                3, // Error severity
                null,
                'Mailchimppro',
                null,
                true
            );
            
            $this->ajaxDie([
                'success' => false,
                'message' => $this->trans('An error occurred: ', [], 'Modules.Mailchimppro.Adminmailchimpproconfiguration') . $e->getMessage(),
            ], null, null, 500);
        }
    }

    /**
     * Remove overrides if they were auto-installed without conflicts
     * 
     * @return void
     */
    public function ajaxProcessRemovePromoOverrides()
    {
        try {
            $autoInstalled = (bool)Configuration::get(MailchimpProConfig::MAILCHIMP_PROMO_OVERRIDE_AUTO_INSTALLED);
            $conflicts = Configuration::get(MailchimpProConfig::MAILCHIMP_PROMO_OVERRIDE_CONFLICTS);
            
            // Only remove if auto-installed and no conflicts
            if ($autoInstalled && (!$conflicts || $conflicts === '[]' || $conflicts === '')) {
                $overrideInstaller = new \PrestaChamps\PrestaShop\Override\OverrideInstaller($this->module);
                $result = $overrideInstaller->uninstall();
                
                if ($result) {
                    // Reset configuration
                    Configuration::updateValue(MailchimpProConfig::MAILCHIMP_PROMO_OVERRIDE_AUTO_INSTALLED, 0);
                    Configuration::updateValue(MailchimpProConfig::MAILCHIMP_PROMO_OVERRIDE_ENABLED, 0);
                    
                    $this->ajaxDie([
                        'success' => true,
                        'message' => $this->trans('Overrides have been removed successfully.', [], 'Modules.Mailchimppro.Adminmailchimpproconfiguration')
                    ]);
                } else {
                    $this->ajaxDie([
                        'success' => false,
                        'message' => $this->trans('Failed to remove overrides.', [], 'Modules.Mailchimppro.Adminmailchimpproconfiguration')
                    ]);
                }
            } else {
                // If not auto-installed or has conflicts, just disable
                Configuration::updateValue(MailchimpProConfig::MAILCHIMP_PROMO_OVERRIDE_ENABLED, 0);
                
                $this->ajaxDie([
                    'success' => true,
                    'message' => $this->trans('Promo overrides have been disabled.', [], 'Modules.Mailchimppro.Adminmailchimpproconfiguration')
                ]);
            }
        } catch (\Exception $e) {
            // Log the error
            PrestaShopLogger::addLog(
                'Error removing promo overrides: ' . $e->getMessage(),
                3, // Error severity
                null,
                'Mailchimppro',
                null,
                true
            );
            
            $this->ajaxDie([
                'success' => false,
                'message' => $this->trans('An error occurred: ', [], 'Modules.Mailchimppro.Adminmailchimpproconfiguration') . $e->getMessage(),
            ], null, null, 500);
        }
    }

    /**
     * Check override installation status
     * 
     * @return void
     */
    public function ajaxProcessCheckOverrideStatus()
    {
        try {
            $enabled = (bool)Configuration::get(MailchimpProConfig::MAILCHIMP_PROMO_OVERRIDE_ENABLED);
            $autoInstalled = (bool)Configuration::get(MailchimpProConfig::MAILCHIMP_PROMO_OVERRIDE_AUTO_INSTALLED);
            
            // Get saved conflicts
            $savedConflicts = Configuration::get(MailchimpProConfig::MAILCHIMP_PROMO_OVERRIDE_CONFLICTS);
            $conflicts = $savedConflicts ? json_decode($savedConflicts, true) : [];
            
            // Check if overrides exist
            $conflictChecker = new \PrestaChamps\PrestaShop\Override\ConflictChecker($this->module);
            $cartOverrideExists = $conflictChecker->overrideExists('classes/Cart.php');
            $cartRuleOverrideExists = $conflictChecker->overrideExists('classes/CartRule.php');
            
            // Check if they have our custom code
            $cartHasCustomCode = $conflictChecker->hasCustomCode('classes/Cart.php');
            $cartRuleHasCustomCode = $conflictChecker->hasCustomCode('classes/CartRule.php');
            
            $this->ajaxDie([
                'success' => true,
                'enabled' => $enabled,
                'autoInstalled' => $autoInstalled,
                'conflicts' => $conflicts,
                'cartOverrideExists' => $cartOverrideExists,
                'cartRuleOverrideExists' => $cartRuleOverrideExists,
                'cartHasCustomCode' => $cartHasCustomCode,
                'cartRuleHasCustomCode' => $cartRuleHasCustomCode,
                'allInstalled' => ($cartOverrideExists && $cartRuleOverrideExists && $cartHasCustomCode && $cartRuleHasCustomCode)
            ]);
        } catch (\Exception $e) {
            // Log the error
            PrestaShopLogger::addLog(
                'Error checking override status: ' . $e->getMessage(),
                3, // Error severity
                null,
                'Mailchimppro',
                null,
                true
            );
            
            $this->ajaxDie([
                'success' => false,
                'message' => $this->trans('An error occurred: ', [], 'Modules.Mailchimppro.Adminmailchimpproconfiguration') . $e->getMessage(),
            ], null, null, 500);
        }
    }

    public function ajaxProcessRefreshReports()
    {
        $this->module->assignStatisticsVariables(['refresh' => true]);
        $this->ajaxDie([
            'statistics' => $this->context->smarty->fetch(
                $this->module->getLocalPath() . 'views/templates/admin/configuration/_statistics-data.tpl'
            ),
        ]);
    }
}
